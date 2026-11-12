<?php

class Facturas
{
    private mysqli $db;

    public function __construct(mysqli $conexion)
    {
        $this->db = $conexion;
    }

    /* OBTENER TODAS LAS FACTURAS CON SU DETALLE
       Devuelve cada factura con el arreglo "detalles" que usa la vista */

    public function obtenerFacturas(?int $limite = null): array
    {
        $sql = "SELECT
                    f.id_factura,
                    f.id_cliente,
                    c.nombre_cliente,
                    c.cedula,
                    f.fecha_factura,
                    f.metodo_pago,
                    f.total,
                    f.estado
                FROM facturas f
                INNER JOIN clientes c
                    ON f.id_cliente = c.id_cliente
                ORDER BY f.id_factura DESC";

        if ($limite !== null && $limite > 0) {
            $sql .= " LIMIT " . $limite;
        }

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            return [];
        }

        $facturas = [];

        foreach ($resultado->fetch_all(MYSQLI_ASSOC) as $fila) {
            $fila['detalles'] = [];
            $facturas[(int) $fila['id_factura']] = $fila;
        }

        if (empty($facturas)) {
            return [];
        }

        // Todos los detalles de esas facturas en una sola consulta
        $ids = implode(',', array_keys($facturas));

        $sqlDetalles = "SELECT
                            id_factura,
                            tipo,
                            descripcion,
                            cantidad,
                            precio_unitario,
                            subtotal
                        FROM factura_detalles
                        WHERE id_factura IN ($ids)
                        ORDER BY id_detalle ASC";

        $detalles = $this->db->query($sqlDetalles);

        if ($detalles) {
            foreach ($detalles->fetch_all(MYSQLI_ASSOC) as $d) {
                $id = (int) $d['id_factura'];
                if (isset($facturas[$id])) {
                    $facturas[$id]['detalles'][] = $d;
                }
            }
        }

        return array_values($facturas);
    }

    /* MEMBRESÍAS QUE AÚN NO TIENEN FACTURA PAGADA
       Si una factura se anula, su membresía vuelve a quedar disponible */

    public function obtenerMembresiasPendientes(): array
    {
        $sql = "SELECT
                    m.id_membresia,
                    m.id_cliente,
                    m.costo,
                    m.fecha_registro,
                    m.fecha_vencimiento
                FROM membresias m
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM factura_detalles d
                    INNER JOIN facturas f
                        ON f.id_factura = d.id_factura
                    WHERE d.id_membresia = m.id_membresia
                      AND d.tipo = 'membresia'
                      AND f.estado = 'pagada'
                )
                ORDER BY m.id_membresia DESC";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            return [];
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /* CREAR UNA FACTURA
       Los precios y el stock se leen siempre de la base de datos,
       nunca se confía en lo que llega del formulario.

       $tipo      = 'membresia' | 'productos'  (una factura nunca mezcla los dos)
       $productos = [ ['id_producto' => 3, 'cantidad' => 2], ... ]

       Devuelve ['ok' => bool, 'mensaje' => string, 'id_factura' => int|null] */

    public function crearFactura(
        int $id_cliente,
        string $metodo_pago,
        string $tipo,
        ?int $id_membresia,
        array $productos
    ): array {

        try {

            if (!in_array($tipo, ['membresia', 'productos'], true)) {
                throw new RuntimeException("El tipo de factura no es válido.");
            }

            $this->db->begin_transaction();

            // 1. El cliente debe existir
            $stmt = $this->db->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ?");
            $stmt->bind_param("i", $id_cliente);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$existe) {
                throw new RuntimeException("El cliente seleccionado no existe.");
            }

            $lineas = [];
            $total  = 0.0;

            // 2. Factura de membresía
            if ($tipo === 'membresia') {

                if ($id_membresia === null || $id_membresia <= 0) {
                    throw new RuntimeException("Selecciona la membresía a facturar.");
                }

                $stmt = $this->db->prepare(
                    "SELECT id_membresia, costo, fecha_registro, fecha_vencimiento
                     FROM membresias
                     WHERE id_membresia = ? AND id_cliente = ?
                     FOR UPDATE"
                );
                $stmt->bind_param("ii", $id_membresia, $id_cliente);
                $stmt->execute();
                $membresia = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$membresia) {
                    throw new RuntimeException("La membresía no pertenece a este cliente.");
                }

                $stmt = $this->db->prepare(
                    "SELECT 1
                     FROM factura_detalles d
                     INNER JOIN facturas f ON f.id_factura = d.id_factura
                     WHERE d.id_membresia = ? AND d.tipo = 'membresia' AND f.estado = 'pagada'"
                );
                $stmt->bind_param("i", $id_membresia);
                $stmt->execute();
                $yaFacturada = $stmt->get_result()->num_rows > 0;
                $stmt->close();

                if ($yaFacturada) {
                    throw new RuntimeException("Esta membresía ya fue facturada.");
                }

                $costo = (float) $membresia['costo'];

                $lineas[] = [
                    'tipo'            => 'membresia',
                    'id_producto'     => null,
                    'id_membresia'    => $id_membresia,
                    'descripcion'     => 'Membresía ' . $membresia['fecha_registro']
                                         . ' al ' . $membresia['fecha_vencimiento'],
                    'cantidad'        => 1,
                    'precio_unitario' => $costo,
                    'subtotal'        => $costo,
                ];

                $total += $costo;
            }

            // 3. Factura de productos: si un producto viene repetido se suman las cantidades
            $cantidades = [];

            foreach (($tipo === 'productos' ? $productos : []) as $item) {
                $id_producto = (int) ($item['id_producto'] ?? 0);
                $cantidad    = (int) ($item['cantidad'] ?? 0);

                if ($id_producto > 0 && $cantidad > 0) {
                    $cantidades[$id_producto] = ($cantidades[$id_producto] ?? 0) + $cantidad;
                }
            }

            foreach ($cantidades as $id_producto => $cantidad) {

                // FOR UPDATE bloquea la fila para que dos ventas no gasten el mismo stock
                $stmt = $this->db->prepare(
                    "SELECT p.nombre_producto,
                            p.precio_venta,
                            COALESCE(i.stock_disponible, 0) AS stock_disponible
                     FROM productos p
                     LEFT JOIN inventario i ON i.id_producto = p.id_producto
                     WHERE p.id_producto = ?
                     FOR UPDATE"
                );
                $stmt->bind_param("i", $id_producto);
                $stmt->execute();
                $producto = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$producto) {
                    throw new RuntimeException("Uno de los productos ya no existe.");
                }

                if ((int) $producto['stock_disponible'] < $cantidad) {
                    throw new RuntimeException(
                        "Stock insuficiente de \"" . $producto['nombre_producto'] . "\" (disponible: "
                        . (int) $producto['stock_disponible'] . ")."
                    );
                }

                $precio   = (float) $producto['precio_venta'];
                $subtotal = $precio * $cantidad;

                $lineas[] = [
                    'tipo'            => 'producto',
                    'id_producto'     => $id_producto,
                    'id_membresia'    => null,
                    'descripcion'     => $producto['nombre_producto'],
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal'        => $subtotal,
                ];

                $total += $subtotal;
            }

            if (empty($lineas) || $total <= 0) {
                throw new RuntimeException(
                    $tipo === 'productos'
                        ? "Agrega al menos un producto."
                        : "La factura no tiene nada que cobrar."
                );
            }

            // 4. Encabezado de la factura
            $stmt = $this->db->prepare(
                "INSERT INTO facturas (id_cliente, metodo_pago, total) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("isd", $id_cliente, $metodo_pago, $total);
            $stmt->execute();
            $id_factura = $this->db->insert_id;
            $stmt->close();

            // 5. Detalle + descuento de inventario
            $stmtDetalle = $this->db->prepare(
                "INSERT INTO factura_detalles
                    (id_factura, tipo, id_producto, id_membresia, descripcion,
                     cantidad, precio_unitario, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmtStock = $this->db->prepare(
                "UPDATE inventario
                 SET stock_disponible = stock_disponible - ?
                 WHERE id_producto = ?"
            );

            foreach ($lineas as $l) {

                $stmtDetalle->bind_param(
                    "isiisidd",
                    $id_factura,
                    $l['tipo'],
                    $l['id_producto'],
                    $l['id_membresia'],
                    $l['descripcion'],
                    $l['cantidad'],
                    $l['precio_unitario'],
                    $l['subtotal']
                );
                $stmtDetalle->execute();

                if ($l['tipo'] === 'producto') {
                    $stmtStock->bind_param("ii", $l['cantidad'], $l['id_producto']);
                    $stmtStock->execute();
                }
            }

            $stmtDetalle->close();
            $stmtStock->close();

            $this->db->commit();

            return [
                'ok'         => true,
                'mensaje'    => '¡Factura registrada correctamente!',
                'id_factura' => $id_factura,
            ];

        } catch (RuntimeException $e) {

            // Errores de validación: el mensaje es seguro para mostrar
            $this->db->rollback();

            return ['ok' => false, 'mensaje' => $e->getMessage(), 'id_factura' => null];

        } catch (Throwable $e) {

            // Errores internos: se registran, no se muestran al usuario
            $this->db->rollback();
            error_log("Error al crear factura: " . $e->getMessage());

            return [
                'ok'         => false,
                'mensaje'    => 'No se pudo registrar la factura.',
                'id_factura' => null,
            ];
        }
    }
}
