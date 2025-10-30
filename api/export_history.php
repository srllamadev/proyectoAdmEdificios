<?php
require_once __DIR__ . '/../includes/financial.php';

$residentId = intval($_GET['resident_id'] ?? 0);
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$type = $_GET['type'] ?? null;

if ($residentId <= 0) {
    http_response_code(400);
    echo 'resident_id requerido';
    exit;
}

$csv = exportInvoiceHistoryCsv($residentId, $from, $to, $type);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="history_resident_' . $residentId . '.csv"');
echo $csv;
exit;

?>