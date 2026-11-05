document.addEventListener('DOMContentLoaded', () => {
    const nav = document.getElementById('nav');
    if (nav && !sessionStorage.getItem('navAnimado')) {
        nav.classList.add('nav-animado');
        sessionStorage.setItem('navAnimado', '1');
    }

    const transicion = document.getElementById('transicion');

    document.querySelectorAll('#nav .btn-nav').forEach(link => {
        link.addEventListener('click', (e) => {
            // Si es la descarga de respaldo o un enlace externo, permitir el comportamiento estándar
            if (link.getAttribute('href') && (link.getAttribute('href').includes('action=export') || link.getAttribute('href').startsWith('#'))) {
                return;
            }

            if (link.classList.contains('active')) return; // Ya en este módulo

            if (transicion) {
                e.preventDefault();
                transicion.classList.remove('revelando');
                transicion.classList.add('cubriendo');

                setTimeout(() => {
                    window.location.href = link.href;
                }, 350);
            }
        });
    });

    // Mobile sidebar toggle and backdrop
    const btnHamburger = document.getElementById('btnHamburger');
    const sidebarMenu = document.getElementById('sidebarMenu');
    const backdrop = document.getElementById('sidebarBackdrop');

    if (btnHamburger && sidebarMenu) {
        btnHamburger.addEventListener('click', () => {
            sidebarMenu.classList.toggle('mobile-open');
            if (backdrop) {
                backdrop.classList.toggle('active');
            }
        });
    }

    if (backdrop && sidebarMenu) {
        backdrop.addEventListener('click', () => {
            sidebarMenu.classList.remove('mobile-open');
            backdrop.classList.remove('active');
        });
    }

    // Control interactivo de Modo Claro / Modo Oscuro
    const btnThemeToggle = document.getElementById('btnThemeToggle');
    const themeIcon = document.getElementById('themeToggleIcon');
    const themeText = document.getElementById('themeToggleText');

    function syncThemeUI(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'light') {
            if (themeIcon) {
                themeIcon.className = 'bi bi-moon-stars-fill';
                themeIcon.style.color = '#2563EB';
            }
            if (themeText) {
                themeText.textContent = 'Modo oscuro';
            }
        } else {
            if (themeIcon) {
                themeIcon.className = 'bi bi-sun-fill';
                themeIcon.style.color = '#FBBF24';
            }
            if (themeText) {
                themeText.textContent = 'Modo claro';
            }
        }
    }

    // Inicializar estado de interfaz
    const currentTheme = localStorage.getItem('gymsys_theme') || 'dark';
    syncThemeUI(currentTheme);

    if (btnThemeToggle) {
        btnThemeToggle.addEventListener('click', () => {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('gymsys_theme', newTheme);
            syncThemeUI(newTheme);
        });
    }
});

window.addEventListener('pageshow', (e) => {
    const transicion = document.getElementById('transicion');
    if (transicion && e.persisted) {
        transicion.classList.remove('cubriendo');
        transicion.classList.add('revelando');
    }
});
