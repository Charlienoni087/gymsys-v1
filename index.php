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
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/animations.css">

</head>


<body>
    <div class="modal-login" id="modalLogin" aria-hidden="true">
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
    
    <div class="pagina d-flex justify-content-center align-items-center">

        <div class="login-shell" style="max-width: 980px;">
            <div class="row g-0">

                <!-- Panel de marca: solo pantallas grandes -->
                <aside class="col-lg-6 panel-marca d-none d-lg-flex">
                    <div class="marca-logo">
                        <img src="assets/img/logo.jpeg" alt="Logo de GYMSYS">
                        <span>GYMSYS</span>
                    </div>

                    <div class="marca-texto">
                        <h1>Administra tu gimnasio desde un solo lugar.</h1>
                        <p>Inicia sesión para continuar.</p>
                    </div>

                    <div class="disco" aria-hidden="true"></div>
                </aside>

                <!-- Panel del formulario -->
                <main class="col-lg-6 panel-form">
                    <div class="form-interior">

                        <img src="assets/img/logo.jpeg" alt="Logo de GYMSYS" class="logo-movil d-lg-none">

                        <h2>Inicia sesión</h2>
                        <p class="subtitulo">Ingresa a tu cuenta.</p>

                        <form id="loginForm" action="Controllers/LoginController.php" method="POST" novalidate>

                            <div class="campo">
                                <label for="email">Correo electrónico</label>
                                <div class="entrada">
                                    <i class="bi bi-envelope icono"></i>
                                    <input type="email" class="form-control" id="email" name="correo"
                                           placeholder="correo@example.com" autocomplete="email" required>
                                </div>
                                <div class="mensaje-error">Ingresa un correo válido.</div>
                            </div>

                            <div class="campo">
                                <label for="password">Contraseña</label>
                                <div class="entrada">
                                    <i class="bi bi-lock icono"></i>
                                    <input type="password" class="form-control con-boton" id="password" name="contrasena"
                                           placeholder="Tu contraseña" autocomplete="current-password" required>
                                    <button type="button" class="btn-ojo" id="togglePass" aria-label="Mostrar contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="mensaje-error">Ingresa tu contraseña.</div>
                            </div>

                            <button type="submit" class="btn-entrar" id="btnEntrar">Entrar</button>
                        </form>

                    </div>
                </main>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/index.js"></script>
</body>
</html>