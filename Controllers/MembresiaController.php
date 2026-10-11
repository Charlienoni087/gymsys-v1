
<?php

/* CONTROLADOR DE MEMBRESÍAS
   Archivo: Controllers/MembresiaController.php */

/* IMPORTAR LOS ARCHIVOS NECESARIOS */

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/membresias.php";

$modeloMembresias = new Membresias($conexion);

/* VALIDAR Y REGISTRAR UNA NUEVA MEMBRESÍA */

if ($_SERVER['REQUEST_METHOD'] === "POST"
    && isset($_POST['agregar_membresia'])) {

    $id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    $costo = filter_input(INPUT_POST, 'costo', FILTER_VALIDATE_FLOAT);
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $fecha_registro = $_POST['fecha_registro'] ?? '';
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

    $metodosPermitidos = ['efectivo', 'transferencia'];

    if (
        $id_cliente !== false && $id_cliente !== null && $id_cliente > 0
        && $costo !== false && $costo !== null && $costo > 0
        && in_array($metodo_pago, $metodosPermitidos, true)
        && $fecha_registro !== ''
        && $fecha_vencimiento !== ''
        && $fecha_vencimiento >= $fecha_registro
    ) {
        $resultado = $modeloMembresias->crear(
            $id_cliente,
            (float) $costo,
            $metodo_pago,
            $fecha_registro,
            $fecha_vencimiento
        );
    } else {
        $resultado = false;
    }

    $status = $resultado ? 'save_success' : 'error';

    header("Location: Principal.php?page=membresias&status={$status}");
    exit;
}

/* EDITAR UNA MEMBRESÍA EXISTENTE*/

if ($_SERVER['REQUEST_METHOD'] === "POST"
    && isset($_POST['editar_membresia'])) {

    $id = filter_input(INPUT_POST, 'id_membresia', FILTER_VALIDATE_INT);
    $id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    $costo = filter_input(INPUT_POST, 'costo', FILTER_VALIDATE_FLOAT);
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $fecha_registro = $_POST['fecha_registro'] ?? '';
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

    $metodosPermitidos = ['efectivo', 'transferencia'];

    if (
        $id !== false && $id !== null && $id > 0
        && $id_cliente !== false && $id_cliente !== null && $id_cliente > 0
        && $costo !== false && $costo !== null && $costo > 0
        && in_array($metodo_pago, $metodosPermitidos, true)
        && $fecha_registro !== ''
        && $fecha_vencimiento !== ''
        && $fecha_vencimiento >= $fecha_registro
    ) {
        $resultado = $modeloMembresias->actualizar(
            $id,
            $id_cliente,
            (float) $costo,
            $metodo_pago,
            $fecha_registro,
            $fecha_vencimiento
        );
    } else {
        $resultado = false;
    }

    $status = $resultado ? 'update_success' : 'error';

    header("Location: Principal.php?page=membresias&status={$status}");
    exit;
}

/* CARGAR LOS DATOS EN MODO EDICIÓN */

$en_modo_edicion = false;

$u_id = "";
$u_id_cliente = "";
$u_costo = "";
$u_metodo_pago = "";
$u_fecha_registro = "";
$u_fecha_vencimiento = "";

if (isset($_GET['editar_membresia'])) {

    $id_edit = filter_input(INPUT_GET, 'editar_membresia', FILTER_VALIDATE_INT);

    if ($id_edit !== false && $id_edit !== null && $id_edit > 0) {

        $membresia_data = $modeloMembresias->obtenerPorId($id_edit);

        if ($membresia_data) {
            $en_modo_edicion = true;

            $u_id = $membresia_data['id_membresia'];
            $u_id_cliente = $membresia_data['id_cliente'];
            $u_costo = $membresia_data['costo'];
            $u_metodo_pago = $membresia_data['metodo_pago'];
            $u_fecha_registro = $membresia_data['fecha_registro'];
            $u_fecha_vencimiento = $membresia_data['fecha_vencimiento'];
        }
    }
}

/* ELIMINAR UNA MEMBRESÍA */

if (isset($_GET['btn_eliminar']) && isset($_GET['delete_id'])) {

    $id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);

    if (
        $id !== false && $id !== null && $id > 0
        && $modeloMembresias->eliminar($id)
    ) {
        $status = 'delete_success';
    } else {
        $status = 'error';
    }

    header("Location: Principal.php?page=membresias&status={$status}");
    exit;
}

/* NOTIFICACIONES DEL MÓDULO */

$toast = null;
$status = $_GET['status'] ?? '';

switch ($status) {

    case 'save_success':
        $toast = [
            'tipo' => 'success',
            'mensaje' => '¡Membresía registrada con éxito!'
        ];
        break;

    case 'update_success':
        $toast = [
            'tipo' => 'success',
            'mensaje' => '¡Membresía actualizada con éxito!'
        ];
        break;

    case 'delete_success':
        $toast = [
            'tipo' => 'success',
            'mensaje' => '¡Membresía eliminada con éxito!'
        ];
        break;

    case 'error':
        $toast = [
            'tipo' => 'error',
            'mensaje' => 'Ocurrió un error al procesar la membresía.'
        ];
        break;
}

/* CARGAR LOS DATOS PARA LA VISTA */

$listaMembresias = $modeloMembresias->obtenerMembresias();
$listaClientes = $modeloMembresias->obtenerClientes();

/* MOSTRAR LA VISTA DE MEMBRESÍAS */

require_once __DIR__ . "/../Views/Membresias.php";

?>
