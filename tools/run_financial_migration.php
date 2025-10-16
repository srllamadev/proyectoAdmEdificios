<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();
if (!$conn) {
    echo "No database connection\n";
    exit(1);
}

$sql = file_get_contents(__DIR__ . '/../db/financial_tables.sql');
$parts = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));

$i = 0;
foreach ($parts as $stmt) {
    if (empty($stmt)) continue;
    $i++;
    try {
        $conn->exec($stmt);
        echo "[OK] Statement #$i executed.\n";
    } catch (Exception $e) {
        echo "[ERROR] Statement #$i: " . $e->getMessage() . "\n";
    }
}

echo "Financial migration completed.\n";