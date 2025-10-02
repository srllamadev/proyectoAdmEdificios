<?php
echo "=== Test de Conexión MySQL ===\n\n";

// Verificar extensiones PHP
echo "Verificando extensiones PHP:\n";
echo "PDO instalado: " . (extension_loaded('pdo') ? 'SÍ' : 'NO') . "\n";
echo "PDO MySQL instalado: " . (extension_loaded('pdo_mysql') ? 'SÍ' : 'NO') . "\n";
echo "MySQL instalado: " . (extension_loaded('mysql') ? 'SÍ' : 'NO') . "\n";
echo "MySQLi instalado: " . (extension_loaded('mysqli') ? 'SÍ' : 'NO') . "\n\n";

// Intentar conexión simple
try {
    $dsn = "mysql:host=localhost;port=3306;dbname=edificio_admin;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conexión exitosa a la base de datos!\n\n";
    
    // Verificar usuarios
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users LIMIT 5");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "Usuarios encontrados:\n";
    echo "====================\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Email: {$user['email']} | Nombre: {$user['name']} | Rol: {$user['role']}\n";
    }
    
    // Actualizar contraseñas a texto plano
    echo "\nActualizando contraseñas...\n";
    $stmt = $pdo->prepare("UPDATE users SET password = 'password'");
    $stmt->execute();
    echo "✅ Contraseñas actualizadas a 'password'\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "Posibles causas:\n";
    echo "1. MySQL no está ejecutándose en XAMPP\n";
    echo "2. La base de datos 'edificio_admin' no existe\n";
    echo "3. Extensión PHP MySQL no está habilitada\n";
}
?>