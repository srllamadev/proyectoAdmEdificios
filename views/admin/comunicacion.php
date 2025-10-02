<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Centro de Comunicaciones - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Procesar envío de comunicación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_comunicacion'])) {
    $destinatario_id = $_POST['destinatario_id'] == 'todos' ? null : clean_input($_POST['destinatario_id']);
    $asunto = clean_input($_POST['asunto']);
    $mensaje = clean_input($_POST['mensaje']);
    $tipo = clean_input($_POST['tipo']);
    $prioridad = clean_input($_POST['prioridad']);
    
    if (empty($asunto) || empty($mensaje)) {
        $message = '<div style="color: red;">Por favor, complete todos los campos obligatorios.</div>';
    } else {
        try {
            $query = "INSERT INTO comunicacion (remitente_id, destinatario_id, asunto, mensaje, tipo, prioridad) 
                      VALUES (:remitente_id, :destinatario_id, :asunto, :mensaje, :tipo, :prioridad)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':remitente_id', $_SESSION['user_id']);
            $stmt->bindValue(':destinatario_id', $destinatario_id);
            $stmt->bindParam(':asunto', $asunto);
            $stmt->bindParam(':mensaje', $mensaje);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':prioridad', $prioridad);
            
            if ($stmt->execute()) {
                $message = '<div style="color: green;">Comunicación enviada exitosamente.</div>';
            } else {
                $message = '<div style="color: red;">Error al enviar la comunicación.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div style="color: red;">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Obtener usuarios para el destinatario
try {
    $query = "SELECT id, name, email, role FROM users WHERE role IN ('empleado', 'inquilino') ORDER BY role, name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener comunicaciones enviadas
    $query = "SELECT c.*, u.name as destinatario_nombre 
              FROM comunicacion c 
              LEFT JOIN users u ON c.destinatario_id = u.id 
              WHERE c.remitente_id = :user_id 
              ORDER BY c.created_at DESC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $comunicaciones_enviadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $message = '<div style="color: red;">Error al cargar datos: ' . $e->getMessage() . '</div>';
}
?>

<style>
    /* Estilos específicos para el Centro de Comunicaciones */
    .communications-dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .comm-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        text-align: center;
    }

    .comm-header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        font-weight: 800;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .comm-header p {
        font-size: 1.2rem;
        opacity: 0.9;
        margin: 0;
    }

    .comm-main-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    .comm-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2F455C;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2F455C;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        transform: translateY(-1px);
    }

    .select-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
        cursor: pointer;
    }

    .select-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .textarea-control {
        width: 100%;
        padding: 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }

    .textarea-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-send {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .priority-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-alta {
        background: linear-gradient(135deg, #ff6b6b, #ff5252);
        color: white;
    }

    .priority-media {
        background: linear-gradient(135deg, #ffa726, #ff9800);
        color: white;
    }

    .priority-baja {
        background: linear-gradient(135deg, #66bb6a, #4caf50);
        color: white;
    }

    .comm-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 5px solid;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .comm-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .comm-card:hover::before {
        opacity: 1;
    }

    .comm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .comm-card.priority-alta {
        border-left-color: #ff5252;
    }

    .comm-card.priority-media {
        border-left-color: #ff9800;
    }

    .comm-card.priority-baja {
        border-left-color: #4caf50;
    }

    .comm-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
    }

    .comm-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .comm-content {
        color: #495057;
        line-height: 1.6;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
    }

    .comm-timestamp {
        font-size: 0.85rem;
        color: #868e96;
        font-style: italic;
        position: relative;
        z-index: 1;
    }

    .templates-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }

    .template-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 15px;
        padding: 25px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .template-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .template-card:hover::before {
        opacity: 1;
    }

    .template-card:hover {
        transform: translateY(-5px);
        border-color: #667eea;
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }

    .template-card.maintenance {
        border-left: 5px solid #17a2b8;
    }

    .template-card.payment {
        border-left: 5px solid #dc3545;
    }

    .template-card.meeting {
        border-left: 5px solid #28a745;
    }

    .template-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2F455C;
        margin-bottom: 15px;
        position: relative;
        z-index: 1;
    }

    .template-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }

    .btn-template {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .btn-template:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    }

    .btn-template.maintenance {
        background: linear-gradient(135deg, #17a2b8, #0288d1);
    }

    .btn-template.payment {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }

    .btn-template.meeting {
        background: linear-gradient(135deg, #28a745, #2e7d32);
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .stats-bar {
        display: flex;
        justify-content: space-around;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #667eea;
        display: block;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .comm-main-grid {
            grid-template-columns: 1fr;
        }
        
        .templates-grid {
            grid-template-columns: 1fr;
        }
        
        .comm-meta {
            grid-template-columns: 1fr;
        }
        
        .stats-bar {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>

<div class="communications-dashboard">
    <div class="comm-header">
        <h1><i class="fas fa-satellite-dish"></i> Centro de Comunicaciones</h1>
        <p>Sistema de comunicación integral para administración del edificio</p>
    </div>

    <?php if ($message): ?>
        <div style="margin-bottom: 30px;">
            <?php 
            if (strpos($message, 'exitosamente') !== false) {
                showAlert(strip_tags($message), 'success');
            } else {
                showAlert(strip_tags($message), 'error');
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number"><?php echo count($usuarios); ?></span>
            <div class="stat-label">Usuarios Activos</div>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count($comunicaciones_enviadas); ?></span>
            <div class="stat-label">Enviadas Hoy</div>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($usuarios, fn($u) => $u['role'] == 'empleado')); ?></span>
            <div class="stat-label">Empleados</div>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count(array_filter($usuarios, fn($u) => $u['role'] == 'inquilino')); ?></span>
            <div class="stat-label">Inquilinos</div>
        </div>
    </div>

    <div class="comm-main-grid">
        <!-- Formulario de nueva comunicación -->
        <div class="comm-section">
            <h2 class="section-title">
                <i class="fas fa-paper-plane"></i>
                Enviar Nueva Comunicación
            </h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="destinatario_id">
                        <i class="fas fa-users"></i> Destinatario
                    </label>
                    <select id="destinatario_id" name="destinatario_id" required class="select-control">
                        <option value="todos">📢 Todos los usuarios</option>
                        <optgroup label="👷 Empleados">
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if ($usuario['role'] == 'empleado'): ?>
                                    <option value="<?php echo $usuario['id']; ?>">
                                        <?php echo htmlspecialchars($usuario['name']); ?> (<?php echo htmlspecialchars($usuario['email']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="🏠 Inquilinos">
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if ($usuario['role'] == 'inquilino'): ?>
                                    <option value="<?php echo $usuario['id']; ?>">
                                        <?php echo htmlspecialchars($usuario['name']); ?> (<?php echo htmlspecialchars($usuario['email']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tipo">
                        <i class="fas fa-tag"></i> Tipo de Comunicación
                    </label>
                    <select id="tipo" name="tipo" required class="select-control">
                        <option value="aviso_general">📢 Aviso General</option>
                        <option value="mensaje_personal">💬 Mensaje Personal</option>
                        <option value="notificacion">🔔 Notificación</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="prioridad">
                        <i class="fas fa-exclamation-triangle"></i> Nivel de Prioridad
                    </label>
                    <select id="prioridad" name="prioridad" required class="select-control">
                        <option value="baja">🟢 Baja</option>
                        <option value="media" selected>🟡 Media</option>
                        <option value="alta">🔴 Alta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="asunto">
                        <i class="fas fa-heading"></i> Asunto
                    </label>
                    <input type="text" id="asunto" name="asunto" required class="form-control"
                           placeholder="Ej: Mantenimiento programado, Reunión de consorcio, etc.">
                </div>
                
                <div class="form-group">
                    <label for="mensaje">
                        <i class="fas fa-edit"></i> Contenido del Mensaje
                    </label>
                    <textarea id="mensaje" name="mensaje" required class="textarea-control"
                              placeholder="Escriba aquí el contenido detallado del mensaje..."></textarea>
                </div>
                
                <button type="submit" name="enviar_comunicacion" class="btn-send">
                    <i class="fas fa-rocket"></i>
                    Enviar Comunicación
                </button>
            </form>
        </div>
        
        <!-- Comunicaciones enviadas -->
        <div class="comm-section">
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Comunicaciones Enviadas
            </h2>
            
            <?php if (!empty($comunicaciones_enviadas)): ?>
                <?php foreach ($comunicaciones_enviadas as $comunicacion): ?>
                    <div class="comm-card priority-<?php echo $comunicacion['prioridad']; ?>">
                        <div class="comm-title" style="color: <?php 
                            echo $comunicacion['prioridad'] == 'alta' ? '#dc3545' : 
                                ($comunicacion['prioridad'] == 'media' ? '#ff9800' : '#28a745'); 
                        ?>;">
                            <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                            <span class="priority-indicator priority-<?php echo $comunicacion['prioridad']; ?>">
                                <i class="fas fa-flag"></i>
                                <?php echo ucfirst($comunicacion['prioridad']); ?>
                            </span>
                        </div>
                        
                        <div class="comm-meta">
                            <div class="meta-item">
                                <i class="fas fa-user-tag"></i>
                                <strong>Para:</strong> 
                                <?php echo $comunicacion['destinatario_nombre'] ? htmlspecialchars($comunicacion['destinatario_nombre']) : 'Todos los usuarios'; ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <strong>Tipo:</strong> <?php echo str_replace('_', ' ', ucfirst($comunicacion['tipo'])); ?>
                            </div>
                        </div>
                        
                        <div class="comm-content">
                            <?php echo htmlspecialchars(substr($comunicacion['mensaje'], 0, 150)); ?><?php echo strlen($comunicacion['mensaje']) > 150 ? '...' : ''; ?>
                        </div>
                        
                        <div class="comm-timestamp">
                            <i class="fas fa-clock"></i>
                            Enviado: <?php echo $comunicacion['created_at']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No hay comunicaciones enviadas aún.</p>
                    <small>Los mensajes enviados aparecerán aquí</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="templates-section">
        <h2 class="section-title">
            <i class="fas fa-layer-group"></i>
            Plantillas de Comunicación Frecuentes
        </h2>
        <p style="color: #6c757d; margin-bottom: 0;">Utilice estas plantillas prediseñadas para acelerar la creación de comunicaciones comunes</p>
        
        <div class="templates-grid">
            <div class="template-card maintenance">
                <div class="template-title">
                    <i class="fas fa-tools"></i>
                    Mantenimiento Programado
                </div>
                <div class="template-description">
                    Plantilla para avisos de mantenimiento de ascensores, agua, electricidad, y otras instalaciones del edificio.
                </div>
                <button onclick="llenarPlantilla('mantenimiento')" class="btn-template maintenance">
                    <i class="fas fa-magic"></i>
                    Usar Plantilla
                </button>
            </div>
            
            <div class="template-card payment">
                <div class="template-title">
                    <i class="fas fa-credit-card"></i>
                    Recordatorio de Pago
                </div>
                <div class="template-description">
                    Plantilla para recordar pagos de alquiler vencidos o próximos a vencer, incluyendo detalles de pago.
                </div>
                <button onclick="llenarPlantilla('pago')" class="btn-template payment">
                    <i class="fas fa-magic"></i>
                    Usar Plantilla
                </button>
            </div>
            
            <div class="template-card meeting">
                <div class="template-title">
                    <i class="fas fa-users"></i>
                    Reunión de Consorcio
                </div>
                <div class="template-description">
                    Plantilla para convocar a reuniones de consorcio, asambleas de propietarios y eventos comunitarios.
                </div>
                <button onclick="llenarPlantilla('reunion')" class="btn-template meeting">
                    <i class="fas fa-magic"></i>
                    Usar Plantilla
                </button>
            </div>
        </div>
    </div>
</div>
    
    <script>
        function llenarPlantilla(tipo) {
            const asunto = document.getElementById('asunto');
            const mensaje = document.getElementById('mensaje');
            const prioridad = document.getElementById('prioridad');
            const tipoSelect = document.getElementById('tipo');
            
            switch(tipo) {
                case 'mantenimiento':
                    asunto.value = 'Mantenimiento programado - [Especificar área/servicio]';
                    mensaje.value = 'Estimados residentes,\n\nLes informamos que se realizará mantenimiento preventivo en [especificar área/servicio] el día [fecha] desde las [hora inicio] hasta las [hora fin] horas.\n\nDurante este período, [especificar afectaciones].\n\nAgradecemos su comprensión.\n\nAdministración del Edificio';
                    prioridad.value = 'alta';
                    tipoSelect.value = 'aviso_general';
                    break;
                    
                case 'pago':
                    asunto.value = 'Recordatorio de pago de alquiler';
                    mensaje.value = 'Estimado/a [nombre],\n\nLe recordamos que su pago de alquiler correspondiente al mes de [mes] vence el [fecha].\n\nMonto: $[monto]\n\nPuede realizar el pago mediante:\n- Transferencia bancaria\n- Efectivo en administración\n- Débito automático\n\nPara consultas, no dude en contactarnos.\n\nAdministración del Edificio';
                    prioridad.value = 'media';
                    tipoSelect.value = 'mensaje_personal';
                    break;
                    
                case 'reunion':
                    asunto.value = 'Convocatoria a Reunión de Consorcio';
                    mensaje.value = 'Estimados propietarios e inquilinos,\n\nLos convocamos a la reunión de consorcio que se realizará el día [fecha] a las [hora] horas en [lugar].\n\nTemas a tratar:\n1. [Tema 1]\n2. [Tema 2]\n3. [Tema 3]\n4. Varios\n\nSu participación es importante para la buena administración del edificio.\n\nAdministración del Edificio';
                    prioridad.value = 'alta';
                    tipoSelect.value = 'aviso_general';
                    break;
            }
        }
    </script>
</body>
</html>