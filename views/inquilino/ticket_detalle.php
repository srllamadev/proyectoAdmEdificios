<?php
// views/inquilino/ticket_detalle.php
require_once '../../includes/functions.php';

if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    echo "No se especificó el ticket.";
    exit();
}

$ticket_id = intval($_GET['id']);
$database = new Database();
$db = $database->getConnection();

// Buscar ticket
$stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $ticket_id);
$stmt->execute();
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    echo "Ticket no encontrado.";
    exit();
}

// Datos seguros
$titulo = htmlspecialchars($ticket['titulo']);
$descripcion = nl2br(htmlspecialchars($ticket['descripcion']));
$estado = htmlspecialchars($ticket['estado']);
$imagen = $ticket['imagen'] ? '../../uploads/' . htmlspecialchars($ticket['imagen']) : '../../assets/noimage.png';
$fecha = isset($ticket['fecha_creacion']) ? formatDate($ticket['fecha_creacion'], 'd/m/Y H:i') : 'N/A';

// Si en el futuro agregas un técnico asignado, esto evita error
$tecnico = isset($ticket['tecnico_asignado']) && $ticket['tecnico_asignado']
    ? htmlspecialchars($ticket['tecnico_asignado'])
    : 'Sin asignar aún';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Ticket</title>
    <link rel="stylesheet" href="ticket_detalle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f7f9fb; margin:20px; color:#2F455C; }
        .container { max-width: 800px; margin: 0 auto; background:#fff; border-radius:10px; padding:24px; box-shadow:0 4px 10px rgba(0,0,0,0.08); }
        h1 { display:flex; align-items:center; gap:10px; font-size:1.6rem; margin-bottom:20px; }
        .ticket-img { width:100%; border-radius:8px; margin-bottom:20px; background:#eee; object-fit:cover; max-height:400px; }
        .info { margin-bottom:16px; }
        .info strong { display:inline-block; width:140px; color:#1D3557; }
        .estado { display:inline-block; padding:6px 10px; border-radius:8px; text-transform:capitalize; }
        .estado.abierto { background:#e6f9f4; color:#0b7a55; }
        .estado.en-progreso { background:#fff7e6; color:#b36b00; }
        .estado.cerrado { background:#ffe6e6; color:#a00; }
        a.volver { display:inline-block; margin-top:16px; text-decoration:none; color:#fff; background:#1DCDFE; padding:10px 14px; border-radius:8px; }
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-ticket-alt"></i> Detalle del Ticket</h1>

    <img src="<?= $imagen ?>" alt="Imagen del ticket" class="ticket-img" onerror="this.src='../../assets/noimage.png'">

    <div class="info"><strong>Título:</strong> <?= $titulo ?></div>
    <div class="info"><strong>Estado:</strong>
        <span class="estado <?= strtolower(str_replace(' ', '-', $estado)) ?>"><?= $estado ?></span>
    </div>
    <div class="info"><strong>Asignado a:</strong> <?= $tecnico ?></div>
    <div class="info"><strong>Descripción:</strong><br><?= $descripcion ?></div>
    <div class="info"><strong>Fecha:</strong> <?= $fecha ?></div>

    <a href="tickets.php" class="volver"><i class="fas fa-arrow-left"></i> Volver</a>
</div>
</body>
</html>
