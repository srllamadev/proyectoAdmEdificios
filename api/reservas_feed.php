<?php
// api/reservas_feed.php
require_once __DIR__.'/../config/db.php';

$area = $_GET['area'] ?? null; // opcional: filtrar por area_id

$sql = "SELECT r.id, r.fecha_inicio AS start, r.fecha_fin AS end, r.estado, r.descripcion, a.nombre AS area_nombre, u.name AS inquilino
        FROM reservas r
        JOIN areas_comunes a ON r.area_comun_id = a.id
        JOIN inquilinos i ON r.inquilino_id = i.id
        JOIN users u ON i.user_id = u.id
        WHERE 1=1";

$params = [];
if ($area) {
    $sql .= " AND r.area_comun_id = :area";
    $params[':area'] = $area;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$events = [];
foreach ($rows as $r) {
    $title = "{$r['area_nombre']} · {$r['inquilino']}";
    $color = ($r['estado'] === 'confirmada') ? '#28a745' : (($r['estado'] === 'pendiente') ? '#ffc107' : '#6c757d');
    $events[] = [
        'id' => $r['id'],
        'title' => $title,
        'start' => $r['start'],
        'end' => $r['end'],
        'color' => $color,
        'extendedProps' => [
            'estado' => $r['estado'],
            'descripcion' => $r['descripcion'],
        ],
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($events);
