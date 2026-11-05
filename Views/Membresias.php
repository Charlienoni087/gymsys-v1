<?php
/* VISTA DE MEMBRESÍAS — GYMSYS DARK & BLUE */

$listaMembresias = $listaMembresias ?? [];
$listaClientes = $listaClientes ?? [];
$en_modo_edicion = $en_modo_edicion ?? false;
$toast = $toast ?? null;

$u_id = $u_id ?? "";
$u_id_cliente = $u_id_cliente ?? "";
$u_nombre = $u_nombre ?? "";
$u_tipo = $u_tipo ?? "";
$u_costo = $u_costo ?? "";
$u_metodo_pago = $u_metodo_pago ?? "efectivo";
$u_fecha_registro = $u_fecha_registro ?? date('Y-m-d');
$u_fecha_vencimiento = $u_fecha_vencimiento ?? "";
$u_estado = $u_estado ?? "Activa";
?>

<div class="gym-card modulo-membresias mb-4">
    <!-- Header del Módulo -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-gym">
        <div>
            <h3 class="fw-bold text-gym-main m-0">Control de Membresías</h3>
            <p class="text-gym-muted small m-0">Administra planes, suscripciones de clientes y estados del gimnasio.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-accion" id="btnAgregarMembresia" data-bs-toggle="modal" data-bs-target="#modalMembresia">
                <i class="bi bi-plus-lg me-1"></i> Nueva membresía
            </button>
            <button type="button" class="btn btn-secondary" id="btnEditarMembresia">
                <i class="bi bi-pencil me-1"></i> Editar
            </button>
            <button type="button" class="btn btn-outline-danger" id="btnEliminarMembresia">
                <i class="bi bi-trash me-1"></i> Eliminar
            </button>
        </div>
    </div>

    <!-- Buscador y Métricas Rápidas -->
    <div class="row align-items-center g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-7">
            <div class="d-flex align-items-center gap-2">
                <span class="badge-gym-blue">
                    <i class="bi bi-card-checklist"></i> Total: <?= count($listaMembresias) ?>
                </span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-5">
            <div class="search-box-wrap">
                <i class="bi bi-search"></i>
                <input type="search" id="SearchBarMembresias" class="form-control" placeholder="Buscar por plan, cliente o estado..." aria-label="Buscar membresías">
            </div>
        </div>
    </div>

    <!-- Tabla de Membresías -->
    <div class="table-responsive rounded border-gym" style="border: 1px solid var(--gym-border);">
        <table class="table table-hover align-middle text-center mb-0 tabla-membresias">
            <thead>
                <tr>
                    <th class="py-3 text-start ps-3">Plan</th>
                    <th class="py-3">Tipo</th>
                    <th class="py-3">Precio</th>
                    <th class="py-3">Estado</th>
                    <th class="py-3">Fecha Creación</th>
                    <th class="py-3">Fecha Vencimiento</th>
                    <th class="py-3 pe-3" style="width: 90px;">Seleccionar</th>
                </tr>
            </thead>
            <tbody id="tablaMembresias">
                <?php if (!empty($listaMembresias)): ?>
                    <?php foreach ($listaMembresias as $membresia): ?>
                        <?php 
                            $estado = $membresia['estado'] ?? 'Activa';
                            $badgeClass = 'badge-gym-active';
                            if ($estado === 'Vencida') {
                                $badgeClass = 'badge-gym-danger';
                            } elseif ($estado === 'Inactiva') {
                                $badgeClass = 'badge-gym-warning';
                            } elseif ($estado === 'Suspendida') {
                                $badgeClass = 'badge-gym-warning';
                            }
                        ?>
                        <tr>
                            <td class="text-start ps-3">
                                <div class="fw-bold text-gym-main"><?= htmlspecialchars($membresia['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small text-gym-muted"><?= htmlspecialchars($membresia['nombre_cliente'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: var(--gym-surface-elevated); border: 1px solid var(--gym-border); color: var(--gym-primary);">
                                    <?= htmlspecialchars($membresia['tipo'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="fw-bold" style="color: var(--gym-primary);">
                                C$ <?= number_format((float) $membresia['costo'], 2, '.', ',') ?>
                            </td>
                            <td>
                                <span class="<?= $badgeClass ?>">
                                    <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-gym-muted">
                                <?= date('d/m/Y', strtotime($membresia['fecha_registro'])) ?>
                            </td>
                            <td class="text-gym-muted">
                                <?= date('d/m/Y', strtotime($membresia['fecha_vencimiento'])) ?>
                            </td>
                            <td class="pe-3">
                                <input type="checkbox" class="form-check-input check-membresia" value="<?= (int) $membresia['id_membresia'] ?>" style="width: 1.25rem; height: 1.25rem; cursor: pointer;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="filaSinRegistros">
                        <td colspan="7" class="text-center py-5 text-gym-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            No hay membresías registradas actualmente en el sistema.
                        </td>
                    </tr>
                <?php endif; ?>
                <tr id="filaSinResultados" style="display: none;">
                    <td colspan="7" class="text-center py-4 text-gym-muted">
                        <i class="bi bi-search me-2"></i>No se encontraron membresías que coincidan con la búsqueda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL PARA AGREGAR Y EDITAR MEMBRESÍA -->
<div class="modal fade" id="modalMembresia" tabindex="-1" aria-labelledby="modalMembresiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="Principal.php?page=membresias" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalMembresiaLabel">
                        <i class="bi <?= $en_modo_edicion ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2" style="color: var(--gym-primary);"></i>
                        <?= $en_modo_edicion ? 'Editar membresía' : 'Registrar nueva membresía' ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_membresia" value="<?= htmlspecialchars((string) $u_id, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- SELECCIONAR CLIENTE -->
                    <div class="mb-3">
                        <label for="id_cliente" class="form-label fw-semibold text-gym-main">Cliente Asociado</label>
                        <select name="id_cliente" id="id_cliente" class="form-select" required>
                            <option value="">Selecciona un cliente</option>
                            <?php foreach ($listaClientes as $cliente): ?>
                                <option value="<?= (int) $cliente['id_cliente'] ?>" <?= (string) $u_id_cliente === (string) $cliente['id_cliente'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cliente['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($cliente['cedula'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- NOMBRE DE LA MEMBRESÍA -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold text-gym-main">Nombre de la Membresía / Plan</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej. Plan Mensual Gold" value="<?= htmlspecialchars((string) $u_nombre, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- TIPO DE MEMBRESÍA -->
                    <div class="mb-3">
                        <label for="tipo" class="form-label fw-semibold text-gym-main">Tipo</label>
                        <input type="text" name="tipo" id="tipo" class="form-control" placeholder="Ej. General, VIP, Personalizado" value="<?= htmlspecialchars((string) $u_tipo, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- PRECIO -->
                    <div class="mb-3">
                        <label for="costo" class="form-label fw-semibold text-gym-main">Precio (C$)</label>
                        <input type="number" name="costo" id="costo" class="form-control" step="0.01" min="0" placeholder="Ej. 350.00" value="<?= htmlspecialchars((string) $u_costo, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- ESTADO -->
                    <div class="mb-3">
                        <label for="estado" class="form-label fw-semibold text-gym-main">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="Activa" <?= $u_estado === 'Activa' ? 'selected' : '' ?>>Activa</option>
                            <option value="Inactiva" <?= $u_estado === 'Inactiva' ? 'selected' : '' ?>>Inactiva</option>
                            <option value="Vencida" <?= $u_estado === 'Vencida' ? 'selected' : '' ?>>Vencida</option>
                            <option value="Suspendida" <?= $u_estado === 'Suspendida' ? 'selected' : '' ?>>Suspendida</option>
                        </select>
                    </div>

                    <!-- FECHA DE CREACIÓN -->
                    <div class="mb-3">
                        <label for="fecha_registro" class="form-label fw-semibold text-gym-main">Fecha de Registro</label>
                        <input type="date" name="fecha_registro" id="fecha_registro" class="form-control" value="<?= htmlspecialchars((string) $u_fecha_registro, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_vencimiento" class="form-label fw-semibold text-gym-main">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="<?= htmlspecialchars((string) $u_fecha_vencimiento, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- CAMPOS AUXILIARES OCULTOS -->
                    <input type="hidden" name="metodo_pago" value="efectivo">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?php if ($en_modo_edicion): ?>
                        <button type="submit" name="editar_membresia" class="btn btn-accion">
                            <i class="bi bi-check-lg me-1"></i> Actualizar
                        </button>
                    <?php else: ?>
                        <button type="submit" name="agregar_membresia" class="btn btn-accion" <?= empty($listaClientes) ? 'disabled' : '' ?>>
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS DEL MÓDULO -->
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

<script src="../js/membresias.js"></script>

<?php if (isset($toast) && $toast !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.mostrarNotificacion === 'function') {
        window.mostrarNotificacion(<?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);
    }
});
</script>
<?php endif; ?>