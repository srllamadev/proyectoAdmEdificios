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
    $query = "SELECT i.*, u.name, u.email, a.numero_departamento, a.precio_mensual, a.estado as estado_alquiler
              FROM inquilinos i 
              JOIN users u ON i.user_id = u.id 
              LEFT JOIN alquileres a ON i.id = a.inquilino_id AND a.estado = 'activo'
              WHERE i.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($inquilino) {
        // Obtener pagos del inquilino
        $query = "SELECT p.* 
                  FROM pagos p 
                  JOIN alquileres a ON p.alquiler_id = a.id 
                  WHERE a.inquilino_id = :inquilino_id 
                  ORDER BY p.fecha_vencimiento DESC LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':inquilino_id', $inquilino['id']);
        $stmt->execute();
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener reservas del inquilino
        $query = "SELECT r.*, ac.nombre as area_nombre 
                  FROM reservas r 
                  JOIN areas_comunes ac ON r.area_comun_id = ac.id 
                  WHERE r.inquilino_id = :inquilino_id 
                  ORDER BY r.fecha_inicio DESC LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':inquilino_id', $inquilino['id']);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener comunicaciones dirigidas al inquilino
        $query = "SELECT c.*, u.name as remitente_nombre 
                  FROM comunicacion c 
                  JOIN users u ON c.remitente_id = u.id 
                  WHERE (c.destinatario_id = :user_id OR c.destinatario_id IS NULL) 
                  ORDER BY c.created_at DESC LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $comunicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $error = "Error al obtener información: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inquilino - Sistema de Edificios</title>
</head>
<body>
    <h1>Panel de Inquilino</h1>
    <p>Bienvenido, <?php echo $_SESSION['user_name']; ?> | 
       Departamento: <?php echo $inquilino['numero_departamento'] ?? 'Sin asignar'; ?> | 
       <a href="../../logout.php">Cerrar Sesión</a></p>
    
    <hr>
    
    <?php if (isset($error)): ?>
        <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <strong>Error:</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Información del Alquiler -->
    <?php if ($inquilino && $inquilino['numero_departamento']): ?>
        <div style="background: #e6f3ff; padding: 15px; margin: 10px 0; border: 1px solid #007cba;">
            <h3>Información de su Alquiler</h3>
            <p><strong>Departamento:</strong> <?php echo $inquilino['numero_departamento']; ?></p>
            <p><strong>Precio Mensual:</strong> $<?php echo number_format($inquilino['precio_mensual'], 2); ?></p>
            <p><strong>Estado:</strong> <?php echo ucfirst($inquilino['estado_alquiler']); ?></p>
            <p><strong>Fecha de Ingreso:</strong> <?php echo $inquilino['fecha_ingreso']; ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Accesos Rápidos -->
    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
        <a href="reservas.php" style="background: #28a745; color: white; padding: 15px 20px; text-decoration: none; display: block; text-align: center; min-width: 150px;">
            Reservar Áreas Comunes
        </a>
        <a href="pagos.php" style="background: #007cba; color: white; padding: 15px 20px; text-decoration: none; display: block; text-align: center; min-width: 150px;">
            Ver Mis Pagos
        </a>
        <a href="comunicaciones.php" style="background: #ffc107; color: black; padding: 15px 20px; text-decoration: none; display: block; text-align: center; min-width: 150px;">
            Ver Comunicaciones
        </a>
    </div>
    
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <!-- Columna izquierda: Pagos -->
        <div style="flex: 1; min-width: 400px;">
            <h2>Estado de Pagos</h2>
            
            <?php if (!empty($pagos)): ?>
                <?php foreach ($pagos as $pago): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: <?php echo $pago['estado'] == 'pagado' ? '#e6ffe6' : ($pago['estado'] == 'vencido' ? '#ffe6e6' : '#fff8e1'); ?>;">
                        <h4 style="margin-top: 0; color: <?php 
                            echo $pago['estado'] == 'pagado' ? 'green' : 
                                ($pago['estado'] == 'vencido' ? 'red' : 'orange'); 
                        ?>;">
                            <?php echo htmlspecialchars($pago['descripcion']); ?>
                        </h4>
                        <p><strong>Monto:</strong> $<?php echo number_format($pago['monto'], 2); ?></p>
                        <p><strong>Fecha Vencimiento:</strong> <?php echo $pago['fecha_vencimiento']; ?></p>
                        <?php if ($pago['fecha_pago']): ?>
                            <p><strong>Fecha Pago:</strong> <?php echo $pago['fecha_pago']; ?></p>
                            <p><strong>Método de Pago:</strong> <?php echo $pago['metodo_pago']; ?></p>
                        <?php endif; ?>
                        <p><strong>Estado:</strong> 
                            <span style="color: <?php 
                                echo $pago['estado'] == 'pagado' ? 'green' : 
                                    ($pago['estado'] == 'vencido' ? 'red' : 'orange'); 
                            ?>;">
                                <?php echo ucfirst($pago['estado']); ?>
                            </span>
                        </p>
                        <?php if ($pago['recargo'] > 0): ?>
                            <p><strong>Recargo:</strong> $<?php echo number_format($pago['recargo'], 2); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay registros de pagos.</p>
            <?php endif; ?>
        </div>
        
        <!-- Columna derecha: Reservas y Comunicaciones -->
        <div style="flex: 1; min-width: 300px;">
            <h2>Mis Reservas Recientes</h2>
            
            <?php if (!empty($reservas)): ?>
                <?php foreach ($reservas as $reserva): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: <?php echo $reserva['estado'] == 'confirmada' ? '#e6ffe6' : ($reserva['estado'] == 'cancelada' ? '#ffe6e6' : '#fff8e1'); ?>;">
                        <h4 style="margin-top: 0;">
                            <?php echo htmlspecialchars($reserva['area_nombre']); ?>
                        </h4>
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])); ?> - <?php echo date('H:i', strtotime($reserva['fecha_fin'])); ?></p>
                        <p><strong>Estado:</strong> 
                            <span style="color: <?php 
                                echo $reserva['estado'] == 'confirmada' ? 'green' : 
                                    ($reserva['estado'] == 'cancelada' ? 'red' : 'orange'); 
                            ?>;">
                                <?php echo ucfirst($reserva['estado']); ?>
                            </span>
                        </p>
                        <p><strong>Precio Total:</strong> $<?php echo number_format($reserva['precio_total'], 2); ?></p>
                        <?php if ($reserva['descripcion']): ?>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($reserva['descripcion']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tienes reservas registradas.</p>
            <?php endif; ?>
            
            <hr>
            
            <h2>Comunicaciones Recientes</h2>
            
            <?php if (!empty($comunicaciones)): ?>
                <?php foreach (array_slice($comunicaciones, 0, 3) as $comunicacion): ?>
                    <div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0; background: <?php echo $comunicacion['leido'] ? '#f9f9f9' : '#e6f3ff'; ?>;">
                        <h5 style="margin-top: 0;">
                            <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                            <?php if (!$comunicacion['leido']): ?>
                                <span style="color: red; font-size: 10px;">[NUEVO]</span>
                            <?php endif; ?>
                        </h5>
                        <p style="font-size: 12px;"><strong>De:</strong> <?php echo htmlspecialchars($comunicacion['remitente_nombre']); ?></p>
                        <p style="font-size: 12px;"><?php echo htmlspecialchars(substr($comunicacion['mensaje'], 0, 100)); ?><?php echo strlen($comunicacion['mensaje']) > 100 ? '...' : ''; ?></p>
                        <small>Fecha: <?php echo $comunicacion['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comunicaciones recientes.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>