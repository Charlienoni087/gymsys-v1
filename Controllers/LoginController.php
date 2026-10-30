<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../Models/usuarios.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit();
}

$correo = trim($_POST['correo'] ?? '');
$pass = $_POST['contrasena'] ?? '';

if (empty($correo) || empty($pass)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor ingrese correo y contraseña.'
    ]);
    exit();
}

$usuariosModel = new Usuario($conexion);
$user = $usuariosModel->obtenerPorCorreo($correo);

// 1. Si el usuario no existe
if (!$user) {
    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    echo json_encode([
        'success' => false,
        'message' => 'Correo o contraseña incorrectos.'
    ]);
    exit();
}

// 2. Verificar si la cuenta está actualmente bloqueada
if (!empty($user['bloqueado_hasta'])) {
    $tiempoBloqueo = strtotime($user['bloqueado_hasta']);
    $ahora = time();

    if ($tiempoBloqueo > $ahora) {
        $minutosRestantes = (int) ceil(($tiempoBloqueo - $ahora) / 60);
        $mensaje = "Demasiados intentos fallidos. Tu cuenta está bloqueada temporalmente. Intenta de nuevo en $minutosRestantes minuto(s).";
        
        $_SESSION['login_error'] = $mensaje;
        echo json_encode([
            'success' => false,
            'message' => $mensaje,
            'bloqueado' => true
        ]);
        exit();
    }
}

// 3. Validar contraseña
$verificar_contrasena = password_verify($pass, $user['pass']);

if ($verificar_contrasena) {
    // Éxito: restablecer intentos y quitar bloqueo
    $usuariosModel->resetearBloqueo((int) $user['id_usuario']);

    session_regenerate_id(true);

    $_SESSION['id_usuario'] = (int) $user['id_usuario'];
    $_SESSION['nombre_usuario'] = $user['nombre'];
    $_SESSION['correo'] = $user['correo'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['login_exitoso'] = true;

    unset($_SESSION['login_error']);

    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión correcto.',
        'redirect' => '/GYMSYS/Views/Principal.php'
    ]);
    exit();
} else {
    // Error en contraseña: registrar intento fallido
    $intentosPrevios = (int) ($user['intentos_fallidos'] ?? 0);
    $resultado = $usuariosModel->registrarFallo((int) $user['id_usuario'], $intentosPrevios);

    if ($resultado['bloqueado']) {
        $mensaje = 'Has superado el límite de 5 intentos fallidos. Tu cuenta ha sido bloqueada por 15 minutos.';
    } else {
        $restantes = 5 - $resultado['nuevosIntentos'];
        $mensaje = "Contraseña incorrecta. Te quedan $restantes intento(s).";
    }

    $_SESSION['login_error'] = $mensaje;
    echo json_encode([
        'success' => false,
        'message' => $mensaje,
        'bloqueado' => $resultado['bloqueado']
    ]);
    exit();
}