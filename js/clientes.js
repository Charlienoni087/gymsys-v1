document.addEventListener('DOMContentLoaded', function () {

    //Editar
    const btnEditar = document.getElementById('btnEditarCliente');

    if (btnEditar) {

        btnEditar.addEventListener('click', function () {

            const seleccionados = document.querySelectorAll(
                '.check-cliente:checked'
            );

            // No seleccionó ninguno
            if (seleccionados.length === 0) {

                alert('Por favor, selecciona un cliente para editar.');
                return;
            }

            // Seleccionó más de uno
            if (seleccionados.length > 1) {

                alert('Por favor, selecciona solo un cliente para editar.');
                return;
            }

            // Obtener ID
            const idCliente = seleccionados[0].value;

            // Enviar al controlador
            window.location.href =
                `Principal.php?page=clientes&editar_cliente=${idCliente}`;
        });
    }

    //Eliminar

    const btnEliminar = document.getElementById('btnEliminarCliente');

    if (btnEliminar) {

        btnEliminar.addEventListener('click', function () {

            const seleccionados = document.querySelectorAll(
                '.check-cliente:checked'
            );

            // No seleccionó ninguno
            if (seleccionados.length === 0) {

                alert('Por favor, selecciona un cliente para eliminar.');
                return;
            }

            // Más de uno
            if (seleccionados.length > 1) {

                alert('Por favor, selecciona solo un cliente a la vez.');
                return;
            }

            const idEliminar = seleccionados[0].value;

            // Confirmación
            if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {

                window.location.href =
                    `Principal.php?page=clientes&btn_eliminar=1&delete_id=${idEliminar}`;
            }
        });
    }

});
