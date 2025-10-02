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

// Obtener reservas con información de usuarios y áreas
try {
    $query = "SELECT r.*, a.nombre as area_nombre, a.capacidad, u.name as usuario_nombre, u.email,
                     CASE 
                        WHEN r.fecha < CURDATE() THEN 'finalizada'
                        WHEN r.fecha = CURDATE() AND r.hora_fin < CURTIME() THEN 'finalizada'
                        WHEN r.fecha = CURDATE() AND r.hora_inicio <= CURTIME() AND r.hora_fin >= CURTIME() THEN 'en_curso'
                        ELSE 'programada'
                     END as estado_calculado
              FROM reservas r 
              JOIN areas_comunes a ON r.area_id = a.id
              JOIN users u ON r.user_id = u.id
              ORDER BY r.fecha DESC, r.hora_inicio DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas
    $total_reservas = count($reservas);
    $reservas_hoy = count(array_filter($reservas, function($r) { return $r['fecha'] == date('Y-m-d'); }));
    $reservas_activas = count(array_filter($reservas, function($r) { return $r['estado'] == 'activa'; }));
    $reservas_pendientes = count(array_filter($reservas, function($r) { return $r['estado'] == 'pendiente'; }));
    
} catch (PDOException $e) {
    $error = "Error al obtener reservas: " . $e->getMessage();
}
?>

<div class="page-header">
    <h1><i class="fas fa-calendar-check"></i> Gestión de Reservas</h1>
    <p>Administración de reservas de áreas comunes</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-number"><?php echo $total_reservas; ?></div>
        <div class="stat-label">Total Reservas</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green));">
        <div class="stat-number"><?php echo $reservas_hoy; ?></div>
        <div class="stat-label">Reservas Hoy</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--secondary-green), var(--accent-mint));">
        <div class="stat-number"><?php echo $reservas_activas; ?></div>
        <div class="stat-label">Activas</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ffeb3b);">
        <div class="stat-number"><?php echo $reservas_pendientes; ?></div>
        <div class="stat-label">Pendientes</div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="bento-grid" style="margin-bottom: 30px;">
    <div class="bento-card" style="background: linear-gradient(135deg, var(--accent-mint), var(--secondary-green));">
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
                        <th><i class="fas fa-users"></i> Personas</th>
                        <th><i class="fas fa-traffic-light"></i> Estado</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><strong><?php echo $reserva['id']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($reserva['usuario_nombre']); ?>
                                <br><small style="color: var(--dark-gray);"><?php echo htmlspecialchars($reserva['email']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($reserva['area_nombre']); ?></strong>
                                <br><small style="color: var(--dark-gray);">Capacidad: <?php echo $reserva['capacidad']; ?> personas</small>
                            </td>
                            <td>
                                <i class="fas fa-calendar-alt"></i> <?php echo formatDate($reserva['fecha']); ?>
                            </td>
                            <td>
                                <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($reserva['hora_inicio'])); ?> - <?php echo date('H:i', strtotime($reserva['hora_fin'])); ?>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--accent-mint); color: var(--dark-blue);">
                                    <?php echo $reserva['numero_personas']; ?> personas
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