window.mostrarNotificacion = function (notificacion) {

    const notificacionEl =
        document.getElementById('notificacionApp');

    const textoEl =
        document.getElementById('notificacionTexto');

    const iconoEl =
        document.getElementById('notificacionIcono');

    const cerrarEl =
        document.getElementById('notificacionCerrar');


    if (!notificacionEl || !textoEl || !iconoEl) {
        console.error('No se encontró el sistema de notificaciones.');
        return;
    }


    // Cancelar temporizador anterior
    if (window.notificacionTimeout) {
        clearTimeout(window.notificacionTimeout);
    }


    // Limpiar estados anteriores
    notificacionEl.classList.remove(
        'success',
        'error',
        'mostrar',
        'ocultar'
    );


    // Configurar tipo
    if (notificacion.tipo === 'success') {

        notificacionEl.classList.add('success');

        iconoEl.className =
            'bi bi-check-circle-fill';

    }

    else if (notificacion.tipo === 'error') {

        notificacionEl.classList.add('error');

        iconoEl.className =
            'bi bi-exclamation-triangle-fill';

    }

    else {

        console.warn(
            'Tipo de notificación no válido:',
            notificacion.tipo
        );

        return;
    }


    // Insertar mensaje
    textoEl.textContent = notificacion.mensaje;


    // Estado inicial: oculto arriba
    notificacionEl.classList.add('ocultar');


    /*
     * Esto obliga al navegador a procesar
     * el estado inicial antes de mostrarlo.
     */
    void notificacionEl.offsetWidth;


    // Animación de entrada
    notificacionEl.classList.remove('ocultar');

    notificacionEl.classList.add('mostrar');


    // Esperar 3 segundos
    window.notificacionTimeout = setTimeout(() => {

        // Animación de salida
        notificacionEl.classList.remove('mostrar');

        notificacionEl.classList.add('ocultar');

    }, 3000);


    // Botón cerrar
    if (cerrarEl) {

        cerrarEl.onclick = function () {

            clearTimeout(window.notificacionTimeout);

            notificacionEl.classList.remove('mostrar');

            notificacionEl.classList.add('ocultar');

        };

    }
};