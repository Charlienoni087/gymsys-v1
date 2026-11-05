<?php
/* ==========================================================================
   CONTROLADOR DEL DASHBOARD — GYMSYS
   Archivo: Controllers/DashboardController.php
   Controlador MVC para proveer datos reales de base de datos a la vista
   ========================================================================== */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../Models/dashboard.php';

$dashboardModel = new DashboardModel($conexion);

// 1. Obtener métricas reales de la base de datos
$totalMiembros        = $dashboardModel->obtenerMiembrosActivos();
$asistenciasHoy       = $dashboardModel->obtenerAsistenciasHoy();
$ingresosMes          = $dashboardModel->obtenerIngresosMes();
$membresiasPorVencer  = $dashboardModel->obtenerTotalMembresiasPorVencer();

// 2. Formatear tarjetas KPI
$stats = [
    [
        'icono'     => 'bi-people',
        'valor'     => (string) $totalMiembros,
        'etiqueta'  => 'Miembros activos',
        'trend'     => 'En sistema',
        'destacada' => true
    ],
    [
        'icono'     => 'bi-calendar-check',
        'valor'     => (string) $asistenciasHoy,
        'etiqueta'  => 'Asistencias hoy',
        'trend'     => 'Registro diario',
        'destacada' => false
    ],
    [
        'icono'     => 'bi-cash-stack',
        'valor'     => 'C$' . number_format($ingresosMes, 2, '.', ','),
        'etiqueta'  => 'Ingresos del mes',
        'trend'     => date('F Y'),
        'destacada' => false
    ],
    [
        'icono'     => 'bi-hourglass-split',
        'valor'     => (string) $membresiasPorVencer,
        'etiqueta'  => 'Membresías por vencer',
        'trend'     => 'Próximos 7 días',
        'destacada' => false
    ],
];

// 3. Asistencias de la semana actual
$asistenciaSemana = $dashboardModel->obtenerAsistenciasSemana();
$maxAsistencia    = max($asistenciaSemana) ?: 1;
$diaPico          = array_search(max($asistenciaSemana), $asistenciaSemana);
$nombresDiasCompletos = [
    'Lun' => 'Lunes',
    'Mar' => 'Martes',
    'Mié' => 'Miércoles',
    'Jue' => 'Jueves',
    'Vie' => 'Viernes',
    'Sáb' => 'Sábado',
    'Dom' => 'Domingo'
];
$textoDiaPico = $nombresDiasCompletos[$diaPico] ?? $diaPico;

// 4. Listas detalladas
$porVencer       = $dashboardModel->obtenerMembresiasPorVencerDetallado(5);
$ultimosMiembros = $dashboardModel->obtenerUltimosMiembros(5);

// 5. Mapeo de estados
$estados = [
    'activo'    => ['Activo',     'estado-activo'],
    'inactivo'  => ['Inactivo',   'estado-vencido'],
    'porvencer' => ['Por vencer', 'estado-porvencer'],
    'vencido'   => ['Vencido',    'estado-vencido'],
];

// Cargar la vista limpia sin consultas SQL internas
require __DIR__ . '/../Views/Dashboard.php';

