<?php
// Evita que salidas previas bloqueen el header
ob_start();

require_once __DIR__ . '/../config/conexion.php';

$nombre_usuario = trim($_POST["nombre_usuario"] ?? '');
$correo = trim($_POST["correo"] ?? '');
$contrasena = $_POST["contrasena"] ?? '';

$rol = "administrador";

$password_hash = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, correo, pass, rol) VALUES (?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssss", $nombre_usuario, $correo, $password_hash, $rol);

if ($stmt->execute()) {
    // Usar __DIR__ garantiza que se cree exactamente en la carpeta 'instalador'
    $lock_file = __DIR__ . '/terminado.lock';
    if (file_put_contents($lock_file, '') === false) {
        die("Error al crear el archivo de bloqueo.");
    }

    header("Location: /GYMSYS/index.php");
    exit();
} else {
    echo "Error al crear el administrador: " . $conexion->error;
}

$stmt->close();
$conexion->close();