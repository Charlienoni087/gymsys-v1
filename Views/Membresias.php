
<?php

/* VARIABLES DEL MÓDULO DE MEMBRESÍAS*/

$listaMembresias = $listaMembresias ?? [];
$listaClientes = $listaClientes ?? [];
$en_modo_edicion = $en_modo_edicion ?? false;
$toast = $toast ?? null;

$u_id = $u_id ?? "";
$u_id_cliente = $u_id_cliente ?? "";
$u_costo = $u_costo ?? "";
$u_metodo_pago = $u_metodo_pago ?? "efectivo";
$u_fecha_registro = $u_fecha_registro ?? date('Y-m-d');
$u_fecha_vencimiento = $u_fecha_vencimiento ?? "";
?>
<!-- ESTILOS Y RECURSOS DE MEMBRESÍAS -->

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/notification.css">
</head>
<style>

/* REDUCIR LETRAS DE CLIENTE, CÉDULA, cOSTO etc*/
.tabla-membresias td:nth-child(1),
.tabla-membresias td:nth-child(2),
.tabla-membresias td:nth-child(3) 
.tabla-membresias td:nth-child(4)   
.tabla-membresias td:nth-child(5)   
.tabla-membresias td:nth-child(6)
{
    font-size: 13px;
}

/* ESTILOS GENERALES */

.modulo-membresias {
    background-color: var(--gym-negro);
    color: #ffffff;
   border: none;
    border-radius: 16px;
    padding: 30px;
    min-height: 650px;
}

.modulo-membresias .titulo-seccion {
    font-weight: bold;
    margin-bottom: 24px;
}

.modulo-membresias .btn-accion {
    background-color: var(--gym-amarillo-osc);
    color: var(--gym-negro);
    border: 1px solid var(--gym-amarillo-osc);
    padding: 12px 10px;
    font-weight: 500;
}

.modulo-membresias .btn-accion:hover {
    background-color: #ffe34d;
    border-color: #ffe34d;
}

.modulo-membresias .btn-eliminar {
    color: #dc3545;
    font-weight: bold;
}

.modulo-membresias .buscador-membresias {
    border: 2px solid var(--gym-amarillo-osc);
    border-radius: 8px;
    min-height: 48px;
}

.modulo-membresias .tabla-membresias thead th {
    background-color: var(--gym-amarillo-osc);
    color: var(--gym-negro);
    white-space: nowrap;
}

.modulo-membresias .tabla-membresias tbody td {
    vertical-align: middle;
}

.modulo-membresias .modal-content {
    border: 1px solid var(--gym-amarillo-osc);
}

.modulo-membresias .modal-header {
    background-color: var(--gym-amarillo-osc);
    color: var(--gym-negro);
}

.modulo-membresias .modal-body,
.modulo-membresias .modal-footer {
    background-color: var(--gym-negro);
    color: #ffffff;
}

.modulo-membresias .form-control:focus,
.modulo-membresias .form-select:focus {
    border-color: var(--gym-amarillo-osc);
    box-shadow: 0 0 0 0.2rem rgba(255, 230, 0, 0.2);
}

@media (max-width: 768px) {
    .modulo-membresias {
        padding: 16px;
    }
}

/* MODAL DE MEMBRESÍAS - MISMO DISEÑO QUE CLIENTES*/

#modalMembresia .modal-content {
    background-color: var(--gym-negro, #0d0d0d);
    color: #ffffff;
    border: 1px solid var(--gym-amarillo-osc, #ffd600);
    border-radius: 12px;
    overflow: hidden;
}

/* Encabezado amarillo */
#modalMembresia .modal-header {
    background-color: var(--gym-amarillo-osc, #ffd600);
    color: #ffffff;
    border-bottom: 1px solid var(--gym-amarillo-osc, #ffd600);
}

#modalMembresia .modal-title {
    color: #ffffff;
    font-weight: 700;
}

/* Botón para cerrar */
#modalMembresia .btn-close {
    filter: none;
    opacity: 0.7;
}

#modalMembresia .btn-close:hover {
    opacity: 1;
}

/* Etiquetas */
#modalMembresia .form-label {
    color: #ffffff;
    font-weight: 500;
}

/* Campos de texto y listas desplegables */
#modalMembresia .form-control,
#modalMembresia .form-select {
    background-color: #0d0d0d;
    color: #ffffff;
    border: 2px solid var(--gym-amarillo-osc, #ffd600);
    border-radius: 8px;
}

#modalMembresia .form-control::placeholder {
    color: #888888;
}

#modalMembresia .form-control:focus,
#modalMembresia .form-select:focus {
    background-color: #0d0d0d;
    color: #ffffff;
    border-color: #ffd600;
    box-shadow: 0 0 0 0.2rem rgba(255, 214, 0, 0.2);
}

/* Opciones de las listas */
#modalMembresia .form-select option {
    background-color: #0d0d0d;
    color: #ffffff;
}

/* Pie del modal */
#modalMembresia .modal-footer {
    background-color: #0d0d0d;
    border-top: 1px solid #333333;
}

/* Botón secundario */
#modalMembresia .btn-secondary {
    background-color: #6c757d;
    color: #ffffff;
    border: none;
}

/* Mantener legible el texto de ayuda */
#modalMembresia .text-warning {
    color: #ffd600 !important;
}

</style>

<!-- CONTENEDOR PRINCIPAL -->

<div class="container-fluid my-4 modulo-membresias">

    <!-- BOTONES DE ACCIÓN-->

    <p class="fw-bold">¿Qué deseas hacer hoy?</p>

    <div class="row g-3 mb-5">

        <div class="col-12 col-sm-6 col-lg-4 d-grid">
            <button
                type="button"
                class="btn btn-accion"
                id="btnAgregarMembresia"
                data-bs-toggle="modal"
                data-bs-target="#modalMembresia">

                <i class="bi bi-plus-lg fs-5"></i>
                Agregar membresía

            </button>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 d-grid">
            <button
                type="button"
                class="btn btn-accion"
                id="btnEditarMembresia">

                <i class="bi bi-pencil fs-5"></i>
                Editar

            </button>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 d-grid">
            <button
                type="button"
                class="btn btn-accion btn-eliminar"
                id="btnEliminarMembresia">

                <i class="bi bi-trash fs-5"></i>
                Eliminar

            </button>
        </div>

    </div>

    <!-- BUSCADOR DE MEMBRESÍAS -->

    <div class="row align-items-center g-3 mb-4">

        <div class="col-12 col-lg-6">
            <h4 class="fw-bold mb-0">Membresías registradas</h4>
        </div>

        <div class="col-12 col-lg-6">
            <input
                type="search"
                id="SearchBarMembresias"
                class="form-control buscador-membresias"
                placeholder="Buscar por cliente o cédula..."
                aria-label="Buscar membresías">
        </div>

    </div>

    <!-- TABLA DE MEMBRESÍAS -->

    <div class="table-responsive shadow-sm rounded">

        <table class="table table-striped table-bordered table-hover text-center mb-0 tabla-membresias">

            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Cédula</th>
                    <th>Costo</th>
                    <th>Método de pago</th>
                    <th>Registro</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th>Seleccionar</th>
                </tr>
            </thead>

            <tbody id="tablaMembresias">

                <?php if (!empty($listaMembresias)): ?>

                    <?php foreach ($listaMembresias as $membresia): ?>

                        <?php
                        /* DETERMINAR EL ESTADO DE LA MEMBRESÍA*/

                        $fechaHoy = date('Y-m-d');

                        $fechaVencimiento = $membresia['fecha_vencimiento'];

                        $estadoMembresia = (
                            $fechaVencimiento >= $fechaHoy
                        ) ? 'Activa' : 'Vencida';

                        $claseEstado = (
                            $estadoMembresia === 'Activa'
                        ) ? 'bg-success' : 'bg-danger';

                        ?>

                        <tr>
                            <td>
                                <?= htmlspecialchars($membresia['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($membresia['cedula'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                C$ <?= number_format((float) $membresia['costo'], 2, '.', ',') ?>
                            </td>

                            <td>
                                <?php if ($membresia['metodo_pago'] === 'efectivo'): ?>

                                    <span class="badge bg-secondary">
                                        Efectivo
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Transferencia
                                    </span>

                                <?php endif; ?>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($membresia['fecha_registro'])) ?>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($membresia['fecha_vencimiento'])) ?>
                            </td>

                            <td>
                                <span class="badge <?= $claseEstado ?>">
                                    <?= $estadoMembresia ?>
                                </span>
                            </td>

                            <td>
                                <input
                                    type="checkbox"
                                    class="form-check-input check-membresia"
                                    value="<?= (int) $membresia['id_membresia'] ?>"
                                    aria-label="Seleccionar membresía <?= (int) $membresia['id_membresia'] ?>">
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            No hay membresías registradas en el sistema.
                        </td>
                    </tr>

                <?php endif; ?>

                <tr id="filaSinResultados" style="display: none;">
                    <td colspan="9" class="text-center text-muted">
                        No se encontraron membresías con esa búsqueda.
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</div>

<!-- MODAL PARA AGREGAR Y EDITAR MEMBRESÍAS-->

<div
    class="modal fade"
    id="modalMembresia"
    tabindex="-1"
    aria-labelledby="modalMembresiaLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form action="Principal.php?page=membresias" method="POST">

                <!-- ENCABEZADO DEL MODAL -->

                <div class="modal-header">

                    <h5 class="modal-title" id="modalMembresiaLabel">

                        <?= $en_modo_edicion
                            ? 'Editar membresía'
                            : 'Registrar nueva membresía' ?>

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>

                </div>

                <!-- CUERPO DEL MODAL -->

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="id_membresia"
                        value="<?= htmlspecialchars((string) $u_id, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- SELECCIONAR CLIENTE -->

                    <div class="mb-3">

                        <label for="id_cliente" class="form-label">
                            Cliente
                        </label>

                        <select
                            name="id_cliente"
                            id="id_cliente"
                            class="form-select"
                            required>

                            <option value="">
                                Selecciona un cliente
                            </option>

                            <?php foreach ($listaClientes as $cliente): ?>

                                <option
                                    value="<?= (int) $cliente['id_cliente'] ?>"
                                    <?= (string) $u_id_cliente === (string) $cliente['id_cliente'] ? 'selected' : '' ?>>

                                    <?= htmlspecialchars($cliente['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?>
                                    — <?= htmlspecialchars($cliente['cedula'], ENT_QUOTES, 'UTF-8') ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <?php if (empty($listaClientes)): ?>

                            <small class="text-warning">
                                No hay clientes activos disponibles para asignar una membresía.
                            </small>

                        <?php endif; ?>

                    </div>

                    <!-- COSTO -->

                    <div class="mb-3">

                        <label for="costo" class="form-label">
                            Costo (C$)
                        </label>

                        <input
                            type="number"
                            name="costo"
                            id="costo"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            placeholder="Ejemplo: 350.00"
                            value="<?= htmlspecialchars((string) $u_costo, ENT_QUOTES, 'UTF-8') ?>"
                            required>

                    </div>

                    <!-- MÉTODO DE PAGO -->

                    <div class="mb-3">

                        <label for="metodo_pago" class="form-label">
                            Método de pago
                        </label>

                        <select
                            name="metodo_pago"
                            id="metodo_pago"
                            class="form-select"
                            required>

                            <option
                                value="efectivo"
                                <?= $u_metodo_pago === 'efectivo' ? 'selected' : '' ?>>

                                Efectivo

                            </option>

                            <option
                                value="transferencia"
                                <?= $u_metodo_pago === 'transferencia' ? 'selected' : '' ?>>

                                Transferencia

                            </option>

                        </select>

                    </div>

                    <!-- FECHA DE REGISTRO -->

                    <div class="mb-3">

                        <label for="fecha_registro" class="form-label">
                            Fecha de registro
                        </label>

                        <input
                            type="date"
                            name="fecha_registro"
                            id="fecha_registro"
                            class="form-control"
                            value="<?= htmlspecialchars((string) $u_fecha_registro, ENT_QUOTES, 'UTF-8') ?>"
                            required>

                    </div>

                    <!-- FECHA DE VENCIMIENTO -->

                    <div class="mb-3">

                        <label for="fecha_vencimiento" class="form-label">
                            Fecha de vencimiento
                        </label>

                        <input
                            type="date"
                            name="fecha_vencimiento"
                            id="fecha_vencimiento"
                            class="form-control"
                            min="<?= htmlspecialchars((string) $u_fecha_registro, ENT_QUOTES, 'UTF-8') ?>"
                            value="<?= htmlspecialchars((string) $u_fecha_vencimiento, ENT_QUOTES, 'UTF-8') ?>"
                            required>

                    </div>

                </div>

                <!-- BOTONES DEL MODAL -->

                <div class="modal-footer">

                    <?php if ($en_modo_edicion): ?>

                        <button
                            type="submit"
                            name="editar_membresia"
                            class="btn btn-accion">

                            <i class="bi bi-check-lg"></i>
                            Actualizar

                        </button>

                    <?php else: ?>

                       
<button
    type="submit"
    name="agregar_membresia"
    class="btn"
    style="background-color: #ffc107; color: #000000; border-color: #ffc107;"
    <?= empty($listaClientes) ? 'disabled' : '' ?>>

    <i class="bi bi-save"></i>
    Guardar

</button>


                    <?php endif; ?>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- CONTENEDOR PARA NOTIFICACIONES -->

<div id="notificacionApp" class="notificacion-app">

    <i id="notificacionIcono" class="bi"></i>

    <span id="notificacionTexto"></span>

    <button
        type="button"
        id="notificacionCerrar"
        class="notificacion-cerrar">

        <i class="bi bi-x"></i>

    </button>

</div>

<!-- ABRIR EL MODAL AUTOMÁTICAMENTE AL EDITAR -->

<?php if ($en_modo_edicion): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const elementoModal = document.getElementById('modalMembresia');

    if (elementoModal) {
        const modal = new bootstrap.Modal(elementoModal);
        modal.show();
    }

});
</script>

<?php endif; ?>

<!-- ARCHIVOS JAVASCRIPT DEL MÓDULO -->

<script src="../js/notifications.js"></script>
<script src="../js/membresias.js"></script>

<!-- MOSTRAR NOTIFICACIONES DEL CONTROLADOR -->

<?php if (isset($toast) && $toast !== null): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof window.mostrarNotificacion === 'function') {

        window.mostrarNotificacion(
            <?= json_encode(
                $toast,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>
        );

    }

});
</script>

<?php endif; ?>
