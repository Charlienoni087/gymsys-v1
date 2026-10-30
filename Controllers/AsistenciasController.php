<?php

// 1. Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/asistencia.php";

$modeloAsistencias = new Asistencias($conexion);
$urlRetorno = "/GYMSYS/Views/Principal.php?page=reportes&tipo=asistencia";

/* =====================================================
   REGISTRAR ASISTENCIA
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_asistencia'])) {

    $id_usuario    = (int) ($_SESSION['id_usuario'] ?? 0);
    $id_cliente    = (int) ($_POST['id_cliente'] ?? 0);
    $fecha_raw     = trim($_POST['fecha_asistencia'] ?? '');
    $hora_ent_raw  = trim($_POST['hora_entrada'] ?? '');
    $hora_sal_raw  = trim($_POST['hora_salida'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $observaciones = $observaciones !== '' ? mb_substr($observaciones, 0, 255) : null;

    if ($id_usuario <= 0) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Tu sesión expiró. Inicia sesión de nuevo.'];
        header("Location: {$urlRetorno}");
        exit();
    }

    $dFecha = DateTime::createFromFormat('Y-m-d', $fecha_raw);
    $fechaValida = ($dFecha && $dFecha->format('Y-m-d') === $fecha_raw) ? $fecha_raw : null;

    $dEntrada = DateTime::createFromFormat('H:i', $hora_ent_raw);
    $entradaValida = ($dEntrada && $dEntrada->format('H:i') === $hora_ent_raw) ? $hora_ent_raw : null;

    $salidaValida = null;
    $salidaEscrita = ($hora_sal_raw !== '');
    if ($salidaEscrita) {
        $dSalida = DateTime::createFromFormat('H:i', $hora_sal_raw);
        $salidaValida = ($dSalida && $dSalida->format('H:i') === $hora_sal_raw) ? $hora_sal_raw : null;
    }

    $idsActivos = array_column($modeloAsistencias->obtenerClientesActivos(), 'id_cliente');

    if ($id_cliente <= 0 || !in_array($id_cliente, array_map('intval', $idsActivos), true)) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Selecciona un cliente activo de la lista.'];
    } elseif ($fechaValida === null || $fechaValida > date('Y-m-d')) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'La fecha no es válida o es posterior a hoy.'];
    } elseif ($entradaValida === null) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'La hora de entrada no es válida.'];
    } 
    elseif ($salidaEscrita && $salidaValida === null) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'La hora de salida no es válida.'];
    }
     elseif ($salidaValida !== null && $salidaValida < $entradaValida) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'La salida no puede ser anterior a la entrada.'];
    }
    
     elseif ($salidaValida === null && $modeloAsistencias->tieneEntradaAbierta($id_cliente, $fechaValida)) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Este cliente ya tiene una entrada sin salida en esa fecha. Marca su salida primero.'];
    }
    
    else {
        $resultado = $modeloAsistencias->crear($id_cliente, $id_usuario, $fechaValida, $entradaValida, $salidaValida, $observaciones);

        if ($resultado) {
            $_SESSION['toast'] = ['tipo' => 'success', 'mensaje' => '¡Asistencia registrada con éxito!'];
            header("Location: {$urlRetorno}&guardado=1");
            exit();
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'No se pudo registrar la asistencia.'];
        }
    }

    header("Location: {$urlRetorno}");
    exit();
}

/* =====================================================
   REGISTRAR SALIDA
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_salida'])) {

    $id_usuario = (int) ($_SESSION['id_usuario'] ?? 0);

    if ($id_usuario <= 0) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Tu sesión expiró. Inicia sesión de nuevo.'];
        header("Location: {$urlRetorno}");
        exit();
    }

    $id_asistencia = (int) ($_POST['id_asistencia'] ?? 0);

    if ($id_asistencia > 0) {
        $resultado = $modeloAsistencias->registrarSalida($id_asistencia, date('H:i'));

        if ($resultado) {
            $_SESSION['toast'] = ['tipo' => 'success', 'mensaje' => '¡Salida registrada con éxito!'];
            header("Location: {$urlRetorno}&salida=1");
            exit();
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'No se pudo registrar la salida. Puede que ya tuviera una.'];
        }
    } else {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Asistencia no válida.'];
    }

    header("Location: {$urlRetorno}");
    exit();
}

/* =====================================================
   ELIMINAR ASISTENCIA
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_asistencia'])) {

    $id_usuario = (int) ($_SESSION['id_usuario'] ?? 0);
    $rolUsuario = $_SESSION['rol'] ?? '';

    if ($id_usuario <= 0) {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Tu sesión expiró. Inicia sesión de nuevo.'];
        header("Location: {$urlRetorno}");
        exit();
    }

    if ($rolUsuario !== 'administrador') {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'No tienes permiso para realizar esta acción.'];
        header("Location: {$urlRetorno}");
        exit();
    }

    $id_asistencia = (int) ($_POST['id_asistencia'] ?? 0);

    if ($id_asistencia > 0) {
        $resultado = $modeloAsistencias->eliminar($id_asistencia);

        if ($resultado) {
            $_SESSION['toast'] = ['tipo' => 'success', 'mensaje' => '¡Asistencia eliminada con éxito!'];
            header("Location: {$urlRetorno}&eliminado=1");
            exit();
        } else {
            $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'No se pudo eliminar la asistencia.'];
        }
    } else {
        $_SESSION['toast'] = ['tipo' => 'error', 'mensaje' => 'Asistencia no válida.'];
    }

    header("Location: {$urlRetorno}");
    exit();
}

// Si se accede directo por GET, volver a la vista principal
header("Location: {$urlRetorno}");
exit();