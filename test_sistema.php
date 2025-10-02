<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test del Sistema</title>
</head>
<body>
    <h1>🔧 Test del Sistema</h1>
    
    <?php
    // Test de inclusión de archivos
    echo "<h2>1. Test de Rutas de Archivos</h2>";
    
    $config_path = dirname(__FILE__) . '/config/database.php';
    $functions_path = dirname(__FILE__) . '/includes/functions.php';
    
    echo "<p><strong>Ruta config:</strong> $config_path</p>";
    echo "<p><strong>Existe config:</strong> " . (file_exists($config_path) ? '✅ SÍ' : '❌ NO') . "</p>";
    echo "<p><strong>Ruta functions:</strong> $functions_path</p>";
    echo "<p><strong>Existe functions:</strong> " . (file_exists($functions_path) ? '✅ SÍ' : '❌ NO') . "</p>";
    
    // Test de inclusión
    try {
        require_once 'includes/functions.php';
        echo "<p style='color: green;'>✅ Archivos incluidos correctamente</p>";
        
        // Test de conexión
        echo "<h2>2. Test de Conexión a Base de Datos</h2>";
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            echo "<p style='color: green;'>✅ Conexión a base de datos exitosa</p>";
            
            // Verificar usuario admin
            $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@edificio.com'");
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                echo "<h3>✅ Usuario Admin Encontrado:</h3>";
                echo "<ul>";
                echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
                echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
                echo "<li><strong>Nombre:</strong> " . $admin['name'] . "</li>";
                echo "<li><strong>Rol:</strong> " . $admin['role'] . "</li>";
                echo "</ul>";
                
                // Actualizar contraseña
                $stmt = $db->prepare("UPDATE users SET password = 'password' WHERE email = 'admin@edificio.com'");
                $stmt->execute();
                echo "<p style='color: green;'>✅ Contraseña actualizada</p>";
                
            } else {
                echo "<p style='color: red;'>❌ Usuario admin no encontrado</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Error de conexión a base de datos</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <hr>
    <h2>3. Credenciales de Login</h2>
    <div style="background: #e6f3ff; padding: 15px; border: 1px solid #007cba;">
        <p><strong>📧 Email:</strong> admin@edificio.com</p>
        <p><strong>🔑 Contraseña:</strong> password</p>
        <p><a href="login.php" style="background: #007cba; color: white; padding: 10px 15px; text-decoration: none;">Ir al Login</a></p>
    </div>
    
    <hr>
    <h2>4. Otros Usuarios de Prueba</h2>
    <div style="background: #f9f9f9; padding: 15px;">
        <h4>Empleados:</h4>
        <ul>
            <li>empleado1@edificio.com</li>
            <li>empleado2@edificio.com</li>
            <li>empleado3@edificio.com</li>
        </ul>
        
        <h4>Inquilinos:</h4>
        <ul>
            <li>inquilino1@edificio.com</li>
            <li>inquilino2@edificio.com</li>
            <li>inquilino3@edificio.com</li>
            <li>inquilino4@edificio.com</li>
            <li>inquilino5@edificio.com</li>
        </ul>
        
        <p><strong>Todos con contraseña:</strong> password</p>
    </div>
</body>
</html>