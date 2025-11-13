<?php
/**
 * Configuración CORS para desarrollo y producción
 * Permite conexiones desde localhost, Ngrok y dominios autorizados
 */

require_once __DIR__ . '/../config/environment.php';

// Configuración de orígenes permitidos
$allowed_origins = [
    'http://localhost',
    'http://localhost:8080',
    'http://127.0.0.1',
    'http://127.0.0.1:8080',
    'https://localhost',
    'https://localhost:8080',
    'https://127.0.0.1',
    'https://127.0.0.1:8080',
];

// Agregar orígenes de Ngrok (dominios *.ngrok.io)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (strpos($origin, '.ngrok.io') !== false || strpos($origin, '.ngrok-free.app') !== false) {
        $allowed_origins[] = $origin;
    }
}

// Verificar origen
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowed_origins) || ENVIRONMENT === 'development' || PHP_SAPI === 'cli') {
    if (!headers_sent() && $origin) {
        header("Access-Control-Allow-Origin: $origin");
    }
} else {
    // En producción, solo permitir orígenes específicos
    if (!headers_sent()) {
        header("Access-Control-Allow-Origin: https://tu-dominio.com");
    }
}

if (!headers_sent()) {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // 24 horas
}

// Manejar preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (!headers_sent()) {
        http_response_code(200);
    }
    exit();
}
?>