<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Dashboard Administrador - Sistema de Edificios';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas
try {
    // Total de inquilinos activos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inquilinos WHERE estado = 'activo'");
    $stmt->execute();
    $total_inquilinos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de empleados activos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM empleados WHERE estado = 'activo'");
    $stmt->execute();
    $total_empleados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pagos pendientes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM pagos WHERE estado = 'pendiente'");
    $stmt->execute();
    $pagos_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Reservas pendientes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'");
    $stmt->execute();
    $reservas_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (PDOException $e) {
    $error = "Error al obtener estadísticas: " . $e->getMessage();
}

// Contar facturas vencidas (morosidad)
try {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM invoices WHERE status <> 'paid' AND due_date IS NOT NULL AND due_date < CURDATE()");
    $stmt->execute();
    $overdue_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $overdue_count = 0;
}
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
    <p class="bento-page-subtitle">Bienvenido al sistema de gestión de edificios</p>
</div>

<?php // Banner de depuración visible sólo en localhost para confirmar que este archivo es el servido
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false): ?>
    <div class="bento-alert bento-alert-warning">
        <i class="fas fa-info-circle"></i> <strong>DEBUG:</strong> Módulo Finanzas integrado en este dashboard (archivo actualizado en servidor).
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas principales -->
<div class="bento-stats-grid">
    <div class="bento-stat-card">
        <div class="bento-stat-number"><i class="fas fa-users"></i> <?php echo $total_inquilinos; ?></div>
        <div class="bento-stat-label">Inquilinos Activos</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number"><i class="fas fa-user-tie"></i> <?php echo $total_empleados; ?></div>
        <div class="bento-stat-label">Empleados Activos</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number"><i class="fas fa-money-bill-wave"></i> <?php echo $pagos_pendientes; ?></div>
        <div class="bento-stat-label">Pagos Pendientes</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number"><i class="fas fa-calendar-check"></i> <?php echo $reservas_pendientes; ?></div>
        <div class="bento-stat-label">Reservas Pendientes</div>
    </div>
</div>

<!-- Módulos principales en grid bento (Finanzas movida al inicio para visibilidad) -->
<div class="bento-grid">
    <div id="finanzas-card" class="bento-card bento-card-finanzas">
        <h3 class="bento-card-title"><i class="fas fa-wallet"></i> Gestión Financiera</h3>
        <p class="bento-card-description">Facturación, control de pagos, morosidad, nómina e integraciones de pasarelas.</p>
        <?php if (!empty($overdue_count) && $overdue_count > 0): ?>
            <div class="bento-card-badge"><span class="status-badge status-expired"><?=htmlspecialchars($overdue_count)?> Vencida(s)</span></div>
        <?php endif; ?>
        <a href="../../finanzas.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-chart-pie"></i> Abrir Finanzas
        </a>
    </div>

    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-users"></i> Gestión de Inquilinos</h3>
        <p class="bento-card-description">Administra inquilinos, alquileres y información de residentes del edificio.</p>
        <a href="inquilinos.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-eye"></i> Ver Inquilinos
        </a>
    </div>
    
    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-user-tie"></i> Gestión de Empleados</h3>
        <p class="bento-card-description">Controla empleados, asigna tareas y supervisa el personal del edificio.</p>
        <a href="empleados.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-eye"></i> Ver Empleados
        </a>
    </div>
    
    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-money-bill-wave"></i> Gestión de Pagos</h3>
        <p class="bento-card-description">Control completo de pagos de alquiler, vencimientos y reportes financieros.</p>
        <a href="pagos.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-eye"></i> Ver Pagos
        </a>
    </div>
    
    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-calendar-check"></i> Reservas de Áreas</h3>
        <p class="bento-card-description">Gestiona reservas de áreas comunes y supervisa la disponibilidad.</p>
        <a href="reservas.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-eye"></i> Ver Reservas
        </a>
    </div>
    
    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-comments"></i> Centro de Comunicación</h3>
        <p class="bento-card-description">Envía avisos generales, mensajes personales y notificaciones importantes.</p>
        <a href="comunicacion.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-paper-plane"></i> Enviar Comunicación
        </a>
    </div>
    
    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-swimming-pool"></i> Áreas Comunes</h3>
        <p class="bento-card-description">Configura y administra las áreas comunes disponibles para reserva.</p>
        <a href="areas_comunes.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-cog"></i> Configurar Áreas
        </a>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="bento-grid bento-actions-grid">
    <div class="bento-card bento-card-actions">
        <h3 class="bento-card-title"><i class="fas fa-plus-circle"></i> Acciones Rápidas</h3>
        <p class="bento-card-description">Herramientas de acceso rápido para tareas comunes.</p>
        <div class="bento-actions-buttons">
            <a href="comunicacion.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-bullhorn"></i> Enviar Aviso
            </a>
            <a href="pagos.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-search"></i> Buscar Pago
            </a>
            <a href="../../finanzas.php" class="bento-btn bento-btn-finanzas">
                <i class="fas fa-wallet"></i> Abrir Finanzas (Panel)
            </a>
        </div>
    </div>
    

    <div class="bento-card bento-card-reports">
        <h3 class="bento-card-title"><i class="fas fa-chart-line"></i> Reportes y Estadísticas</h3>
        <p class="bento-card-description">Visualiza información importante del edificio en tiempo real.</p>
        <div class="bento-actions-buttons">
            <a href="../../estado_sesion.php" class="bento-btn bento-btn-dark">
                <i class="fas fa-info-circle"></i> Estado Sistema
            </a>
            <a href="../../test_sistema.php" class="bento-btn bento-btn-dark">
                <i class="fas fa-tools"></i> Test Conexión
            </a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>