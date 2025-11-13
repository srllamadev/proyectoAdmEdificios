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

// Obtener datos del cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    if ($action === 'get_dashboard_stats') {
        // Obtener el rol del usuario desde el input
        $userRole = $input['role'] ?? 'admin';
        $userId = $input['user_id'] ?? null;
        
        $stats = [];
        
        if ($userRole === 'admin') {
            // Estadísticas para administrador
            
            // Total de departamentos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM departamentos");
            $stats['total_departamentos'] = (int)$stmt->fetch()['total'];
            
            // Inquilinos activos
            $stmt = $pdo->query("SELECT COUNT(DISTINCT inquilino_id) as total FROM alquileres WHERE estado = 'activo'");
            $stats['inquilinos_activos'] = (int)$stmt->fetch()['total'];
            
            // Empleados activos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'empleado'");
            $stats['empleados_activos'] = (int)$stmt->fetch()['total'];
            
            // Pagos pendientes
            $stmt = $pdo->query("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto FROM pagos WHERE estado = 'pendiente'");
            $result = $stmt->fetch();
            $stats['pagos_pendientes_cantidad'] = (int)$result['cantidad'];
            $stats['pagos_pendientes_monto'] = (float)$result['monto'];
            
            // Pagos vencidos
            $stmt = $pdo->query("SELECT COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto FROM pagos WHERE estado = 'pendiente' AND fecha_vencimiento < CURDATE()");
            $result = $stmt->fetch();
            $stats['pagos_vencidos_cantidad'] = (int)$result['cantidad'];
            $stats['pagos_vencidos_monto'] = (float)$result['monto'];
            
            // Solicitudes de mantenimiento
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mantenimiento WHERE estado IN ('pendiente', 'en_proceso')");
            $stats['mantenimiento_pendiente'] = (int)$stmt->fetch()['total'];
            
            // Departamentos ocupados vs vacantes
            $stmt = $pdo->query("SELECT COUNT(DISTINCT numero_departamento) as ocupados FROM alquileres WHERE estado = 'activo'");
            $stats['departamentos_ocupados'] = (int)$stmt->fetch()['ocupados'];
            $stats['departamentos_vacantes'] = $stats['total_departamentos'] - $stats['departamentos_ocupados'];
            
            // Ingresos del mes actual
            $stmt = $pdo->query("SELECT COALESCE(SUM(monto), 0) as total FROM pagos WHERE estado = 'pagado' AND MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())");
            $stats['ingresos_mes'] = (float)$stmt->fetch()['total'];
            
        } elseif ($userRole === 'empleado') {
            // Estadísticas para empleado
            
            // Tareas pendientes asignadas
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mantenimiento WHERE estado IN ('pendiente', 'en_proceso')");
            $stats['tareas_pendientes'] = (int)$stmt->fetch()['total'];
            
            // Departamentos a cargo
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM departamentos");
            $stats['departamentos_total'] = (int)$stmt->fetch()['total'];
            
            // Solicitudes hoy
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mantenimiento WHERE DATE(fecha_solicitud) = CURDATE()");
            $stats['solicitudes_hoy'] = (int)$stmt->fetch()['total'];
            
            // Pagos para verificar
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagos WHERE estado = 'pendiente'");
            $stats['pagos_verificar'] = (int)$stmt->fetch()['total'];
            
        } elseif ($userRole === 'inquilino') {
            // Estadísticas para inquilino
            
            // Obtener alquiler del inquilino (necesitarías asociar user_id con inquilino_id)
            // Por ahora, usaré datos de ejemplo
            
            // Próximo pago
            $stmt = $pdo->query("SELECT * FROM pagos WHERE estado = 'pendiente' ORDER BY fecha_vencimiento ASC LIMIT 1");
            $proximoPago = $stmt->fetch();
            
            if ($proximoPago) {
                $stats['proximo_pago_monto'] = (float)$proximoPago['monto'];
                $stats['proximo_pago_fecha'] = $proximoPago['fecha_vencimiento'];
            } else {
                $stats['proximo_pago_monto'] = 0;
                $stats['proximo_pago_fecha'] = null;
            }
            
            // Pagos realizados este año
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagos WHERE estado = 'pagado' AND YEAR(fecha_pago) = YEAR(CURDATE())");
            $stats['pagos_realizados_anio'] = (int)$stmt->fetch()['total'];
            
            // Solicitudes de mantenimiento activas
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mantenimiento WHERE estado IN ('pendiente', 'en_proceso')");
            $stats['mantenimiento_activo'] = (int)$stmt->fetch()['total'];
            
            // Avisos sin leer
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM avisos WHERE leido = 0");
            $stats['avisos_sin_leer'] = (int)$stmt->fetch()['total'];
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'role' => $userRole
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
?>
