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
    <style>
        body {
            background: linear-gradient(135deg, #E8F5F1 0%, #D4F1E8 30%, #C8EFE0 60%, #E1F0FF 100%);
            background-attachment: fixed;
        }

        .bento-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .bento-nav-links {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .bento-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .bento-btn-secondary {
            background: linear-gradient(135deg, #001F54, #009B77);
            color: white;
        }

        .bento-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 155, 119, 0.4);
        }

        .bento-btn-outline {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            color: #2F2F2F;
            border: 2px solid rgba(0, 155, 119, 0.3);
        }

        .bento-btn-outline:hover {
            background: rgba(244, 67, 54, 0.2);
            border-color: #f44336;
            color: #f44336;
            transform: translateY(-2px);
        }

        .bento-section-title {
            background: linear-gradient(135deg, #001F54, #009B77);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 30px 0 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .bento-communications-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }

        .bento-communication-card {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 155, 119, 0.15);
        }

        .bento-communication-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(135deg, #009B77, #7ED957);
        }

        .bento-communication-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 155, 119, 0.25);
            border: 1px solid rgba(0, 155, 119, 0.4);
        }

        .bento-communication-unread {
            background: rgba(0, 155, 119, 0.15);
            border: 2px solid rgba(0, 155, 119, 0.4);
        }

        .bento-communication-unread::before {
            width: 8px;
            background: linear-gradient(135deg, #7ED957, #A8E063);
            box-shadow: 0 0 15px rgba(126, 217, 87, 0.5);
        }

        .bento-priority-alta::before {
            background: linear-gradient(135deg, #f44336, #ff5252) !important;
        }

        .bento-priority-media::before {
            background: linear-gradient(135deg, #D4AF37, #F4D03F) !important;
        }

        .bento-priority-baja::before {
            background: linear-gradient(135deg, #7ED957, #A8E063) !important;
        }

        .bento-communication-card h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .bento-communication-card .nuevo-badge {
            background: linear-gradient(135deg, #f44336, #ff5252);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            margin-left: 10px;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bento-communication-card .marcar-leido-btn {
            background: linear-gradient(135deg, #009B77, #7ED957);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            font-size: 0.85rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(0, 155, 119, 0.3);
        }

        .bento-communication-card .marcar-leido-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 155, 119, 0.4);
        }

        .bento-communication-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
            padding: 15px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .bento-communication-meta p {
            margin: 5px 0;
            color: #2F2F2F;
            font-size: 0.95rem;
        }

        .bento-communication-meta strong {
            color: #001F54;
            font-weight: 700;
        }

        .bento-priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .bento-priority-badge.alta {
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.25), rgba(255, 82, 82, 0.25));
            color: #f44336;
            border: 1px solid rgba(244, 67, 54, 0.4);
        }

        .bento-priority-badge.media {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.25), rgba(244, 208, 63, 0.25));
            color: #D4AF37;
            border: 1px solid rgba(212, 175, 55, 0.4);
        }

        .bento-priority-badge.baja {
            background: linear-gradient(135deg, rgba(126, 217, 87, 0.25), rgba(168, 224, 99, 0.25));
            color: #7ED957;
            border: 1px solid rgba(126, 217, 87, 0.4);
        }

        .bento-communication-message {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
            border: 1px solid rgba(0, 155, 119, 0.2);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .bento-communication-message p {
            margin: 0;
            line-height: 1.7;
            white-space: pre-wrap;
            color: #2F2F2F;
            font-size: 1rem;
        }

        .bento-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .bento-stat-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 155, 119, 0.15);
            position: relative;
            overflow: hidden;
        }

        .bento-stat-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #009B77, #7ED957);
        }

        .bento-stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 155, 119, 0.25);
            border: 1px solid rgba(0, 155, 119, 0.4);
        }

        .bento-stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #009B77, #7ED957);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .bento-stat-number.bento-stat-alert {
            background: linear-gradient(135deg, #f44336, #ff5252);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .bento-stat-label {
            color: #2F2F2F;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .bento-empty-state {
            text-align: center;
            padding: 60px 40px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(16px);
            border: 2px dashed rgba(0, 155, 119, 0.3);
            border-radius: 20px;
            margin: 20px 0;
        }

        .bento-empty-state h3 {
            color: #009B77;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .bento-empty-state p {
            color: #2F2F2F;
            font-size: 1.1rem;
        }

        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(0, 155, 119, 0.3), transparent);
            margin: 40px 0;
        }

        @media (max-width: 768px) {
            .bento-communication-meta {
                grid-template-columns: 1fr;
            }

            .bento-stats-grid {
                grid-template-columns: 1fr;
            }

            .bento-nav-links {
                flex-direction: column;
            }
        }
    </style>
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
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <h3>
                            <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                            <?php if (!$comunicacion['leido']): ?>
                                <span class="nuevo-badge">
                                    <i class="fas fa-star"></i> NUEVO
                                </span>
                            <?php endif; ?>
                        </h3>
                        <?php if (!$comunicacion['leido']): ?>
                            <a href="?marcar_leido=<?php echo $comunicacion['id']; ?>" class="marcar-leido-btn">
                               <i class="fas fa-check"></i> Marcar como Leído
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bento-communication-meta">
                        <p><strong><i class="fas fa-user"></i> De:</strong> <?php echo htmlspecialchars($comunicacion['remitente_nombre']); ?></p>
                        <p><strong><i class="fas fa-tag"></i> Tipo:</strong> <?php echo str_replace('_', ' ', ucfirst($comunicacion['tipo'])); ?></p>
                        <p><strong><i class="fas fa-flag"></i> Prioridad:</strong> 
                            <span class="bento-priority-badge <?php echo $comunicacion['prioridad']; ?>">
                                <?php echo ucfirst($comunicacion['prioridad']); ?>
                            </span>
                        </p>
                        <p><strong><i class="fas fa-clock"></i> Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($comunicacion['created_at'])); ?></p>
                    </div>
                    
                    <div class="bento-communication-message">
                        <p><?php echo htmlspecialchars($comunicacion['mensaje']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bento-empty-state">
            <i class="fas fa-inbox" style="font-size: 4rem; color: #009B77; opacity: 0.5; margin-bottom: 20px; display: block;"></i>
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