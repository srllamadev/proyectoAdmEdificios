<?php
// Módulo financiero: funciones PDO-powered para facturación, pagos, nómina y reportes
require_once __DIR__ . '/db.php';

// Asegurar existencia de tabla staff (si no existe)
function ensureStaffTableExists() {
    global $pdo;
    $sql = "CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(128) NOT NULL,
        type VARCHAR(64) DEFAULT 'empleado',
        area VARCHAR(128) DEFAULT NULL,
        days_per_month INT DEFAULT 30,
        monthly_rate DECIMAL(12,2) DEFAULT 0,
        daily_rate DECIMAL(12,2) DEFAULT 0,
        meta JSON DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
}

function createStaff(array $data) {
    global $pdo;
    ensureStaffTableExists();
    $stmt = $pdo->prepare("INSERT INTO staff (name,type,area,days_per_month,monthly_rate,daily_rate,meta) VALUES (:name,:type,:area,:days,:monthly,:daily,:meta)");
    $stmt->execute([
        ':name'=>$data['name'] ?? '',
        ':type'=>$data['type'] ?? 'empleado',
        ':area'=>$data['area'] ?? null,
        ':days'=>intval($data['days_per_month'] ?? 30),
        ':monthly'=>floatval($data['monthly_rate'] ?? 0),
        ':daily'=>floatval($data['daily_rate'] ?? 0),
        ':meta'=> isset($data['meta']) ? json_encode($data['meta']) : null
    ]);
    return ['status'=>'ok','id'=>$pdo->lastInsertId()];
}

function getStaff($id = null) {
    global $pdo;
    ensureStaffTableExists();
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    $stmt = $pdo->query("SELECT * FROM staff ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateReference($prefix = 'INV') {
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function createInvoice(array $data) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        $ref = generateReference('INV');
        // si no se pasó amount, calcularlo desde items
        if (empty($data['amount']) && !empty($data['items']) && is_array($data['items'])) {
            $sum = 0.0;
            foreach ($data['items'] as $it) {
                $qty = intval($it['qty'] ?? 1);
                $unit = floatval($it['unit_price'] ?? 0);
                $sub = round($unit * $qty, 2);
                $sum += $sub;
            }
            $data['amount'] = round($sum, 2);
        }

        $stmt = $pdo->prepare("INSERT INTO invoices (reference,resident_id,amount,due_date,meta) VALUES (:ref,:resident,:amount,:due,:meta)");
        $stmt->execute([
            ':ref'=>$ref,
            ':resident'=>$data['resident_id'] ?? null,
            ':amount'=>$data['amount'] ?? 0,
            ':due'=>$data['due_date'] ?? null,
            ':meta'=> isset($data['meta']) ? json_encode($data['meta']) : null
        ]);
        $invoiceId = $pdo->lastInsertId();
        if (!empty($data['items']) && is_array($data['items'])) {
            $stmtItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id,description,qty,unit_price) VALUES (:inv,:desc,:qty,:price)");
            foreach ($data['items'] as $it) {
                $stmtItem->execute([
                    ':inv'=>$invoiceId,
                    ':desc'=>$it['description'] ?? '',
                    ':qty'=>$it['qty'] ?? 1,
                    ':price'=>$it['unit_price'] ?? 0
                ]);
            }
        }
        $pdo->commit();
        return ['status'=>'ok','invoice_id'=>$invoiceId,'reference'=>$ref];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
}

function getInvoice($idOrRef) {
    global $pdo;
    if (is_numeric($idOrRef)) {
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = :v");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE reference = :v");
    }
    $stmt->execute([':v'=>$idOrRef]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inv) return null;
    $stmtItems = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :inv");
    $stmtItems->execute([':inv'=>$inv['id']]);
    $inv['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    return $inv;
}

function recordPayment($invoiceId, $amount, $method = 'manual', $gateway = null, $tx_ref = null, $metadata = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO payments (invoice_id,amount,method,gateway,tx_ref,metadata) VALUES (:inv,:amount,:method,:gateway,:tx,:meta)");
        $stmt->execute([
            ':inv'=>$invoiceId,
            ':amount'=>$amount,
            ':method'=>$method,
            ':gateway'=>$gateway,
            ':tx'=>$tx_ref,
            ':meta'=> $metadata ? json_encode($metadata) : null
        ]);
        // actualizar estado de factura
        $paidSumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as paid FROM payments WHERE invoice_id = :inv");
        $paidSumStmt->execute([':inv'=>$invoiceId]);
        $paid = (float)$paidSumStmt->fetchColumn();
        $invStmt = $pdo->prepare("SELECT amount FROM invoices WHERE id = :inv");
        $invStmt->execute([':inv'=>$invoiceId]);
        $total = (float)$invStmt->fetchColumn();
        $newStatus = $paid >= $total ? 'paid' : 'pending';
        if ($paid >= $total) {
            $update = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = :inv");
            $update->execute([':inv'=>$invoiceId]);
        }
        return ['status'=>'ok','paid_total'=>$paid,'invoice_total'=>$total,'invoice_status'=>$newStatus];
    } catch (Exception $e) {
        return ['status'=>'error','message'=>$e->getMessage()];
    }
}

function getOverdues() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM invoices WHERE status <> 'paid' AND due_date IS NOT NULL AND due_date < CURDATE()");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generatePayroll(array $entries, $period) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO payroll (staff_id,period,gross,deductions,meta) VALUES (:staff,:period,:gross,:ded,:meta)");
    $ids = [];
    foreach ($entries as $e) {
        $stmt->execute([
            ':staff'=>$e['staff_id'],
            ':period'=>$period,
            ':gross'=>$e['gross'],
            ':ded'=>$e['deductions'] ?? 0,
            ':meta'=> isset($e['meta']) ? json_encode($e['meta']) : null
        ]);
        $ids[] = $pdo->lastInsertId();
    }
    return ['status'=>'ok','created'=>count($ids),'ids'=>$ids];
}

function markPayrollPaid($payrollId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE payroll SET paid = 1 WHERE id = :id");
    $stmt->execute([':id'=>$payrollId]);
    return ['status'=>'ok','updated'=>$stmt->rowCount()];
}

function reportIncomeExpenses($from, $to) {
    global $pdo;
    $stmtInc = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='income' AND DATE(created_at) BETWEEN :from AND :to");
    $stmtExp = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='expense' AND DATE(created_at) BETWEEN :from AND :to");
    $stmtInc->execute([':from'=>$from,':to'=>$to]);
    $stmtExp->execute([':from'=>$from,':to'=>$to]);
    $inc = (float)$stmtInc->fetchColumn();
    $exp = (float)$stmtExp->fetchColumn();
    return ['from'=>$from,'to'=>$to,'income'=>$inc,'expenses'=>$exp,'net'=>$inc-$exp];
}

function integratePaymentGateway($gateway, $payload = []) {
    // stubs: implementar según API de proveedor
    switch (strtolower($gateway)) {
        case 'tigo':
        case 'tigo_money':
            return ['status'=>'ok','gateway'=>'tigo','payload'=>$payload];
        case 'card':
        case 'tarjeta':
            // mock: devolver URL de pago y payload útil
            $paymentId = 'CARD-' . strtoupper(bin2hex(random_bytes(3)));
            $paymentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['PHP_SELF']) . 
                '/pay_invoice.php?ref=' . urlencode($payload['invoice_ref'] ?? ($payload['reference'] ?? '')) . '&tx=' . urlencode($paymentId);
            return ['status'=>'ok','gateway'=>'card','payment_id'=>$paymentId,'payment_url'=>$paymentUrl,'payload'=>$payload];
        case 'wallet':
            return ['status'=>'ok','gateway'=>'wallet','payload'=>$payload];
        case 'crypto':
            return ['status'=>'ok','gateway'=>'crypto','payment_address'=>'tb1q...','payload'=>$payload];
        default:
            return ['status'=>'error','message'=>'Gateway no soportado'];
    }
}

function generateInvoicePDF($invoice) {
    // Construir HTML elegante para la factura
    $companyName = 'Administración del Edificio';
    $html = "<html><head><meta charset='utf-8'><style>
        body{font-family: Inter,Arial,Helvetica,sans-serif;color:#222;margin:0;padding:20px;background:#f7f7f8}
        .card{background:#fff;border-radius:8px;padding:18px;box-shadow:0 4px 14px rgba(0,0,0,0.06);max-width:800px;margin:0 auto}
        .header{display:flex;justify-content:space-between;align-items:flex-start}
        .meta{font-size:12px;color:#666}
        h2{margin:0 0 6px 0}
        .items{width:100%;border-collapse:collapse;margin-top:12px}
        .items th, .items td{border-bottom:1px solid #eee;padding:10px;text-align:left}
        .items thead th{background:#fafafa;color:#333;font-weight:600}
        .total{font-size:18px;font-weight:700;margin-top:12px;text-align:right}
        .qr{margin-top:16px;text-align:center}
        .status-badge{display:inline-block;padding:6px 10px;border-radius:6px;background:#ffefc2;color:#8a6d00;font-weight:700;font-size:12px}
    </style></head><body><div class='card'>";
    // decode meta if present
    $meta = [];
    if (!empty($invoice['meta'])) {
        if (is_string($invoice['meta'])) {
            $meta = json_decode($invoice['meta'], true) ?: [];
        } elseif (is_array($invoice['meta'])) {
            $meta = $invoice['meta'];
        }
    }
    $clientName = !empty($meta['user_name']) ? $meta['user_name'] : ($invoice['resident_id'] ? 'Residente #' . $invoice['resident_id'] : '');
    $invoiceMonth = !empty($meta['month']) ? $meta['month'] : '';
    $paymentMethod = !empty($meta['payment_method']) ? $meta['payment_method'] : '';

    $html .= "<div class='header'><div><h2>{$companyName}</h2>";
    if ($clientName) {
        $html .= "<div class='meta'>Cliente: <strong>" . htmlspecialchars($clientName) . "</strong></div>";
    }
    $html .= "<div class='meta'>Factura: <strong>{$invoice['reference']}</strong></div>";
    $status = htmlspecialchars($invoice['status'] ?? 'pending');
    $html .= "<div style='margin-top:6px'><span class='status-badge'>" . ucfirst($status) . "</span></div></div>";

    $payUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . 
        dirname($_SERVER['PHP_SELF']) . "/pay_invoice.php?ref=" . urlencode($invoice['reference']);
    $qrSrc = generateQRCodeUrl($payUrl, 180);
    // Si qrSrc es una URL externa (fallback a Google Charts), intentar descargar y convertir a data URI
    if ($qrSrc && (stripos($qrSrc, 'http://') === 0 || stripos($qrSrc, 'https://') === 0)) {
        $img = @file_get_contents($qrSrc);
        if ($img !== false && strlen($img) > 0) {
            $qrSrc = 'data:image/png;base64,' . base64_encode($img);
        }
    }
    $html .= "<div style='text-align:right'><div class='meta'>Vence: " . htmlspecialchars($invoice['due_date']) . "</div>";
    $html .= "<div class='meta'>Generado: " . date('Y-m-d H:i') . "</div>";
    if ($invoiceMonth) {
        $html .= "<div class='meta'>Mes: " . htmlspecialchars($invoiceMonth) . "</div>";
    }
    if ($paymentMethod) {
        $html .= "<div class='meta'>Método: " . htmlspecialchars(ucfirst($paymentMethod)) . "</div>";
    }
    $html .= "</div></div>";

    // Mostrar un bloque de concepto/resumen (tipo, mes, método)
    $typeKey = $meta['type'] ?? '';
    $typeMap = [
        'alquiler' => 'Alquiler',
        'electricidad' => 'Electricidad',
        'agua' => 'Agua',
        'gas' => 'Gas',
        'mantenimiento' => 'Mantenimiento'
    ];
    $typeLabel = $typeMap[$typeKey] ?? ($typeKey ?: 'Servicio');

    $html .= "<div style='margin-top:12px;padding:8px;border-radius:6px;background:#fbfbfc'><strong>Concepto:</strong> " . htmlspecialchars($typeLabel) . " ";
    if (!empty($invoiceMonth)) $html .= " - Mes: " . htmlspecialchars($invoiceMonth);
    if (!empty($paymentMethod)) $html .= " - Método: " . htmlspecialchars(ucfirst($paymentMethod));
    $html .= "</div>";

    $html .= "<table class='items'><thead><tr><th>Descripción</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead><tbody>";
    $sum = 0;
    if (!empty($invoice['items'])) {
        foreach ($invoice['items'] as $it) {
            $qty = intval($it['qty'] ?? 1);
            $unitRaw = (float)($it['unit_price'] ?? 0);
            $price = number_format($unitRaw, 2);
            $sub = round($qty * $unitRaw, 2);
            $sum += $sub;
            $html .= "<tr><td>" . htmlspecialchars($it['description']) . "</td><td>".$qty."</td><td>$".$price."</td><td>$".number_format($sub,2)."</td></tr>";
        }
    }
    // Si no hay items, mostrar una fila con el concepto y el monto total
    if (empty($invoice['items'])) {
        $amt = round((float)$invoice['amount'],2);
        $html .= "<tr><td>" . htmlspecialchars($typeLabel) . "</td><td>1</td><td>$" . number_format($amt,2) . "</td><td>$" . number_format($amt,2) . "</td></tr>";
    }
    $html .= "</tbody></table>";
    $total = number_format(round((float)($invoice['amount'] ?? $sum),2), 2);
    $html .= "<div class='total'>Total: $" . $total . "</div>";

    // QR para pago/estado: solo incluir si el método de pago es 'qr'
    if (strtolower($paymentMethod) === 'qr' && !empty($qrSrc)) {
        $html .= "<div class='qr'><strong>Pago rápido / Estado</strong><br><img src='" . htmlspecialchars($qrSrc) . "' alt='QR pago' style='width:180px;height:180px' /></div>";
    }
    $html .= "</div></body></html>";

    // Si existe Dompdf, generar PDF binario, si no, devolver HTML para mostrar en navegador
    if (class_exists('Dompdf\\Dompdf')) {
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();
        return $dompdf->output();
    }
    return $html;
}

function generateQRCodeUrl($text, $size = 200) {
    // Intentar generar QR localmente usando chillerlan/php-qrcode
    if (class_exists('chillerlan\\QRCode\\QRCode')) {
        try {
            $options = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\QROptions::OUTPUT_IMAGE_PNG,
                'eccLevel' => \chillerlan\QRCode\QROptions::ECC_L,
                // scale ajustado según tamaño solicitado
                'scale' => max(2, intval($size / 50)),
            ]);
            $qrcode = new \chillerlan\QRCode\QRCode($options);
            $pngData = $qrcode->render($text);
            return 'data:image/png;base64,' . base64_encode($pngData);
        } catch (Exception $e) {
            // si falla, caemos al fallback externo
        }
    }
    // Fallback: Google Charts
    return "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl=" . urlencode($text);
}

// Simple generator de PDF usando la librería mínima FPDF incluida (fallback)
function generateInvoicePDFWithFPDF($invoice) {
    // Cargar fpdf mínimo
    $fpdfFile = __DIR__ . '/libs/fpdf.php';
    if (!file_exists($fpdfFile)) return null;
    require_once $fpdfFile;

    // Preparar QR image (generación local si la librería está disponible)
    $payUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . 
        dirname($_SERVER['PHP_SELF']) . "/../pay_invoice.php?ref=" . urlencode($invoice['reference']);
    // usar carpeta tmp del proyecto para mayor previsibilidad
    $projectTmp = realpath(__DIR__ . '/../tmp') ?: (__DIR__ . '/../tmp');
    $tmp = $projectTmp . '/qr_' . preg_replace('/[^A-Za-z0-9_-]/', '', $invoice['reference']) . '.png';

    // Intentar usar chillerlan/php-qrcode si está instalada
    if (class_exists('\\chillerlan\\QRCode\\QRCode')) {
        try {
            // Opciones básicas
            $options = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\QROptions::OUTPUT_IMAGE_PNG,
                'eccLevel' => \chillerlan\QRCode\QROptions::ECC_L,
                'scale' => 5,
            ]);
            $qrcode = new \chillerlan\QRCode\QRCode($options);
            $imageData = $qrcode->render($payUrl);
            @file_put_contents($tmp, $imageData);
        } catch (Exception $e) {
            // fallback a Google Charts si algo falla
            $qrUrl = generateQRCodeUrl($payUrl, 200);
            $img = @file_get_contents($qrUrl);
            if ($img !== false) @file_put_contents($tmp, $img);
        }
    } else {
        // Fallback: descargar QR desde Google Charts
        $qrUrl = generateQRCodeUrl($payUrl, 200);
        $img = @file_get_contents($qrUrl);
        if ($img !== false) @file_put_contents($tmp, $img);
    }

    $pdf = new FPDF('P','mm','A4');
    $pdf->SetFont('Helvetica','',12);
    $pdf->AddPage();
    // Mostrar cliente y metadatos si existen
    $pdf->Cell(0,6, 'Administración del Edificio', 0,1);
    // extraer meta
    $metaArr = [];
    if (!empty($invoice['meta'])) {
        if (is_string($invoice['meta'])) $metaArr = json_decode($invoice['meta'], true) ?: [];
        elseif (is_array($invoice['meta'])) $metaArr = $invoice['meta'];
    }
    $client = $metaArr['user_name'] ?? ($invoice['resident_id'] ? 'Residente #' . $invoice['resident_id'] : '');
    if ($client) $pdf->Cell(0,6, 'Cliente: ' . $client, 0,1);
    $pdf->Cell(0,6, 'Factura: ' . ($invoice['reference'] ?? ''), 0,1);
    // Tipo/Concepto legible
    $pdfTypeLabel = $typeMap[$metaArr['type'] ?? ''] ?? ($metaArr['type'] ?? 'Servicio');
    $pdf->Cell(0,6, 'Concepto: ' . $pdfTypeLabel, 0,1);
    if (!empty($metaArr['month'])) $pdf->Cell(0,6, 'Mes: ' . $metaArr['month'], 0,1);
    if (!empty($metaArr['payment_method'])) $pdf->Cell(0,6, 'Método: ' . ucfirst($metaArr['payment_method']), 0,1);
    $pdf->Ln(4);
    $pdf->Cell(0,6, 'Fecha: ' . date('Y-m-d H:i'), 0,1);
    $pdf->Ln(4);

    // Items
    $pdf->Cell(100,6,'Descripción',1);
    $pdf->Cell(30,6,'Cantidad',1);
    $pdf->Cell(30,6,'Unit.',1);
    $pdf->Cell(30,6,'Subtotal',1);
    $pdf->Ln();
    $sum = 0;
    if (!empty($invoice['items']) && is_array($invoice['items'])) {
        foreach ($invoice['items'] as $it) {
            $qty = intval($it['qty'] ?? 1);
            $unitRaw = (float)($it['unit_price'] ?? 0);
            $unit = number_format($unitRaw,2);
            $sub = round($qty * $unitRaw, 2);
            $sum += $sub;
            $pdf->Cell(100,6, substr($it['description'] ?? '',0,60),1);
            $pdf->Cell(30,6,$qty,1);
            $pdf->Cell(30,6,'$'.$unit,1);
            $pdf->Cell(30,6,'$'.number_format($sub,2),1);
            $pdf->Ln();
        }
    }
    $total = number_format(round((float)($invoice['amount'] ?? $sum),2),2);
    $pdf->Ln(4);
    $pdf->Cell(0,6,'Total: $' . $total,0,1);

    // Si no hay items, mostrar la fila con concepto y monto
    if (empty($invoice['items'])) {
        $pdf->Ln(2);
        $amt = number_format(round((float)$invoice['amount'],2),2);
        $pdf->Cell(100,6, $pdfTypeLabel,1);
        $pdf->Cell(30,6, '1',1);
        $pdf->Cell(30,6, '$' . $amt,1);
        $pdf->Cell(30,6, '$' . $amt,1);
    }

    // QR
    // QR: solo insertar si el método de pago en meta es 'qr'
    $metaArr = [];
    if (!empty($invoice['meta'])) {
        if (is_string($invoice['meta'])) $metaArr = json_decode($invoice['meta'], true) ?: [];
        elseif (is_array($invoice['meta'])) $metaArr = $invoice['meta'];
    }
    $pdfMethod = strtolower($metaArr['payment_method'] ?? '');
    if ($pdfMethod === 'qr' && file_exists($tmp) && filesize($tmp) > 0) {
        // FPDF necesita ruta local; aseguramos ruta absoluta
        $pdf->Image($tmp,150,40,40,40);
    }

    // Output binary
    ob_start();
    $pdf->Output('doc.pdf','S');
    $out = ob_get_clean();
    // eliminar tmp
    if (file_exists($tmp)) @unlink($tmp);
    return $out;
}

// Obtener facturas de un residente (por resident_id)
function getInvoicesByResident($residentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE resident_id = :rid ORDER BY created_at DESC");
    $stmt->execute([':rid'=>$residentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener historial de pagos de un residente (todos los pagos asociados a sus facturas)
function getPaymentsByResident($residentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, i.reference as invoice_reference, i.resident_id FROM payments p LEFT JOIN invoices i ON p.invoice_id = i.id WHERE i.resident_id = :rid ORDER BY p.created_at DESC");
    $stmt->execute([':rid'=>$residentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener historial de facturas con filtros opcionales
function getInvoiceHistoryByResident($residentId, $from = null, $to = null, $type = null) {
    global $pdo;
    $sql = "SELECT i.*, COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id = i.id),0) as paid FROM invoices i WHERE i.resident_id = :rid";
    $params = [':rid'=>$residentId];
    if ($from) {
        $sql .= " AND DATE(i.created_at) >= :from";
        $params[':from'] = $from;
    }
    if ($to) {
        $sql .= " AND DATE(i.created_at) <= :to";
        $params[':to'] = $to;
    }
    if ($type) {
        $sql .= " AND JSON_EXTRACT(i.meta,'$.type') = :type";
        $params[':type'] = '"' . $type . '"'; // almacenar como JSON string
    }
    $sql .= " ORDER BY i.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Registrar pago manual y actualizar estado de la factura
function addManualPayment($invoiceId, $amount, $method = 'manual', $metadata = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO payments (invoice_id,amount,method,gateway,tx_ref,metadata) VALUES (:inv,:amount,:method,:gateway,:tx,:meta)");
        $stmt->execute([
            ':inv'=>$invoiceId,
            ':amount'=>$amount,
            ':method'=>$method,
            ':gateway'=>null,
            ':tx'=>null,
            ':meta'=> $metadata ? json_encode($metadata) : null
        ]);
        // actualizar estado similar a recordPayment
        $paidSumStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as paid FROM payments WHERE invoice_id = :inv");
        $paidSumStmt->execute([':inv'=>$invoiceId]);
        $paid = (float)$paidSumStmt->fetchColumn();
        $invStmt = $pdo->prepare("SELECT amount FROM invoices WHERE id = :inv");
        $invStmt->execute([':inv'=>$invoiceId]);
        $total = (float)$invStmt->fetchColumn();
        if ($paid >= $total) {
            $update = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = :inv");
            $update->execute([':inv'=>$invoiceId]);
        }
        return ['status'=>'ok','paid_total'=>$paid,'invoice_total'=>$total];
    } catch (Exception $e) {
        return ['status'=>'error','message'=>$e->getMessage()];
    }
}

// Exportar historial de facturas de un residente a CSV
function exportInvoiceHistoryCsv($residentId, $from = null, $to = null, $type = null) {
    $rows = getInvoiceHistoryByResident($residentId, $from, $to, $type);
    $out = fopen('php://memory','r+');
    fputcsv($out, ['reference','created_at','due_date','type','amount','paid','status','observations']);
    foreach ($rows as $r) {
        $meta = [];
        if (!empty($r['meta'])) {
            if (is_string($r['meta'])) $meta = json_decode($r['meta'], true) ?: [];
            elseif (is_array($r['meta'])) $meta = $r['meta'];
        }
        $typeLabel = $meta['type'] ?? '';
        $obs = $meta['observations'] ?? '';
        fputcsv($out, [$r['reference'],$r['created_at'],$r['due_date'],$typeLabel,$r['amount'],$r['paid'],$r['status'],$obs]);
    }
    rewind($out);
    $csv = stream_get_contents($out);
    fclose($out);
    return $csv;
}

// Obtener deuda (sumatoria de facturas no pagadas) por residente
function getDebtsByResident($residentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT i.id,i.reference,i.amount,i.due_date,i.status,
        COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id = i.id),0) as paid
        FROM invoices i WHERE i.resident_id = :rid ORDER BY i.due_date ASC");
    $stmt->execute([':rid'=>$residentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debt = 0.0;
    foreach ($rows as $r) {
        $owed = max(0, (float)$r['amount'] - (float)$r['paid']);
        if ($owed > 0) $debt += $owed;
    }
    return ['invoices'=>$rows,'total_debt'=>$debt];
}

// Calcular consumos (luz, agua, gas) para un residente y convertir a montos
// Asunciones razonables aplicadas cuando no hay una tabla de tarifas:
// - Se intenta mapear el alquiler.activo.numero_departamento -> departamentos.nombre
// - Si no se encuentra departamento, devolvemos error para que el administrador valide
// - Tarifas por defecto (pueden ajustarse): luz: 0.12, agua: 0.50, gas: 0.60 (por unidad)
// Retorna ['status'=>'ok','items'=>[], 'total'=>float, 'breakdown'=>[]] o ['status'=>'error','message'=>...] 
function computeConsumptionsForResident($residentId, $month = null, $rates = null) {
    global $pdo;
    try {
        // Obtener alquiler activo del inquilino
        $stmt = $pdo->prepare("SELECT * FROM alquileres WHERE inquilino_id = :rid AND estado = 'activo' LIMIT 1");
        $stmt->execute([':rid'=>$residentId]);
        $alquiler = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$alquiler) return ['status'=>'error','message'=>'No se encontró un alquiler activo para el residente'];

        // Intentar mapear numero_departamento -> departamentos.id buscando por nombre
        $numero = $alquiler['numero_departamento'] ?? null;
        $departamentoId = null;
        if ($numero) {
            $dstmt = $pdo->prepare("SELECT id FROM departamentos WHERE nombre = :num LIMIT 1");
            $dstmt->execute([':num'=>$numero]);
            $drow = $dstmt->fetch(PDO::FETCH_ASSOC);
            if ($drow) $departamentoId = $drow['id'];
            else {
                // intentar como número entero
                if (is_numeric($numero)) {
                    $dstmt2 = $pdo->prepare("SELECT id FROM departamentos WHERE id = :id LIMIT 1");
                    $dstmt2->execute([':id'=>intval($numero)]);
                    $d2 = $dstmt2->fetch(PDO::FETCH_ASSOC);
                    if ($d2) $departamentoId = $d2['id'];
                }
            }
        }

        if (!$departamentoId) {
            return ['status'=>'error','message'=>'No se pudo mapear el número de departamento del alquiler al registro de departamentos. Por favor verifique el campo numero_departamento o cree el departamento correspondiente.'];
        }

        // Determinar rango de fechas basado en $month (format YYYY-MM). Si es null, usar mes anterior
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $from = date('Y-m-01', strtotime($month . '-01'));
            $to = date('Y-m-t', strtotime($month . '-01'));
        } else {
            // mes anterior
            $from = date('Y-m-01', strtotime('first day of previous month'));
            $to = date('Y-m-t', strtotime('last day of previous month'));
        }

        // Tarifas por defecto si no se pasan
        $defaultRates = ['luz'=>0.12, 'agua'=>0.50, 'gas'=>0.60];
        $rates = is_array($rates) ? array_merge($defaultRates, $rates) : $defaultRates;

        // Agregar lecturas por tipo (unimos con sensores para conocer el tipo 'luz','agua','gas')
        $sql = "SELECT s.tipo AS service_type, COALESCE(SUM(l.valor),0) AS total_qty
                FROM lecturas l
                LEFT JOIN sensores s ON s.id = l.sensor_id
                WHERE l.departamento_id = :dep AND DATE(l.recibido_en) BETWEEN :from AND :to
                GROUP BY s.tipo";
        $q = $pdo->prepare($sql);
        $q->execute([':dep'=>$departamentoId, ':from'=>$from, ':to'=>$to]);
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        $total = 0.0;
        $breakdown = [];
        foreach ($rows as $r) {
            $stype = $r['service_type'] ?? null;
            if (!$stype) continue;
            $qty = (float)$r['total_qty'];
            $unitPrice = $rates[$stype] ?? 0.0;
            $sub = round($qty * $unitPrice, 2);
            $items[] = ['description'=> ucfirst($stype), 'qty'=>$qty, 'unit_price'=>$unitPrice];
            $breakdown[$stype] = ['qty'=>$qty,'unit_price'=>$unitPrice,'subtotal'=>$sub];
            $total += $sub;
        }

        // Asegurar que existan items para los tres servicios (si no hubo lecturas, qty = 0)
        foreach (['luz','agua','gas'] as $svc) {
            if (!isset($breakdown[$svc])) {
                $items[] = ['description'=> ucfirst($svc), 'qty'=>0, 'unit_price'=>($rates[$svc] ?? 0)];
                $breakdown[$svc] = ['qty'=>0,'unit_price'=>($rates[$svc] ?? 0),'subtotal'=>0.00];
            }
        }

        $total = round($total,2);
        return ['status'=>'ok','items'=>$items,'total'=>$total,'breakdown'=>$breakdown,'from'=>$from,'to'=>$to];
    } catch (Exception $e) {
        return ['status'=>'error','message'=>$e->getMessage()];
    }
}

// Generar PDF/HTML para una planilla (payroll entries) para un periodo
function generatePayrollPDFForPeriod($period) {
    global $pdo;
    // Obtener planillas del periodo
    $stmt = $pdo->prepare("SELECT pr.*, s.name as staff_name FROM payroll pr LEFT JOIN staff s ON pr.staff_id = s.id WHERE pr.period = :period");
    $stmt->execute([':period'=>$period]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = "<html><head><meta charset='utf-8'><style>body{font-family:Arial,Helvetica,sans-serif}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f4f4f4}</style></head><body>";
    $html .= "<h2>Planilla - Periodo: " . htmlspecialchars($period) . "</h2>";
    $html .= "<table><thead><tr><th>Empleado</th><th>Gross</th><th>Deducciones</th><th>Net</th><th>Pagado</th></tr></thead><tbody>";
    foreach ($rows as $r) {
        $html .= "<tr><td>".htmlspecialchars($r['staff_name'] ?? $r['staff_id'])."</td><td>$".number_format($r['gross'],2)."</td><td>$".number_format($r['deductions'],2)."</td><td>$".number_format($r['net'],2)."</td><td>".($r['paid'] ? 'Sí' : 'No')."</td></tr>";
    }
    $html .= "</tbody></table></body></html>";

    if (class_exists('Dompdf\\Dompdf')) {
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();
        return $dompdf->output();
    }
    return $html;
}

// Registrar ingreso/egreso en tabla transactions
function addTransaction($type, $amount, $reference = null, $category = null, $metadata = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO transactions (type,reference,amount,category,metadata) VALUES (:type,:ref,:amount,:cat,:meta)");
    $stmt->execute([
        ':type'=>$type,
        ':ref'=>$reference,
        ':amount'=>$amount,
        ':cat'=>$category,
        ':meta'=> $metadata ? json_encode($metadata) : null
    ]);
    return ['status'=>'ok','id'=>$pdo->lastInsertId()];
}

?>
