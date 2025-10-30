<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../includes/functions.php';
require_once '../../config/database.php';

if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

// Librería QR ya usada antes
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Dompdf (para crear PDF). Si no está instalado, se detecta más abajo.
use Dompdf\Dompdf;

$database = new Database();
$db = $database->getConnection();
$message = '';

try {
    // Datos del inquilino logueado (si quieres nombre puedes hacer JOIN con users, pero lo dejamos tal cual)
    $query = "SELECT i.* FROM inquilinos i WHERE i.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $inquilino = $stmt->fetch(PDO::FETCH_ASSOC);

    // Crear reserva (botón original "crear_reserva")
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_reserva'])) {
        $area_id    = clean_input($_POST['area_id']);
        $fecha      = clean_input($_POST['fecha']);
        $hora_inicio= clean_input($_POST['hora_inicio']);
        $hora_fin   = clean_input($_POST['hora_fin']);
        $descripcion= clean_input($_POST['descripcion']);

        if (empty($area_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
            $message = '<div class="alert-error">⚠️ Por favor, complete todos los campos obligatorios.</div>';
        } else {
            // Formar datetimes
            $fecha_inicio = $fecha . ' ' . $hora_inicio;
            $fecha_fin    = $fecha . ' ' . $hora_fin;

            // Validación: fecha y hora coherentes (fin debe ser mayor que inicio)
            $dtInicio = new DateTime($fecha_inicio);
            $dtFin    = new DateTime($fecha_fin);
            if ($dtFin <= $dtInicio) {
                $message = '<div class="alert-error">❌ La hora de fin debe ser posterior a la hora de inicio.</div>';
            } else {
                // Verificar conflictos
                $query = "SELECT COUNT(*) as conflictos
                          FROM reservas
                          WHERE area_comun_id = :area_id
                            AND estado != 'cancelada'
                            AND (
                                (:fecha_inicio BETWEEN fecha_inicio AND fecha_fin)
                                OR (:fecha_fin BETWEEN fecha_inicio AND fecha_fin)
                                OR (fecha_inicio BETWEEN :fecha_inicio2 AND :fecha_fin2)
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
                    $message = '<div class="alert-error">❌ El área ya está reservada en ese horario. Por favor, elija otro.</div>';
                } else {
                    // Obtener datos del área (precio_hora, nombre)
                    $query = "SELECT * FROM areas_comunes WHERE id = :area_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':area_id', $area_id);
                    $stmt->execute();
                    $area = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$area) {
                        $message = '<div class="alert-error">⚠️ Área no encontrada.</div>';
                    } else {
                        // Calcular horas y precio
                        $interval = $dtFin->diff($dtInicio);
                        $horas = $interval->h + ($interval->i / 60) + ($interval->d * 24); // cuenta días por si cruza medianoche
                        $precio_total = round($horas * $area['precio_hora'], 2);

                        // Insertar reserva (estado 'pendiente' como lo tenías)
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
                            $reserva_id = $db->lastInsertId();

                            // Generar QR con Endroid v6
                            $qrText = "Reserva ID: $reserva_id\nÁrea: {$area['nombre']}\nFecha inicio: $fecha_inicio\nFecha fin: $fecha_fin\nPrecio: $$precio_total";
                            $qrCode = new QrCode($qrText);
                            $writer = new PngWriter();
                            $qrResult = $writer->write($qrCode);
                            $qr_base64 = base64_encode($qrResult->getString());

                            // --- Generación de recibo PDF usando Dompdf (si está instalado) ---
                            $recibo_link = ''; // si se crea el PDF se guardará el link aquí
                            if (class_exists('Dompdf\\Dompdf')) {
                                try {
                                    // Crear carpeta para recibos si no existe
                                    $recibosDir = __DIR__ . '/../../uploads/recibos';
                                    if (!is_dir($recibosDir)) {
                                        mkdir($recibosDir, 0755, true);
                                    }

                                    // HTML del recibo (puedes ajustar el diseño aquí)
                                    $html = '
                                        <html>
                                        <head>
                                            <meta charset="utf-8">
                                            <style>
                                                body { font-family: DejaVu Sans, Arial, sans-serif; color: #2F455C; }
                                                .header { background: #1DCDFE; padding: 10px; color: #fff; text-align: center; border-radius: 6px; }
                                                .content { margin-top: 15px; }
                                                .row { margin-bottom: 8px; }
                                                .label { font-weight: bold; color: #2F455C; }
                                                .qr { margin-top: 10px; }
                                                .footer { margin-top: 20px; font-size: 12px; color: #666; }
                                            </style>
                                        </head>
                                        <body>
                                            <div class="header"><h2>Recibo de Reserva</h2></div>
                                            <div class="content">
                                                <div class="row"><span class="label">Reserva ID:</span> ' . $reserva_id . '</div>
                                                <div class="row"><span class="label">Área:</span> ' . htmlspecialchars($area['nombre']) . '</div>
                                                <div class="row"><span class="label">Fecha inicio:</span> ' . htmlspecialchars($fecha_inicio) . '</div>
                                                <div class="row"><span class="label">Fecha fin:</span> ' . htmlspecialchars($fecha_fin) . '</div>
                                                <div class="row"><span class="label">Precio total:</span> $' . number_format($precio_total,2) . '</div>
                                                <div class="row"><span class="label">Descripción:</span> ' . nl2br(htmlspecialchars($descripcion)) . '</div>
                                                <div class="qr"><img src="data:image/png;base64,' . $qr_base64 . '" width="140" height="140" /></div>
                                                <div class="footer">Emitido: ' . date('Y-m-d H:i') . '</div>
                                            </div>
                                        </body>
                                        </html>
                                    ';

                                    // Generar PDF con Dompdf
                                    $dompdf = new Dompdf();
                                    $dompdf->loadHtml($html);
                                    $dompdf->setPaper('A4', 'portrait');
                                    $dompdf->render();

                                    $pdfOutput = $dompdf->output();
                                    $reciboFilename = 'recibo_reserva_' . $reserva_id . '.pdf';
                                    $reciboPath = $recibosDir . '/' . $reciboFilename;
                                    file_put_contents($reciboPath, $pdfOutput);

                                    // Generar link relativo para descarga desde web
                                    $recibo_link = 'uploads/recibos/' . $reciboFilename;

                                } catch (Exception $e) {
                                    // Si falla Dompdf por alguna razón, se continúa sin PDF
                                    $recibo_link = '';
                                }
                            } else {
                                // Dompdf no instalado — informamos al usuario cómo instalar
                                $recibo_link = '';
                            }

                            // Mensaje final: incluye QR y enlace al PDF si existe
                            $message = '<div class="alert-success">';
                            $message .= '✅ Reserva creada exitosamente. <br>';
                            $message = '<div class="alert-success"> 
                ✅ Reserva creada exitosamente.<br> 
                <img src="data:image/png;base64,' . $qr_base64 . '" alt="QR de la reserva" class="qr-img"><br><br>
                <a href="recibo_reserva.php?id=' . $reserva_id . '" class="btn-pdf" target="_blank">🧾 Descargar PDF</a>
            </div>';

                            if (!empty($recibo_link)) {
                                $message .= '<a href="' . htmlspecialchars($recibo_link) . '" target="_blank">📄 Descargar recibo (PDF)</a>';
                            } else {
                                // Si Dompdf no está disponible, instrucción breve
                                $message .= '<br><small>Recibo PDF no generado automáticamente. Para habilitar PDFs instale Dompdf: <code>composer require dompdf/dompdf</code></small>';
                            }
                            $message .= '</div>';
                        } else {
                            $message = '<div class="alert-error">❌ Error al crear la reserva.</div>';
                        }
                    }
                }
            }
        }
    }

    // Áreas disponibles
    $query = "SELECT * FROM areas_comunes WHERE estado = 'disponible' ORDER BY nombre";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mis reservas
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
    $message = '<div class="alert-error">Error: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reserva de Áreas Comunes - Inquilino</title>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<style>
/* 🎨 Paleta personalizada */
:root {
    --azul: #1DCDFE;
    --verde: #21D0B2;
    --menta: #34F5C5;
    --oscuro: #2F455C;
    --gris-fondo: #f4f6f9;
}
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: var(--gris-fondo);
    margin: 0;
    padding: 20px;
}
h1, h2 { color: var(--oscuro); text-align: center; }
a { color: var(--azul); text-decoration: none; }
a:hover { text-decoration: underline; }
.container {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-top: 20px;
    justify-content: center;
}
.form-box, .reservas-box {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    flex: 1;
    min-width: 350px;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.form-box:hover, .reservas-box:hover { transform: translateY(-3px); }
button {
    background: var(--azul);
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}
button:hover { background: var(--verde); }
select, input, textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 12px;
}
.reserva-item {
    background: #f9f9f9;
    border-left: 5px solid var(--azul);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 12px;
}
.reserva-item span { font-weight: bold; }
.alert-success {
    background: var(--menta);
    color: var(--oscuro);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    text-align: center;
}
.alert-error {
    background: #ffdddd;
    color: var(--oscuro);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    text-align: center;
}
.qr-img {
    margin-top: 10px;
    width: 150px;
    height: 150px;
}
#calendar {
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
}
.btn-pdf {
    background: var(--oscuro);
    color: #fff;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
}
.btn-pdf:hover {
    background: var(--azul);
}

</style>
</head>
<body>

<h1>Reserva de Áreas Comunes</h1>
<p style="text-align:center;">
    <a href="dashboard.php">← Volver al Dashboard</a> | <a href="../../logout.php">Cerrar Sesión</a>
</p>

<?php if ($message) echo $message; ?>

<div class="container">
    <div class="form-box">
        <h2>📌 Nueva Reserva</h2>
        <form method="POST">
            <label>Área Común:</label>
            <select name="area_id" required>
                <option value="">Seleccione un área...</option>
                <?php foreach($areas as $area): ?>
                    <option value="<?= $area['id'] ?>">
                        <?= htmlspecialchars($area['nombre']) ?> - $<?= number_format($area['precio_hora'],2) ?>/hora
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Fecha:</label>
            <input type="date" name="fecha" required min="<?= date('Y-m-d'); ?>">

            <label>Hora Inicio:</label>
            <input type="time" name="hora_inicio" required>

            <label>Hora Fin:</label>
            <input type="time" name="hora_fin" required>

            <label>Descripción:</label>
            <textarea name="descripcion" rows="3"></textarea>

            <button type="submit" name="crear_reserva">Crear Reserva</button>
        </form>
    </div>

    <div class="reservas-box">
        <h2>📅 Mis Reservas</h2>
        <?php if (!empty($mis_reservas)): ?>
            <?php foreach($mis_reservas as $reserva): ?>
                <div class="reserva-item">
                    <p><span>Área:</span> <?= htmlspecialchars($reserva['area_nombre']); ?></p>
                    <p><span>Fecha:</span> <?= date('d/m/Y H:i', strtotime($reserva['fecha_inicio'])) ?> - <?= date('H:i', strtotime($reserva['fecha_fin'])) ?></p>
                    <p><span>Estado:</span> <?= ucfirst($reserva['estado']); ?></p>
                    <p><span>Precio:</span> $<?= number_format($reserva['precio_total'],2); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes reservas registradas.</p>
        <?php endif; ?>
    </div>
</div>

<h2 style="text-align:center;">📌 Calendario de Reservas</h2>
<div id='calendar'></div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        locale: 'es',
        headerToolbar: { 
            left: 'prev,next today', 
            center: 'title', 
            right: 'dayGridMonth,timeGridWeek,timeGridDay' 
        },
        events: [
            <?php foreach($mis_reservas as $reserva): ?>
            {
                title: '<?= htmlspecialchars($reserva['area_nombre']); ?>',
                start: '<?= $reserva['fecha_inicio']; ?>',
                end: '<?= $reserva['fecha_fin']; ?>',
                color: '<?= $reserva['estado']=="pendiente"?"#1DCDFE":($reserva['estado']=="confirmada"?"#21D0B2":"#ffc107"); ?>'
            },
            <?php endforeach; ?>
        ]
    });
    calendar.render();
});
</script>

</body>
</html>
