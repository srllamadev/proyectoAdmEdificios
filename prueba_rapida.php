<?php
/**
 * Prueba Rápida del Sistema
 * Verifica que todo esté funcionando correctamente
 */

require_once 'includes/functions.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Prueba Rápida del Sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .success { border-left-color: #10b981; }
        .error { border-left-color: #ef4444; }
        h1 { color: #667eea; }
        .status { font-weight: bold; }
        .ok { color: #10b981; }
        .fail { color: #ef4444; }
    </style>
</head>
<body>
<h1>🧪 Prueba Rápida del Sistema</h1>";

$all_ok = true;

// Test 1: Conexión a BD
echo "<div class='test ";
try {
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo "success'>";
        echo "<strong>✓ Conexión a Base de Datos:</strong> <span class='status ok'>OK</span>";
    } else {
        echo "error'>";
        echo "<strong>✗ Conexión a Base de Datos:</strong> <span class='status fail'>FALLO</span>";
        $all_ok = false;
    }
} catch (Exception $e) {
    echo "error'>";
    echo "<strong>✗ Conexión a Base de Datos:</strong> <span class='status fail'>ERROR - {$e->getMessage()}</span>";
    $all_ok = false;
}
echo "</div>";

// Test 2: Funciones de seguridad
echo "<div class='test ";
if (function_exists('hashPassword') && function_exists('verifyPassword')) {
    echo "success'>";
    echo "<strong>✓ Funciones de Seguridad:</strong> <span class='status ok'>Disponibles</span>";
    
    // Probar hash
    $test_password = "test123";
    $hash = hashPassword($test_password);
    $verify = verifyPassword($test_password, $hash);
    
    if ($verify) {
        echo " - Hashing funciona correctamente";
    } else {
        echo " - <span class='status fail'>Hashing no funciona</span>";
        $all_ok = false;
    }
} else {
    echo "error'>";
    echo "<strong>✗ Funciones de Seguridad:</strong> <span class='status fail'>No disponibles</span>";
    $all_ok = false;
}
echo "</div>";

// Test 3: Usuarios en BD
if ($db) {
    echo "<div class='test ";
    try {
        $stmt = $db->query("SELECT COUNT(*) as count, 
                           SUM(CASE WHEN role='admin' THEN 1 ELSE 0 END) as admins,
                           SUM(CASE WHEN role='empleado' THEN 1 ELSE 0 END) as empleados,
                           SUM(CASE WHEN role='inquilino' THEN 1 ELSE 0 END) as inquilinos
                           FROM users");
        $stats = $stmt->fetch();
        
        if ($stats['count'] > 0) {
            echo "success'>";
            echo "<strong>✓ Usuarios del Sistema:</strong> <span class='status ok'>{$stats['count']} usuarios</span>";
            echo " (Admins: {$stats['admins']}, Empleados: {$stats['empleados']}, Inquilinos: {$stats['inquilinos']})";
        } else {
            echo "error'>";
            echo "<strong>✗ Usuarios del Sistema:</strong> <span class='status fail'>No hay usuarios</span>";
            $all_ok = false;
        }
    } catch (Exception $e) {
        echo "error'>";
        echo "<strong>✗ Usuarios del Sistema:</strong> <span class='status fail'>Error al consultar</span>";
        $all_ok = false;
    }
    echo "</div>";
    
    // Test 4: Tablas críticas
    $tables = ['departamentos', 'inquilinos', 'empleados', 'areas_comunes', 'reservas', 'pagos'];
    $tables_ok = 0;
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $result = $stmt->fetch();
            $tables_ok++;
        } catch (Exception $e) {
            $all_ok = false;
        }
    }
    
    echo "<div class='test ";
    if ($tables_ok == count($tables)) {
        echo "success'>";
        echo "<strong>✓ Tablas del Sistema:</strong> <span class='status ok'>{$tables_ok}/" . count($tables) . " tablas OK</span>";
    } else {
        echo "error'>";
        echo "<strong>✗ Tablas del Sistema:</strong> <span class='status fail'>{$tables_ok}/" . count($tables) . " tablas disponibles</span>";
        $all_ok = false;
    }
    echo "</div>";
    
    // Test 5: Columnas de seguridad
    echo "<div class='test ";
    try {
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'failed_login_attempts'");
        if ($stmt->rowCount() > 0) {
            echo "success'>";
            echo "<strong>✓ Esquema de Seguridad:</strong> <span class='status ok'>Instalado</span>";
        } else {
            echo "error'>";
            echo "<strong>✗ Esquema de Seguridad:</strong> <span class='status fail'>No instalado - ejecutar migrar_sistema.php</span>";
            $all_ok = false;
        }
    } catch (Exception $e) {
        echo "error'>";
        echo "<strong>✗ Esquema de Seguridad:</strong> <span class='status fail'>Error al verificar</span>";
        $all_ok = false;
    }
    echo "</div>";
}

// Test 6: Archivos críticos
$critical_files = [
    'index.php',
    'login.php',
    'views/admin/dashboard.php',
    'views/empleado/dashboard.php',
    'views/inquilino/dashboard.php',
    'includes/functions.php',
    'config/database.php'
];

$files_ok = 0;
foreach ($critical_files as $file) {
    if (file_exists($file)) $files_ok++;
}

echo "<div class='test ";
if ($files_ok == count($critical_files)) {
    echo "success'>";
    echo "<strong>✓ Archivos del Sistema:</strong> <span class='status ok'>{$files_ok}/" . count($critical_files) . " archivos OK</span>";
} else {
    echo "error'>";
    echo "<strong>✗ Archivos del Sistema:</strong> <span class='status fail'>{$files_ok}/" . count($critical_files) . " archivos encontrados</span>";
    $all_ok = false;
}
echo "</div>";

// Resumen final
echo "<hr>";
if ($all_ok) {
    echo "<div class='test success'>
            <h2 style='color: #10b981; margin: 0;'>🎉 ¡Sistema Completamente Funcional!</h2>
            <p>Todas las pruebas pasaron exitosamente. El sistema está listo para usar.</p>
            <p><a href='login.php' style='color: #667eea; text-decoration: none; font-weight: bold;'>→ Ir al Login</a></p>
          </div>";
} else {
    echo "<div class='test error'>
            <h2 style='color: #ef4444; margin: 0;'>⚠️ Sistema con Problemas</h2>
            <p>Algunas pruebas fallaron. Revise los errores anteriores.</p>
            <p><strong>Acciones recomendadas:</strong></p>
            <ol>
                <li>Ejecutar <a href='migrar_sistema.php'>migrar_sistema.php</a></li>
                <li>Ejecutar <a href='update_passwords.php'>update_passwords.php</a></li>
                <li>Revisar <a href='verificar_sistema.php'>verificar_sistema.php</a></li>
            </ol>
          </div>";
}

echo "<div style='text-align: center; margin-top: 30px; color: #666;'>
        <p>Scripts disponibles: 
           <a href='verificar_sistema.php'>Verificación Completa</a> | 
           <a href='migrar_sistema.php'>Migración</a> | 
           <a href='diagnostico_db.php'>Diagnóstico DB</a>
        </p>
      </div>";

echo "</body></html>";
?>
