<?php
require_once 'config/database.php';

echo "=== Verificando y corrigiendo contraseñas ===\n\n";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Error: No se pudo conectar a la base de datos\n");
}

try {
    // Verificar usuarios existentes
    $query = "SELECT id, name, email, role FROM users ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados en la base de datos:\n";
    echo "=====================================\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Email: {$user['email']} | Nombre: {$user['name']} | Rol: {$user['role']}\n";
    }
    echo "\n";
    
    // Actualizar todas las contraseñas a "password" sin hash
    echo "Actualizando contraseñas...\n";
    $plainPassword = 'password';
    
    $updateQuery = "UPDATE users SET password = :password";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindParam(':password', $plainPassword);
    
    if ($stmt->execute()) {
        echo "✅ Contraseñas actualizadas exitosamente.\n";
        echo "Todas las contraseñas son ahora: 'password' (sin hash)\n\n";
    } else {
        echo "❌ Error al actualizar contraseñas.\n";
    }
    
    // Verificar el usuario admin específicamente
    $adminQuery = "SELECT * FROM users WHERE email = 'admin@edificio.com'";
    $stmt = $db->prepare($adminQuery);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Usuario administrador encontrado:\n";
        echo "Email: {$admin['email']}\n";
        echo "Nombre: {$admin['name']}\n";
        echo "Rol: {$admin['role']}\n";
        echo "Contraseña: {$admin['password']}\n\n";
    } else {
        echo "❌ Usuario administrador NO encontrado.\n";
    }
    
    echo "=== Proceso completado ===\n";
    echo "Puedes usar estos datos para login:\n";
    echo "Email: admin@edificio.com\n";
    echo "Contraseña: password\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>