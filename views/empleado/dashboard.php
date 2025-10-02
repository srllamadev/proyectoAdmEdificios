<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es empleado
if (!isLoggedIn() || !hasRole('empleado')) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener información del empleado
try {
    $query = "SELECT e.*, u.name, u.email 
              FROM empleados e 
              JOIN users u ON e.user_id = u.id 
              WHERE e.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener tareas asignadas
    $query = "SELECT t.*, u.name as asignado_por_nombre 
              FROM tareas t 
              JOIN users u ON t.asignado_por = u.id 
              WHERE t.empleado_id = :empleado_id 
              ORDER BY t.fecha_vencimiento ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':empleado_id', $empleado['id']);
    $stmt->execute();
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener comunicaciones dirigidas al empleado
    $query = "SELECT c.*, u.name as remitente_nombre 
              FROM comunicacion c 
              JOIN users u ON c.remitente_id = u.id 
              WHERE (c.destinatario_id = :user_id OR c.destinatario_id IS NULL) 
              ORDER BY c.created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $comunicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error al obtener información: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empleado - Sistema de Edificios</title>
</head>
<body>
    <h1>Panel de Empleado</h1>
    <p>Bienvenido, <?php echo $_SESSION['user_name']; ?> | Cargo: <?php echo $empleado['cargo']; ?> | <a href="../../logout.php">Cerrar Sesión</a></p>
    
    <hr>
    
    <?php if (isset($error)): ?>
        <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <strong>Error:</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <!-- Columna izquierda: Tareas -->
        <div style="flex: 1; min-width: 400px;">
            <h2>Mis Tareas Asignadas</h2>
            
            <?php if (!empty($tareas)): ?>
                <?php foreach ($tareas as $tarea): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;">
                        <h3 style="margin-top: 0; color: <?php 
                            echo $tarea['estado'] == 'completada' ? 'green' : 
                                ($tarea['estado'] == 'en_progreso' ? 'orange' : 'black'); 
                        ?>;">
                            <?php echo htmlspecialchars($tarea['titulo']); ?>
                        </h3>
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($tarea['descripcion'] ?? 'Sin descripción'); ?></p>
                        <p><strong>Asignado por:</strong> <?php echo htmlspecialchars($tarea['asignado_por_nombre']); ?></p>
                        <p><strong>Fecha asignación:</strong> <?php echo $tarea['fecha_asignacion']; ?></p>
                        <p><strong>Fecha vencimiento:</strong> <?php echo $tarea['fecha_vencimiento'] ?? 'Sin fecha límite'; ?></p>
                        <p><strong>Prioridad:</strong> 
                            <span style="color: <?php 
                                echo $tarea['prioridad'] == 'alta' ? 'red' : 
                                    ($tarea['prioridad'] == 'media' ? 'orange' : 'green'); 
                            ?>;">
                                <?php echo ucfirst($tarea['prioridad']); ?>
                            </span>
                        </p>
                        <p><strong>Estado:</strong> 
                            <span style="color: <?php 
                                echo $tarea['estado'] == 'completada' ? 'green' : 
                                    ($tarea['estado'] == 'en_progreso' ? 'orange' : 'red'); 
                            ?>;">
                                <?php echo str_replace('_', ' ', ucfirst($tarea['estado'])); ?>
                            </span>
                        </p>
                        <?php if ($tarea['observaciones']): ?>
                            <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($tarea['observaciones']); ?></p>
                        <?php endif; ?>
                        
                        <a href="actualizar_tarea.php?id=<?php echo $tarea['id']; ?>" 
                           style="background: #007cba; color: white; padding: 8px 15px; text-decoration: none; font-size: 14px;">
                           Actualizar Estado
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tienes tareas asignadas en este momento.</p>
            <?php endif; ?>
        </div>
        
        <!-- Columna derecha: Comunicaciones -->
        <div style="flex: 1; min-width: 300px;">
            <h2>Comunicaciones Recientes</h2>
            
            <?php if (!empty($comunicaciones)): ?>
                <?php foreach ($comunicaciones as $comunicacion): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: <?php echo $comunicacion['leido'] ? '#f9f9f9' : '#e6f3ff'; ?>;">
                        <h4 style="margin-top: 0;">
                            <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                            <?php if (!$comunicacion['leido']): ?>
                                <span style="color: red; font-size: 12px;">[NUEVO]</span>
                            <?php endif; ?>
                        </h4>
                        <p><strong>De:</strong> <?php echo htmlspecialchars($comunicacion['remitente_nombre']); ?></p>
                        <p><strong>Tipo:</strong> <?php echo str_replace('_', ' ', ucfirst($comunicacion['tipo'])); ?></p>
                        <p><strong>Prioridad:</strong> 
                            <span style="color: <?php 
                                echo $comunicacion['prioridad'] == 'alta' ? 'red' : 
                                    ($comunicacion['prioridad'] == 'media' ? 'orange' : 'green'); 
                            ?>;">
                                <?php echo ucfirst($comunicacion['prioridad']); ?>
                            </span>
                        </p>
                        <p><?php echo htmlspecialchars(substr($comunicacion['mensaje'], 0, 150)); ?><?php echo strlen($comunicacion['mensaje']) > 150 ? '...' : ''; ?></p>
                        <small>Fecha: <?php echo $comunicacion['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comunicaciones recientes.</p>
            <?php endif; ?>
            
            <a href="comunicaciones.php" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none;">
                Ver Todas las Comunicaciones
            </a>
        </div>
    </div>
</body>
</html>