<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /GYMSYS/index.php");
    exit;
}

$nombreUsuario = $_SESSION['nombre_usuario'] ?? ($_SESSION['nombre'] ?? 'Administrador');
$rolUsuario = strtolower(trim($_SESSION['rol'] ?? 'administrador'));
unset($_SESSION['login_exitoso']);
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$permisos = [
    'administrador' => ['dashboard', 'clientes', 'membresias', 'productos', 'reportes', 'usuarios', 'facturacion', 'pagos', 'asistencias', 'backup'],
    'recepcionista' => ['dashboard', 'clientes', 'membresias', 'productos', 'reportes', 'facturacion', 'pagos', 'backup'],
    'entrenador' => ['dashboard', 'clientes'],
];

$modulosPermitidos = $permisos[$rolUsuario] ?? $permisos['administrador'];

$titulosModulos = [
    'dashboard'   => 'Dashboard General',
    'clientes'    => 'Gestión de Clientes',
    'membresias'  => 'Control de Membresías', 
    'productos'   => 'Inventario y Productos',
    'facturacion' => 'Facturación y Finanzas',
    'reportes'    => 'Reportes del Sistema',
    'usuarios'    => 'Administración de Usuarios',
    'backup'      => 'Descargar Respaldo',
];

$tituloActual = $titulosModulos[$page] ?? 'Panel de Control';

// Cache busters para evitar que el navegador use CSS antiguo en memoria
$cssDir = __DIR__ . '/../assets/css/';
$vVar  = file_exists($cssDir . 'variables.css') ? filemtime($cssDir . 'variables.css') : time();
$vMenu = file_exists($cssDir . 'menu.css') ? filemtime($cssDir . 'menu.css') : time();
$vDash = file_exists($cssDir . 'dashboard.css') ? filemtime($cssDir . 'dashboard.css') : time();
$vNoti = file_exists($cssDir . 'notification.css') ? filemtime($cssDir . 'notification.css') : time();
$vAnim = file_exists($cssDir . 'animations.css') ? filemtime($cssDir . 'animations.css') : time();
$vRep  = file_exists($cssDir . 'reportes.css') ? filemtime($cssDir . 'reportes.css') : time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMSYS - <?= htmlspecialchars($tituloActual, ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Tipografía Editorial / SaaS Premium -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Librerías de Estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos del Sistema Centralizados con Cache-Buster -->
    <link rel="stylesheet" href="../assets/css/variables.css?v=<?= $vVar ?>">
    <link rel="stylesheet" href="../assets/css/menu.css?v=<?= $vMenu ?>">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= $vDash ?>">
    <link rel="stylesheet" href="../assets/css/notification.css?v=<?= $vNoti ?>">
    <link rel="stylesheet" href="../assets/css/animations.css?v=<?= $vAnim ?>">
    <?php if ($page === 'reportes' || $page === 'asistencias'): ?>
        <link rel="stylesheet" href="../assets/css/reportes.css?v=<?= $vRep ?>">
    <?php endif; ?>
    <!-- Anti-flicker pre-render theme application -->
    <script>
        (function() {
            var savedTheme = localStorage.getItem('gymsys_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>

<body>
    <!-- Expandable Dynamic Notch Notification Container -->
    <div id="notificacionApp" class="notificacion-app" role="alert" aria-live="polite">
        <div class="notch-icon-wrapper" id="notificacionIconoWrapper">
            <i id="notificacionIcono" class="bi bi-bell-fill"></i>
        </div>
        <div class="notch-content">
            <span class="notch-tag" id="notificacionTag">GYMSYS</span>
            <span class="notch-text" id="notificacionTexto"></span>
        </div>
        <button type="button" id="notificacionCerrar" class="notificacion-cerrar notch-btn-close" aria-label="Cerrar notificación">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="notch-progress" id="notificacionProgreso"></div>
    </div>

    <!-- Telón para móviles -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <!-- Barra lateral de navegación -->
    <aside class="d-flex flex-column" id="sidebarMenu" aria-label="Menú principal">
        
        <!-- Identidad de Marca -->
        <div class="sidebar-brand-box">
            <img src="../assets/img/logo.jpeg" alt="Logo de GYMSYS" class="brand-logo-img logo" width="46" height="46" style="width: 46px !important; height: 46px !important; max-width: 46px !important; max-height: 46px !important; min-width: 46px !important; min-height: 46px !important; border-radius: 10px !important; object-fit: cover !important; display: block !important; flex-shrink: 0 !important; border: 1.5px solid #D4AF37 !important;">
            <div class="brand-info">
                <span class="brand-name">GYMSYS</span>
                <span class="brand-tagline">Club Management</span>
            </div>
        </div>

        <div class="nav-section-title">Navegación</div>

        <nav class="nav flex-column w-100" id="nav">
            <?php if (in_array('dashboard', $modulosPermitidos)): ?>
                <a href="Principal.php?page=dashboard" class="btn-nav <?= $page == 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('clientes', $modulosPermitidos)): ?>
                <a href="Principal.php?page=clientes" class="btn-nav <?= $page == 'clientes' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> <span>Clientes</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('membresias', $modulosPermitidos)): ?>
                <a href="Principal.php?page=membresias" class="btn-nav <?= $page == 'membresias' ? 'active' : '' ?>">
                    <i class="bi bi-card-checklist"></i> <span>Membresías</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('productos', $modulosPermitidos)): ?>
                <a href="Principal.php?page=productos" class="btn-nav <?= $page == 'productos' ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i> <span>Productos</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('facturacion', $modulosPermitidos)): ?>
                <a href="Principal.php?page=facturacion" class="btn-nav <?= $page == 'facturacion' ? 'active' : '' ?>">
                    <i class="bi bi-receipt-cutoff"></i> <span>Facturación</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('reportes', $modulosPermitidos)): ?>
                <a href="Principal.php?page=reportes" class="btn-nav <?= $page == 'reportes' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Reportes</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('usuarios', $modulosPermitidos)): ?>
                <a href="Principal.php?page=usuarios" class="btn-nav <?= $page == 'usuarios' ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i> <span>Usuarios</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('backup', $modulosPermitidos)): ?>
                <a href="../Controllers/BackupController.php?action=export" class="btn-nav">
                    <i class="bi bi-database-down"></i> <span>Descargar respaldo</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- Tarjeta de Cuenta de Usuario -->
        <div class="userCard">
            <div class="user-profile-badge">
                <div class="user-avatar-circle">
                    <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
                </div>
                <div class="user-details">
                    <span class="user-name-text"><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="user-role-text"><?= htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <form id="formLogout" action="../Controllers/LogoutController.php" method="POST" style="display: none;"></form>

            <a href="#" class="btn-logout" id="btnLogout" data-bs-toggle="modal" data-bs-target="#modalLogout">
                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
            </a>
        </div>
    </aside>

    <!-- Modal de Confirmación de Cierre de Sesión -->
    <div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-3">
                <div class="modal-body py-4">
                    <div class="logout-icon-wrapper mx-auto">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark" id="modalLogoutLabel">¿Deseas cerrar tu sesión?</h5>
                    <p class="text-muted small mb-0">Deberás ingresar tus credenciales nuevamente para acceder al sistema.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0 pb-3 gap-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4" id="confirmarLogout" form="formLogout">
                        <i class="bi bi-check2 me-1"></i> Sí, cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Marco Principal de Contenido -->
    <div class="content-frame" id="contentFrame">
        
        <!-- Barra Superior Administrativa -->
        <header class="gym-topbar">
            <div class="gym-topbar-left">
                <button type="button" class="btn-hamburger" id="btnHamburger" aria-label="Abrir menú de navegación">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h1 class="gym-page-title"><?= htmlspecialchars($tituloActual, ENT_QUOTES, 'UTF-8') ?></h1>
                </div>
            </div>

            <div class="gym-topbar-right">
                <button type="button" class="btn-theme-toggle" id="btnThemeToggle" aria-label="Cambiar tema">
                    <i class="bi bi-sun-fill" id="themeToggleIcon" style="color: #FBBF24;"></i>
                    <span id="themeToggleText">Modo claro</span>
                </button>

                <div class="gym-date-badge d-none d-sm-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    <span><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </header>

        <div id="transicion" class="revelando" aria-hidden="true"></div>

        <main class="main-content" id="contenidoModulo">
            <?php
            if (!in_array($page, $modulosPermitidos)) {
                echo "<div class='gym-card text-center py-5'>";
                echo "<i class='bi bi-shield-x fs-1 text-danger mb-3 d-block'></i>";
                echo "<h3 class='fw-bold text-dark'>Acceso Denegado</h3>";
                echo "<p class='text-muted'>No dispones de los permisos necesarios para acceder a este módulo.</p>";
                echo "<a href='Principal.php?page=dashboard' class='btn btn-primary mt-2'>Volver al Dashboard</a>";
                echo "</div>";
            } else {
                switch ($page) {
                    case 'dashboard':
                        require_once __DIR__ . '/../Controllers/DashboardController.php';
                        break;

                    case 'clientes':
                        require_once __DIR__ . '/../Controllers/ClienteController.php';
                        break;

                    case 'membresias':
                        require_once __DIR__ . '/../Controllers/MembresiaController.php';
                        break;

                    case 'productos':
                        require_once __DIR__ . '/../Controllers/ProductoController.php';
                        break;

                    case 'facturacion':
                        require_once __DIR__ . '/../Controllers/FacturacionController.php';
                        break;

                    case 'reportes':
                        require_once __DIR__ . '/../Controllers/ReporteController.php';
                        break;

                    case 'asistencias':
                        require_once __DIR__ . '/../Controllers/ReporteController.php';
                        break;

                    case 'usuarios':
                        require_once __DIR__ . '/../Controllers/UsuarioController.php';
                        break;

                    case 'backup':
                        require_once __DIR__ . '/../Controllers/BackupController.php';
                        break;

                    default:
                        if (in_array('dashboard', $modulosPermitidos)) {
                            require_once __DIR__ . '/../Controllers/DashboardController.php';
                        } else {
                            echo "<div class='gym-card text-center py-5'>";
                            echo "<h3 class='fw-bold text-dark'>Acceso Denegado</h3>";
                            echo "<p class='text-muted'>No tienes permisos asignados para este módulo.</p>";
                            echo "</div>";
                        }
                        break;
                }
            }
            ?>
        </main>
    </div>

    <!-- Scripts Esenciales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/notifications.js"></script>
    <script src="../js/principal.js"></script>
</body>
</html>
