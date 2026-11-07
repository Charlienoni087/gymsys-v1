
<?php
/* MODELO DE MEMBRESÍAS
   Archivo: Models/membresias.php */

class Membresias {

    private mysqli $db;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    /* OBTENER TODAS LAS MEMBRESÍAS CONECTADAS CON CLIENTES */
    public function obtenerMembresias(): array {
        $sql = "SELECT
                    m.id_membresia,
                    m.id_cliente,
                    c.nombre_cliente,
                    c.cedula,
                    m.nombre,
                    m.tipo,
                    m.costo,
                    m.metodo_pago,
                    m.fecha_registro,
                    m.fecha_vencimiento,
                    m.estado
                FROM control_membresias m
                INNER JOIN clientes c
                    ON m.id_cliente = c.id_cliente
                ORDER BY m.id_membresia DESC";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            return [];
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /* OBTENER UNA MEMBRESÍA POR SU ID */
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT
                    m.id_membresia,
                    m.id_cliente,
                    c.nombre_cliente,
                    c.cedula,
                    m.nombre,
                    m.tipo,
                    m.costo,
                    m.metodo_pago,
                    m.fecha_registro,
                    m.fecha_vencimiento,
                    m.estado
                FROM control_membresias m
                INNER JOIN clientes c
                    ON m.id_cliente = c.id_cliente
                WHERE m.id_membresia = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $membresia = $resultado->fetch_assoc();

        $stmt->close();

        return $membresia ?: null;
    }

    /* OBTENER CLIENTES ACTIVOS PARA EL SELECT */
    public function obtenerClientes(): array {
        $sql = "SELECT id_cliente, nombre_cliente, cedula
                FROM clientes
                WHERE estado = 'activo'
                ORDER BY nombre_cliente ASC";

        $resultado = $this->db->query($sql);

        if (!$resultado) {
            return [];
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /* REGISTRAR UNA NUEVA MEMBRESÍA */
    public function crear(
        int $id_cliente,
        string $nombre,
        string $tipo,
        float $costo,
        string $estado,
        string $fecha_registro,
        string $fecha_vencimiento
    ): bool {
        $sql = "INSERT INTO control_membresias
                    (id_cliente, nombre, tipo, costo, metodo_pago, estado, fecha_registro, fecha_vencimiento)
                VALUES (?, ?, ?, ?, 'efectivo', ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        // Tipos: i (int), s (string), s (string), d (decimal/float), s (string), s (string), s (string)
        $stmt->bind_param(
            "issdsss",
            $id_cliente,
            $nombre,
            $tipo,
            $costo,
            $estado,
            $fecha_registro,
            $fecha_vencimiento
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /* ACTUALIZAR UNA MEMBRESÍA EXISTENTE */
    public function actualizar(
        int $id,
        int $id_cliente,
        string $nombre,
        string $tipo,
        float $costo,
        string $estado,
        string $fecha_registro,
        string $fecha_vencimiento
    ): bool {
        $sql = "UPDATE control_membresias
                SET id_cliente = ?,
                    nombre = ?,
                    tipo = ?,
                    costo = ?,
                    estado = ?,
                    fecha_registro = ?,
                    fecha_vencimiento = ?
                WHERE id_membresia = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "issdsssi",
            $id_cliente,
            $nombre,
            $tipo,
            $costo,
            $estado,
            $fecha_registro,
            $fecha_vencimiento,
            $id
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /* ELIMINAR UNA MEMBRESÍA */
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM control_membresias
                WHERE id_membresia = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /* OBTENER LA MEMBRESÍA DE UN CLIENTE (prioriza la activa, luego la de vencimiento más reciente) */
    public function obtenerPorCliente(int $id_cliente)
    {
        $sql = "SELECT m.id_membresia, m.id_cliente, m.nombre, m.tipo, m.costo,
                    m.estado, m.fecha_registro, m.fecha_vencimiento,
                    c.nombre_cliente
                FROM control_membresias m
                INNER JOIN clientes c ON c.id_cliente = m.id_cliente
                WHERE m.id_cliente = ?
                ORDER BY (m.estado = 'Activa') DESC, m.fecha_vencimiento DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }
}