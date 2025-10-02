<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es inquilino
if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener información del inquilino
try {
    $query = "SELECT i.*, a.id as alquiler_id, a.numero_departamento, a.precio_mensual 
              FROM inquilinos i 
              LEFT JOIN alquileres a ON i.id = a.inquilino_id AND a.estado = 'activo'
              WHERE i.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($inquilino && $inquilino['alquiler_id']) {
        // Obtener todos los pagos del inquilino
        $query = "SELECT p.* 
                  FROM pagos p 
                  WHERE p.alquiler_id = :alquiler_id 
                  ORDER BY p.fecha_vencimiento DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':alquiler_id', $inquilino['alquiler_id']);
        $stmt->execute();
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular estadísticas
        $total_pagos = count($pagos);
        $pagos_pendientes = count(array_filter($pagos, function($p) { return $p['estado'] == 'pendiente'; }));
        $pagos_vencidos = count(array_filter($pagos, function($p) { return $p['estado'] == 'vencido'; }));
        $monto_pendiente = array_sum(array_map(function($p) { 
            return $p['estado'] == 'pendiente' ? $p['monto'] + $p['recargo'] : 0; 
        }, $pagos));
    }
    
} catch (PDOException $e) {
    $error = "Error al obtener información de pagos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos - Inquilino</title>
</head>
<body>
    <h1>Gestión de Pagos</h1>
    <p><a href="dashboard.php">← Volver al Dashboard</a> | <a href="../../logout.php">Cerrar Sesión</a></p>
    
    <hr>
    
    <?php if (isset($error)): ?>
        <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <strong>Error:</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($inquilino && $inquilino['alquiler_id']): ?>
        <!-- Información del alquiler -->
        <div style="background: #e6f3ff; padding: 15px; margin: 10px 0; border: 1px solid #007cba;">
            <h3>Información de su Alquiler</h3>
            <p><strong>Departamento:</strong> <?php echo $inquilino['numero_departamento']; ?></p>
            <p><strong>Precio Mensual:</strong> $<?php echo number_format($inquilino['precio_mensual'], 2); ?></p>
        </div>
        
        <!-- Estadísticas de pagos -->
        <h2>Resumen de Pagos</h2>
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
            <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
                <h4>Total de Pagos</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $total_pagos; ?></p>
            </div>
            <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
                <h4>Pagos Pendientes</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: <?php echo $pagos_pendientes > 0 ? 'orange' : 'green'; ?>;"><?php echo $pagos_pendientes; ?></p>
            </div>
            <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
                <h4>Pagos Vencidos</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: <?php echo $pagos_vencidos > 0 ? 'red' : 'green'; ?>;"><?php echo $pagos_vencidos; ?></p>
            </div>
            <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
                <h4>Monto Pendiente</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: <?php echo $monto_pendiente > 0 ? 'red' : 'green'; ?>;">$<?php echo number_format($monto_pendiente, 2); ?></p>
            </div>
        </div>
        
        <!-- Lista de pagos -->
        <h2>Historial de Pagos</h2>
        
        <?php if (!empty($pagos)): ?>
            <div style="margin: 20px 0;">
                <?php foreach ($pagos as $pago): ?>
                    <div style="border: 1px solid #ddd; padding: 20px; margin: 15px 0; background: <?php 
                        echo $pago['estado'] == 'pagado' ? '#e6ffe6' : 
                            ($pago['estado'] == 'vencido' ? '#ffe6e6' : '#fff8e1'); 
                    ?>; border-left: 4px solid <?php 
                        echo $pago['estado'] == 'pagado' ? 'green' : 
                            ($pago['estado'] == 'vencido' ? 'red' : 'orange'); 
                    ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <h3 style="margin: 0; color: <?php 
                                echo $pago['estado'] == 'pagado' ? 'green' : 
                                    ($pago['estado'] == 'vencido' ? 'red' : 'orange'); 
                            ?>;">
                                <?php echo htmlspecialchars($pago['descripcion']); ?>
                            </h3>
                            <span style="background: <?php 
                                echo $pago['estado'] == 'pagado' ? 'green' : 
                                    ($pago['estado'] == 'vencido' ? 'red' : 'orange'); 
                            ?>; color: white; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                <?php echo strtoupper($pago['estado']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                            <div>
                                <p style="margin: 5px 0;"><strong>Monto:</strong> $<?php echo number_format($pago['monto'], 2); ?></p>
                                <?php if ($pago['recargo'] > 0): ?>
                                    <p style="margin: 5px 0; color: red;"><strong>Recargo:</strong> $<?php echo number_format($pago['recargo'], 2); ?></p>
                                    <p style="margin: 5px 0; font-weight: bold;"><strong>Total:</strong> $<?php echo number_format($pago['monto'] + $pago['recargo'], 2); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <p style="margin: 5px 0;"><strong>Fecha Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></p>
                                <?php if ($pago['fecha_pago']): ?>
                                    <p style="margin: 5px 0; color: green;"><strong>Fecha Pago:</strong> <?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></p>
                                    <p style="margin: 5px 0;"><strong>Método de Pago:</strong> <?php echo htmlspecialchars($pago['metodo_pago']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($pago['estado'] == 'pendiente'): ?>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                                <h4>Formas de Pago Disponibles:</h4>
                                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                    <div style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                        <strong>Transferencia Bancaria</strong><br>
                                        <small>Banco: Banco Ejemplo<br>
                                        Cuenta: 1234567890<br>
                                        CBU: 1234567890123456789012</small>
                                    </div>
                                    <div style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                        <strong>Efectivo</strong><br>
                                        <small>En administración<br>
                                        Lunes a Viernes: 9:00 - 17:00<br>
                                        Sábados: 9:00 - 13:00</small>
                                    </div>
                                    <div style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                        <strong>Débito Automático</strong><br>
                                        <small>Contactar administración<br>
                                        para configurar débito<br>
                                        automático mensual</small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pago['estado'] == 'vencido'): ?>
                            <div style="background: #ffebee; padding: 10px; border-radius: 5px; margin-top: 15px;">
                                <strong style="color: red;">⚠️ PAGO VENCIDO</strong><br>
                                <small>Este pago está vencido. Puede aplicar recargos. Contacte a administración para más información.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: #f9f9f9; border: 1px solid #ddd;">
                <h3>No hay registros de pagos</h3>
                <p>No se encontraron pagos asociados a su alquiler.</p>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: #fff8e1; border: 1px solid #ffc107;">
            <h3>Sin Alquiler Activo</h3>
            <p>No tiene un alquiler activo en este momento. Contacte a administración si esto es un error.</p>
        </div>
    <?php endif; ?>
    
    <hr>
    
    <div style="background: #e6f3ff; padding: 15px; margin: 20px 0; border: 1px solid #007cba;">
        <h3>Información de Contacto</h3>
        <p><strong>Administración del Edificio</strong></p>
        <p>📧 Email: admin@edificio.com</p>
        <p>📞 Teléfono: +1234567890</p>
        <p>🕒 Horarios de atención:</p>
        <ul style="margin: 5px 0 5px 20px;">
            <li>Lunes a Viernes: 9:00 - 17:00</li>
            <li>Sábados: 9:00 - 13:00</li>
            <li>Domingos: Cerrado</li>
        </ul>
    </div>
</body>
</html>