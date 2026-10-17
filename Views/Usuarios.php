<?php
/** @var array $listaUsuarios */
/** @var string $errorUsuario */
$listaUsuarios = $listaUsuarios ?? [];
$errorUsuario = $errorUsuario ?? '';
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/variables.css">
</head>

<style>
  body {
    background-color: var(--gym-gris);
    color: #ffffff;
  }
  .table-dark {
    background-color: var(--gym-amarillo-osc);
    color: #ffffff;
  }
  .badge-rol {
    background-color: var(--gym-amarillo-osc);
    color: #0d0d0d;
    font-weight: 600;
  }
</style>
<body>

  <div class="container my-4" style="background-color:var(--gym-negro); color: #ffffff;">

    <?php if ($errorUsuario !== ''): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errorUsuario) ?></div>
    <?php endif; ?>

    <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="d-flex align-items-center gap-3">
        <p class="fw-bold fs-8 mb-10">Usuarios registrados</p>
        <input
          type="text"
          id="buscadorUsuarios"
          class="form-control"
          style="min-width: 260px; border: 3px solid var(--gym-amarillo-osc);"
          placeholder="Buscador"
        >
      </div>
      <button type="button" class="btn py-2" style="background-color: var(--gym-amarillo-osc);" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
        <i class="bi bi-plus-lg fs-5"></i> Nuevo usuario
      </button>
    </div>

    <div class="table-responsive shadow-sm rounded">
      <table class="table table-striped table-bordered table-hover align-middle text-center mb-0" id="tablaUsuarios">
        <thead class="table-dark">
          <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($listaUsuarios) > 0): ?>
            <?php foreach ($listaUsuarios as $usuario): ?>
              <tr>
                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                <td><span class="badge badge-rol text-capitalize"><?= htmlspecialchars($usuario['rol']) ?></span></td>
                <td>
                  <button type="button"
                    class="btn btn-sm me-1 btn-editar-usuario"
                    style="background-color: var(--gym-amarillo-osc);"
                    data-id="<?= (int) $usuario['id_usuario'] ?>"
                    data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                    data-correo="<?= htmlspecialchars($usuario['correo']) ?>"
                    data-rol="<?= htmlspecialchars($usuario['rol']) ?>"
                    data-bs-toggle="modal" data-bs-target="#modalEditarUsuario">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button type="button"
                    class="btn btn-sm me-1 btn-clave-usuario"
                    style="background-color: var(--gym-gris-claro);"
                    data-id="<?= (int) $usuario['id_usuario'] ?>"
                    data-bs-toggle="modal" data-bs-target="#modalClaveUsuario">
                    <i class="bi bi-key"></i>
                  </button>
                  <button type="button"
                    class="btn btn-sm btn-eliminar-usuario"
                    style="background-color: var(--gym-amarillo-osc); color: red;"
                    data-id="<?= (int) $usuario['id_usuario'] ?>"
                    data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                    data-bs-toggle="modal" data-bs-target="#modalEliminarUsuario">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">No hay usuarios registrados en el sistema</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>


  <div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" class="modal-content" style="background-color: var(--gym-negro); color: #fff;">
        <div class="modal-header border-0">
          <h5 class="modal-title">Nuevo usuario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="contrasena" class="form-control" minlength="6" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="rol" class="form-select" required>
              <option value="" disabled selected>Selecciona un rol</option>
              <option value="administrador">Administrador</option>
              <option value="recepcionista">Recepcionista</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="agregar_usuario" class="btn" style="background-color: var(--gym-amarillo-osc);">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  
  <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" class="modal-content" style="background-color: var(--gym-negro); color: #fff;">
        <input type="hidden" name="id_usuario" id="editar_id_usuario">
        <div class="modal-header border-0">
          <h5 class="modal-title">Editar usuario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" id="editar_correo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="rol" id="editar_rol" class="form-select" required>
              <option value="administrador">Administrador</option>
              <option value="recepcionista">Recepcionista</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="editar_usuario" class="btn" style="background-color: var(--gym-amarillo-osc);">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal fade" id="modalClaveUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" class="modal-content" style="background-color: var(--gym-negro); color: #fff;">
        <input type="hidden" name="id_usuario" id="clave_id_usuario">
        <div class="modal-header border-0">
          <h5 class="modal-title">Restablecer contraseña</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="nueva_contrasena" class="form-control" minlength="6" required>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="cambiar_contrasena" class="btn" style="background-color: var(--gym-amarillo-osc);">Actualizar</button>
        </div>
      </form>
    </div>
  </div>


  <div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" class="modal-content" style="background-color: var(--gym-negro); color: #fff;">
        <input type="hidden" name="id_usuario" id="eliminar_id_usuario">
        <div class="modal-body text-center py-4">
          <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3"></i>
          <h5>¿Eliminar a <span id="eliminar_nombre_usuario"></span>?</h5>
          <p class="text-muted">Esta acción no se puede deshacer.</p> 
        </div>
        <div class="modal-footer border-0 justify-content-center pb-4">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="eliminar_usuario" class="btn btn-danger">Sí, eliminar</button>
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
</body>
