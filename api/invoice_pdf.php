<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';

// Permite descarga de factura en PDF o HTML público (si existe)
if (empty($_GET['ref'])) {
    http_response_code(400); echo 'Missing ref'; exit;
}
$ref = $_GET['ref'];
$inv = getInvoice($ref);
if (!$inv) { http_response_code(404); echo 'Invoice not found'; exit; }

$pdf = generateInvoicePDF($inv);

// Si Dompdf está disponible, devolvemos PDF
if (is_string($pdf) && substr($pdf,0,5) === '%PDF-') {
    header('Content-Type: application/pdf');
    $name = 'factura_' . preg_replace('/[^A-Za-z0-9_-]/','', $inv['reference']) . '.pdf';
    header('Content-Disposition: attachment; filename="' . $name . '"');
    echo $pdf; exit;
}

// Fallback: intentar generar con FPDF mínimo
$pdf2 = generateInvoicePDFWithFPDF($inv);
if ($pdf2 && is_string($pdf2) && substr($pdf2,0,5) === '%PDF-') {
    header('Content-Type: application/pdf');
    $name = 'factura_' . preg_replace('/[^A-Za-z0-9_-]/','', $inv['reference']) . '.pdf';
    header('Content-Disposition: attachment; filename="' . $name . '"');
    echo $pdf2; exit;
}

// Si no es binario PDF, mostramos HTML en navegador y ofrecemos descarga
echo $pdf;

?>
