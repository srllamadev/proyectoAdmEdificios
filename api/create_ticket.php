<?php
// api/create_ticket.php
session_start();
require_once __DIR__ . '/../config/db.php';

// asumo que $_SESSION['inquilino_id'] existe
$inquilino_id = $_SESSION['inquilino_id'] ?? null;
if (!$inquilino_id) { http_response_code(403); echo json_encode(['error'=>'No autenticado']); exit; }

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');

if (!$titulo) { http_response_code(400); echo json_encode(['error'=>'Título requerido']); exit; }

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO tickets (inquilino_id, titulo, descripcion, categoria, estado, created_at) VALUES (:inq,:titulo,:desc,:cat,'abierto',NOW())");
    $stmt->execute([':inq'=>$inquilino_id, ':titulo'=>$titulo, ':desc'=>$descripcion, ':cat'=>$categoria]);
    $ticket_id = $pdo->lastInsertId();

    // manejo adjunto
    if (!empty($_FILES['archivo']) && $_FILES['archivo']['error'] == UPLOAD_ERR_OK) {
        $u = $_FILES['archivo'];
        $allowed = ['image/jpeg','image/png','image/webp'];
        if (!in_array($u['type'],$allowed)) throw new Exception('Tipo de archivo no permitido');
        $ext = pathinfo($u['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../assets/uploads/tickets';
        if (!is_dir($dir)) mkdir($dir,0755,true);
        $dest = $dir . "/ticket_{$ticket_id}_" . time() . "." . $ext;
        if (!move_uploaded_file($u['tmp_name'], $dest)) throw new Exception('Error al mover archivo');
        $pdo->prepare("INSERT INTO ticket_attachments (ticket_id, filename, path, created_at) VALUES (:t,:f,:p,NOW())")
            ->execute([':t'=>$ticket_id, ':f'=>basename($dest), ':p'=> 'assets/uploads/tickets/'.basename($dest)]);
    }

    $pdo->commit();
    echo json_encode(['success'=>true,'ticket_id'=>$ticket_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
