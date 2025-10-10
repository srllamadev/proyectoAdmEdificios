<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    // Crear departamento de prueba si no existe
    $conn->exec("INSERT IGNORE INTO departamentos (id, nombre) VALUES (1, 'Dep Prueba')");

    // Crear dispositivo y token
    $conn->exec("INSERT IGNORE INTO dispositivos (id, departamento_id, identificador, tipo, descripcion) VALUES (1, 1, 'dev-seed-1', 'medidor', 'Seed device')");
    $conn->exec("INSERT IGNORE INTO sensores (id, dispositivo_id, canal, tipo, unidad) VALUES (1, 1, 'agua_main', 'agua', 'm3')");
    $conn->exec("INSERT IGNORE INTO device_tokens (id, dispositivo_id, token, activo) VALUES (1, 1, 'TOKEN_SEED_123', 1)");

    // Insertar varias lecturas de ejemplo
    $now = new DateTime();
    $stmt = $conn->prepare('INSERT INTO lecturas (sensor_id, departamento_id, valor, tipo, recibido_en, creado_en, procesado) VALUES (:sensor_id, :departamento_id, :valor, :tipo, :recibido_en, NOW(), 0)');
    for ($i = 0; $i < 30; $i++) {
        $t = $now->modify('-5 minutes')->format('Y-m-d H:i:s');
        $val = rand(1, 20) + (rand(0, 100)/100);
        $stmt->execute([':sensor_id' => 1, ':departamento_id' => 1, ':valor' => $val, ':tipo' => 'instantaneo', ':recibido_en' => $t]);
    }

    echo "Seed completado.\n";
} catch (PDOException $e) {
    echo "Error seed: " . $e->getMessage() . "\n";
}

