<?php

if (file_exists("terminado.lock")) {
    header("Location: ../index.php");
    exit();
}

// Ajusta esta ruta según la carpeta donde quede esta página.
$logo = '../assets/img/gymsys-logo.jpg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMSYS - Instalador</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/install.css">

</head>
<body>

    <div class="pagina d-flex justify-content-center align-items-center">

        <div class="instalador-shell">
            <div class="row g-0">

                <!-- Panel de marca: solo pantallas grandes -->
                <aside class="col-lg-6 panel-marca d-none d-lg-flex">
                    <div class="marca-logo">
                        <img src="<?= $logo ?>" alt="Logo de GYMSYS">
                        <span>GYMSYS</span>
                    </div>

                    <div class="marca-texto">
                        <h1>Deja GYMSYS listo para usar.</h1>
                        <p>Crea tu usuario y en unos segundos podrás entrar al sistema.</p>
                    </div>

                    <div class="disco" aria-hidden="true"></div>
                </aside>

                <!-- Panel del formulario -->
                <main class="col-lg-6 panel-form">
                    <div class="form-interior">

                        <img src="<?= $logo ?>" alt="Logo de GYMSYS" class="logo-movil d-lg-none">

                        <h2>Instalación de GYMSYS</h2>
                        <p class="subtitulo">Crea el usuario con el que vas a entrar al sistema.</p>

                        <form id="instaladorForm" action="InstaladorController.php" method="POST" novalidate>

                            <div class="campo">
                                <label for="nombre_usuario">Nombre de usuario</label>
                                <div class="entrada">
                                    <i class="bi bi-person icono"></i>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario"
                                           placeholder="Nombre de usuario" autocomplete="username" required>
                                </div>
                                <div class="mensaje-error">Ingresa un nombre de usuario.</div>
                            </div>

                            <div class="campo">
                                <label for="correo">Correo electrónico</label>
                                <div class="entrada">
                                    <i class="bi bi-envelope icono"></i>
                                    <input type="email" class="form-control" id="correo" name="correo"
                                           placeholder="correo@example.com" autocomplete="email" required>
                                </div>
                                <div class="mensaje-error">Ingresa un correo válido.</div>
                            </div>

                            <div class="campo">
                                <label for="contrasena">Contraseña</label>
                                <div class="entrada">
                                    <i class="bi bi-lock icono"></i>
                                    <input type="password" class="form-control con-boton" id="contrasena" name="contrasena"
                                           placeholder="Contraseña" autocomplete="new-password" required>
                                    <button type="button" class="btn-ojo" data-target="contrasena" aria-label="Mostrar contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="mensaje-error">Ingresa una contraseña.</div>
                            </div>

                            <!-- Sin atributo name: solo se usa para confirmar, no se envía al servidor -->
                            <div class="campo">
                                <label for="confirmar">Confirmar contraseña</label>
                                <div class="entrada">
                                    <i class="bi bi-shield-lock icono"></i>
                                    <input type="password" class="form-control con-boton" id="confirmar"
                                           placeholder="Repite la contraseña" autocomplete="new-password" required>
                                    <button type="button" class="btn-ojo" data-target="confirmar" aria-label="Mostrar contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="mensaje-error" id="msgConfirmar">Repite tu contraseña.</div>
                            </div>

                            <button type="submit" class="btn-instalar" id="btnInstalar">Instalar</button>
                        </form>

                    </div>
                </main>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('instaladorForm');
        const contrasena = document.getElementById('contrasena');
        const confirmar = document.getElementById('confirmar');
        const msgConfirmar = document.getElementById('msgConfirmar');
        const btn = document.getElementById('btnInstalar');

        // Mostrar / ocultar contraseñas
        document.querySelectorAll('.btn-ojo').forEach(boton => {
            boton.addEventListener('click', () => {
                const input = document.getElementById(boton.dataset.target);
                const oculto = input.type === 'password';
                input.type = oculto ? 'text' : 'password';
                boton.querySelector('i').className = oculto ? 'bi bi-eye-slash' : 'bi bi-eye';
                boton.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
                input.focus();
            });
        });

        // Validación con mensajes propios
        function validar(input) {
            let valido = input.checkValidity();

            if (input === confirmar) {
                if (confirmar.value === '') {
                    msgConfirmar.textContent = 'Repite tu contraseña.';
                    valido = false;
                } else if (confirmar.value !== contrasena.value) {
                    msgConfirmar.textContent = 'Las contraseñas no coinciden.';
                    valido = false;
                }
            }

            input.closest('.campo').classList.toggle('tiene-error', !valido);
            input.setAttribute('aria-invalid', !valido);
            return valido;
        }

        form.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('input', () => {
                if (input.closest('.campo').classList.contains('tiene-error')) validar(input);
                // Si cambia la contraseña, revisar otra vez la confirmación
                if (input === contrasena && confirmar.value !== '') validar(confirmar);
            });
        });

        form.addEventListener('submit', (e) => {
            let todoValido = true;
            let primerInvalido = null;

            form.querySelectorAll('input[required]').forEach(input => {
                if (!validar(input)) {
                    todoValido = false;
                    primerInvalido = primerInvalido || input;
                }
            });

            if (!todoValido) {
                e.preventDefault();
                primerInvalido.focus();
                return;
            }

            // Evita envíos dobles mientras se instala
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Instalando...';
        });

        // Si el usuario vuelve con el botón "atrás", restaurar el botón
        window.addEventListener('pageshow', () => {
            btn.disabled = false;
            btn.textContent = 'Instalar';
        });
    </script>
</body>
</html>