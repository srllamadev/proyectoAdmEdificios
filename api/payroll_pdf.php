<?php
require_once __DIR__ . '/../includes/financial.php';

if (!isset($_GET['period'])) {
    http_response_code(400);
    echo 'Periodo requerido';
    exit;
}
$period = preg_replace('/[^0-9A-Za-z_-]/','', $_GET['period']);
$pdf = generatePayrollPDFForPeriod($period);
if (!$pdf) {
    echo "No hay datos para el periodo " . htmlspecialchars($period);
    exit;
}
// Devolver como PDF para descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="planilla_' . $period . '.pdf"');
echo $pdf;
exit;

?><?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','empleado'])) {
    http_response_code(403); echo 'Unauthorized'; exit;
}

if (empty($_GET['period'])) { echo 'Period required'; exit; }
$period = $_GET['period'];
$pdf = generatePayrollPDFForPeriod($period);

if (is_string($pdf) && substr($pdf,0,5) === '%PDF-') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="payroll_'.preg_replace('/[^A-Za-z0-9_-]/','',$period).'.pdf"');
    echo $pdf; exit;
}

echo $pdf;

?>
