<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Si Composer autoload existe, cargarlo para que TCPDF (u otras libs) estén disponibles
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'csv';
$filter_dep = isset($_GET['departamento']) && $_GET['departamento'] !== '' ? $_GET['departamento'] : null;
$filter_recurso = isset($_GET['recurso']) && $_GET['recurso'] !== '' ? $_GET['recurso'] : null;
$filter_start = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : null;
$filter_end = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : null;

$whereParts = [];
$params = [];
if ($filter_dep) { $whereParts[] = 'd.nombre = :dep'; $params[':dep'] = $filter_dep; }
if ($filter_recurso) { $whereParts[] = 's.tipo = :recurso'; $params[':recurso'] = $filter_recurso; }
if ($filter_start) { $whereParts[] = 'l.recibido_en >= :start'; $params[':start'] = $filter_start . ' 00:00:00'; }
if ($filter_end) { $whereParts[] = 'l.recibido_en <= :end'; $params[':end'] = $filter_end . ' 23:59:59'; }
$whereSQL = '';
if (count($whereParts)>0) $whereSQL = 'WHERE ' . implode(' AND ', $whereParts);

$rows = [];
try {
    if (tableExists($conn,'lecturas')) {
        $stmt = $conn->prepare("SELECT d.nombre AS departamento, s.tipo AS recurso, l.valor AS consumo, l.recibido_en AS fecha FROM lecturas l JOIN departamentos d ON d.id = l.departamento_id JOIN sensores s ON s.id = l.sensor_id $whereSQL ORDER BY l.recibido_en DESC");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (tableExists($conn,'consumos')) {
        $sql = "SELECT departamento, recurso, lectura AS consumo, fecha FROM consumos $whereSQL ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
    exit;
}

if ($type === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=dashboard_consumos_' . date('Ymd') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Departamento','Recurso','Consumo','Fecha']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['departamento'],$r['recurso'],$r['consumo'],$r['fecha']]);
    }
    fclose($out);
    exit;
}

// XLSX / Excel export (intento usar PhpSpreadsheet si está disponible)
if ($type === 'xlsx') {
    // Si PhpSpreadsheet está disponible, usarlo
    if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Título
        $sheet->setCellValue('A1', 'Dashboard — Consumos');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        // Headers
        $headers = ['Departamento','Recurso','Consumo','Fecha'];
        $col = 'A';
        $row = 3;
        foreach ($headers as $h) {
            $sheet->setCellValue($col.$row, $h);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $sheet->getStyle($col.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF48FB1');
            $sheet->getStyle($col.$row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $col++;
        }
        // Body
        $r = $row + 1;
        foreach ($rows as $data) {
            $sheet->setCellValue('A'.$r, $data['departamento']);
            $sheet->setCellValue('B'.$r, $data['recurso']);
            $sheet->setCellValue('C'.$r, $data['consumo']);
            $sheet->setCellValue('D'.$r, $data['fecha']);
            $r++;
        }
        // Auto size columns
        foreach (range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="dashboard_consumos_' . date('Ymd') . '.xlsx"');
        $writer->save('php://output');
        exit;
    }

    // Fallback: generar un archivo .xls basado en HTML con estilo (Excel lo abre bien)
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename=dashboard_consumos_' . date('Ymd') . '.xls');
    // BOM
    echo "\xEF\xBB\xBF";
    echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body>";
    echo '<table border="1" cellpadding="6" style="border-collapse:collapse;font-family:Arial,sans-serif;">';
    echo '<tr style="background:#F48FB1;color:#fff;font-weight:bold;"><th>Departamento</th><th>Recurso</th><th>Consumo</th><th>Fecha</th></tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['departamento']) . '</td>';
        echo '<td>' . htmlspecialchars($r['recurso']) . '</td>';
        echo '<td style="mso-number-format:\"0.00\"; text-align:right;">' . htmlspecialchars(number_format($r['consumo'],2,'.',',')) . '</td>';
        echo '<td>' . htmlspecialchars($r['fecha']) . '</td>';
        echo '</tr>';
    }
    echo '</table></body></html>';
    exit;
}

// PDF fallback: si existe TCPDF, usarlo, sino devolver CSV
if ($type === 'pdf') {
    if (class_exists('TCPDF')) {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $html = '<h2>Dashboard — Consumos</h2><table border="1" cellpadding="4"><thead><tr><th>Departamento</th><th>Recurso</th><th>Consumo</th><th>Fecha</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr><td>'.htmlspecialchars($r['departamento']).'</td><td>'.htmlspecialchars($r['recurso']).'</td><td>'.htmlspecialchars($r['consumo']).'</td><td>'.htmlspecialchars($r['fecha']).'</td></tr>';
        }
        $html .= '</tbody></table>';
        $pdf->writeHTML($html);
        $pdf->Output('dashboard_consumos_' . date('Ymd') . '.pdf', 'D');
        exit;
    } else {
        // si no hay TCPDF, devolver CSV con aviso
        header('Content-Type: text/plain; charset=utf-8');
        echo "TCPDF no está instalado en el servidor. Se ha generado un CSV en su lugar.\n";
        echo implode("\n", array_map(function($r){ return implode(',', [$r['departamento'],$r['recurso'],$r['consumo'],$r['fecha']]); }, $rows));
        exit;
    }
}

function tableExists($conn, $table) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE :t");
        $stmt->execute([':t'=>$table]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) { return false; }
}

?>