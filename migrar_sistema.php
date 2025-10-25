<?php
/**
 * Script Maestro de Migración
 * Ejecuta todas las migraciones necesarias para el sistema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migración Completa del Sistema</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
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
            text-align: center;
        }
        .step {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .step h3 {
            margin-top: 0;
            color: #667eea;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        .info {
            background: #e0e7ff;
            padding: 10px;
            border-left: 4px solid #667eea;
            margin: 10px 0;
        }
        .code {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
        }
        .summary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>";

echo "<div class='header'>
        <h1>🚀 Migración Completa del Sistema</h1>
        <p>Actualizando y sincronizando toda la base de datos</p>
      </div>";

try {
    // Conectar a la base de datos
    require_once 'config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<div class='step'>
            <h3>✓ Conexión Establecida</h3>
            <p class='success'>Conectado exitosamente a la base de datos.</p>
          </div>";
    
    $migrations_executed = 0;
    $errors = 0;
    
    // ========================================
    // MIGRACIÓN 1: Columnas de Seguridad en users
    // ========================================
    echo "<div class='step'>
            <h3>1. Actualizar Tabla de Usuarios (Seguridad)</h3>";
    
    $security_columns = [
        'failed_login_attempts' => "ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0",
        'account_locked' => "ALTER TABLE users ADD COLUMN account_locked TINYINT(1) DEFAULT 0",
        'locked_until' => "ALTER TABLE users ADD COLUMN locked_until DATETIME NULL",
        'last_login_at' => "ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL",
        'password_changed_at' => "ALTER TABLE users ADD COLUMN password_changed_at DATETIME NULL",
        'reset_token' => "ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL",
        'reset_token_expires' => "ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL"
    ];
    
    foreach ($security_columns as $column => $sql) {
        try {
            // Verificar si la columna existe
            $check = $pdo->query("SHOW COLUMNS FROM users LIKE '{$column}'");
            if ($check->rowCount() == 0) {
                $pdo->exec($sql);
                echo "<p class='success'>✓ Columna '{$column}' agregada</p>";
                $migrations_executed++;
            } else {
                echo "<p class='info'>• Columna '{$column}' ya existe</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error en '{$column}': {$e->getMessage()}</p>";
            $errors++;
        }
    }
    
    echo "</div>";
    
    // ========================================
    // MIGRACIÓN 2: Tabla de Logs de Seguridad
    // ========================================
    echo "<div class='step'>
            <h3>2. Crear Tabla de Logs de Seguridad</h3>";
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS security_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                event_type VARCHAR(50) NOT NULL,
                description TEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p class='success'>✓ Tabla 'security_logs' creada o verificada</p>";
        $migrations_executed++;
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: {$e->getMessage()}</p>";
        $errors++;
    }
    
    echo "</div>";
    
    // ========================================
    // MIGRACIÓN 3: Tablas de Consumos
    // ========================================
    echo "<div class='step'>
            <h3>3. Módulo de Gestión de Consumos</h3>";
    
    try {
        // Tabla de lecturas de consumo
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS lecturas_consumo (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                departamento_id BIGINT UNSIGNED NOT NULL,
                tipo_servicio ENUM('agua', 'luz', 'gas') NOT NULL,
                lectura_anterior DECIMAL(10,2) DEFAULT 0,
                lectura_actual DECIMAL(10,2) NOT NULL,
                consumo DECIMAL(10,2) GENERATED ALWAYS AS (lectura_actual - lectura_anterior) STORED,
                fecha_lectura DATE NOT NULL,
                periodo VARCHAR(7) NOT NULL,
                observaciones TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_depto_periodo (departamento_id, periodo),
                INDEX idx_fecha (fecha_lectura)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p class='success'>✓ Tabla 'lecturas_consumo' creada o verificada</p>";
        $migrations_executed++;
        
        // Tabla de anomalías
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS anomalias_consumo (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lectura_id BIGINT UNSIGNED NOT NULL,
                tipo_anomalia VARCHAR(50) NOT NULL,
                severidad ENUM('baja', 'media', 'alta') DEFAULT 'media',
                descripcion TEXT NOT NULL,
                estado ENUM('pendiente', 'revisada', 'resuelta', 'falsa_alarma') DEFAULT 'pendiente',
                resolucion TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (lectura_id) REFERENCES lecturas_consumo(id) ON DELETE CASCADE,
                INDEX idx_estado (estado),
                INDEX idx_fecha (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p class='success'>✓ Tabla 'anomalias_consumo' creada o verificada</p>";
        $migrations_executed++;
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: {$e->getMessage()}</p>";
        $errors++;
    }
    
    echo "</div>";
    
    // ========================================
    // MIGRACIÓN 4: Tablas Financieras
    // ========================================
    echo "<div class='step'>
            <h3>4. Módulo de Gestión Financiera</h3>";
    
    try {
        // Tabla de facturas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reference VARCHAR(64) NOT NULL UNIQUE,
                resident_id INT DEFAULT NULL,
                amount DECIMAL(12,2) NOT NULL,
                due_date DATE,
                status ENUM('pending','paid','overdue','cancelled') DEFAULT 'pending',
                meta JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p class='success'>✓ Tabla 'invoices' creada o verificada</p>";
        $migrations_executed++;
        
        // Tabla de items de factura
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT NOT NULL,
                description VARCHAR(255),
                qty INT DEFAULT 1,
                unit_price DECIMAL(12,2) DEFAULT 0,
                total DECIMAL(12,2) GENERATED ALWAYS AS (qty*unit_price) VIRTUAL,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p class='success'>✓ Tabla 'invoice_items' creada o verificada</p>";
        $migrations_executed++;
        
        // Tabla de pagos
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_id INT DEFAULT NULL,
                amount DECIMAL(12,2) NOT NULL,
                method VARCHAR(50),
                gateway VARCHAR(50),
                tx_ref VARCHAR(128),
                metadata JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p class='success'>✓ Tabla 'payments' creada o verificada</p>";
        $migrations_executed++;
        
        // Tabla de nómina
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payroll (
                id INT AUTO_INCREMENT PRIMARY KEY,
                staff_id INT NOT NULL,
                period VARCHAR(20) NOT NULL,
                gross DECIMAL(12,2) NOT NULL,
                deductions DECIMAL(12,2) DEFAULT 0,
                net DECIMAL(12,2) GENERATED ALWAYS AS (gross - deductions) VIRTUAL,
                paid TINYINT(1) DEFAULT 0,
                meta JSON DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p class='success'>✓ Tabla 'payroll' creada o verificada</p>";
        $migrations_executed++;
        
        // Tabla de transacciones
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type ENUM('income','expense','payout') NOT NULL,
                reference VARCHAR(128),
                amount DECIMAL(12,2) NOT NULL,
                description TEXT,
                category VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p class='success'>✓ Tabla 'transactions' creada o verificada</p>";
        $migrations_executed++;
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: {$e->getMessage()}</p>";
        $errors++;
    }
    
    echo "</div>";
    
    // ========================================
    // MIGRACIÓN 5: Verificar Integridad
    // ========================================
    echo "<div class='step'>
            <h3>5. Verificación de Integridad</h3>";
    
    // Verificar que existan usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $user_count = $stmt->fetch()['count'];
    
    if ($user_count == 0) {
        echo "<p class='warning'>⚠ No hay usuarios en el sistema</p>";
        echo "<div class='info'>Ejecute el script SQL 'edificio_admin.sql' para cargar datos iniciales</div>";
    } else {
        echo "<p class='success'>✓ Sistema tiene {$user_count} usuarios registrados</p>";
    }
    
    // Verificar departamentos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM departamentos");
    $dept_count = $stmt->fetch()['count'];
    echo "<p class='success'>✓ Sistema tiene {$dept_count} departamentos</p>";
    
    // Verificar inquilinos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inquilinos");
    $inq_count = $stmt->fetch()['count'];
    echo "<p class='success'>✓ Sistema tiene {$inq_count} inquilinos</p>";
    
    echo "</div>";
    
    // ========================================
    // RESUMEN FINAL
    // ========================================
    if ($errors == 0) {
        echo "<div class='summary'>
                <h2>✅ Migración Completada Exitosamente</h2>
                <p><strong>{$migrations_executed}</strong> migraciones ejecutadas</p>
                <p><strong>0</strong> errores</p>
                <h3 style='margin-top: 20px;'>🎉 El sistema está listo para usar</h3>
              </div>";
    } else {
        echo "<div class='summary' style='background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);'>
                <h2>⚠️ Migración Completada con Advertencias</h2>
                <p><strong>{$migrations_executed}</strong> migraciones exitosas</p>
                <p><strong>{$errors}</strong> errores encontrados</p>
                <p>Revise los errores anteriores y corrija manualmente si es necesario</p>
              </div>";
    }
    
    echo "<div class='step'>
            <h3>📋 Próximos Pasos</h3>
            <ol>
                <li>Ejecutar <code>update_passwords.php</code> para actualizar contraseñas</li>
                <li>Verificar el sistema con <code>verificar_sistema.php</code></li>
                <li>Probar el login con las credenciales de prueba</li>
                <li>Acceder a los diferentes dashboards</li>
            </ol>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='step'>
            <h3 class='error'>❌ Error Crítico</h3>
            <p class='error'>{$e->getMessage()}</p>
            <div class='code'>{$e->getTraceAsString()}</div>
          </div>";
}

echo "</body></html>";
?>
