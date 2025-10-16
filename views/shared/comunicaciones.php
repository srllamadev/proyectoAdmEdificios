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
</head>
<body>
    <h1>Centro de Comunicaciones</h1>
    <p><a href="<?php echo $ruta_dashboard; ?>">← Volver al Dashboard</a> | <a href="../../logout.php">Cerrar Sesión</a></p>
    
    <hr>
    
    <?php if (isset($error)): ?>
        <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;">
            <strong>Error:</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <h2>Mis Comunicaciones</h2>
    
    <?php if (!empty($comunicaciones)): ?>
        <div style="margin: 20px 0;">
            <?php foreach ($comunicaciones as $comunicacion): ?>
                <div style="border: 1px solid #ddd; padding: 20px; margin: 15px 0; background: <?php echo $comunicacion['leido'] ? '#f9f9f9' : '#e6f3ff'; ?>; border-left: 4px solid <?php 
                    echo $comunicacion['prioridad'] == 'alta' ? 'red' : 
                        ($comunicacion['prioridad'] == 'media' ? 'orange' : 'green'); 
                ?>;">
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
    
    <h3>Resumen de Comunicaciones</h3>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <?php
        $total = count($comunicaciones);
        $no_leidas = count(array_filter($comunicaciones, function($c) { return !$c['leido']; }));
        $prioridad_alta = count(array_filter($comunicaciones, function($c) { return $c['prioridad'] == 'alta'; }));
        ?>
        <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
            <h4>Total</h4>
            <p style="font-size: 24px; font-weight: bold; margin: 0;"><?php echo $total; ?></p>
        </div>
        <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
            <h4>No Leídas</h4>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: <?php echo $no_leidas > 0 ? 'red' : 'green'; ?>;"><?php echo $no_leidas; ?></p>
        </div>
        <div style="border: 1px solid #ccc; padding: 15px; min-width: 150px; text-align: center;">
            <h4>Alta Prioridad</h4>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: <?php echo $prioridad_alta > 0 ? 'red' : 'green'; ?>;"><?php echo $prioridad_alta; ?></p>
        </div>
    </div>
</body>
</html>