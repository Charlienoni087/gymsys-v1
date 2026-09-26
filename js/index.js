console.log('GYMSYS LOGIN JS CARGADO');

const form = document.getElementById('loginForm');
const password = document.getElementById('password');
const toggle = document.getElementById('togglePass');
const btn = document.getElementById('btnEntrar');

const modal = document.getElementById('modalLogin');
const statusIcon = document.getElementById('statusIcon');
const statusTitle = document.getElementById('statusTitle');
const statusMessage = document.getElementById('statusMessage');

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

function mostrarModal() {
    modal.classList.remove('error', 'exito');

    statusIcon.className = 'status-icon loading';

    statusTitle.textContent = 'Validando credenciales';
    statusMessage.textContent = 'Espera un momento...';

    modal.classList.add('activo');
    modal.setAttribute('aria-hidden', 'false');
}

function mostrarExito() {
    statusIcon.className = 'status-icon success';

    statusTitle.textContent = '¡Bienvenido a GYMSYS!';
    statusMessage.textContent = 'Credenciales verificadas correctamente.';

    modal.classList.add('exito');
}

function mostrarError() {
    statusIcon.className = 'status-icon error';

    statusTitle.textContent = 'Credenciales incorrectas';
    statusMessage.textContent = 'El correo o la contraseña no son válidos.';

    modal.classList.add('error');
}

function cerrarModal() {
    modal.classList.remove('activo', 'error', 'exito');
    modal.setAttribute('aria-hidden', 'true');
}

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
        const tiempoTranscurrido = Date.now() - inicioValidacion;
        const tiempoRestante = Math.max(0, 1800 - tiempoTranscurrido);

        if (tiempoRestante > 0) {
            await new Promise(resolve => setTimeout(resolve, tiempoRestante));
        }

        if (resultado.success) {
            mostrarExito();

            setTimeout(() => {
                window.location.href = resultado.redirect;
            }, 1800);
        } else {
            mostrarError();

            setTimeout(() => {
                cerrarModal();
                btn.disabled = false;
                btn.textContent = 'Entrar';
            }, 1800);
        }
    } catch (error) {
        statusIcon.className = 'status-icon error';

        statusTitle.textContent = 'Error de conexión';
        statusMessage.textContent = 'No fue posible comunicarse con el servidor.';

        modal.classList.add('error');

        setTimeout(() => {
            cerrarModal();
            btn.disabled = false;
            btn.textContent = 'Entrar';
        }, 1800);
    }

    
});

window.addEventListener('pageshow', () => {
    btn.disabled = false;
    btn.textContent = 'Entrar';
});