<?php
/* ==========================================================================
   VISTA DEL DASHBOARD — GYMSYS (ARQUITECTURA MVC)
   Presenta datos reales obtenidos a través de DashboardController
   Sin consultas directas a base de datos en la vista.
   ========================================================================== */

if (!function_exists('gym_e')) {
    function gym_e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

// Si la vista se incluye sin el controlador, delegar la ejecución a DashboardController
if (!isset($stats) || !isset($asistenciaSemana)) {
    require_once __DIR__ . '/../Controllers/DashboardController.php';
    return;
}

$nombreUsuario = $_SESSION['nombre_usuario'] ?? ($_SESSION['nombre'] ?? 'Administrador');
$maxAsistencia = !empty($asistenciaSemana) ? (max($asistenciaSemana) ?: 1) : 1;
?>

<section class="dashboard-wrapper">
    <!-- Encabezado con Saludo y Acción Rápida -->
    <div class="dash-head">
        <div>
            <h2 class="dash-greeting-title">Bienvenido, <?= gym_e($nombreUsuario) ?></h2>
            <p class="dash-greeting-sub">Resumen de operaciones y actividad de hoy, <?= date('d/m/Y') ?>.</p>
        </div>
        <a href="Principal.php?page=clientes" class="dash-btn-nuevo">
            <i class="bi bi-person-plus-fill"></i>
            <span>Nuevo cliente</span>
        </a>
    </div>

    <!-- Tarjetas de Indicadores Principales (KPIs) con Datos Reales -->
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card <?= !empty($s['destacada']) ? 'destacada' : '' ?>">
                    <div class="stat-card-top">
                        <div class="stat-icon">
                            <i class="bi <?= gym_e($s['icono']) ?>"></i>
                        </div>
                        <?php if (!empty($s['trend'])): ?>
                            <span class="stat-trend-badge"><?= gym_e($s['trend']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="stat-value"><?= gym_e($s['valor']) ?></div>
                        <div class="stat-label"><?= gym_e($s['etiqueta']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Fila Gráfica de Asistencias + Membresías por Vencer -->
    <div class="row g-3 mb-4">
        <!-- Gráfica de Asistencias Semanales -->
        <div class="col-12 col-lg-7">
            <div class="dash-panel">
                <div class="dash-panel-title">
                    <h3><i class="bi bi-bar-chart-line"></i> Asistencias de la semana</h3>
                    <span class="text-muted small fw-semibold">Pico: <?= gym_e($textoDiaPico ?? 'Lunes') ?></span>
                </div>
                <div class="bars" role="img" aria-label="Asistencias por día de la semana">
                    <?php foreach ($asistenciaSemana as $dia => $total):
                        $totalVal = (int) $total;
                        $alto = $maxAsistencia > 0 ? round(($totalVal / $maxAsistencia) * 100) : 0;
                        // Asegurar una altura mínima visual si hay registros
                        if ($totalVal > 0 && $alto < 10) $alto = 10;
                    ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $totalVal ?></span>
                            <div class="bar-track">
                                <div class="bar <?= ($totalVal > 0 && $totalVal === $maxAsistencia) ? 'pico' : '' ?>" style="height: <?= $alto ?>%"></div>
                            </div>
                            <span class="bar-day"><?= gym_e($dia) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Panel de Membresías por Vencer (Sin iniciales) -->
        <div class="col-12 col-lg-5">
            <div class="dash-panel">
                <div class="dash-panel-title">
                    <h3><i class="bi bi-clock-history"></i> Membresías por vencer</h3>
                    <a href="Principal.php?page=membresias">Ver todas</a>
                </div>
                <div class="venc-list">
                    <?php if (!empty($porVencer)): ?>
                        <?php foreach ($porVencer as $p): 
                            $nombreCliente = $p['nombre_cliente'] ?? ($p['nombre'] ?? 'Cliente');
                            $planCliente   = $p['plan'] ?? 'General';
                            $diasRestantes = isset($p['dias_restantes']) ? (int) $p['dias_restantes'] : (isset($p['dias']) ? (int) $p['dias'] : 0);
                        ?>
                            <div class="venc-item">
                                <div class="venc-info">
                                    <div>
                                        <div class="venc-nombre"><?= gym_e($nombreCliente) ?></div>
                                        <div class="venc-plan">Plan <?= gym_e(strtolower($planCliente)) ?></div>
                                    </div>
                                </div>
                                <span class="venc-dias <?= $diasRestantes <= 2 ? 'urgente' : '' ?>">
                                    <?= $diasRestantes ?> <?= $diasRestantes === 1 ? 'día restante' : 'días restantes' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check2-circle fs-3 d-block mb-2" style="color: var(--gym-primary);"></i>
                            <span>No hay membresías próximas a vencer en los próximos 7 días.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Últimos Miembros Registrados (Sin iniciales) -->
    <div class="dash-panel">
        <div class="dash-panel-title">
            <h3><i class="bi bi-person-check"></i> Últimos miembros registrados</h3>
            <a href="Principal.php?page=clientes">Ver clientes</a>
        </div>
        <div class="dash-table-wrap table-responsive">
            <table class="table dash-table align-middle">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Fecha de Ingreso</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ultimosMiembros)): ?>
                        <?php foreach ($ultimosMiembros as $m):
                            $nombreCliente = $m['nombre_cliente'] ?? ($m['nombre'] ?? 'Cliente');
                            $planCliente   = $m['plan'] ?? 'General';
                            $estadoKey     = strtolower(trim($m['estado'] ?? 'activo'));
                            [$textoEstado, $claseEstado] = $estados[$estadoKey] ?? ['Activo', 'estado-activo'];
                            
                            $fechaFormateada = '—';
                            if (!empty($m['fecha_registro'])) {
                                $fechaFormateada = date('d/m/Y', strtotime($m['fecha_registro']));
                            } elseif (!empty($m['ingreso'])) {
                                $fechaFormateada = gym_e($m['ingreso']);
                            }
                        ?>
                            <tr>
                                <td class="fw-semibold text-gym-main">
                                    <span><?= gym_e($nombreCliente) ?></span>
                                </td>
                                <td><?= gym_e($planCliente) ?></td>
                                <td class="text-muted"><?= $fechaFormateada ?></td>
                                <td><span class="estado <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No se registran clientes aún.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
