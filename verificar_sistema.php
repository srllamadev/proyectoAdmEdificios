<?php
/**
 * Script de Verificación Completa del Sistema
 * Revisa todos los componentes críticos de la aplicación
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificación del Sistema</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section h2 {
            margin-top: 0;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin: 5px 0;
        }
        .status.ok {
            background: #10b981;
            color: white;
        }
        .status.error {
            background: #ef4444;
            color: white;
        }
        .status.warning {
            background: #f59e0b;
            color: white;
        }
        .status.info {
            background: #3b82f6;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .detail {
            background: #f9fafb;
            padding: 10px;
            border-left: 4px solid #667eea;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .check-item {
            padding: 10px;
            margin: 5px 0;
            background: #f9fafb;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card h3 {
            margin: 0;
            font-size: 2em;
        }
        .summary-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>";

echo "<div class='header'>
        <h1>🔍 Verificación Completa del Sistema</h1>
        <p>Análisis de todos los componentes de la aplicación Edificio Admin</p>
      </div>";

$issues = [];
$warnings = [];
$ok_count = 0;

// ========================================
// 1. VERIFICAR CONEXIÓN A BASE DE DATOS
// ========================================
echo "<div class='section'>
        <h2>1. Conexión a Base de Datos</h2>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<div class='check-item'>
                <span>✓ Conexión establecida correctamente</span>
                <span class='status ok'>OK</span>
              </div>";
        $ok_count++;
        
        // Obtener información de la BD
        $stmt = $db->query("SELECT DATABASE() as db_name, VERSION() as version");
        $info = $stmt->fetch();
        echo "<div class='detail'>Base de datos: {$info['db_name']} | Versión MySQL: {$info['version']}</div>";
    } else {
        $issues[] = "No se pudo conectar a la base de datos";
        echo "<div class='check-item'>
                <span>✗ Error de conexión</span>
                <span class='status error'>ERROR</span>
              </div>";
    }
} catch (Exception $e) {
    $issues[] = "Error en conexión DB: " . $e->getMessage();
    echo "<div class='check-item'>
            <span>✗ Excepción: {$e->getMessage()}</span>
            <span class='status error'>ERROR</span>
          </div>";
}

echo "</div>";

// ========================================
// 2. VERIFICAR TABLAS REQUERIDAS
// ========================================
echo "<div class='section'>
        <h2>2. Estructura de Base de Datos</h2>";

$required_tables = [
    'users' => 'Tabla de usuarios',
    'alquileres' => 'Gestión de alquileres',
    'areas_comunes' => 'Áreas comunes del edificio',
    'comunicacion' => 'Sistema de comunicación',
    'departamentos' => 'Departamentos del edificio',
    'empleados' => 'Personal del edificio',
    'inquilinos' => 'Inquilinos/residentes',
    'mantenimiento' => 'Solicitudes de mantenimiento',
    'pagos' => 'Registro de pagos',
    'personal' => 'Personal adicional',
    'reservas' => 'Reservas de áreas comunes',
];

if ($db) {
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Descripción</th><th>Registros</th><th>Estado</th></tr>";
    
    foreach ($required_tables as $table => $description) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM `{$table}`");
            $result = $stmt->fetch();
            $count = $result['count'];
            
            echo "<tr>
                    <td><strong>{$table}</strong></td>
                    <td>{$description}</td>
                    <td>{$count}</td>
                    <td><span class='status ok'>✓ OK</span></td>
                  </tr>";
            $ok_count++;
        } catch (Exception $e) {
            echo "<tr>
                    <td><strong>{$table}</strong></td>
                    <td>{$description}</td>
                    <td>-</td>
                    <td><span class='status error'>✗ FALTA</span></td>
                  </tr>";
            $issues[] = "Tabla faltante: {$table}";
        }
    }
    
    echo "</table>";
    
    // Verificar tablas adicionales (módulos nuevos)
    echo "<h3>Módulos Adicionales</h3>";
    $additional_tables = ['invoices', 'payments', 'payroll', 'transactions', 'lecturas_consumo', 'anomalias_consumo'];
    
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Estado</th><th>Registros</th></tr>";
    
    foreach ($additional_tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM `{$table}`");
            $result = $stmt->fetch();
            $count = $result['count'];
            
            echo "<tr>
                    <td><strong>{$table}</strong></td>
                    <td><span class='status ok'>✓ Instalado</span></td>
                    <td>{$count}</td>
                  </tr>";
        } catch (Exception $e) {
            echo "<tr>
                    <td><strong>{$table}</strong></td>
                    <td><span class='status warning'>⚠ No instalado</span></td>
                    <td>-</td>
                  </tr>";
            $warnings[] = "Módulo opcional no instalado: {$table}";
        }
    }
    
    echo "</table>";
}

echo "</div>";

// ========================================
// 3. VERIFICAR ARCHIVOS CRÍTICOS
// ========================================
echo "<div class='section'>
        <h2>3. Archivos del Sistema</h2>";

$critical_files = [
    'config/database.php' => 'Configuración de base de datos',
    'includes/functions.php' => 'Funciones principales',
    'includes/header.php' => 'Encabezado común',
    'includes/footer.php' => 'Pie de página común',
    'login.php' => 'Sistema de autenticación',
    'index.php' => 'Página principal',
    'views/admin/dashboard.php' => 'Dashboard de administrador',
    'views/empleado/dashboard.php' => 'Dashboard de empleado',
    'views/inquilino/dashboard.php' => 'Dashboard de inquilino',
];

echo "<table>";
echo "<tr><th>Archivo</th><th>Descripción</th><th>Tamaño</th><th>Estado</th></tr>";

foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        $size_kb = round($size / 1024, 2);
        echo "<tr>
                <td><code>{$file}</code></td>
                <td>{$description}</td>
                <td>{$size_kb} KB</td>
                <td><span class='status ok'>✓ OK</span></td>
              </tr>";
        $ok_count++;
    } else {
        echo "<tr>
                <td><code>{$file}</code></td>
                <td>{$description}</td>
                <td>-</td>
                <td><span class='status error'>✗ FALTA</span></td>
              </tr>";
        $issues[] = "Archivo crítico faltante: {$file}";
    }
}

echo "</table>";

echo "</div>";

// ========================================
// 4. VERIFICAR USUARIOS DE PRUEBA
// ========================================
echo "<div class='section'>
        <h2>4. Usuarios del Sistema</h2>";

if ($db) {
    try {
        $stmt = $db->query("SELECT id, name, email, role FROM users ORDER BY role, id");
        $users = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></tr>";
        
        foreach ($users as $user) {
            $role_badge = [
                'admin' => 'ok',
                'empleado' => 'info',
                'inquilino' => 'warning'
            ][$user['role']] ?? 'info';
            
            echo "<tr>
                    <td>{$user['id']}</td>
                    <td>{$user['name']}</td>
                    <td>{$user['email']}</td>
                    <td><span class='status {$role_badge}'>{$user['role']}</span></td>
                    <td><span class='status ok'>✓ Activo</span></td>
                  </tr>";
        }
        
        echo "</table>";
        
        echo "<div class='detail'>Total de usuarios: " . count($users) . "</div>";
        $ok_count++;
        
    } catch (Exception $e) {
        echo "<div class='check-item'>
                <span>✗ Error al cargar usuarios: {$e->getMessage()}</span>
                <span class='status error'>ERROR</span>
              </div>";
        $issues[] = "Error al consultar usuarios";
    }
}

echo "</div>";

// ========================================
// 5. VERIFICAR EXTENSIONES PHP
// ========================================
echo "<div class='section'>
        <h2>5. Extensiones PHP Requeridas</h2>";

$required_extensions = [
    'pdo' => 'PDO (base de datos)',
    'pdo_mysql' => 'MySQL Driver',
    'mbstring' => 'Multibyte String',
    'openssl' => 'OpenSSL (seguridad)',
    'json' => 'JSON',
    'session' => 'Sesiones',
];

echo "<table>";
echo "<tr><th>Extensión</th><th>Descripción</th><th>Estado</th></tr>";

foreach ($required_extensions as $ext => $desc) {
    $loaded = extension_loaded($ext);
    
    echo "<tr>
            <td><strong>{$ext}</strong></td>
            <td>{$desc}</td>
            <td>" . ($loaded 
                ? "<span class='status ok'>✓ Cargada</span>" 
                : "<span class='status error'>✗ Falta</span>") . "</td>
          </tr>";
    
    if ($loaded) {
        $ok_count++;
    } else {
        $issues[] = "Extensión PHP faltante: {$ext}";
    }
}

echo "</table>";

echo "<div class='detail'>Versión PHP: " . phpversion() . "</div>";

echo "</div>";

// ========================================
// 6. VERIFICAR PERMISOS DE ESCRITURA
// ========================================
echo "<div class='section'>
        <h2>6. Permisos de Directorios</h2>";

$writable_dirs = [
    'logs' => 'Archivos de log',
    'temp_files' => 'Archivos temporales',
];

echo "<table>";
echo "<tr><th>Directorio</th><th>Descripción</th><th>Estado</th></tr>";

foreach ($writable_dirs as $dir => $desc) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "<tr>
                <td><code>{$dir}/</code></td>
                <td>{$desc}</td>
                <td>" . ($writable 
                    ? "<span class='status ok'>✓ Escribible</span>" 
                    : "<span class='status warning'>⚠ Solo lectura</span>") . "</td>
              </tr>";
        
        if ($writable) {
            $ok_count++;
        } else {
            $warnings[] = "Directorio sin permisos de escritura: {$dir}";
        }
    } else {
        echo "<tr>
                <td><code>{$dir}/</code></td>
                <td>{$desc}</td>
                <td><span class='status warning'>⚠ No existe</span></td>
              </tr>";
        $warnings[] = "Directorio no existe: {$dir}";
    }
}

echo "</table>";

echo "</div>";

// ========================================
// 7. RESUMEN FINAL
// ========================================
echo "<div class='section'>
        <h2>7. Resumen de Verificación</h2>";

$total_checks = $ok_count + count($issues) + count($warnings);
$error_count = count($issues);
$warning_count = count($warnings);

echo "<div class='summary'>
        <div class='summary-card' style='background: linear-gradient(135deg, #10b981 0%, #059669 100%);'>
            <h3>{$ok_count}</h3>
            <p>Verificaciones OK</p>
        </div>
        <div class='summary-card' style='background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);'>
            <h3>{$warning_count}</h3>
            <p>Advertencias</p>
        </div>
        <div class='summary-card' style='background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);'>
            <h3>{$error_count}</h3>
            <p>Errores Críticos</p>
        </div>
        <div class='summary-card'>
            <h3>{$total_checks}</h3>
            <p>Total Verificaciones</p>
        </div>
      </div>";

if (count($issues) > 0) {
    echo "<h3 style='color: #ef4444;'>❌ Problemas Críticos Detectados:</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li><strong>{$issue}</strong></li>";
    }
    echo "</ul>";
}

if (count($warnings) > 0) {
    echo "<h3 style='color: #f59e0b;'>⚠️ Advertencias:</h3>";
    echo "<ul>";
    foreach ($warnings as $warning) {
        echo "<li>{$warning}</li>";
    }
    echo "</ul>";
}

if (count($issues) == 0 && count($warnings) == 0) {
    echo "<div class='check-item' style='background: #10b981; color: white; font-size: 1.2em;'>
            <span>🎉 ¡Sistema completamente funcional! No se detectaron problemas.</span>
            <span class='status ok'>PERFECTO</span>
          </div>";
}

echo "</div>";

// ========================================
// 8. RECOMENDACIONES
// ========================================
if (count($issues) > 0 || count($warnings) > 0) {
    echo "<div class='section'>
            <h2>8. Recomendaciones y Acciones</h2>";
    
    if ($db && count($issues) > 0) {
        echo "<h3>Para solucionar problemas:</h3>";
        echo "<ol>
                <li>Ejecutar script de migración de base de datos: 
                    <code>tools/run_migrations.php</code></li>
                <li>Ejecutar migración financiera: 
                    <code>tools/run_financial_migration.php</code></li>
                <li>Actualizar esquema de seguridad: 
                    <code>update_security_simple.php</code></li>
                <li>Actualizar contraseñas: 
                    <code>update_passwords.php</code></li>
              </ol>";
    }
    
    if (count($warnings) > 0) {
        echo "<h3>Mejoras opcionales:</h3>";
        echo "<ul>
                <li>Crear directorios faltantes para logs y archivos temporales</li>
                <li>Ajustar permisos de escritura en directorios necesarios</li>
                <li>Instalar módulos adicionales según necesidad</li>
              </ul>";
    }
    
    echo "</div>";
}

echo "<div class='section' style='background: #f0f9ff; border-left: 4px solid #3b82f6;'>
        <h3>📋 Próximos Pasos:</h3>
        <ol>
            <li>Corregir errores críticos si existen</li>
            <li>Ejecutar scripts de migración necesarios</li>
            <li>Probar login con usuarios de prueba</li>
            <li>Verificar acceso a los diferentes dashboards</li>
            <li>Probar funcionalidades principales de cada módulo</li>
        </ol>
      </div>";

echo "</body></html>";
?>
