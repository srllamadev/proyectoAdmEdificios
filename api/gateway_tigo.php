<?php
// Mock simple de pasarela Tigo Money para demostración
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    // Esperamos invoice_ref, amount, phone
    if (empty($body['invoice_ref']) || empty($body['amount']) || empty($body['phone'])) {
        http_response_code(400); echo json_encode(['status'=>'error','message'=>'invoice_ref, amount and phone required']); exit;
    }
    // Generar tx mock
    $tx = 'TIGO-' . strtoupper(bin2hex(random_bytes(3)));
    // Guardar payment provisional (no confirmado)
    $stmt = $pdo->prepare("INSERT INTO payments (invoice_id,amount,method,gateway,tx_ref,metadata) VALUES ((SELECT id FROM invoices WHERE reference = :ref),:amount,'tigo','tigo',:tx,:meta)");
    $meta = json_encode(['phone'=>$body['phone']]);
    $stmt->execute([':ref'=>$body['invoice_ref'],':amount'=>$body['amount'],':tx'=>$tx,':meta'=>$meta]);
    echo json_encode(['status'=>'pending','tx_ref'=>$tx,'message'=>'Instrucciones enviadas al número, confirme en su app Tigo Money (mock)']);
    exit;
}

if ($method === 'GET' && !empty($_GET['tx'])) {
    $tx = $_GET['tx'];
    // Buscar payment y marcar como pagado para demo
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE tx_ref = :tx");
    $stmt->execute([':tx'=>$tx]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Tx not found']); exit; }
    // Marcar pago
    $update = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = :inv");
    $update->execute([':inv'=>$p['invoice_id']]);
    echo json_encode(['status'=>'ok','message'=>'Pago simulado confirmado','tx'=>$tx]); exit;
}

http_response_code(405); echo json_encode(['status'=>'error','message'=>'Method not allowed']);

?>
