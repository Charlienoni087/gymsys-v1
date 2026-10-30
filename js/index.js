console.log('GYMSYS LOGIN JS CARGADO');

const form = document.getElementById('loginForm');
const password = document.getElementById('password');
const toggle = document.getElementById('togglePass');
const btn = document.getElementById('btnEntrar');

const modal = document.getElementById('modalLogin');
const statusIcon = document.getElementById('statusIcon');
const statusTitle = document.getElementById('statusTitle');
const statusMessage = document.getElementById('statusMessage');

// Mostrar / ocultar contraseña
toggle.addEventListener('click', () => {
    const oculto = password.type === 'password';

    password.type = oculto ? 'text' : 'password';

    toggle.querySelector('i').className = oculto
        ? 'bi bi-eye-slash'
        : 'bi bi-eye';

    toggle.setAttribute(
        'aria-label',
        oculto ? 'Ocultar contraseña' : 'Mostrar contraseña'
    );

    password.focus();
});

// Validación de inputs
function validar(input) {
    const valido = input.checkValidity();
    const campo = input.closest('.campo');

    campo.classList.toggle('tiene-error', !valido);
    campo.classList.toggle('invalido', !valido);

    input.setAttribute('aria-invalid', !valido);

    return valido;
}

form.querySelectorAll('input[required]').forEach(input => {
    input.addEventListener('input', () => {
        if (
            input.closest('.campo').classList.contains('tiene-error') ||
            input.closest('.campo').classList.contains('invalido')
        ) {
            validar(input);
        }
    });
});

// Control de estados en el Modal
function mostrarModal() {
    modal.classList.remove('error', 'exito');

    statusIcon.className = 'status-icon loading';

    statusTitle.textContent = 'Validando credenciales';
    statusMessage.textContent = 'Espera un momento...';

    modal.classList.add('activo');
    modal.setAttribute('aria-hidden', 'false');
}

function mostrarExito(mensaje = 'Credenciales verificadas correctamente.') {
    statusIcon.className = 'status-icon success';

    statusTitle.textContent = '¡Bienvenido a GYMSYS!';
    statusMessage.textContent = mensaje;

    modal.classList.add('exito');
}

function mostrarError(mensaje = 'El correo o la contraseña no son válidos.', titulo = 'Credenciales incorrectas') {
    statusIcon.className = 'status-icon error';

    statusTitle.textContent = titulo;
    statusMessage.textContent = mensaje;

    modal.classList.add('error');
}

function cerrarModal() {
    modal.classList.remove('activo', 'error', 'exito');
    modal.setAttribute('aria-hidden', 'true');
}

// Envío del formulario
form.addEventListener('submit', async e => {
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
        primerInvalido.focus();
        return;
    }

    btn.disabled = true;

    mostrarModal();
    const inicioValidacion = Date.now();

    const datos = new FormData(form);

    try {
        const respuesta = await fetch(form.action, {
            method: 'POST',
            body: datos,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const resultado = await respuesta.json();

        // 1 segundo exacto (1000ms) de espera mínima para que no sea molesto
        const tiempoTranscurrido = Date.now() - inicioValidacion;
        const tiempoRestante = Math.max(0, 1000 - tiempoTranscurrido);

        if (tiempoRestante > 0) {
            await new Promise(resolve => setTimeout(resolve, tiempoRestante));
        }

        if (resultado.success) {
            mostrarExito(resultado.message);

            // Redirección rápida tras éxito
            setTimeout(() => {
                window.location.href = resultado.redirect;
            }, 1000);
        } else {
            const tituloModal = resultado.bloqueado ? 'Acceso bloqueado' : 'Credenciales incorrectas';
            const mensajeModal = resultado.message || 'El correo o la contraseña no son válidos.';

            mostrarError(mensajeModal, tituloModal);

            // Si está bloqueado se deja 2.5s para que lea bien el tiempo; si solo falló la clave, se quita en 1.2s
            const duracionEspera = resultado.bloqueado ? 3000 : 1200;

            setTimeout(() => {
                cerrarModal();

                if (typeof window.mostrarNotificacion === 'function') {
                    window.mostrarNotificacion({
                        tipo: 'error',
                        mensaje: mensajeModal
                    });
                }

                if (resultado.bloqueado) {
                    btn.disabled = true;
                    btn.textContent = 'Bloqueado';
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Entrar';
                }
            }, duracionEspera);
        }
    } catch (error) {
        mostrarError('No fue posible comunicarse con el servidor.', 'Error de conexión');

        setTimeout(() => {
            cerrarModal();
            btn.disabled = false;
            btn.textContent = 'Entrar';

            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion({
                    tipo: 'error',
                    mensaje: 'Error de conexión con el servidor.'
                });
            }
        }, 1200);
    }
});

// Reactivar botón al regresar con el historial del navegador
window.addEventListener('pageshow', () => {
    btn.disabled = false;
    btn.textContent = 'Entrar';
});