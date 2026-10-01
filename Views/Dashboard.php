<?php


if (!function_exists('gym_e')) {
    function gym_e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$moneda        = '$';
$nombreUsuario = $_SESSION['nombre'] ?? 'Administrador';



$stats = [
    ['icono' => 'bi-people-fill',         'valor' => '68',                          'etiqueta' => 'Miembros activos',       'destacada' => true],
    ['icono' => 'bi-calendar-check-fill', 'valor' => '23',                           'etiqueta' => 'Asistencias hoy',        'destacada' => false],
    ['icono' => 'bi-cash-coin',           'valor' => 'C$12,450',                      'etiqueta' => 'Ingresos del mes',       'destacada' => false],
    ['icono' => 'bi-hourglass-split',     'valor' => '17',                           'etiqueta' => 'Membresías por vencer',  'destacada' => false],
];

$asistenciaSemana = ['Lun' => 52, 'Mar' => 61, 'Mié' => 48, 'Jue' => 66, 'Vie' => 58, 'Sáb' => 40, 'Dom' => 15];
$maxAsistencia    = max($asistenciaSemana) ?: 1;

$ultimosMiembros = [
    ['nombre' => 'María López',    'plan' => 'Mensual',    'ingreso' => '18/09/2026', 'estado' => 'activo'],
    ['nombre' => 'Carlos Medina',  'plan' => 'Trimestral', 'ingreso' => '17/09/2026', 'estado' => 'activo'],
    ['nombre' => 'Ana Gutiérrez',  'plan' => 'Mensual',    'ingreso' => '15/09/2026', 'estado' => 'porvencer'],
    ['nombre' => 'Luis Ramírez',   'plan' => 'Semanal',    'ingreso' => '12/09/2026', 'estado' => 'vencido'],
    ['nombre' => 'Sofía Herrera',  'plan' => 'Anual',      'ingreso' => '10/09/2026', 'estado' => 'activo'],
];

$porVencer = [
    ['nombre' => 'Ana Gutiérrez',  'plan' => 'Mensual',    'dias' => 1],
    ['nombre' => 'Pedro Castillo', 'plan' => 'Mensual',    'dias' => 2],
    ['nombre' => 'Daniela Ortiz',  'plan' => 'Trimestral', 'dias' => 4],
    ['nombre' => 'José Espinoza',  'plan' => 'Mensual',    'dias' => 6],
];

$estados = [
    'activo'    => ['Activo',     'estado-activo'],
    'porvencer' => ['Por vencer', 'estado-porvencer'],
    'vencido'   => ['Vencido',    'estado-vencido'],
];
?>

<head>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/variables.css">
</head>

<style>
    body {
        background-color: var(--gym-gris);
        color: var(--gym-gris-claro);
    }
</style>

<body>
    
    <div class="dash-head">
        <div>
    
            <p style="color: var(--gym-gris-claro);">Hola, <?= gym_e($nombreUsuario) ?>. Este es el resumen de hoy, <?= date('d/m/Y') ?>.</p>
        </div>
        <a href="Principal.php?page=clientes" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-2"></i>Nuevo cliente
        </a>
    </div>
    
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card <?= $s['destacada'] ? 'destacada' : '' ?>">
                    <div class="stat-icon"><i class="bi <?= gym_e($s['icono']) ?>"></i></div>
                    <div>
                        <div class="stat-value"><?= gym_e($s['valor']) ?></div>
                        <div class="stat-label"><?= gym_e($s['etiqueta']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
            <div class="dash-panel">
                <div class="dash-panel-title">
                    <h3>Asistencias de la semana</h3>
                </div>
                <div class="bars" role="img" aria-label="Asistencias por día de la semana">
                    <?php foreach ($asistenciaSemana as $dia => $total):
                        $alto = round(($total / $maxAsistencia) * 100); ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= (int) $total ?></span>
                            <div class="bar-track">
                                <div class="bar <?= $total === $maxAsistencia ? 'pico' : '' ?>" style="height: <?= $alto ?>%"></div>
                            </div>
                            <span class="bar-day"><?= gym_e($dia) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    
        <div class="col-12 col-lg-5">
            <div class="dash-panel">
                <div class="dash-panel-title">
                    <h3>Membresías por vencer</h3>
                    <a href="Principal.php?page=clientes">Ver todas</a>
                </div>
                <?php foreach ($porVencer as $p): ?>
                    <div class="venc-item">
                        <div>
                            <div class="venc-nombre"><?= gym_e($p['nombre']) ?></div>
                            <div class="venc-plan">Plan <?= gym_e(strtolower($p['plan'])) ?></div>
                        </div>
                        <span class="venc-dias <?= $p['dias'] <= 2 ? 'urgente' : '' ?>">
                            <?= (int) $p['dias'] ?> <?= $p['dias'] === 1 ? 'día' : 'días' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="dash-panel">
        <div class="dash-panel-title">
            <h3>Últimos miembros registrados</h3>
            <a href="Principal.php?page=clientes">Ver clientes</a>
        </div>
        <div class="dash-table-wrap table-responsive">
            <table class="table dash-table align-middle">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Ingreso</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosMiembros as $m):
                        [$textoEstado, $claseEstado] = $estados[$m['estado']] ?? $estados['activo']; ?>
                        <tr>
                            <td class="fw-semibold"><?= gym_e($m['nombre']) ?></td>
                            <td><?= gym_e($m['plan']) ?></td>
                            <td><?= gym_e($m['ingreso']) ?></td>
                            <td><span class="estado <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>