<?php

class Usuario {

    private mysqli $db;

    public function __construct(mysqli $conexion) {
        $this->db = $conexion;
    }

    public function obtenerPorCorreo(string $correo): ?array {
        $sql = "SELECT id_usuario, nombre, correo, pass, rol, intentos_fallidos, bloqueado_hasta 
                FROM usuarios 
                WHERE correo = ? 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
      
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }

    public function registrarFallo(int $id_usuario, int $intentosActuales): array {
        $nuevosIntentos = $intentosActuales + 1;
        $bloqueado = false;
        $bloqueo = null;

        if ($nuevosIntentos >= 5) {
            $bloqueado = true;
            $bloqueo = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }

        $sql = "UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isi", $nuevosIntentos, $bloqueo, $id_usuario);
        $stmt->execute();

        return [
            'nuevosIntentos' => $nuevosIntentos,
            'bloqueado'      => $bloqueado
        ];
    }

    public function resetearBloqueo(int $id_usuario): bool {
        $sql = "UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
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