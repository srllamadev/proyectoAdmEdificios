<?php
/**
 * PDF Generator para Facturas y Comprobantes de Pago
 * Utiliza FPDF (más ligero que TCPDF)
 */

require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';

class InvoicePDF {
    private $pdf;
    
    public function __construct() {
        // Vamos a usar FPDF simplificado inline
    }
    
    /**
     * Genera PDF de factura para inquilino
     */
    public function generateInvoicePDF($invoiceData, $output = 'D') {
        // Crear HTML para convertir a PDF
        $html = $this->generateInvoiceHTML($invoiceData);
        
        // Por ahora, retornamos HTML que se puede imprimir
        // En producción, usar biblioteca como TCPDF o DomPDF
        return $html;
    }
    
    /**
     * Genera PDF de comprobante de pago de nómina
     */
    public function generatePayrollPDF($payrollData, $output = 'D') {
        $html = $this->generatePayrollHTML($payrollData);
        return $html;
    }
    
    /**
     * Genera QR Code para factura
     */
    public function generateQRCode($data, $filename = null) {
        if ($filename === null) {
            // Generar en temp
            $tempDir = sys_get_temp_dir();
            if (!is_writable($tempDir)) {
                $tempDir = __DIR__ . '/../temp/qr';
                if (!file_exists($tempDir)) {
                    @mkdir($tempDir, 0755, true);
                }
            }
            $filename = $tempDir . '/qr_' . md5($data . time()) . '.png';
        }
        
        // Verificar si GD está disponible
        if (!extension_loaded('gd') || !function_exists('imagecreate')) {
            // Usar SVG en lugar de PNG
            $svgFile = str_replace('.png', '.svg', $filename);
            QRcode::svg($data, $svgFile, QR_ECLEVEL_L, 4, 2);
            return $svgFile;
        }
        
        // Generar QR en PNG
        QRcode::png($data, $filename, QR_ECLEVEL_L, 4, 2);
        
        return $filename;
    }
    
    /**
     * Genera HTML para factura
     */
    private function generateInvoiceHTML($invoice) {
        // Preparar datos
        $invoiceRef = $invoice['invoice_ref'] ?? 'N/A';
        $residentName = $invoice['resident_name'] ?? 'N/A';
        $amount = $invoice['amount'] ?? 0;
        $status = $invoice['status'] ?? 'pending';
        $issueDate = $invoice['issue_date'] ?? date('Y-m-d');
        $dueDate = $invoice['due_date'] ?? date('Y-m-d');
        $type = $invoice['type'] ?? 'general';
        
        // Datos del QR (puede ser URL de pago o datos de la factura)
        $qrData = "FACTURA:" . $invoiceRef . "|MONTO:$" . number_format($amount, 2) . "|VENCE:" . $dueDate;
        $qrFile = $this->generateQRCode($qrData);
        
        // Determinar si es SVG o PNG
        $isSvg = (pathinfo($qrFile, PATHINFO_EXTENSION) === 'svg');
        
        if ($isSvg) {
            // Leer SVG directamente
            $qrContent = file_get_contents($qrFile);
            unlink($qrFile);
            $qrImageTag = $qrContent;
        } else {
            // Convertir PNG a base64
            $qrBase64 = base64_encode(file_get_contents($qrFile));
            unlink($qrFile); // Eliminar archivo temporal
            $qrImageTag = '<img src="data:image/png;base64,' . $qrBase64 . '" alt="QR Code" style="width: 150px; height: 150px;">';
        }
        
        // Desglose de items
        $itemsHTML = '';
        if (isset($invoice['items']) && is_array($invoice['items'])) {
            foreach ($invoice['items'] as $item) {
                $itemsHTML .= '<tr>';
                $itemsHTML .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($item['description'] ?? '') . '</td>';
                $itemsHTML .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . htmlspecialchars($item['quantity'] ?? 1) . '</td>';
                $itemsHTML .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">$' . number_format($item['unit_price'] ?? 0, 2) . '</td>';
                $itemsHTML .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">$' . number_format($item['subtotal'] ?? 0, 2) . '</td>';
                $itemsHTML .= '</tr>';
            }
        }
        
        // Generar HTML
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura {$invoiceRef}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th { background: #007bff; color: white; padding: 10px; text-align: left; }
        .table td { border: 1px solid #ddd; padding: 8px; }
        .total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
        .qr-code { text-align: center; margin-top: 30px; }
        .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURA DE SERVICIOS</h1>
        <p>Sistema de Administración de Edificios</p>
    </div>
    
    <div class="invoice-info">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <strong>Factura N°:</strong> {$invoiceRef}<br>
                    <strong>Fecha Emisión:</strong> {$issueDate}<br>
                    <strong>Fecha Vencimiento:</strong> {$dueDate}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Cliente:</strong> {$residentName}<br>
                    <strong>Tipo:</strong> {$type}<br>
                    <strong>Estado:</strong> <span style="color: {$this->getStatusColor($status)};">{$this->getStatusLabel($status)}</span>
                </td>
            </tr>
        </table>
    </div>
    
    <h3>Detalle de Servicios</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="text-align: center;">Cantidad</th>
                <th style="text-align: right;">Precio Unitario</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            {$itemsHTML}
        </tbody>
    </table>
    
    <div class="total">
        TOTAL A PAGAR: $<span style="color: #007bff;">{$amount}</span>
    </div>
    
    <div class="qr-code">
        <p><strong>Código QR para Pago:</strong></p>
        {$qrImageTag}
        <p style="font-size: 12px; color: #666;">Escanee este código para realizar el pago</p>
    </div>
    
    <div class="footer">
        <p>Este es un documento generado electrónicamente.</p>
        <p>Para consultas, contacte con la administración del edificio.</p>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; font-size: 16px;">
            Imprimir / Guardar como PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Cerrar
        </button>
    </div>
</body>
</html>
HTML;
        
        return $html;
    }
    
    /**
     * Genera HTML para comprobante de nómina
     */
    private function generatePayrollHTML($payroll) {
        $period = $payroll['period'] ?? 'N/A';
        $staffName = $payroll['staff_name'] ?? 'N/A';
        $gross = $payroll['gross_amount'] ?? 0;
        $deductions = $payroll['deductions'] ?? 0;
        $net = $payroll['net_amount'] ?? 0;
        $paidDate = $payroll['paid_date'] ?? 'Pendiente';
        $status = $payroll['status'] ?? 'pending';
        
        // QR para comprobante
        $qrData = "NOMINA:$period|EMPLEADO:$staffName|NETO:$" . number_format($net, 2);
        $qrFile = $this->generateQRCode($qrData);
        
        // Determinar si es SVG o PNG
        $isSvg = (pathinfo($qrFile, PATHINFO_EXTENSION) === 'svg');
        
        if ($isSvg) {
            // Leer SVG directamente
            $qrContent = file_get_contents($qrFile);
            unlink($qrFile);
            $qrImageTag = $qrContent;
        } else {
            // Convertir PNG a base64
            $qrBase64 = base64_encode(file_get_contents($qrFile));
            unlink($qrFile);
            $qrImageTag = '<img src="data:image/png;base64,' . $qrBase64 . '" alt="QR Code" style="width: 150px; height: 150px;">';
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - {$period}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; background: #28a745; color: white; padding: 20px; }
        .payroll-info { margin: 20px 0; }
        .amount-box { border: 2px solid #007bff; padding: 15px; margin: 10px 0; }
        .qr-code { text-align: center; margin-top: 30px; }
        .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPROBANTE DE PAGO DE NÓMINA</h1>
        <p>Sistema de Administración de Edificios</p>
    </div>
    
    <div class="payroll-info">
        <table style="width: 100%; margin: 20px 0;">
            <tr>
                <td style="width: 50%;"><strong>Empleado:</strong> {$staffName}</td>
                <td style="width: 50%; text-align: right;"><strong>Período:</strong> {$period}</td>
            </tr>
            <tr>
                <td><strong>Fecha de Pago:</strong> {$paidDate}</td>
                <td style="text-align: right;"><strong>Estado:</strong> <span style="color: {$this->getStatusColor($status)};">{$this->getStatusLabel($status)}</span></td>
            </tr>
        </table>
    </div>
    
    <h3>Detalle de Pago</h3>
    
    <div class="amount-box" style="background: #e7f5ff;">
        <table style="width: 100%;">
            <tr>
                <td><strong>Salario Bruto:</strong></td>
                <td style="text-align: right; font-size: 18px;">$<span style="color: #007bff;">{$gross}</span></td>
            </tr>
        </table>
    </div>
    
    <div class="amount-box" style="background: #ffe7e7;">
        <table style="width: 100%;">
            <tr>
                <td><strong>Deducciones:</strong></td>
                <td style="text-align: right; font-size: 18px;">-$<span style="color: #dc3545;">{$deductions}</span></td>
            </tr>
        </table>
    </div>
    
    <div class="amount-box" style="background: #e7ffe7; border-color: #28a745; border-width: 3px;">
        <table style="width: 100%;">
            <tr>
                <td><strong style="font-size: 20px;">NETO A PAGAR:</strong></td>
                <td style="text-align: right; font-size: 24px; font-weight: bold;">$<span style="color: #28a745;">{$net}</span></td>
            </tr>
        </table>
    </div>
    
    <div class="qr-code">
        <p><strong>Código de Verificación:</strong></p>
        {$qrImageTag}
    </div>
    
    <div class="footer">
        <p>Este es un comprobante de pago generado electrónicamente.</p>
        <p>Conserve este documento para sus registros.</p>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; font-size: 16px;">
            Imprimir / Guardar como PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Cerrar
        </button>
    </div>
</body>
</html>
HTML;
        
        return $html;
    }
    
    private function getStatusColor($status) {
        switch($status) {
            case 'paid': return '#28a745';
            case 'pending': return '#ffc107';
            case 'overdue': return '#dc3545';
            default: return '#6c757d';
        }
    }
    
    private function getStatusLabel($status) {
        switch($status) {
            case 'paid': return 'PAGADO';
            case 'pending': return 'PENDIENTE';
            case 'overdue': return 'VENCIDO';
            default: return strtoupper($status);
        }
    }
}
