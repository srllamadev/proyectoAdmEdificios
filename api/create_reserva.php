<?php
// api/create_reserva.php
session_start();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../includes/helpers.php';

// Asumo que inquilino está logueado y su id de inquilino está en $_SESSION['inquilino_id']
// Si no, adapta según tu sistema de sesiones.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Mapa de entrada (POST)
$area_id = intval($_POST['area_id'] ?? 0);
$start   = trim($_POST['fecha_inicio'] ?? '');
$end     = trim($_POST['fecha_fin'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$inquilino_id = $_SESSION['inquilino_id'] ?? null;

if (!$area_id || !$start || !$end || !$inquilino_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Validar intervalos
try {
    $s = new DateTime($start);
    $e = new DateTime($end);
    if ($s >= $e) throw new Exception('Fechas inválidas');
} catch (Exception $ex) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido']);
    exit;
}

// Chequear horario apertura/cierre del área
$stmt = $pdo->prepare("SELECT horario_apertura, horario_cierre FROM areas_comunes WHERE id = :id");
$stmt->execute([':id' => $area_id]);
$area = $stmt->fetch();
if (!$area) { http_response_code(404); echo json_encode(['error'=>'Área no encontrada']); exit; }

// (Opcional) validar que las horas están dentro del horario; se puede omitir si quieres permitir reservas fuera de horario
// Comprobación simple:
$hora_inicio = $s->format('H:i:s');
$hora_fin = $e->format('H:i:s');
if ($area['horario_apertura'] && $area['horario_cierre']) {
    if ($hora_inicio < $area['horario_apertura'] || $hora_fin > $area['horario_cierre']) {
        http_response_code(400);
        echo json_encode(['error'=>'Reserva fuera del horario del área']);
        exit;
    }
}

// Disponibilidad
if (!isAreaAvailable($pdo, $area_id, $start, $end)) {
    http_response_code(409);
    echo json_encode(['error' => 'El rango de fecha/hora ya está reservado']);
    exit;
}

// Calcular precio
$precio_total = calcularPrecioTotal($pdo, $area_id, $start, $end);

// Insert reserva
$insert = $pdo->prepare("INSERT INTO reservas (inquilino_id, area_comun_id, fecha_inicio, fecha_fin, estado, descripcion, precio_total, created_at)
                         VALUES (:inq, :area, :fstart, :fend, 'pendiente', :desc, :precio, NOW())");
$insert->execute([
    ':inq' => $inquilino_id,
    ':area' => $area_id,
    ':fstart' => $start,
    ':fend' => $end,
    ':desc' => $descripcion,
    ':precio' => $precio_total
]);
$reserva_id = $pdo->lastInsertId();

// Registrar pago simulado
$insPago = $pdo->prepare("INSERT INTO pagos_reservas (reserva_id, monto, estado, created_at) VALUES (:res, :monto, 'pendiente', NOW())");
$insPago->execute([':res'=>$reserva_id, ':monto'=>$precio_total]);

// Respuesta: id de reserva y pasos (link para simular pago)
echo json_encode([
    'success' => true,
    'reserva_id' => $reserva_id,
    'precio_total' => $precio_total,
    'msg' => 'Reserva creada en estado PENDIENTE. Procede a pago simulado o espera aprobación administrativa.'
]);
