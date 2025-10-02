<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema</title>
</head>
<body>
    <h1>Diagnóstico del Sistema</h1>
    
    <?php
    echo "<h2>Información PHP</h2>";
    echo "<p><strong>Versión PHP:</strong> " . phpversion() . "</p>";
    echo "<p><strong>PDO instalado:</strong> " . (extension_loaded('pdo') ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>PDO MySQL instalado:</strong> " . (extension_loaded('pdo_mysql') ? 'SÍ' : 'NO') . "</p>";
    echo "<p><strong>MySQLi instalado:</strong> " . (extension_loaded('mysqli') ? 'SÍ' : 'NO') . "</p>";
    
    echo "<h2>Test de Conexión a Base de Datos</h2>";
    
    try {
        $dsn = "mysql:host=localhost;port=3306;dbname=edificio_admin;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "<p style='color: green;'><strong>✅ Conexión exitosa a la base de datos!</strong></p>";
        
        // Verificar usuarios
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@edificio.com'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<h3>Usuario Administrador Encontrado:</h3>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
            echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
            echo "<li><strong>Nombre:</strong> " . $admin['name'] . "</li>";
            echo "<li><strong>Rol:</strong> " . $admin['role'] . "</li>";
            echo "</ul>";
            
            // Actualizar contraseña a texto plano
            $stmt = $pdo->prepare("UPDATE users SET password = 'password' WHERE email = 'admin@edificio.com'");
            $stmt->execute();
            echo "<p style='color: green;'>✅ Contraseña del admin actualizada a 'password'</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Usuario administrador NO encontrado</p>";
        }
        
        // Mostrar todos los usuarios
        echo "<h3>Todos los Usuarios:</h3>";
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users ORDER BY id");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Nombre</th><th>Rol</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['name'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'><strong>❌ Error de conexión:</strong> " . $e->getMessage() . "</p>";
        echo "<h3>Posibles soluciones:</h3>";
        echo "<ol>";
        echo "<li>Verificar que MySQL esté ejecutándose en XAMPP</li>";
        echo "<li>Verificar que la base de datos 'edificio_admin' exista</li>";
        echo "<li>Ejecutar el archivo edificio_admin.sql en phpMyAdmin</li>";
        echo "</ol>";
    }
    ?>
    
    <hr>
    <h2>Instrucciones de Login</h2>
    <p><strong>Email:</strong> admin@edificio.com</p>
    <p><strong>Contraseña:</strong> password</p>
    <p><a href="login.php">Ir al Login</a></p>
</body>
</html>