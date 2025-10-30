<?php
/**
 * QR Code Helper
 * Funciones auxiliares para generar códigos QR en el sistema
 */

require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';

/**
 * Genera una imagen QR code y retorna la ruta del archivo
 */
function generateQRImage($data, $outputPath = null) {
    if ($outputPath === null) {
        // Crear directorio temporal si no existe
        $tmpDir = __DIR__ . '/../temp/qr';
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        $outputPath = $tmpDir . '/qr_' . md5($data . time()) . '.png';
    }
    
    QRcode::png($data, $outputPath, QR_ECLEVEL_L, 4, 2);
    return $outputPath;
}

/**
 * Genera QR code y retorna como base64 para usar en <img src="data:...">
 */
function generateQRBase64($data) {
    $tmpFile = generateQRImage($data);
    $base64 = base64_encode(file_get_contents($tmpFile));
    unlink($tmpFile); // Eliminar temporal
    return $base64;
}

/**
 * Genera QR code directamente en el navegador (headers PNG)
 */
function outputQR($data) {
    header('Content-Type: image/png');
    QRcode::png($data, false, QR_ECLEVEL_L, 4, 2);
}

/**
 * Genera QR para factura
 */
function generateInvoiceQR($invoiceRef, $amount, $dueDate, $asBase64 = true) {
    $data = "FACTURA:$invoiceRef|MONTO:$$amount|VENCE:$dueDate";
    return $asBase64 ? generateQRBase64($data) : generateQRImage($data);
}

/**
 * Genera QR para pago de nómina
 */
function generatePayrollQR($period, $employeeName, $netAmount, $asBase64 = true) {
    $data = "NOMINA:$period|EMPLEADO:$employeeName|NETO:$$netAmount";
    return $asBase64 ? generateQRBase64($data) : generateQRImage($data);
}

/**
 * Genera QR para información de contacto (vCard)
 */
function generateContactQR($name, $phone, $email, $asBase64 = true) {
    $data = "BEGIN:VCARD\nVERSION:3.0\nFN:$name\nTEL:$phone\nEMAIL:$email\nEND:VCARD";
    return $asBase64 ? generateQRBase64($data) : generateQRImage($data);
}

/**
 * Genera QR para URL
 */
function generateURLQR($url, $asBase64 = true) {
    return $asBase64 ? generateQRBase64($url) : generateQRImage($url);
}
