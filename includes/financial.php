<?php
// Módulo financiero: funciones PDO-powered para facturación, pagos, nómina y reportes
require_once __DIR__ . '/db.php';

function generateReference($prefix = 'INV') {
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function createInvoice(array $data) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        $ref = generateReference('INV');
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
        body{font-family: Arial,Helvetica,sans-serif;color:#222}
        .header{display:flex;justify-content:space-between;align-items:center}
        .meta{font-size:12px;color:#666}
        .items{width:100%;border-collapse:collapse;margin-top:12px}
        .items th, .items td{border:1px solid #ddd;padding:8px;text-align:left}
        .total{font-size:18px;font-weight:700;margin-top:12px}
        .qr{margin-top:16px}
    </style></head><body>";
    $html .= "<div class='header'><div><h2>{$companyName}</h2><div class='meta'>Factura: <strong>{$invoice['reference']}</strong></div></div>";

    $payUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . 
        dirname($_SERVER['PHP_SELF']) . "/pay_invoice.php?ref=" . urlencode($invoice['reference']);
    $qrUrl = generateQRCodeUrl($payUrl, 180);
    $html .= "<div style='text-align:right'><div class='meta'>Vence: " . htmlspecialchars($invoice['due_date']) . "</div>";
    $html .= "<div class='meta'>Generado: " . date('Y-m-d H:i') . "</div></div></div>";

    $html .= "<table class='items'><thead><tr><th>Descripción</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead><tbody>";
    $sum = 0;
    if (!empty($invoice['items'])) {
        foreach ($invoice['items'] as $it) {
            $qty = intval($it['qty'] ?? 1);
            $price = number_format((float)($it['unit_price'] ?? 0), 2);
            $sub = $qty * ((float)($it['unit_price'] ?? 0));
            $sum += $sub;
            $html .= "<tr><td>" . htmlspecialchars($it['description']) . "</td><td>".$qty."</td><td>$".$price."</td><td>$".number_format($sub,2)."</td></tr>";
        }
    }
    $html .= "</tbody></table>";
    $total = number_format((float)($invoice['amount'] ?? $sum), 2);
    $html .= "<div class='total'>Total: $" . $total . "</div>";

    // QR para pago/estado
    $html .= "<div class='qr'><strong>Pago rápido / Estado</strong><br><img src='" . htmlspecialchars($qrUrl) . "' alt='QR pago' /></div>";
    $html .= "</body></html>";

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
    return "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl=".urlencode($text);
}

?>
