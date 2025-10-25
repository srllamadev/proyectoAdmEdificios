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
    $inquilinos = [];
}
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-users"></i> Gestión de Inquilinos</h1>
    <p class="bento-page-subtitle">Administración completa de inquilinos y alquileres</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas rápidas -->
<div class="bento-stats-grid">
    <div class="bento-stat-card">
        <div class="bento-stat-number"><?php echo count($inquilinos); ?></div>
        <div class="bento-stat-label">Total Inquilinos</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number"><?php echo count(array_filter($inquilinos, function($i) { return $i['estado'] == 'activo'; })); ?></div>
        <div class="bento-stat-label">Inquilinos Activos</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number"><?php echo count(array_filter($inquilinos, function($i) { return !empty($i['numero_departamento']); })); ?></div>
        <div class="bento-stat-label">Con Alquiler</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number">$<?php echo number_format(array_sum(array_map(function($i) { return $i['precio_mensual'] ?? 0; }, $inquilinos)), 0); ?></div>
        <div class="bento-stat-label">Ingresos Mensuales</div>
    </div>
</div>

<!-- Lista de inquilinos en tarjetas bento -->
<div class="bento-card">
    <h3 class="bento-card-title"><i class="fas fa-list"></i> Lista de Inquilinos</h3>
    
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
                                   class="bento-link-email">
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
                                    <strong class="bento-price-green">$<?php echo number_format($inquilino['precio_mensual'], 2); ?></strong>
                                <?php else: ?>
                                    <span class="bento-text-muted">N/A</span>
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
                                    <span class="bento-text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bento-empty-state">
            <i class="fas fa-users"></i>
            <h3>No hay inquilinos registrados</h3>
            <p>Los inquilinos aparecerán aquí cuando se registren en el sistema.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Acciones adicionales -->
<div class="bento-grid bento-actions-grid">
    <div class="bento-card bento-card-actions">
        <h3 class="bento-card-title"><i class="fas fa-plus-circle"></i> Acciones Rápidas</h3>
        <p class="bento-card-description">Herramientas útiles para la gestión de inquilinos.</p>
        <div class="bento-actions-buttons">
            <a href="../shared/comunicaciones.php" class="bento-btn bento-btn-dark">
                <i class="fas fa-envelope"></i> Ver Comunicaciones
            </a>
            <a href="pagos.php" class="bento-btn bento-btn-dark">
                <i class="fas fa-money-bill-wave"></i> Gestionar Pagos
            </a>
        </div>
    </div>
    
    <div class="bento-card bento-card-finance">
        <h3 class="bento-card-title"><i class="fas fa-chart-bar"></i> Resumen Financiero</h3>
        <p class="bento-card-description">Estado financiero de los alquileres activos.</p>
        <div class="bento-finance-summary">
            <div class="bento-finance-amount">
                <strong>$<?php echo number_format(array_sum(array_map(function($i) { return $i['precio_mensual'] ?? 0; }, $inquilinos)), 2); ?></strong>
                <br><small>Ingresos Mensuales Totales</small>
            </div>
            <i class="fas fa-dollar-sign"></i>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>