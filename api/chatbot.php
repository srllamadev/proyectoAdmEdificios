<?php
/**
 * API para el Chatbot del Edificio
 * Maneja las peticiones AJAX del chatbot
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/deepseek_client.php';
require_once __DIR__ . '/../config/database.php';

// Verificar autenticación
if (!isLoggedIn() || !hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Configurar cabeceras JSON
header('Content-Type: application/json');

try {
    // Obtener datos de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'send_message':
            $message = $input['message'] ?? '';
            $conversationHistory = $input['history'] ?? [];
            
            if (empty($message)) {
                throw new Exception('Mensaje vacío');
            }
            
            // Obtener contexto del edificio
            $buildingContext = getBuildingContext();
            
            // Crear prompt del sistema
            $systemPrompt = createSystemPrompt($buildingContext);
            
            // Enviar a DeepSeek
            $deepseek = new DeepSeekClient();
            $response = $deepseek->chat($message, $conversationHistory, $systemPrompt);
            
            if ($response['success']) {
                echo json_encode([
                    'success' => true,
                    'response' => $response['message'],
                    'usage' => $response['usage']
                ]);
            } else {
                throw new Exception($response['error']);
            }
            break;
            
        case 'get_building_stats':
            $stats = getBuildingContext();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtener contexto completo del edificio para el chatbot
 */
function getBuildingContext() {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Inicializar contexto con valores por defecto
    $context = [
        'total_departamentos' => 0,
        'inquilinos_activos' => 0,
        'empleados_activos' => 0,
        'pagos_pendientes_cantidad' => 0,
        'pagos_pendientes_monto' => 0,
        'pagos_vencidos_cantidad' => 0,
        'pagos_vencidos_monto' => 0,
        'reservas_pendientes' => 0,
        'areas_disponibles' => 0,
        'consumos_mes' => [],
        'deuda_consumos' => 0,
        'top_deudores' => [],
        'eventos_seguridad_hoy' => 0,
        'fecha_actual' => date('Y-m-d H:i:s')
    ];
    
    try {
        // Estadísticas de departamentos
        $stmt = $conn->query("SELECT COUNT(*) as total FROM departamentos");
        $context['total_departamentos'] = $stmt->fetchColumn();
        
        // Estadísticas de inquilinos
        $stmt = $conn->query("SELECT COUNT(*) as total FROM inquilinos WHERE estado = 'activo'");
        $context['inquilinos_activos'] = $stmt->fetchColumn();
        
        // Estadísticas de empleados
        $stmt = $conn->query("SELECT COUNT(*) as total FROM empleados WHERE estado = 'activo'");
        $context['empleados_activos'] = $stmt->fetchColumn();
        
        // Pagos pendientes
        $stmt = $conn->query("
            SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as total
            FROM pagos 
            WHERE estado = 'pendiente'
        ");
        $pagos = $stmt->fetch(PDO::FETCH_ASSOC);
        $context['pagos_pendientes_cantidad'] = $pagos['cantidad'];
        $context['pagos_pendientes_monto'] = $pagos['total'];
        
        // Pagos vencidos
        $stmt = $conn->query("
            SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as total
            FROM pagos 
            WHERE estado = 'vencido'
        ");
        $vencidos = $stmt->fetch(PDO::FETCH_ASSOC);
        $context['pagos_vencidos_cantidad'] = $vencidos['cantidad'];
        $context['pagos_vencidos_monto'] = $vencidos['total'];
        
        // Reservas pendientes
        $stmt = $conn->query("
            SELECT COUNT(*) as total 
            FROM reservas 
            WHERE estado = 'pendiente' AND fecha_inicio >= NOW()
        ");
        $context['reservas_pendientes'] = $stmt->fetchColumn();
        
        // Áreas comunes disponibles
        $stmt = $conn->query("
            SELECT COUNT(*) as total 
            FROM areas_comunes 
            WHERE estado = 'disponible'
        ");
        $context['areas_disponibles'] = $stmt->fetchColumn();
        
        // Consumos del mes actual
        $stmt = $conn->query("
            SELECT 
                tipo_servicio,
                SUM(consumo) as total_consumo,
                SUM(costo_total) as total_costo
            FROM lecturas_consumo
            WHERE MONTH(fecha_lectura) = MONTH(CURDATE())
            AND YEAR(fecha_lectura) = YEAR(CURDATE())
            GROUP BY tipo_servicio
        ");
        $consumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $context['consumos_mes'] = $consumos;
        
        // Deuda total del edificio
        $stmt = $conn->query("
            SELECT COALESCE(SUM(costo_total), 0) as total
            FROM lecturas_consumo
            WHERE estado_pago IN ('pendiente', 'vencido')
        ");
        $context['deuda_consumos'] = $stmt->fetchColumn();
        
        // Top 3 departamentos con mayor deuda
        $stmt = $conn->query("
            SELECT d.nombre, SUM(p.monto + COALESCE(p.recargo, 0)) as deuda
            FROM pagos p
            JOIN alquileres a ON p.alquiler_id = a.id
            JOIN departamentos d ON a.departamento_id = d.id
            WHERE p.estado IN ('pendiente', 'vencido')
            GROUP BY d.id, d.nombre
            ORDER BY deuda DESC
            LIMIT 3
        ");
        $context['top_deudores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Eventos de seguridad hoy
        $stmt = $conn->query("
            SELECT COUNT(*) as total
            FROM security_logs
            WHERE DATE(created_at) = CURDATE()
        ");
        $context['eventos_seguridad_hoy'] = $stmt->fetchColumn();
        
        // Fecha actual
        $context['fecha_actual'] = date('Y-m-d H:i:s');
        
    } catch (PDOException $e) {
        error_log("Error obteniendo contexto: " . $e->getMessage());
    }
    
    return $context;
}

/**
 * Crear prompt del sistema con contexto del edificio
 */
function createSystemPrompt($context) {
    $consumos_texto = '';
    if (!empty($context['consumos_mes'])) {
        foreach ($context['consumos_mes'] as $consumo) {
            $consumos_texto .= "- {$consumo['tipo_servicio']}: {$consumo['total_consumo']} unidades, \${$consumo['total_costo']}\n";
        }
    }
    
    $deudores_texto = '';
    if (!empty($context['top_deudores'])) {
        foreach ($context['top_deudores'] as $deudor) {
            $deudores_texto .= "- {$deudor['nombre']}: \${$deudor['deuda']}\n";
        }
    }
    
    return <<<PROMPT
Eres un asistente virtual inteligente del Sistema de Administración de Edificios. Tu nombre es "Edificio AI".

CONTEXTO ACTUAL DEL EDIFICIO (Fecha: {$context['fecha_actual']}):

📊 ESTADÍSTICAS GENERALES:
- Total de departamentos: {$context['total_departamentos']}
- Inquilinos activos: {$context['inquilinos_activos']}
- Empleados activos: {$context['empleados_activos']}

💰 INFORMACIÓN FINANCIERA:
- Pagos pendientes: {$context['pagos_pendientes_cantidad']} pagos por \${$context['pagos_pendientes_monto']}
- Pagos vencidos: {$context['pagos_vencidos_cantidad']} pagos por \${$context['pagos_vencidos_monto']}
- Deuda de consumos (agua/luz/gas): \${$context['deuda_consumos']}

📅 RESERVAS Y ÁREAS:
- Reservas pendientes: {$context['reservas_pendientes']}
- Áreas comunes disponibles: {$context['areas_disponibles']}

⚡ CONSUMOS DEL MES ACTUAL:
{$consumos_texto}

🔝 TOP 3 DEPARTAMENTOS CON MAYOR DEUDA:
{$deudores_texto}

🔒 SEGURIDAD:
- Eventos de seguridad hoy: {$context['eventos_seguridad_hoy']}

TUS RESPONSABILIDADES:
1. Responder preguntas sobre el estado del edificio usando los datos actuales
2. Proporcionar información financiera clara y precisa
3. Sugerir acciones cuando detectes problemas (deudas altas, muchos pagos vencidos, etc.)
4. Ser amigable, profesional y conciso
5. Si no tienes información específica, indícalo claramente
6. Puedes hacer cálculos y análisis de los datos proporcionados
7. Recomendar mejores prácticas de administración

FORMATO DE RESPUESTA:
- Usa emojis para hacer las respuestas más visuales
- Estructura la información con viñetas cuando sea apropiado
- Sé directo y útil
- Si detectas algo urgente, menciona

IMPORTANTE: Solo responde con información relacionada al edificio y su administración. Si te preguntan algo fuera de este contexto, indica amablemente que solo puedes ayudar con temas del edificio.
PROMPT;
}
