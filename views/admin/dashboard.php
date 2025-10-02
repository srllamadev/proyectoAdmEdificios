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
?>

<div class="page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Panel de Administración</h1>
    <p>Bienvenido al sistema de gestión de edificios</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><i class="fas fa-users"></i> <?php echo $total_inquilinos; ?></div>
        <div class="stat-label">Inquilinos Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><i class="fas fa-user-tie"></i> <?php echo $total_empleados; ?></div>
        <div class="stat-label">Empleados Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><i class="fas fa-money-bill-wave"></i> <?php echo $pagos_pendientes; ?></div>
        <div class="stat-label">Pagos Pendientes</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><i class="fas fa-calendar-check"></i> <?php echo $reservas_pendientes; ?></div>
        <div class="stat-label">Reservas Pendientes</div>
    </div>
</div>

<!-- Módulos principales en grid bento -->
<div class="bento-grid">
    <div class="bento-card">
        <h3><i class="fas fa-users" style="color: var(--primary-blue);"></i> Gestión de Inquilinos</h3>
        <p>Administra inquilinos, alquileres y información de residentes del edificio.</p>
        <a href="inquilinos.php" class="btn btn-primary">
            <i class="fas fa-eye"></i> Ver Inquilinos
        </a>
    </div>
    
    <div class="bento-card">
        <h3><i class="fas fa-user-tie" style="color: var(--secondary-green);"></i> Gestión de Empleados</h3>
        <p>Controla empleados, asigna tareas y supervisa el personal del edificio.</p>
        <a href="empleados.php" class="btn btn-primary">
            <i class="fas fa-eye"></i> Ver Empleados
        </a>
    </div>
    
    <div class="bento-card">
        <h3><i class="fas fa-money-bill-wave" style="color: var(--accent-mint);"></i> Gestión de Pagos</h3>
        <p>Control completo de pagos de alquiler, vencimientos y reportes financieros.</p>
        <a href="pagos.php" class="btn btn-primary">
            <i class="fas fa-eye"></i> Ver Pagos
        </a>
    </div>
    
    <div class="bento-card">
        <h3><i class="fas fa-calendar-check" style="color: var(--dark-blue);"></i> Reservas de Áreas</h3>
        <p>Gestiona reservas de áreas comunes y supervisa la disponibilidad.</p>
        <a href="reservas.php" class="btn btn-primary">
            <i class="fas fa-eye"></i> Ver Reservas
        </a>
    </div>
    
    <div class="bento-card">
        <h3><i class="fas fa-comments" style="color: var(--primary-blue);"></i> Centro de Comunicación</h3>
        <p>Envía avisos generales, mensajes personales y notificaciones importantes.</p>
        <a href="comunicacion.php" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Enviar Comunicación
        </a>
    </div>
    
    <div class="bento-card">
        <h3><i class="fas fa-swimming-pool" style="color: var(--secondary-green);"></i> Áreas Comunes</h3>
        <p>Configura y administra las áreas comunes disponibles para reserva.</p>
        <a href="areas_comunes.php" class="btn btn-primary">
            <i class="fas fa-cog"></i> Configurar Áreas
        </a>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="bento-grid" style="margin-top: 30px;">
    <div class="bento-card" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); color: white;">
        <h3><i class="fas fa-plus-circle"></i> Acciones Rápidas</h3>
        <p style="color: rgba(255,255,255,0.9);">Herramientas de acceso rápido para tareas comunes.</p>
        <div class="d-flex gap-10" style="flex-wrap: wrap;">
            <a href="comunicacion.php" class="btn btn-secondary">
                <i class="fas fa-bullhorn"></i> Enviar Aviso
            </a>
            <a href="pagos.php" class="btn btn-secondary">
                <i class="fas fa-search"></i> Buscar Pago
            </a>
        </div>
    </div>
    
    <div class="bento-card" style="background: linear-gradient(135deg, var(--accent-mint), var(--secondary-green)); color: var(--dark-blue);">
        <h3><i class="fas fa-chart-line"></i> Reportes y Estadísticas</h3>
        <p>Visualiza información importante del edificio en tiempo real.</p>
        <div class="d-flex gap-10" style="flex-wrap: wrap;">
            <a href="../../estado_sesion.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-info-circle"></i> Estado Sistema
            </a>
            <a href="../../test_sistema.php" class="btn" style="background: var(--dark-blue); color: white;">
                <i class="fas fa-tools"></i> Test Conexión
            </a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>