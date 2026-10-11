<?php 
/** @var array $listaClientes */
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/notification.css">
</head>

<style>
  body {
    background-color: var(--gym-gris);
    color: #ffffff;
  }
  .table thead th {
    background-color: var(--gym-amarillo-osc);
    color: var(--gym-negro);
  }

  #toastApp {
    transform: translateY(-120px);
    opacity: 0;
    visibility: hidden;
    transition: transform 0.4s ease, opacity 0.4s ease;
}

  #toastApp.toast-mostrar {
      transform: translateY(0);
      opacity: 1;
      visibility: visible;
  }

  #toastApp.toast-ocultar {
      transform: translateY(-120px);
      opacity: 0;
      visibility: hidden;
  }

</style>
<body>
  
  <div class="container my-4" style="background-color:var(--gym-negro); color: #ffffff;">
    <p class="fw-bold fs-6">¿Qué deseas hacer hoy?</p>
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button class="btn  py-2" style="background-color: var(--gym-amarillo-osc);" data-bs-toggle="modal" data-bs-target="#modalAgregarCliente">
          <i class="bi bi-plus-lg fs-5"></i> Agregar cliente
        </button>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button id="btnMembresia" class="btn  py-2" style="background-color:var(--gym-amarillo-osc);" data-bs-toggle="modal" data-bs-target="#tarjetaMembresia">
          <i class="bi bi-person fs-5"></i> Ver membresías
        </button>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button id="btnEditarCliente" class="btn  py-2" style="background-color:var(--gym-amarillo-osc);">
          <i class="bi bi-pencil fs-5"></i> Editar
        </button>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button class="btn text-bold py-2" id="btnEliminarCliente" name="btn_eliminar" style="background-color: var(--gym-amarillo-osc); color: red;">
          <i class="bi bi-trash fs-5"></i> <b>Eliminar</b>
        </button>
      </div>
    </div>
  
    <div class="table-responsive shadow-sm rounded">
      <div class="container my-4" style="background-color: var(--gym-negro); color: #ffffff;">
        <div class="col-12 d-flex align-items-center gap-3">
          <p class="fw-bold fs-5 mb-0">Clientes registrados</p>
          <input 
            type="text" 
            id="SearchBar" 
            class="form-control" 
            style="max-width: 50%; border: 2px solid var(--gym-amarillo-osc);" 
            placeholder="Busca por nombre o cédula..."
          >
        </div>
      </div>
      <table class="table table-striped table-bordered table-hover align-middle text-center mb-0">
  
  
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Fecha de registro</th>
            <th>Estado</th>
            <th>Seleccionar</th>
          </tr>
        </thead>
        <tbody>
          <?php if(isset($listaClientes) && count($listaClientes) > 0): ?>
              <?php foreach ($listaClientes as $clientes): ?>
                  <tr>
                      <td><?= htmlspecialchars($clientes['nombre_cliente']) ?></td>
                      <td><?= htmlspecialchars($clientes['cedula']) ?></td>
                      <td><?= htmlspecialchars($clientes['correo']) ?></td>
                      <td><?= htmlspecialchars($clientes['telefono']) ?></td>
                      <td><?= htmlspecialchars($clientes['fecha_registro']) ?></td>
                      <td>
                          <?php if ($clientes['estado'] === 'activo'): ?>
                              <span class="badge bg-success">Activo</span>
                          <?php else: ?>
                              <span class="badge bg-danger">Inactivo</span>
                          <?php endif; ?>
                      </td>
                      <td>
                        <input type="checkbox" class="form-check-input check-cliente" value="<?php echo $clientes['id_cliente']?>">
                      </td>
                  </tr>
              <?php endforeach; ?>
          <?php else: ?>
              <tr>
                  <td colspan="7" class="text-center text-muted">No hay clientes registrados en el sistema</td>
              </tr>
          <?php endif; ?>
          
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal para crear clientes -->
<div class="modal fade" id="modalAgregarCliente" tabindex="-1" aria-labelledby="modalFormularioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"> 
    <div class="modal-content" style="border: 1px solid var(--gym-amarillo-osc);">
      
      <form action="Principal.php?page=clientes" method="POST">
        
        <div class="modal-header" style="background-color: var(--gym-amarillo-osc);">
          <h5 class="modal-title" id="modalFormularioLabel" style="color: #ffffff">
            <?= $en_modo_edicion
            ? 'Editar Cliente'
            : 'Registrar Cliente' ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body" style="background-color: var(--gym-negro);">
          <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($u_id) ?>">
          <div class="mb-3">
            <label for="nombreInput" class="form-label">Nombre completo</label>
            <input type="text" class="form-control formInput" name="nombre_cliente" required value="<?= htmlspecialchars($u_nombre) ?>">
          </div>
          
          <div class="mb-3">
            <label for="cedulaInput" class="form-label">Cedula de identidad</label>
            <input type="text" class="form-control formInput" name="cedula" placeholder="562-000000-1234A" required value="<?= htmlspecialchars($u_cedula) ?>">
          </div>

          <div class="mb-3">
            <label for="telefonoInput" class="form-label">Telefono</label>
            <input type="tel" class="form-control formInput" name="telefono" required value="<?= htmlspecialchars($u_telefono) ?>">
          </div>
          <div class="mb-3">
            <label for="correoInput" class="form-label">Correo</label>
            <input type="email" class="form-control formInput" name="correo" required value="<?= htmlspecialchars($u_correo) ?>">
          </div>
          <div class="mb-3">
            <label for="fechaInput" class="form-label">Fecha</label>
            <input type="date" class="form-control formInput" id="fechaInput" name="fecha_registro" required value="<?= htmlspecialchars($u_fecha_registro) ?>">
          </div>
          <div class="mb-3">
            <label for="fechaInput" class="form-label">Estado</label>
            <select name="estado" id="formInput" class="form-select formInput" required>
              <option value="activo" <?= $u_estado ==='activo' ? 'selected' : '' ?>>Activo</option>
              <option value="inactivo" <?= $u_estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
          </div>
        </div>

        <div class="modal-footer" style="background-color: var(--gym-negro);">
          <?php if ($en_modo_edicion) : ?>
            <button type="submit" name="editar_cliente" class="btn" style="background-color: var(--gym-amarillo-osc);">Actualizar</button>
            <?php else: ?>
              <button type="submit" name="agregar_cliente" class="btn" style="background-color: var(--gym-amarillo-osc);">Guardar</button>
            <?php endif; ?>
            
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>

      </form>

    </div>
  </div>
</div>

<div class="modal fade" id="tarjetaMembresia" tabindex="-1" aria-labelledby="tarjetaMembresiaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
    <div class="modal-content ratio ratio-16x9 rounded-4 overflow-hidden shadow" style="border: 2px solid var(--gym-amarillo-osc);">
      <div class="d-flex flex-column h-100 w-100">
        <div class="modal-header py-2 px-4" style="background-color: var(--gym-amarillo-osc);">
          <h5 class="modal-title fs-5 fw-bold" id="modalTarjetaMembresia" style="color: #ffffff;">
            Tarjeta de Membresía
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body d-flex flex-column justify-content-between p-4" style="background-color: var(--gym-negro);">
          <div class="row align-items-center g-3 my-auto">
            <div class="col-auto text-center">
              <div class="d-flex align-items-center justify-content-center rounded-3 bg-secondary bg-opacity-25" style="width: 110px; height: 125px; border: 2px dashed rgba(255, 255, 255, 0.3);">
                <i class="bi bi-person-fill text-white-50" style="font-size: 4.5rem; line-height: 1;"></i>
              </div>
            </div>

            <div class="col d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="text-uppercase text-white-50 d-block small fw-bold">Tipo de Membresía</span>
                  <span class="badge bg-warning text-dark fw-bold fs-6">Mensual</span>
                </div>
                <div class="text-end">
                  <span class="text-uppercase text-white-50 d-block small fw-bold">Costo</span>
                  <span class="fw-bold text-success fs-5">C$720 / mes</span>
                </div>
              </div>

              <div>
                <span class="text-uppercase text-white-50 d-block small fw-bold">Socio</span>
                <h3 class="fw-bold text-white m-0 text-truncate">Carlos Toledo</h3>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-end border-top border-secondary pt-3">
            <div>
              <span class="text-uppercase text-white-50 d-block small fw-bold">Estado</span>
              <span class="badge bg-success fs-6">Activo</span>
            </div>
            <div class="text-end">
              <span class="text-uppercase text-white-50 d-block small fw-bold">Vence</span>
              <span class="fw-bold text-white fs-5 font-monospace">01/11/2026</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--Contenedor para los mensajes-->
<div id="notificacionApp" class="notificacion-app">

    <i id="notificacionIcono" class="bi"></i>

    <span id="notificacionTexto"></span>

    <button type="button"
            id="notificacionCerrar"
            class="notificacion-cerrar">

        <i class="bi bi-x"></i>

    </button>

</div>

<?php if ($en_modo_edicion): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = new bootstrap.Modal(document.getElementById('modalAgregarCliente'));
    modal.show();
});
</script>
<?php endif; ?>

<script src="../js/notifications.js"></script>
<script src="../js/clientes.js"></script>
<script src="../js/searchbar.js"></script>




</body>