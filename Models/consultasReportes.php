<?php

class ConsultasReportes {
    private mysqli $db;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    private function ejecutar(string $sql, string $tipos = "", array $params = []): array {
        $stmt = $this->db->prepare($sql);
        if ($tipos !== "") {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Costos, fechas y vencimientos
    public function membresias(?string $ini, ?string $fin, ?string $estado): array {
        $sql = "SELECT c.nombre_cliente, c.cedula, m.costo, m.metodo_pago,
                       m.fecha_registro, m.fecha_vencimiento,
                       CASE WHEN m.fecha_vencimiento >= CURDATE()
                            THEN 'Vigente' ELSE 'Vencida' END AS estado
                FROM Membresias m
                INNER JOIN Clientes c ON c.id_cliente = m.id_cliente
                WHERE (? IS NULL OR m.fecha_registro >= ?)
                  AND (? IS NULL OR m.fecha_registro <= ?)
                HAVING (? IS NULL OR estado = ?)
                ORDER BY m.fecha_vencimiento ASC";
        return $this->ejecutar($sql, "ssssss", [$ini, $ini, $fin, $fin, $estado, $estado]);
    }

    // Registrados y por estado
    public function clientes(?string $ini, ?string $fin, ?string $estado): array {
        $sql = "SELECT nombre_cliente, cedula, correo, telefono, fecha_registro, estado
                FROM Clientes
                WHERE (? IS NULL OR fecha_registro >= ?)
                  AND (? IS NULL OR fecha_registro <= ?)
                  AND (? IS NULL OR estado = ?)
                ORDER BY fecha_registro DESC";
        return $this->ejecutar($sql, "ssssss", [$ini, $ini, $fin, $fin, $estado, $estado]);
    }

    // Productos y existencias (no usa rango de fechas)
    public function inventario(): array {
        $sql = "SELECT p.nombre_producto, cat.nombre_categoria,
                       p.precio_venta, i.stock_disponible
                FROM Productos p
                INNER JOIN Categorias cat ON cat.id_categoria = p.id_categoria
                INNER JOIN Inventario i ON i.id_producto = p.id_producto
                ORDER BY i.stock_disponible ASC";
        return $this->ejecutar($sql);
    }

    // Entradas y salidas de clientes
    public function asistencia(?string $ini, ?string $fin): array {
        $sql = "SELECT c.nombre_cliente, c.cedula, a.fecha_asistencia,
                       a.hora_entrada, a.hora_salida,
                       COALESCE(TIME_FORMAT(TIMEDIFF(a.hora_salida, a.hora_entrada), '%H:%i'),
                                'En el gym') AS permanencia,
                       u.nombre AS registrado_por
                FROM Asistencias a
                INNER JOIN Clientes c ON c.id_cliente = a.id_cliente
                INNER JOIN Usuarios u ON u.id_usuario = a.id_usuario
                WHERE (? IS NULL OR a.fecha_asistencia >= ?)
                  AND (? IS NULL OR a.fecha_asistencia <= ?)
                ORDER BY a.fecha_asistencia DESC, a.hora_entrada DESC";
        return $this->ejecutar($sql, "ssss", [$ini, $ini, $fin, $fin]);
    }
}