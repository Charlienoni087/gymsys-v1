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

    public function obtenerUsuarios(): array {
        $sql = "SELECT id_usuario, nombre, correo, rol FROM usuarios ORDER BY nombre";
        $resultado = $this->db->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT id_usuario, nombre, correo, rol FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc() ?: null;
    }

    public function actualizarUsuario(int $id, string $nombre_usuario, string $correo, string $rol) {
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $nombre_usuario, $correo, $rol, $id);
        return $stmt->execute();
    }

    public function eliminarUsuario(int $id) {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function correoExiste(string $correo, ?int $excluirId = null): bool {
        if ($excluirId !== null) {
            $sql = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $correo, $excluirId);
        } else {
            $sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $correo);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}

?>