// MÓDULO DE MEMBRESÍAS
// Archivo: js/membresias.js

document.addEventListener('DOMContentLoaded', function () {

    // EDITAR MEMBRESÍA

    const btnEditar = document.getElementById('btnEditarMembresia');

    if (btnEditar) {

        btnEditar.addEventListener('click', function () {

            const seleccionados = document.querySelectorAll(
                '.check-membresia:checked'
            );

            // No seleccionó ninguno
            if (seleccionados.length === 0) {
                alert('Por favor, selecciona una membresía para editar.');
                return;
            }

            // Seleccionó más de uno
            if (seleccionados.length > 1) {
                alert('Por favor, selecciona solo una membresía para editar.');
                return;
            }

            // Obtener ID de la membresía
            const idMembresia = seleccionados[0].value;

            // Enviar al controlador
            window.location.href =
                `Principal.php?page=membresias&editar_membresia=${encodeURIComponent(idMembresia)}`;

        });

    }

    // ELIMINAR MEMBRESÍA

    const btnEliminar = document.getElementById('btnEliminarMembresia');

    if (btnEliminar) {

        btnEliminar.addEventListener('click', function () {

            const seleccionados = document.querySelectorAll(
                '.check-membresia:checked'
            );

            // No seleccionó ninguno
            if (seleccionados.length === 0) {
                alert('Por favor, selecciona una membresía para eliminar.');
                return;
            }

            // Seleccionó más de uno
            if (seleccionados.length > 1) {
                alert('Por favor, selecciona solo una membresía a la vez.');
                return;
            }

            // Obtener ID
            const idEliminar = seleccionados[0].value;

            // Confirmación
            if (confirm('¿Estás seguro de que deseas eliminar esta membresía?')) {

                window.location.href =
                    `Principal.php?page=membresias&btn_eliminar=1&delete_id=${encodeURIComponent(idEliminar)}`;

            }

        });

    }

    
    // BUSCAR MEMBRESÍAS
    // Busca por nombre del cliente o cédula
    

    const buscador = document.getElementById('SearchBarMembresias');
    const tabla = document.getElementById('tablaMembresias');
    const filaSinResultados = document.getElementById('filaSinResultados');

    if (buscador && tabla) {

        buscador.addEventListener('input', function () {

            const busqueda = buscador.value
                .trim()
                .toLocaleLowerCase();

            const filas = tabla.querySelectorAll('tr:not(#filaSinResultados)');
            let coincidencias = 0;

            filas.forEach(function (fila) {

                const textoFila = fila.textContent.toLocaleLowerCase();
                const coincide = textoFila.includes(busqueda);

                fila.style.display = coincide ? '' : 'none';

                if (coincide) {
                    coincidencias++;
                }

            });

            // Mostrar mensaje cuando no hay coincidencias
            if (filaSinResultados) {
                filaSinResultados.style.display =
                    coincidencias === 0 ? '' : 'none';
            }

        });

    }

    
    // VALIDAR FECHAS DEL FORMULARIO
    // La fecha de vencimiento no puede ser anterior
    // a la fecha de registro
    const fechaRegistro = document.getElementById('fecha_registro');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');

    if (fechaRegistro && fechaVencimiento) {

        function actualizarFechaMinima() {

            fechaVencimiento.min = fechaRegistro.value;

            if (
                fechaRegistro.value &&
                fechaVencimiento.value &&
                fechaVencimiento.value < fechaRegistro.value
            ) {
                fechaVencimiento.value = fechaRegistro.value;
            }

        }

        fechaRegistro.addEventListener('change', actualizarFechaMinima);

    }

});
