<?php
session_start();

if (!file_exists(__DIR__ . "/instalador/terminado.lock")) {
    header("Location: instalador/");
    exit();
}

$hayError = isset($_SESSION['login_error']);
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMSYS - Iniciar Sesión</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <?php
    $cssDir = __DIR__ . '/assets/css/';
    $vVar  = file_exists($cssDir . 'variables.css') ? filemtime($cssDir . 'variables.css') : time();
    $vIdx  = file_exists($cssDir . 'index.css') ? filemtime($cssDir . 'index.css') : time();
    $vNoti = file_exists($cssDir . 'notification.css') ? filemtime($cssDir . 'notification.css') : time();
    $vAnim = file_exists($cssDir . 'animations.css') ? filemtime($cssDir . 'animations.css') : time();
    ?>
    <link rel="stylesheet" href="assets/css/variables.css?v=<?= $vVar ?>">
    <link rel="stylesheet" href="assets/css/index.css?v=<?= $vIdx ?>">
    <link rel="stylesheet" href="assets/css/notification.css?v=<?= $vNoti ?>">
    <link rel="stylesheet" href="assets/css/animations.css?v=<?= $vAnim ?>">
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

    <!-- Modal de compatibilidad para evitar errores si algún script antiguo lo busca -->
    <div class="modal-login" id="modalLogin" aria-hidden="true" style="display: none !important;">
        <div class="login-status">
            <div class="status-icon loading" id="statusIcon">
                <span class="spinner"></span>
                <i class="bi bi-check-lg"></i>
                <i class="bi bi-x-lg"></i>
            </div>
            <h3 id="statusTitle">Validando credenciales</h3>
            <p id="statusMessage">Espera un momento...</p>
        </div>
    </div>
    
    <div class="login-split-layout">

        <!-- Columna Izquierda: Identidad de Marca GYMSYS (50% pantalla) -->
        <aside class="split-col-brand d-none d-lg-flex">
            <div class="brand-split-content">
                <div class="marca-logo">
                    <img src="assets/img/logo.jpeg" alt="Logo de GYMSYS" width="52" height="52" style="width: 52px !important; height: 52px !important; max-width: 52px !important; max-height: 52px !important; border-radius: 12px !important; object-fit: cover !important; border: 1.5px solid #D4AF37 !important; display: block !important;">
                    <div class="marca-titular">
                        <span>GYMSYS</span>
                        <small>Club & Sports Management</small>
                    </div>
                </div>

                <div class="marca-texto">

                    <h1>Control integral para tu centro de entrenamiento.</h1>
                </div>

                <div class="marca-footer">
                    <span>&copy; <?= date('Y') ?> GYMSYS Software. Todos los derechos reservados.</span>
                </div>
            </div>

            <div class="split-disco-decor" aria-hidden="true"></div>
            <div class="split-lines-decor" aria-hidden="true"></div>
        </aside>

        <!-- Columna Derecha: Formulario de Iniciar Sesión (50% pantalla) -->
        <main class="split-col-form">
            <div class="form-container-box">

                <div class="d-lg-none mb-4 text-center">
                    <img src="assets/img/logo.jpeg" alt="Logo de GYMSYS" class="logo-movil mb-2" width="54" height="54" style="width: 54px !important; height: 54px !important; max-width: 54px !important; max-height: 54px !important; border-radius: 12px !important; object-fit: cover !important; border: 1.5px solid #D4AF37 !important; display: inline-block !important;">
                    <h3 class="fw-bold text-dark m-0">GYMSYS</h3>
                    <p class="text-muted small">Club & Sports Management</p>
                </div>

                <h2>Iniciar sesión</h2>
                <p class="subtitulo">Ingresa tus credenciales para acceder a la plataforma.</p>

                <form id="loginForm" action="Controllers/LoginController.php" method="POST" novalidate>

                    <div class="campo">
                        <label for="email">Correo electrónico</label>
                        <div class="entrada">
                            <i class="bi bi-envelope icono"></i>
                            <input type="email" class="form-control" id="email" name="correo"
                                   placeholder="usuario@ejemplo.com" autocomplete="email" required>
                        </div>
                        <div class="mensaje-error">Ingresa un correo electrónico válido.</div>
                    </div>

                    <div class="campo">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="m-0">Contraseña</label>
                        </div>
                        <div class="entrada">
                            <i class="bi bi-lock icono"></i>
                            <input type="password" class="form-control con-boton" id="password" name="contrasena"
                                   placeholder="Tu contraseña de acceso" autocomplete="current-password" required>
                            <button type="button" class="btn-ojo" id="togglePass" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="mensaje-error">Ingresa tu contraseña de acceso.</div>
                    </div>

                    <button type="submit" class="btn-entrar" id="btnEntrar">
                        <span class="btn-text">Entrar al sistema</span>
                        <i class="bi bi-arrow-right ms-2 btn-icon"></i>
                    </button>
                </form>

                <div class="login-seguridad-pie text-center mt-4">
                </div>

            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/index.js"></script>
</body>
</html>
