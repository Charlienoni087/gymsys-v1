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

    public function crear(string $nombre_cliente, string $cedula, string $correo, string $telefono, string $fecha_registro, string $estado) {
        $sql = "INSERT INTO clientes (nombre_cliente, cedula, correo, telefono, fecha_registro, estado) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", $nombre_cliente, $cedula, $correo, $telefono, $fecha_registro, $estado);
        return $stmt->execute();
        
    }
}

?>