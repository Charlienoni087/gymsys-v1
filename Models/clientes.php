<?php

class Clientes {
    private mysqli $db;

    public int $id;
    public string $nombre_cliente;
    public string $cedula;
    public string $correo;
    public string $telefono;
    public string $fecha_registro;
    public string $estado;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    public function obtenerClientes() {
        $sql = "SELECT * FROM clientes";
        $resultado = $this->db->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId(int $id) {
        $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            die("Error prepare: " . $this->db->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
        
    }

    public function crear(string $nombre_cliente, string $cedula, string $correo, string $telefono, string $fecha_registro, string $estado) {
        $sql = "INSERT INTO clientes (nombre_cliente, cedula, correo, telefono, fecha_registro, estado) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", $nombre_cliente, $cedula, $correo, $telefono, $fecha_registro, $estado);
        return $stmt->execute();
        
    }

    public function actualizar(int $id, string $nombre_cliente, string $cedula, string $correo, string $telefono, string $estado) {
        $sql = "UPDATE clientes SET nombre_cliente = ?, cedula = ?, correo = ?, telefono = ?, estado = ? 
        WHERE id_cliente = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssi", $nombre_cliente, $cedula, $correo, $telefono, $estado, $id);
        return $stmt->execute();
    }

    public function eliminar(int $id) {
        $sql = "DELETE FROM clientes WHERE id_cliente = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

?>