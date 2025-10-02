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
?>