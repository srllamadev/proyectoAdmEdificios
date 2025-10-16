<?php
// Script para ejecutar la migración SQL del módulo de consumos
// Uso: desde PowerShell ejecutar: C:\xampp\php\php.exe "C:\xampp\htdocs\proyectoAdmEdificios\tools\run_migrations.php"

require_once __DIR__ . '/../config/database.php';

$sqlFile = __DIR__ . '/../sql/consumos_tables.sql';
if (!file_exists($sqlFile)) {
    echo "Archivo de migración no encontrado: $sqlFile\n";
    exit(1);
}

$db = new Database();
$conn = $db->getConnection();
if (!$conn) {
    echo "No se pudo conectar a la base de datos. Revisa config/database.php\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Error leyendo el archivo SQL.\n";
    exit(1);
}

// Dividir por ; y ejecutar cada sentencia (simple parser, suficiente para DDL simple)
$parts = preg_split('/;\s*\n/', $sql);
$i = 0;
foreach ($parts as $part) {
    $stmt = trim($part);
    if ($stmt === '') continue;
    $i++;
    try {
        $conn->exec($stmt);
        echo "[OK] Sentencia #$i ejecutada.\n";
    } catch (PDOException $e) {
        echo "[ERROR] Sentencia #$i: " . $e->getMessage() . "\n";
    }
}

echo "Migración terminada. Verifica las tablas en la base de datos.\n";
