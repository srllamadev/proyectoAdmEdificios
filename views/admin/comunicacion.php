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

<div class="page-header">
    <h1><i class="fas fa-comments"></i> Centro de Comunicaciones</h1>
    <p>Sistema de comunicación interna del edificio</p>
</div>

<?php if ($message): ?>
    <?php 
    if (strpos($message, 'exitosamente') !== false) {
        showAlert(strip_tags($message), 'success');
    } else {
        showAlert(strip_tags($message), 'error');
    }
    ?>
<?php endif; ?>

<div class="bento-grid" style="margin-bottom: 30px;">
        <!-- Formulario de nueva comunicación -->
        <div style="flex: 1; min-width: 400px;">
            <h2>Enviar Nueva Comunicación</h2>
            
            <form method="POST" action="">
                <div style="margin: 15px 0;">
                    <label for="destinatario_id"><strong>Destinatario:</strong></label><br>
                    <select id="destinatario_id" name="destinatario_id" required style="width: 100%; padding: 8px;">
                        <option value="todos">Todos los usuarios</option>
                        <optgroup label="Empleados">
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if ($usuario['role'] == 'empleado'): ?>
                                    <option value="<?php echo $usuario['id']; ?>">
                                        <?php echo htmlspecialchars($usuario['name']); ?> (<?php echo htmlspecialchars($usuario['email']); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Inquilinos">
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
                
                <div style="margin: 15px 0;">
                    <label for="tipo"><strong>Tipo de Comunicación:</strong></label><br>
                    <select id="tipo" name="tipo" required style="width: 100%; padding: 8px;">
                        <option value="aviso_general">Aviso General</option>
                        <option value="mensaje_personal">Mensaje Personal</option>
                        <option value="notificacion">Notificación</option>
                    </select>
                </div>
                
                <div style="margin: 15px 0;">
                    <label for="prioridad"><strong>Prioridad:</strong></label><br>
                    <select id="prioridad" name="prioridad" required style="width: 100%; padding: 8px;">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                
                <div style="margin: 15px 0;">
                    <label for="asunto"><strong>Asunto:</strong></label><br>
                    <input type="text" id="asunto" name="asunto" required 
                           style="width: 100%; padding: 8px;"
                           placeholder="Ej: Mantenimiento programado, Reunión de consorcio, etc.">
                </div>
                
                <div style="margin: 15px 0;">
                    <label for="mensaje"><strong>Mensaje:</strong></label><br>
                    <textarea id="mensaje" name="mensaje" rows="6" required
                              style="width: 100%; padding: 8px;"
                              placeholder="Escriba aquí el contenido del mensaje..."></textarea>
                </div>
                
                <button type="submit" name="enviar_comunicacion" 
                        style="background: #007cba; color: white; padding: 12px 20px; border: none; cursor: pointer;">
                    Enviar Comunicación
                </button>
            </form>
        </div>
        
        <!-- Comunicaciones enviadas -->
        <div style="flex: 1; min-width: 400px;">
            <h2>Comunicaciones Enviadas Recientemente</h2>
            
            <?php if (!empty($comunicaciones_enviadas)): ?>
                <?php foreach ($comunicaciones_enviadas as $comunicacion): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #f9f9f9;">
                        <h4 style="margin-top: 0; color: <?php 
                            echo $comunicacion['prioridad'] == 'alta' ? 'red' : 
                                ($comunicacion['prioridad'] == 'media' ? 'orange' : 'green'); 
                        ?>;">
                            <?php echo htmlspecialchars($comunicacion['asunto']); ?>
                        </h4>
                        <p><strong>Para:</strong> 
                            <?php echo $comunicacion['destinatario_nombre'] ? htmlspecialchars($comunicacion['destinatario_nombre']) : 'Todos los usuarios'; ?>
                        </p>
                        <p><strong>Tipo:</strong> <?php echo str_replace('_', ' ', ucfirst($comunicacion['tipo'])); ?></p>
                        <p><strong>Prioridad:</strong> 
                            <span style="color: <?php 
                                echo $comunicacion['prioridad'] == 'alta' ? 'red' : 
                                    ($comunicacion['prioridad'] == 'media' ? 'orange' : 'green'); 
                            ?>;">
                                <?php echo ucfirst($comunicacion['prioridad']); ?>
                            </span>
                        </p>
                        <p><?php echo htmlspecialchars(substr($comunicacion['mensaje'], 0, 150)); ?><?php echo strlen($comunicacion['mensaje']) > 150 ? '...' : ''; ?></p>
                        <small>Enviado: <?php echo $comunicacion['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comunicaciones enviadas.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <hr>
    
    <h2>Plantillas de Comunicación Frecuentes</h2>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div style="border: 1px solid #ddd; padding: 15px; min-width: 250px; background: #e6f3ff;">
            <h4>Mantenimiento Programado</h4>
            <p>Para avisos de mantenimiento de ascensores, agua, electricidad, etc.</p>
            <button onclick="llenarPlantilla('mantenimiento')" style="background: #007cba; color: white; padding: 8px 15px; border: none; cursor: pointer;">
                Usar Plantilla
            </button>
        </div>
        
        <div style="border: 1px solid #ddd; padding: 15px; min-width: 250px; background: #ffe6e6;">
            <h4>Recordatorio de Pago</h4>
            <p>Para recordar pagos de alquiler vencidos o próximos a vencer.</p>
            <button onclick="llenarPlantilla('pago')" style="background: #dc3545; color: white; padding: 8px 15px; border: none; cursor: pointer;">
                Usar Plantilla
            </button>
        </div>
        
        <div style="border: 1px solid #ddd; padding: 15px; min-width: 250px; background: #e6ffe6;">
            <h4>Reunión de Consorcio</h4>
            <p>Para convocar a reuniones de consorcio o asambleas.</p>
            <button onclick="llenarPlantilla('reunion')" style="background: #28a745; color: white; padding: 8px 15px; border: none; cursor: pointer;">
                Usar Plantilla
            </button>
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