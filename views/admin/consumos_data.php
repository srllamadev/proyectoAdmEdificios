<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(401);
    echo json_encode([]);
    exit();
}

$conn = get_db_connection();
$stmt = $conn->prepare('SELECT l.id, l.sensor_id, l.departamento_id, l.valor, l.tipo, l.recibido_en, s.tipo AS sensor_tipo FROM lecturas l LEFT JOIN sensores s ON s.id = l.sensor_id ORDER BY l.recibido_en DESC LIMIT 20');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);

?>