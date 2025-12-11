<?php
require_once '../../includes/functions.php';

// Verificar que está logueado
if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener comunicaciones del usuario
try {
    $query = "SELECT c.*, u.name as remitente_nombre 
              FROM comunicacion c 
              JOIN users u ON c.remitente_id = u.id 
              WHERE (c.destinatario_id = :user_id OR c.destinatario_id IS NULL) 
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $comunicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marcar como leídas si se hace clic en una comunicación
    if (isset($_GET['marcar_leido']) && is_numeric($_GET['marcar_leido'])) {
        $comunicacion_id = $_GET['marcar_leido'];
        $query = "UPDATE comunicacion SET leido = 1 WHERE id = :id AND (destinatario_id = :user_id OR destinatario_id IS NULL)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $comunicacion_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        // Recargar la página para actualizar el estado
        header('Location: shared/comunicaciones.php');
        exit();
    }
    
} catch (PDOException $e) {
    $error = "Error al obtener comunicaciones: " . $e->getMessage();
}

// Determinar la ruta de regreso según el rol
$ruta_dashboard = '';
switch($_SESSION['role']) {
    case 'admin':
        $ruta_dashboard = '../admin/dashboard.php';
        break;
    case 'empleado':
        $ruta_dashboard = '../empleado/dashboard.php';
        break;
    case 'inquilino':
        $ruta_dashboard = '../inquilino/dashboard.php';
        break;
    default:
        $ruta_dashboard = '../../login.php';
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicaciones - Sistema de Edificios</title>
    <link rel="stylesheet" href="../../assets/css/bento-glass-emerald.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bento-body">
    <div class="bento-page-header">
        <h1 class="bento-page-title"><i class="fas fa-comments"></i> Centro de Comunicaciones</h1>
        <p class="bento-page-subtitle">Mensajes y comunicaciones del edificio</p>
    </div>

    <div class="bento-container">
        <div class="bento-nav-links">
            <a href="<?php echo $ruta_dashboard; ?>" class="bento-btn bento-btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <a href="../../logout.php" class="bento-btn bento-btn-outline">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    
    <?php if (isset($error)): ?>
        <div class="bento-alert bento-alert-error">
            <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <h2 class="bento-section-title"><i class="fas fa-envelope"></i> Mis Comunicaciones</h2>
    
    <?php if (!empty($comunicaciones)): ?>
        <div class="bento-communications-list">
            <?php foreach ($comunicaciones as $comunicacion): ?>
                <div class="bento-communication-card <?php echo $comunicacion['leido'] ? 'bento-communication-read' : 'bento-communication-unread'; ?> bento-priority-<?php echo $comunicacion['prioridad']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="margin: 0; color: <?php 
                            echo $comunicacion['prioridad'] == 'alta' ? 'red' : 
                                ($comunicacion['prioridad'] == 'media' ? '#ff8c00' : 'green'); 
                        ?>;">
                            <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                            <?php if (!$comunicacion['leido']): ?>
                                <span style="background: red; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 10px;">NUEVO</span>
                            <?php endif; ?>
                        </h3>
                        <?php if (!$comunicacion['leido']): ?>
                            <a href="?marcar_leido=<?php echo $comunicacion['id']; ?>" 
                               style="background: #007cba; color: white; padding: 5px 10px; text-decoration: none; font-size: 12px; border-radius: 3px;">
                               Marcar como Leído
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin: 10px 0;">
                        <p style="margin: 5px 0;"><strong>De:</strong> <?php echo htmlspecialchars($comunicacion['remitente_nombre']); ?></p>
                        <p style="margin: 5px 0;"><strong>Tipo:</strong> <?php echo str_replace('_', ' ', ucfirst($comunicacion['tipo'])); ?></p>
                        <p style="margin: 5px 0;"><strong>Prioridad:</strong> 
                            <span style="color: <?php 
                                echo $comunicacion['prioridad'] == 'alta' ? 'red' : 
                                    ($comunicacion['prioridad'] == 'media' ? 'orange' : 'green'); 
                            ?>; font-weight: bold;">
                                <?php echo ucfirst($comunicacion['prioridad']); ?>
                            </span>
                        </p>
                        <p style="margin: 5px 0;"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($comunicacion['created_at'])); ?></p>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 15px;">
                        <p style="margin: 0; line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($comunicacion['mensaje']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: #f9f9f9; border: 1px solid #ddd;">
            <h3>No hay comunicaciones</h3>
            <p>No tienes comunicaciones en este momento.</p>
        </div>
    <?php endif; ?>
    
    <hr>
    
    <h3 class="bento-section-title"><i class="fas fa-chart-bar"></i> Resumen de Comunicaciones</h3>
    <div class="bento-stats-grid">
        <?php
        $total = count($comunicaciones);
        $no_leidas = count(array_filter($comunicaciones, function($c) { return !$c['leido']; }));
        $prioridad_alta = count(array_filter($comunicaciones, function($c) { return $c['prioridad'] == 'alta'; }));
        ?>
        <div class="bento-stat-card">
            <div class="bento-stat-number"><?php echo $total; ?></div>
            <div class="bento-stat-label">Total</div>
        </div>
        <div class="bento-stat-card">
            <div class="bento-stat-number <?php echo $no_leidas > 0 ? 'bento-stat-alert' : ''; ?>"><?php echo $no_leidas; ?></div>
            <div class="bento-stat-label">No Leídas</div>
        </div>
        <div class="bento-stat-card">
            <div class="bento-stat-number <?php echo $prioridad_alta > 0 ? 'bento-stat-alert' : ''; ?>"><?php echo $prioridad_alta; ?></div>
            <div class="bento-stat-label">Alta Prioridad</div>
        </div>
    </div>
</body>
</html>