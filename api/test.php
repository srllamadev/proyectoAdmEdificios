<?php
/**
 * Script de prueba para verificar configuración con Ngrok
 * Accede desde: https://tu-ngrok-url/proyectoAdmEdificios/api/test.php
 */

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../config/environment.php';

header('Content-Type: application/json; charset=utf-8');

// Información del servidor
$server_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => ENVIRONMENT,
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'http_origin' => $_SERVER['HTTP_ORIGIN'] ?? 'None',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
];

// Verificar conexión a base de datos
$db_status = '❌ No conectado';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT 1");
    $db_status = '✅ Conectado';
} catch (PDOException $e) {
    $db_status = '❌ Error: ' . $e->getMessage();
}

// Verificar API key de DeepSeek
$deepseek_status = DEEPSEEK_API_KEY ? '✅ Configurada' : '❌ No configurada';

// Verificar archivos importantes
$files_check = [
    'config/database.php' => file_exists(__DIR__ . '/../config/database.php'),
    'includes/functions.php' => file_exists(__DIR__ . '/../includes/functions.php'),
    'includes/env_loader.php' => file_exists(__DIR__ . '/../includes/env_loader.php'),
    '.env' => file_exists(__DIR__ . '/../.env'),
];

$response = [
    'status' => 'success',
    'message' => 'API de test funcionando correctamente',
    'server_info' => $server_info,
    'database' => $db_status,
    'deepseek_api' => $deepseek_status,
    'files_check' => $files_check,
    'cors_enabled' => true,
    'ngrok_ready' => strpos($_SERVER['HTTP_HOST'] ?? '', 'ngrok') !== false
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>