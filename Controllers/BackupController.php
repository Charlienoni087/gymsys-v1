<?php
session_start();

// 1. Verificación de seguridad
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /GYMSYS/index.php");
    exit;
}

$rolesPermitidos = ['administrador', 'recepcionista'];
if (!in_array($_SESSION['rol'] ?? '', $rolesPermitidos)) {
    http_response_code(403);
    die("Acceso restringido: no tienes permisos para respaldar el sistema.");
}

// 2. Si se invoca desde el switch de Principal.php, carga la vista
if (!isset($_GET['action']) || $_GET['action'] !== 'export') {
    include __DIR__ . '/../Views/backup.php'; // Ajusta la ruta a tu carpeta de vistas
    exit;
}

// 3. Proceso de Exportación (cuando action=export)
set_time_limit(300);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Contraseña de tu MySQL local
$dbName = 'gymsys'; // Nombre exacto de tu BD

// Definir ruta del ejecutable mysqldump
$mysqldumpPath = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
    ? 'C:\xampp\mysql\bin\mysqldump.exe'
    : 'mysqldump';

$tempDir = sys_get_temp_dir();
$fileName = 'GYMSYS_Backup_' . date('Ymd_His') . '.sql';
$filePath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

$passwordFlag = !empty($dbPass) ? "-p\"{$dbPass}\"" : "";
$command = "\"{$mysqldumpPath}\" -h {$dbHost} -u {$dbUser} {$passwordFlag} {$dbName} > \"{$filePath}\" 2>&1";

$output = [];
$returnVar = null;
exec($command, $output, $returnVar);

if ($returnVar !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    die("Error al generar el respaldo: " . htmlspecialchars(implode("\n", $output)));
}

// Limpiar cualquier buffer previo de PHP para evitar un .sql corrupto
if (ob_get_level()) {
    ob_end_clean();
}

// Enviar cabeceras de descarga
header('Content-Description: File Transfer');
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
flush();

readfile($filePath);

// Borrar el archivo temporal tras enviarlo
if (file_exists($filePath)) {
    unlink($filePath);
}
exit;