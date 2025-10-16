<?php
// Script simplificado para actualizar el esquema de base de datos
require_once 'config/database.php';

echo "<h1>Actualización Simplificada de Base de Datos</h1>";
echo "<p>Este script es más robusto y no depende de columnas específicas.</p>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>✅ Conectado a la base de datos</h2>";

    // Leer y ejecutar el archivo SQL simplificado
    $sql = file_get_contents('sql/security_schema_simple.sql');

    // Ejecutar las sentencias SQL una por una
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Ejecutado: " . substr($statement, 0, 50) . "...<br>";
            } catch (PDOException $e) {
                echo "⚠️ Error en: " . substr($statement, 0, 50) . "...<br>";
                echo "   Detalles: " . $e->getMessage() . "<br>";
            }
        }
    }

    echo "<h3 style='color: green;'>✅ Proceso completado</h3>";

    // Verificar el resultado
    echo "<h3>Verificación final:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }

    $securityColumns = [
        'password_reset_token',
        'password_reset_expires',
        'failed_login_attempts',
        'locked_until',
        'last_failed_login',
        'password_changed_at',
        'account_locked'
    ];

    echo "<ul>";
    foreach ($securityColumns as $col) {
        if (in_array($col, $columns)) {
            echo "<li style='color: green;'>✅ $col</li>";
        } else {
            echo "<li style='color: red;'>❌ $col</li>";
        }
    }
    echo "</ul>";

    // Verificar tabla security_logs
    $result = $pdo->query("SHOW TABLES LIKE 'security_logs'");
    if ($result->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabla security_logs creada</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla security_logs no creada</p>";
    }

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de conexión</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>