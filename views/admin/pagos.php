<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Gestión de Pagos - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los pagos con información de inquilinos
try {
    $query = "SELECT p.*, a.numero_departamento, i.dni, u.name as inquilino_nombre, u.email
              FROM pagos p 
              JOIN alquileres a ON p.alquiler_id = a.id
              JOIN inquilinos i ON a.inquilino_id = i.id
              JOIN users u ON i.user_id = u.id
              ORDER BY p.fecha_vencimiento DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estadísticas
    $total_pagos = count($pagos);
    $pagos_pendientes = count(array_filter($pagos, function($p) { return $p['estado'] == 'pendiente'; }));
    $pagos_vencidos = count(array_filter($pagos, function($p) { return $p['estado'] == 'vencido'; }));
    $pagos_pagados = count(array_filter($pagos, function($p) { return $p['estado'] == 'pagado'; }));
    $monto_pendiente = array_sum(array_map(function($p) { 
        return $p['estado'] == 'pendiente' ? $p['monto'] + $p['recargo'] : 0; 
    }, $pagos));
    $monto_vencido = array_sum(array_map(function($p) { 
        return $p['estado'] == 'vencido' ? $p['monto'] + $p['recargo'] : 0; 
    }, $pagos));
    
} catch (PDOException $e) {
    $error = "Error al obtener información de pagos: " . $e->getMessage();
}
?>

<div class="page-header">
    <h1><i class="fas fa-money-bill-wave"></i> Gestión de Pagos</h1>
    <p>Control y seguimiento de pagos de alquiler</p>
</div>

<?php if (isset($error)): ?>
    <?php showAlert($error, 'error'); ?>
<?php endif; ?>

<!-- Estadísticas de pagos -->
<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-number"><?php echo $total_pagos; ?></div>
        <div class="stat-label">Total Pagos</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--secondary-green), var(--accent-mint));">
        <div class="stat-number"><?php echo $pagos_pagados; ?></div>
        <div class="stat-label">Pagados</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ffeb3b);">
        <div class="stat-number"><?php echo $pagos_pendientes; ?></div>
        <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #ff6b6b);">
        <div class="stat-number"><?php echo $pagos_vencidos; ?></div>
        <div class="stat-label">Vencidos</div>
    </div>
</div>

<!-- Filtros -->
<div class="bento-card" style="margin-bottom: 30px;">
    <h3><i class="fas fa-filter"></i> Filtros</h3>
    <div class="d-flex gap-10" style="flex-wrap: wrap;">
        <button onclick="filtrarPagos('todos')" class="btn" style="background: var(--primary-blue);">
            <i class="fas fa-list"></i> Todos los Pagos
        </button>
        <button onclick="filtrarPagos('pendiente')" class="btn" style="background: #ffc107; color: black;">
            <i class="fas fa-clock"></i> Solo Pendientes
        </button>
        <button onclick="filtrarPagos('vencido')" class="btn" style="background: #dc3545;">
            <i class="fas fa-exclamation-triangle"></i> Solo Vencidos
        </button>
        <button onclick="filtrarPagos('pagado')" class="btn" style="background: var(--secondary-green);">
            <i class="fas fa-check-circle"></i> Solo Pagados
        </button>
    </div>
</div>

<!-- Lista de pagos -->
<div class="bento-card">
    <h3 id="titulo-pagos"><i class="fas fa-list-alt"></i> Detalle de Todos los Pagos</h3>
    
    <?php if (!empty($pagos)): ?>
        <div id="lista-pagos">
            <?php foreach ($pagos as $pago): ?>
                <div class="pago-item payment-card" data-estado="<?php echo $pago['estado']; ?>">
                    <div class="payment-header">
                        <h4><?php echo htmlspecialchars($pago['descripcion']); ?></h4>
                        <?php echo getStatusBadge($pago['estado']); ?>
                    </div>
                    
                    <div class="payment-details">
                        <div class="detail-group">
                            <h5><i class="fas fa-user"></i> Información del Inquilino</h5>
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pago['inquilino_nombre']); ?></p>
                            <p><strong>Departamento:</strong> <?php echo htmlspecialchars($pago['numero_departamento']); ?></p>
                            <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($pago['email']); ?>"><?php echo htmlspecialchars($pago['email']); ?></a></p>
                            <p><strong>DNI:</strong> <code><?php echo htmlspecialchars($pago['dni']); ?></code></p>
                        </div>
                        
                        <div class="detail-group">
                            <h5><i class="fas fa-dollar-sign"></i> Información de Pago</h5>
                            <p><strong>Monto:</strong> <?php echo formatCurrency($pago['monto']); ?></p>
                            <?php if ($pago['recargo'] > 0): ?>
                                <p style="color: #dc3545;"><strong>Recargo:</strong> <?php echo formatCurrency($pago['recargo']); ?></p>
                                <p style="font-weight: bold; color: var(--dark-blue);"><strong>Total:</strong> <?php echo formatCurrency($pago['monto'] + $pago['recargo']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="detail-group">
                            <h5><i class="fas fa-calendar"></i> Fechas</h5>
                            <p><strong>Fecha Vencimiento:</strong> <?php echo formatDate($pago['fecha_vencimiento']); ?></p>
                            <?php if ($pago['fecha_pago']): ?>
                                <p style="color: var(--secondary-green);"><strong>Fecha Pago:</strong> <?php echo formatDate($pago['fecha_pago']); ?></p>
                                <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($pago['metodo_pago']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($pago['estado'] != 'pagado'): ?>
                        <div class="payment-actions">
                            <a href="marcar_pagado.php?id=<?php echo $pago['id']; ?>" 
                               onclick="return confirm('¿Confirmar que este pago ha sido recibido?')"
                               class="btn" style="background: var(--secondary-green);">
                               <i class="fas fa-check"></i> Marcar como Pagado
                            </a>
                            <a href="agregar_recargo.php?id=<?php echo $pago['id']; ?>" 
                               class="btn" style="background: #dc3545;">
                               <i class="fas fa-plus"></i> Agregar Recargo
                            </a>
                            <a href="enviar_recordatorio.php?id=<?php echo $pago['id']; ?>" 
                               class="btn" style="background: #ffc107; color: black;">
                               <i class="fas fa-envelope"></i> Enviar Recordatorio
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: var(--light-gray); border-radius: var(--border-radius);">
            <i class="fas fa-money-bill-wave" style="font-size: 3rem; color: var(--dark-gray); margin-bottom: 15px;"></i>
            <h3 style="color: var(--dark-gray);">No hay registros de pagos</h3>
            <p style="color: var(--dark-gray);">No se encontraron pagos en el sistema.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    function filtrarPagos(estado) {
        const items = document.querySelectorAll('.pago-item');
        const titulo = document.getElementById('titulo-pagos');
        
        items.forEach(item => {
            if (estado === 'todos' || item.dataset.estado === estado) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Actualizar título según filtro
        switch(estado) {
            case 'pendiente':
                titulo.innerHTML = '<i class="fas fa-clock"></i> Pagos Pendientes';
                break;
            case 'vencido':
                titulo.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pagos Vencidos';
                break;
            case 'pagado':
                titulo.innerHTML = '<i class="fas fa-check-circle"></i> Pagos Realizados';
                break;
            default:
                titulo.innerHTML = '<i class="fas fa-list-alt"></i> Detalle de Todos los Pagos';
                break;
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>