<?php
require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit();
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Loop simple: poll DB cada 2 segundos y enviar nuevas lecturas
while (true) {
    try {
        $conn = get_db_connection();
        $stmt = $conn->prepare('SELECT l.id, l.sensor_id, l.departamento_id, l.valor, l.tipo, l.recibido_en, s.tipo AS sensor_tipo FROM lecturas l LEFT JOIN sensores s ON s.id = l.sensor_id WHERE l.id > :lastId ORDER BY l.id ASC LIMIT 50');
        $stmt->execute([':lastId' => $lastId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows && count($rows) > 0) {
            foreach ($rows as $r) {
                echo "event: lectura\n";
                echo "data: " . json_encode($r) . "\n\n";
                $lastId = max($lastId, (int)$r['id']);
            }
            // enviar último id
            echo "event: last_id\n";
            echo "data: {\"last_id\": $lastId}\n\n";
            ob_flush();
            flush();
        }
    } catch (Exception $e) {
        // enviar error como evento
        echo "event: error\n";
        echo "data: " . json_encode(['message' => $e->getMessage()]) . "\n\n";
        ob_flush();
        flush();
    }

    sleep(2);
}
