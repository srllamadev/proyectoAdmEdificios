<?php
/**
 * Actualizar Email del Administrador a admin@admin.com
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Actualizar Email Admin</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .success { color: #10b981; background: #d1fae5; padding: 15px; border-radius: 5px; border-left: 4px solid #10b981; margin: 20px 0; }
            .error { color: #ef4444; background: #fee2e2; padding: 15px; border-radius: 5px; border-left: 4px solid #ef4444; margin: 20px 0; }
            .info { background: #e0e7ff; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea; margin: 20px 0; }
            h1 { color: #667eea; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f3f4f6; font-weight: bold; }
        </style>
    </head>
    <body>
    <h1>🔄 Actualizar Email del Administrador</h1>";
    
    // Actualizar el email del admin de llamakachera@gmail.com a admin@admin.com
    $sql = "UPDATE users SET email = 'admin@admin.com' 
            WHERE role = 'admin' AND (email = 'llamakachera@gmail.com' OR email = 'admin@edificio.com')";
    $result = $pdo->exec($sql);
    
    if ($result > 0) {
        echo "<div class='success'>
                <h2>✅ Email Actualizado Exitosamente</h2>
                <p>El email del administrador ha sido actualizado a <strong>admin@admin.com</strong></p>
              </div>";
        
        echo "<div class='info'>
                <h3>📋 Nueva Credencial de Administrador:</h3>
                <ul>
                    <li><strong>Email:</strong> admin@admin.com</li>
                    <li><strong>Contraseña:</strong> ko87K#adm-0</li>
                </ul>
                <p><a href='login.php' style='color: #667eea; font-weight: bold; text-decoration: none;'>→ Ir al Login</a></p>
              </div>";
    } else {
        echo "<div class='error'>
                <h2>⚠️ No se realizaron cambios</h2>
                <p>El email ya podría estar actualizado o el usuario no existe.</p>
              </div>";
    }
    
    // Verificar el cambio - mostrar todos los admins
    $sql = "SELECT id, name, email, role FROM users WHERE role = 'admin'";
    $stmt = $pdo->query($sql);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>
            <h3>👨‍💼 Administradores del Sistema:</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>";
    
    foreach ($admins as $admin) {
        echo "<tr>
                <td>{$admin['id']}</td>
                <td>{$admin['name']}</td>
                <td><strong style='color: #667eea;'>{$admin['email']}</strong></td>
                <td><span style='background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.9em;'>{$admin['role']}</span></td>
              </tr>";
    }
    
    echo "</table></div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<div class='error'>
            <h2>❌ Error</h2>
            <p>{$e->getMessage()}</p>
          </div>";
}
?>
