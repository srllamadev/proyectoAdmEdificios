<?php
/**
 * Endpoint para generar QR codes dinámicamente
 * Uso: qr_code.php?data=texto
 */
require_once __DIR__ . '/../includes/qr_helper.php';

$data = $_GET['data'] ?? '';
$type = $_GET['type'] ?? 'text';

if (empty($data)) {
    http_response_code(400);
    die('Parámetro data requerido');
}

// Tipos especiales
if ($type === 'invoice' && isset($_GET['ref'], $_GET['amount'], $_GET['due'])) {
    $data = "FACTURA:" . $_GET['ref'] . "|MONTO:$" . $_GET['amount'] . "|VENCE:" . $_GET['due'];
} elseif ($type === 'payroll' && isset($_GET['period'], $_GET['name'], $_GET['net'])) {
    $data = "NOMINA:" . $_GET['period'] . "|EMPLEADO:" . $_GET['name'] . "|NETO:$" . $_GET['net'];
} elseif ($type === 'url') {
    // Data ya contiene la URL
}

// Generar y mostrar QR
outputQR($data);
