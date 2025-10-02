<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es inquilino
if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$message = '';

// Obtener información del inquilino
try {
    $query = "SELECT i.* FROM inquilinos i WHERE i.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Procesar formulario de nueva reserva
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_reserva'])) {
        $area_id = clean_input($_POST['area_id']);
        $fecha = clean_input($_POST['fecha']);
        $hora_inicio = clean_input($_POST['hora_inicio']);
        $hora_fin = clean_input($_POST['hora_fin']);
        $descripcion = clean_input($_POST['descripcion']);
        
        if (empty($area_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
            $message = '<div style="color: red;">Por favor, complete todos los campos obligatorios.</div>';
        } else {
            $fecha_inicio = $fecha . ' ' . $hora_inicio;
            $fecha_fin = $fecha . ' ' . $hora_fin;
            
            // Verificar disponibilidad
            $query = "SELECT COUNT(*) as conflictos 
                      FROM reservas 
                      WHERE area_comun_id = :area_id 
                      AND estado != 'cancelada'
                      AND (
                          (:fecha_inicio BETWEEN fecha_inicio AND fecha_fin) OR
                          (:fecha_fin BETWEEN fecha_inicio AND fecha_fin) OR
                          (fecha_inicio BETWEEN :fecha_inicio2 AND :fecha_fin2)
                      )";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':area_id', $area_id);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':fecha_inicio2', $fecha_inicio);
            $stmt->bindParam(':fecha_fin2', $fecha_fin);
            $stmt->execute();
            $conflictos = $stmt->fetch(PDO::FETCH_ASSOC)['conflictos'];
            
            if ($conflictos > 0) {
                $message = '<div style="color: red;">El área ya está reservada en ese horario. Por favor, elija otro horario.</div>';
            } else {
                // Obtener precio del área
                $query = "SELECT precio_hora FROM areas_comunes WHERE id = :area_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':area_id', $area_id);
                $stmt->execute();
                $area = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Calcular precio total
                $inicio = new DateTime($fecha_inicio);
                $fin = new DateTime($fecha_fin);
                $horas = $fin->diff($inicio)->h + ($fin->diff($inicio)->i / 60);
                $precio_total = $horas * $area['precio_hora'];
                
                // Crear reserva
                $query = "INSERT INTO reservas (inquilino_id, area_comun_id, fecha_inicio, fecha_fin, descripcion, precio_total, estado) 
                          VALUES (:inquilino_id, :area_id, :fecha_inicio, :fecha_fin, :descripcion, :precio_total, 'pendiente')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':inquilino_id', $inquilino['id']);
                $stmt->bindParam(':area_id', $area_id);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':precio_total', $precio_total);
                
                if ($stmt->execute()) {
                    $message = '<div style="color: green;">Reserva creada exitosamente. Está pendiente de confirmación.</div>';
                } else {
                    $message = '<div style="color: red;">Error al crear la reserva.</div>';
                }
            }
        }
    }
    
    // Obtener áreas comunes disponibles
    $query = "SELECT * FROM areas_comunes WHERE estado = 'disponible' ORDER BY nombre";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener reservas del inquilino
    $query = "SELECT r.*, ac.nombre as area_nombre 
              FROM reservas r 
              JOIN areas_comunes ac ON r.area_comun_id = ac.id 
              WHERE r.inquilino_id = :inquilino_id 
              ORDER BY r.fecha_inicio DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':inquilino_id', $inquilino['id']);
    $stmt->execute();
    $mis_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $message = '<div style="color: red;">Error: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Áreas Comunes - Inquilino</title>
</head>
<body>
    <h1>Reserva de Áreas Comunes</h1>
    <p><a href="dashboard.php">← Volver al Dashboard</a> | <a href="../../logout.php">Cerrar Sesión</a></p>
    
    <hr>
    
    <?php if ($message): ?>
        <?php echo $message; ?>
    <?php endif; ?>
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        <!-- Formulario de nueva reserva -->
        <div style="flex: 1; min-width: 400px;">
            <h2>Nueva Reserva</h2>
            
            <form method="POST" action="">
                <div style="margin: 15px 0;">
                    <label for="area_id"><strong>Área Común:</strong></label><br>
                    <select id="area_id" name="area_id" required style="width: 100%; padding: 8px;">
                        <option value="">Seleccione un área...</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?php echo $area['id']; ?>">
                                <?php echo htmlspecialchars($area['nombre']); ?> - 
                                $<?php echo number_format($area['precio_hora'], 2); ?>/hora
                                (Capacidad: <?php echo $area['capacidad']; ?> personas)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin: 15px 0;">
                    <label for="fecha"><strong>Fecha:</strong></label><br>
                    <input type="date" id="fecha" name="fecha" required 
                           min="<?php echo date('Y-m-d'); ?>" 
                           style="width: 100%; padding: 8px;">
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="hora_inicio"><strong>Hora Inicio:</strong></label><br>
                        <input type="time" id="hora_inicio" name="hora_inicio" required style="width: 100%; padding: 8px;">
                    </div>
                    <div style="flex: 1;">
                        <label for="hora_fin"><strong>Hora Fin:</strong></label><br>
                        <input type="time" id="hora_fin" name="hora_fin" required style="width: 100%; padding: 8px;">
                    </div>
                </div>
                
                <div style="margin: 15px 0;">
                    <label for="descripcion"><strong>Descripción del evento:</strong></label><br>
                    <textarea id="descripcion" name="descripcion" rows="3" 
                              style="width: 100%; padding: 8px;"
                              placeholder="Ej: Cumpleaños familiar, reunión de amigos, etc."></textarea>
                </div>
                
                <button type="submit" name="crear_reserva" 
                        style="background: #28a745; color: white; padding: 12px 20px; border: none; cursor: pointer;">
                    Crear Reserva
                </button>
            </form>
        </div>
        
        <!-- Lista de mis reservas -->
        <div style="flex: 1; min-width: 400px;">
            <h2>Mis Reservas</h2>
            
            <?php if (!empty($mis_reservas)): ?>
                <?php foreach ($mis_reservas as $reserva): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: <?php echo $reserva['estado'] == 'confirmada' ? '#e6ffe6' : ($reserva['estado'] == 'cancelada' ? '#ffe6e6' : '#fff8e1'); ?>;">
                        <h4 style="margin-top: 0;">
                            <?php echo htmlspecialchars($reserva['area_nombre']); ?>
                        </h4>
                        <p><strong>Fecha y Hora:</strong><br>
                           <?php echo date('d/m/Y', strtotime($reserva['fecha_inicio'])); ?> de 
                           <?php echo date('H:i', strtotime($reserva['fecha_inicio'])); ?> a 
                           <?php echo date('H:i', strtotime($reserva['fecha_fin'])); ?></p>
                        <p><strong>Estado:</strong> 
                            <span style="color: <?php 
                                echo $reserva['estado'] == 'confirmada' ? 'green' : 
                                    ($reserva['estado'] == 'cancelada' ? 'red' : 'orange'); 
                            ?>;">
                                <?php echo ucfirst($reserva['estado']); ?>
                            </span>
                        </p>
                        <p><strong>Precio Total:</strong> $<?php echo number_format($reserva['precio_total'], 2); ?></p>
                        <?php if ($reserva['descripcion']): ?>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($reserva['descripcion']); ?></p>
                        <?php endif; ?>
                        <small>Creada: <?php echo $reserva['created_at']; ?></small>
                        
                        <?php if ($reserva['estado'] == 'pendiente'): ?>
                            <br><br>
                            <a href="cancelar_reserva.php?id=<?php echo $reserva['id']; ?>" 
                               onclick="return confirm('¿Está seguro de cancelar esta reserva?')"
                               style="background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; font-size: 12px;">
                               Cancelar Reserva
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tienes reservas registradas.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <hr>
    
    <h2>Áreas Comunes Disponibles</h2>
    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        <?php foreach ($areas as $area): ?>
            <div style="border: 1px solid #ddd; padding: 15px; min-width: 250px; background: #f9f9f9;">
                <h3><?php echo htmlspecialchars($area['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($area['descripcion'] ?? 'Sin descripción'); ?></p>
                <p><strong>Capacidad:</strong> <?php echo $area['capacidad']; ?> personas</p>
                <p><strong>Precio por hora:</strong> $<?php echo number_format($area['precio_hora'], 2); ?></p>
                <p><strong>Horario:</strong> <?php echo $area['horario_apertura']; ?> - <?php echo $area['horario_cierre']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>