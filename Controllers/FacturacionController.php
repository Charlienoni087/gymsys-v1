<?php

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/Facturas.php";
require_once __DIR__ . "/../Models/membresias.php";
require_once __DIR__ . "/../Models/productos.php";

// Modelos
$modeloFacturas   = new Facturas($conexion);
$modeloMembresias = new Membresias($conexion);
$modeloProductos  = new Productos($conexion);

$toast = null;


/* =====================================================
   AGREGAR FACTURA
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_factura'])) {

    $id_cliente   = (int) ($_POST['id_cliente'] ?? 0);
    $metodo_pago  = $_POST['metodo_pago'] ?? '';
    $tipo_factura = $_POST['tipo_factura'] ?? '';
    $id_membresia = (int) ($_POST['id_membresia'] ?? 0);
    $productos    = $_POST['productos'] ?? [];

    if (!is_array($productos)) {
        $productos = [];
    }

    if ($id_cliente <= 0) {

        $_SESSION['toast'] = [
            'tipo'    => 'error',
            'mensaje' => 'Selecciona un cliente.'
        ];

    } elseif (!in_array($metodo_pago, ['efectivo', 'transferencia'], true)) {

        $_SESSION['toast'] = [
            'tipo'    => 'error',
            'mensaje' => 'El método de pago no es válido.'
        ];

    } elseif (!in_array($tipo_factura, ['membresia', 'productos'], true)) {

        $_SESSION['toast'] = [
            'tipo'    => 'error',
            'mensaje' => 'Selecciona si la factura es de membresía o de productos.'
        ];

    } else {

        $resultado = $modeloFacturas->crearFactura(
            $id_cliente,
            $metodo_pago,
            $tipo_factura,
            $id_membresia > 0 ? $id_membresia : null,
            $productos
        );

        $mensaje = $resultado['mensaje'];

        if ($resultado['ok'] && $resultado['id_factura']) {
            $mensaje = 'Factura N° ' . str_pad((string) $resultado['id_factura'], 6, '0', STR_PAD_LEFT)
                     . ' registrada. Ya aparece en el historial.';
        }

        $_SESSION['toast'] = [
            'tipo'    => $resultado['ok'] ? 'success' : 'error',
            'mensaje' => $mensaje
        ];
    }

    // Se redirige para que al recargar no se repita la factura
    header("Location:/GYMSYS/Views/Principal.php?page=facturacion");
    exit();
}


/* =====================================================
   DATOS PARA LA VISTA
===================================================== */
$listaFacturas   = $modeloFacturas->obtenerFacturas(5);   // últimas 5; el resto está en el historial
$listaClientes   = $modeloMembresias->obtenerClientes();
$listaProductos  = $modeloProductos->obtenerProductos();
$listaMembresias = $modeloFacturas->obtenerMembresiasPendientes();

require_once __DIR__ . "/../Views/facturacion.php";
