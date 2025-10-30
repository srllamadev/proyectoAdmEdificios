<?php
// includes/helpers.php
require_once __DIR__ . '/../config/db.php';

/**
 * Comprueba si un área está disponible entre dos datetimes.
 * start, end -> 'YYYY-MM-DD HH:MM:SS'
 */
function isAreaAvailable(PDO $pdo, int $area_id, string $start, string $end, ?int $exclude_reserva_id = null): bool {
    $sql = "SELECT COUNT(*) FROM reservas
            WHERE area_comun_id = :area
              AND estado != 'cancelada'
              AND NOT (fecha_fin <= :start OR fecha_inicio >= :end)";
    if ($exclude_reserva_id) $sql .= " AND id != :exclude_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':area', $area_id, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start);
    $stmt->bindValue(':end', $end);
    if ($exclude_reserva_id) $stmt->bindValue(':exclude_id', $exclude_reserva_id, PDO::PARAM_INT);
    $stmt->execute();
    return ($stmt->fetchColumn() == 0);
}

/**
 * Calcula precio total (puedes adaptar a fracciones)
 * $start, $end -> DateTime strings
 */
function calcularPrecioTotal(PDO $pdo, int $area_id, string $start, string $end): float {
    $stmt = $pdo->prepare("SELECT precio_hora FROM areas_comunes WHERE id = :id");
    $stmt->execute([':id' => $area_id]);
    $row = $stmt->fetch();
    $precio_hora = $row ? (float)$row['precio_hora'] : 0.0;

    $s = new DateTime($start);
    $e = new DateTime($end);
    $seconds = $e->getTimestamp() - $s->getTimestamp();
    if ($seconds <= 0) return 0.0;
    // calculo por horas decimales (p. ej. 1.5h * precio_hora)
    $hours = $seconds / 3600.0;
    return round($hours * $precio_hora, 2);
}

/**
 * Genera un QR y devuelve la ruta (relativa) al archivo o URL externa como fallback.
 * Requiere: GD activado o la librería phpqrcode/QRcode disponible en assets.
 */
function generarQRParaReserva(int $reserva_id, string $contenido): string {
    $dir = __DIR__ . '/../assets/qrcodes';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = "reserva_{$reserva_id}.png";
    $filepath = $dir . '/' . $filename;

    // Opción A: Si dispones de la librería phpqrcode (colócala en assets/phpqrcode/)
    if (file_exists(__DIR__ . '/../assets/phpqrcode/qrlib.php')) {
        require_once __DIR__ . '/../assets/phpqrcode/qrlib.php';
        // QRcode::png($contenido, $filepath, 'L', 5, 2);
        \QRcode::png($contenido, $filepath, QR_ECLEVEL_L, 5, 2);
        return 'assets/qrcodes/' . $filename;
    }

    // Opción B: fallback a Google Chart API (requiere internet) — devuelve URL
    $url = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($contenido);
    return $url;
}


function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_datetime($datetime) {
    return date("d/m/Y H:i", strtotime($datetime));
}
?>
