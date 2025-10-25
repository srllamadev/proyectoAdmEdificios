<?php
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Test de Datos de Consumos</h2>";

// Verificar tabla lecturas_consumo
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM lecturas_consumo");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ <strong>lecturas_consumo:</strong> " . $result['total'] . " registros</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en lecturas_consumo: " . $e->getMessage() . "</p>";
}

// Verificar departamentos
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM departamentos");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ <strong>departamentos:</strong> " . $result['total'] . " registros</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en departamentos: " . $e->getMessage() . "</p>";
}

// Probar query de consumo promedio
try {
    $stmt = $conn->prepare("SELECT tipo_servicio AS recurso, AVG(consumo) AS promedio FROM lecturas_consumo GROUP BY tipo_servicio");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Consumo Promedio:</h3>";
    echo "<ul>";
    foreach ($rows as $r) {
        echo "<li><strong>" . ucfirst($r['recurso']) . ":</strong> " . round($r['promedio'], 2) . "</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p>❌ Error en consumo promedio: " . $e->getMessage() . "</p>";
}

// Probar query de gráfica mensual
try {
    $stmt = $conn->prepare("SELECT DATE_FORMAT(l.fecha_lectura,'%Y-%m') AS ym, l.tipo_servicio AS recurso, SUM(l.consumo) as total FROM lecturas_consumo l WHERE l.fecha_lectura >= :since GROUP BY ym, recurso ORDER BY ym, recurso");
    $since = date('Y-m-01', strtotime('-5 months'));
    $stmt->execute([':since'=>$since]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Consumo Mensual (últimos 6 meses):</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Mes</th><th>Recurso</th><th>Total</th></tr>";
    foreach ($rows as $r) {
        echo "<tr><td>{$r['ym']}</td><td>" . ucfirst($r['recurso']) . "</td><td>" . round($r['total'], 2) . "</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>❌ Error en gráfica mensual: " . $e->getMessage() . "</p>";
}

// Probar query de tabla detallada
try {
    $stmt = $conn->prepare("SELECT d.nombre AS departamento, l.tipo_servicio AS recurso, l.consumo, l.fecha_lectura AS fecha 
                            FROM lecturas_consumo l 
                            JOIN departamentos d ON d.id = l.departamento_id 
                            ORDER BY l.fecha_lectura DESC LIMIT 10");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Últimas 10 Lecturas:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Departamento</th><th>Recurso</th><th>Consumo</th><th>Fecha</th></tr>";
    foreach ($rows as $r) {
        echo "<tr><td>{$r['departamento']}</td><td>" . ucfirst($r['recurso']) . "</td><td>" . round($r['consumo'], 2) . "</td><td>{$r['fecha']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>❌ Error en tabla detallada: " . $e->getMessage() . "</p>";
}
?>
