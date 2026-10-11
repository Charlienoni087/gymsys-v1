
<?php

/* MODELO DE MEMBRESÍAS
   Archivo: Models/membresias.php */

class Membresias {

    /* CONEXIÓN A LA BASE DE DATOS*/

    private mysqli $db;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    /* OBTENER TODAS LAS MEMBRESÍAS
       Incluye nombre y cédula del cliente */

    public function obtenerMembresias(): array {

        $sql = "SELECT
                    m.id_membresia,
                    m.id_cliente,
                    c.nombre_cliente,
                    c.cedula,
                    m.costo,
                    m.metodo_pago,
                    m.fecha_registro,
                    m.fecha_vencimiento
                FROM membresias m
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
                    m.costo,
                    m.metodo_pago,
                    m.fecha_registro,
                    m.fecha_vencimiento
                FROM membresias m
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

    /* OBTENER CLIENTES ACTIVOS
       Se utilizan en el formulario de membresías*/

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
        float $costo,
        string $metodo_pago,
        string $fecha_registro,
        string $fecha_vencimiento
    ): bool {

        $sql = "INSERT INTO membresias
                    (id_cliente, costo, metodo_pago,
                     fecha_registro, fecha_vencimiento)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "idsss",
            $id_cliente,
            $costo,
            $metodo_pago,
            $fecha_registro,
            $fecha_vencimiento
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    /* ACTUALIZAR UNA MEMBRESÍA EXISTENTE*/

    public function actualizar(
        int $id,
        int $id_cliente,
        float $costo,
        string $metodo_pago,
        string $fecha_registro,
        string $fecha_vencimiento
    ): bool {

        $sql = "UPDATE membresias
                SET id_cliente = ?,
                    costo = ?,
                    metodo_pago = ?,
                    fecha_registro = ?,
                    fecha_vencimiento = ?
                WHERE id_membresia = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "idsssi",
            $id_cliente,
            $costo,
            $metodo_pago,
            $fecha_registro,
            $fecha_vencimiento,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    /* ELIMINAR UNA MEMBRESÍA*/

    public function eliminar(int $id): bool {

        $sql = "DELETE FROM membresias
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
}

?>
