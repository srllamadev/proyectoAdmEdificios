<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();
try {
    $stmt = $conn->query('SELECT COUNT(*) AS c FROM lecturas');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Lecturas: " . ($row['c'] ?? 0) . "\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
