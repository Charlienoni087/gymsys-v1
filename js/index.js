/**
 * GYMSYS LOGIN & AUTHENTICATION SCRIPT
 * Integrated with the Expandable Dynamic Notch Notification System
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePass');
    const btn = document.getElementById('btnEntrar');
    const shell = document.querySelector('.form-container-box') || document.querySelector('.login-shell');

    if (!form || !btn) return;

    // 1. Mostrar / ocultar contraseña
    if (toggle && password) {
        toggle.addEventListener('click', () => {
            const oculto = password.type === 'password';
            password.type = oculto ? 'text' : 'password';

            const icon = toggle.querySelector('i');
            if (icon) {
                icon.className = oculto ? 'bi bi-eye-slash' : 'bi bi-eye';
            }

            toggle.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
            password.focus();
        });
    }

    // 2. Validación de inputs individuales
    function validar(input) {
        const valido = input.checkValidity();
        const campo = input.closest('.campo');

        if (campo) {
            campo.classList.toggle('tiene-error', !valido);
            campo.classList.toggle('invalido', !valido);
        }

        input.setAttribute('aria-invalid', !valido);
        return valido;
    }

    form.querySelectorAll('input[required]').forEach(input => {
        input.addEventListener('input', () => {
            const campo = input.closest('.campo');
            if (campo && (campo.classList.contains('tiene-error') || campo.classList.contains('invalido'))) {
                validar(input);
            }
        });
    });

    // 3. Envío del formulario con soporte para Expandable Notch
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        let todoValido = true;
        let primerInvalido = null;

        form.querySelectorAll('input[required]').forEach(input => {
            if (!validar(input)) {
                todoValido = false;
                primerInvalido = primerInvalido || input;
            }
        });

        if (!todoValido) {
            if (primerInvalido) primerInvalido.focus();

            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion({
                    tipo: 'warning',
                    titulo: 'CAMPOS INCOMPLETOS',
                    mensaje: 'Por favor ingresa tu correo electrónico y contraseña.',
                    duracion: 3200
                });
            }
            return;
        }

        // Estado cargando en el botón
        btn.disabled = true;
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span><span>Verificando...</span>';

        // Notificación Notch en progreso
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion({
                tipo: 'info',
                titulo: 'AUTENTICACIÓN',
                mensaje: 'Verificando credenciales de acceso...',
                duracion: 4500
            });
        }

        const datos = new FormData(form);
        const inicioValidacion = Date.now();

        try {
            const respuesta = await fetch(form.action, {
                method: 'POST',
                body: datos,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const resultado = await respuesta.json();

            // Pequeña pausa táctica para suavizar la transición (mínimo 500ms)
            const tiempoTranscurrido = Date.now() - inicioValidacion;
            const tiempoRestante = Math.max(0, 500 - tiempoTranscurrido);
            if (tiempoRestante > 0) {
                await new Promise(resolve => setTimeout(resolve, tiempoRestante));
            }

            if (resultado.success) {
                // Estado de éxito
                btn.classList.add('btn-exito');
                btn.innerHTML = '<i class="bi bi-check2 me-2"></i><span>¡Acceso Concedido!</span>';

                if (typeof window.mostrarNotificacion === 'function') {
                    window.mostrarNotificacion({
                        tipo: 'success',
                        titulo: 'AUTENTICACIÓN EXITOSA',
                        mensaje: resultado.message || '¡Bienvenido a GYMSYS! Redirigiendo...',
                        duracion: 2500
                    });
                }

                // Redirección inmediata y fluida
                setTimeout(() => {
                    window.location.href = resultado.redirect || '/GYMSYS/Views/Principal.php';
                }, 700);

            } else {
                // Manejo de error o bloqueo
                if (shell) {
                    shell.classList.remove('card-shake');
                    void shell.offsetWidth;
                    shell.classList.add('card-shake');
                    setTimeout(() => shell.classList.remove('card-shake'), 450);
                }

                // Marcar campos con error
                form.querySelectorAll('.campo').forEach(c => c.classList.add('tiene-error'));

                const esBloqueado = Boolean(resultado.bloqueado);
                const mensajeError = resultado.message || 'El correo o la contraseña no son correctos.';

                if (typeof window.mostrarNotificacion === 'function') {
                    window.mostrarNotificacion({
                        tipo: 'error',
                        titulo: esBloqueado ? 'CUENTA BLOQUEADA' : 'CREDENCIALES INCORRECTAS',
                        mensaje: mensajeError,
                        duracion: esBloqueado ? 5500 : 3800
                    });
                }

                if (esBloqueado) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-shield-lock me-2"></i><span>Acceso bloqueado</span>';
                } else {
                    btn.disabled = false;
                    btn.innerHTML = textoOriginal;
                    if (password) {
                        password.value = '';
                        password.focus();
                    }
                }
            }

        } catch (error) {
            console.error('Error de autenticación:', error);

            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion({
                    tipo: 'error',
                    titulo: 'ERROR DE CONEXIÓN',
                    mensaje: 'No fue posible comunicarse con el servidor. Verifica tu conexión.',
                    duracion: 3500
                });
            }

            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    });

    // Reactivar el botón al navegar hacia atrás en el historial
    window.addEventListener('pageshow', () => {
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('btn-exito');
            btn.innerHTML = '<span class="btn-text">Entrar al sistema</span><i class="bi bi-arrow-right ms-2 btn-icon"></i>';
        }
    });
});
