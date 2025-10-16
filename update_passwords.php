<?php
require_once 'includes/functions.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "<h2>Actualizando contraseñas de usuarios de prueba...</h2>";

    // Contraseña por defecto para testing
    $defaultPassword = 'password';
    $hashedPassword = hashPassword($defaultPassword);

    // Actualizar todas las contraseñas de usuarios existentes
    $sql = "UPDATE users SET
            password = ?,
            password_changed_at = NOW(),
            failed_login_attempts = 0,
            account_locked = 0,
            locked_until = NULL
            WHERE password NOT LIKE '$2y$%'"; // Solo actualizar si no están hasheadas

    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$hashedPassword]);

    if ($result) {
        echo "✅ Contraseñas actualizadas exitosamente<br>";
        echo "Nueva contraseña hasheada: " . substr($hashedPassword, 0, 20) . "...<br>";
        echo "Para testing, usar: <strong>'password'</strong><br>";
    } else {
        echo "❌ Error al actualizar contraseñas<br>";
    }

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>