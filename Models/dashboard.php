<?php

class DashboardModel {
    private mysqli $db;
    private string $tablaMembresias = 'Membresias';

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;

        // Auto-detección defensiva de la tabla de membresías
        $check = $this->db->query("SHOW TABLES LIKE 'control_membresias'");
        if ($check && $check->num_rows > 0) {
            $this->tablaMembresias = 'control_membresias';
        }
    }

    /**
     * Total de clientes activos
     */
    public function obtenerMiembrosActivos(): int {
        $sql = "SELECT COUNT(*) AS total FROM Clientes WHERE LOWER(TRIM(estado)) = 'activo'";
        $res = $this->db->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    /**
     * Total de asistencias registradas hoy
     */
    public function obtenerAsistenciasHoy(): int {
        $sql = "SELECT COUNT(*) AS total FROM Asistencias WHERE DATE(fecha_asistencia) = CURDATE()";
        $res = $this->db->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    /**
     * Ingresos acumulados del mes en curso
     */
    public function obtenerIngresosMes(): float {
        // 1. Probar en tabla Facturacion
        $sqlFacturas = "SELECT COALESCE(SUM(costo), 0) AS total 
                        FROM Facturacion 
                        WHERE MONTH(fecha_facturacion) = MONTH(CURDATE()) 
                          AND YEAR(fecha_facturacion) = YEAR(CURDATE())";
        $res = $this->db->query($sqlFacturas);
        if ($res && $row = $res->fetch_assoc()) {
            $total = (float) $row['total'];
            if ($total > 0) {
                return $total;
            }
        }

        // 2. Si no hay facturas directas, sumar costo de membresías del mes
        $sqlMem = "SELECT COALESCE(SUM(costo), 0) AS total 
                   FROM {$this->tablaMembresias} 
                   WHERE MONTH(fecha_registro) = MONTH(CURDATE()) 
                     AND YEAR(fecha_registro) = YEAR(CURDATE())";
        $res2 = $this->db->query($sqlMem);
        if ($res2 && $row2 = $res2->fetch_assoc()) {
            return (float) $row2['total'];
        }

        return 0.00;
    }

    /**
     * Membresías por vencer en los próximos 7 días
     */
    public function obtenerTotalMembresiasPorVencer(): int {
        $sql = "SELECT COUNT(*) AS total 
                FROM {$this->tablaMembresias} 
                WHERE fecha_vencimiento >= CURDATE() 
                  AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $res = $this->db->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return (int) $row['total'];
        }
        return 0;
    }

    /**
     * Asistencias de la semana actual (Lunes a Domingo)
     */
    public function obtenerAsistenciasSemana(): array {
        $diasSemana = [
            0 => 'Lun',
            1 => 'Mar',
            2 => 'Mié',
            3 => 'Jue',
            4 => 'Vie',
            5 => 'Sáb',
            6 => 'Dom'
        ];

        $resultado = [
            'Lun' => 0,
            'Mar' => 0,
            'Mié' => 0,
            'Jue' => 0,
            'Vie' => 0,
            'Sáb' => 0,
            'Dom' => 0
        ];

        // Lunes de esta semana
        $sql = "SELECT 
                    WEEKDAY(fecha_asistencia) AS dia_idx,
                    COUNT(*) AS total
                FROM Asistencias
                WHERE fecha_asistencia >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                  AND fecha_asistencia <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)
                GROUP BY dia_idx";

        $res = $this->db->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $idx = (int) $row['dia_idx'];
                if (isset($diasSemana[$idx])) {
                    $nombreDia = $diasSemana[$idx];
                    $resultado[$nombreDia] = (int) $row['total'];
                }
            }
        }

        return $resultado;
    }

    /**
     * Lista de clientes con membresías próximas a vencer
     */
    public function obtenerMembresiasPorVencerDetallado(int $limite = 5): array {
        // Determinar columna de nombre de plan si existe
        $colNombrePlan = "COALESCE(m.nombre, m.tipo, 'Membresía')";
        if ($this->tablaMembresias === 'Membresias') {
            $colNombrePlan = "'Plan General'";
        }

        $sql = "SELECT 
                    m.id_membresia,
                    c.nombre_cliente,
                    {$colNombrePlan} AS plan,
                    m.fecha_vencimiento,
                    DATEDIFF(m.fecha_vencimiento, CURDATE()) AS dias_restantes
                FROM {$this->tablaMembresias} m
                INNER JOIN Clientes c ON m.id_cliente = c.id_cliente
                WHERE m.fecha_vencimiento >= CURDATE()
                ORDER BY m.fecha_vencimiento ASC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $res = $stmt->get_result();

        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Lista de los últimos clientes registrados
     */
    public function obtenerUltimosMiembros(int $limite = 5): array {
        $colNombrePlan = "COALESCE(m.nombre, m.tipo, 'Membresía')";
        if ($this->tablaMembresias === 'Membresias') {
            $colNombrePlan = "'General'";
        }

        $sql = "SELECT 
                    c.id_cliente,
                    c.nombre_cliente,
                    c.fecha_registro,
                    c.estado,
                    COALESCE(
                        (SELECT {$colNombrePlan}
                         FROM {$this->tablaMembresias} m 
                         WHERE m.id_cliente = c.id_cliente 
                         ORDER BY m.id_membresia DESC LIMIT 1),
                        'General'
                    ) AS plan
                FROM Clientes c
                ORDER BY c.fecha_registro DESC, c.id_cliente DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $res = $stmt->get_result();

        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

