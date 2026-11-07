<?php 
/** @var array $listaClientes */
/** @var array $listaMembresias */
$en_modo_edicion = $en_modo_edicion ?? false;

/** @var bool $mostrar_tarjeta */
/** @var string $t_socio */
/** @var string $t_plan */
/** @var string $t_tipo */
/** @var string|float $t_costo */
/** @var string $t_estado */
/** @var string $t_vencimiento */
$mostrar_tarjeta = $mostrar_tarjeta ?? false;
$t_socio = $t_socio ?? '';
$t_plan = $t_plan ?? '';
$t_tipo = $t_tipo ?? '';
$t_costo = $t_costo ?? 0;
$t_estado = $t_estado ?? '';
$t_vencimiento = $t_vencimiento ?? '';

$estilosEstado = [
    'Activa'     => ['bg' => 'rgba(22,138,112,0.35)',  'color' => '#B8D8C8', 'border' => '#168A70'],
    'Vencida'    => ['bg' => 'rgba(200,60,60,0.30)',   'color' => '#F0C4C4', 'border' => '#C83C3C'],
    'Suspendida' => ['bg' => 'rgba(214,160,40,0.30)',  'color' => '#F3DFAE', 'border' => '#D6A028'],
    'Inactiva'   => ['bg' => 'rgba(150,150,150,0.30)', 'color' => '#DDDDDD', 'border' => '#999999'],
];
$estiloEstado = $estilosEstado[$t_estado] ?? $estilosEstado['Inactiva'];

?>

    <style>
        .clientes-shell {
            background-color: var(--gym-card-bg);
            border: 1px solid var(--gym-border);
            border-radius: var(--gym-radius-lg, 14px);
            padding: 1.75rem;
            box-shadow: var(--gym-shadow-card);
            color: var(--gym-text-main);
            transition: var(--gym-transition);
        }

        .clientes-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--gym-border);
        }

        .clientes-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .btn-action-primary {
            background: linear-gradient(135deg, var(--gym-primary-start) 0%, var(--gym-primary-end) 100%);
            color: #FFFFFF;
            border: 1px solid var(--gym-primary-glow);
            font-weight: 600;
            padding: 0.55rem 1.15rem;
            border-radius: var(--gym-radius-md, 10px);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.9rem;
            transition: var(--gym-transition);
            box-shadow: 0 4px 14px var(--gym-primary-glow);
        }

        .btn-action-primary:hover {
            background: linear-gradient(135deg, var(--gym-primary-hover) 0%, var(--gym-primary-end) 100%);
            border-color: var(--gym-input-focus-border);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px var(--gym-primary-glow);
        }

        .btn-action-secondary {
            background-color: var(--gym-surface-elevated);
            color: var(--gym-text-muted);
            border: 1px solid var(--gym-border);
            font-weight: 600;
            padding: 0.55rem 1.15rem;
            border-radius: var(--gym-radius-md, 10px);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.9rem;
            transition: var(--gym-transition);
        }

        .btn-action-secondary:hover {
            background-color: var(--gym-border);
            border-color: var(--gym-primary);
            color: var(--gym-text-main);
        }

        .btn-action-danger {
            background-color: var(--gym-danger-bg);
            color: var(--gym-danger);
            border: 1px solid var(--gym-danger-border);
            font-weight: 600;
            padding: 0.55rem 1.15rem;
            border-radius: var(--gym-radius-md, 10px);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.9rem;
            transition: var(--gym-transition);
        }

        .btn-action-danger:hover {
            background-color: var(--gym-danger);
            border-color: var(--gym-danger);
            color: #FFFFFF;
        }

        /* Search input wrapper */
        .search-box-wrap {
            position: relative;
            min-width: 280px;
            flex-grow: 1;
            max-width: 380px;
            display: flex;
            align-items: center;
        }

        .search-box-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gym-accent-icon);
            pointer-events: none;
            z-index: 5;
            font-size: 0.95rem;
            line-height: 1;
        }

        .search-box-wrap input {
            padding-left: 2.75rem !important;
            background-color: var(--gym-input-bg);
            border: 1px solid var(--gym-input-border);
            color: var(--gym-text-main);
            border-radius: var(--gym-radius-md, 10px);
            font-size: 0.9rem;
            transition: var(--gym-transition);
            width: 100%;
        }

        .search-box-wrap input:focus {
            background-color: var(--gym-input-bg);
            border-color: var(--gym-input-focus-border);
            box-shadow: 0 0 0 3px var(--gym-input-focus-ring);
            color: var(--gym-text-main);
        }

        /* Table Design */
        .gym-table-wrap {
            border: 1px solid var(--gym-border);
            border-radius: var(--gym-radius-md, 10px);
            overflow: hidden;
        }

        .gym-table {
            margin: 0;
            width: 100%;
            background-color: var(--gym-card-bg);
            color: var(--gym-text-main);
        }

        .gym-table thead th {
            background-color: var(--gym-surface-elevated);
            color: var(--gym-text-main);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--gym-border);
            white-space: nowrap;
        }

        .gym-table tbody td {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid var(--gym-border);
            color: var(--gym-text-main);
            font-size: 0.9rem;
            vertical-align: middle;
            background-color: var(--gym-card-bg);
        }

        .gym-table tbody tr:hover td {
            background-color: var(--gym-surface-elevated);
            color: var(--gym-text-main);
        }

        .gym-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Checkbox styling */
        .check-cliente {
            width: 1.15rem;
            height: 1.15rem;
            background-color: var(--gym-surface-elevated);
            border-color: var(--gym-input-border);
            cursor: pointer;
        }

        .check-cliente:checked {
            background-color: var(--gym-primary);
            border-color: var(--gym-input-focus-border);
        }

        /* Membership Card Modal Styling */
        .vip-card-box {
            background: linear-gradient(145deg, #12161F 0%, #1A2840 100%);
            border: 1px solid #2B5B9E;
            color: #FFFFFF;
            border: 1px solid rgba(214, 183, 121, 0.35);
            border-radius: 16px;
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(16, 45, 42, 0.35);
        }
        

        .vip-card-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 1px solid rgba(214, 183, 121, 0.12);
            pointer-events: none;
        }
    </style>

<div class="clientes-shell my-3">
    <!-- Barra superior de gestión y búsqueda -->
    <div class="clientes-toolbar">
        <div>
            <h3 class="fw-bold fs-5 mb-1 text-gym-main">Directorio de Clientes</h3>
            <p class="small mb-0 text-gym-muted">Administra los registros, membresías y datos de contacto de tus socios.</p>
        </div>

        <div class="search-box-wrap">
            <i class="bi bi-search"></i>
            <input 
                type="text" 
                id="SearchBar" 
                class="form-control" 
                placeholder="Buscar por nombre o cédula..."
                aria-label="Buscar clientes"
            >
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="clientes-actions mb-4">
        <button class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarCliente">
            <i class="bi bi-plus-lg"></i> Agregar cliente
        </button>

        <button id="btnMembresia" type="button" class="btn-action-secondary">
            <i class="bi bi-credit-card"></i> Ver membresías
        </button>

        <button id="btnEditarCliente" class="btn-action-secondary">
            <i class="bi bi-pencil"></i> Editar
        </button>

        <button class="btn-action-danger" id="btnEliminarCliente" name="btn_eliminar">
            <i class="bi bi-trash"></i> Eliminar
        </button>
    </div>

    <!-- Tabla de Clientes -->
    <div class="gym-table-wrap table-responsive">
        <table class="table gym-table align-middle text-center mb-0">
            <thead>
                <tr>
                    <th class="text-start">Nombre del Cliente</th>
                    <th>Cédula</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Fecha de Registro</th>
                    <th>Estado</th>
                    <th>Seleccionar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($listaClientes) && count($listaClientes) > 0): ?>
                    <?php foreach ($listaClientes as $clientes): ?>
                        <tr>
                            <td class="text-start fw-semibold text-gym-main">
                                <?= htmlspecialchars($clientes['nombre_cliente']) ?>
                            </td>
                            <td class="font-monospace text-muted"><?= htmlspecialchars($clientes['cedula']) ?></td>
                            <td><?= htmlspecialchars($clientes['correo'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($clientes['telefono'] ?: '—') ?></td>
                            <td class="text-muted"><?= date('d/m/Y', strtotime($clientes['fecha_registro'])) ?></td>
                            <td>
                                <?php if ($clientes['estado'] === 'activo'): ?>
                                    <span class="badge-gym-active">
                                        <i class="bi bi-check-circle-fill fs-8"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="badge-gym-danger">
                                        <i class="bi bi-x-circle-fill fs-8"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input check-cliente" value="<?php echo $clientes['id_cliente']?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No hay clientes registrados en el sistema</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Crear y Editar Clientes -->
<div class="modal fade" id="modalAgregarCliente" tabindex="-1" aria-labelledby="modalFormularioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> 
        <div class="modal-content">
            <form action="Principal.php?page=clientes" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormularioLabel">
                        <?= $en_modo_edicion ? '<i class="bi bi-pencil me-1"></i> Editar Cliente' : '<i class="bi bi-person-plus me-1"></i> Registrar Cliente' ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($u_id ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    
                    <div class="mb-3">
                        <label for="nombreInput" class="form-label fw-semibold text-dark">Nombre completo</label>
                        <input type="text" class="form-control formInput" id="nombreInput" name="nombre_cliente" placeholder="Ej: Carlos Toledo" required value="<?= htmlspecialchars($u_nombre) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="cedulaInput" class="form-label fw-semibold text-dark">Cédula de identidad</label>
                        <input type="text" class="form-control formInput" id="cedulaInput" name="cedula" placeholder="Ej: 562-000000-1234A" required value="<?= htmlspecialchars($u_cedula) ?>">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="telefonoInput" class="form-label fw-semibold text-dark">Teléfono</label>
                            <input type="tel" class="form-control formInput" id="telefonoInput" name="telefono" placeholder="Ej: 8888 8888" required value="<?= htmlspecialchars($u_telefono) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="correoInput" class="form-label fw-semibold text-dark">Correo electrónico</label>
                            <input type="email" class="form-control formInput" id="correoInput" name="correo" placeholder="Ej: cliente@example.com" required value="<?= htmlspecialchars($u_correo) ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="fechaInput" class="form-label fw-semibold text-dark">Fecha de registro</label>
                            <input type="date" class="form-control formInput" id="fechaInput" name="fecha_registro" required value="<?= htmlspecialchars($u_fecha_registro ?: date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="estadoInput" class="form-label fw-semibold text-dark">Estado del cliente</label>
                            <select name="estado" id="estadoInput" class="form-select formInput" required>
                                <option value="activo" <?= $u_estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $u_estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?php if ($en_modo_edicion) : ?>
                        <button type="submit" name="editar_cliente" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Actualizar cliente
                        </button>
                    <?php else: ?>
                        <button type="submit" name="agregar_cliente" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar cliente
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tarjeta de Membresía -->
<div class="modal fade" id="tarjetaMembresia" tabindex="-1" aria-labelledby="modalTarjetaMembresia" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
        <div class="modal-content vip-card-box p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-gem text-warning fs-5"></i>
                    <span class="text-uppercase fw-bold text-white small" style="letter-spacing: 0.1em;" id="modalTarjetaMembresia">
                        GYMSYS <?= htmlspecialchars(mb_strtoupper($t_tipo ?: 'VIP'), ENT_QUOTES, 'UTF-8') ?> MEMBERSHIP
                    </span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="row align-items-center g-3 my-2">
                <div class="col-auto text-center">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 90px; height: 100px; background: rgba(255, 255, 255, 0.08); border: 1.5px dashed rgba(214, 183, 121, 0.4);">
                        <i class="bi bi-person-badge" style="font-size: 3rem; color: var(--gym-champagne);"></i>
                    </div>
                </div>

                <div class="col">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="text-uppercase small text-white-50 d-block fw-semibold">Tipo de Plan</span>
                            <span class="fw-bold fs-5 text-white"><?= htmlspecialchars($t_plan ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="text-end">
                            <span class="text-uppercase small text-white-50 d-block fw-semibold">Cuota</span>
                            <span class="fw-bold fs-5 text-white">C$<?= number_format((float) $t_costo, 2) ?></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-uppercase small text-white-50 d-block fw-semibold">Socio Titular</span>
                        <h4 class="fw-bold text-white m-0 text-truncate"><?= htmlspecialchars($t_socio ?: '-', ENT_QUOTES, 'UTF-8') ?></h4>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end pt-3 mt-2" style="border-top: 1px solid rgba(221, 229, 223, 0.15);">
                <div>
                    <span class="text-uppercase small text-white-50 d-block fw-semibold">Estado de Socio</span>
                    <span class="badge" style="background: <?= $estiloEstado['bg'] ?>; color: <?= $estiloEstado['color'] ?>; border: 1px solid <?= $estiloEstado['border'] ?>;">
                        <?= htmlspecialchars($t_estado ?: '-', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div class="text-end">
                    <span class="text-uppercase small text-white-50 d-block fw-semibold">Vigencia</span>
                    <span class="fw-bold text-white font-monospace">
                        <?= $t_vencimiento ? date('d/m/Y', strtotime($t_vencimiento)) : '-' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>



<?php if ($en_modo_edicion): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('modalAgregarCliente');
    if (modalEl) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
});
</script>
<?php endif; ?>

<?php if ($mostrar_tarjeta): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tarjetaEl = document.getElementById('tarjetaMembresia');
    if (tarjetaEl) {
        var tarjeta = new bootstrap.Modal(tarjetaEl);
        tarjetaEl.addEventListener('hidden.bs.modal', function () {
            history.replaceState(null, '', 'Principal.php?page=clientes');
        });
        tarjeta.show();
    }
});
</script>
<?php endif; ?>

<script src="../js/notifications.js"></script>
<script src="../js/clientes.js"></script>
<script src="../js/searchbar.js"></script>

<?php if (isset($toast) && $toast !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.mostrarNotificacion === 'function') {
        window.mostrarNotificacion(<?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);
    }
});
</script>
<?php endif; ?>
