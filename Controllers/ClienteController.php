<?php

//Importar los archivos necesarios
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/clientes.php";

$modeloClientes = new Clientes($conexion);


//Agregar clientes
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['agregar_cliente'])) {

    $nombre_cliente = $_POST['nombre_cliente'];
    $cedula =         $_POST['cedula'];
    $correo =         $_POST['correo'];
    $telefono =       $_POST['telefono'];
    $fecha_registro = $_POST['fecha_registro'];
    $estado =         $_POST['estado'];

    $resultado = $modeloClientes->crear($nombre_cliente, $cedula, $correo, $telefono, $fecha_registro, $estado);
    $status = $resultado ? 'save_success' : 'error';
    header("Location: Principal.php?page=clientes&status={$status}");
    exit;
}

//Editar clientes
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['editar_cliente'])) {
    $nombre = $_POST['nombre_cliente'];
    $cedula = $_POST['cedula'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $estado =$_POST['estado'];

    $id = intval($_POST['id_cliente']);

    $resultado = $modeloClientes->actualizar($id, $nombre, $cedula, $correo, $telefono, $estado);
    $status = $resultado ? 'update_success' : 'error';
    header("Location: Principal.php?page=clientes&status={$status}");
    exit;
}

//Metodo para cargar los datos en modo edicion
$en_modo_edicion = false;
$u_id = ""; $u_nombre = ""; $u_cedula = ""; $u_correo = ""; $u_telefono = ""; $u_fecha_registro = ""; $u_estado = "";

if(isset($_GET['editar_cliente'])) {
    $en_modo_edicion = true;

    $id_edit = intval($_GET['editar_cliente']);
    $cliente_data = $modeloClientes->obtenerPorId($id_edit);

    if ($cliente_data) {
        $u_id =             $cliente_data['id_cliente'];
        $u_nombre =         $cliente_data['nombre_cliente'];
        $u_cedula =         $cliente_data['cedula'];
        $u_correo =         $cliente_data['correo'];
        $u_telefono =       $cliente_data['telefono'];
        $u_fecha_registro = $cliente_data['fecha_registro'];
        $u_estado =         $cliente_data['estado'];
    }
}

//Eliminar clientes
if(isset($_GET['btn_eliminar']) && isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    if ($id > 0 && $modeloClientes->eliminar($id)) {
            header("Location: Principal.php?page=clientes&status=delete_success");
    } 
    else {
        header("Location: Principal.php?page=clientes&status=error");
    }
    exit;

}

$toast = null;
$status = $_GET['status'] ?? '';

switch ($status) {
    case 'save_success':
        $toast = ['tipo' => 'success', 'mensaje' => '¡Cliente guardado con éxito!'];
        break;
    case 'update_success':
        $toast = ['tipo' => 'success', 'mensaje' => '¡Cliente actualizado con éxito!'];
        break;
    case 'delete_success':
        $toast = ['tipo' => 'success', 'mensaje' => '¡Cliente eliminado con éxito!'];
        break;
    case 'error':
        $toast = ['tipo' => 'error', 'mensaje' => 'Ocurrió un error al procesar la solicitud del cliente.'];
        break;
}

//Variable que carga los datos de los clientes
$listaClientes = $modeloClientes ->obtenerClientes();

require_once __DIR__ .'/../Views/Clientes.php';


?>