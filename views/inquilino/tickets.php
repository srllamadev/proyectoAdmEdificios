<?php
// views/inquilino/tickets.php
require_once '../../includes/functions.php';

// Verificar que está logueado y es inquilino
if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$tickets = [];

try {
    // Obtener el ID del inquilino logueado (asegurarnos de obtener la fila completa)
    $stmt = $db->prepare("
        SELECT i.id, i.user_id, u.name as user_name
        FROM inquilinos i
        JOIN users u ON i.user_id = u.id
        WHERE i.user_id = :user_id
        LIMIT 1
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inquilino) {
        // No terminar abruptamente con die(); mostramos mensaje en la vista.
        $message = 'No se encontró el inquilino asociado a su cuenta.';
    } else {
        $inquilino_id = $inquilino['id'];

        // Obtener tickets del inquilino (usar el id correcto del inquilino)
        $stmt = $db->prepare("SELECT * FROM tickets WHERE inquilino_id = :inquilino_id ORDER BY fecha_creacion DESC");
        $stmt->bindParam(':inquilino_id', $inquilino_id);
        $stmt->execute();
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $message = "Error al obtener tickets: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tickets de Mantenimiento</title>
    <!-- Mantengo la referencia a tu CSS; si no existe, la página seguirá funcionando -->
    <link rel="stylesheet" href="tickets.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos de respaldo para que no dependa exclusivamente de tickets.css */
        body { font-family: Arial, sans-serif; background:#f7f9fb; margin:20px; color:#2F455C; }
        .ticket-container { max-width:1100px; margin:0 auto; }
        h1 { display:flex; gap:10px; align-items:center; font-size:1.6rem; }
        .btn-nuevo { display:inline-block; background:#1DCDFE; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; margin:12px 0; }
        .ticket-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:16px; margin-top:16px; }
        .ticket-card { background:#fff; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.06); overflow:hidden; cursor:pointer; transition:transform .12s; display:flex; flex-direction:column; }
        .ticket-card:hover { transform:translateY(-4px); }
        .ticket-card img { width:100%; height:160px; object-fit:cover; display:block; background:#eee; }
        .ticket-info { padding:12px; display:flex; flex-direction:column; gap:8px; }
        .ticket-info h3 { margin:0; font-size:1rem; color:#2F455C; }
        .estado { font-size:0.85rem; padding:6px 8px; border-radius:6px; display:inline-block; }
        .estado.abierto { background:#e6f9f4; color:#0b7a55; }
        .estado.en-progreso { background:#fff7e6; color:#b36b00; }
        .estado.cerrado { background:#ffe6e6; color:#a00; }
        .no-tickets { color:#6c757d; padding:12px; background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
        .meta { font-size:0.9rem; color:#6c757d; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    </style>
</head>
<body>
<div class="ticket-container">
    <h1><i class="fas fa-tools"></i> Mis Tickets de Mantenimiento</h1>

    <?php if ($message): ?>
        <div style="margin:12px 0; padding:12px; background:#ffeedd; border-radius:8px; color:#6b4a00;">
            <strong>Aviso:</strong> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <a href="ticket_nuevo.php" class="btn-nuevo"><i class="fas fa-plus-circle"></i> Nuevo Ticket</a>

    <div class="ticket-grid">
        <?php if (empty($tickets)): ?>
            <div class="no-tickets">
                <p class="no-tickets"><i class="fas fa-info-circle"></i> No has registrado incidencias aún.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $t): ?>
                <?php
                // Normalizar estado a clases CSS
                $estado_class = strtolower($t['estado']);
                $estado_class = str_replace(' ', '-', $estado_class);
                // Fecha a mostrar: usar fecha_creacion si existe
                $fecha_mostrar = isset($t['fecha_creacion']) ? formatDate($t['fecha_creacion'], 'd/m/Y H:i') : (isset($t['fecha_actualizacion']) ? formatDate($t['fecha_actualizacion'], 'd/m/Y H:i') : '');
                // Ruta segura para la imagen (si no hay, mostrar imagen por defecto)
                $imagen_path = '../../uploads/' . ($t['imagen'] ? htmlspecialchars($t['imagen']) : 'noimage.png');
                ?>
                <div class="ticket-card" onclick="window.location='ticket_detalle.php?id=<?= (int)$t['id'] ?>'">
                    <img src="<?= $imagen_path ?>" alt="Imagen del ticket" onerror="this.src='../../assets/noimage.png'">
                    <div class="ticket-info">
                        <h3><?= htmlspecialchars($t['titulo']) ?></h3>
                        <div class="meta">
                            <span class="estado <?= $estado_class ?>"><?= htmlspecialchars($t['estado']) ?></span>
                            <small style="color:#6c757d;"><i class="fas fa-calendar"></i> <?= $fecha_mostrar ?></small>
                        </div>
                        <?php if (!empty($t['descripcion'])): ?>
                            <p style="margin:6px 0 0; color:#495057; font-size:0.95rem; line-height:1.3;">
                                <?= nl2br(htmlspecialchars(mb_strimwidth($t['descripcion'], 0, 160, '...'))) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
