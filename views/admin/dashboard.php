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

    <div class="bento-card bento-card-analytics" style="background: linear-gradient(135deg, #009B77 0%, #7ED957 100%); color: white;">
        <h3 class="bento-card-title" style="color: white;"><i class="fas fa-chart-line"></i> Analíticas</h3>
        <p class="bento-card-description" style="color: rgba(255,255,255,0.95);">Dashboard ejecutivo con gráficas circulares, comparativas de consumo, indicadores financieros y ranking de departamentos.</p>
        <a href="dashboard_analytics.php" class="bento-btn" style="background: white; color: #009B77; font-weight: bold; box-shadow: 0 0 20px rgba(0, 155, 119, 0.3);">
            <i class="fas fa-chart-line"></i> Abrir Dashboard Analytics
        </a>
    </div>

    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-users"></i> Gestión de Inquilinos</h3>
        <p class="bento-card-description">Administra inquilinos, alquileres y información de residentes del edificio.</p>
        <div style="display: flex; gap: 10px;">
            <a href="inquilinos.php" class="bento-btn bento-btn-primary" style="flex: 1;">
                <i class="fas fa-eye"></i> Ver Inquilinos
            </a>
            <a href="crear_usuario.php" class="bento-btn bento-btn-success" style="flex: 1;">
                <i class="fas fa-user-plus"></i> Crear Usuario
            </a>
        </div>
    </div>
    
    <div class="bento-card">
        <h3 class="bento-card-title"><i class="fas fa-user-tie"></i> Gestión de Empleados</h3>
        <p class="bento-card-description">Controla empleados, asigna tareas y supervisa el personal del edificio.</p>
        <div style="display: flex; gap: 10px;">
            <a href="empleados.php" class="bento-btn bento-btn-primary" style="flex: 1;">
                <i class="fas fa-eye"></i> Ver Empleados
            </a>
            <a href="crear_empleado.php" class="bento-btn bento-btn-success" style="flex: 1;">
                <i class="fas fa-user-plus"></i> Crear Empleado
            </a>
        </div>
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

    <div class="bento-card bento-card-recursos">
        <h3 class="bento-card-title"><i class="fas fa-tachometer-alt"></i> Gestión de Recursos y Consumo</h3>
        <p class="bento-card-description">Monitoreo y control de consumos de agua, luz y gas por departamento.</p>
        <a href="gestion_consumo.php" class="bento-btn bento-btn-primary">
            <i class="fas fa-chart-bar"></i> Ver Consumos
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
            <a href="dashboard_analytics.php" class="bento-btn" style="background: linear-gradient(135deg, #009B77 0%, #7ED957 100%); color: white; font-weight: bold; box-shadow: 0 8px 25px rgba(0, 155, 119, 0.3);">
                <i class="fas fa-rocket"></i> Analytics Pro
            </a>
            <a href="gestion_consumo.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-tachometer-alt"></i> Control Consumos
            </a>
            <a href="dashboard_consumos.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-chart-pie"></i> Dashboard Consumos
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

<!-- Botón flotante del Chatbot -->
<div id="chatbot-button" class="chatbot-fab" style="background: linear-gradient(135deg, #009B77, #7ED957); box-shadow: 0 4px 20px rgba(0, 155, 119, 0.4);">
    <i class="fas fa-robot"></i>
</div>

<!-- Widget del Chatbot -->
<div id="chatbot-widget" class="chatbot-widget">
    <div class="chatbot-header">
        <div class="chatbot-header-left">
            <div class="chatbot-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h4>Edificio AI</h4>
                <span class="chatbot-status">
                    <span class="status-dot"></span> En línea
                </span>
            </div>
        </div>
        <div class="chatbot-header-right">
            <button id="chatbot-minimize" class="chatbot-icon-btn" title="Minimizar">
                <i class="fas fa-minus"></i>
            </button>
            <button id="chatbot-close" class="chatbot-icon-btn" title="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="chatbot-messages" id="chatbot-messages">
        <div class="chatbot-message bot-message">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">
                    <p>👋 ¡Hola! Soy <strong>Edificio AI</strong>, tu asistente virtual.</p>
                    <p>Puedo ayudarte con:</p>
                    <ul>
                        <li>📊 Estadísticas del edificio</li>
                        <li>💰 Información de pagos y deudas</li>
                        <li>⚡ Consumos de servicios</li>
                        <li>🏢 Estado de departamentos</li>
                        <li>📅 Reservas y áreas comunes</li>
                    </ul>
                    <p>¿En qué puedo ayudarte hoy?</p>
                </div>
                <span class="message-time"><?php echo date('H:i'); ?></span>
            </div>
        </div>
    </div>
    
    <div class="chatbot-suggestions" id="chatbot-suggestions">
        <button class="suggestion-chip" data-suggestion="¿Cuánto se debe en total?">💰 Deuda total</button>
        <button class="suggestion-chip" data-suggestion="¿Cuál es el consumo del mes?">⚡ Consumos del mes</button>
        <button class="suggestion-chip" data-suggestion="¿Cuántos pagos están vencidos?">⚠️ Pagos vencidos</button>
        <button class="suggestion-chip" data-suggestion="Dame un resumen general">📊 Resumen</button>
    </div>
    
    <div class="chatbot-input-container">
        <div class="chatbot-typing" id="chatbot-typing" style="display: none;">
            <span></span><span></span><span></span>
        </div>
        <textarea 
            id="chatbot-input" 
            class="chatbot-input" 
            placeholder="Escribe tu pregunta..." 
            rows="1"
        ></textarea>
        <button id="chatbot-send" class="chatbot-send-btn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<style>
/* Botón flotante */
.chatbot-fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0, 155, 119, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
    animation: pulse-fab 2s infinite;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.chatbot-fab:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 12px 32px rgba(0, 155, 119, 0.6);
    border-color: rgba(255, 255, 255, 0.5);
}

.chatbot-fab.hidden {
    display: none;
}

@keyframes pulse-fab {
    0%, 100% { box-shadow: 0 8px 24px rgba(0, 155, 119, 0.4); }
    50% { box-shadow: 0 8px 32px rgba(126, 217, 87, 0.7); }
}

/* Widget del chatbot */
.chatbot-widget {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 420px;
    height: 600px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 12px 48px rgba(0, 155, 119, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 1001;
    animation: slideUp 0.3s ease;
}

.chatbot-widget.active {
    display: flex;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Header del chatbot */
.chatbot-header {
    background: linear-gradient(135deg, #001F54 0%, #009B77 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.chatbot-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(126, 217, 87, 0.2) 0%, transparent 70%);
    animation: pulse-header 3s infinite;
}

@keyframes pulse-header {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.chatbot-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.chatbot-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chatbot-status {
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #7ED957;
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(126, 217, 87, 0.6);
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.chatbot-header-right {
    display: flex;
    gap: 8px;
}

.chatbot-icon-btn {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    z-index: 1;
}

.chatbot-icon-btn:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Mensajes */
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: linear-gradient(135deg, rgba(232, 245, 241, 0.5) 0%, rgba(212, 241, 232, 0.5) 100%);
    position: relative;
}

.chatbot-messages::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(circle at 20% 30%, rgba(0, 155, 119, 0.05) 0%, transparent 50%),
                      radial-gradient(circle at 80% 70%, rgba(126, 217, 87, 0.05) 0%, transparent 50%);
    pointer-events: none;
}

.chatbot-message {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    animation: fadeInMessage 0.3s ease;
}

@keyframes fadeInMessage {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.bot-message .message-avatar {
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 155, 119, 0.3);
}

.user-message {
    flex-direction: row-reverse;
}

.user-message .message-avatar {
    background: rgba(47, 47, 47, 0.1);
    backdrop-filter: blur(10px);
    color: #2F2F2F;
    border: 1px solid rgba(47, 47, 47, 0.2);
}

.message-content {
    flex: 1;
    min-width: 0;
}

.user-message .message-content {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.message-bubble {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    padding: 12px 16px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 155, 119, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.user-message .message-bubble {
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 155, 119, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.message-bubble p {
    margin: 0 0 8px 0;
}

.message-bubble p:last-child {
    margin-bottom: 0;
}

.message-bubble ul {
    margin: 8px 0;
    padding-left: 20px;
}

.message-bubble li {
    margin: 4px 0;
}

.message-time {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
    display: block;
}

/* Sugerencias */
.chatbot-suggestions {
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(0, 155, 119, 0.15);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.suggestion-chip {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(0, 155, 119, 0.2);
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s;
    white-space: nowrap;
    color: #2F2F2F;
}

.suggestion-chip:hover {
    background: linear-gradient(135deg, #009B77, #7ED957);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 155, 119, 0.3);
}

/* Indicador de escritura */
.chatbot-typing {
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(0, 155, 119, 0.15);
}

.chatbot-typing span {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: linear-gradient(135deg, #009B77, #7ED957);
    border-radius: 50%;
    margin: 0 2px;
    animation: typing 1.4s infinite;
    box-shadow: 0 2px 4px rgba(0, 155, 119, 0.3);
}

.chatbot-typing span:nth-child(2) {
    animation-delay: 0.2s;
}

.chatbot-typing span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

/* Input */
.chatbot-input-container {
    padding: 16px 20px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(15px);
    border-top: 1px solid rgba(0, 155, 119, 0.15);
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.chatbot-input {
    flex: 1;
    border: 1px solid rgba(0, 155, 119, 0.2);
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    border-radius: 12px;
    padding: 12px 16px;
    font-family: inherit;
    font-size: 14px;
    resize: none;
    max-height: 100px;
    transition: all 0.3s;
}

.chatbot-input:focus {
    outline: none;
    border-color: #009B77;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 12px rgba(0, 155, 119, 0.15);
}

.chatbot-send-btn {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 155, 119, 0.3);
}

.chatbot-send-btn:hover {
    transform: scale(1.15) rotate(15deg);
    box-shadow: 0 6px 20px rgba(0, 155, 119, 0.5);
    border-color: rgba(255, 255, 255, 0.5);
}

.chatbot-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .chatbot-widget {
        width: 100%;
        height: 100%;
        bottom: 0;
        right: 0;
        border-radius: 0;
    }
    
    .chatbot-fab {
        bottom: 20px;
        right: 20px;
    }
}

/* Scrollbar personalizado */
.chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
// Variables globales del chatbot
let conversationHistory = [];
let chatbotOpen = false;

// Elementos DOM
const chatbotButton = document.getElementById('chatbot-button');
const chatbotWidget = document.getElementById('chatbot-widget');
const chatbotClose = document.getElementById('chatbot-close');
const chatbotMinimize = document.getElementById('chatbot-minimize');
const chatbotInput = document.getElementById('chatbot-input');
const chatbotSend = document.getElementById('chatbot-send');
const chatbotMessages = document.getElementById('chatbot-messages');
const chatbotTyping = document.getElementById('chatbot-typing');
const suggestionChips = document.querySelectorAll('.suggestion-chip');

// Abrir/cerrar chatbot
chatbotButton.addEventListener('click', () => {
    chatbotWidget.classList.add('active');
    chatbotButton.classList.add('hidden');
    chatbotOpen = true;
    chatbotInput.focus();
});

chatbotClose.addEventListener('click', () => {
    chatbotWidget.classList.remove('active');
    chatbotButton.classList.remove('hidden');
    chatbotOpen = false;
});

chatbotMinimize.addEventListener('click', () => {
    chatbotWidget.classList.remove('active');
    chatbotButton.classList.remove('hidden');
    chatbotOpen = false;
});

// Enviar mensaje
chatbotSend.addEventListener('click', sendMessage);
chatbotInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Sugerencias
suggestionChips.forEach(chip => {
    chip.addEventListener('click', () => {
        chatbotInput.value = chip.dataset.suggestion;
        sendMessage();
    });
});

// Auto-resize del textarea
chatbotInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// Función para enviar mensaje
async function sendMessage() {
    const message = chatbotInput.value.trim();
    
    if (!message) return;
    
    // Agregar mensaje del usuario
    addMessage(message, 'user');
    
    // Limpiar input
    chatbotInput.value = '';
    chatbotInput.style.height = 'auto';
    
    // Deshabilitar envío
    chatbotSend.disabled = true;
    chatbotInput.disabled = true;
    
    // Mostrar indicador de escritura
    chatbotTyping.style.display = 'block';
    
    try {
        // Enviar a la API
        const response = await fetch('../../api/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                action: 'send_message',
                message: message,
                history: conversationHistory
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Agregar respuesta del bot
            addMessage(data.response, 'bot');
            
            // Actualizar historial
            conversationHistory.push({
                role: 'user',
                content: message
            });
            conversationHistory.push({
                role: 'assistant',
                content: data.response
            });
            
            // Limitar historial a últimos 10 mensajes
            if (conversationHistory.length > 20) {
                conversationHistory = conversationHistory.slice(-20);
            }
        } else {
            addMessage('❌ Lo siento, ocurrió un error: ' + data.error, 'bot');
        }
    } catch (error) {
        console.error('Error:', error);
        addMessage('❌ Lo siento, no pude procesar tu mensaje. Por favor, intenta de nuevo.', 'bot');
    } finally {
        // Ocultar indicador y habilitar input
        chatbotTyping.style.display = 'none';
        chatbotSend.disabled = false;
        chatbotInput.disabled = false;
        chatbotInput.focus();
    }
}

// Función para agregar mensaje al chat
function addMessage(content, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `chatbot-message ${type}-message`;
    
    const now = new Date();
    const time = now.getHours().toString().padStart(2, '0') + ':' + 
                 now.getMinutes().toString().padStart(2, '0');
    
    // Convertir markdown simple a HTML
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    content = content.replace(/\n/g, '<br>');
    
    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-${type === 'bot' ? 'robot' : 'user'}"></i>
        </div>
        <div class="message-content">
            <div class="message-bubble">
                ${content}
            </div>
            <span class="message-time">${time}</span>
        </div>
    `;
    
    chatbotMessages.appendChild(messageDiv);
    
    // Scroll al final
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

console.log('🤖 Chatbot del Edificio AI cargado correctamente');
</script>

<?php require_once '../../includes/footer.php'; ?>