<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/financial.php';

// Mock simple para pagos con tarjeta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (empty($body['invoice_ref']) || empty($body['amount']) || empty($body['card_number'])) {
        http_response_code(400); echo json_encode(['status'=>'error','message'=>'invoice_ref, amount and card_number required']); exit;
    }
    $tx = 'CARD-' . strtoupper(bin2hex(random_bytes(4)));
    // Guardar como payment provisional
    $stmt = $pdo->prepare("INSERT INTO payments (invoice_id,amount,method,gateway,tx_ref,metadata) VALUES ((SELECT id FROM invoices WHERE reference = :ref),:amount,'card','card',:tx,:meta)");
    $meta = json_encode(['card_last4'=>substr($body['card_number'],-4)]);
    $stmt->execute([':ref'=>$body['invoice_ref'],':amount'=>$body['amount'],':tx'=>$tx,':meta'=>$meta]);
    echo json_encode(['status'=>'pending','tx_ref'=>$tx,'message'=>'Pago con tarjeta iniciado (mock)','confirm_url'=>'api/gateway_card.php?tx='.$tx]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['tx'])) {
    $tx = $_GET['tx'];
    // Marcar pago como confirmado
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE tx_ref = :tx");
    $stmt->execute([':tx'=>$tx]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Tx not found']); exit; }
    $update = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = :inv");
    $update->execute([':inv'=>$p['invoice_id']]);
    echo json_encode(['status'=>'ok','message'=>'Pago con tarjeta simulado confirmado','tx'=>$tx]); exit;
}

http_response_code(405); echo json_encode(['status'=>'error','message'=>'Method not allowed']);

?>
