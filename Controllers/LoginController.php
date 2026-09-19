<?php
require_once '../config/config.php';
require_once '../Models/usuarios.php';

$modeloUsuarios = new Usuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $modeloUsuarios->Login($email, $password, $_POST['rol']);

    if ($user) {
        // Iniciar sesión y redirigir al usuario a la página principal
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        header('Location: ../index.php');
        exit();
    } else {
        // Credenciales inválidas, mostrar mensaje de error
        echo "Correo electrónico o contraseña incorrectos.";
    }
}
?>