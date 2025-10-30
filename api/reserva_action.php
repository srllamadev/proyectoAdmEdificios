<?php
// admin/reserva_action.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// validación de rol admin (ajusta según tu sistema)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403); echo json_encode(['error'=>'No autorizado']); exit;
}

$reserva_id = intval($_POST['reserva_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$reserva_id || !in_array($action, ['aprobar','rechazar','cancelar'])) {
    http_response_code(400); echo json_encode(['error'=>'Params invalidos']); exit;
}

if ($action === 'aprobar') {
    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE reservas SET estado='confirmada', updated_at = NOW() WHERE id = :id")->execute([':id'=>$reserva_id]);
        // generar QR
        $qrContent = "reserva:{$reserva_id}";
        $qrPath = generarQRParaReserva($reserva_id, $qrContent);
        $pdo->prepare("UPDATE reservas SET codigo_qr = :qr WHERE id = :id")->execute([':qr'=>$qrPath, ':id'=>$reserva_id]);
        $pdo->commit();
        echo json_encode(['success'=>true,'msg'=>'Reserva aprobada']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500); echo json_encode(['error'=>'Error: '.$e->getMessage()]);
    }
} elseif ($action === 'rechazar' || $action === 'cancelar') {
    $estado = ($action === 'rechazar') ? 'cancelada' : 'cancelada';
    $pdo->prepare("UPDATE reservas SET estado = :estado, updated_at = NOW() WHERE id = :id")->execute([':estado'=>$estado, ':id'=>$reserva_id]);
    echo json_encode(['success'=>true,'msg'=>'Reserva '.$estado]);
}
