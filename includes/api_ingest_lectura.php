<?php
// Endpoint para ingestión de lecturas desde dispositivos
require_once __DIR__ . '/functions.php';

// Permitir solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Método no permitido'], 405);
}

// Leer token del header X-Device-Token
$headers = getallheaders();
$token = isset($headers['X-Device-Token']) ? $headers['X-Device-Token'] : (isset($headers['x-device-token']) ? $headers['x-device-token'] : null);

$device_id = validate_device_token($token);
if (!$device_id) {
    json_response(['error' => 'Token inválido o inactivo'], 401);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    json_response(['error' => 'JSON inválido'], 400);
}

// Validar campos mínimos
$sensor_id = isset($payload['sensor_id']) ? (int)$payload['sensor_id'] : null;
$valor = isset($payload['valor']) ? (float)$payload['valor'] : null;
$tipo = isset($payload['tipo']) ? $payload['tipo'] : 'instantaneo';
$recibido_en = isset($payload['recibido_en']) ? $payload['recibido_en'] : date('Y-m-d H:i:s');
$departamento_id = isset($payload['departamento_id']) ? (int)$payload['departamento_id'] : null;

if (empty($sensor_id) || !is_numeric($valor)) {
    json_response(['error' => 'Campos requeridos faltantes: sensor_id, valor'], 400);
}

// Seguridad adicional: verificar que el sensor pertenece al dispositivo asociado al token
try {
    $check = $conn->prepare('SELECT dispositivo_id FROM sensores WHERE id = :sensor_id LIMIT 1');
    $check->execute([':sensor_id' => $sensor_id]);
    $srow = $check->fetch(PDO::FETCH_ASSOC);
    if (!$srow) {
        json_response(['error' => 'Sensor no encontrado'], 404);
    }
    if ((int)$srow['dispositivo_id'] !== (int)$device_id) {
        json_response(['error' => 'Sensor no pertenece al dispositivo del token (prohibido)'], 403);
    }
} catch (Exception $e) {
    json_response(['error' => 'Error validando sensor', 'detail' => $e->getMessage()], 500);
}

// Insertar lectura
try {
    $conn = get_db_connection();
    $sql = "INSERT INTO lecturas (sensor_id, departamento_id, valor, tipo, recibido_en, creado_en, procesado) VALUES (:sensor_id, :departamento_id, :valor, :tipo, :recibido_en, NOW(), 0)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':sensor_id' => $sensor_id,
        ':departamento_id' => $departamento_id,
        ':valor' => $valor,
        ':tipo' => $tipo,
        ':recibido_en' => $recibido_en
    ]);
    $insertId = $conn->lastInsertId();
    json_response(['success' => true, 'lectura_id' => $insertId], 201);
} catch (Exception $e) {
    json_response(['error' => 'Error al insertar lectura', 'detail' => $e->getMessage()], 500);
}

?>