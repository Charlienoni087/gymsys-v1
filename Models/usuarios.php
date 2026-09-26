<?php

class Usuario {

    private mysqli $db;
    public int $id;
    public string $nombre_usuario;
    public string $correo;
    public string $contrasena;
    public string $rol;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }



    public function obtenerPorCorreo(string $correo): ?array {
        $sql = "SELECT id_usuario, nombre, correo, pass, rol FROM usuarios WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
      
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }

    public function actualizarContrasena(int $id, string $nuevoHash) {
        $sql = "UPDATE usuarios SET pass = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nuevoHash, $id);
        return $stmt->execute();
    }

    public function crearUsuario(string $nombre_usuario, string $correo, string $contrasena, string $rol) {
        $sql = "INSERT INTO usuarios (nombre, correo, pass, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $nombre_usuario, $correo, $contrasena, $rol);
        return $stmt->execute();
    }
}

?>