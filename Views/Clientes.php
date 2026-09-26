<?php 
/** @var array $listaClientes */
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
</style>
<body>
  
  <div class="container my-4" style="background-color:var(--gym-negro); color: #ffffff;">
    <p class="fw-bold fs-6">¿Qué deseas hacer hoy?</p>
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button class="btn  py-2" style="background-color: var(--gym-amarillo-osc);">
          <i class="bi bi-plus-lg fs-5"></i> Agregar cliente
        </button>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button class="btn  py-2" style="background-color:var(--gym-amarillo-osc);">
          <i class="bi bi-person fs-5"></i> Ver membresías
        </button>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button class="btn  py-2" style="background-color:var(--gym-amarillo-osc);">
          <i class="bi bi-pencil fs-5"></i> Editar
        </button>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 d-grid">
        <button class="btn text-bold py-2" style="background-color: var(--gym-amarillo-osc); color: red;">
          <i class="bi bi-trash fs-5"></i> <b>Eliminar</b>
        </button>
      </div>
    </div>
  
    <div class="table-responsive shadow-sm rounded">
      <table class="table table-striped table-bordered table-hover align-middle text-center mb-0">
  
        <div class="container my-4" style="background-color: var(--gym-negro); color: #ffffff;">
          <div class="col-12 d-flex align-items-center gap-3">
            <p class="fw-bold fs-5 mb-0">Clientes registrados</p>
            <input 
              type="text" 
              id="buscadorClientes" 
              class="form-control" 
              style="max-width: 50%; border: 2px solid var(--gym-amarillo-osc);" 
              placeholder="Busca por nombre o cédula..."
            >
          </div>
        </div>
  
        <thead class="table-dark">
          <tr>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Fecha de registro</th>
            <th>Estado</th>
            <th>Acciones</th>
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
                              <span class="bagde bg-danger">Inactivo</span>
                          <?php endif; ?>
                      </td>
                      <td></td>
                  </tr>
              <?php endforeach; ?>
          <?php else: ?>
              <tr>
                  <td colspan="6" class="text-center text-muted">No hay clientes registrados en el sistema</td>
              </tr>
          <?php endif; ?>
          
        </tbody>
      </table>
    </div>
  </div>
</body>