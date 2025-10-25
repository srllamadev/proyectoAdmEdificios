<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Áreas Comunes - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener áreas comunes
$areas = [];
try {
    $query = "SELECT a.*, 
                     COUNT(r.id) as total_reservas,
                     COUNT(CASE WHEN r.fecha_inicio >= NOW() AND r.estado = 'confirmada' THEN 1 END) as reservas_futuras
              FROM areas_comunes a 
              LEFT JOIN reservas r ON a.id = r.area_comun_id
              GROUP BY a.id
              ORDER BY a.nombre";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error al obtener áreas comunes: " . $e->getMessage();
}
?>

<div class="page-header">
    <h1><i class="fas fa-building"></i> Áreas Comunes</h1>
    <p>Gestión de espacios comunes del edificio</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-number"><?php echo count($areas); ?></div>
        <div class="stat-label">Total Áreas</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--secondary-green), var(--accent-mint));">
        <div class="stat-number"><?php echo count(array_filter($areas, function($a) { return $a['estado'] == 'disponible'; })); ?></div>
        <div class="stat-label">Disponibles</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green));">
        <div class="stat-number"><?php echo array_sum(array_column($areas, 'capacidad')); ?></div>
        <div class="stat-label">Capacidad Total</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ffeb3b);">
        <div class="stat-number"><?php echo array_sum(array_column($areas, 'reservas_futuras')); ?></div>
        <div class="stat-label">Reservas Futuras</div>
    </div>
</div>

<!-- Acciones -->
<div class="bento-card" style="margin-bottom: 30px;">
    <h3><i class="fas fa-plus-circle"></i> Gestión de Áreas</h3>
    <div class="d-flex gap-10" style="flex-wrap: wrap;">
        <a href="nueva_area.php" class="btn" style="background: var(--secondary-green);">
            <i class="fas fa-plus"></i> Nueva Área Común
        </a>
        <a href="reservas.php" class="btn" style="background: var(--primary-blue);">
            <i class="fas fa-calendar"></i> Ver Reservas
        </a>
        <a href="dashboard.php" class="btn" style="background: var(--dark-blue);">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
</div>

<!-- Lista de áreas -->
<div class="bento-grid">
    <?php if (!empty($areas)): ?>
        <?php foreach ($areas as $area): ?>
            <div class="bento-card">
                <div class="d-flex justify-between align-center" style="margin-bottom: 15px;">
                    <h3 style="margin: 0; color: var(--dark-blue);">
                        <i class="fas fa-building"></i>
                        <?php echo htmlspecialchars($area['nombre']); ?>
                    </h3>
                    <?php if ($area['estado'] == 'disponible'): ?>
                        <span class="status-badge status-active">
                            <i class="fas fa-check-circle"></i> Disponible
                        </span>
                    <?php elseif ($area['estado'] == 'mantenimiento'): ?>
                        <span class="status-badge status-pending">
                            <i class="fas fa-tools"></i> Mantenimiento
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-expired">
                            <i class="fas fa-times-circle"></i> Fuera de Servicio
                        </span>
                    <?php endif; ?>
                </div>
                
                <p style="color: var(--dark-gray); margin-bottom: 15px;">
                    <?php echo htmlspecialchars($area['descripcion'] ?? 'Sin descripción'); ?>
                </p>
                
                <div class="d-flex justify-between align-center" style="margin-bottom: 15px; font-size: 0.9rem;">
                    <div>
                        <strong style="color: var(--primary-blue);">Capacidad:</strong> 
                        <span style="color: var(--dark-gray);"><?php echo $area['capacidad']; ?> personas</span>
                    </div>
                    <div>
                        <strong style="color: var(--primary-blue);">Horario:</strong> 
                        <span style="color: var(--dark-gray);">
                            <?php 
                            if ($area['horario_apertura'] && $area['horario_cierre']) {
                                echo date('H:i', strtotime($area['horario_apertura'])) . ' - ' . date('H:i', strtotime($area['horario_cierre']));
                            } else {
                                echo 'No definido';
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($area['precio_hora'] > 0): ?>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: var(--secondary-green);">Precio por hora:</strong> 
                        <?php echo formatCurrency($area['precio_hora']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-between align-center" style="margin-bottom: 20px; padding: 10px; background: var(--light-gray); border-radius: 5px;">
                    <div class="text-center">
                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--primary-blue);">
                            <?php echo $area['total_reservas']; ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--dark-gray);">Total Reservas</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--secondary-green);">
                            <?php echo $area['reservas_futuras']; ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--dark-gray);">Futuras</div>
                    </div>
                </div>
                
                <div class="d-flex gap-10" style="flex-wrap: wrap;">
                    <a href="editar_area.php?id=<?php echo $area['id']; ?>" 
                       class="btn btn-sm" style="background: var(--primary-blue); color: white; flex: 1;">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="reservas_area.php?id=<?php echo $area['id']; ?>" 
                       class="btn btn-sm" style="background: var(--secondary-green); color: white; flex: 1;">
                        <i class="fas fa-calendar"></i> Reservas
                    </a>
                    <?php if ($area['estado'] == 'disponible'): ?>
                        <a href="desactivar_area.php?id=<?php echo $area['id']; ?>" 
                           class="btn btn-sm" style="background: #dc3545; color: white;"
                           onclick="return confirm('¿Desactivar esta área?')">
                            <i class="fas fa-ban"></i>
                        </a>
                    <?php else: ?>
                        <a href="activar_area.php?id=<?php echo $area['id']; ?>" 
                           class="btn btn-sm" style="background: var(--secondary-green); color: white;"
                           onclick="return confirm('¿Activar esta área?')">
                            <i class="fas fa-check"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bento-card" style="grid-column: 1 / -1;">
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-building" style="font-size: 3rem; color: var(--dark-gray); margin-bottom: 15px;"></i>
                <h3 style="color: var(--dark-gray);">No hay áreas comunes registradas</h3>
                <p style="color: var(--dark-gray); margin-bottom: 20px;">Comience agregando las áreas comunes del edificio.</p>
                <a href="nueva_area.php" class="btn" style="background: var(--secondary-green);">
                    <i class="fas fa-plus"></i> Agregar Primera Área
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>