<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Sesión</title>
</head>
<body>
    <h1>🔍 Estado de Sesión</h1>
    
    <?php
    session_start();
    
    echo "<h2>Información de Sesión PHP</h2>";
    echo "<p><strong>ID de Sesión:</strong> " . session_id() . "</p>";
    echo "<p><strong>Estado de Sesión:</strong> " . session_status() . "</p>";
    
    echo "<h2>Variables de Sesión</h2>";
    if (!empty($_SESSION)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Variable</th><th>Valor</th></tr>";
        foreach ($_SESSION as $key => $value) {
            echo "<tr><td>$key</td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay variables de sesión activas.</p>";
    }
    
    echo "<h2>Acciones</h2>";
    echo "<p><a href='login.php'>Ir al Login</a></p>";
    echo "<p><a href='logout.php'>Cerrar Sesión</a></p>";
    
    if (isset($_SESSION['role'])) {
        switch($_SESSION['role']) {
            case 'admin':
                echo "<p><a href='views/admin/dashboard.php'>Ir al Dashboard Admin</a></p>";
                break;
            case 'empleado':
                echo "<p><a href='views/empleado/dashboard.php'>Ir al Dashboard Empleado</a></p>";
                break;
            case 'inquilino':
                echo "<p><a href='views/inquilino/dashboard.php'>Ir al Dashboard Inquilino</a></p>";
                break;
        }
    }
    
    echo "<hr>";
    echo "<h2>Test de Credenciales</h2>";
    echo "<div style='background: #f9f9f9; padding: 15px;'>";
    echo "<h4>🔑 Credenciales Correctas:</h4>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@edificio.com / password</li>";
    echo "<li><strong>Empleado:</strong> empleado1@edificio.com / password</li>";
    echo "<li><strong>Inquilino:</strong> inquilino1@edificio.com / password</li>";
    echo "</ul>";
    echo "</div>";
    ?>
</body>
</html>