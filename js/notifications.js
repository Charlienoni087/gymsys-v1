/**
 * GYMSYS EXPANDABLE NOTCH NOTIFICATION ENGINE
 * High-end Dynamic Notch System for System Alerts and Feedback
 */

(function () {
    'use strict';

    function asegurarContenedorNotch() {
        let el = document.getElementById('notificacionApp');
        if (!el) {
            el = document.createElement('div');
            el.id = 'notificacionApp';
            el.className = 'notificacion-app';
            el.setAttribute('role', 'alert');
            el.setAttribute('aria-live', 'polite');
            document.body.appendChild(el);
        }

        // Si el contenedor no tiene la estructura moderna del notch, actualizar su contenido
        if (!el.querySelector('.notch-content')) {
            el.innerHTML = `
                <div class="notch-icon-wrapper" id="notificacionIconoWrapper">
                    <i id="notificacionIcono" class="bi bi-bell-fill"></i>
                </div>
                <div class="notch-content">
                    <span class="notch-tag" id="notificacionTag">GYMSYS</span>
                    <span class="notch-text" id="notificacionTexto"></span>
                </div>
                <button type="button" id="notificacionCerrar" class="notificacion-cerrar notch-btn-close" aria-label="Cerrar">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="notch-progress" id="notificacionProgreso"></div>
            `;
        }

        return el;
    }

    window.mostrarNotificacion = function (notificacion) {
        if (!notificacion) return;

        // Normalizar entrada si pasan solo un string
        if (typeof notificacion === 'string') {
            notificacion = {
                tipo: 'success',
                mensaje: notificacion
            };
        }

        const el = asegurarContenedorNotch();
        const textoEl = document.getElementById('notificacionTexto');
        const iconoEl = document.getElementById('notificacionIcono');
        const tagEl = document.getElementById('notificacionTag');
        const cerrarEl = document.getElementById('notificacionCerrar');
        const progresoEl = document.getElementById('notificacionProgreso');

        // Cancelar temporizador previo
        if (window.notificacionTimeout) {
            clearTimeout(window.notificacionTimeout);
            window.notificacionTimeout = null;
        }

        // Limpiar clases de estado previas
        el.classList.remove('success', 'error', 'warning', 'info', 'mostrar', 'ocultar', 'notch-shake');

        const tipo = (notificacion.tipo || 'info').toLowerCase();
        el.classList.add(tipo);

        // Configuración por tipo
        if (tipo === 'success') {
            iconoEl.className = 'bi bi-check2-circle';
            if (tagEl) tagEl.textContent = notificacion.titulo || 'OPERACIÓN EXITOSA';
        } else if (tipo === 'error') {
            iconoEl.className = 'bi bi-exclamation-octagon-fill';
            if (tagEl) tagEl.textContent = notificacion.titulo || 'ERROR / ATENCIÓN';
            el.classList.add('notch-shake');
        } else if (tipo === 'warning') {
            iconoEl.className = 'bi bi-exclamation-triangle-fill';
            if (tagEl) tagEl.textContent = notificacion.titulo || 'ADVERTENCIA';
        } else {
            iconoEl.className = 'bi bi-info-circle-fill';
            if (tagEl) tagEl.textContent = notificacion.titulo || 'SISTEMA GYMSYS';
        }

        // Mensaje
        textoEl.textContent = notificacion.mensaje || '';

        // Reiniciar barra de progreso
        if (progresoEl) {
            progresoEl.style.animation = 'none';
            void progresoEl.offsetWidth; // Force reflow
            progresoEl.style.animation = '';
        }

        // Forzar reflujo antes de mostrar para reiniciar la animación
        void el.offsetWidth;

        // Mostrar el Notch
        el.classList.add('mostrar');

        // Duración (por defecto 3.2s, errores un poco más: 4.2s)
        const duracion = notificacion.duracion || (tipo === 'error' ? 4200 : 3200);

        if (progresoEl) {
            progresoEl.style.animationDuration = `${duracion / 1000}s`;
        }

        window.notificacionTimeout = setTimeout(() => {
            el.classList.remove('mostrar');
            el.classList.add('ocultar');
        }, duracion);

        // Click en botón cerrar
        if (cerrarEl) {
            cerrarEl.onclick = function (e) {
                e.stopPropagation();
                if (window.notificacionTimeout) {
                    clearTimeout(window.notificacionTimeout);
                }
                el.classList.remove('mostrar');
                el.classList.add('ocultar');
            };
        }
    };

    // Auto-detección en carga de página si existe algún mensaje en sessionStorage
    document.addEventListener('DOMContentLoaded', () => {
        const flashMsg = sessionStorage.getItem('gymsys_flash_msg');
        if (flashMsg) {
            try {
                const parsed = JSON.parse(flashMsg);
                window.mostrarNotificacion(parsed);
            } catch (e) {
                window.mostrarNotificacion({ tipo: 'info', mensaje: flashMsg });
            }
            sessionStorage.removeItem('gymsys_flash_msg');
        }
    });
})();
