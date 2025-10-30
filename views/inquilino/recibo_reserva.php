<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// 🚫 NADA DE HTML o espacios ANTES de este punto

if (!isset($_GET['id'])) {
    die('ID de reserva no especificado');
}

$database = new Database();
$db = $database->getConnection();
$reserva_id = $_GET['id'];

// ✅ Consulta corregida
$query = "SELECT r.*, ac.nombre AS area_nombre, u.name AS inquilino_nombre, u.email AS inquilino_email
          FROM reservas r
          JOIN areas_comunes ac ON r.area_comun_id = ac.id
          JOIN inquilinos i ON r.inquilino_id = i.id
          JOIN users u ON i.user_id = u.id
          WHERE r.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $reserva_id);
$stmt->execute();
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    die('Reserva no encontrada.');
}

// ✅ Generar QR
$qrText = "Reserva ID: {$reserva['id']}\nÁrea: {$reserva['area_nombre']}\nInicio: {$reserva['fecha_inicio']}";
$qrCode = new QrCode($qrText);
$writer = new PngWriter();
$qrResult = $writer->write($qrCode);
$qrBase64 = base64_encode($qrResult->getString());

// ✅ Generar HTML del PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; color: #2F455C; margin: 30px; }
h1 { color: #1DCDFE; text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 8px 12px; border: 1px solid #ccc; }
th { background-color: #E9F9FF; }
.footer { text-align: center; margin-top: 30px; font-size: 12px; color: #555; }
.qr { text-align: center; margin-top: 20px; }
</style>
</head>
<body>
<h1>Recibo de Reserva</h1>
<table>
<tr><th>Inquilino</th><td>'.$reserva['inquilino_nombre'].'</td></tr>
<tr><th>Email</th><td>'.$reserva['inquilino_email'].'</td></tr>
<tr><th>Área Reservada</th><td>'.$reserva['area_nombre'].'</td></tr>
<tr><th>Fecha de Inicio</th><td>'.$reserva['fecha_inicio'].'</td></tr>
<tr><th>Fecha de Fin</th><td>'.$reserva['fecha_fin'].'</td></tr>
<tr><th>Precio Total</th><td>$'.number_format($reserva['precio_total'], 2).'</td></tr>
<tr><th>Estado</th><td>'.ucfirst($reserva['estado']).'</td></tr>
</table>
<div class="qr">
    <img src="data:image/png;base64,'.$qrBase64.'" width="150" height="150">
</div>
<div class="footer">
    <p>Gracias por usar el sistema de reservas del condominio.</p>
</div>
</body>
</html>
';

// ✅ Configuración y render del PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ✅ Enviar al navegador
$dompdf->stream('recibo_reserva_'.$reserva_id.'.pdf', ['Attachment' => true]);
exit;
?>
