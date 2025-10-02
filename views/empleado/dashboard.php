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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header h1 {
            color: #2F455C;
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .welcome-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(135deg, #34F5C5, #21D0B2);
            padding: 15px 25px;
            border-radius: 15px;
            color: #2F455C;
            font-weight: 600;
        }

        .cargo-badge {
            background: linear-gradient(135deg, #6f42c1, #8b5cf6);
            color: white;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .tasks-section, .communications-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: #2F455C;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .task-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(52, 245, 197, 0.1), rgba(33, 208, 178, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .task-card:hover::before {
            opacity: 1;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .task-card.pendiente {
            border-left-color: #ffc107;
        }

        .task-card.en_progreso {
            border-left-color: #17a2b8;
        }

        .task-card.completada {
            border-left-color: #28a745;
        }

        .task-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .task-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .priority-badge, .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-alta {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            color: white;
        }

        .priority-media {
            background: linear-gradient(135deg, #ffa726, #ff9800);
            color: white;
        }

        .priority-baja {
            background: linear-gradient(135deg, #66bb6a, #4caf50);
            color: white;
        }

        .status-pendiente {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: #2F455C;
        }

        .status-en_progreso {
            background: linear-gradient(135deg, #17a2b8, #0288d1);
            color: white;
        }

        .status-completada {
            background: linear-gradient(135deg, #28a745, #2e7d32);
            color: white;
        }

        .update-btn {
            background: linear-gradient(135deg, #007cba, #0288d1);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }

        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 124, 186, 0.3);
        }

        .communication-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .communication-card.unread {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-left: 4px solid #2196f3;
        }

        .communication-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .comm-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .comm-title {
            font-weight: 700;
            color: #2F455C;
            margin-bottom: 5px;
        }

        .new-badge {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .comm-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .comm-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .comm-message {
            color: #495057;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .comm-date {
            font-size: 0.8rem;
            color: #868e96;
            font-style: italic;
        }

        .view-all-btn {
            background: linear-gradient(135deg, #28a745, #2e7d32);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .error-alert {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 1px solid #f1aeb5;
            color: #721c24;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .welcome-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .task-meta {
                grid-template-columns: 1fr;
            }
            
            .comm-meta {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1><i class="fas fa-user-tie"></i>Panel de Empleado</h1>
            <div class="welcome-info">
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    Bienvenido, <?php echo $_SESSION['user_name']; ?>
                </div>
                <div class="cargo-badge">
                    <i class="fas fa-briefcase"></i>
                    <?php echo $empleado['cargo']; ?>
                </div>
                <a href="../../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Error:</strong> <?php echo $error; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="main-content">
            <!-- Columna izquierda: Tareas -->
            <div class="tasks-section">
                <div class="section-header">
                    <i class="fas fa-tasks"></i>
                    Mis Tareas Asignadas
                </div>
                
                <?php if (!empty($tareas)): ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <div class="task-card <?php echo $tarea['estado']; ?>">
                            <div class="task-title" style="color: <?php 
                                echo $tarea['estado'] == 'completada' ? '#28a745' : 
                                    ($tarea['estado'] == 'en_progreso' ? '#17a2b8' : '#2F455C'); 
                            ?>;">
                                <?php echo htmlspecialchars($tarea['titulo']); ?>
                            </div>
                            
                            <div class="task-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <strong>Asignado por:</strong> <?php echo htmlspecialchars($tarea['asignado_por_nombre']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-plus"></i>
                                    <strong>Asignación:</strong> <?php echo $tarea['fecha_asignacion']; ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-times"></i>
                                    <strong>Vencimiento:</strong> <?php echo $tarea['fecha_vencimiento'] ?? 'Sin fecha límite'; ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-flag"></i>
                                    <strong>Prioridad:</strong> 
                                    <span class="priority-badge priority-<?php echo $tarea['prioridad']; ?>">
                                        <?php echo ucfirst($tarea['prioridad']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <p style="margin-bottom: 15px; position: relative; z-index: 1;">
                                <strong><i class="fas fa-align-left"></i> Descripción:</strong> 
                                <?php echo htmlspecialchars($tarea['descripcion'] ?? 'Sin descripción'); ?>
                            </p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1;">
                                <span class="status-badge status-<?php echo $tarea['estado']; ?>">
                                    <i class="fas fa-info-circle"></i>
                                    <?php echo str_replace('_', ' ', ucfirst($tarea['estado'])); ?>
                                </span>
                                
                                <a href="actualizar_tarea.php?id=<?php echo $tarea['id']; ?>" class="update-btn">
                                    <i class="fas fa-edit"></i>
                                    Actualizar Estado
                                </a>
                            </div>
                            
                            <?php if ($tarea['observaciones']): ?>
                                <div style="margin-top: 15px; padding: 15px; background: rgba(0,0,0,0.05); border-radius: 10px; position: relative; z-index: 1;">
                                    <strong><i class="fas fa-comment"></i> Observaciones:</strong> 
                                    <?php echo htmlspecialchars($tarea['observaciones']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No tienes tareas asignadas en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Columna derecha: Comunicaciones -->
            <div class="communications-section">
                <div class="section-header">
                    <i class="fas fa-comments"></i>
                    Comunicaciones Recientes
                </div>
                
                <?php if (!empty($comunicaciones)): ?>
                    <?php foreach ($comunicaciones as $comunicacion): ?>
                        <div class="communication-card <?php echo !$comunicacion['leido'] ? 'unread' : ''; ?>">
                            <div class="comm-header">
                                <div class="comm-title">
                                    <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                                </div>
                                <?php if (!$comunicacion['leido']): ?>
                                    <span class="new-badge">
                                        <i class="fas fa-star"></i> NUEVO
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="comm-meta">
                                <div class="comm-meta-item">
                                    <i class="fas fa-user"></i>
                                    <strong>De:</strong> <?php echo htmlspecialchars($comunicacion['remitente_nombre']); ?>
                                </div>
                                <div class="comm-meta-item">
                                    <i class="fas fa-tag"></i>
                                    <strong>Tipo:</strong> <?php echo str_replace('_', ' ', ucfirst($comunicacion['tipo'])); ?>
                                </div>
                                <div class="comm-meta-item">
                                    <i class="fas fa-flag"></i>
                                    <strong>Prioridad:</strong> 
                                    <span class="priority-badge priority-<?php echo $comunicacion['prioridad']; ?>">
                                        <?php echo ucfirst($comunicacion['prioridad']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="comm-message">
                                <?php echo htmlspecialchars(substr($comunicacion['mensaje'], 0, 150)); ?><?php echo strlen($comunicacion['mensaje']) > 150 ? '...' : ''; ?>
                            </div>
                            
                            <div class="comm-date">
                                <i class="fas fa-clock"></i>
                                Fecha: <?php echo $comunicacion['created_at']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <a href="comunicaciones.php" class="view-all-btn">
                        <i class="fas fa-envelope-open"></i>
                        Ver Todas las Comunicaciones
                    </a>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay comunicaciones recientes.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>