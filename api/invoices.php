<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';
require_once __DIR__ . '/../includes/functions.php';

// Protege endpoint: sólo admin o empleado
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin','empleado'])) {
    http_response_code(403);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $inv = getInvoice($_GET['id']);
        echo json_encode($inv ?: ['error'=>'Not found']);
        exit;
    }
    $status = $_GET['status'] ?? null;
    if ($status) {
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE status = :s ORDER BY created_at DESC");
        $stmt->execute([':s'=>$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM invoices ORDER BY created_at DESC LIMIT 200");
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!$payload) { http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }
    $res = createInvoice($payload);
    echo json_encode($res);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);

?>
