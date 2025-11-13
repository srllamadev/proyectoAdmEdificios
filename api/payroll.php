<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','empleado'])) {
    http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (empty($body['entries']) || empty($body['period'])) {
        http_response_code(400); echo json_encode(['error'=>'entries and period required']); exit;
    }
    $res = generatePayroll($body['entries'], $body['period']);
    echo json_encode($res);
    exit;
}

if ($method === 'PATCH') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (empty($body['payroll_id'])) { http_response_code(400); echo json_encode(['error'=>'payroll_id required']); exit; }
    $res = markPayrollPaid($body['payroll_id']);
    echo json_encode($res);
    exit;
}

http_response_code(405); echo json_encode(['error'=>'Method not allowed']);

if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','empleado'])) {
    http_response_code(403); echo json_encode(['error'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (empty($body['entries']) || empty($body['period'])) {
        http_response_code(400); echo json_encode(['error'=>'entries and period required']); exit;
    }
    $res = generatePayroll($body['entries'], $body['period']);
    echo json_encode($res);
    exit;
}

if ($method === 'PATCH') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (empty($body['payroll_id'])) { http_response_code(400); echo json_encode(['error'=>'payroll_id required']); exit; }
    $res = markPayrollPaid($body['payroll_id']);
    echo json_encode($res);
    exit;
}

http_response_code(405); echo json_encode(['error'=>'Method not allowed']);

?>
