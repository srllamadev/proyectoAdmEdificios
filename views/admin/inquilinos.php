<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Gestión de Inquilinos - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener lista de inquilinos con información de alquiler
try {
    $query = "SELECT i.*, u.name, u.email, a.numero_departamento, a.precio_mensual, a.estado as estado_alquiler
              FROM inquilinos i 
              JOIN users u ON i.user_id = u.id 
              LEFT JOIN alquileres a ON i.id = a.inquilino_id AND a.estado = 'activo'
              ORDER BY i.id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $inquilinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error al obtener inquilinos: " . $e->getMessage();
}
?>

<div class="page-header">
    <h1><i class="fas fa-users"></i> Gestión de Inquilinos</h1>
    <p>Administración completa de inquilinos y alquileres</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas rápidas -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-number"><?php echo count($inquilinos); ?></div>
        <div class="stat-label">Total Inquilinos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo count(array_filter($inquilinos, function($i) { return $i['estado'] == 'activo'; })); ?></div>
        <div class="stat-label">Inquilinos Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo count(array_filter($inquilinos, function($i) { return !empty($i['numero_departamento']); })); ?></div>
        <div class="stat-label">Con Alquiler</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">$<?php echo number_format(array_sum(array_map(function($i) { return $i['precio_mensual'] ?? 0; }, $inquilinos)), 0); ?></div>
        <div class="stat-label">Ingresos Mensuales</div>
    </div>
</div>

<!-- Lista de inquilinos en tarjetas bento -->
<div class="bento-card">
    <h3><i class="fas fa-list"></i> Lista de Inquilinos</h3>
    
    <?php if (!empty($inquilinos)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Nombre</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-id-card"></i> DNI</th>
                        <th><i class="fas fa-phone"></i> Teléfono</th>
                        <th><i class="fas fa-home"></i> Departamento</th>
                        <th><i class="fas fa-money-bill"></i> Precio Mensual</th>
                        <th><i class="fas fa-traffic-light"></i> Estado</th>
                        <th><i class="fas fa-calendar"></i> Fecha Ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquilinos as $inquilino): ?>
                        <tr>
                            <td><strong><?php echo $inquilino['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($inquilino['name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($inquilino['email']); ?>" 
                                   style="color: var(--primary-blue); text-decoration: none;">
                                    <?php echo htmlspecialchars($inquilino['email']); ?>
                                </a>
                            </td>
                            <td><code><?php echo htmlspecialchars($inquilino['dni']); ?></code></td>
                            <td><?php echo htmlspecialchars($inquilino['telefono'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($inquilino['numero_departamento']): ?>
                                    <span class="status-badge status-active">
                                        <i class="fas fa-home"></i> <?php echo htmlspecialchars($inquilino['numero_departamento']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($inquilino['precio_mensual']): ?>
                                    <strong style="color: var(--secondary-green);">$<?php echo number_format($inquilino['precio_mensual'], 2); ?></strong>
                                <?php else: ?>
                                    <span style="color: var(--dark-gray);">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $inquilino['estado'] == 'activo' ? 'status-active' : 'status-expired'; ?>">
                                    <i class="fas fa-<?php echo $inquilino['estado'] == 'activo' ? 'check-circle' : 'times-circle'; ?>"></i>
                                    <?php echo ucfirst($inquilino['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($inquilino['fecha_ingreso']): ?>
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($inquilino['fecha_ingreso'])); ?>
                                <?php else: ?>
                                    <span style="color: var(--dark-gray);">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: var(--light-gray); border-radius: var(--border-radius);">
            <i class="fas fa-users" style="font-size: 3rem; color: var(--dark-gray); margin-bottom: 15px;"></i>
            <h3 style="color: var(--dark-gray);">No hay inquilinos registrados</h3>
            <p style="color: var(--dark-gray);">Los inquilinos aparecerán aquí cuando se registren en el sistema.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Acciones adicionales -->
<div class="bento-grid" style="margin-top: 30px;">
    <div class="bento-card" style="background: linear-gradient(135deg, var(--accent-mint), var(--secondary-green));">
        <h3><i class="fas fa-plus-circle"></i> Acciones Rápidas</h3>
        <p style="color: var(--dark-blue);">Herramientas útiles para la gestión de inquilinos.</p>
        <div class="d-flex gap-10" style="flex-wrap: wrap;">
            <a href="../comunicaciones.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-envelope"></i> Ver Comunicaciones
            </a>
            <a href="pagos.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-money-bill-wave"></i> Gestionar Pagos
            </a>
        </div>
    </div>
    
    <div class="bento-card" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); color: white;">
        <h3><i class="fas fa-chart-bar"></i> Resumen Financiero</h3>
        <p style="color: rgba(255,255,255,0.9);">Estado financiero de los alquileres activos.</p>
        <div class="d-flex justify-between align-center">
            <div>
                <strong style="font-size: 1.5rem;">$<?php echo number_format(array_sum(array_map(function($i) { return $i['precio_mensual'] ?? 0; }, $inquilinos)), 2); ?></strong>
                <br><small>Ingresos Mensuales Totales</small>
            </div>
            <i class="fas fa-dollar-sign" style="font-size: 2rem; opacity: 0.7;"></i>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>