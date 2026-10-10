<?php

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/productos.php";

// Modelo de productos
$modeloProductos = new Productos($conexion);
$toast = null;


/* =====================================================
   AGREGAR CATEGORÍA
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_categoria'])) {

    $nombre_categoria = trim($_POST['nombre_categoria'] ?? '');

    if ($nombre_categoria !== '') {

        if ($modeloProductos->existeCategoria($nombre_categoria)) {

            $_SESSION['toast'] = [
                'tipo' => 'error',
                'mensaje' => '¡Esta categoría ya existe!'
            ];

        } else {

            $resultado = $modeloProductos->crearCategoria($nombre_categoria);

            if ($resultado) {
                $_SESSION['toast'] = [
                    'tipo' => 'success',
                    'mensaje' => '¡Categoría agregada correctamente!'
                ];
            } else {
                $_SESSION['toast'] = [
                    'tipo' => 'error',
                    'mensaje' => 'No se pudo agregar la categoría.'
                ];
            }
        }

    } else {
        $_SESSION['toast'] = [
            'tipo' => 'error',
            'mensaje' => 'Escribe el nombre de la categoría.'
        ];
    }

    header("Location: /GYMSYS/Views/Principal.php?page=productos");
    exit();
}

/* ELIMINAR CATEGORÍA */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_categoria'])) {

    $id_categoria = (int) ($_POST['id_categoria_eliminar'] ?? 0);

    if ($id_categoria > 0) {

        if ($modeloProductos->categoriaTieneProductos($id_categoria)) {

            $_SESSION['toast'] = [
                'tipo' => 'error',
                'mensaje' => 'No puedes eliminar esta categoría porque tiene productos asociados.'
            ];

        } else {

            $resultado = $modeloProductos->eliminarCategoria($id_categoria);

            if ($resultado) {
                $_SESSION['toast'] = [
                    'tipo' => 'success',
                    'mensaje' => '¡Categoría eliminada correctamente!'
                ];
            } else {
                $_SESSION['toast'] = [
                    'tipo' => 'error',
                    'mensaje' => 'No se pudo eliminar la categoría.'
                ];
            }
        }

    } else {
        $_SESSION['toast'] = [
            'tipo' => 'error',
            'mensaje' => 'La categoría seleccionada no es válida.'
        ];
    }

    header("Location: /GYMSYS/Views/Principal.php?page=productos");
    exit();
}


// Agregar prducto
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['agregar_producto'])
) {

    $nombre_producto = trim($_POST['nombre_producto'] ?? '');
    $id_categoria = (int) ($_POST['id_categoria'] ?? 0);
    $precio_venta = (float) ($_POST['precio_venta'] ?? 0);
    $stock_inicial = (int) ($_POST['stock_inicial'] ?? 0);

// validae datos
    if (
        $nombre_producto !== ''
        && $id_categoria > 0
        && $precio_venta > 0
        && $stock_inicial >= 0
    ) {


        // Verificar si el producto ya existe

       if ($modeloProductos->existeProducto($nombre_producto)) {

    $toast = [
        'tipo' => 'error',
        'mensaje' => '¡El producto ya existe!'
    ];

} else {
            // Crear producto
            $resultado = $modeloProductos->crearProducto(
                $nombre_producto,
                $id_categoria,
                $precio_venta,
                $stock_inicial
            );

            if ($resultado) {
                   $_SESSION['toast'] = [
        'tipo' => 'success',
        'mensaje' => '¡Producto agregado con éxito!'
    ];

                header(
                    "Location: /GYMSYS/Views/Principal.php?page=productos&guardado=1"
                );

                exit();

            } else {

                $mensajeError = "No se pudo guardar el producto.";
            }
        }

    } else {

        $mensajeError = "Por favor completa correctamente todos los campos.";
    }
}

// editar producto

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['editar_producto'])
) {

    $id_producto = (int) ($_POST['id_producto'] ?? 0);
    $nombre_producto = trim($_POST['nombre_producto_editar'] ?? '');
    $id_categoria = (int) ($_POST['id_categoria_editar'] ?? 0);
    $precio_venta = (float) ($_POST['precio_venta_editar'] ?? 0);
    $stock_disponible = (int) ($_POST['stock_disponible_editar'] ?? 0);

// validar datos
    if (
        $id_producto > 0
        && $nombre_producto !== ''
        && $id_categoria > 0
        && $precio_venta > 0
        && $stock_disponible >= 0
    ) {

        $resultado = $modeloProductos->editarProducto(
            $id_producto,
            $nombre_producto,
            $id_categoria,
            $precio_venta,
            $stock_disponible
        );

        if ($resultado) {
             $_SESSION['toast'] = [
        'tipo' => 'success',
        'mensaje' => '¡Producto editado con éxito!'
    ];

            header(
                "Location: /GYMSYS/Views/Principal.php?page=productos&editado=1"
            );

            exit();

        } else {

            $mensajeError = "No se pudo actualizar el producto.";
        }

    } else {

        $mensajeError = "Por favor completa correctamente todos los campos.";
    }
}

// Eliminar producto
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['eliminar_producto'])
) {

    $id_producto = (int) ($_POST['id_producto_eliminar'] ?? 0);

    if ($id_producto > 0) {

        $resultado = $modeloProductos->eliminarProducto($id_producto);

        if ($resultado) {
             $_SESSION['toast'] = [
        'tipo' => 'success',
        'mensaje' => '¡Producto eliminado con éxito!'
    ];

            header(
                "Location: /GYMSYS/Views/Principal.php?page=productos&eliminado=1"
            );

            exit();

        } else {

            $mensajeError = "No se pudo eliminar el producto.";
        }

    } else {

        $mensajeError = "Producto no válido.";
    }
}

// cargar lista de productos y categorias
$listaProductos = $modeloProductos->obtenerProductos();

$listaCategorias = $modeloProductos->obtenerCategorias();

// Cargar la vista de productos
require_once __DIR__ . "/../Views/Productos.php";

?>