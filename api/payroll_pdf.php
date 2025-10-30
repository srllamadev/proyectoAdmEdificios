<?php
/**
 * Endpoint para generar PDF de comprobante de nómina
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_generator.php';

// Verificar que está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Obtener ID de registro de nómina o período
$payrollId = $_GET['id'] ?? null;
$period = $_GET['period'] ?? null;

$database = new Database();
$db = $database->getConnection();

// Si se especificó un ID específico
if ($payrollId) {
    $query = "SELECT p.*, e.cargo, u.name as staff_name, u.email 
              FROM payroll p 
              LEFT JOIN empleados e ON p.staff_id = e.id 
              LEFT JOIN users u ON e.user_id = u.id 
              WHERE p.id = :payroll_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':payroll_id', $payrollId);
    $stmt->execute();
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payroll) {
        // Intentar con tabla staff
        $query = "SELECT p.*, s.name as staff_name, s.email 
                  FROM payroll p 
                  LEFT JOIN staff s ON p.staff_id = s.id 
                  WHERE p.id = :payroll_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':payroll_id', $payrollId);
        $stmt->execute();
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$payroll) {
        http_response_code(404);
        die('Registro de nómina no encontrado');
    }
    
    // Verificar permisos: Admin puede ver todos, empleados solo los suyos
    if (!hasRole('admin')) {
        // Si no es admin, verificar que sea empleado y que el comprobante le pertenezca
        if (!hasRole('empleado')) {
            http_response_code(403);
            die('No tiene permisos para ver este comprobante');
        }
        
        // Verificar que el comprobante pertenece al empleado logueado
        $query = "SELECT e.id FROM empleados e WHERE e.user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$empleado || $payroll['staff_id'] != $empleado['id']) {
            http_response_code(403);
            die('No tiene permisos para ver este comprobante');
        }
    }
    
    // Preparar datos para PDF
    // La tabla payroll usa: gross, deductions, net, paid (sin sufijos _amount)
    $grossAmount = $payroll['gross'] ?? $payroll['gross_amount'] ?? 0;
    $deductionsAmount = $payroll['deductions'] ?? 0;
    $netAmount = $payroll['net'] ?? $payroll['net_amount'] ?? ($grossAmount - $deductionsAmount);
    $isPaid = !empty($payroll['paid']) || ($payroll['status'] ?? '') === 'paid';
    
    // Fecha de pago: si está pagado, usar created_at como referencia
    $paidDate = 'Pendiente';
    if ($isPaid) {
        $paidDate = $payroll['created_at'] ?? date('Y-m-d H:i:s');
        // Formatear solo la fecha
        $paidDate = date('d/m/Y', strtotime($paidDate));
    }
    
    $payrollData = [
        'period' => $payroll['period'] ?? 'N/A',
        'staff_name' => $payroll['staff_name'] ?? 'N/A',
        'gross_amount' => number_format($grossAmount, 2),
        'deductions' => number_format($deductionsAmount, 2),
        'net_amount' => number_format($netAmount, 2),
        'paid_date' => $paidDate,
        'status' => $isPaid ? 'paid' : 'pending'
    ];
    
    // Generar PDF
    $pdfGenerator = new InvoicePDF();
    $html = $pdfGenerator->generatePayrollPDF($payrollData);
    
    // Mostrar HTML (que se puede imprimir como PDF)
    echo $html;
    exit;
}

// Si se especificó un período, generar resumen para todos
if ($period && hasRole('admin')) {
    $period = preg_replace('/[^0-9A-Za-z_-]/','', $period);
    $pdf = generatePayrollPDFForPeriod($period);
    
    if ($pdf) {
        if (is_string($pdf) && substr($pdf,0,5) === '%PDF-') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="payroll_'.$period.'.pdf"');
            echo $pdf;
        } else {
            echo $pdf;
        }
        exit;
    }
}

http_response_code(400);
echo 'Parámetros inválidos. Especifique id o period';

