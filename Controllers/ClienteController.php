<?php

//Importar los archivos necesarios
require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/clientes.php";

$modeloClientes = new Clientes($conexion);

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['agregar_cliente'])) {
    
}

$listaClientes = $modeloClientes ->obtenerClientes();

require_once __DIR__ .'/../Views/Clientes.php';


?>