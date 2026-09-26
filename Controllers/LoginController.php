<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../Models/usuarios.php';

header('Content-Type: application/json; charset=utf-8');

$usuariosModel = new Usuario($conexion);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit();
}

$correo = trim($_POST['correo'] ?? '');
$pass = $_POST['contrasena'] ?? '';

$user = $usuariosModel->obtenerPorCorreo($correo);

if ($user && $row = $user) {
    $verificar_contrasena = password_verify($pass, $row['pass']);

    if ($verificar_contrasena) {
        session_regenerate_id(true);

        $_SESSION['id_usuario'] = (int) $row['id_usuario'];
        $_SESSION['nombre_usuario'] = $row['nombre'];
        $_SESSION['correo'] = $row['correo'];
        $_SESSION['rol'] = $row['rol'];
        $_SESSION['login_exitoso'] = true;

        unset($_SESSION['login_error']);

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión correcto.',
            'redirect' => '/GYMSYS/Views/Principal.php'
        ]);
        exit();
    }
}

$_SESSION['login_error'] = "Correo o contraseña incorrectos.";

echo json_encode([
    'success' => false,
    'message' => 'Correo o contraseña incorrectos.'
]);

exit();