
<?php

class Reportes {
    private mysqli $db;

    public int $id_reportes;
    public int $id_usuario;
    public string $tipo_reporte;
    public string $fecha_generacion;
    public ?string $rango_inicio = null;
    public ?string $rango_fin = null;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    // Obtener todos los reportes
    public function obtenerReportes(): array {
        $sql = "SELECT R.*, U.nombre AS nombre_usuario
                FROM Reportes R
                INNER JOIN Usuarios U
                    ON R.id_usuario = U.id_usuario
                ORDER BY R.fecha_generacion DESC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener un reporte por ID
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT * FROM Reportes
                WHERE id_reporte = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc() ?: null;
    }

    // Crear un reporte en el historial
    public function crear(
        int $id_usuario,
        string $tipo_reporte,
        ?string $rango_inicio,
        ?string $rango_fin
    ): bool {
        $sql = "INSERT INTO Reportes
                (id_usuario, tipo_reporte, rango_inicio, rango_fin)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "isss",
            $id_usuario,
            $tipo_reporte,
            $rango_inicio,
            $rango_fin
        );

        return $stmt->execute();
    }

    // Actualizar un reporte existente
    public function actualizar(
        int $id,
        int $id_usuario,
        string $tipo_reporte,
        ?string $rango_inicio,
        ?string $rango_fin
    ): bool {
        $sql = "UPDATE Reportes
                SET id_usuario = ?,
                    tipo_reporte = ?,
                    rango_inicio = ?,
                    rango_fin = ?
                WHERE id_reportes = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "isssi",
            $id_usuario,
            $tipo_reporte,
            $rango_inicio,
            $rango_fin,
            $id
        );

        return $stmt->execute();
    }

    // Eliminar un reporte del historial
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM Reportes
                WHERE id_reportes = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // Obtener reportes por tipo
    public function obtenerPorTipo(string $tipo_reporte): array {
        $sql = "SELECT R.*, U.nombre AS nombre_usuario
                FROM Reportes R
                INNER JOIN Usuarios U
                    ON R.id_usuario = U.id_usuario
                WHERE R.tipo_reporte = ?
                ORDER BY R.fecha_generacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $tipo_reporte);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>
