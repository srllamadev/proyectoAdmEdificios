<?php
// Iniciar sesión solo si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de base de datos usando ruta absoluta
require_once dirname(__DIR__) . '/config/database.php';

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Función para verificar el rol del usuario
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Función para redirigir según el rol
function redirectToRolePage() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    switch($_SESSION['role']) {
        case 'admin':
            header('Location: views/admin/dashboard.php');
            break;
        case 'empleado':
            header('Location: views/empleado/dashboard.php');
            break;
        case 'inquilino':
            header('Location: views/inquilino/dashboard.php');
            break;
        default:
            header('Location: login.php');
            break;
    }
    exit();
}

// Función para limpiar datos de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para mostrar alertas con estilo
function showAlert($message, $type = 'info') {
    $alertClass = '';
    switch($type) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
        default:
            $alertClass = 'alert-info';
    }
    
    echo "<div class='alert $alertClass' role='alert'>$message</div>";
}

// Función para verificar el rol requerido y redirigir si no coincide
function checkUserRole($requiredRole) {
    if (!isLoggedIn()) {
        header('Location: ../../login.php');
        exit();
    }
    
    if (!hasRole($requiredRole)) {
        header('Location: ../../unauthorized.php');
        exit();
    }
    
    return true;
}

// Función para formatear moneda
function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}

// Función para obtener la unidad de medida de un recurso
function getResourceUnit($resource) {
    switch($resource) {
        case 'agua':
            return 'L';
        case 'luz':
            return 'kWh';
        case 'gas':
            return 'm³';
        default:
            return '';
    }
}

// Función para formatear fechas
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    // Intentar convertir la fecha
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'Fecha inválida';
    }
    
    return date($format, $timestamp);
}

// Función para obtener el estado con badge
function getStatusBadge($status) {
    $badges = [
        'activo' => ['class' => 'status-active', 'icon' => 'check-circle', 'text' => 'Activo'],
        'inactivo' => ['class' => 'status-expired', 'icon' => 'times-circle', 'text' => 'Inactivo'],
        'pendiente' => ['class' => 'status-pending', 'icon' => 'clock', 'text' => 'Pendiente'],
        'vencido' => ['class' => 'status-expired', 'icon' => 'exclamation-triangle', 'text' => 'Vencido']
    ];
    
    $badge = $badges[$status] ?? ['class' => 'status-pending', 'icon' => 'question', 'text' => ucfirst($status)];
    
    return "<span class='status-badge {$badge['class']}'>
                <i class='fas fa-{$badge['icon']}'></i> {$badge['text']}
            </span>";
}

// Obtener conexión PDO reusando Database class
function get_db_connection() {
    static $conn = null;
    if ($conn) return $conn;

    $db = new Database();
    $conn = $db->getConnection();
    return $conn;
}

// Validar token de dispositivo sencillo (busca en device_tokens)
function validate_device_token($token) {
    if (empty($token)) return false;
    $conn = get_db_connection();
    if (!$conn) return false;
    $sql = "SELECT dispositivo_id, activo FROM device_tokens WHERE token = :token LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;
    return (int)$row['activo'] === 1 ? (int)$row['dispositivo_id'] : false;
}

// Helper para escribir respuestas JSON y terminar
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

// Crear alerta en base de datos
function create_alert($departamento_id, $sensor_id, $tipo, $mensaje, $prioridad = 'media', $metadata = null) {
    $conn = get_db_connection();
    $sql = "INSERT INTO alertas (departamento_id, sensor_id, tipo, prioridad, mensaje, metadata, leido, creado_en) VALUES (:departamento_id, :sensor_id, :tipo, :prioridad, :mensaje, :metadata, 0, NOW())";
    $stmt = $conn->prepare($sql);
    $meta_json = null;
    if (!is_null($metadata)) {
        $meta_json = json_encode($metadata, JSON_UNESCAPED_UNICODE);
    }
    $stmt->execute([
        ':departamento_id' => $departamento_id,
        ':sensor_id' => $sensor_id,
        ':tipo' => $tipo,
        ':prioridad' => $prioridad,
        ':mensaje' => $mensaje,
        ':metadata' => $meta_json
    ]);
    return $conn->lastInsertId();
}

// Obtener umbrales activos (puede devolver por sensor o por departamento)
function get_active_umbrales() {
    $conn = get_db_connection();
    $sql = "SELECT * FROM umbrales WHERE activo = 1";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ===========================================
   FUNCIONES DE SEGURIDAD Y AUTENTICACIÓN
   =========================================== */

// Generar token seguro para recuperación de contraseña
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Generar hash seguro de contraseña
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3,
    ]);
}

// Verificar contraseña
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Validar fortaleza de contraseña
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra mayúscula";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra minúscula";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un número";
    }

    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un carácter especial";
    }

    return $errors;
}

// Verificar si la cuenta está bloqueada
function isAccountLocked($userId) {
    $conn = get_db_connection();
    $sql = "SELECT account_locked, locked_until FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return false;

    // Verificar bloqueo permanente
    if ($user['account_locked']) return true;

    // Verificar bloqueo temporal
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        return true;
    }

    return false;
}

// Registrar intento fallido de login
function recordFailedLogin($email) {
    $conn = get_db_connection();
    $max_attempts = 5; // Máximo de intentos fallidos
    $lock_duration = 15 * 60; // 15 minutos de bloqueo

    // Obtener información actual del usuario
    $sql = "SELECT id, failed_login_attempts, name FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $current_attempts = $user ? $user['failed_login_attempts'] : 0;
    $new_attempts = $current_attempts + 1;

    // Incrementar contador de intentos fallidos
    $sql = "UPDATE users SET
            failed_login_attempts = failed_login_attempts + 1,
            last_failed_login = NOW(),
            locked_until = CASE
                WHEN failed_login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? MINUTE)
                ELSE locked_until
            END
            WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$max_attempts, $lock_duration / 60, $email]);

    // Log del intento fallido
    logSecurityEvent($user ? $user['id'] : null, 'failed_login_attempt', "Intento fallido desde IP: " . $_SERVER['REMOTE_ADDR'], $email);
    
    // Enviar notificación por correo si se alcanzaron 3 o más intentos
    if ($user && $new_attempts >= 3) {
        sendFailedLoginNotification($user['id'], $email, $new_attempts);
        
        // Log del envío de notificación
        logSecurityEvent($user['id'], 'security_notification_sent', "Notificación de $new_attempts intentos fallidos enviada");
    }
}

// Limpiar intentos fallidos después de login exitoso
function clearFailedLoginAttempts($userId) {
    $conn = get_db_connection();
    $sql = "UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_failed_login = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
}

// Crear token de recuperación de contraseña
function createPasswordResetToken($email) {
    $conn = get_db_connection();
    $token = generateSecureToken();
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora

    $sql = "UPDATE users SET
            password_reset_token = ?,
            password_reset_expires = ?
            WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$token, $expires, $email]);

    return $token;
}

// Verificar token de recuperación
function verifyPasswordResetToken($token) {
    $conn = get_db_connection();
    $sql = "SELECT id, email, password_reset_expires FROM users
            WHERE password_reset_token = ? AND password_reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$token]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Actualizar contraseña con token
function resetPasswordWithToken($token, $newPassword) {
    $conn = get_db_connection();

    // Verificar token
    $user = verifyPasswordResetToken($token);
    if (!$user) {
        return ['success' => false, 'message' => 'Token inválido o expirado'];
    }

    // Validar nueva contraseña
    $errors = validatePasswordStrength($newPassword);
    if (!empty($errors)) {
        return ['success' => false, 'message' => 'Contraseña no cumple con los requisitos: ' . implode(', ', $errors)];
    }

    // Actualizar contraseña
    $hashedPassword = hashPassword($newPassword);
    $sql = "UPDATE users SET
            password = ?,
            password_reset_token = NULL,
            password_reset_expires = NULL,
            password_changed_at = NOW(),
            failed_login_attempts = 0,
            locked_until = NULL
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hashedPassword, $user['id']]);

    // Obtener información del usuario para el correo
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Enviar correo de confirmación
    sendPasswordChangeConfirmation($user['email'], $userData['name']);

    // Log del cambio de contraseña
    logSecurityEvent($user['id'], 'password_reset', 'Contraseña cambiada mediante token de recuperación');

    return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
}

// Registrar nuevo usuario
function registerUser($name, $email, $password, $role = 'inquilino') {
    $conn = get_db_connection();

    // Verificar si el email ya existe
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'El email ya está registrado'];
    }

    // Validar contraseña
    $errors = validatePasswordStrength($password);
    if (!empty($errors)) {
        return ['success' => false, 'message' => 'Contraseña no cumple con los requisitos: ' . implode(', ', $errors)];
    }

    // Crear usuario
    $hashedPassword = hashPassword($password);
    $sql = "INSERT INTO users (name, email, password, role, password_changed_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $email, $hashedPassword, $role]);

    $userId = $conn->lastInsertId();

    // Log de creación de cuenta
    logSecurityEvent($userId, 'account_created', 'Cuenta creada mediante registro');

    return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $userId];
}

// Log de eventos de seguridad
function logSecurityEvent($userId, $action, $details = '', $email = null) {
    $conn = get_db_connection();

    // Si no tenemos userId pero sí email, buscar el userId
    if (!$userId && $email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user ? $user['id'] : null;
    }

    $sql = "INSERT INTO security_logs (user_id, action, ip_address, user_agent, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $details
    ]);
}

// Obtener tiempo restante de bloqueo
function getLockoutTimeRemaining($userId) {
    $conn = get_db_connection();
    $sql = "SELECT locked_until FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['locked_until']) return 0;

    $remaining = strtotime($user['locked_until']) - time();
    return max(0, $remaining);
}

/* ===========================================
   FUNCIONES DE ENVÍO DE CORREO
   =========================================== */

// Enviar correo usando PHPMailer o función nativa de PHP
function sendEmail($to, $subject, $body, $isHTML = true) {
    // Configuración del correo
    $headers = [];
    
    if ($isHTML) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
    }
    
    $headers[] = 'From: Sistema Edificio Admin <noreply@edificio.com>';
    $headers[] = 'Reply-To: noreply@edificio.com';
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // En desarrollo, guardar en archivo en lugar de enviar
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
        $log_dir = dirname(__DIR__) . '/logs/emails';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $filename = $log_dir . '/email_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
        $content = "To: $to\n";
        $content .= "Subject: $subject\n";
        $content .= "Headers: " . implode("\n", $headers) . "\n\n";
        $content .= $body;
        
        file_put_contents($filename, $content);
        return true; // Simular envío exitoso en desarrollo
    }
    
    // En producción, enviar correo real
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

// Enviar notificación de intentos fallidos de login
function sendFailedLoginNotification($userId, $email, $attempts) {
    $conn = get_db_connection();
    
    // Obtener información del usuario
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $name = $user ? $user['name'] : 'Usuario';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
    $timestamp = date('d/m/Y H:i:s');
    
    $subject = "⚠️ Alerta de Seguridad - Intentos de Acceso Fallidos";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .alert-box { background: #fef3cd; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
            .info-box { background: white; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #e5e7eb; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔒 Alerta de Seguridad</h1>
            </div>
            <div class='content'>
                <p>Hola <strong>$name</strong>,</p>
                
                <div class='alert-box'>
                    <strong>⚠️ ADVERTENCIA:</strong> Se han detectado múltiples intentos fallidos de acceso a tu cuenta.
                </div>
                
                <div class='info-box'>
                    <h3>Detalles del Intento:</h3>
                    <ul>
                        <li><strong>Cuenta:</strong> $email</li>
                        <li><strong>Intentos fallidos:</strong> $attempts</li>
                        <li><strong>Fecha y hora:</strong> $timestamp</li>
                        <li><strong>Dirección IP:</strong> $ip</li>
                    </ul>
                </div>
                
                <p><strong>¿Fuiste tú?</strong></p>
                <ul>
                    <li>Si fuiste tú quien intentó acceder, puedes recuperar tu contraseña usando el enlace a continuación.</li>
                    <li>Si NO fuiste tú, tu cuenta podría estar en riesgo. Te recomendamos cambiar tu contraseña inmediatamente.</li>
                </ul>
                
                <div style='text-align: center;'>
                    <a href='http://localhost/proyectoAdmEdificios/forgot-password.php' class='button'>
                        Recuperar Contraseña
                    </a>
                </div>
                
                <div class='alert-box' style='background: #fee2e2; border-left-color: #ef4444;'>
                    <strong>⚠️ IMPORTANTE:</strong> Si se alcanzan 5 intentos fallidos, tu cuenta será bloqueada temporalmente por 15 minutos por seguridad.
                </div>
                
                <div class='footer'>
                    <p>Este es un correo automático del sistema de seguridad.</p>
                    <p>Sistema de Administración de Edificios</p>
                    <p style='font-size: 0.8em; color: #999;'>Si no solicitaste este correo, ignóralo.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body, true);
}

// Enviar correo de recuperación de contraseña
function sendPasswordResetEmail($email, $token, $userName) {
    $resetLink = "http://localhost/proyectoAdmEdificios/reset-password.php?token=" . urlencode($token);
    
    $subject = "🔑 Recuperación de Contraseña - Sistema Edificio";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .info-box { background: white; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #e5e7eb; }
            .warning { background: #fef3cd; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔑 Recuperación de Contraseña</h1>
            </div>
            <div class='content'>
                <p>Hola <strong>$userName</strong>,</p>
                
                <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en el Sistema de Administración de Edificios.</p>
                
                <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                
                <div style='text-align: center;'>
                    <a href='$resetLink' class='button'>
                        Restablecer Contraseña
                    </a>
                </div>
                
                <div class='info-box'>
                    <p><strong>O copia y pega este enlace en tu navegador:</strong></p>
                    <p style='word-break: break-all; color: #667eea;'>$resetLink</p>
                </div>
                
                <div class='warning'>
                    <strong>⏱️ IMPORTANTE:</strong> Este enlace expirará en <strong>1 hora</strong> por seguridad.
                </div>
                
                <p><strong>¿No solicitaste este cambio?</strong></p>
                <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este correo. Tu contraseña actual permanecerá sin cambios.</p>
                
                <div class='footer'>
                    <p>Este es un correo automático del sistema.</p>
                    <p>Sistema de Administración de Edificios</p>
                    <p style='font-size: 0.8em; color: #999;'>Por favor, no respondas a este correo.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body, true);
}

// Enviar confirmación de cambio de contraseña
function sendPasswordChangeConfirmation($email, $userName) {
    $subject = "✅ Contraseña Actualizada - Sistema Edificio";
    
    $timestamp = date('d/m/Y H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .success-box { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; }
            .info-box { background: white; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #e5e7eb; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Contraseña Actualizada</h1>
            </div>
            <div class='content'>
                <p>Hola <strong>$userName</strong>,</p>
                
                <div class='success-box'>
                    <strong>✅ Tu contraseña ha sido actualizada exitosamente.</strong>
                </div>
                
                <div class='info-box'>
                    <h3>Detalles de la Actualización:</h3>
                    <ul>
                        <li><strong>Cuenta:</strong> $email</li>
                        <li><strong>Fecha y hora:</strong> $timestamp</li>
                        <li><strong>Dirección IP:</strong> $ip</li>
                    </ul>
                </div>
                
                <p>Ahora puedes iniciar sesión con tu nueva contraseña.</p>
                
                <p><strong>¿No fuiste tú?</strong></p>
                <p>Si no realizaste este cambio, contacta inmediatamente al administrador del sistema.</p>
                
                <div class='footer'>
                    <p>Este es un correo automático del sistema.</p>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body, true);
}
?>