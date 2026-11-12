<?php
// Views/Facturacion.php
require_once __DIR__ . '/../config/conexion.php';

$listaFacturas   = $listaFacturas   ?? [];
$listaClientes   = $listaClientes   ?? [];
$listaProductos  = $listaProductos  ?? [];
$listaMembresias = $listaMembresias ?? [];

$toast = $toast ?? ($_SESSION['toast'] ?? null);
unset($_SESSION['toast']);

$facturas = [];
$totalFacturado = 0.0;
$facturasMes = 0;
$emitidas = 0; 
$mesActual = date('Y-m');

try {
    $modeloFacturas = new Facturas($conexion);
    $facturas = $modeloFacturas->obtenerFacturas();   // tablas facturas + factura_detalles
 
    foreach ($facturas as $f) {
        if (strtolower($f['estado']) !== 'pagada') {
            continue; // las anuladas no suman
        }
 
        $monto = (float) $f['total'];
        $totalFacturado += $monto;
        $emitidas++;
 
        if (strpos($f['fecha_factura'], $mesActual) === 0) {
            $facturasMes += $monto;
        }
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
}

try {
    $sql = "SELECT f.id_facturas, f.total, f.fecha_factura,
                   m.id_membresia, m.metodo_pago, m.fecha_registro, m.fecha_vencimiento,
                   c.nombre_cliente, c.cedula,
                   u.nombre AS nombre_usuario
            FROM Facturas f
            LEFT JOIN Membresias m ON f.id_membresia = m.id_membresia
            LEFT JOIN Clientes c ON m.id_cliente = c.id_cliente
            LEFT JOIN Usuarios u ON f.id_usuario = u.id_usuario
            ORDER BY f.id_facturas DESC";

    $res = $conexion->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $facturas[] = $row;
            $totalFacturado += (float) $row['total'];
            if (strpos($row['fecha_factura'], $mesActual) === 0) {
                $facturasMes += (float) $row['total'];
            }
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
}

// Si la tabla de facturación está vacía, calculamos totales desde las membresías pagadas para dar contexto financiero real
if (empty($facturas)) {
    try {
        $sqlMembresias = "SELECT m.id_membresia, m.costo, m.fecha_registro, m.fecha_vencimiento, m.metodo_pago,
                                 c.nombre_cliente, c.cedula
                          FROM Membresias m
                          LEFT JOIN Clientes c ON m.id_cliente = c.id_cliente
                          ORDER BY m.id_membresia DESC";
        $resM = $conexion->query($sqlMembresias);
        if ($resM) {
            while ($row = $resM->fetch_assoc()) {
                $facturas[] = [
                    'id_facturas' => $row['id_membresia'],
                    'total' => $row['costo'],
                    'fecha_factura' => $row['fecha_registro'],
                    'id_membresia' => $row['id_membresia'],
                    'metodo_pago' => $row['metodo_pago'],
                    'fecha_registro' => $row['fecha_registro'],
                    'fecha_vencimiento' => $row['fecha_vencimiento'],
                    'nombre_cliente' => $row['nombre_cliente'],
                    'cedula' => $row['cedula'],
                    'nombre_usuario' => 'Administrador'
                ];
                $totalFacturado += (float) $row['costo'];
                if (strpos($row['fecha_registro'], $mesActual) === 0) {
                    $facturasMes += (float) $row['costo'];
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
    }
}

$promedioFactura = count($facturas) > 0 ? ($totalFacturado / count($facturas)) : 0;
?>

<style>
        .facturacion-shell {
            background-color: var(--gym-card-bg);
            border: 1px solid var(--gym-border);
            border-radius: var(--gym-radius-lg, 14px);
            padding: 1.75rem;
            box-shadow: var(--gym-shadow-card);
            color: var(--gym-text-main);
            transition: var(--gym-transition);
        }

        .facturacion-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--gym-border);
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

        .folio-tag {
            font-family: monospace;
            font-weight: 700;
            color: var(--gym-primary);
            background-color: var(--gym-surface-elevated);
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            border: 1px solid var(--gym-border);
        }
         .facturacion-container { background-color: var(--gym-primary); color: #ffffff; }
         .btn-gym { background-color: var(--gym-surface-elevated); color: #fff; font-weight: 600; }
         .btn-gym:hover { background-color: #fff600; color: #fff; }

    /* tabla de ítems del modal */
    .tabla-items thead th { background-color: var(--gym-surface-elevated); color: #fff; font-size: .85rem; }
    .total-factura { font-size: 1.4rem; font-weight: 700; color: var(--gym-surface-elevated); }
    </style>

 

<div class="facturacion-shell my-3">
    <!-- Toolbar -->
    <div class="facturacion-toolbar">
        <div>
            <h3 class="fw-bold fs-5 mb-1 text-gym-main">Facturación y Registro Financiero</h3>
            <p class="text-muted small mb-0">Comprobantes de pago de membresías, servicios e ingresos del gimnasio.</p>
        </div>

        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <button type="button" class="btn btn-gym" data-bs-toggle="modal" data-bs-target="#modalNuevaFactura">
                <i class="bi bi-plus-lg"></i> Nueva factura
            </button>
        </div>
    </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir Libro Contable
            </button>
        </div>
    </div>


    <!-- KPIs Financieros -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card destacada">
                <div class="stat-card-top">
                    <div class="stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <span class="stat-trend-badge">Histórico</span>
                </div>
                <div>
                    <div class="stat-value">C$ <?= number_format($totalFacturado, 2) ?></div>
                    <div class="stat-label">Total Recaudado</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <span class="stat-trend-badge"><?= date('F Y') ?></span>
                </div>
                <div>
                    <div class="stat-value">C$ <?= number_format($facturasMes, 2) ?></div>
                    <div class="stat-label">Facturado este Mes</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon">
                        <i class="bi bi-receipt"></i>
                    </div> 
                    <span class="stat-trend-badge">Emitidos</span>
                </div>
                <div>
                    <div class="stat-value"><?= count($listaFacturas) ?></div>
                    <div class="stat-label">Total de Facturas</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="stat-trend-badge">Promedio</span>
                </div>
                <div>
                    <div class="stat-value">C$ <?= number_format($promedioFactura, 2) ?></div>
                    <div class="stat-label">Ticket Promedio</div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" id="modalNuevaFactura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border: 1px solid var(--gym-amarillo-osc);">

            <div class="modal-header" style="background-color: var(--gym-amarillo-osc);">
                <h5 class="modal-title" style="color: #ffffff;">
                    <i class="bi bi-receipt"></i> Nueva factura
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
                <form id="formNuevaFactura" method="POST" action="Principal.php?page=facturacion">

                <div class="modal-body" style="background-color: var(--gym-negro); color: #ffffff;">

                    <div class="row g-3 mb-3">

                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select name="id_cliente" id="facturaCliente" class="form-select" required>
                                <option value="">Seleccione un cliente</option>
                                <?php foreach ($listaClientes as $cliente): ?>
                                    <option value="<?= (int) $cliente['id_cliente'] ?>">
                                        <?= htmlspecialchars($cliente['nombre_cliente']) ?>
                                        (<?= htmlspecialchars($cliente['cedula']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Método de pago</label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <!-- tipo de factura: membresía o productos, nunca mezclados -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de factura</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_factura" id="tipoMembresia" value="membresia" checked>
                            <label class="btn btn-outline-warning" for="tipoMembresia">
                                <i class="bi bi-wallet"></i> Membresía
                            </label>
                            <input type="radio" class="btn-check" name="tipo_factura" id="tipoProductos" value="productos">
                            <label class="btn btn-outline-warning" for="tipoProductos">
                                <i class="bi bi-basket"></i> Productos
                            </label>
                        </div>
                    </div>

                    <!-- membresía del cliente -->
                    <div class="mb-3" id="seccionMembresia">
                        <label class="form-label">
                            <i class="bi bi-wallet"></i> Membresía a facturar
                        </label>
                        <select name="id_membresia" id="facturaMembresia" class="form-select" disabled>
                            <option value="">Seleccione primero un cliente</option>
                        </select>
                    </div>

                    <!-- productos -->
                    <div id="seccionProductos" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0"><i class="bi bi-basket"></i> Productos</label>
                            <button type="button" id="btnAgregarItem" class="btn btn-sm btn-gym">
                                <i class="bi bi-plus-lg"></i> Agregar producto
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark table-bordered align-middle mb-0 tabla-items">
                                <thead>
                                    <tr>
                                        <th style="width:40%">Producto</th>
                                        <th style="width:15%">Cant.</th>
                                        <th style="width:18%">Precio</th>
                                        <th style="width:18%">Subtotal</th>
                                        <th style="width:9%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsFactura">
                                    <tr id="filaSinItems">
                                        <td colspan="5" class="text-center text-muted">
                                            Aún no has agregado productos.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- total -->
                    <div class="text-end mt-3">
                        <span class="me-2">Total a pagar:</span>
                        <span class="total-factura" id="totalFactura">C$ 0.00</span>
                    </div>
                </div>

                <div class="modal-footer" style="background-color: var(--gym-negro);">
                    <button type="submit" name="agregar_factura" value="1" id="btnGuardarFactura"
                            class="btn" style="background-color: var(--gym-amarillo-osc);" disabled>
                        <i class="bi bi-floppy"></i> Guardar factura
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Búsqueda y Filtro -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold fs-6 mb-0 text-gym-main">Libro Diario de Facturas</h4>
        </div>
        <div class="col-md-6">
            <input type="text" id="buscadorFacturas" class="form-control formInput" placeholder="Buscar por cliente, cédula o folio...">
        </div>
    </div>

    <!-- Tabla de Facturación -->
    <div class="gym-table-wrap table-responsive">
        <table class="table gym-table align-middle text-center mb-0" id="tablaFacturas">
            <thead>
                <tr>
                <th>N° Factura</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Método de pago</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($facturas)): ?>
                    <?php foreach ($listaFacturas as $f): ?>
                        <?php
                        $tipos = array_unique(array_column($f['detalles'] ?? [], 'tipo'));
                        if (count($tipos) > 1)                 { $concepto = 'Membresía y productos'; }
                        elseif (in_array('membresia', $tipos)) { $concepto = 'Membresía'; }
                        else                                   { $concepto = 'Productos'; }

                        $anulada = ($f['estado'] ?? 'pagada') === 'anulada';
                        $numero  = str_pad((string) $f['id_factura'], 6, '0', STR_PAD_LEFT);
                        ?>
                        <tr>
                        <td><?= htmlspecialchars($numero) ?></td>
                        <td>
                            <?= htmlspecialchars($f['nombre_cliente']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($f['cedula'] ?? '') ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($f['fecha_factura'])) ?></td>
                        <td><?= $concepto ?></td>
                        <td><?= $f['metodo_pago'] === 'efectivo'
                                    ? '<i class="bi bi-cash-coin"></i> Efectivo'
                                    : '<i class="bi bi-bank"></i> Transferencia' ?></td>
                        <td>C$ <?= number_format((float) $f['total'], 2) ?></td>
                        <td>
                            <span class= "badge-gym-active" <?= $anulada ? 'badge-anulada' : 'badge-pagada' ?>">
                                <i class="bi bi-check2-circle"></i> 
                                <?= $anulada ? 'Anulada' : 'Pagada' ?>
                            </span>
                        </td>
                            <td>
                                <span class="badge" style="background-color: var(--gym-surface-elevated); color: var(--gym-text-main); border: 1px solid var(--gym-border);">
                                    
                                    Pagos realizados 

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No se registran facturas en el sistema.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- datos para javascript -->
<script>
    window.facturas   = <?= json_encode($listaFacturas,   JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
    window.productos  = <?= json_encode($listaProductos,  JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
    window.membresias = <?= json_encode($listaMembresias, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
</script>

<!-- javascript -->
<script src="../js/notifications.js"></script>
<script src="../js/Facturacion.js"></script>

<?php if ($toast !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.mostrarNotificacion(
        <?= json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    );
});
</script>
<?php endif; ?>

<script>
document.getElementById('buscadorFacturas').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#tablaFacturas tbody tr').forEach(function (row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>

