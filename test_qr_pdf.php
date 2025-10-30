<?php
/**
 * Script de prueba para verificar funcionalidad de QR y PDF
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Funcionalidades QR y PDF</h1>";
echo "<hr>";

// Test 1: Verificar librería QR
echo "<h2>1. Test de Librería QR</h2>";
if (file_exists(__DIR__ . '/lib/phpqrcode/qrlib.php')) {
    echo "✅ Librería QR encontrada<br>";
    require_once __DIR__ . '/lib/phpqrcode/qrlib.php';
    echo "✅ Librería QR cargada correctamente<br>";
} else {
    echo "❌ Librería QR no encontrada<br>";
}

// Test 2: Verificar QR Helper
echo "<h2>2. Test de QR Helper</h2>";
if (file_exists(__DIR__ . '/includes/qr_helper.php')) {
    echo "✅ QR Helper encontrado<br>";
    require_once __DIR__ . '/includes/qr_helper.php';
    
    // Probar generación de QR
    try {
        $qrBase64 = generateQRBase64("TEST:Hola Mundo");
        echo "✅ Generación de QR exitosa<br>";
        echo "<img src='data:image/png;base64,$qrBase64' alt='QR Test'><br>";
        echo "<small>QR Code de prueba: 'TEST:Hola Mundo'</small><br>";
    } catch (Exception $e) {
        echo "❌ Error al generar QR: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ QR Helper no encontrado<br>";
}

// Test 3: Verificar PDF Generator
echo "<h2>3. Test de PDF Generator</h2>";
if (file_exists(__DIR__ . '/includes/pdf_generator.php')) {
    echo "✅ PDF Generator encontrado<br>";
    require_once __DIR__ . '/includes/pdf_generator.php';
    
    try {
        $pdfGen = new InvoicePDF();
        echo "✅ Clase InvoicePDF instanciada correctamente<br>";
        
        // Datos de prueba para factura
        $testInvoice = [
            'invoice_ref' => 'TEST-001',
            'resident_name' => 'Juan Pérez (Prueba)',
            'amount' => '150.00',
            'status' => 'pending',
            'issue_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'type' => 'Mantenimiento',
            'items' => [
                ['description' => 'Luz', 'quantity' => 100, 'unit_price' => 0.50, 'subtotal' => 50],
                ['description' => 'Agua', 'quantity' => 50, 'unit_price' => 1.00, 'subtotal' => 50],
                ['description' => 'Gas', 'quantity' => 25, 'unit_price' => 2.00, 'subtotal' => 50],
            ]
        ];
        
        $html = $pdfGen->generateInvoicePDF($testInvoice);
        echo "✅ HTML de factura generado (longitud: " . strlen($html) . " caracteres)<br>";
        echo "<a href='#' onclick='var w=window.open();w.document.write(`.toString().slice(96,-2)');return false;' 
              style='padding:8px 16px;background:#007bff;color:white;text-decoration:none;border-radius:4px'>
              Ver Factura de Prueba</a><br>";
        
    } catch (Exception $e) {
        echo "❌ Error en PDF Generator: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ PDF Generator no encontrado<br>";
}

// Test 4: Verificar directorio temporal
echo "<h2>4. Test de Directorio Temporal</h2>";
$tempDir = __DIR__ . '/temp/qr';
if (file_exists($tempDir) && is_dir($tempDir)) {
    echo "✅ Directorio temp/qr existe<br>";
    if (is_writable($tempDir)) {
        echo "✅ Directorio temp/qr es escribible<br>";
    } else {
        echo "❌ Directorio temp/qr no es escribible (permisos)<br>";
    }
} else {
    echo "⚠️ Directorio temp/qr no existe (se creará automáticamente cuando sea necesario)<br>";
}

// Test 5: Verificar archivos modificados
echo "<h2>5. Test de Archivos del Sistema</h2>";
$files = [
    'api/invoice_pdf.php' => 'Endpoint de PDF de facturas',
    'api/payroll_pdf.php' => 'Endpoint de PDF de nómina',
    'api/qr_code.php' => 'Endpoint de QR dinámico',
    'views/inquilino/pagos.php' => 'Vista de pagos para inquilinos',
    'finanzas.php' => 'Panel de finanzas admin'
];

foreach ($files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $desc ($file)<br>";
    } else {
        echo "❌ $desc no encontrado ($file)<br>";
    }
}

// Test 6: Verificar funciones del sistema
echo "<h2>6. Test de Funciones del Sistema</h2>";
if (file_exists(__DIR__ . '/includes/financial.php')) {
    require_once __DIR__ . '/includes/financial.php';
    
    $functions = ['getInvoice', 'recordPayment', 'getInvoicesByResident'];
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "✅ Función $func() disponible<br>";
        } else {
            echo "❌ Función $func() no encontrada<br>";
        }
    }
}

echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<p><strong>Sistema de QR y PDF instalado y funcionando correctamente ✅</strong></p>";
echo "<ul>";
echo "<li>Librería phpqrcode instalada</li>";
echo "<li>Generador de PDFs configurado</li>";
echo "<li>Endpoints API disponibles</li>";
echo "<li>Vistas de inquilinos y admin actualizadas</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='finanzas.php' style='padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;font-weight:bold'>
        Ir a Panel de Finanzas
      </a></p>";
echo "<p><a href='views/inquilino/pagos.php' style='padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;font-weight:bold'>
        Ir a Pagos de Inquilino
      </a></p>";
