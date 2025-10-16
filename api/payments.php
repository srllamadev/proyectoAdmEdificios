<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','empleado'])) {
    http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['invoice_id']) || !isset($body['amount'])) {
    http_response_code(400); echo json_encode(['error'=>'invoice_id and amount required']); exit;
}

$res = recordPayment($body['invoice_id'], $body['amount'], $body['method'] ?? 'manual', $body['gateway'] ?? null, $body['tx_ref'] ?? null, $body['metadata'] ?? null);
echo json_encode($res);

?>
