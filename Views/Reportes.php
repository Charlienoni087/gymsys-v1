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
    'membresias' => ['titulo' => 'Membresías', 'detalle' => 'Costos, fechas y vencimientos.',  'icono' => 'bi-card-checklist'],
    'clientes'   => ['titulo' => 'Clientes',   'detalle' => 'Registrados y por estado.',         'icono' => 'bi-people'],
    'inventario' => ['titulo' => 'Inventario', 'detalle' => 'Productos y existencias.',          'icono' => 'bi-box-seam'],
    'asistencia' => ['titulo' => 'Asistencia', 'detalle' => 'Entradas y salidas de socios.',     'icono' => 'bi-clock-history'],
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


<div class="reportes-container my-3">

    <!-- Encabezado del módulo -->
    <div class="rep-encabezado no-imprimir">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <h2 class="fs-4 fw-bold">Centro de Reportes</h2>
        <p class="mb-0 small">Genera reportes de membresías, asistencia, productos e historial de clientes.</p>
    </div>

    <!-- Volver a ver todas las tarjetas -->
    <div id="barraVolver" class="mb-3 d-none no-imprimir">
        <button type="button" id="btnVolver" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Ver todos los reportes
        </button>
    </div>

    <!-- Botones/Tarjetas de tipo de reporte -->
    <div id="zonaTarjetas" class="row g-3 mb-4 no-imprimir" role="group" aria-label="Tipo de reporte">
        <?php foreach ($tiposReporte as $clave => $t): ?>
            <div class="col-12 col-md-6 col-lg-3 col-tarjeta" data-tipo="<?= rep_e($clave) ?>">
                <button type="button"
                        class="rep-tarjeta"
                        data-tipo="<?= rep_e($clave) ?>"
                        aria-pressed="<?= $tipoSeleccionado === $clave ? 'true' : 'false' ?>">
                    <div>
                        <i class="bi <?= rep_e($t['icono']) ?>"></i>
                        <strong><?= rep_e($t['titulo']) ?></strong>
                        <span><?= rep_e($t['detalle']) ?></span>
                    </div>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Registro de asistencia (panel extensible) -->
    <?php require __DIR__ . '/Asistencias.php'; ?>

    <!-- Filtros y generación del reporte -->
    <form method="POST" action="Principal.php?page=reportes" id="formReporte" class="no-imprimir">
        <input type="hidden" name="tipo_reporte" id="tipoReporte" value="<?= rep_e($tipoSeleccionado) ?>">

        <div id="panelFiltros" class="rep-panel mb-4 d-none">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-funnel text-success fs-5"></i>
                <h4 class="h6 fw-bold text-dark m-0">Parámetros del Reporte</h4>
            </div>
            <p class="text-muted small mb-3" id="ayudaFiltros"></p>

            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3" data-filtro="fechas">
                    <label for="rangoInicio" class="form-label">Desde</label>
                    <input type="date" class="form-control formInput" id="rangoInicio" name="rango_inicio"
                           value="<?= rep_e($filtros['rango_inicio'] ?? '') ?>">
                </div>
                <div class="col-6 col-md-3" data-filtro="fechas">
                    <label for="rangoFin" class="form-label">Hasta</label>
                    <input type="date" class="form-control formInput" id="rangoFin" name="rango_fin"
                           value="<?= rep_e($filtros['rango_fin'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-3" data-filtro="estado">
                    <label for="estadoReporte" class="form-label">Estado</label>
                    <select class="form-select formInput" id="estadoReporte" name="estado"
                            data-seleccionado="<?= rep_e($filtros['estado'] ?? '') ?>">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" name="generar_reporte" id="btnGenerar"
                            class="btn btn-gym w-100 py-2" disabled>
                        <i class="bi bi-play-fill me-1"></i> Generar reporte
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

        // Métricas ejecutivas y sumatorias para KPIs en pantalla y PDF
        $sumDinero           = 0.0;
        $conteoActivos       = 0;
        $conteoInactivos     = 0;
        $totalStock          = 0;
        $stockCritico        = 0;
        $sociosUnicos        = [];
        $sesionesCompletadas = 0;

        if ($total > 0) {
            foreach ($resultadosReporte as $r) {
                if (isset($r['costo'])) {
                    $sumDinero += (float) $r['costo'];
                } elseif (isset($r['precio_venta'])) {
                    $sumDinero += (float) $r['precio_venta'] * (int) ($r['stock_disponible'] ?? 1);
                }

                if (isset($r['estado'])) {
                    $est = mb_strtolower((string) $r['estado']);
                    if (in_array($est, ['vigente', 'activo', 'activa'], true)) {
                        $conteoActivos++;
                    } else {
                        $conteoInactivos++;
                    }
                }

                if (isset($r['stock_disponible'])) {
                    $stk = (int) $r['stock_disponible'];
                    $totalStock += $stk;
                    if ($stk <= 5) {
                        $stockCritico++;
                    }
                }

                if (isset($r['nombre_cliente'])) {
                    $sociosUnicos[$r['nombre_cliente']] = true;
                }

                if (isset($r['hora_salida']) && !empty($r['hora_salida']) && $r['hora_salida'] !== '—') {
                    $sesionesCompletadas++;
                }
            }
        }
        $numSociosUnicos = count($sociosUnicos);

        // Configuración de tarjetas KPI según el tipo de reporte
        $kpiItems = [
            [
                'icono' => 'bi-collection',
                'label' => 'Total Registros',
                'valor' => number_format($total),
                'sub'   => 'Filas procesadas'
            ]
        ];

        if ($tipoGenerado === 'membresias') {
            $kpiItems[] = [
                'icono' => 'bi-cash-stack',
                'label' => 'Recaudación Total',
                'valor' => 'C$ ' . number_format($sumDinero, 2),
                'sub'   => 'Importe de registros'
            ];
            $kpiItems[] = [
                'icono' => 'bi-check-circle',
                'label' => 'Vigentes / Vencidas',
                'valor' => $conteoActivos . ' / ' . $conteoInactivos,
                'sub'   => $total > 0 ? (round(($conteoActivos / $total) * 100) . '% de vigencia') : '0%'
            ];
        } elseif ($tipoGenerado === 'clientes') {
            $kpiItems[] = [
                'icono' => 'bi-person-check',
                'label' => 'Clientes Activos',
                'valor' => number_format($conteoActivos),
                'sub'   => $total > 0 ? (round(($conteoActivos / $total) * 100) . '% del padrón') : '0%'
            ];
            $kpiItems[] = [
                'icono' => 'bi-person-x',
                'label' => 'Clientes Inactivos',
                'valor' => number_format($conteoInactivos),
                'sub'   => 'Cuentas en pausa'
            ];
        } elseif ($tipoGenerado === 'inventario') {
            $kpiItems[] = [
                'icono' => 'bi-boxes',
                'label' => 'Total Unidades',
                'valor' => number_format($totalStock) . ' uds.',
                'sub'   => 'Existencias en almacén'
            ];
            $kpiItems[] = [
                'icono' => 'bi-exclamation-triangle',
                'label' => 'Stock Crítico',
                'valor' => number_format($stockCritico),
                'sub'   => $stockCritico > 0 ? ($stockCritico . ' productos ≤ 5 uds.') : 'Inventario óptimo'
            ];
        } elseif ($tipoGenerado === 'asistencia') {
            $kpiItems[] = [
                'icono' => 'bi-people',
                'label' => 'Socios Únicos',
                'valor' => number_format($numSociosUnicos),
                'sub'   => 'Asistentes distintos'
            ];
            $kpiItems[] = [
                'icono' => 'bi-clock',
                'label' => 'Salidas Marcadas',
                'valor' => number_format($sesionesCompletadas),
                'sub'   => ($total - $sesionesCompletadas) . ' en sala'
            ];
        }

        $filtroTexto = !empty($filtros['estado']) ? ucfirst($filtros['estado']) : 'Todos';
        $periodoTexto = ($usaFechas && ($filtros['rango_inicio'] !== '' || $filtros['rango_fin'] !== ''))
            ? (rep_fecha($filtros['rango_inicio'] ?: null) . ' al ' . rep_fecha($filtros['rango_fin'] ?: null))
            : 'Historial completo';

        $kpiItems[] = [
            'icono' => 'bi-funnel',
            'label' => 'Filtro / Periodo',
            'valor' => $filtroTexto,
            'sub'   => $periodoTexto
        ];

        $folioRpt    = 'RPT-' . strtoupper(substr($tipoGenerado, 0, 3)) . '-' . date('Ymd-His');
        $operadorRpt = $_SESSION['nombre_usuario'] ?? ($_SESSION['nombre'] ?? 'Administrador');
        $rolRpt      = $_SESSION['rol'] ?? 'Administrador';
        ?>

        <!-- Área que se imprime -->
        <div id="areaImpresion">

            <!-- Encabezado corporativo institucional (Solo para impresión física / PDF) -->
            <header class="rep-pdf-header">
                <div class="rep-pdf-brand-col">
                    <div class="rep-pdf-logo-wrap">
                        <img src="../assets/img/logo.jpeg" alt="Logo GYMSYS" class="rep-pdf-logo-img">
                    </div>
                    <div class="rep-pdf-brand-text">
                        <h1 class="rep-pdf-gym-title"><?= rep_e($info['nombre_negocio'] ?? 'GYMSYS CLUB & FITNESS') ?></h1>
                        <span class="rep-pdf-gym-sub">Centro Deportivo & Plataforma Integral de Entrenamiento</span>
                        <div class="rep-pdf-gym-contacts">
                            <span><i class="bi bi-geo-alt-fill"></i> <?= rep_e($info['direccion'] ?? 'Sede Central') ?></span>
                            <?php if (!empty($info['telefono'])): ?>
                                <span> · <i class="bi bi-telephone-fill"></i> <?= rep_e($info['telefono']) ?></span>
                            <?php endif; ?>
                            <span> · <i class="bi bi-envelope-fill"></i> <?= rep_e($info['correo'] ?? 'contacto@gymsys.com') ?></span>
                        </div>
                    </div>
                </div>

                <div class="rep-pdf-meta-col">
                    <div class="rep-pdf-meta-badge">DOCUMENTO OFICIAL AUDITABLE</div>
                    <table class="rep-pdf-meta-table">
                        <tr>
                            <th>Folio:</th>
                            <td><?= rep_e($folioRpt) ?></td>
                        </tr>
                        <tr>
                            <th>Emisión:</th>
                            <td><?= date('d/m/Y h:i A') ?></td>
                        </tr>
                        <tr>
                            <th>Operador:</th>
                            <td><?= rep_e($operadorRpt) ?> <span class="rep-pdf-meta-role">(<?= rep_e(ucfirst($rolRpt)) ?>)</span></td>
                        </tr>
                        <tr>
                            <th>Periodo:</th>
                            <td><?= rep_e($periodoTexto) ?></td>
                        </tr>
                    </table>
                </div>
            </header>

            <div class="rep-pdf-divider"></div>

            <div class="rep-pdf-title-banner">
                <div class="rep-pdf-title-left">
                    <h2 class="rep-pdf-main-title">REPORTE OFICIAL DE <?= mb_strtoupper($titulo, 'UTF-8') ?></h2>
                    <span class="rep-pdf-main-subtitle">Documento de control administrativo generado a partir de registros del sistema.</span>
                </div>
                <div class="rep-pdf-title-right">
                    <span class="rep-pdf-badge-count"><?= $total ?> <?= $total === 1 ? 'REGISTRO' : 'REGISTROS' ?></span>
                </div>
            </div>

            <!-- Resumen Ejecutivo de Métricas / KPIs (Visible en Pantalla y en Impresión) -->
            <div class="rep-kpi-grid mb-4">
                <?php foreach ($kpiItems as $kpi): ?>
                    <div class="rep-kpi-card">
                        <div class="rep-kpi-icon">
                            <i class="bi <?= rep_e($kpi['icono']) ?>"></i>
                        </div>
                        <div class="rep-kpi-body">
                            <span class="rep-kpi-label"><?= rep_e($kpi['label']) ?></span>
                            <strong class="rep-kpi-value"><?= rep_e($kpi['valor']) ?></strong>
                            <span class="rep-kpi-sub"><?= rep_e($kpi['sub']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <section id="zonaResultado" class="rep-resultado mb-4" aria-label="Resultado del reporte">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom no-imprimir">
                    <div>
                        <h3 class="fw-bold fs-5 m-0" style="color: var(--gym-text-main);">Detalle de Registros</h3>
                        <div class="rep-meta"><?= $total ?> <?= $total === 1 ? 'registro encontrado' : 'registros encontrados' ?> para este reporte</div>
                    </div>
                    <?php if ($total > 0): ?>
                        <button type="button" class="btn btn-gym btn-sm" onclick="imprimirReporte()">
                            <i class="bi bi-printer-fill me-1"></i> Imprimir o Guardar PDF
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($total === 0): ?>
                    <div class="alert alert-warning py-3 text-center my-3" style="background-color: var(--gym-champagne-light); border-color: #E8D5AC; color: var(--gym-champagne-dark);">
                        <i class="bi bi-info-circle me-1"></i> No se encontraron registros con los filtros seleccionados. Prueba con otro rango de fechas o limpia el estado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive rounded-3 border rep-table-container">
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
                                                <td class="num fw-bold">C$ <?= number_format((float) $valor, 2) ?></td>

                                            <?php elseif (in_array($col, $columnasFecha, true)): ?>
                                                <td class="text-muted"><?= rep_fecha($valor) ?></td>

                                            <?php elseif ($col === 'estado'): ?>
                                                <?php $ok = in_array(mb_strtolower((string)$valor), ['vigente', 'activo', 'activa'], true); ?>
                                                <td><span class="rep-chip <?= $ok ? 'rep-chip-verde' : 'rep-chip-rojo' ?>"><?= rep_e(ucfirst((string)$valor)) ?></span></td>

                                            <?php elseif ($col === 'stock_disponible'): ?>
                                                <td class="num">
                                                    <?= (int) $valor ?>
                                                    <?php if ((int) $valor <= 5): ?>
                                                        <span class="rep-chip rep-chip-rojo ms-1">Bajo</span>
                                                    <?php endif; ?>
                                                </td>

                                            <?php elseif ($col === 'hora_salida'): ?>
                                                <td class="font-monospace"><?= $valor ? rep_e(substr($valor, 0, 5)) : '—' ?></td>

                                            <?php elseif ($col === 'hora_entrada'): ?>
                                                <td class="font-monospace fw-semibold"><?= rep_e(substr((string) $valor, 0, 5)) ?></td>

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

            <!-- Pie de página institucional y firmas (Solo para impresión / PDF) -->
            <footer class="rep-pdf-footer">
                <div class="rep-pdf-signatures">
                    <div class="rep-pdf-sig-box">
                        <div class="rep-pdf-sig-line"></div>
                        <strong class="rep-pdf-sig-name"><?= rep_e($operadorRpt) ?></strong>
                        <span class="rep-pdf-sig-role">Generado y Validado por</span>
                        <span class="rep-pdf-sig-detail"><?= rep_e(ucfirst($rolRpt)) ?> · GYMSYS</span>
                    </div>
                    <div class="rep-pdf-sig-box">
                        <div class="rep-pdf-sig-line"></div>
                        <strong class="rep-pdf-sig-name"><?= rep_e($info['nombre_negocio'] ?? 'Dirección General GYMSYS') ?></strong>
                        <span class="rep-pdf-sig-role">Firma Autorizada y Sello</span>
                        <span class="rep-pdf-sig-detail">Control Administrativo & Finanzas</span>
                    </div>
                </div>

                <div class="rep-pdf-legal-bar">
                    <div class="rep-pdf-legal-left">
                        <i class="bi bi-shield-check"></i>
                        <span>Documento emitido por el sistema <strong>GYMSYS Club Management</strong>. La información contenida es confidencial y para uso exclusivo administrativo.</span>
                    </div>
                    <div class="rep-pdf-legal-right">
                        <span>Folio <?= rep_e($folioRpt) ?></span>
                    </div>
                </div>
            </footer>

        </div>
        <!-- /areaImpresion -->
    <?php endif; ?>

    <!-- Historial de reportes -->
    <section class="no-imprimir mt-4" aria-label="Historial de reportes">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-bold fs-5 mb-1 text-dark">Historial de Reportes Generados</h4>
                <p class="text-muted small mb-0">Listado de los últimos reportes solicitados en el sistema.</p>
            </div>
        </div>

        <?php if (empty($listaReportes)): ?>
            <div class="rep-panel text-muted text-center py-4">
                <i class="bi bi-clock-history fs-3 d-block mb-2 text-muted"></i>
                Aún no se ha generado ningún reporte en el historial. Selecciona un tipo arriba para comenzar.
            </div>
        <?php else: ?>
            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle text-center mb-0 tabla-historial">
                    <thead>
                        <tr>
                            <th class="text-start ps-3">Fecha y Hora</th>
                            <th>Tipo de Reporte</th>
                            <th>Rango Seleccionado</th>
                            <th>Generado Por</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaReportes as $r): ?>
                            <tr>
                                <td class="text-start ps-3 text-muted font-monospace"><?= rep_fecha_hora($r['fecha_generacion']) ?></td>
                                <td class="fw-semibold text-dark">
                                    <span class="badge" style="background-color: var(--gym-sage-light); color: var(--gym-text-main); border: 1px solid var(--gym-sage);">
                                        <?= rep_e($tiposReporte[$r['tipo_reporte']]['titulo'] ?? $r['tipo_reporte']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php if ($r['rango_inicio'] || $r['rango_fin']): ?>
                                        <?= rep_fecha($r['rango_inicio']) ?> al <?= rep_fecha($r['rango_fin']) ?>
                                    <?php else: ?>
                                        <span class="text-secondary italic">Histórico completo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= rep_e($r['nombre_usuario']) ?></td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="Principal.php?page=reportes&reimprimir=<?= (int) $r['id_reporte'] ?>"
                                           class="btn btn-sm btn-outline-secondary"
                                           style="border-radius: 6px; font-size: 0.8rem;"
                                           title="Reimprimir reporte"
                                           aria-label="Imprimir reporte">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                        <?php if ($esAdmin): ?>
                                            <form method="POST" action="Principal.php?page=reportes" class="d-inline"
                                                  onsubmit="return confirm('¿Deseas eliminar este registro del historial?');">
                                                <input type="hidden" name="id_reportes" value="<?= (int) $r['id_reporte'] ?>">
                                                <button type="submit" name="eliminar_reporte"
                                                        class="btn btn-sm btn-outline-danger"
                                                        style="border-radius: 6px; font-size: 0.8rem;"
                                                        aria-label="Eliminar registro">
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
            ayuda: 'Las fechas son opcionales: déjalas vacías para consultar todo el historial.'
        },
        clientes: {
            fechas: true,
            estados: [['activo', 'Activos'], ['inactivo', 'Inactivos']],
            ayuda: 'Filtra socios por fecha de alta o por su estado actual.'
        },
        inventario: {
            fechas: false,
            estados: null,
            ayuda: 'Este reporte muestra las existencias actuales de inventario y precios de venta.'
        },
        asistencia: {
            fechas: true,
            estados: null,
            panel: true,
            ayuda: 'Registra asistencias inmediatas o genera el reporte detallado por rango de fechas.'
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

    function limpiarImpresion() {
        const r = document.getElementById('printRoot');
        if (r) r.remove();
        document.body.classList.remove('rep-imprimiendo');
    }

    function prepararImpresion() {
        if (document.getElementById('printRoot')) return;
        const origen = document.getElementById('areaImpresion');
        if (!origen || origen.classList.contains('d-none')) return;

        limpiarImpresion();
        document.body.classList.add('rep-imprimiendo');
        const copia = origen.cloneNode(true);
        copia.removeAttribute('id');
        copia.querySelectorAll('[id]').forEach(n => n.removeAttribute('id'));

        const raiz = document.createElement('div');
        raiz.id = 'printRoot';
        raiz.appendChild(copia);
        document.body.appendChild(raiz);
    }

    window.imprimirReporte = function () {
        prepararImpresion();
        window.print();
    };

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
        if (typeof window.imprimirReporte === 'function') {
            window.imprimirReporte();
        } else {
            window.print();
        }
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
