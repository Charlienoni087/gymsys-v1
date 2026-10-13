<?php

// Importar los archivos necesarios
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/usuarios.php";

$modeloUsuarios = new Usuario($conexion);

$rolesPermitidos = ['administrador', 'recepcionista'];
$errorUsuario = '';


if (($_SESSION['rol'] ?? '') !== 'administrador') {
    echo "<h2>Acceso Denegado</h2><p>No tienes permisos para administrar usuarios.</p>";
    require_once __DIR__ . '/../Views/Usuarios.php';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if (isset($_POST['agregar_usuario'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $rol = $_POST['rol'] ?? '';

        if ($nombre === '' || $correo === '' || $contrasena === '' || !in_array($rol, $rolesPermitidos, true)) {
            $errorUsuario = 'Completa todos los campos para poder validar.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errorUsuario = 'El correo no tiene un formato válido.';
        } elseif ($modeloUsuarios->correoExiste($correo)) {
            $errorUsuario = 'Ya existe un usuario con ese correo.';
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $modeloUsuarios->crearUsuario($nombre, $correo, $hash, $rol);
        }
    }

    if (isset($_POST['editar_usuario'])) {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $rol = $_POST['rol'] ?? '';

        if ($id <= 0 || $nombre === '' || $correo === '' || !in_array($rol, $rolesPermitidos, true)) {
            $errorUsuario = 'Completa todos los campos para poder validar.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errorUsuario = 'El correo no tiene un formato válido.';
        } elseif ($modeloUsuarios->correoExiste($correo, $id)) {
            $errorUsuario = 'Ya existe otro usuario con ese correo.';
        } else {
            $modeloUsuarios->actualizarUsuario($id, $nombre, $correo, $rol);
        }
    }


    if (isset($_POST['cambiar_contrasena'])) {
        $id = (int) ($_POST['id_usuario'] ?? 0);
        $nuevaContrasena = $_POST['nueva_contrasena'] ?? '';

        if ($id <= 0 || strlen($nuevaContrasena) < 8) {
            $errorUsuario = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
            $modeloUsuarios->actualizarContrasena($id, $hash);
        }
    }


    if (isset($_POST['eliminar_usuario'])) {
        $id = (int) ($_POST['id_usuario'] ?? 0);

        if ($id === (int) ($_SESSION['id_usuario'] ?? 0)) {
            $errorUsuario = 'No puedes eliminar tu propio usuario.';
        } elseif ($id > 0) {
            $modeloUsuarios->eliminarUsuario($id);
        }
    }
}

$listaUsuarios = $modeloUsuarios->obtenerUsuarios();

require_once __DIR__ . '/../Views/Usuarios.php';

?>
