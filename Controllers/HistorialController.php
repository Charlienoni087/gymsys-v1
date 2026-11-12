<?php

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/../Models/Facturas.php";

$modeloFacturas = new Facturas($conexion);

/* =====================================================
   DATOS PARA LA VISTA
===================================================== */
$listaFacturas = $modeloFacturas->obtenerFacturas();

require_once __DIR__ . "/../Views/HistorialFactura.php";
 