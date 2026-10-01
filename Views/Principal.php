<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: /GYMSYS/index.php");
    exit;
}

//$nombreUsuario = $_SESSION['nombre_usuario'] ?? '';
unset($_SESSION['login_exitoso']);
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';


$permisos = [
    'administrador' => ['dashboard', 'clientes', 'membresias', 'productos', 'reportes', 'usuarios', 'facturacion', 'pagos'],
    'recepcionista' => ['dashboard', 'clientes'],
    'entrenador' => ['dashboard', 'clientes'],
];

$rol = $_SESSION['rol'];
$modulosPermitidos = $permisos[$rol] ?? [];


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMSYS - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/menu.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
</head>

<body>

    <div class="d-flex flex-column" id="sidebarMenu">
        <div class="px-4 mb-4 text-white">
            <img src="../assets/img/logo.jpeg" alt="Logo de GYMSYS" class="logo">
        </div>

        <div class="nav flex-column w-100" id="nav">
            <?php if (in_array('dashboard', $modulosPermitidos)): ?>
                <a href="Principal.php?page=dashboard" class="btn-nav <?= $page == 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill me-3 fs-5" style="color: #fff600"></i> <span>Dashboard</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('clientes', $modulosPermitidos)): ?>
                <a href="Principal.php?page=clientes" class="btn-nav <?= $page == 'clientes' ? 'active' : '' ?>">
                    <i class="bi bi-person me-3 fs-5" style="color: #fff600;"></i> <span>Clientes</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('membresias', $modulosPermitidos)): ?>
                <a href="Principal.php?page=membresias" class="btn-nav <?= $page == 'membresias' ? 'active' : '' ?>">
                    <i class="bi bi-wallet me-3 fs-5" style="color: #fff600;"></i> <span>Membresías</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('productos', $modulosPermitidos)): ?>
                <a href="Principal.php?page=productos" class="btn-nav <?= $page == 'productos' ? 'active' : '' ?>">
                    <i class="bi bi-basket me-3 fs-5" style="color: #fff600;"></i> <span>Productos</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('facturacion', $modulosPermitidos)): ?>
                <a href="Principal.php?page=facturacion" class="btn-nav <?= $page == 'facturacion' ? 'active' : '' ?>">
                    <i class="bi bi-receipt me-3 fs-5" style="color: #fff600;"></i> <span>Facturación</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('reportes', $modulosPermitidos)): ?>
                <a href="Principal.php?page=reportes" class="btn-nav <?= $page == 'reportes' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text me-3 fs-5" style="color: #fff600;"></i> <span>Reportes</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('pagos', $modulosPermitidos)): ?>
                <a href="Principal.php?page=pagos" class="btn-nav <?= $page == 'pagos' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text me-3 fs-5" style="color: #fff600;"></i> <span>Pagos</span>
                </a>
            <?php endif; ?>

            <?php if (in_array('usuarios', $modulosPermitidos)): ?>
                <a href="Principal.php?page=usuarios" class="btn-nav <?= $page == 'usuarios' ? 'active' : '' ?>">
                    <i class="bi bi-people me-3 fs-5" style="color: #fff600;"></i> <span>Usuarios</span>
                </a>
            <?php endif; ?>

            <br>            
            <form id="formLogout" action="../Controllers/LogoutController.php" method="POST" style="display: none;">
            </form>

            <div class="userCard">
                
                <a href="#" class="btn-logout" id="btnLogout" data-bs-toggle="modal" data-bs-target="#modalLogout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-box-arrow-right fs-1 text-danger mb-3"></i>
                    <h5>¿Cerrar sesión?</h5>
                    <p class="text-muted">Tendrás que iniciar sesión de nuevo para continuar.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmarLogout" form="formLogout">Sí, cerrar sesión</button>
                </div>
            </div>
        </div>
    </div>

    <div class="content-frame" id="contentFrame">
        <div id="transicion" class="revelando" aria-hidden="true">
        </div>

        <div class="main-content" id="contenidoModulo">

            <?php
            if (!in_array($page, $modulosPermitidos)) {
                echo "<h2>Acceso Denegado</h2>";
                echo "<p>No tienes permisos para acceder a este módulo.</p>";
            } else {
                switch ($page) {
                    case 'dashboard':
                        echo "<h2>Dashboard</h2>";
                        include 'Dashboard.php';
                        break;

                    case 'clientes':
                        echo "<h2>Clientes</h2>";
                        require_once __DIR__ . '/../Controllers/ClienteController.php';
                        break;
                    case 'membresias':
                        echo "<h2>Membresías</h2>";
                        //require_once __DIR__ . '/../Controllers/MembresiaController.php';
                        break;

                    case 'productos':
                        echo "<h2>Productos</h2>";
                        //require_once __DIR__ . '/../Controllers/ProductoController.php';
                        break;

                    case 'facturacion':
                        echo "<h2>Facturación</h2>";
                        //require_once __DIR__ . '/../Controllers/FacturacionController.php';
                        break;

                    case 'reportes':
                        echo "<h2>Reportes</h2>";
                        //require_once __DIR__ . '/../Controllers/ReporteController.php';
                        break;

                    case 'pagos':
                        echo "<h2>Pagos</h2>";
                        //require_once __DIR__ . '/../Controllers/PagoController.php';
                        break;

                    case 'usuarios':
                        echo "<h2>Usuarios</h2>";
                        //require_once __DIR__ . '/../Controllers/UsuarioController.php';
                        break;

                    default:
                        if (in_array('dashboard', $modulosPermitidos)) {
                            include 'dashboard.php';
                        } else {
                            echo "<h2>Acceso Denegado</h2>";
                        }
                        break;
                        }
                        
                        
                        }
                        
                        ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/main.js"></script>
    <script src="../js/principal.js"></script>
</body>
</html>