<?php
/**
 * Configuración de entorno para el proyecto
 */

// Definir entorno (development/production)
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Configuración de base de datos
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'edificio_admin');

// Configuración de API
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'http://localhost:8080/proyectoAdmEdificios/api/');

// Configuración de DeepSeek
define('DEEPSEEK_API_KEY', getenv('DEEPSEEK_API_KEY') ?: '');
define('DEEPSEEK_BASE_URL', getenv('DEEPSEEK_BASE_URL') ?: 'https://api.deepseek.com/v1/');

// Configuración de seguridad
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'tu_jwt_secret_aqui');
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600); // 1 hora

// Configuración de uploads
define('UPLOAD_PATH', getenv('UPLOAD_PATH') ?: __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', getenv('MAX_FILE_SIZE') ?: 5242880); // 5MB

// Configuración de logs
define('LOG_PATH', getenv('LOG_PATH') ?: __DIR__ . '/../logs/');
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de email (opcional)
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
?>