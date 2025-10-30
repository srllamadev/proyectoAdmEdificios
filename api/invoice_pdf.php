<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_generator.php';

// Verificar que está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Obtener ID o referencia de factura
$invoiceId = $_GET['id'] ?? $_GET['ref'] ?? null;

if (!$invoiceId) {
    http_response_code(400);
    die('ID de factura no especificado');
}

// Obtener datos de la factura
$invoice = getInvoice($invoiceId);

if (!$invoice) {
    http_response_code(404);
    die('Factura no encontrada');
}

// Verificar permisos (admin puede ver todas, inquilino solo las suyas)
if (!hasRole('admin')) {
    // Si es inquilino, verificar que la factura le pertenece
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT i.id FROM inquilinos i WHERE i.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inquilino || $invoice['resident_id'] != $inquilino['id']) {
        http_response_code(403);
        die('No tiene permisos para ver esta factura');
    }
}

// Obtener nombre del inquilino
$database = new Database();
$db = $database->getConnection();

$query = "SELECT u.name FROM inquilinos i 
          LEFT JOIN users u ON i.user_id = u.id 
          WHERE i.id = :resident_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':resident_id', $invoice['resident_id']);
$stmt->execute();
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

// Preparar datos para PDF
$invoiceData = [
    'invoice_ref' => $invoice['reference'] ?? $invoice['invoice_ref'] ?? 'N/A',
    'resident_name' => $resident['name'] ?? 'N/A',
    'amount' => number_format($invoice['amount'], 2),
    'status' => $invoice['status'] ?? 'pending',
    'issue_date' => $invoice['issue_date'] ?? $invoice['created_at'] ?? date('Y-m-d'),
    'due_date' => $invoice['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
    'type' => $invoice['type'] ?? 'General',
    'items' => []
];

// Obtener items de la factura
if (isset($invoice['items']) && is_array($invoice['items'])) {
    // Los items ya vienen en el array $invoice
    $items = $invoice['items'];
} else {
    // Obtener items de la base de datos
    $query = "SELECT * FROM invoice_items WHERE invoice_id = :invoice_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':invoice_id', $invoice['id']);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($items as $item) {
    $invoiceData['items'][] = [
        'description' => $item['description'] ?? 'Item',
        'quantity' => $item['quantity'] ?? $item['qty'] ?? 1,
        'unit_price' => $item['unit_price'] ?? 0,
        'subtotal' => $item['subtotal'] ?? ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)
    ];
}

// Generar PDF (HTML imprimible con QR)
$pdfGenerator = new InvoicePDF();
$html = $pdfGenerator->generateInvoicePDF($invoiceData);

// Mostrar HTML (que se puede imprimir como PDF desde el navegador)
echo $html;

