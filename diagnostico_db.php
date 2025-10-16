<?php
// Script de diagnóstico para verificar el estado de la base de datos
require_once 'config/database.php';

echo "<h1>Diagnóstico de Base de Datos - Sistema de Edificios</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>✅ Conexión exitosa a la base de datos</h2>";

    // Verificar si la tabla users existe
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() == 0) {
        echo "<h3 style='color: red;'>❌ La tabla 'users' no existe</h3>";
        echo "<p>Necesitas ejecutar primero el archivo edificio_admin.sql para crear las tablas base.</p>";
        exit;
    }

    echo "<h3>✅ Tabla 'users' encontrada</h3>";

    // Mostrar estructura actual de la tabla users
    echo "<h3>Estructura actual de la tabla users:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verificar qué campos de seguridad faltan
    $existingColumns = [];
    $stmt = $pdo->query("DESCRIBE users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }

    $requiredColumns = [
        'password_reset_token',
        'password_reset_expires',
        'failed_login_attempts',
        'locked_until',
        'last_failed_login',
        'password_changed_at',
        'account_locked'
    ];

    echo "<h3>Campos de seguridad requeridos:</h3>";
    echo "<ul>";
    foreach ($requiredColumns as $column) {
        if (in_array($column, $existingColumns)) {
            echo "<li style='color: green;'>✅ $column - Presente</li>";
        } else {
            echo "<li style='color: red;'>❌ $column - Faltante</li>";
        }
    }
    echo "</ul>";

    // Verificar tabla security_logs
    $result = $pdo->query("SHOW TABLES LIKE 'security_logs'");
    if ($result->rowCount() > 0) {
        echo "<h3>✅ Tabla 'security_logs' existe</h3>";

        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM security_logs");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p>Total de logs de seguridad: $count</p>";
    } else {
        echo "<h3 style='color: orange;'>⚠️ Tabla 'security_logs' no existe</h3>";
    }

    // Mostrar algunos usuarios de ejemplo
    echo "<h3>Usuarios registrados:</h3>";
    $stmt = $pdo->query("SELECT id, name, email, role FROM users LIMIT 10");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>";
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h3>¿Qué hacer ahora?</h3>";
    echo "<ol>";
    echo "<li>Si faltan campos de seguridad, ejecuta: <a href='update_security_schema.php'>update_security_schema.php</a></li>";
    echo "<li>Si las contraseñas no están hasheadas, ejecuta: <a href='update_passwords.php'>update_passwords.php</a></li>";
    echo "<li>Para probar el login, ve a: <a href='login.php'>login.php</a></li>";
    echo "<li>Para probar el registro, ve a: <a href='register.php'>register.php</a></li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de conexión a la base de datos</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Soluciones posibles:</h3>";
    echo "<ul>";
    echo "<li>Asegúrate de que XAMPP esté ejecutándose (Apache y MySQL)</li>";
    echo "<li>Verifica que la base de datos 'edificio_admin' exista</li>";
    echo "<li>Revisa las credenciales en config/database.php</li>";
    echo "<li>Ejecuta el archivo edificio_admin.sql si no has creado las tablas</li>";
    echo "</ul>";
}
?>