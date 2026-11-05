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
        ?>

        <!-- Área que se imprime -->
        <div id="areaImpresion">

            <!-- Encabezado para impresión física/PDF -->
            <div class="rep-impresion-encabezado">
                <h2><?= rep_e($info['nombre_negocio'] ?? 'GYMSYS') ?></h2>
                <p>
                    <?= rep_e($info['direccion'] ?? 'Dirección no disponible') ?>
                    <?= !empty($info['telefono']) ? ' · Tel: ' . rep_e($info['telefono']) : '' ?>
                    <?= !empty($info['correo']) ? ' · ' . rep_e($info['correo']) : 'gymsys@gmail.com' ?>
                </p>
                <p><strong>Reporte oficial de <?= rep_e(mb_strtolower($titulo)) ?></strong></p>
                <p>
                    Generado el <?= date('d/m/Y H:i') ?> por <?= rep_e($_SESSION['nombre_usuario'] ?? ($_SESSION['nombre'] ?? 'usuario')) ?>
                    <?php if ($usaFechas && ($filtros['rango_inicio'] !== '' || $filtros['rango_fin'] !== '')): ?>
                        · Periodo: <?= rep_fecha($filtros['rango_inicio'] ?: null) ?> al <?= rep_fecha($filtros['rango_fin'] ?: null) ?>
                    <?php endif; ?>
                    <?php if (($filtros['estado'] ?? '') !== ''): ?>
                        · Filtro de estado: <?= rep_e($filtros['estado']) ?>
                    <?php endif; ?>
                </p>
            </div>

            <section id="zonaResultado" class="rep-resultado mb-4" aria-label="Resultado del reporte">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom no-imprimir">
                    <div>
                        <h3 class="fw-bold fs-5 text-dark m-0">Resultado del Reporte de <?= rep_e($titulo) ?></h3>
                        <div class="rep-meta"><?= $total ?> <?= $total === 1 ? 'registro encontrado' : 'registros encontrados' ?></div>
                    </div>
                    <?php if ($total > 0): ?>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Imprimir o Guardar PDF
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($total === 0): ?>
                    <div class="alert alert-warning py-3 text-center my-3" style="background-color: var(--gym-champagne-light); border-color: #E8D5AC; color: var(--gym-champagne-dark);">
                        <i class="bi bi-info-circle me-1"></i> No se encontraron registros con los filtros seleccionados. Prueba con otro rango de fechas o limpia el estado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive rounded-3 border">
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
                                                <td class="num text-dark fw-bold">C$ <?= number_format((float) $valor, 2) ?></td>

                                            <?php elseif (in_array($col, $columnasFecha, true)): ?>
                                                <td class="text-muted"><?= rep_fecha($valor) ?></td>

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

            <!-- Pie solo para impresión -->
            <div class="rep-impresion-pie">
                Total de registros: <?= $total ?>
            </div>

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
