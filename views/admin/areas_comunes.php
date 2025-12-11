<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Áreas Comunes - Administrador';
require_once '../../includes/header.php';

echo '<style>
.page-header {
    background: linear-gradient(135deg, #001F54 0%, #009B77 100%);
    color: white;
    padding: 40px 30px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(0, 155, 119, 0.3);
    text-align: center;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 800;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.page-header p {
    font-size: 1.2rem;
    opacity: 0.9;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
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

.stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0, 155, 119, 0.25);
    border: 1px solid rgba(0, 155, 119, 0.4);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, #009B77, #7ED957);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.stat-label {
    color: #2F2F2F;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.bento-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.bento-card {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 25px rgba(0, 155, 119, 0.15);
    transition: all 0.3s ease;
}

.bento-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 155, 119, 0.25);
    border: 1px solid rgba(0, 155, 119, 0.4);
}

.bento-card h3 {
    color: #001F54;
    font-weight: 700;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.status-active {
    background: linear-gradient(135deg, rgba(126, 217, 87, 0.25), rgba(168, 224, 99, 0.25));
    color: #7ED957;
    border-color: rgba(126, 217, 87, 0.4);
}

.status-pending {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.25), rgba(244, 208, 63, 0.25));
    color: #D4AF37;
    border-color: rgba(212, 175, 55, 0.4);
}

.status-expired {
    background: linear-gradient(135deg, rgba(244, 67, 54, 0.25), rgba(255, 82, 82, 0.25));
    color: #f44336;
    border-color: rgba(244, 67, 54, 0.4);
}

.btn {
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

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9rem;
}

.d-flex {
    display: flex;
}

.gap-10 {
    gap: 10px;
}

.justify-between {
    justify-content: space-between;
}

.align-center {
    align-items: center;
}

.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .bento-grid {
        grid-template-columns: 1fr;
    }
}
</style>';

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
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(0, 155, 119, 0.25), rgba(126, 217, 87, 0.25)); backdrop-filter: blur(16px);">
        <div class="stat-number"><?php echo count(array_filter($areas, function($a) { return $a['estado'] == 'disponible'; })); ?></div>
        <div class="stat-label">Disponibles</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(0, 31, 84, 0.25), rgba(0, 155, 119, 0.25)); backdrop-filter: blur(16px);">
        <div class="stat-number"><?php echo array_sum(array_column($areas, 'capacidad')); ?></div>
        <div class="stat-label">Capacidad Total</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.25), rgba(244, 208, 63, 0.25)); backdrop-filter: blur(16px);">
        <div class="stat-number"><?php echo array_sum(array_column($areas, 'reservas_futuras')); ?></div>
        <div class="stat-label">Reservas Futuras</div>
    </div>
</div>

<!-- Acciones -->
<div class="bento-card" style="margin-bottom: 30px;">
    <h3><i class="fas fa-plus-circle"></i> Gestión de Áreas</h3>
    <div class="d-flex gap-10" style="flex-wrap: wrap;">
        <a href="nueva_area.php" class="btn" style="background: linear-gradient(135deg, #009B77, #7ED957); color: white; box-shadow: 0 4px 15px rgba(0, 155, 119, 0.3);">
            <i class="fas fa-plus"></i> Nueva Área Común
        </a>
        <a href="reservas.php" class="btn" style="background: linear-gradient(135deg, #001F54, #009B77); color: white; box-shadow: 0 4px 15px rgba(0, 155, 119, 0.3);">
            <i class="fas fa-calendar"></i> Ver Reservas
        </a>
        <a href="dashboard.php" class="btn" style="background: linear-gradient(135deg, #D4AF37, #F4D03F); color: white; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
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
                    <h3 style="margin: 0; color: #001F54;">
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
                
                <p style="color: #2F2F2F; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($area['descripcion'] ?? 'Sin descripción'); ?>
                </p>
                
                <div class="d-flex justify-between align-center" style="margin-bottom: 15px; font-size: 0.9rem;">
                    <div>
                        <strong style="color: #009B77;">Capacidad:</strong> 
                        <span style="color: var(--dark-gray);"><?php echo $area['capacidad']; ?> personas</span>
                    </div>
                    <div>
                        <strong style="color: #009B77;">Horario:</strong> 
                        <span style="color: #2F2F2F;">
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
                        <strong style="color: #7ED957;">Precio por hora:</strong> 
                        <span style="color: #D4AF37; font-weight: 700; font-size: 1.1rem;"><?php echo formatCurrency($area['precio_hora']); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-between align-center" style="margin-bottom: 20px; padding: 15px; background: rgba(255, 255, 255, 0.35); backdrop-filter: blur(10px); border: 1px solid rgba(0, 155, 119, 0.2); border-radius: 12px;">
                    <div class="text-center">
                        <div style="font-size: 1.4rem; font-weight: 800; background: linear-gradient(135deg, #001F54, #009B77); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            <?php echo $area['total_reservas']; ?>
                        </div>
                        <div style="font-size: 0.8rem; color: #2F2F2F; font-weight: 600;">Total Reservas</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 1.4rem; font-weight: 800; background: linear-gradient(135deg, #009B77, #7ED957); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            <?php echo $area['reservas_futuras']; ?>
                        </div>
                        <div style="font-size: 0.8rem; color: #2F2F2F; font-weight: 600;">Futuras</div>
                    </div>
                </div>
                
                <div class="d-flex gap-10" style="flex-wrap: wrap;">
                    <a href="editar_area.php?id=<?php echo $area['id']; ?>" 
                       class="btn btn-sm" style="background: linear-gradient(135deg, #001F54, #009B77); color: white; flex: 1; box-shadow: 0 4px 12px rgba(0, 155, 119, 0.3);">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="reservas_area.php?id=<?php echo $area['id']; ?>" 
                       class="btn btn-sm" style="background: linear-gradient(135deg, #009B77, #7ED957); color: white; flex: 1; box-shadow: 0 4px 12px rgba(0, 155, 119, 0.3);">
                        <i class="fas fa-calendar"></i> Reservas
                    </a>
                    <?php if ($area['estado'] == 'disponible'): ?>
                        <a href="desactivar_area.php?id=<?php echo $area['id']; ?>" 
                           class="btn btn-sm" style="background: linear-gradient(135deg, #f44336, #ff5252); color: white; box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);"
                           onclick="return confirm('¿Desactivar esta área?')">
                            <i class="fas fa-ban"></i>
                        </a>
                    <?php else: ?>
                        <a href="activar_area.php?id=<?php echo $area['id']; ?>" 
                           class="btn btn-sm" style="background: linear-gradient(135deg, #7ED957, #A8E063); color: white; box-shadow: 0 4px 12px rgba(126, 217, 87, 0.3);"
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
                <i class="fas fa-building" style="font-size: 3rem; color: #009B77; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3 style="color: #2F2F2F;">No hay áreas comunes registradas</h3>
                <p style="color: #2F2F2F; margin-bottom: 20px;">Comience agregando las áreas comunes del edificio.</p>
                <a href="nueva_area.php" class="btn" style="background: linear-gradient(135deg, #009B77, #7ED957); color: white; box-shadow: 0 4px 15px rgba(0, 155, 119, 0.3);">
                    <i class="fas fa-plus"></i> Agregar Primera Área
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>