<?php
/** @var array $listaProductos */
/** @var array $listaCategorias */
$toast = $toast ?? ($_SESSION['toast'] ?? null);
unset($_SESSION['toast']);
?>
<style>
        .productos-shell {
            background-color: var(--gym-card-bg);
            border: 1px solid var(--gym-border);
            border-radius: var(--gym-radius-lg, 14px);
            padding: 1.75rem;
            box-shadow: var(--gym-shadow-card);
            color: var(--gym-text-main);
            transition: var(--gym-transition);
        }

        .productos-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--gym-border);
        }

        .productos-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

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

        /* Buscador en Modales */
        .buscador-editar {
            position: relative;
        }

        .resultados-productos,
        .lista-resultados {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #172033;
            border: 1px solid var(--gym-border);
            border-radius: 0 0 var(--gym-radius-md) var(--gym-radius-md);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1056;
            display: none;
            box-shadow: var(--gym-shadow-md);
        }

        .resultado-producto {
            padding: 9px 12px;
            cursor: pointer;
            color: var(--gym-text-main);
            background-color: #172033;
            border-bottom: 1px solid #F2EFE6;
            font-size: 0.9rem;
            transition: background 0.15s;
        }

        .resultado-producto:hover {
            background-color: var(--gym-sage-light);
            color: var(--gym-deep);
        }

        .producto-seleccionado {
            background-color: var(--gym-primary-subtle);
            border: 1px solid var(--gym-sage);
            border-radius: var(--gym-radius-md);
            padding: 8px 12px;
            margin-top: 6px;
            color: var(--gym-primary);
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-categoria {
            color: var(--gym-text-main);
            border: 1px solid var(--gym-border);
            font-weight: 600;
            font-size: 0.785rem;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
        }
    </style>

<div class="productos-shell my-3">
    <!-- Mensaje de error general -->
    <?php if (!empty($mensajeError)): ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: #FAF4E7; border-color: #E8D5AC; color: #96742B;">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-warning"></i>
            <div>
                <strong>Atención:</strong> <?= htmlspecialchars($mensajeError) ?>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Toolbar de Productos -->
    <div class="productos-toolbar">
        <div>
            <h3 class="fw-bold fs-5 mb-1 text-gym-main">Inventario de Productos</h3>
            <p class="small mb-0 text-gym-muted">Controla stock, suplementos, bebidas y categorías disponibles para la venta.</p>
        </div>

        <div class="search-box-wrap">
            <i class="bi bi-search"></i>
            <input 
                type="text" 
                id="buscadorProductos" 
                class="form-control" 
                placeholder="Buscar por producto o categoría..."
                aria-label="Buscar productos">
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="productos-actions mb-4">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
            <i class="bi bi-plus-lg me-1"></i> Agregar producto
        </button>

        <button type="button" id="btnEditarProducto" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalEditarProducto">
            <i class="bi bi-pencil me-1"></i> Editar
        </button>

        <button type="button" id="btnEliminarProducto" class="btn btn-outline-danger" style="border-radius: var(--gym-radius-md, 10px); font-weight: 600; padding: 0.55rem 1.15rem;" data-bs-toggle="modal" data-bs-target="#modalEliminarProducto">
            <i class="bi bi-trash me-1"></i> Eliminar
        </button>

        <button type="button" class="btn btn-outline-secondary" style="border-radius: var(--gym-radius-md, 10px); font-weight: 600; padding: 0.55rem 1.15rem; background-color: #FFFFFF; border-color: var(--gym-border);" data-bs-toggle="modal" data-bs-target="#modalCategorias">
            <i class="bi bi-tags me-1"></i> Administrar categorías
        </button>
    </div>

    <!-- Tabla de Productos -->
    <div class="gym-table-wrap table-responsive">
        <table class="table gym-table align-middle text-center mb-0 tabla-productos" id="tablaProductos">
            <thead>
                <tr>
                    <th class="text-start">Nombre del Producto</th>
                    <th>Categoría</th>
                    <th>Precio de Venta</th>
                    <th>Stock Disponible</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($listaProductos) && count($listaProductos) > 0): ?>
                    <?php foreach ($listaProductos as $producto): ?>
                        <tr>
                            <td class="text-start fw-semibold text-gym-main">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="venc-avatar" style="width: 32px; height: 32px; border-radius: 8px; font-size: 0.8rem; background: var(--gym-primary-subtle); color: var(--gym-primary); display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <span><?= htmlspecialchars($producto['nombre_producto']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-categoria">
                                    <?= htmlspecialchars($producto['nombre_categoria']) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark">C$ <?= number_format((float)$producto['precio_venta'], 2) ?></td>
                            <td>
                                <?php $stock = (int)$producto['stock_disponible']; ?>
                                <?php if ($stock <= 3): ?>
                                    <span class="badge-gym-danger">
                                        <i class="bi bi-exclamation-circle-fill"></i> <?= $stock ?> u. (Crítico)
                                    </span>
                                <?php elseif ($stock <= 8): ?>
                                    <span class="badge-gym-warning">
                                        <?= $stock ?> u. (Bajo)
                                    </span>
                                <?php else: ?>
                                    <span class="badge-gym-active">
                                        <?= $stock ?> unidades
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            No hay productos registrados en el sistema.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar Producto -->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formAgregarProducto" method="POST" action="Principal.php?page=productos">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-1"></i> Agregar nuevo producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nombre del producto</label>
                        <input type="text" name="nombre_producto" class="form-control formInput" placeholder="Ej: Proteína Whey 2lbs" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-#172033">Categoría</label>
                        <select name="id_categoria" class="form-select formInput" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($listaCategorias as $categoria): ?>
                                <option value="<?= (int)$categoria['id_categoria'] ?>">
                                    <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="avisoCategoria" class="alert mt-2 mb-0 py-2 px-3 small rounded-3" role="alert" style="background-color: var(--gym-sage-light); color: var(--gym-text-muted); border: 1px solid var(--gym-sage);">
                            <i class="bi bi-info-circle me-1 text-success"></i>
                            <strong>¿No encuentras la categoría?</strong> Créala desde <strong>Administrar categorías</strong>.
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-#172033">Precio de venta</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-semibold">C$</span>
                                <input type="number" name="precio_venta" class="form-control formInput border-start-0 ps-1" min="0.01" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-#172033">Stock inicial</label>
                            <input type="number" name="stock_inicial" class="form-control formInput" min="0" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="agregar_producto" value="1" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarProducto" method="POST" action="Principal.php?page=productos">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-1"></i> Editar producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <!-- Buscador integrado -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Buscar producto a modificar</label>
                        <div class="buscador-editar">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    type="text" 
                                    id="buscarProductoEditar" 
                                    class="form-control formInput border-start-0" 
                                    placeholder="Escribe el nombre del producto..."
                                    autocomplete="off">
                            </div>
                            <div id="resultadosProductos" class="resultados-productos"></div>
                        </div>
                        <div id="productoSeleccionado" class="producto-seleccionado" style="display: none;"></div>
                    </div>

                    <input type="hidden" name="id_producto" id="id_producto">

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nombre del producto</label> 
                        <input type="text" name="nombre_producto_editar" id="nombre_producto_editar" class="form-control formInput" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Categoría</label>
                        <select name="id_categoria_editar" id="id_categoria_editar" class="form-select formInput" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($listaCategorias as $categoria): ?>
                                <option value="<?= (int)$categoria['id_categoria'] ?>">
                                    <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Precio de venta</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-semibold">C$</span>
                                <input type="number" name="precio_venta_editar" id="precio_venta_editar" class="form-control formInput border-start-0 ps-1" min="0.01" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Stock disponible</label>
                            <input type="number" name="stock_disponible_editar" id="stock_disponible_editar" class="form-control formInput" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_producto" value="1" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Producto -->
<div class="modal fade" id="modalEliminarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-trash me-1"></i> Eliminar producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Buscar producto a eliminar</label>
                    <input type="text" id="buscarProductoEliminar" class="form-control formInput" placeholder="Escribe el nombre del producto..." autocomplete="off">
                    <div id="resultadosProductosEliminar" class="lista-resultados mt-1" style="display: none;"></div>
                </div>

                <div id="productoEliminarSeleccionado" class="alert alert-warning mt-2 small" style="display: none;"></div>

                <form method="POST">
                    <input type="hidden" name="id_producto_eliminar" id="id_producto_eliminar">
                    <div class="alert alert-danger mt-3 small py-2 px-3 rounded-3" style="background-color: var(--gym-danger-bg); border-color: var(--gym-danger-border); color: var(--gym-danger);">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Esta acción dará de baja el producto y afectará su conteo de inventario.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="eliminar_producto" class="btn btn-danger" id="btnConfirmarEliminar" disabled>
                            <i class="bi bi-trash me-1"></i> Confirmar eliminación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Administrar Categorías -->
<div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriasLabel">
                    <i class="bi bi-tags me-1"></i> Administrar categorías
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form method="POST" action="Principal.php?page=productos" class="mb-4">
                    <label for="nombre_categoria" class="form-label fw-semibold text-dark">
                        Nueva categoría
                    </label>
                    <div class="input-group">
                        <input type="text" name="nombre_categoria" id="nombre_categoria" class="form-control formInput" placeholder="Ej: Suplementos, Bebidas, Ropa..." maxlength="100" required>
                        <button type="submit" name="agregar_categoria" value="1" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Agregar
                        </button>
                    </div>
                </form>

                <hr style="border-color: var(--gym-border);">

                <h6 class="fw-bold text-dark mb-3">Categorías registradas</h6>

                <div class="list-group rounded-3">
                    <?php foreach ($listaCategorias as $categoria): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border" style="background-color: #FAF8F3;">
                            <span class="fw-semibold text-#172033">
                                <?= htmlspecialchars($categoria['nombre_categoria'], ENT_QUOTES, 'UTF-8') ?>
                            </span>

                            <form method="POST" action="Principal.php?page=productos" onsubmit="return confirm('¿Deseas eliminar esta categoría?');">
                                <input type="hidden" name="id_categoria_eliminar" value="<?= (int) $categoria['id_categoria'] ?>">
                                <button type="submit" name="eliminar_categoria" value="1" class="btn btn-sm btn-outline-danger py-1 px-2" style="border-radius: 6px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($listaCategorias)): ?>
                        <div class="list-group-item text-muted text-center py-3">
                            No hay categorías registradas.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<!-- Inyección de datos JS -->
<script>
    window.productos = <?= json_encode($listaProductos, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script src="../js/notifications.js"></script>
<script src="../js/productos.js"></script>

<?php if ($toast !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.mostrarNotificacion === 'function') {
        window.mostrarNotificacion(
            <?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
        );
    }
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalAgregarProducto');
    const aviso = document.getElementById('avisoCategoria');

    let temporizador;

    if (modal && aviso) {
        modal.addEventListener('shown.bs.modal', function () {
            clearTimeout(temporizador);
            aviso.style.display = 'block';
            aviso.style.opacity = '1';

            temporizador = setTimeout(function () {
                aviso.style.transition = 'opacity 0.5s ease';
                aviso.style.opacity = '0';
                setTimeout(function () {
                    aviso.style.display = 'none';
                }, 500);
            }, 4000);
        });

        modal.addEventListener('hidden.bs.modal', function () {
            clearTimeout(temporizador);
        });
    }
});
</script>
