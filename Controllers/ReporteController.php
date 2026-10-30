<?php

// Sesión (por si el controlador se abre sin pasar por Principal.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Importar archivos necesarios
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/reportes.php";
require_once __DIR__ . "/../Models/ConsultasReportes.php";
require_once __DIR__ . "/../Models/asistencia.php";

$modeloReportes    = new Reportes($conexion);
$consultas         = new ConsultasReportes($conexion);
$modeloAsistencias = new Asistencias($conexion);

// Valores permitidos
$tipos_validos = ['membresias', 'clientes', 'inventario', 'asistencia'];

// Estados permitidos según el tipo de reporte
$estados_validos = [
    'membresias' => ['Vigente', 'Vencida'],
    'clientes'   => ['activo', 'inactivo'],
];

// Variables que usa la vista
$toast             = null;
$resultadosReporte = null;
$tipoGenerado      = null;
$autoImprimir      = false;
$filtros = [
    'tipo_reporte' => '',
    'rango_inicio' => '',
    'rango_fin'    => '',
    'estado'       => '',
];

// Validar fecha en formato Y-m-d
function fechaValida(?string $f): ?string {
    if ($f === null || $f === '') {
        return null;
    }

    $d = DateTime::createFromFormat('Y-m-d', $f);

    return ($d && $d->format('Y-m-d') === $f) ? $f : null;
}

// Ejecutar la consulta que corresponde a cada tipo de reporte
function ejecutarReporte(
    ConsultasReportes $consultas,
    string $tipo,
    ?string $rango_inicio,
    ?string $rango_fin,
    ?string $estado
): array {
    return match ($tipo) {
        'membresias' => $consultas->membresias($rango_inicio, $rango_fin, $estado),
        'clientes'   => $consultas->clientes($rango_inicio, $rango_fin, $estado),
        'inventario' => $consultas->inventario(),
        'asistencia' => $consultas->asistencia($rango_inicio, $rango_fin),
    };
}

// Preseleccionar un tipo al volver de otro controlador (ej. al registrar una asistencia)
$tipoGet = $_GET['tipo'] ?? '';

if (in_array($tipoGet, $tipos_validos, true)) {
    $filtros['tipo_reporte'] = $tipoGet;
}

// Generar reporte y guardarlo en el historial
if (
    $_SERVER['REQUEST_METHOD'] === "POST"
    && isset($_POST['generar_reporte'])
) {
    $id_usuario   = intval($_SESSION['id_usuario'] ?? 0);
    $tipo         = $_POST['tipo_reporte'] ?? '';
    $rango_inicio = fechaValida($_POST['rango_inicio'] ?? null);
    $rango_fin    = fechaValida($_POST['rango_fin'] ?? null);
    $estado       = trim($_POST['estado'] ?? '');
    $estado       = $estado !== '' ? $estado : null;

    // Conservar lo que escribió el usuario para rellenar el formulario
    $filtros = [
        'tipo_reporte' => $tipo,
        'rango_inicio' => $rango_inicio ?? '',
        'rango_fin'    => $rango_fin ?? '',
        'estado'       => $estado ?? '',
    ];

    $tipoOk   = in_array($tipo, $tipos_validos, true);
    $rangoOk  = $rango_inicio === null
             || $rango_fin === null
             || $rango_inicio <= $rango_fin;
    $estadoOk = $estado === null
             || (isset($estados_validos[$tipo])
                 && in_array($estado, $estados_validos[$tipo], true));

    if ($id_usuario > 0 && $tipoOk && $rangoOk && $estadoOk) {
        try {
            $resultadosReporte = ejecutarReporte(
                $consultas,
                $tipo,
                $rango_inicio,
                $rango_fin,
                $estado
            );

            $tipoGenerado = $tipo;

            // Inventario no usa rango de fechas ni estado
            if ($tipo === 'inventario') {
                $rango_inicio = null;
                $rango_fin    = null;
            }

            // Registrar en el historial
            $modeloReportes->crear($id_usuario, $tipo, $rango_inicio, $rango_fin);

            $toast = [
                'tipo'    => 'success',
                'mensaje' => '¡Reporte generado con éxito!'
            ];
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $resultadosReporte = null;
            $tipoGenerado      = null;

            $toast = [
                'tipo'    => 'error',
                'mensaje' => 'No se pudo generar el reporte.'
            ];
        }
    } else {
        $toast = [
            'tipo'    => 'error',
            'mensaje' => 'Los datos del reporte no son válidos.'
        ];
    }
}

// Volver a imprimir un reporte del historial
// Se vuelve a ejecutar la consulta con el tipo y el rango guardados
// (no crea un registro nuevo en el historial)
if (
    $_SERVER['REQUEST_METHOD'] === "GET"
    && isset($_GET['reimprimir'])
) {
    $id_reimprimir = intval($_GET['reimprimir']);
    $registro = $id_reimprimir > 0
        ? $modeloReportes->obtenerPorId($id_reimprimir)
        : null;

    if ($registro && in_array($registro['tipo_reporte'], $tipos_validos, true)) {
        try {
            $tipo         = $registro['tipo_reporte'];
            $rango_inicio = $registro['rango_inicio'] ?: null;
            $rango_fin    = $registro['rango_fin'] ?: null;

            $resultadosReporte = ejecutarReporte(
                $consultas,
                $tipo,
                $rango_inicio,
                $rango_fin,
                null
            );

            $tipoGenerado = $tipo;
            $filtros = [
                'tipo_reporte' => $tipo,
                'rango_inicio' => $rango_inicio ?? '',
                'rango_fin'    => $rango_fin ?? '',
                'estado'       => '',
            ];

            // Solo se abre el diálogo de impresión si hay algo que imprimir
            $autoImprimir = count($resultadosReporte) > 0;
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());

            $toast = [
                'tipo'    => 'error',
                'mensaje' => 'No se pudo preparar el reporte para imprimir.'
            ];
        }
    } else {
        $toast = [
            'tipo'    => 'error',
            'mensaje' => 'No se encontró el reporte en el historial.'
        ];
    }
}

// Eliminar registro del historial (solo administrador, solo POST)
if (
    $_SERVER['REQUEST_METHOD'] === "POST"
    && isset($_POST['eliminar_reporte'])
) {
    $id = intval($_POST['id_reportes'] ?? 0);

    if (($_SESSION['rol'] ?? '') !== 'administrador') {
        $_SESSION['toast'] = [
            'tipo'    => 'error',
            'mensaje' => 'No tienes permiso para realizar esta acción.'
        ];
    } elseif ($id > 0) {
        try {
            $_SESSION['toast'] = $modeloReportes->eliminar($id)
                ? ['tipo' => 'success', 'mensaje' => '¡Reporte eliminado del historial!']
                : ['tipo' => 'error',   'mensaje' => 'Ocurrió un error al procesar el reporte.'];
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());

            $_SESSION['toast'] = [
                'tipo'    => 'error',
                'mensaje' => 'Ocurrió un error al procesar el reporte.'
            ];
        }
    } else {
        $_SESSION['toast'] = [
            'tipo'    => 'error',
            'mensaje' => 'Ocurrió un error al procesar el reporte.'
        ];
    }

    header("Location: Principal.php?page=reportes");
    exit;
}

// Datos para la sección de asistencia (lista de clientes y últimas asistencias)
$listaClientes    = $modeloAsistencias->obtenerClientesActivos();
$listaAsistencias = $modeloAsistencias->obtenerRecientes(10);

// Cargar historial (últimos 50)
$listaReportes = $modeloReportes->obtenerReportes();

// Cargar la vista
require_once __DIR__ . "/../Views/Reportes.php";

?>