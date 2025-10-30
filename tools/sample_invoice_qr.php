<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';

// Script demo: crea una factura ejemplo y muestra QR/descarga PDF
// Úsalo en el navegador: http://localhost/proyectoAdmEdificios/tools/sample_invoice_qr.php

// Crear factura demo
$data = [
    'resident_id' => 1,
    'items' => [
        ['description'=>'Alquiler mensual', 'qty'=>1, 'unit_price'=>350.00],
        ['description'=>'Mantenimiento', 'qty'=>1, 'unit_price'=>25.00]
    ],
    'amount' => 375.00,
    'due_date' => date('Y-m-d', strtotime('+30 days')),
    'meta' => ['type'=>'alquiler']
];

$res = createInvoice($data);
if ($res['status'] !== 'ok') {
    echo "Error creando factura demo: " . htmlspecialchars($res['message'] ?? 'unknown');
    exit;
}

$ref = $res['reference'];
$pdfUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['PHP_SELF']) . 
    '/../api/invoice_pdf.php?ref=' . urlencode($ref);

// QR via Google Charts (simple)
$qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($pdfUrl);

?><!doctype html>
<html><head><meta charset='utf-8'><title>Demo Invoice QR</title></head><body style="font-family:Arial,Helvetica,sans-serif;max-width:800px;margin:30px auto;">
<h2>Factura demo creada: <?php echo htmlspecialchars($ref); ?></h2>
<p>Monto: $<?php echo number_format($data['amount'],2); ?></p>
<p>Vencimiento: <?php echo htmlspecialchars($data['due_date']); ?></p>
<p>
  <a href="<?php echo $pdfUrl; ?>" target="_blank">Descargar PDF</a>
</p>
<p>QR para descargar la factura (apunta al PDF):</p>
<img src="<?php echo $qrUrl; ?>" alt="QR factura">
<p>Si lo deseas, escanea el QR para abrir la URL en otro dispositivo.</p>
</body></html>
