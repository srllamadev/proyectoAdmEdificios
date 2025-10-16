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
    <div class="bento-card" style="background: linear-gradient(135deg, var(--accent-mint), var(--secondary-green));">
        <h3><i class="fas fa-plus-circle"></i> Gestión de Personal</h3>
        <p style="color: var(--dark-blue);">Herramientas para administrar el personal del edificio.</p>
        <div class="d-flex gap-10" style="flex-wrap: wrap;">
            <a href="comunicacion.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-envelope"></i> Comunicaciones
            </a>
            <a href="dashboard.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
    </div>
    
    <div class="bento-card" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); color: white;">
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