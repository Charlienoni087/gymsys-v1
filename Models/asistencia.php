<?php

class Asistencias {
    private mysqli $db;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    // Obtener todas las asistencias
    public function obtenerAsistencias(): array {
        $sql = "SELECT
                    A.*,
                    C.nombre_cliente,
                    C.cedula,
                    U.nombre AS nombre_usuario
                FROM Asistencias A
                INNER JOIN Clientes C
                    ON A.id_cliente = C.id_cliente
                INNER JOIN Usuarios U
                    ON A.id_usuario = U.id_usuario
                ORDER BY
                    A.fecha_asistencia DESC,
                    A.hora_entrada DESC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Últimas asistencias registradas (para la lista de la vista)
    public function obtenerRecientes(int $limite = 10): array {
        $sql = "SELECT
                    A.*,
                    C.nombre_cliente,
                    C.cedula,
                    U.nombre AS nombre_usuario
                FROM Asistencias A
                INNER JOIN Clientes C
                    ON A.id_cliente = C.id_cliente
                INNER JOIN Usuarios U
                    ON A.id_usuario = U.id_usuario
                ORDER BY
                    A.fecha_asistencia DESC,
                    A.hora_entrada DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Clientes activos para la lista desplegable
    public function obtenerClientesActivos(): array {
        $sql = "SELECT id_cliente, nombre_cliente, cedula
                FROM Clientes
                WHERE estado = 'activo'
                ORDER BY nombre_cliente ASC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener asistencia por ID
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT
                    A.*,
                    C.nombre_cliente,
                    C.cedula,
                    U.nombre AS nombre_usuario
                FROM Asistencias A
                INNER JOIN Clientes C
                    ON A.id_cliente = C.id_cliente
                INNER JOIN Usuarios U
                    ON A.id_usuario = U.id_usuario
                WHERE A.id_asistencia = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc() ?: null;
    }

    // ¿El cliente ya tiene una entrada sin salida ese día?
    public function tieneEntradaAbierta(int $id_cliente, string $fecha): bool {
        $sql = "SELECT 1
                FROM Asistencias
                WHERE id_cliente = ?
                  AND fecha_asistencia = ?
                  AND hora_salida IS NULL
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $id_cliente, $fecha);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    // Registrar la asistencia de un cliente
    public function crear(
        int $id_cliente,
        int $id_usuario,
        string $fecha_asistencia,
        string $hora_entrada,
        ?string $hora_salida,
        ?string $observaciones
    ): bool {
        $sql = "INSERT INTO Asistencias
                (id_cliente, id_usuario, fecha_asistencia,
                 hora_entrada, hora_salida, observaciones)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "iissss",
            $id_cliente,
            $id_usuario,
            $fecha_asistencia,
            $hora_entrada,
            $hora_salida,
            $observaciones
        );

        return $stmt->execute();
    }

    // Marcar la salida de una asistencia que sigue abierta
    public function registrarSalida(int $id, string $hora_salida): bool {
        $sql = "UPDATE Asistencias
                SET hora_salida = ?
                WHERE id_asistencia = ?
                  AND hora_salida IS NULL
                  AND hora_entrada <= ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sis", $hora_salida, $id, $hora_salida);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // Actualizar salida y observaciones
    public function actualizar(
        int $id,
        ?string $hora_salida,
        ?string $observaciones
    ): bool {
        $sql = "UPDATE Asistencias
                SET hora_salida = ?,
                    observaciones = ?
                WHERE id_asistencia = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssi",
            $hora_salida,
            $observaciones,
            $id
        );

        return $stmt->execute();
    }

    // Eliminar asistencia
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM Asistencias
                WHERE id_asistencia = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    // Obtener asistencias de una fecha
    public function obtenerPorFecha(string $fecha): array {
        $sql = "SELECT
                    A.*,
                    C.nombre_cliente,
                    C.cedula,
                    U.nombre AS nombre_usuario
                FROM Asistencias A
                INNER JOIN Clientes C
                    ON A.id_cliente = C.id_cliente
                INNER JOIN Usuarios U
                    ON A.id_usuario = U.id_usuario
                WHERE A.fecha_asistencia = ?
                ORDER BY A.hora_entrada DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Contar asistencias de una fecha
    public function contarPorFecha(string $fecha): int {
        $sql = "SELECT COUNT(*)
                FROM Asistencias
                WHERE fecha_asistencia = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_row()[0];
    }
}

?>