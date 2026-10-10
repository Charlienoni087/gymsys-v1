<?php
/** @var array $listaProductos */
/** @var array $listaCategorias */
//alertas temporales
$toast = $toast ?? ($_SESSION['toast'] ?? null);
unset($_SESSION['toast']);
?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/notification.css">
    <style>

/* posición de notificación en productos */
.notificacion-app {
    top: 50%;
    bottom: auto;
    transform: translate(-50%, -50%);
}
.notificacion-app.mostrar {
    transform: translate(-50%, -50%);
}
.notificacion-app.ocultar {
    transform: translate(-50%, -50%);
}
</style>
</head>
<style>
    .productos-container {background-color: var(--gym-negro);color: #ffffff;}
    .btn-gym {background-color: var(--gym-amarillo-osc);color: #000;font-weight: 600; }
    .btn-gym:hover {background-color: #fff600;color: #000; }
    .tabla-productos thead th {background-color: var(--gym-amarillo-osc);color: #000;}
    /* buscador del modal */
    .buscador-editar {position: relative;}
    .resultados-productos {position: absolute;top: 100%;left: 0;right: 0;background: #ffffff;border: 1px solid #ced4da;
     border-radius: 0 0 6px 6px;max-height: 200px;overflow-y: auto;z-index: 1056;display: none;}
    .resultado-producto {padding: 10px 12px;cursor: pointer;color: #000; background-color: #ffffff}
    .resultado-producto:hover {background-color: #fff600;}
    .producto-seleccionado {background-color: #e9ecef;border-radius: 6px;padding: 8px 12px;margin-top: 5px;color: #000;}
</style>
<div class="container my-4 productos-container">
    <!-- mensaje de error -->
    <?php if (!empty($mensajeError)): ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Producto no registrado</strong><br>
                <?= htmlspecialchars($mensajeError) ?>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <p class="fw-bold fs-6">¿Qué deseas hacer hoy?</p>
    <!-- botones principales -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4 d-grid">
            <button type="button" class="btn btn-gym py-2" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                <i class="bi bi-plus-lg fs-5"></i>
                Agregar producto
            </button>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 d-grid">
            <button type="button" id="btnEditarProducto" class="btn btn-gym py-2" data-bs-toggle="modal" data-bs-target="#modalEditarProducto">
                <i class="bi bi-pencil fs-5"></i>
                Editar
            </button>
        </div>
        <div class="col-12 col-sm-6 col-lg-4 d-grid">
            <button type="button" id="btnEliminarProducto" class="btn btn-gym py-2" style="color:red;" data-bs-toggle="modal" data-bs-target="#modalEliminarProducto">
                <i class="bi bi-trash fs-5"></i>
                <b>Eliminar</b>
            </button>
        </div>
    </div>
    
<!-- Botón para administrar categorías -->
<div class="col-12 col-sm-6 col-lg-4 d-grid">
    <button
        type="button"
        class="btn btn-gym py-2"
        data-bs-toggle="modal"
        data-bs-target="#modalCategorias">
        <i class="bi bi-tags fs-5"></i>
        Administrar categorías
    </button>
</div>

    <!-- título y buscador -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-5">
            <p class="fw-bold fs-5 mb-0">Productos registrados</p>
        </div>
        <div class="col-md-7">
            <input type="text" id="buscadorProductos" class="form-control" placeholder="Buscar por producto o categoría..." style="border: 2px solid var(--gym-amarillo-osc);">
        </div>
    </div>
    <!-- tabla productos -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-striped table-bordered table-hover align-middle text-center mb-0 tabla-productos" id="tablaProductos">

            <thead>
                <tr>
                    <th>Producto</th> <th>Categoría</th> <th>Precio de venta</th> <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($listaProductos) && count($listaProductos) > 0): ?>
                    <?php foreach ($listaProductos as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre_producto']) ?></td>
                            <td><?= htmlspecialchars($producto['nombre_categoria']) ?></td>
                            <td>C$ <?= number_format((float)$producto['precio_venta'], 2) ?></td>
                            <td><?= (int)$producto['stock_disponible'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No hay productos registrados en el sistema.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- modal agregar producto -->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid var(--gym-amarillo-osc);">
            <form id="formAgregarProducto" method="POST" action="Principal.php?page=productos">
                <!-- encabezado -->
                <div class="modal-header" style="background-color: var(--gym-amarillo-osc);">
                    <h5 class="modal-title" style="color: #ffffff;">
                        <i class="bi bi-basket"></i>
                        Agregar producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <!-- cuerpo -->
                <div class="modal-body" style="background-color: var(--gym-negro);">
                    <!-- nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre del producto</label>
                        <input type="text" name="nombre_producto" class="form-control" placeholder="Ej: Proteína Whey" required>
                    </div>
                    <!-- categoría -->
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($listaCategorias as $categoria): ?>
                                <option value="<?= (int)$categoria['id_categoria'] ?>">
                                    <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- precio -->
                    <div class="mb-3">
                        <label class="form-label">Precio de venta</label>
                        <div class="input-group">
                            <span class="input-group-text">C$</span>
                            <input type="number" name="precio_venta" class="form-control" min="0.01" step="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <!-- stock -->
                    <div class="mb-3">
                        <label class="form-label">Stock inicial</label>
                        <input type="number" name="stock_inicial" class="form-control" min="0" value="0" required>
                    </div>
                </div>
                <!-- botones -->
                <div class="modal-footer" style="background-color: var(--gym-negro);">
                    <button type="submit" name="agregar_producto" value="1" class="btn" style="background-color: var(--gym-amarillo-osc);">
                        <i class="bi bi-floppy"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- modal editar producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid var(--gym-amarillo-osc);">
            <form id="formEditarProducto" method="POST" action="Principal.php?page=productos">
                <!-- encabezado -->
                <div class="modal-header" style="background-color: var(--gym-amarillo-osc);">
                    <h5 class="modal-title" style="color: #ffffff;">
                        <i class="bi bi-pencil-square"></i>
                        Editar producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <!-- cuerpo -->
                <div class="modal-body" style="background-color: var(--gym-negro);">
                    <!-- buscar producto -->
                    <div class="mb-3">
                        <label class="form-label">Buscar producto</label>
                        <div class="buscador-editar">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    type="text" id="buscarProductoEditar" class="form-control" placeholder="Escriba el nombre del producto..."
                                    autocomplete="off">
                            </div>
                            <!-- resultados -->
                            <div id="resultadosProductos" class="resultados-productos"></div>
                        </div>
                        <!-- producto seleccionado -->
                        <div id="productoSeleccionado" class="producto-seleccionado" style="display: none;"> </div>
                    </div>
                    <!-- id del producto -->
                    <input type="hidden" name="id_producto" id="id_producto">
                    <!-- nombre -->
                    <div class="mb-3"> <label class="form-label">Nombre del producto</label> 
                    <input type="text" name="nombre_producto_editar" id="nombre_producto_editar" class="form-control" required> </div>
<!-- categoría -->
 <div class="mb-3">
<label class="form-label">Categoría</label>
<select name="id_categoria_editar" id="id_categoria_editar" class="form-select" required>
<option value="">Seleccione una categoría</option>
 <?php foreach ($listaCategorias as $categoria): ?>
 <option value="<?= (int)$categoria['id_categoria'] ?>">
 <?= htmlspecialchars($categoria['nombre_categoria']) ?> </option>
 <?php endforeach; ?> </select> </div>

                    <!-- precio -->
                    <div class="mb-3">
                        <label class="form-label">Precio de venta</label>
                        <div class="input-group"> <span class="input-group-text">C$</span>
 <input type="number" name="precio_venta_editar"
   id="precio_venta_editar" class="form-control" min="0.01"step="0.01"required> </div> </div>

                    <!-- stock -->
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock_disponible_editar" id="stock_disponible_editar"
                            class="form-control" min="0"required>
                    </div>
                </div>
                <!-- botones -->
                <div class="modal-footer" style="background-color: var(--gym-negro);">
                    <button type="button"class="btn btn-secondary"data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit"name="editar_producto"value="1" class="btn"
                        style="background-color: var(--gym-amarillo-osc);">
                        <i class="bi bi-check-lg"></i>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- modal eliminar producto -->
<div class="modal fade" id="modalEliminarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid var(--gym-amarillo-osc);">
            <!-- encabezado -->
            <div class="modal-header" style="background-color: var(--gym-amarillo-osc);">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="bi bi-trash"></i>
                    Eliminar producto
                </h5>
                <button
                    type="button" class="btn-close" data-bs-dismiss="modal"aria-label="Cerrar">
                </button>
            </div>
            <!-- cuerpo -->
            <div class="modal-body" style="background-color: var(--gym-negro);">
                <!-- buscar producto -->
                <div class="mb-3">
                    <label class="form-label">Buscar producto</label>
                    <input type="text" id="buscarProductoEliminar" class="form-control"placeholder="Escribe el nombre del producto"
                        autocomplete="off">
                    <!-- resultados -->
                    <div id="resultadosProductosEliminar" class="lista-resultados mt-2"style="display: none;"> </div>
                </div>
                <!-- producto seleccionado -->
                <div id="productoEliminarSeleccionado" class="alert alert-warning mt-3" style="display: none;"> </div>
                <!-- formulario para eliminar -->
                <form method="POST">
                    <input type="hidden" name="id_producto_eliminar" id="id_producto_eliminar">
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        Esta acción eliminará el producto y su registro de inventario.
                    </div>
                    <!-- botones -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" name="eliminar_producto" class="btn btn-danger" id="btnConfirmarEliminar">
                            <i class="bi bi-trash"></i>
                            Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     MODAL PARA ADMINISTRAR CATEGORÍAS
     ===================================================== -->

<div class="modal fade" id="modalCategorias" tabindex="-1"
     aria-labelledby="modalCategoriasLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"
             style="background-color: var(--gym-negro); border: 1px solid var(--gym-amarillo-osc);">

            <!-- ENCABEZADO -->
            <div class="modal-header"
                 style="background-color: var(--gym-amarillo-osc);">

                <h5 class="modal-title" id="modalCategoriasLabel"
                    style="color: #ffffff;">
                    <i class="bi bi-tags"></i>
                    Administrar categorías
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Cerrar">
                </button>
            </div>

            <!-- CUERPO -->
            <div class="modal-body">

                <form method="POST" action="Principal.php?page=productos">

                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">
                            Nombre de la categoría
                        </label>

                        <input type="text"
                               name="nombre_categoria"
                               id="nombre_categoria"
                               class="form-control"
                               placeholder="Ejemplo: Proteínas"
                               maxlength="100"
                               required>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit"
                                name="agregar_categoria"
                                value="1"
                                class="btn btn-gym">
                            <i class="bi bi-plus-lg"></i>
                            Agregar categoría
                        </button>
                    </div>

                </form>

                <hr style="border-color: var(--gym-amarillo-osc);">

                <!-- LISTA DE CATEGORÍAS -->
                <h6 class="text-white mb-3">Categorías registradas</h6>

                <div class="list-group">
                    <?php foreach ($listaCategorias as $categoria): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">

                            <span>
                                <?= htmlspecialchars($categoria['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <form method="POST"
                                  action="Principal.php?page=productos"
                                  onsubmit="return confirm('¿Deseas eliminar esta categoría?');">

                                <input type="hidden"
                                       name="id_categoria_eliminar"
                                       value="<?= (int) $categoria['id_categoria'] ?>">

                                <button type="submit"
                                        name="eliminar_categoria"
                                        value="1"
                                        class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                    Eliminar
                                </button>

                            </form>

                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($listaCategorias)): ?>
                        <div class="list-group-item text-muted">
                            No hay categorías registradas.
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- PIE DEL MODAL -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- contenedor para los mensajes -->

<div id="notificacionApp" class="notificacion-app">
    <i id="notificacionIcono" class="bi"></i>
    <span id="notificacionTexto"></span>

    <button type="button" id="notificacionCerrar" class="notificacion-cerrar">
        <i class="bi bi-x"></i>
    </button>
</div>

<!-- datos para javascript -->
<script>
    window.productos = <?= json_encode($listaProductos, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- javascript -->
<script src="../js/notifications.js"></script>
<script src="../js/productos.js"></script>

<?php if ($toast !== null): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.mostrarNotificacion(
        <?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    );

});
</script>

<?php endif; ?>

</body>