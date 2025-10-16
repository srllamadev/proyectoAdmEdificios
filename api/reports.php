<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','empleado'])) {
    http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit;
}

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-t');

$res = reportIncomeExpenses($from, $to);
echo json_encode($res);

?>
