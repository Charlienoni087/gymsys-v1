/* datos de los productos */
const productos = window.productos;

/* elementos del buscador */

const buscadorEditar = document.getElementById('buscarProductoEditar');
const resultadosProductos = document.getElementById('resultadosProductos');
const productoSeleccionado = document.getElementById('productoSeleccionado');


/* eliminar producto */

const buscadorEliminar = document.getElementById('buscarProductoEliminar');
const resultadosEliminar = document.getElementById('resultadosProductosEliminar');
const productoEliminarSeleccionado = document.getElementById('productoEliminarSeleccionado');
const idProductoEliminar = document.getElementById('id_producto_eliminar');
const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');


/* buscar productos para eliminar */

buscadorEliminar.addEventListener('input', function () {

    const texto = this.value.trim().toLowerCase();
    resultadosEliminar.innerHTML = '';

    if (texto === '') {
        resultadosEliminar.style.display = 'none';
        return;
    }

    const resultados = productos.filter(function (producto) {
        return producto.nombre_producto.toLowerCase().includes(texto);
    });

    if (resultados.length === 0) {
        resultadosEliminar.innerHTML =
            '<div class="resultado-producto">No se encontraron productos</div>';

        resultadosEliminar.style.display = 'block';
        return;
    }

    resultados.forEach(function (producto) {

        const elemento = document.createElement('div');

        elemento.className = 'resultado-producto';
        elemento.textContent = producto.nombre_producto;

        elemento.addEventListener('click', function () {
            seleccionarProductoEliminar(producto);
        });

        resultadosEliminar.appendChild(elemento);
    });

    resultadosEliminar.style.display = 'block';
});


/* seleccionar producto para eliminar */

function seleccionarProductoEliminar(producto) {

    idProductoEliminar.value = producto.id_producto;
    buscadorEliminar.value = producto.nombre_producto;
    resultadosEliminar.style.display = 'none';

    productoEliminarSeleccionado.textContent =
        'Producto seleccionado: ' + producto.nombre_producto;

    productoEliminarSeleccionado.style.display = 'block';
    btnConfirmarEliminar.disabled = false;
}


/* limpiar modal eliminar */

document.getElementById('modalEliminarProducto')
    .addEventListener('hidden.bs.modal', function () {

        buscadorEliminar.value = '';
        idProductoEliminar.value = '';
        resultadosEliminar.innerHTML = '';
        resultadosEliminar.style.display = 'none';
        productoEliminarSeleccionado.style.display = 'none';
        btnConfirmarEliminar.disabled = true;

    });


/* confirmar eliminación */

btnConfirmarEliminar.addEventListener('click', function () {

    const producto = buscadorEliminar.value.trim();

    if (idProductoEliminar.value === '') {
        alert('Primero selecciona un producto.');
        return;
    }

    const confirmar = confirm(
        '¿Seguro que quieres eliminar el producto "' + producto + '"?'
    );

    if (confirmar) {

        const formulario = btnConfirmarEliminar.closest('form');
        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = 'eliminar_producto';
        input.value = '1';

        formulario.appendChild(input);
        formulario.submit();
    }

});


/* buscar productos para editar */

buscadorEditar.addEventListener('input', function () {

    const texto = this.value.trim().toLowerCase();
    resultadosProductos.innerHTML = '';

    if (texto === '') {
        resultadosProductos.style.display = 'none';
        return;
    }

    const resultados = productos.filter(function (producto) {
        return producto.nombre_producto.toLowerCase().includes(texto);
    });

    if (resultados.length === 0) {

        resultadosProductos.innerHTML =
            '<div class="resultado-producto">No se encontraron productos</div>';

        resultadosProductos.style.display = 'block';
        return;
    }

    resultados.forEach(function (producto) {

        const elemento = document.createElement('div');

        elemento.className = 'resultado-producto';
        elemento.textContent = producto.nombre_producto;

        elemento.addEventListener('click', function () {
            cargarProducto(producto);
        });

        resultadosProductos.appendChild(elemento);
    });

    resultadosProductos.style.display = 'block';
});


/* cargar producto */

function cargarProducto(producto) {

    document.getElementById('id_producto').value = producto.id_producto;
    document.getElementById('nombre_producto_editar').value = producto.nombre_producto;
    document.getElementById('id_categoria_editar').value = producto.id_categoria;
    document.getElementById('precio_venta_editar').value = producto.precio_venta;
    document.getElementById('stock_disponible_editar').value = producto.stock_disponible;

    productoSeleccionado.textContent =
        'Producto seleccionado: ' + producto.nombre_producto;

    productoSeleccionado.style.display = 'block';
    buscadorEditar.value = producto.nombre_producto;
    resultadosProductos.style.display = 'none';
}


/* cerrar resultados al hacer clic afuera */

document.addEventListener('click', function (evento) {

    if (
        !buscadorEditar.contains(evento.target) &&
        !resultadosProductos.contains(evento.target)
    ) {
        resultadosProductos.style.display = 'none';
    }

});


/* limpiar modal editar */

document.getElementById('modalEditarProducto')
    .addEventListener('hidden.bs.modal', function () {

        buscadorEditar.value = '';
        resultadosProductos.innerHTML = '';
        resultadosProductos.style.display = 'none';
        productoSeleccionado.textContent = '';
        productoSeleccionado.style.display = 'none';

        document.getElementById('id_producto').value = '';
        document.getElementById('nombre_producto_editar').value = '';
        document.getElementById('id_categoria_editar').value = '';
        document.getElementById('precio_venta_editar').value = '';
        document.getElementById('stock_disponible_editar').value = '';

    });


/* buscador general de productos */

document.getElementById('buscadorProductos')
    .addEventListener('keyup', function () {

        const texto = this.value.toLowerCase();
        const filas = document.querySelectorAll('#tablaProductos tbody tr');

        filas.forEach(function (fila) {

            const contenido = fila.textContent.toLowerCase();

            fila.style.display =
                contenido.includes(texto) ? '' : 'none';

        });

    });