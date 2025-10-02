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

<div class="page-header">
    <h1><i class="fas fa-user-tie"></i> Gestión de Empleados</h1>
    <p>Administración del personal y empleados del edificio</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas rápidas -->
<div class="employee-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="employee-stat-card" style="background: white; padding: 25px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #1DCDFE; transition: transform 0.3s ease;">
        <div class="employee-stat-number" style="font-size: 2.5rem; font-weight: bold; color: #1DCDFE; margin-bottom: 8px; line-height: 1;">
            <?php echo count($empleados); ?>
        </div>
        <div class="employee-stat-label" style="color: #2F455C; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
            <i class="fas fa-users" style="margin-right: 5px; color: #1DCDFE;"></i> Total Empleados
        </div>
    </div>
    <div class="employee-stat-card" style="background: white; padding: 25px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #21D0B2; transition: transform 0.3s ease;">
        <div class="employee-stat-number" style="font-size: 2.5rem; font-weight: bold; color: #21D0B2; margin-bottom: 8px; line-height: 1;">
            <?php echo count(array_filter($empleados, function($e) { return $e['estado'] == 'activo'; })); ?>
        </div>
        <div class="employee-stat-label" style="color: #2F455C; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
            <i class="fas fa-check-circle" style="margin-right: 5px; color: #21D0B2;"></i> Activos
        </div>
    </div>
    <div class="employee-stat-card" style="background: white; padding: 25px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #ffc107; transition: transform 0.3s ease;">
        <div class="employee-stat-number" style="font-size: 2.5rem; font-weight: bold; color: #ffc107; margin-bottom: 8px; line-height: 1;">
            <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'mantenimiento'; })); ?>
        </div>
        <div class="employee-stat-label" style="color: #2F455C; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
            <i class="fas fa-tools" style="margin-right: 5px; color: #ffc107;"></i> Mantenimiento
        </div>
    </div>
    <div class="employee-stat-card" style="background: white; padding: 25px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #2F455C; transition: transform 0.3s ease;">
        <div class="employee-stat-number" style="font-size: 2.5rem; font-weight: bold; color: #2F455C; margin-bottom: 8px; line-height: 1;">
            <?php echo count(array_filter($empleados, function($e) { return $e['cargo'] == 'seguridad'; })); ?>
        </div>
        <div class="employee-stat-label" style="color: #2F455C; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
            <i class="fas fa-shield-alt" style="margin-right: 5px; color: #2F455C;"></i> Seguridad
        </div>
    </div>
</div>

<style>
.employee-stats .employee-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
}

.employee-stats {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.employee-stat-card {
    cursor: pointer;
}

.cargo-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.cargo-mantenimiento {
    background: linear-gradient(135deg, #ffc107, #ffeb3b);
    color: #333;
}

.cargo-seguridad {
    background: linear-gradient(135deg, #1DCDFE, #21D0B2);
    color: white;
}

.cargo-administracion {
    background: linear-gradient(135deg, #34F5C5, #21D0B2);
    color: #2F455C;
}

.cargo-conserje {
    background: linear-gradient(135deg, #6f42c1, #8b5cf6);
    color: white;
}

.cargo-limpieza {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    color: white;
}

.cargo-general {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
}

.employee-email {
    color: #1DCDFE;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.employee-email:hover {
    text-decoration: underline;
}

.employee-dni {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    color: #2F455C;
}
</style>

<!-- Lista de empleados -->
<div class="bento-card">
    <h3><i class="fas fa-users"></i> Lista de Empleados</h3>
    
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
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <span style="color: #1DCDFE; font-weight: 500;">
                                            <i class="fas fa-calendar-alt"></i> <?php echo formatDate($fecha_mostrar); ?>
                                        </span>
                                        <small style="color: #6c757d; font-size: 0.75rem;">
                                            <?php echo $tipo_fecha; ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #6c757d; text-align: center; display: block;">
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
        <div style="text-align: center; padding: 40px; background: var(--light-gray); border-radius: var(--border-radius);">
            <i class="fas fa-user-tie" style="font-size: 3rem; color: var(--dark-gray); margin-bottom: 15px;"></i>
            <h3 style="color: var(--dark-gray);">No hay empleados registrados</h3>
            <p style="color: var(--dark-gray);">Los empleados aparecerán aquí cuando se registren en el sistema.</p>
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