<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Gestión de Reservas - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Inicializar variables
$reservas = [];
$total_reservas = 0;
$reservas_hoy = 0;
$reservas_activas = 0;
$reservas_pendientes = 0;
$error = '';

// Obtener reservas con información de usuarios y áreas
try {
    // Obtener reservas con información de inquilinos y áreas comunes
    $query = "SELECT r.*, 
                     u.name as usuario_nombre, 
                     u.email as usuario_email,
                     ac.nombre as area_nombre,
                     ac.capacidad as area_capacidad
              FROM reservas r
              LEFT JOIN inquilinos i ON r.inquilino_id = i.id
              LEFT JOIN users u ON i.user_id = u.id
              LEFT JOIN areas_comunes ac ON r.area_comun_id = ac.id
              ORDER BY r.fecha_inicio DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas
    $total_reservas = count($reservas);
    $reservas_hoy = count(array_filter($reservas, function($r) { 
        return isset($r['fecha_inicio']) && date('Y-m-d', strtotime($r['fecha_inicio'])) == date('Y-m-d'); 
    }));
    $reservas_activas = count(array_filter($reservas, function($r) { 
        return isset($r['estado']) && $r['estado'] == 'confirmada'; 
    }));
    $reservas_pendientes = count(array_filter($reservas, function($r) { 
        return isset($r['estado']) && $r['estado'] == 'pendiente'; 
    }));
    
} catch (PDOException $e) {
    $error = "Error al obtener reservas: " . $e->getMessage();
}
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-calendar-check"></i> Gestión de Reservas</h1>
    <p class="bento-page-subtitle">Administración de reservas de áreas comunes</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Estadísticas -->
<div class="bento-stats-grid">
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $total_reservas; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Total Reservas</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $reservas_hoy; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Reservas Hoy</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $reservas_activas; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Confirmadas</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ffeb3b 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $reservas_pendientes; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Pendientes</div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="bento-grid" style="margin-bottom: 30px;">
    <div class="bento-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
        <h3><i class="fas fa-plus-circle"></i> Gestión de Áreas</h3>
        <p style="color: var(--dark-blue);">Administrar áreas comunes y disponibilidad.</p>
        <div class="d-flex gap-10" style="flex-wrap: wrap;">
            <a href="areas_comunes.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-building"></i> Ver Áreas Comunes
            </a>
            <a href="nueva_area.php" class="btn" style="background: var(--primary-blue); color: white;">
                <i class="fas fa-plus"></i> Nueva Área
            </a>
        </div>
    </div>
    
    <div class="bento-card" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); color: white;">
        <h3><i class="fas fa-chart-line"></i> Estadísticas de Uso</h3>
        <p style="color: rgba(255,255,255,0.9);">Análisis del uso de las áreas comunes.</p>
        <div class="d-flex justify-between align-center">
            <div>
                <strong style="font-size: 1.2rem;">
                    Activas: <?php echo $reservas_activas; ?><br>
                    Pendientes: <?php echo $reservas_pendientes; ?><br>
                    Hoy: <?php echo $reservas_hoy; ?>
                </strong>
            </div>
            <i class="fas fa-chart-bar" style="font-size: 2rem; opacity: 0.7;"></i>
        </div>
    </div>
</div>

<!-- Lista de reservas -->
<div class="bento-card">
    <h3><i class="fas fa-list"></i> Lista de Reservas</h3>
    
    <?php if (!empty($reservas)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-building"></i> Área</th>
                        <th><i class="fas fa-calendar"></i> Fecha</th>
                        <th><i class="fas fa-clock"></i> Horario</th>
                        <th><i class="fas fa-euro-sign"></i> Precio</th>
                        <th><i class="fas fa-traffic-light"></i> Estado</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><strong><?php echo $reserva['id']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($reserva['usuario_nombre'] ?? 'N/A'); ?>
                                <br><small style="color: var(--dark-gray);"><?php echo htmlspecialchars($reserva['usuario_email'] ?? 'N/A'); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($reserva['area_nombre'] ?? 'N/A'); ?></strong>
                                <br><small style="color: var(--dark-gray);">Capacidad: <?php echo $reserva['area_capacidad'] ?? 0; ?> personas</small>
                            </td>
                            <td>
                                <i class="fas fa-calendar-alt"></i> 
                                <?php 
                                if (isset($reserva['fecha_inicio'])) {
                                    echo date('d/m/Y', strtotime($reserva['fecha_inicio']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <i class="fas fa-clock"></i> 
                                <?php 
                                if (isset($reserva['fecha_inicio']) && isset($reserva['fecha_fin'])) {
                                    echo date('H:i', strtotime($reserva['fecha_inicio'])) . ' - ' . date('H:i', strtotime($reserva['fecha_fin']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--accent-mint); color: var(--dark-blue);">
                                    <?php echo number_format($reserva['precio_total'] ?? 0, 2); ?> €
                                </span>
                            </td>
                            <td>
                                <?php 
                                $estado_mostrar = $reserva['estado_calculado'] ?? $reserva['estado'];
                                switch($estado_mostrar) {
                                    case 'en_curso':
                                        echo '<span class="status-badge status-active"><i class="fas fa-play"></i> En Curso</span>';
                                        break;
                                    case 'finalizada':
                                        echo '<span class="status-badge status-expired"><i class="fas fa-check"></i> Finalizada</span>';
                                        break;
                                    case 'programada':
                                        echo '<span class="status-badge status-pending"><i class="fas fa-calendar"></i> Programada</span>';
                                        break;
                                    default:
                                        echo getStatusBadge($reserva['estado']);
                                }
                                ?>
                            </td>
                            <td>
                                <div class="d-flex gap-5">
                                    <?php if ($reserva['estado'] == 'pendiente'): ?>
                                        <a href="aprobar_reserva.php?id=<?php echo $reserva['id']; ?>" 
                                           class="btn btn-sm" style="background: var(--secondary-green); color: white; padding: 5px 10px;">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="rechazar_reserva.php?id=<?php echo $reserva['id']; ?>" 
                                           class="btn btn-sm" style="background: #dc3545; color: white; padding: 5px 10px;">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="detalle_reserva.php?id=<?php echo $reserva['id']; ?>" 
                                       class="btn btn-sm" style="background: var(--primary-blue); color: white; padding: 5px 10px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: var(--light-gray); border-radius: var(--border-radius);">
            <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--dark-gray); margin-bottom: 15px;"></i>
            <h3 style="color: var(--dark-gray);">No hay reservas registradas</h3>
            <p style="color: var(--dark-gray);">Las reservas aparecerán aquí cuando los usuarios las realicen.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
 
 