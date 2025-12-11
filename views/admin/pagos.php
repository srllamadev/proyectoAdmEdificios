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

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-money-bill-wave"></i> Gestión de Pagos</h1>
    <p class="bento-page-subtitle">Control y seguimiento de pagos de alquiler</p>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Estadísticas de pagos -->
<div class="bento-stats-grid">
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #001F54 0%, #009B77 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $total_pagos; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.95);">Total Pagos</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $pagos_pagados; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.95);">Pagados</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $pagos_pendientes; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.95);">Pendientes</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $pagos_vencidos; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.95);">Vencidos</div>
    </div>
</div>

<!-- Filtros -->
<div class="bento-card" style="margin-bottom: 30px;">
    <h3 class="bento-card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">
        <button onclick="filtrarPagos('todos')" class="filter-btn filter-todos">
            <i class="fas fa-list"></i> Todos los Pagos
        </button>
        <button onclick="filtrarPagos('pendiente')" class="filter-btn filter-pendiente">
            <i class="fas fa-clock"></i> Solo Pendientes
        </button>
        <button onclick="filtrarPagos('vencido')" class="filter-btn filter-vencido">
            <i class="fas fa-exclamation-triangle"></i> Solo Vencidos
        </button>
        <button onclick="filtrarPagos('pagado')" class="filter-btn filter-pagado">
            <i class="fas fa-check-circle"></i> Solo Pagados
        </button>
    </div>
</div>

<!-- Lista de pagos -->
<div class="bento-card">
    <h3 class="bento-card-title" id="titulo-pagos"><i class="fas fa-list-alt"></i> Detalle de Todos los Pagos</h3>
    
    <?php if (!empty($pagos)): ?>
        <div id="lista-pagos" style="display: grid; gap: 20px; margin-top: 20px;">
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
                               class="btn" style="background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);">
                               <i class="fas fa-check"></i> Marcar como Pagado
                            </a>
                            <a href="agregar_recargo.php?id=<?php echo $pago['id']; ?>" 
                               class="btn" style="background: #dc3545;">
                               <i class="fas fa-plus"></i> Agregar Recargo
                            </a>
                            <a href="enviar_recordatorio.php?id=<?php echo $pago['id']; ?>" 
                               class="btn" style="background: #D4AF37; color: white;">
                               <i class="fas fa-envelope"></i> Enviar Recordatorio
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bento-empty-state">
            <i class="fas fa-money-bill-wave"></i>
            <h3>No hay registros de pagos</h3>
            <p>No se encontraron pagos en el sistema.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.payment-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-left: 5px solid #009B77;
    transition: all 0.3s ease;
}

.payment-card:hover {
    box-shadow: 0 5px 20px rgba(0, 155, 119, 0.2);
    transform: translateY(-2px);
}

.payment-card[data-estado="pagado"] {
    border-left-color: #7ED957;
}

.payment-card[data-estado="pendiente"] {
    border-left-color: #D4AF37;
}

.payment-card[data-estado="vencido"] {
    border-left-color: #dc3545;
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f3f4f6;
}

.payment-header h4 {
    margin: 0;
    color: #2F2F2F;
    font-size: 1.3em;
}

.payment-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.detail-group {
    background: linear-gradient(135deg, rgba(0, 155, 119, 0.05) 0%, rgba(255, 255, 255, 0.5) 100%);
    padding: 15px;
    border-radius: 10px;
    border: 1px solid rgba(0, 155, 119, 0.1);
}

.detail-group h5 {
    margin: 0 0 12px 0;
    color: #009B77;
    font-size: 1em;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
}

.detail-group p {
    margin: 8px 0;
    color: #2F2F2F;
    font-size: 0.95em;
}

.detail-group code {
    background: white;
    padding: 3px 8px;
    border-radius: 5px;
    color: #009B77;
    font-weight: 600;
    border: 1px solid rgba(0, 155, 119, 0.2);
}

.payment-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 15px;
    border-top: 2px solid #f3f4f6;
}

.payment-actions .btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.payment-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.filter-btn {
    padding: 12px 24px;
    border-radius: 10px;
    border: 2px solid rgba(0, 155, 119, 0.2);
    background: white;
    color: #2F2F2F;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.filter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 155, 119, 0.15);
}

.filter-btn.filter-todos:hover {
    background: linear-gradient(135deg, #001F54 0%, #009B77 100%);
    color: white;
    border-color: #009B77;
}

.filter-btn.filter-pagado:hover {
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
    color: white;
    border-color: #7ED957;
}

.filter-btn.filter-pendiente:hover {
    background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 100%);
    color: white;
    border-color: #D4AF37;
}

.filter-btn.filter-vencido:hover {
    background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
    color: white;
    border-color: #dc3545;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

@media (max-width: 768px) {
    .payment-details {
        grid-template-columns: 1fr;
    }
    
    .payment-actions {
        flex-direction: column;
    }
    
    .payment-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

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