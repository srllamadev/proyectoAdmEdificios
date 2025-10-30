<?php
// api/simulate_payment.php
session_start();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../includes/helpers.php';

$reserva_id = intval($_POST['reserva_id'] ?? 0);
$metodo = $_POST['metodo'] ?? 'simulado';

if (!$reserva_id) { http_response_code(400); echo json_encode(['error'=>'reserva_id requerido']); exit; }

// Marcar pago
$stmt = $pdo->prepare("UPDATE pagos_reservas SET estado='pagado', fecha_pago = NOW(), metodo_pago = :metodo, updated_at = NOW() WHERE reserva_id = :res");
$stmt->execute([':metodo'=>$metodo, ':res'=>$reserva_id]);

// Opcional: confirmar reserva automáticamente después del pago
$auto_confirm = true; // cambia según política
if ($auto_confirm) {
    // generar codigo qr y cambiar estado a confirmada
    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE reservas SET estado='confirmada', updated_at = NOW() WHERE id = :id")->execute([':id'=>$reserva_id]);

        // generar QR: contenido mínimo con id
        $qrContent = "reserva:{$reserva_id}";
        $qrPath = generarQRParaReserva($reserva_id, $qrContent);
        // actualizar ruta en tabla
        $upd = $pdo->prepare("UPDATE reservas SET codigo_qr = :qr WHERE id = :id");
        $upd->execute([':qr' => $qrPath, ':id' => $reserva_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error al confirmar reserva: ' . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => true, 'msg' => 'Pago registrado y reserva confirmada (si aplica).']);
