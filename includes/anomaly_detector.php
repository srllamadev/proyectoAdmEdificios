<?php
// Detector de anomalías (ejecutar por cron cada X minutos)
require_once __DIR__ . '/functions.php';

// Configuración básica
$now = new DateTime();

try {
    $conn = get_db_connection();

    // Cargar umbrales activos
    $umbrales = get_active_umbrales();

    foreach ($umbrales as $u) {
        $sensor_id = $u['sensor_id'];
        $departamento_id = $u['departamento_id'];
        $tipo_alerta = $u['tipo_alerta'];
        $valor_umbr = (float)$u['valor'];
        $ventana = (int)$u['ventana_minutos'];

        // Construir rango de tiempo
        $desde = $now->modify("-{$ventana} minutes")->format('Y-m-d H:i:s');
        $now = new DateTime(); // reset $now

        // Si hay sensor específico, filtrar por sensor, si no, por departamento
        $where = [];
        $params = [':desde' => $desde];
        $sql = "SELECT * FROM lecturas WHERE recibido_en >= :desde";
        if (!empty($sensor_id)) {
            $sql .= " AND sensor_id = :sensor_id";
            $params[':sensor_id'] = $sensor_id;
        }
        if (!empty($departamento_id)) {
            $sql .= " AND departamento_id = :departamento_id";
            $params[':departamento_id'] = $departamento_id;
        }

        $stmt = $conn->prepare($sql . " ORDER BY recibido_en ASC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) === 0) continue;

        // Calcular métricas simples
        $valores = array_column($rows, 'valor');
        $avg = array_sum($valores) / count($valores);
        $min = min($valores);
        $max = max($valores);

        // Regla: consumo alto
        if ($tipo_alerta === 'consumo_alto') {
            if ($avg > $valor_umbr) {
                $mensaje = "Consumo promedio en los últimos {$ventana} minutos = {$avg} > umbral ({$valor_umbr})";
                create_alert($departamento_id ?? $rows[0]['departamento_id'], $sensor_id ?? $rows[0]['sensor_id'], 'consumo_alto', $mensaje, 'media', ['avg' => $avg, 'min' => $min, 'max' => $max]);
            }
        }

        // Regla: posible fuga (pendiente positiva sostenida o aumentos bruscos)
        if ($tipo_alerta === 'posible_fuga') {
            // calcular pendiente aproximada: (ultimo - primero) / n
            $first = (float)$valores[0];
            $last = (float)$valores[count($valores)-1];
            $slope = ($last - $first) / max(1, count($valores));
            // umbral interpretado como pendiente mínima significativa o incremento
            if ($slope > $valor_umbr) {
                $mensaje = "Posible fuga: pendiente {$slope} en los últimos {$ventana} minutos (> {$valor_umbr})";
                create_alert($departamento_id ?? $rows[0]['departamento_id'], $sensor_id ?? $rows[0]['sensor_id'], 'posible_fuga', $mensaje, 'alta', ['slope' => $slope, 'first' => $first, 'last' => $last]);
            }
        }
    }

    // Marcar lecturas procesadas opcionalmente (se puede optimizar)
    // Por ahora no marcamos globalmente para no interferir en re-ejecuciones

} catch (Exception $e) {
    // En caso de error, log básico
    error_log('Error en anomaly_detector: ' . $e->getMessage());
    exit(1);
}

?>