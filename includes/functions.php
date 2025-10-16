<?php
session_start();

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
    $icons = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-triangle',
        'warning' => 'fas fa-exclamation-circle',
        'info' => 'fas fa-info-circle'
    ];
    
    $icon = $icons[$type] ?? $icons['info'];
    
    echo "<div class='alert alert-{$type}'>
            <i class='{$icon}'></i>
            {$message}
          </div>";
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

// Función para formatear moneda
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
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
    logSecurityEvent(null, 'failed_login_attempt', "Intento fallido desde IP: " . $_SERVER['REMOTE_ADDR'], $email);
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
?>