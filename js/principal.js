if (!sessionStorage.getItem('navAnimado')) {
            document.getElementById('nav').classList.add('nav-animado');
            sessionStorage.setItem('navAnimado', '1');
}

const transicion = document.getElementById('transicion');

document.querySelectorAll('#nav .btn-nav').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        if (link.classList.contains('active')) return; // ya estás en ese módulo

        transicion.classList.remove('revelando');
        transicion.classList.add('cubriendo');

        setTimeout(() => {
            window.location.href = link.href;
        }, 550); // un poco más que la duración de la animación
    });
});

window.addEventListener('pageshow', (e) => {
    if (e.persisted) transicion.classList.remove('cubriendo');
});