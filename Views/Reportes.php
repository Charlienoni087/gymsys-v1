<?php
/** @var mysqli $conexion */
/** @var array $listaReportes */
/** @var array $listaClientes */
/** @var array $listaAsistencias */
/** @var array|null $resultadosReporte */
/** @var string|null $tipoGenerado */
/** @var array $filtros */
/** @var bool $autoImprimir */
/** @var array|null $toast */

// Alertas temporales
$toast = $toast ?? ($_SESSION['toast'] ?? null);
unset($_SESSION['toast']);

$esAdmin = ($_SESSION['rol'] ?? '') === 'administrador';

$tiposReporte = [
    'membresias' => ['titulo' => 'Membresías', 'detalle' => 'Costos, fechas y vencimientos.',  'icono' => 'bi-calendar2-check'],
    'clientes'   => ['titulo' => 'Clientes',   'detalle' => 'Registrados y por estado.',         'icono' => 'bi-people'],
    'inventario' => ['titulo' => 'Inventario', 'detalle' => 'Productos y existencias.',          'icono' => 'bi-box-seam'],
    'asistencia' => ['titulo' => 'Asistencia', 'detalle' => 'Entradas y salidas de clientes.',   'icono' => 'bi-clipboard-check'],
];

$etiquetasColumnas = [
    'nombre_cliente'    => 'Cliente',
    'cedula'            => 'Cédula',
    'costo'             => 'Costo',
    'metodo_pago'       => 'Método de pago',
    'fecha_registro'    => 'Registro',
    'fecha_vencimiento' => 'Vencimiento',
    'estado'            => 'Estado',
    'correo'            => 'Correo',
    'telefono'          => 'Teléfono',
    'nombre_producto'   => 'Producto',
    'nombre_categoria'  => 'Categoría',
    'precio_venta'      => 'Precio',
    'stock_disponible'  => 'Existencias',
    'fecha_asistencia'  => 'Fecha',
    'hora_entrada'      => 'Entrada',
    'hora_salida'       => 'Salida',
    'permanencia'       => 'Permanencia',
    'registrado_por'    => 'Registrado por',
];

$columnasFecha  = ['fecha_registro', 'fecha_vencimiento', 'fecha_asistencia'];
$columnasDinero = ['costo', 'precio_venta'];

if (!function_exists('rep_e')) {
    function rep_e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('rep_fecha')) {
    function rep_fecha(?string $f): string {
        if (!$f) return '—';
        $t = strtotime($f);
        return $t ? date('d/m/Y', $t) : rep_e($f);
    }
}

if (!function_exists('rep_fecha_hora')) {
    function rep_fecha_hora(?string $f): string {
        if (!$f) return '—';
        $t = strtotime($f);
        return $t ? date('d/m/Y H:i', $t) : rep_e($f);
    }
}

// Datos del negocio para el encabezado impreso
$info = [];
try {
    $res = $conexion->query("SELECT * FROM InformacionGym LIMIT 1");
    $info = $res ? ($res->fetch_assoc() ?: []) : [];
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
}

$tipoSeleccionado = $filtros['tipo_reporte'] ?? '';
$hayResultado     = $resultadosReporte !== null && $tipoGenerado !== null;
?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/notification.css">
    <link rel="stylesheet" href="../assets/css/reportes.css">
    <style>
        /* Posición de notificación */
        .notificacion-app {
            top: 50%;
            bottom: auto;
            transform: translate(-50%, -50%);
        }
        .notificacion-app.mostrar,
        .notificacion-app.ocultar {
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<div class="container my-4 reportes-container">

    <!-- Encabezado del módulo -->
    <div class="rep-encabezado mb-4 no-imprimir">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <h2 class="text-white mt-2 mb-1 fs-4 fw-bold">Módulo de reportes</h2>
        <p class="text-secondary mb-0">Elige un reporte, ajusta los filtros y genéralo.</p>
    </div>

    <!-- Volver a ver todas las tarjetas -->
    <div id="barraVolver" class="mb-3 d-none no-imprimir">
        <button type="button" id="btnVolver" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Ver todos los reportes
        </button>
    </div>

    <!-- Botones/Tarjetas de tipo de reporte -->
    <div id="zonaTarjetas" class="row g-3 mb-4 no-imprimir" role="group" aria-label="Tipo de reporte">
        <?php foreach ($tiposReporte as $clave => $t): ?>
            <div class="col-12 col-md-6 col-tarjeta" data-tipo="<?= rep_e($clave) ?>">
                <button type="button"
                        class="rep-tarjeta"
                        data-tipo="<?= rep_e($clave) ?>"
                        aria-pressed="<?= $tipoSeleccionado === $clave ? 'true' : 'false' ?>">
                    <i class="bi <?= rep_e($t['icono']) ?>"></i>
                    <strong><?= rep_e($t['titulo']) ?></strong>
                    <span><?= rep_e($t['detalle']) ?></span>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Registro de asistencia -->
    <?php require __DIR__ . '/Asistencias.php'; ?>

    <!-- Filtros y generación del reporte -->
    <form method="POST" action="Principal.php?page=reportes" id="formReporte" class="no-imprimir">
        <input type="hidden" name="tipo_reporte" id="tipoReporte" value="<?= rep_e($tipoSeleccionado) ?>">

        <div id="panelFiltros" class="rep-panel mb-4 d-none">
            <p class="mb-3" id="ayudaFiltros" style="color: #a8a8a8; font-size: .9rem;"></p>

            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3" data-filtro="fechas">
                    <label for="rangoInicio" class="form-label">Desde</label>
                    <input type="date" class="form-control" id="rangoInicio" name="rango_inicio"
                           value="<?= rep_e($filtros['rango_inicio'] ?? '') ?>">
                </div>
                <div class="col-6 col-md-3" data-filtro="fechas">
                    <label for="rangoFin" class="form-label">Hasta</label>
                    <input type="date" class="form-control" id="rangoFin" name="rango_fin"
                           value="<?= rep_e($filtros['rango_fin'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-3" data-filtro="estado">
                    <label for="estadoReporte" class="form-label">Estado</label>
                    <select class="form-select" id="estadoReporte" name="estado"
                            data-seleccionado="<?= rep_e($filtros['estado'] ?? '') ?>">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" name="generar_reporte" id="btnGenerar"
                            class="btn btn-gym w-100 py-2" disabled>
                        <i class="bi bi-play-fill"></i> Generar reporte
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Resultado del reporte -->
    <?php if ($hayResultado): ?>
        <?php
        $titulo    = $tiposReporte[$tipoGenerado]['titulo'] ?? ucfirst($tipoGenerado);
        $total     = count($resultadosReporte);
        $usaFechas = in_array($tipoGenerado, ['membresias', 'clientes', 'asistencia'], true);
        ?>

        <!-- Área que se imprime -->
        <div id="areaImpresion">

            <!-- Encabezado para impresión física/PDF -->
            <div class="rep-impresion-encabezado">
                <h2><?= rep_e($info['nombre_negocio'] ?? 'GYMSYS') ?></h2>
                <p>
                    <?= rep_e($info['direccion'] ?? '') ?>
                    <?= !empty($info['telefono']) ? ' · Tel: ' . rep_e($info['telefono']) : '' ?>
                    <?= !empty($info['correo']) ? ' · ' . rep_e($info['correo']) : '' ?>
                </p>
                <p><strong>Reporte de <?= rep_e(mb_strtolower($titulo)) ?></strong></p>
                <p>
                    Generado el <?= date('d/m/Y H:i') ?> por <?= rep_e($_SESSION['nombre'] ?? 'usuario') ?>
                    <?php if ($usaFechas && ($filtros['rango_inicio'] !== '' || $filtros['rango_fin'] !== '')): ?>
                        · Del <?= rep_fecha($filtros['rango_inicio'] ?: null) ?> al <?= rep_fecha($filtros['rango_fin'] ?: null) ?>
                    <?php endif; ?>
                    <?php if (($filtros['estado'] ?? '') !== ''): ?>
                        · Estado: <?= rep_e($filtros['estado']) ?>
                    <?php endif; ?>
                </p>
            </div>

            <section id="zonaResultado" class="rep-resultado mb-4" aria-label="Resultado del reporte">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-imprimir">
                    <div>
                        <h3>Reporte de <?= rep_e(mb_strtolower($titulo)) ?></h3>
                        <div class="rep-meta"><?= $total ?> <?= $total === 1 ? 'registro' : 'registros' ?></div>
                    </div>
                    <?php if ($total > 0): ?>
                        <button type="button" class="btn btn-dark" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimir reporte
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($total === 0): ?>
                    <p class="mb-0">No hay registros con los filtros elegidos. Prueba con otro rango de fechas o quita el estado.</p>
                <?php else: ?>
                    <div class="table-responsive shadow-sm rounded">
                        <table class="rep-tabla">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($resultadosReporte[0]) as $col): ?>
                                        <th><?= rep_e($etiquetasColumnas[$col] ?? $col) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultadosReporte as $fila): ?>
                                    <tr>
                                        <?php foreach ($fila as $col => $valor): ?>
                                            <?php if (in_array($col, $columnasDinero, true)): ?>
                                                <td class="num">C$ <?= number_format((float) $valor, 2) ?></td>

                                            <?php elseif (in_array($col, $columnasFecha, true)): ?>
                                                <td><?= rep_fecha($valor) ?></td>

                                            <?php elseif ($col === 'estado'): ?>
                                                <?php $ok = in_array($valor, ['Vigente', 'activo'], true); ?>
                                                <td><span class="rep-chip <?= $ok ? 'ok' : 'alerta' ?>"><?= rep_e($valor) ?></span></td>

                                            <?php elseif ($col === 'stock_disponible'): ?>
                                                <td class="num">
                                                    <?= (int) $valor ?>
                                                    <?php if ((int) $valor <= 5): ?>
                                                        <span class="rep-chip alerta ms-1">Bajo</span>
                                                    <?php endif; ?>
                                                </td>

                                            <?php elseif ($col === 'hora_salida'): ?>
                                                <td><?= $valor ? rep_e(substr($valor, 0, 5)) : '—' ?></td>

                                            <?php elseif ($col === 'hora_entrada'): ?>
                                                <td><?= rep_e(substr((string) $valor, 0, 5)) ?></td>

                                            <?php else: ?>
                                                <td><?= ($valor === null || $valor === '') ? '—' : rep_e($valor) ?></td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Pie solo para impresión -->
            <div class="rep-impresion-pie">
                Total: <?= $total ?> <?= $total === 1 ? 'registro' : 'registros' ?>
            </div>

        </div>
        <!-- /areaImpresion -->
    <?php endif; ?>

    <!-- Historial de reportes -->
    <section class="no-imprimir" aria-label="Historial de reportes">
        <div class="row mb-3 align-items-center">
            <div class="col-md-12">
                <p class="fw-bold fs-5 mb-0 text-white">Historial de reportes</p>
            </div>
        </div>

        <?php if (empty($listaReportes)): ?>
            <div class="rep-panel text-muted">
                Aún no se ha generado ningún reporte. Elige uno arriba para empezar.
            </div>
        <?php else: ?>
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-striped table-bordered table-hover align-middle text-center mb-0 tabla-historial">
                    <thead>
                        <tr>
                            <th>Generado</th>
                            <th>Reporte</th>
                            <th>Rango</th>
                            <th>Por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider bg-white text-dark">
                        <?php foreach ($listaReportes as $r): ?>
                            <tr>
                                <td><?= rep_fecha_hora($r['fecha_generacion']) ?></td>
                                <td><?= rep_e($tiposReporte[$r['tipo_reporte']]['titulo'] ?? $r['tipo_reporte']) ?></td>
                                <td>
                                    <?php if ($r['rango_inicio'] || $r['rango_fin']): ?>
                                        <?= rep_fecha($r['rango_inicio']) ?> a <?= rep_fecha($r['rango_fin']) ?>
                                    <?php else: ?>
                                        Sin rango
                                    <?php endif; ?>
                                </td>
                                <td><?= rep_e($r['nombre_usuario']) ?></td>
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <a href="Principal.php?page=reportes&reimprimir=<?= (int) $r['id_reporte'] ?>"
                                           class="btn btn-sm btn-outline-warning text-dark"
                                           title="Volver a generar e imprimir este reporte"
                                           aria-label="Imprimir reporte">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                        <?php if ($esAdmin): ?>
                                            <form method="POST" action="Principal.php?page=reportes" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este registro del historial?');">
                                                <input type="hidden" name="id_reportes" value="<?= (int) $r['id_reporte'] ?>">
                                                <button type="submit" name="eliminar_reporte"
                                                        class="btn btn-sm btn-outline-danger"
                                                        aria-label="Eliminar registro del historial">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Contenedor para las notificaciones unificadas -->
<div id="notificacionApp" class="notificacion-app">
    <i id="notificacionIcono" class="bi"></i>
    <span id="notificacionTexto"></span>
    <button type="button" id="notificacionCerrar" class="notificacion-cerrar">
        <i class="bi bi-x"></i>
    </button>
</div>

<!-- Javascript -->
<script src="../js/notifications.js"></script>

<script>
(function () {
    const zonaTarjetas    = document.getElementById('zonaTarjetas');
    const columnas        = document.querySelectorAll('.col-tarjeta');
    const tarjetas        = document.querySelectorAll('.rep-tarjeta');
    const barraVolver     = document.getElementById('barraVolver');
    const btnVolver       = document.getElementById('btnVolver');
    const inputTipo       = document.getElementById('tipoReporte');
    const panelFiltros    = document.getElementById('panelFiltros');
    const panelAsistencia = document.getElementById('panelAsistencia');
    const zonaResultado   = document.getElementById('zonaResultado');
    const areaImpresion   = document.getElementById('areaImpresion');
    const btnGenerar      = document.getElementById('btnGenerar');
    const ayuda           = document.getElementById('ayudaFiltros');
    const selEstado       = document.getElementById('estadoReporte');
    const bloquesFechas   = document.querySelectorAll('[data-filtro="fechas"]');
    const bloqueEstado    = document.querySelector('[data-filtro="estado"]');

    const config = {
        membresias: {
            fechas: true,
            estados: [['Vigente', 'Vigentes'], ['Vencida', 'Vencidas']],
            ayuda: 'Las fechas son opcionales: déjalas vacías para ver todo el historial.'
        },
        clientes: {
            fechas: true,
            estados: [['activo', 'Activos'], ['inactivo', 'Inactivos']],
            ayuda: 'Las fechas son opcionales: déjalas vacías para ver todo el historial.'
        },
        inventario: {
            fechas: false,
            estados: null,
            ayuda: 'Este reporte muestra las existencias actuales y no necesita filtros.'
        },
        asistencia: {
            fechas: true,
            estados: null,
            panel: true,
            ayuda: 'Para ver el reporte de asistencia, elige un rango de fechas (opcional) y genéralo.'
        }
    };

    function ocultarResultado() {
        if (areaImpresion) areaImpresion.classList.add('d-none');
    }

    function mostrarTodos() {
        inputTipo.value = '';
        zonaTarjetas.classList.remove('modo-seleccion');
        columnas.forEach(col => col.classList.remove('d-none'));
        tarjetas.forEach(t => t.setAttribute('aria-pressed', 'false'));
        barraVolver.classList.add('d-none');
        panelFiltros.classList.add('d-none');
        if (panelAsistencia) panelAsistencia.classList.add('d-none');
        btnGenerar.disabled = true;
    }

    function aplicarTipo(tipo, inicial) {
        const c = config[tipo];

        if (!c) {
            mostrarTodos();
            return;
        }

        inputTipo.value = tipo;

        zonaTarjetas.classList.add('modo-seleccion');
        columnas.forEach(col => col.classList.toggle('d-none', col.dataset.tipo !== tipo));
        tarjetas.forEach(t => t.setAttribute('aria-pressed', t.dataset.tipo === tipo ? 'true' : 'false'));
        barraVolver.classList.remove('d-none');

        panelFiltros.classList.remove('d-none');
        if (panelAsistencia) panelAsistencia.classList.toggle('d-none', !c.panel);
        btnGenerar.disabled = false;
        ayuda.textContent = c.ayuda;

        bloquesFechas.forEach(b => b.classList.toggle('d-none', !c.fechas));
        if (!c.fechas) {
            document.getElementById('rangoInicio').value = '';
            document.getElementById('rangoFin').value = '';
        }

        const previo = inicial ? selEstado.dataset.seleccionado : '';
        selEstado.innerHTML = '<option value="">Todos</option>';
        if (c.estados) {
            c.estados.forEach(([valor, texto]) => {
                const op = document.createElement('option');
                op.value = valor;
                op.textContent = texto;
                if (valor === previo) op.selected = true;
                selEstado.appendChild(op);
            });
            bloqueEstado.classList.remove('d-none');
        } else {
            bloqueEstado.classList.add('d-none');
        }

        if (!inicial) ocultarResultado();
    }

        // Copia limpia del reporte para imprimir (independiente del layout)
    function limpiarImpresion() {
        const r = document.getElementById('printRoot');
        if (r) r.remove();
    }

    function prepararImpresion() {
        const origen = document.getElementById('areaImpresion');
        if (!origen || origen.classList.contains('d-none')) return;

        limpiarImpresion();
        const copia = origen.cloneNode(true);
        copia.removeAttribute('id');
        copia.querySelectorAll('[id]').forEach(n => n.removeAttribute('id'));

        const raiz = document.createElement('div');
        raiz.id = 'printRoot';
        raiz.appendChild(copia);
        document.body.appendChild(raiz);
    }

    window.addEventListener('beforeprint', prepararImpresion);
    window.addEventListener('afterprint', limpiarImpresion);

    tarjetas.forEach(t => t.addEventListener('click', () => {
        if (t.getAttribute('aria-pressed') === 'true') return;
        aplicarTipo(t.dataset.tipo, false);
    }));

    btnVolver.addEventListener('click', () => {
        mostrarTodos();
        ocultarResultado();
    });

    aplicarTipo(inputTipo.value, true);

    document.getElementById('formReporte').addEventListener('submit', function (e) {
        const ini = document.getElementById('rangoInicio').value;
        const fin = document.getElementById('rangoFin').value;
        if (ini && fin && ini > fin) {
            e.preventDefault();
            alert('La fecha "Desde" no puede ser posterior a la fecha "Hasta".');
        }
    });

    <?php if (!empty($autoImprimir)): ?>
    window.addEventListener('load', function () {
        window.print();
        history.replaceState(null, '', location.pathname + '?page=reportes');
    });
    <?php endif; ?>
})();
</script>

<?php if ($toast !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.mostrarNotificacion(
        <?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    );
});
</script>
<?php endif; ?>