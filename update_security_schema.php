<?php
// Script para actualizar el esquema de base de datos con funcionalidades de seguridad
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Actualizando esquema de base de datos para funcionalidades de seguridad...</h2>";

    // Agregar campos de seguridad para recuperación de contraseña
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(255) NULL AFTER remember_token";
    $pdo->exec($sql);
    echo "✓ Campo password_reset_token agregado<br>";

    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_expires TIMESTAMP NULL AFTER password_reset_token";
    $pdo->exec($sql);
    echo "✓ Campo password_reset_expires agregado<br>";

    // Agregar campos para bloqueo de cuenta por intentos fallidos
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0 AFTER password_reset_expires";
    $pdo->exec($sql);
    echo "✓ Campo failed_login_attempts agregado<br>";

    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL AFTER failed_login_attempts";
    $pdo->exec($sql);
    echo "✓ Campo locked_until agregado<br>";

    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_failed_login TIMESTAMP NULL AFTER locked_until";
    $pdo->exec($sql);
    echo "✓ Campo last_failed_login agregado<br>";

    // Agregar campos para política de contraseñas
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP NULL AFTER last_failed_login";
    $pdo->exec($sql);
    echo "✓ Campo password_changed_at agregado<br>";

    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS account_locked TINYINT(1) DEFAULT 0 AFTER password_changed_at";
    $pdo->exec($sql);
    echo "✓ Campo account_locked agregado<br>";

    // Actualizar registros existentes
    $sql = "UPDATE users SET
        failed_login_attempts = 0,
        account_locked = 0,
        password_changed_at = COALESCE(password_changed_at, created_at)
        WHERE failed_login_attempts IS NULL";
    $pdo->exec($sql);
    echo "✓ Registros existentes actualizados<br>";

    // Crear tabla de logs de seguridad
    $sql = "CREATE TABLE IF NOT EXISTS security_logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NULL,
        action VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        details TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_user_id (user_id),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✓ Tabla security_logs creada<br>";

    // Insertar logs iniciales
    $sql = "INSERT IGNORE INTO security_logs (user_id, action, details, created_at) VALUES
        (1, 'account_created', 'Cuenta de administrador creada durante setup inicial', NOW()),
        (2, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
        (3, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
        (4, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
        (5, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
        (6, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
        (7, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
        (8, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
        (9, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW())";
    $pdo->exec($sql);
    echo "✓ Logs de seguridad iniciales insertados<br>";

    echo "<h3 style='color: green;'>✅ Actualización completada exitosamente!</h3>";
    echo "<p>La base de datos ahora soporta:</p>";
    echo "<ul>";
    echo "<li>Recuperación de contraseña con tokens seguros</li>";
    echo "<li>Bloqueo de cuenta por intentos fallidos</li>";
    echo "<li>Política de contraseñas</li>";
    echo "<li>Logs de seguridad</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error en la actualización:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>