<?php
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/env_loader.php';
require_once __DIR__ . '/../config/environment.php';

// Iniciar sesión para acceder a datos del usuario autenticado
session_start();

// Cargar variables de entorno
EnvLoader::load();

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
 * Función para generar respuestas del chatbot usando DeepSeek AI
 */
function generarRespuestaChatbot($message, $pdo) {
    // Verificar si tenemos API key configurada
    if (empty(DEEPSEEK_API_KEY)) {
        return "Lo siento, el servicio de IA no está configurado correctamente. Por favor, contacta al administrador.";
    }

    try {
        // Obtener información del edificio para contextualizar
        $context = getBuildingContext($pdo);

        // Obtener información financiera personalizada si el usuario está autenticado
        $userFinancialContext = "";
        if (isset($_SESSION['user_id'])) {
            $userFinancialContext = getUserFinancialContext($pdo, $_SESSION['user_id']);
        }

        // Preparar el prompt para DeepSeek
        $prompt = "Eres un asistente virtual amigable y útil para un edificio residencial llamado 'SLH'.
        Tu nombre es 'SLH Assistant' y eres el asistente oficial del edificio.

        Información del edificio:
        $context

        Información financiera personal del usuario:
        $userFinancialContext

        El usuario pregunta: '$message'

        Responde de manera:
        - Amigable y profesional
        - Concisa pero completa
        - En español
        - Útil y accionable
        - Si no sabes algo específico, sugiere contactar a administración
        - Si el usuario pregunta sobre información financiera personal (deudas, pagos, alquiler), usa la información proporcionada arriba

        Si la pregunta es sobre temas específicos del edificio, usa la información proporcionada.
        Si es una pregunta general, responde normalmente.
        Si el usuario pregunta sobre su información financiera personal y no hay datos disponibles, informa que debe iniciar sesión.";

        // Llamar a la API de DeepSeek
        $response = callDeepSeekAPI($prompt);

        return $response ?: "Lo siento, tuve un problema procesando tu mensaje. ¿Puedes intentarlo de nuevo?";

    } catch (Exception $e) {
        error_log("Error en chatbot DeepSeek: " . $e->getMessage());
        return "Lo siento, tuve un problema técnico. ¿Puedes intentarlo de nuevo en unos momentos?";
    }
}

/**
 * Obtener contexto del edificio desde la base de datos
 */
function getBuildingContext($pdo) {
    try {
        $context = [];

        // Estadísticas básicas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM departamentos");
        $context[] = "Total de departamentos: " . $stmt->fetch()['total'];

        $stmt = $pdo->query("SELECT COUNT(*) as ocupados FROM alquileres WHERE estado = 'activo'");
        $context[] = "Departamentos ocupados: " . $stmt->fetch()['ocupados'];

        $stmt = $pdo->query("SELECT COUNT(*) as pendientes FROM mantenimiento WHERE estado IN ('pendiente', 'en_proceso')");
        $context[] = "Solicitudes de mantenimiento pendientes: " . $stmt->fetch()['pendientes'];

        // Servicios disponibles
        $context[] = "Servicios: Gestión de alquileres, mantenimiento, pagos, comunicaciones, reservas de áreas comunes";

        return implode("\n", $context);

    } catch (Exception $e) {
        return "Información del edificio no disponible temporalmente.";
    }
}

/**
 * Obtener información financiera personalizada del usuario
 */
function getUserFinancialContext($pdo, $userId) {
    try {
        $context = [];

        // Obtener información del inquilino y alquiler activo
        $query = "SELECT i.*, a.id as alquiler_id, a.numero_departamento, a.precio_mensual, u.name
                  FROM inquilinos i
                  LEFT JOIN alquileres a ON i.id = a.inquilino_id AND a.estado = 'activo'
                  LEFT JOIN users u ON i.user_id = u.id
                  WHERE i.user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($inquilino && $inquilino['alquiler_id']) {
            $context[] = "Usuario autenticado: " . $inquilino['name'];
            $context[] = "Departamento: " . $inquilino['numero_departamento'];
            $context[] = "Precio mensual del alquiler: $" . number_format($inquilino['precio_mensual'], 2);

            // Obtener todos los pagos del inquilino
            $query = "SELECT p.* FROM pagos p WHERE p.alquiler_id = :alquiler_id ORDER BY p.fecha_vencimiento DESC";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':alquiler_id', $inquilino['alquiler_id']);
            $stmt->execute();
            $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($pagos)) {
                $total_pagos = count($pagos);
                $pagos_pendientes = count(array_filter($pagos, function($p) { return $p['estado'] == 'pendiente'; }));
                $pagos_vencidos = count(array_filter($pagos, function($p) { return $p['estado'] == 'vencido'; }));
                $monto_pendiente = array_sum(array_map(function($p) {
                    return $p['estado'] == 'pendiente' ? $p['monto'] + $p['recargo'] : 0;
                }, $pagos));

                $context[] = "Total de pagos registrados: " . $total_pagos;
                $context[] = "Pagos pendientes: " . $pagos_pendientes;
                $context[] = "Pagos vencidos: " . $pagos_vencidos;
                $context[] = "Monto total pendiente: $" . number_format($monto_pendiente, 2);

                // Detalles de pagos pendientes
                $pagos_pendientes_detalle = array_filter($pagos, function($p) { return $p['estado'] == 'pendiente'; });
                if (!empty($pagos_pendientes_detalle)) {
                    $context[] = "Pagos pendientes detallados:";
                    foreach ($pagos_pendientes_detalle as $pago) {
                        $context[] = "- " . $pago['descripcion'] . ": $" . number_format($pago['monto'] + $pago['recargo'], 2) . " (Vence: " . $pago['fecha_vencimiento'] . ")";
                    }
                }

                // Último pago realizado
                $pagos_realizados = array_filter($pagos, function($p) { return $p['estado'] == 'pagado'; });
                if (!empty($pagos_realizados)) {
                    $pagos_realizados = array_values($pagos_realizados); // Reindexar el array
                    $ultimo_pago = $pagos_realizados[0]; // Ya ordenado por fecha_vencimiento DESC
                    $context[] = "Último pago realizado: " . $ultimo_pago['descripcion'] . " - $" . number_format($ultimo_pago['monto'], 2) . " (Pagado: " . $ultimo_pago['fecha_pago'] . ")";
                } else {
                    $context[] = "No hay pagos realizados aún.";
                }
            } else {
                $context[] = "No hay pagos registrados para este usuario.";
            }
        } else {
            $context[] = "Usuario no tiene alquiler activo registrado.";
        }

        return implode("\n", $context);

    } catch (Exception $e) {
        return "Información financiera personal no disponible temporalmente.";
    }
}

/**
 * Llamar a la API de DeepSeek
 */
function callDeepSeekAPI($prompt) {
    $apiKey = DEEPSEEK_API_KEY;
    $apiUrl = DEEPSEEK_BASE_URL . 'chat/completions';

    $data = [
        'model' => 'deepseek-chat',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 500,
        'temperature' => 0.7
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        throw new Exception("Error de conexión: " . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception("Error de API (HTTP $httpCode): " . $response);
    }

    $result = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar respuesta JSON");
    }

    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception("Respuesta de API inválida");
    }

    return trim($result['choices'][0]['message']['content']);
}
?>
