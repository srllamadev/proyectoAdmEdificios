<?php
// Configurar CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir configuración de base de datos
require_once __DIR__ . '/config/database.php';

// Verificar token de autenticación
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

// Por ahora, permitir acceso sin validar token para pruebas
// En producción deberías validar el token

// Obtener datos del cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    if ($action === 'get_building_stats') {
        // Obtener estadísticas reales del edificio desde la base de datos
        $stats = [];
        
        // Contar total de departamentos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM departamentos");
        $stats['total_apartments'] = (int)$stmt->fetch()['total'];
        
        // Contar departamentos ocupados (con alquileres activos)
        $stmt = $pdo->query("SELECT COUNT(DISTINCT numero_departamento) as ocupados FROM alquileres WHERE estado = 'activo'");
        $stats['occupied'] = (int)$stmt->fetch()['ocupados'];
        
        // Calcular vacantes
        $stats['vacant'] = $stats['total_apartments'] - $stats['occupied'];
        
        // Contar solicitudes de mantenimiento pendientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM mantenimiento WHERE estado IN ('pendiente', 'en_proceso')");
        $stats['maintenance_requests'] = (int)$stmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        
    } elseif ($action === 'send_message') {
        $message = $input['message'] ?? '';
        $history = $input['history'] ?? [];
        
        // Generar respuesta del chatbot basada en el mensaje
        $response = generarRespuestaChatbot($message, $pdo);
        
        echo json_encode([
            'success' => true,
            'response' => $response
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

/**
 * Función para generar respuestas del chatbot
 */
function generarRespuestaChatbot($message, $pdo) {
    $message = strtolower(trim($message));
    
    // Respuestas simples basadas en palabras clave
    if (strpos($message, 'departamento') !== false || strpos($message, 'apartamento') !== false) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM departamentos");
        $total = $stmt->fetch()['total'];
        return "Actualmente tenemos $total departamentos en el edificio. ¿Necesitas información específica sobre alguno?";
    }
    
    if (strpos($message, 'mantenimiento') !== false) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM mantenimiento WHERE estado = 'pendiente'");
        $pendientes = $stmt->fetch()['total'];
        return "Hay $pendientes solicitudes de mantenimiento pendientes. ¿Deseas reportar algún problema?";
    }
    
    if (strpos($message, 'alquiler') !== false || strpos($message, 'inquilino') !== false) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM alquileres WHERE estado = 'activo'");
        $activos = $stmt->fetch()['total'];
        return "Actualmente hay $activos contratos de alquiler activos. ¿En qué puedo ayudarte?";
    }
    
    if (strpos($message, 'pago') !== false || strpos($message, 'factura') !== false) {
        return "Puedo ayudarte con información sobre pagos y facturas. ¿Qué necesitas saber específicamente?";
    }
    
    // Respuesta por defecto
    return "He recibido tu mensaje: '$message'. Puedo ayudarte con información sobre departamentos, mantenimiento, alquileres, pagos y más. ¿Qué necesitas?";
}
?>
