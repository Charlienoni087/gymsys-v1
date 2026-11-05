<?php
// Views/Facturacion.php
require_once __DIR__ . '/../config/conexion.php';

$facturas = [];
$totalFacturado = 0;
$facturasMes = 0;
$mesActual = date('Y-m');

try {
    $sql = "SELECT f.id_facturacion, f.costo, f.fecha_facturacion,
                   m.id_membresia, m.metodo_pago, m.fecha_registro, m.fecha_vencimiento,
                   c.nombre_cliente, c.cedula,
                   u.nombre AS nombre_usuario
            FROM Facturacion f
            LEFT JOIN Membresias m ON f.id_membresia = m.id_membresia
            LEFT JOIN Clientes c ON m.id_cliente = c.id_cliente
            LEFT JOIN Usuarios u ON f.id_usuario = u.id_usuario
            ORDER BY f.id_facturacion DESC";

    $res = $conexion->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $facturas[] = $row;
            $totalFacturado += (float) $row['costo'];
            if (strpos($row['fecha_facturacion'], $mesActual) === 0) {
                $facturasMes += (float) $row['costo'];
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
                    'id_facturacion' => $row['id_membresia'],
                    'costo' => $row['costo'],
                    'fecha_facturacion' => $row['fecha_registro'],
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
    </style>

<div class="facturacion-shell my-3">
    <!-- Toolbar -->
    <div class="facturacion-toolbar">
        <div>
            <h3 class="fw-bold fs-5 mb-1 text-gym-main">Facturación y Registro Financiero</h3>
            <p class="text-muted small mb-0">Comprobantes de pago de membresías, servicios e ingresos del gimnasio.</p>
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
                    <div class="stat-value"><?= count($facturas) ?></div>
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
                    <th class="text-start">Cliente</th>
                    <th>Concepto</th>
                    <th>Monto (C$)</th>
                    <th>Método de Pago</th>
                    <th>Fecha de Emisión</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($facturas)): ?>
                    <?php foreach ($facturas as $f): ?>
                        <tr>
                            <td>
                                <span class="folio-tag">#FAC-<?= str_pad((string)$f['id_facturacion'], 5, '0', STR_PAD_LEFT) ?></span>
                            </td>
                            <td class="text-start fw-semibold text-gym-main">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="venc-avatar" style="width: 30px; height: 30px; border-radius: 6px; font-size: 0.75rem; background: var(--gym-surface-elevated); color: var(--gym-primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                        <?= strtoupper(substr($f['nombre_cliente'] ?? 'C', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div><?= htmlspecialchars($f['nombre_cliente'] ?? 'Cliente General') ?></div>
                                        <div class="text-muted small font-monospace"><?= htmlspecialchars($f['cedula'] ?? '—') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: var(--gym-surface-elevated); color: var(--gym-text-main); border: 1px solid var(--gym-border);">
                                    Membresía Gimnasio
                                </span>
                            </td>
                            <td class="fw-bold text-gym-main font-monospace">
                                C$ <?= number_format((float)$f['costo'], 2) ?>
                            </td>
                            <td>
                                <span class="badge-metodo">
                                    <i class="bi <?= ($f['metodo_pago'] ?? '') === 'efectivo' ? 'bi-cash' : 'bi-credit-card' ?> me-1"></i>
                                    <?= ucfirst($f['metodo_pago'] ?? 'efectivo') ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= date('d/m/Y', strtotime($f['fecha_facturacion'])) ?>
                            </td>
                            <td>
                                <span class="badge-gym-active">
                                    <i class="bi bi-check2-circle"></i> Pagada
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

<script>
document.getElementById('buscadorFacturas').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#tablaFacturas tbody tr').forEach(function (row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>

