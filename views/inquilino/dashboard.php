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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #E8F5F1 0%, #D4F1E8 30%, #C8EFE0 60%, #E1F0FF 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 155, 119, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .header:hover {
            box-shadow: 0 12px 40px rgba(0, 155, 119, 0.25);
            border: 1px solid rgba(0, 155, 119, 0.4);
        }

        .header h1 {
            background: linear-gradient(135deg, #001F54, #009B77);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 i {
            background: linear-gradient(135deg, #009B77, #7ED957);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            background: linear-gradient(135deg, #009B77, #7ED957);
            padding: 15px 25px;
            border-radius: 15px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 155, 119, 0.3);
            transition: all 0.3s ease;
        }

        .user-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 155, 119, 0.4);
        }

        .apartment-badge {
            background: linear-gradient(135deg, #D4AF37, #F4D03F);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
        }

        .apartment-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
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

        .rental-info {
            background: rgba(0, 155, 119, 0.15);
            backdrop-filter: blur(16px);
            border-left: 5px solid #009B77;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 155, 119, 0.2);
            border: 1px solid rgba(0, 155, 119, 0.3);
            transition: all 0.3s ease;
        }

        .rental-info:hover {
            box-shadow: 0 12px 35px rgba(0, 155, 119, 0.3);
        }

        .rental-info h3 {
            background: linear-gradient(135deg, #001F54, #009B77);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .rental-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .rental-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-btn {
            background: linear-gradient(135deg, #009B77, #7ED957);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(0, 155, 119, 0.25);
        }

        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 155, 119, 0.4);
        }

        .action-btn.payments {
            background: linear-gradient(135deg, #001F54, #009B77);
            box-shadow: 0 8px 25px rgba(0, 155, 119, 0.25);
        }

        .action-btn.payments:hover {
            box-shadow: 0 15px 35px rgba(0, 155, 119, 0.4);
        }

        .action-btn.communications {
            background: linear-gradient(135deg, #D4AF37, #F4D03F);
            color: white;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25);
        }

        .action-btn.communications:hover {
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
        }

        .action-btn i {
            font-size: 2rem;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .payments-section, .activities-section {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 155, 119, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .payments-section:hover, .activities-section:hover {
            box-shadow: 0 12px 40px rgba(0, 155, 119, 0.25);
            border: 1px solid rgba(0, 155, 119, 0.4);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #001F54, #009B77);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .payment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 155, 119, 0.05), rgba(126, 217, 87, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .payment-card:hover::before {
            opacity: 1;
        }

        .payment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 155, 119, 0.2);
            border-color: rgba(0, 155, 119, 0.4);
        }

        .payment-card.pagado {
            border-left-color: #7ED957;
            border-left-width: 5px;
            background: rgba(126, 217, 87, 0.15);
        }

        .payment-card.pendiente {
            border-left-color: #D4AF37;
            border-left-width: 5px;
            background: rgba(212, 175, 55, 0.15);
        }

        .payment-card.vencido {
            border-left-color: #f44336;
            border-left-width: 5px;
            background: rgba(244, 67, 54, 0.15);
        }
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fdf2f2, #f8d7da);
        }

        .payment-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .payment-amount {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .payment-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
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

        .status-badge {
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            position: relative;
            z-index: 1;
        }

        .status-pagado {
            background: linear-gradient(135deg, #7ED957, #A8E063);
            color: white;
            box-shadow: 0 4px 12px rgba(126, 217, 87, 0.3);
        }

        .status-pendiente {
            background: linear-gradient(135deg, #D4AF37, #F4D03F);
            color: white;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
        }

        .status-vencido {
            background: linear-gradient(135deg, #f44336, #ff5252);
            color: white;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }

        .reservation-card {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            border-left: 4px solid;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .reservation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 155, 119, 0.2);
            border-color: rgba(0, 155, 119, 0.4);
        }

        .reservation-card.confirmada {
            border-left-color: #7ED957;
            border-left-width: 4px;
        }

        .reservation-card.pendiente {
            border-left-color: #D4AF37;
            border-left-width: 4px;
        }

        .reservation-card.cancelada {
            border-left-color: #f44336;
            border-left-width: 4px;
        }

        .communication-card {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .communication-card.unread {
            background: rgba(0, 155, 119, 0.15);
            border-left: 4px solid #009B77;
            border: 1px solid rgba(0, 155, 119, 0.3);
        }

        .communication-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 155, 119, 0.2);
            border: 1px solid rgba(0, 155, 119, 0.4);
        }

        .comm-title {
            font-weight: 700;
            color: #001F54;
            margin-bottom: 10px;
        }

        .new-badge {
            background: linear-gradient(135deg, #ff6b6b, #ff5252);
            color: white;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
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
            
            .payment-meta {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .rental-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1><i class="fas fa-home"></i>Panel de Inquilino</h1>
            <div class="welcome-info">
                <div class="user-info">
                    <i class="fas fa-user"></i>
                    Bienvenido, <?php echo $_SESSION['user_name']; ?>
                </div>
                <div class="apartment-badge">
                    <i class="fas fa-door-open"></i>
                    Departamento: <?php echo $inquilino['numero_departamento'] ?? 'Sin asignar'; ?>
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
        
        <!-- Información del Alquiler -->
        <?php if ($inquilino && $inquilino['numero_departamento']): ?>
            <div class="rental-info">
                <h3><i class="fas fa-info-circle"></i>Información de su Alquiler</h3>
                <div class="rental-details">
                    <div class="rental-detail">
                        <i class="fas fa-building"></i>
                        <div>
                            <strong>Departamento:</strong> <?php echo $inquilino['numero_departamento']; ?>
                        </div>
                    </div>
                    <div class="rental-detail">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <strong>Precio Mensual:</strong> $<?php echo number_format($inquilino['precio_mensual'], 2); ?>
                        </div>
                    </div>
                    <div class="rental-detail">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Estado:</strong> <?php echo ucfirst($inquilino['estado_alquiler']); ?>
                        </div>
                    </div>
                    <div class="rental-detail">
                        <i class="fas fa-calendar-check"></i>
                        <div>
                            <strong>Fecha de Ingreso:</strong> <?php echo $inquilino['fecha_ingreso']; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Accesos Rápidos -->
        <div class="quick-actions">
            <a href="reservas.php" class="action-btn">
                <i class="fas fa-calendar-plus"></i>
                <span>Reservar Áreas Comunes</span>
            </a>
            <a href="pagos.php" class="action-btn payments">
                <i class="fas fa-credit-card"></i>
                <span>Ver Mis Pagos</span>
            </a>
            <a href="../shared/comunicaciones.php" class="action-btn communications">
                <i class="fas fa-comments"></i>
                <span>Ver Comunicaciones</span>
            </a>
        </div>
        
        <div class="main-content">
            <!-- Columna izquierda: Pagos -->
            <div class="payments-section">
                <div class="section-header">
                    <i class="fas fa-money-bill-wave"></i>
                    Estado de Pagos
                </div>
                
                <?php if (!empty($pagos)): ?>
                    <?php foreach ($pagos as $pago): ?>
                        <div class="payment-card <?php echo $pago['estado']; ?>">
                            <div class="payment-title" style="color: <?php 
                                echo $pago['estado'] == 'pagado' ? '#28a745' : 
                                    ($pago['estado'] == 'vencido' ? '#dc3545' : '#ffc107'); 
                            ?>;">
                                <?php echo htmlspecialchars($pago['descripcion']); ?>
                            </div>
                            
                            <div class="payment-amount" style="color: <?php 
                                echo $pago['estado'] == 'pagado' ? '#28a745' : 
                                    ($pago['estado'] == 'vencido' ? '#dc3545' : '#e67e22'); 
                            ?>;">
                                <i class="fas fa-dollar-sign"></i>
                                $<?php echo number_format($pago['monto'], 2); ?>
                            </div>
                            
                            <div class="payment-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar-times"></i>
                                    <strong>Vencimiento:</strong> <?php echo $pago['fecha_vencimiento']; ?>
                                </div>
                                <?php if ($pago['fecha_pago']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <strong>Fecha Pago:</strong> <?php echo $pago['fecha_pago']; ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-credit-card"></i>
                                        <strong>Método:</strong> <?php echo $pago['metodo_pago']; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pago['recargo'] > 0): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Recargo:</strong> $<?php echo number_format($pago['recargo'], 2); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div style="text-align: right;">
                                <span class="status-badge status-<?php echo $pago['estado']; ?>">
                                    <i class="fas fa-info-circle"></i>
                                    <?php echo ucfirst($pago['estado']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No hay registros de pagos.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Columna derecha: Reservas y Comunicaciones -->
            <div class="activities-section">
                <div class="section-header">
                    <i class="fas fa-calendar-alt"></i>
                    Mis Reservas Recientes
                </div>
                
                <?php if (!empty($reservas)): ?>
                    <?php foreach ($reservas as $reserva): ?>
                        <div class="reservation-card <?php echo $reserva['estado']; ?>">
                            <h4 style="margin-bottom: 15px; color: #2F455C; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($reserva['area_nombre']); ?>
                            </h4>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-hourglass-end"></i>
                                    <strong>Hasta:</strong> <?php echo date('H:i', strtotime($reserva['fecha_fin'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <strong>Precio:</strong> $<?php echo number_format($reserva['precio_total'], 2); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span class="status-badge status-<?php echo $reserva['estado']; ?>">
                                        <?php echo ucfirst($reserva['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($reserva['descripcion']): ?>
                                <div style="background: rgba(0,0,0,0.05); padding: 10px; border-radius: 8px;">
                                    <strong><i class="fas fa-comment"></i> Descripción:</strong> 
                                    <?php echo htmlspecialchars($reserva['descripcion']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No tienes reservas registradas.</p>
                    </div>
                <?php endif; ?>
                
                <hr style="margin: 30px 0; border: none; height: 1px; background: linear-gradient(to right, transparent, #ddd, transparent);">
                
                <div class="section-header">
                    <i class="fas fa-envelope"></i>
                    Comunicaciones Recientes
                </div>
                
                <?php if (!empty($comunicaciones)): ?>
                    <?php foreach (array_slice($comunicaciones, 0, 3) as $comunicacion): ?>
                        <div class="communication-card <?php echo !$comunicacion['leido'] ? 'unread' : ''; ?>">
                            <div class="comm-title" style="display: flex; justify-content: space-between; align-items: center;">
                                <span><?php echo htmlspecialchars($comunicacion['asunto']); ?></span>
                                <?php if (!$comunicacion['leido']): ?>
                                    <span class="new-badge">
                                        <i class="fas fa-star"></i> NUEVO
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: flex; gap: 15px; margin-bottom: 10px; font-size: 0.9rem; color: #6c757d;">
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-user"></i>
                                    <strong>De:</strong> <?php echo htmlspecialchars($comunicacion['remitente_nombre']); ?>
                                </div>
                            </div>
                            
                            <p style="color: #495057; line-height: 1.5; margin-bottom: 10px;">
                                <?php echo htmlspecialchars(substr($comunicacion['mensaje'], 0, 100)); ?><?php echo strlen($comunicacion['mensaje']) > 100 ? '...' : ''; ?>
                            </p>
                            
                            <small style="color: #868e96; display: flex; align-items: center; gap: 5px;">
                                <i class="fas fa-clock"></i>
                                Fecha: <?php echo $comunicacion['created_at']; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
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