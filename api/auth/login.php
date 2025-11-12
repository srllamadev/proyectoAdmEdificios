<?php
// Configurar CORS para permitir solicitudes desde Flutter
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Incluir configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Obtener datos del POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validación básica
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email y contraseña requeridos']);
    exit();
}

try {
    // Obtener conexión a la base de datos
    $pdo = getDBConnection();
    
    // Buscar usuario por email (tabla 'users' según la BD existente)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verificar si el usuario existe y la contraseña es correcta
    // password_verify funciona tanto con bcrypt como con Argon2
    if ($user && password_verify($password, $user['password'])) {
        // Generar token único
        $token = bin2hex(random_bytes(32));
        
        // Actualizar último login (la tabla users tiene 'last_login_at')
        $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Respuesta exitosa
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Credenciales inválidas
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales inválidas'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
