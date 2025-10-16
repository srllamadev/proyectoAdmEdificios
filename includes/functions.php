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
?>