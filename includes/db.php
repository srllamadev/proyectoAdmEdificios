<?php
// DB wrapper que usa la clase Database en config/database.php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$pdo = $db->getConnection();

if (!$pdo) {
    // En entornos de desarrollo puede ser útil ver el error
    http_response_code(500);
    echo "Error de conexión a la base de datos. Revisa config/database.php";
    exit;
}

// $pdo disponible para los includes que lo requieran

?>
