<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Gestión de Empleados - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Estilos personalizados
echo '<style>
.bento-employee-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.bento-employee-stat-card {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0, 155, 119, 0.15);
}

.bento-employee-stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0, 155, 119, 0.25);
    border: 1px solid rgba(0, 155, 119, 0.4);
}

.bento-employee-stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
}

.bento-stat-total::before {
    background: linear-gradient(135deg, #001F54 0%, #009B77 100%);
}

.bento-stat-active::before {
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
}

.bento-stat-maintenance::before {
    background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 100%);
}

.bento-stat-security::before {
    background: linear-gradient(135deg, #001F54 0%, #009B77 100%);
}

.bento-employee-stat-number {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, #009B77, #7ED957);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.bento-employee-stat-label {
    color: #2F2F2F;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.bento-employee-stat-label i {
    display: block;
    font-size: 2rem;
    margin-bottom: 10px;
    color: #009B77;
}

.cargo-badge {
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

.cargo-mantenimiento {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.25), rgba(244, 208, 63, 0.25));
    color: #D4AF37;
    border-color: rgba(212, 175, 55, 0.4);
}

.cargo-seguridad {
    background: linear-gradient(135deg, rgba(0, 31, 84, 0.25), rgba(0, 155, 119, 0.25));
    color: #001F54;
    border-color: rgba(0, 31, 84, 0.4);
}

.cargo-administracion {
    background: linear-gradient(135deg, rgba(0, 155, 119, 0.25), rgba(126, 217, 87, 0.25));
    color: #009B77;
    border-color: rgba(0, 155, 119, 0.4);
}

.cargo-limpieza {
    background: linear-gradient(135deg, rgba(126, 217, 87, 0.25), rgba(168, 224, 99, 0.25));
    color: #7ED957;
    border-color: rgba(126, 217, 87, 0.4);
}

.cargo-conserje {
    background: linear-gradient(135deg, rgba(0, 155, 119, 0.25), rgba(126, 217, 87, 0.25));
    color: #009B77;
    border-color: rgba(0, 155, 119, 0.4);
}

.cargo-general {
    background: linear-gradient(135deg, rgba(47, 47, 47, 0.15), rgba(100, 100, 100, 0.15));
    color: #2F2F2F;
    border-color: rgba(47, 47, 47, 0.3);
}

.employee-email {
    color: #009B77;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.employee-email:hover {
    color: #7ED957;
    text-decoration: underline;
}

.employee-dni {
    font-family: "Courier New", monospace;
    font-weight: 600;
    background: linear-gradient(135deg, rgba(0, 155, 119, 0.1), rgba(126, 217, 87, 0.1));
    padding: 6px 12px;
    border-radius: 8px;
    color: #2F2F2F;
    border: 1px solid rgba(0, 155, 119, 0.2);
}

.bento-employee-date {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.bento-employee-date-main {
    color: #2F2F2F;
    font-weight: 600;
}

.bento-employee-date-main i {
    color: #009B77;
}

.bento-employee-date-type {
    color: #7ED957;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.bento-employee-no-date {
    color: #999;
    text-align: center;
}

.bento-employee-no-date i {
    font-size: 1.2rem;
    display: block;
    margin-bottom: 4px;
}

.employee-distribution {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.distribution-item {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.distribution-item:hover {
    transform: scale(1.05);
    background: rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.distribution-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    margin-bottom: 8px;
}

.distribution-label {
    color: rgba(255, 255, 255, 0.95);
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.distribution-label i {
    display: block;
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.table-container {
    overflow-x: auto;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 20px;
}

.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

.table thead th {
    background: linear-gradient(135deg, #001F54, #009B77);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
    border: none;
}

.table thead th:first-child {
    border-radius: 10px 0 0 10px;
}

.table thead th:last-child {
    border-radius: 0 10px 10px 0;
}

.table tbody tr {
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    transform: scale(1.01);
    box-shadow: 0 8px 25px rgba(0, 155, 119, 0.2);
    border: 1px solid rgba(0, 155, 119, 0.4);
}

.table tbody td {
    padding: 18px 15px;
    color: #2F2F2F;
    border: none;
}

.table tbody td:first-child {
    border-radius: 10px 0 0 10px;
    font-weight: 700;
    color: #009B77;
}

.table tbody td:last-child {
    border-radius: 0 10px 10px 0;
}

@media (max-width: 768px) {
    .bento-employee-stats {
        grid-template-columns: 1fr;
    }
    
    .table-container {
        font-size: 0.9rem;
    }
    
    .employee-distribution {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>';


// Obtener lista de empleados
try {
    $query = "SELECT e.*, u.name, u.email, u.created_at as fecha_registro
              FROM empleados e 
              JOIN users u ON e.user_id = u.id 
              ORDER BY e.id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: mostrar la estructura de datos para entender qué campos están disponibles
    if (!empty($empleados)) {
        error_log("Campos disponibles en empleados: " . implode(', ', array_keys($empleados[0])));
    }
    
} catch (PDOException $e) {
    $error = "Error al obtener empleados: " . $e->getMessage();
}
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-user-tie"></i> Gestión de Empleados</h1>
    <p class="bento-page-subtitle">Administración del personal y empleados del edificio</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas rápidas -->
<div class="bento-employee-stats">
    <div class="bento-employee-stat-card bento-stat-total">
        <div class="bento-employee-stat-number">
            <?php echo count($empleados); ?>
        </div>
        <div class="bento-employee-stat-label">
            <i class="fas fa-users"></i> Total Empleados
        </div>
    </div>
    <div class="bento-employee-stat-card bento-stat-active">
        <div class="bento-employee-stat-number">
            <?php echo count(array_filter($empleados, function($e) { return $e['estado'] == 'activo'; })); ?>
        </div>
        <div class="bento-employee-stat-label">
            <i class="fas fa-check-circle"></i> Activos
        </div>
    </div>
    <div class="bento-employee-stat-card bento-stat-maintenance">
        <div class="bento-employee-stat-number">
            <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'mantenimiento'; })); ?>
        </div>
        <div class="bento-employee-stat-label">
            <i class="fas fa-tools"></i> Mantenimiento
        </div>
    </div>
    <div class="bento-employee-stat-card bento-stat-security">
        <div class="bento-employee-stat-number">
            <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'seguridad'; })); ?>
        </div>
        <div class="bento-employee-stat-label">
            <i class="fas fa-shield-alt"></i> Seguridad
        </div>
    </div>
</div>

<!-- Lista de empleados -->
<div class="bento-card">
    <h3 class="bento-card-title"><i class="fas fa-users"></i> Lista de Empleados</h3>
    
    <?php if (!empty($empleados)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Nombre</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-id-card"></i> DNI</th>
                        <th><i class="fas fa-briefcase"></i> Cargo</th>
                        <th><i class="fas fa-phone"></i> Teléfono</th>
                        <th><i class="fas fa-calendar"></i> Fecha Contratación</th>
                        <th><i class="fas fa-traffic-light"></i> Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $empleado): ?>
                        <tr>
                            <td><strong><?php echo $empleado['id']; ?></strong></td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <strong><?php echo htmlspecialchars($empleado['name']); ?></strong>
                                    <small style="color: var(--dark-gray);">ID: <?php echo $empleado['user_id']; ?></small>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($empleado['email']); ?>" 
                                   class="employee-email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($empleado['email']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="employee-dni"><?php echo htmlspecialchars($empleado['dni']); ?></span>
                            </td>
                            <td>
                                <?php 
                                $cargo = strtolower($empleado['cargo']);
                                $cargo_class = '';
                                $cargo_icon = '';
                                
                                // Mapear diferentes tipos de cargo a las clases CSS
                                if (strpos($cargo, 'mantenimiento') !== false || strpos($cargo, 'técnico') !== false) {
                                    $cargo_class = 'cargo-mantenimiento';
                                    $cargo_icon = 'tools';
                                } elseif (strpos($cargo, 'seguridad') !== false || strpos($cargo, 'vigilancia') !== false || strpos($cargo, 'guardia') !== false) {
                                    $cargo_class = 'cargo-seguridad';
                                    $cargo_icon = 'shield-alt';
                                } elseif (strpos($cargo, 'administr') !== false || strpos($cargo, 'gerente') !== false || strpos($cargo, 'supervisor') !== false) {
                                    $cargo_class = 'cargo-administracion';
                                    $cargo_icon = 'user-tie';
                                } elseif (strpos($cargo, 'limpieza') !== false || strpos($cargo, 'aseo') !== false) {
                                    $cargo_class = 'cargo-limpieza';
                                    $cargo_icon = 'broom';
                                } elseif (strpos($cargo, 'conserje') !== false || strpos($cargo, 'portero') !== false) {
                                    $cargo_class = 'cargo-conserje';
                                    $cargo_icon = 'user-shield';
                                } else {
                                    $cargo_class = 'cargo-general';
                                    $cargo_icon = 'user-cog';
                                }
                                ?>
                                <span class="cargo-badge <?php echo $cargo_class; ?>">
                                    <i class="fas fa-<?php echo $cargo_icon; ?>"></i>
                                    <?php echo htmlspecialchars($empleado['cargo']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($empleado['telefono'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                // Buscar diferentes campos de fecha disponibles
                                $fecha_mostrar = null;
                                $tipo_fecha = '';
                                
                                if (isset($empleado['fecha_contratacion']) && !empty($empleado['fecha_contratacion']) && $empleado['fecha_contratacion'] != '0000-00-00') {
                                    $fecha_mostrar = $empleado['fecha_contratacion'];
                                    $tipo_fecha = 'Contratación';
                                } elseif (isset($empleado['fecha_ingreso']) && !empty($empleado['fecha_ingreso']) && $empleado['fecha_ingreso'] != '0000-00-00') {
                                    $fecha_mostrar = $empleado['fecha_ingreso'];
                                    $tipo_fecha = 'Ingreso';
                                } elseif (isset($empleado['fecha_registro']) && !empty($empleado['fecha_registro'])) {
                                    $fecha_mostrar = $empleado['fecha_registro'];
                                    $tipo_fecha = 'Registro';
                                } elseif (isset($empleado['created_at']) && !empty($empleado['created_at'])) {
                                    $fecha_mostrar = $empleado['created_at'];
                                    $tipo_fecha = 'Registro';
                                }
                                
                                if ($fecha_mostrar): ?>
                                    <div class="bento-employee-date">
                                        <span class="bento-employee-date-main">
                                            <i class="fas fa-calendar-alt"></i> <?php echo formatDate($fecha_mostrar); ?>
                                        </span>
                                        <small class="bento-employee-date-type">
                                            <?php echo $tipo_fecha; ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <span class="bento-employee-no-date">
                                        <i class="fas fa-calendar-times"></i><br>
                                        <small>Sin fecha</small>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo getStatusBadge($empleado['estado']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bento-empty-state">
            <i class="fas fa-user-tie"></i>
            <h3>No hay empleados registrados</h3>
            <p>Los empleados aparecerán aquí cuando se registren en el sistema.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Acciones adicionales -->
<div class="bento-grid" style="margin-top: 30px;">
    <div class="bento-card" style="background: linear-gradient(135deg, rgba(0, 155, 119, 0.25), rgba(126, 217, 87, 0.25)); backdrop-filter: blur(16px); border: 1px solid rgba(0, 155, 119, 0.3);">
        <h3><i class="fas fa-plus-circle"></i> Gestión de Personal</h3>
        <p style="color: #2F2F2F; font-weight: 500;">Herramientas para administrar el personal del edificio.</p>
        <div class="d-flex gap-10" style="flex-wrap: wrap;">
            <a href="comunicacion.php" class="btn" style="background: linear-gradient(135deg, #001F54, #009B77); color: white; border: none; box-shadow: 0 4px 15px rgba(0, 155, 119, 0.3);">
                <i class="fas fa-envelope"></i> Comunicaciones
            </a>
            <a href="dashboard.php" class="btn" style="background: linear-gradient(135deg, #009B77, #7ED957); color: white; border: none; box-shadow: 0 4px 15px rgba(0, 155, 119, 0.3);">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
    </div>
    
    <div class="bento-card" style="background: linear-gradient(135deg, #001F54, #009B77); color: white; backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 8px 30px rgba(0, 155, 119, 0.3);">
        <h3><i class="fas fa-chart-pie"></i> Distribución por Cargo</h3>
        <p style="color: rgba(255,255,255,0.9);">Resumen del personal por departamento.</p>
        <div class="employee-distribution">
            <div class="distribution-item">
                <div class="distribution-number">
                    <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'mantenimiento'; })); ?>
                </div>
                <div class="distribution-label">
                    <i class="fas fa-tools"></i> Mantenimiento
                </div>
            </div>
            <div class="distribution-item">
                <div class="distribution-number">
                    <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'seguridad'; })); ?>
                </div>
                <div class="distribution-label">
                    <i class="fas fa-shield-alt"></i> Seguridad
                </div>
            </div>
            <div class="distribution-item">
                <div class="distribution-number">
                    <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'administracion'; })); ?>
                </div>
                <div class="distribution-label">
                    <i class="fas fa-user-tie"></i> Administración
                </div>
            </div>
            <div class="distribution-item">
                <div class="distribution-number">
                    <?php echo count(array_filter($empleados, function($e) { return !in_array($e['cargo'], ['mantenimiento', 'seguridad', 'administracion']); })); ?>
                </div>
                <div class="distribution-label">
                    <i class="fas fa-ellipsis-h"></i> Otros
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug temporal: mostrar estructura de datos -->
<?php if (!empty($empleados) && isset($_GET['debug'])): ?>
<div class="bento-card" style="margin-top: 20px; background: #f8f9fa;">
    <h3>🐛 Debug - Estructura de Datos de Empleados</h3>
    <pre style="background: white; padding: 15px; border-radius: 5px; font-size: 12px; overflow-x: auto;">
        <?php 
        echo "Primer empleado:\n";
        print_r($empleados[0]); 
        ?>
    </pre>
    <p><small>Para ocultar este debug, elimina <code>?debug</code> de la URL</small></p>
</div>
<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>