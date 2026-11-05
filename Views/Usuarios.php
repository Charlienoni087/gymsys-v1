<?php
/** @var array $listaUsuarios */
/** @var string $errorUsuario */
$listaUsuarios = $listaUsuarios ?? [];
$errorUsuario = $errorUsuario ?? '';
?>

<style>
        .usuarios-shell {
            background-color: var(--gym-card-bg);
            border: 1px solid var(--gym-border);
            border-radius: var(--gym-radius-lg, 14px);
            padding: 1.75rem;
            box-shadow: var(--gym-shadow-card);
            color: var(--gym-text-main);
            transition: var(--gym-transition);
        }

        .usuarios-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--gym-border);
        }

        .search-box-wrap {
            position: relative;
            min-width: 260px;
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

        .badge-role-admin {
            background-color: rgba(0, 136, 255, 0.15);
            color: #60A5FA;
            border: 1px solid rgba(59, 130, 246, 0.35);
            font-weight: 600;
            font-size: 0.785rem;
            padding: 0.35rem 0.65rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-role-recep {
            background-color: var(--gym-champagne-light);
            color: var(--gym-champagne-dark);
            border: 1px solid #E8D5AC;
            font-weight: 600;
            font-size: 0.785rem;
            padding: 0.35rem 0.65rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-table-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--gym-border);
            background-color: #FFFFFF;
            color: var(--gym-text-main);
            transition: var(--gym-transition);
        }

        .btn-table-action:hover {
            background-color: #FAF8F3;
            border-color: #CCD6CF;
            color: var(--gym-deep);
        }

        .btn-table-action.danger:hover {
            background-color: var(--gym-danger-bg);
            border-color: var(--gym-danger-border);
            color: var(--gym-danger);
        }
    </style>

<div class="usuarios-shell my-3">
    <?php if ($errorUsuario !== ''): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 py-2 px-3 rounded-3" style="background-color: var(--gym-danger-bg); border-color: var(--gym-danger-border); color: var(--gym-danger);">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($errorUsuario) ?></div>
        </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="usuarios-toolbar">
        <div>
            <h3 class="fw-bold fs-5 mb-1 text-gym-main">Gestión de Usuarios del Sistema</h3>
            <p class="text-muted small mb-0">Controla roles, permisos y credenciales de acceso para el personal del gimnasio.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="search-box-wrap">
                <i class="bi bi-search"></i>
                <input
                    type="text"
                    id="buscadorUsuarios"
                    class="form-control"
                    placeholder="Buscar por nombre o correo..."
                    aria-label="Buscar usuarios"
                >
            </div>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                <i class="bi bi-person-plus me-1"></i> Nuevo usuario
            </button>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="gym-table-wrap table-responsive">
        <table class="table gym-table align-middle text-center mb-0" id="tablaUsuarios">
            <thead>
                <tr>
                    <th class="text-start">Nombre Completo</th>
                    <th>Correo Electrónico</th>
                    <th>Rol / Permiso</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($listaUsuarios) > 0): ?>
                    <?php foreach ($listaUsuarios as $usuario): ?>
                        <tr>
                            <td class="text-start fw-semibold text-gym-main">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="venc-avatar" style="width: 32px; height: 32px; border-radius: 8px; font-size: 0.8rem; background: var(--gym-sage-light); color: var(--gym-primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted font-monospace"><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td>
                                <?php if ($usuario['rol'] === 'administrador'): ?>
                                    <span class="badge-role-admin">
                                        <i class="bi bi-shield-check"></i> Administrador
                                    </span>
                                <?php else: ?>
                                    <span class="badge-role-recep">
                                        <i class="bi bi-person-badge"></i> Recepcionista
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <button type="button"
                                        class="btn-table-action btn-editar-usuario"
                                        title="Editar datos"
                                        data-id="<?= (int) $usuario['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                        data-correo="<?= htmlspecialchars($usuario['correo']) ?>"
                                        data-rol="<?= htmlspecialchars($usuario['rol']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalEditarUsuario">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <button type="button"
                                        class="btn-table-action btn-clave-usuario"
                                        title="Cambiar contraseña"
                                        data-id="<?= (int) $usuario['id_usuario'] ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalClaveUsuario">
                                        <i class="bi bi-key"></i>
                                    </button>

                                    <button type="button"
                                        class="btn-table-action danger btn-eliminar-usuario"
                                        title="Eliminar usuario"
                                        data-id="<?= (int) $usuario['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalEliminarUsuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No hay usuarios registrados en el sistema</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-1"></i> Registrar nuevo usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control formInput" placeholder="Ej: Marcela Gómez" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control formInput" placeholder="correo@gymsys.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Contraseña</label>
                    <input type="password" name="contrasena" class="form-control formInput" minlength="6" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold text-dark">Rol asignado</label>
                    <select name="rol" class="form-select formInput" required>
                        <option value="" disabled selected>Selecciona un rol</option>
                        <option value="administrador">Administrador</option>
                        <option value="recepcionista">Recepcionista</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="agregar_usuario" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Guardar usuario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <input type="hidden" name="id_usuario" id="editar_id_usuario">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-1"></i> Editar datos del usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Nombre completo</label>
                    <input type="text" name="nombre" id="editar_nombre" class="form-control formInput" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Correo electrónico</label>
                    <input type="email" name="correo" id="editar_correo" class="form-control formInput" required>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold text-dark">Rol asignado</label>
                    <select name="rol" id="editar_rol" class="form-select formInput" required>
                        <option value="administrador">Administrador</option>
                        <option value="recepcionista">Recepcionista</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="editar_usuario" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Restablecer Contraseña -->
<div class="modal fade" id="modalClaveUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <input type="hidden" name="id_usuario" id="clave_id_usuario">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key me-1"></i> Restablecer contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label fw-semibold text-dark">Nueva contraseña</label>
                    <input type="password" name="nueva_contrasena" class="form-control formInput" minlength="6" placeholder="Mínimo 6 caracteres" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="cambiar_contrasena" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Actualizar contraseña
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar Usuario -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <input type="hidden" name="id_usuario" id="eliminar_id_usuario">
            <div class="modal-body text-center py-4">
                <div class="logout-icon-wrapper mx-auto">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 class="fw-bold mb-2 text-dark">¿Eliminar a <span id="eliminar_nombre_usuario" class="text-danger"></span>?</h5>
                <p class="text-muted small mb-0">Esta acción removerá el acceso del usuario permanentemente.</p> 
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4 pt-0 gap-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="eliminar_usuario" class="btn btn-danger px-4">
                    <i class="bi bi-trash me-1"></i> Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.btn-editar-usuario').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('editar_id_usuario').value = btn.dataset.id;
        document.getElementById('editar_nombre').value = btn.dataset.nombre;
        document.getElementById('editar_correo').value = btn.dataset.correo;
        document.getElementById('editar_rol').value = btn.dataset.rol;
    });
});

document.querySelectorAll('.btn-clave-usuario').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('clave_id_usuario').value = btn.dataset.id;
    });
});

document.querySelectorAll('.btn-eliminar-usuario').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('eliminar_id_usuario').value = btn.dataset.id;
        document.getElementById('eliminar_nombre_usuario').textContent = btn.dataset.nombre;
    });
});

document.getElementById('buscadorUsuarios').addEventListener('input', function () {
    const filtro = this.value.toLowerCase();
    document.querySelectorAll('#tablaUsuarios tbody tr').forEach(function (fila) {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
});
</script>
