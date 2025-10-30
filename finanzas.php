<?php
require_once 'includes/db.php';
require_once 'includes/financial.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
// Preview consumptions button handler: calcular y mostrar desglose sin crear factura
$previewCons = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calc_consumption'])) {
    $previewResident = intval($_POST['resident_id'] ?? 0);
    $previewMonth = $_POST['invoice_month'] ?? null;
    if ($previewResident <= 0) {
        $message = 'Seleccione un residente válido para calcular consumos.';
    } else {
        // Si se proporcionaron montos fijos en el formulario, preferirlos para la vista previa
        $fixed_luz = floatval($_POST['luz_amount'] ?? 0);
        $fixed_agua = floatval($_POST['agua_amount'] ?? 0);
        $fixed_gas = floatval($_POST['gas_amount'] ?? 0);
        if ($fixed_luz > 0 || $fixed_agua > 0 || $fixed_gas > 0) {
            $items = [];
            if ($fixed_luz > 0) $items[] = ['description'=>'Luz','qty'=>1,'unit_price'=>$fixed_luz];
            if ($fixed_agua > 0) $items[] = ['description'=>'Agua','qty'=>1,'unit_price'=>$fixed_agua];
            if ($fixed_gas > 0) $items[] = ['description'=>'Gas','qty'=>1,'unit_price'=>$fixed_gas];
            $total = 0; foreach ($items as $it) { $total += round($it['qty'] * $it['unit_price'],2); }
            $previewCons = ['status'=>'ok','items'=>$items,'total'=>round($total,2),'from'=>'-','to'=>'-'];
        } else {
            $previewCons = computeConsumptionsForResident($previewResident, $previewMonth);
            if ($previewCons['status'] !== 'ok') {
                $message = 'No se pudo calcular consumos: ' . ($previewCons['message'] ?? 'error');
            }
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_invoice'])) {
    $data = [
        'resident_id' => $_POST['resident_id'] ?: null,
        'items' => [],
        'amount' => floatval($_POST['amount'] ?? 0),
        'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
        'meta' => [
            'type' => $_POST['service_type'] ?? 'alquiler',
            'user_name' => trim($_POST['user_name'] ?? ''),
            'month' => $_POST['invoice_month'] ?? null,
            'payment_method' => $_POST['payment_method'] ?? 'qr'
        ]
    ];
    // Leer montos fijos mensuales si el admin los proporcionó
    $fixed_luz = floatval($_POST['luz_amount'] ?? 0);
    $fixed_agua = floatval($_POST['agua_amount'] ?? 0);
    $fixed_gas = floatval($_POST['gas_amount'] ?? 0);
    $use_fixed = ($fixed_luz > 0 || $fixed_agua > 0 || $fixed_gas > 0);
    if ($use_fixed) {
        $fixedItems = [];
        if ($fixed_luz > 0) $fixedItems[] = ['description'=>'Luz','qty'=>1,'unit_price'=>$fixed_luz];
        if ($fixed_agua > 0) $fixedItems[] = ['description'=>'Agua','qty'=>1,'unit_price'=>$fixed_agua];
        if ($fixed_gas > 0) $fixedItems[] = ['description'=>'Gas','qty'=>1,'unit_price'=>$fixed_gas];
        $data['items'] = $fixedItems;
        $sumFixed = 0.0; foreach ($fixedItems as $fi) { $sumFixed += round($fi['qty'] * $fi['unit_price'],2); }
        $data['amount'] = round($sumFixed,2);
        $data['meta']['fixed_monthly_charges'] = ['luz'=>$fixed_luz,'agua'=>$fixed_agua,'gas'=>$fixed_gas];
    }
    // Si se solicitó generar para todos, iterar sobre inquilinos activos
    $createdRefs = [];
    if (!empty($_POST['generate_for_all']) && $_POST['generate_for_all'] == '1') {
        // obtener lista de inquilinos con alquiler activo
        $tstmt = $pdo->query("SELECT i.id FROM inquilinos i LEFT JOIN alquileres a ON a.inquilino_id = i.id AND a.estado = 'activo' WHERE a.id IS NOT NULL");
        $allTenants = $tstmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($allTenants as $tenantId) {
            $d = $data;
            $d['resident_id'] = intval($tenantId);
            if (isset($d['meta']['type']) && $d['meta']['type'] === 'mantenimiento') {
                if ($use_fixed) {
                    // usar montos fijos ya preparados en $data
                    $d['items'] = $data['items'];
                    $d['amount'] = $data['amount'];
                    $d['meta']['fixed_monthly_charges'] = $data['meta']['fixed_monthly_charges'] ?? [];
                } else {
                    $cons = computeConsumptionsForResident($d['resident_id'], $d['meta']['month'] ?? null);
                    if ($cons['status'] === 'ok') {
                        $d['items'] = $cons['items'];
                        $d['amount'] = $cons['total'];
                        $d['meta']['consumption_breakdown'] = $cons['breakdown'];
                        $d['meta']['consumption_from'] = $cons['from'];
                        $d['meta']['consumption_to'] = $cons['to'];
                    } else {
                        // registrar error en mensaje (pero continuar con otros inquilinos)
                        $createdRefs[] = ['tenant'=>$tenantId,'status'=>'error','message'=>$cons['message'] ?? 'error calculando consumos'];
                        continue;
                    }
                }
            }
            $r = createInvoice($d);
            if ($r['status'] === 'ok') $createdRefs[] = ['tenant'=>$tenantId,'status'=>'ok','reference'=>$r['reference']];
            else $createdRefs[] = ['tenant'=>$tenantId,'status'=>'error','message'=>$r['message'] ?? ''];
        }
        // construir mensaje resumen
        $okRefs = array_filter($createdRefs, function($x){ return $x['status'] === 'ok'; });
        $errRefs = array_filter($createdRefs, function($x){ return $x['status'] !== 'ok'; });
        $message = count($okRefs) . ' facturas creadas. ' . (count($errRefs) ? (count($errRefs) . ' errores ocurrieron.') : '');
        $createdRef = null;
    } else {
        // crear sola factura (ya se puede haber calculado items si mantenimiento)
        if (isset($data['meta']['type']) && $data['meta']['type'] === 'mantenimiento') {
            if ($use_fixed) {
                // usar montos fijos para una sola factura
                $data['items'] = $data['items'];
                $data['meta']['fixed_monthly_charges'] = $data['meta']['fixed_monthly_charges'] ?? [];
            } else {
                $cons = computeConsumptionsForResident(intval($data['resident_id'] ?? 0), $data['meta']['month'] ?? null);
                if ($cons['status'] === 'ok') {
                    $data['items'] = $cons['items'];
                    $data['amount'] = $cons['total'];
                    $data['meta']['consumption_breakdown'] = $cons['breakdown'];
                    $data['meta']['consumption_from'] = $cons['from'];
                    $data['meta']['consumption_to'] = $cons['to'];
                } else {
                    $message = 'No se pudo calcular consumos: ' . ($cons['message'] ?? 'error');
                }
            }
        }
        $res = createInvoice($data);
        if ($res['status'] === 'ok') {
            $message = "Factura creada: {$res['reference']}";
            $createdRef = $res['reference'];
        } else {
            $message = "Error: {$res['message']}";
        }
    }
}

// Manejar acciones de planilla (marcar pagada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_payroll_paid'])) {
    $payrollId = intval($_POST['payroll_id'] ?? 0);
    if ($payrollId > 0) {
        $resPay = markPayrollPaid($payrollId);
        if ($resPay['status'] === 'ok') {
            $message = "Planilla #{$payrollId} marcada como pagada.";
        } else {
            $message = "Error al marcar planilla: " . ($resPay['message'] ?? 'desconocido');
        }
    }
}

// Crear empleado (staff)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_staff'])) {
    $staffData = [
        'name' => trim($_POST['staff_name'] ?? ''),
        'type' => $_POST['staff_type'] ?? 'empleado',
        'area' => $_POST['staff_area'] ?? null,
        'days_per_month' => intval($_POST['staff_days'] ?? 30),
        'monthly_rate' => floatval($_POST['staff_monthly'] ?? 0),
        'daily_rate' => floatval($_POST['staff_daily'] ?? 0),
    ];
    $cs = createStaff($staffData);
    if ($cs['status'] === 'ok') {
        $message = 'Empleado creado con ID ' . $cs['id'];
    } else {
        $message = 'Error creando empleado';
    }
}

// Crear entrada de planilla individual (vinculada a staff)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payroll_entry'])) {
    $entry = [
        'staff_id' => intval($_POST['pay_staff_id'] ?? 0),
        'gross' => floatval($_POST['pay_gross'] ?? 0),
        'deductions' => floatval($_POST['pay_deductions'] ?? 0),
        'meta' => [ 'days_worked' => intval($_POST['pay_days_worked'] ?? 0) ]
    ];
    $period = $_POST['pay_period'] ?? date('Y-m');
    $gen = generatePayroll([$entry], $period);
    if ($gen['status'] === 'ok') {
        $message = 'Entrada de planilla creada';
    } else {
        $message = 'Error creando entrada de planilla';
    }
}

// Registrar pago manual desde la UI de historial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_payment'])) {
    $invoiceId = intval($_POST['invoice_id'] ?? 0);
    $amount = floatval($_POST['pay_amount'] ?? 0);
    $method = $_POST['pay_method'] ?? 'manual';
    if ($invoiceId > 0 && $amount > 0) {
        $r = addManualPayment($invoiceId, $amount, $method, ['entered_by'=>getCurrentUserName() ?? 'system']);
        if ($r['status'] === 'ok') {
            $message = 'Pago registrado correctamente.';
        } else {
            $message = 'Error al registrar pago: ' . ($r['message'] ?? '');
        }
    } else {
        $message = 'Factura y monto válidos son requeridos.';
    }
}

// Obtener morosos y reportes
$overdues = getOverdues();
$report = reportIncomeExpenses(date('Y-m-01'), date('Y-m-t'));

// Cálculos adicionales basados en historial de facturas
try {
    // Total facturado
    $totStmt = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM invoices");
    $totalInvoiced = (float)$totStmt->fetchColumn();
    // Total cobrado
    $colStmt = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments");
    $totalCollected = (float)$colStmt->fetchColumn();
    // Pendiente (simple diferencia) — también puede calcularse por factura
    $totalOutstanding = round($totalInvoiced - $totalCollected, 2);

    // Facturas creadas este mes
    $monthFrom = date('Y-m-01');
    $monthTo = date('Y-m-t');
    $mStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE DATE(created_at) BETWEEN :from AND :to");
    $mStmt->execute([':from'=>$monthFrom, ':to'=>$monthTo]);
    $invoicesThisMonth = (int)$mStmt->fetchColumn();

    // Número de morosos
    $overdueCount = count($overdues);

    // Últimas 10 facturas con suma pagada
    $recentStmt = $pdo->query("SELECT i.*, COALESCE(SUM(p.amount),0) as paid FROM invoices i LEFT JOIN payments p ON p.invoice_id = i.id GROUP BY i.id ORDER BY i.created_at DESC LIMIT 10");
    $recentInvoices = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $totalInvoiced = $totalCollected = $totalOutstanding = 0;
    $invoicesThisMonth = $overdueCount = 0;
    $recentInvoices = [];
}

// Planilla: periodo seleccionado y filas
$payrollPeriod = $_POST['payroll_period'] ?? date('Y-m');
try {
    $prStmt = $pdo->prepare("SELECT pr.*, s.name as staff_name FROM payroll pr LEFT JOIN staff s ON pr.staff_id = s.id WHERE pr.period = :period ORDER BY pr.created_at DESC");
    $prStmt->execute([':period'=>$payrollPeriod]);
    $payrollRows = $prStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $payrollRows = [];
}

// Obtener lista de empleados para los formularios
$staffList = getStaff();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Finanzas - SLH</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/bento-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bento-body">
    <div class="bento-page-header">
        <h1 class="bento-page-title"><i class="fas fa-wallet"></i> Gestión Financiera</h1>
        <p class="bento-page-subtitle">Sistema completo de facturación y control financiero</p>
    </div>

    <div class="bento-container">
        <div class="bento-nav-links">
            <a href="views/admin/dashboard.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <a href="logout.php" class="bento-btn bento-btn-outline">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>

        <?php if (!empty($previewCons) && is_array($previewCons) && $previewCons['status'] === 'ok'): ?>
            <div class="bento-card">
                <h3 class="bento-card-title"><i class="fas fa-list"></i> Desglose de consumos (vista previa)</h3>
                <p>Periodo: <?php echo htmlspecialchars($previewCons['from'] . ' — ' . $previewCons['to']); ?></p>
                <div class="bento-table-container">
                    <table class="bento-table">
                        <thead><tr><th>Servicio</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach ($previewCons['items'] as $it): $qty = floatval($it['qty']); $unit = floatval($it['unit_price']); $sub = round($qty * $unit,2); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($it['description']); ?></td>
                                <td><?php echo number_format($qty, 3); ?></td>
                                <td>$<?php echo number_format($unit, 2); ?></td>
                                <td>$<?php echo number_format($sub, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="text-align:right;margin-top:8px"><strong>Total estimado: $<?php echo number_format($previewCons['total'],2); ?></strong></div>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="bento-alert bento-alert-info">
                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($message); ?>
                <?php if (!empty($createdRef)): ?>
                    <div style="margin-top:8px">
                        <a class="bento-btn bento-btn-primary" href="api/invoice_pdf.php?ref=<?php echo urlencode($createdRef); ?>" target="_blank">
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </a>
                        <a class="bento-btn bento-btn-outline" href="pay_invoice.php?ref=<?php echo urlencode($createdRef); ?>" target="_blank">
                            <i class="fas fa-qrcode"></i> Ir a Pago / QR
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($createdRef)): ?>
            <script>
                // Descargar automáticamente el PDF de la factura creada
                (function(){
                    const url = 'api/invoice_pdf.php?ref=' + encodeURIComponent('<?php echo $createdRef; ?>');
                    // abrir en nueva ventana para forzar descarga
                    window.open(url, '_blank');
                })();
            </script>
        <?php endif; ?>

        <div class="bento-card">
            <h2 class="bento-card-title"><i class="fas fa-plus-circle"></i> Crear Nueva Factura</h2>
            <p class="bento-card-description">Genera una nueva factura para un residente del edificio</p>
            
            <form method="post" class="bento-form">
                <div class="bento-form-row">
                    <div class="bento-form-group">
                        <label for="resident_id" class="bento-form-label">
                            <i class="fas fa-user"></i> Residente
                        </label>
                        <?php
                            // Cargar lista de inquilinos con alquiler activo
                            try {
                                $tstmt = $pdo->query("SELECT i.id, u.name AS nombre, a.numero_departamento FROM inquilinos i LEFT JOIN users u ON i.user_id = u.id LEFT JOIN alquileres a ON a.inquilino_id = i.id AND a.estado = 'activo' WHERE a.id IS NOT NULL ORDER BY u.name ASC");
                                $tenants = $tstmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (Exception $e) {
                                $tenants = [];
                            }
                        ?>
                        <select id="resident_id" name="resident_id" class="bento-form-input">
                            <option value="">-- Seleccione un residente --</option>
                            <?php foreach ($tenants as $t): ?>
                                <option value="<?php echo intval($t['id']); ?>"><?php echo htmlspecialchars(($t['nombre'] ?: 'Residente #' . $t['id']) . ' — Dept: ' . ($t['numero_departamento'] ?? '-')); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="bento-form-help">Elija un residente o marque "Generar para todos" para crear facturas de mantenimiento para todos los inquilinos activos.</small>
                    </div>

                    <div class="bento-form-group">
                        <label class="bento-form-label"><input type="checkbox" name="generate_for_all" value="1"> Generar facturas de mantenimiento para TODOS los inquilinos activos</label>
                    </div>

                    <div class="bento-form-group">
                        <label for="user_name" class="bento-form-label">
                            <i class="fas fa-id-badge"></i> Nombre del Inquilino
                        </label>
                        <input type="text" id="user_name" name="user_name" class="bento-form-input" 
                               placeholder="Nombre completo del inquilino (opcional)" title="Nombre del usuario para incluir en la factura">
                    </div>

                    <div class="bento-form-group">
                        <label for="amount" class="bento-form-label">
                            <i class="fas fa-dollar-sign"></i> Monto
                        </label>
                        <div class="bento-input-group">
                            <span class="bento-input-prefix">$</span>
                            <input type="number" id="amount" name="amount" step="0.01" min="0" 
                                   class="bento-form-input" placeholder="0.00" 
                                   title="Monto total de la factura">
                        </div>
                    </div>

                    <div class="bento-form-row" style="gap:12px">
                        <div class="bento-form-group">
                            <label class="bento-form-label"><i class="fas fa-bolt"></i> Monto mensual Luz</label>
                            <input type="number" step="0.01" name="luz_amount" class="bento-form-input" value="0">
                        </div>
                        <div class="bento-form-group">
                            <label class="bento-form-label"><i class="fas fa-tint"></i> Monto mensual Agua</label>
                            <input type="number" step="0.01" name="agua_amount" class="bento-form-input" value="0">
                        </div>
                        <div class="bento-form-group">
                            <label class="bento-form-label"><i class="fas fa-fire"></i> Monto mensual Gas</label>
                            <input type="number" step="0.01" name="gas_amount" class="bento-form-input" value="0">
                        </div>
                    </div>
                </div>

                <div class="bento-form-group">
                    <label for="due_date" class="bento-form-label">
                        <i class="fas fa-calendar"></i> Fecha de Vencimiento
                    </label>
                    <input type="date" id="due_date" name="due_date" class="bento-form-input" 
                           value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" 
                           min="<?php echo date('Y-m-d'); ?>" required
                           title="Fecha límite para el pago de la factura">
                    <small class="bento-form-help">La factura vencerá 30 días a partir de hoy por defecto</small>
                </div>

                <div class="bento-form-group">
                    <label for="service_type" class="bento-form-label">
                        <i class="fas fa-tags"></i> Tipo de Pago
                    </label>
                    <!-- Solo mostrar Mantenimiento según petición del usuario -->
                    <select id="service_type" name="service_type" class="bento-form-input">
                        <option value="mantenimiento">Mantenimiento</option>
                    </select>
                    <small class="bento-form-help">Las facturas generadas aquí serán del tipo <strong>mantenimiento</strong> y se calcularán automáticamente a partir de los consumos del cliente.</small>
                </div>

                <div class="bento-form-group">
                    <label for="invoice_month" class="bento-form-label">
                        <i class="fas fa-calendar-alt"></i> Mes correspondiente
                    </label>
                    <input type="month" id="invoice_month" name="invoice_month" class="bento-form-input" value="<?php echo date('Y-m'); ?>">
                    <small class="bento-form-help">Mes al que corresponde la factura (opcional)</small>
                </div>

                <!-- Items removed: form uses amount + service_type as before -->

                <div class="bento-form-group">
                    <label for="payment_method" class="bento-form-label">
                        <i class="fas fa-credit-card"></i> Método de Pago Preferido
                    </label>
                    <select id="payment_method" name="payment_method" class="bento-form-input">
                        <option value="qr">QR (Tigo/Wallet)</option>
                        <option value="card">Tarjeta</option>
                        <option value="transfer">Transferencia</option>
                    </select>
                    <small class="bento-form-help">Método sugerido para el pago de esta factura</small>
                </div>

                <input type="hidden" name="create_invoice" value="1">

                <div class="bento-form-actions">
                    <button type="button" class="bento-btn bento-btn-ghost" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                    <button type="submit" name="calc_consumption" value="1" class="bento-btn bento-btn-outline">
                        <i class="fas fa-calculator"></i> Calcular Consumos
                    </button>
                    <button type="submit" class="bento-btn bento-btn-primary">
                        <i class="fas fa-save"></i> Crear Factura
                    </button>
                </div>
            </form>
        </div>

        <div class="bento-card">
            <h2 class="bento-card-title"><i class="fas fa-exclamation-triangle"></i> Morosidad</h2>
            <?php if (empty($overdues)): ?>
                <div class="bento-empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>¡Excelente!</h3>
                    <p>No hay facturas vencidas en este momento.</p>
                </div>
            <?php else: ?>
                <div class="bento-table-container">
                    <table class="bento-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Referencia</th>
                                <th><i class="fas fa-user"></i> Residente</th>
                                <th><i class="fas fa-dollar-sign"></i> Monto</th>
                                <th><i class="fas fa-calendar-times"></i> Vencimiento</th>
                                <th><i class="fas fa-info-circle"></i> Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($overdues as $o): ?>
                            <tr>
                                <td class="bento-table-code"><?php echo htmlspecialchars($o['reference']); ?></td>
                                <td><?php echo htmlspecialchars($o['resident_id']); ?></td>
                                <td class="bento-price-negative">$<?php echo number_format($o['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($o['due_date']); ?></td>
                                <td><span class="status-badge status-expired"><?php echo htmlspecialchars($o['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="bento-card">
            <h2 class="bento-card-title"><i class="fas fa-history"></i> Historial y Pagos por Cliente</h2>
            <p class="bento-card-description">Consulta historial de facturas y pagos de un residente, registra pagos manuales y exporta el historial.</p>

            <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:12px">
                <div>
                    <label><i class="fas fa-user"></i> Inquilino</label>
                    <?php
                        // Cargar lista de inquilinos con alquiler activo para historial
                        try {
                            $htstmt = $pdo->query("SELECT i.id, u.name AS nombre, a.numero_departamento FROM inquilinos i LEFT JOIN users u ON i.user_id = u.id LEFT JOIN alquileres a ON a.inquilino_id = i.id AND a.estado = 'activo' WHERE a.id IS NOT NULL ORDER BY u.name ASC");
                            $histTenants = $htstmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            $histTenants = [];
                        }
                        $selectedHistResident = intval($_GET['hist_resident_id'] ?? 0);
                    ?>
                    <select name="hist_resident_id" class="bento-form-input">
                        <option value="">-- Seleccione un inquilino --</option>
                        <?php foreach ($histTenants as $ht): ?>
                            <option value="<?php echo intval($ht['id']); ?>" <?php echo ($selectedHistResident == intval($ht['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(($ht['nombre'] ?: 'Residente #' . $ht['id']) . ' — Dept: ' . ($ht['numero_departamento'] ?? '-')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label>Desde</label><input type="date" name="hist_from" class="bento-form-input" value="<?php echo htmlspecialchars($_GET['hist_from'] ?? ''); ?>"></div>
                <div><label>Hasta</label><input type="date" name="hist_to" class="bento-form-input" value="<?php echo htmlspecialchars($_GET['hist_to'] ?? ''); ?>"></div>
                <div><label>Tipo</label>
                    <select name="hist_type" class="bento-form-input">
                        <option value="">Todos</option>
                        <option value="alquiler">Alquiler</option>
                        <option value="electricidad">Electricidad</option>
                        <option value="agua">Agua</option>
                        <option value="gas">Gas</option>
                        <option value="mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                <div style="display:flex;gap:6px;align-items:center;margin-top:20px">
                    <button class="bento-btn bento-btn-primary" type="submit">Buscar</button>
                    <a class="bento-btn bento-btn-outline" href="?">Limpiar</a>
                </div>
            </form>

            <?php
                $histResident = intval($_GET['hist_resident_id'] ?? 0);
                if ($histResident > 0) {
                    $histFrom = $_GET['hist_from'] ?? null;
                    $histTo = $_GET['hist_to'] ?? null;
                    $histType = $_GET['hist_type'] ?? null;
                    $history = getInvoiceHistoryByResident($histResident, $histFrom, $histTo, $histType);
                    $paymentsHist = getPaymentsByResident($histResident);
                    
                    // calcular saldo pendiente y estadísticas
                    $saldo = 0.0;
                    $totalFacturado = 0.0;
                    $totalPagado = 0.0;
                    $facturasPagadas = 0;
                    $facturasDeudas = 0;
                    
                    foreach ($history as $h) {
                        $totalFacturado += (float)$h['amount'];
                        $totalPagado += (float)$h['paid'];
                        $owed = max(0, (float)$h['amount'] - (float)$h['paid']);
                        $saldo += $owed;
                        if ($h['status'] === 'paid') $facturasPagadas++;
                        if ($owed > 0) $facturasDeudas++;
                    }
                    
                    // Obtener información del inquilino
                    try {
                        $inqStmt = $pdo->prepare("SELECT u.name, a.numero_departamento FROM inquilinos i LEFT JOIN users u ON i.user_id = u.id LEFT JOIN alquileres a ON a.inquilino_id = i.id AND a.estado = 'activo' WHERE i.id = :id LIMIT 1");
                        $inqStmt->execute([':id'=>$histResident]);
                        $inqInfo = $inqStmt->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $inqInfo = null;
                    }
            ?>

            <div style="background:#f8f9fa;padding:16px;border-radius:8px;margin-bottom:16px">
                <?php if ($inqInfo): ?>
                    <h3 style="margin:0 0 12px 0"><i class="fas fa-user"></i> <?php echo htmlspecialchars($inqInfo['name'] ?? 'Inquilino #' . $histResident); ?> - Dept: <?php echo htmlspecialchars($inqInfo['numero_departamento'] ?? '-'); ?></h3>
                <?php endif; ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
                    <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #28a745">
                        <div style="font-size:24px;font-weight:bold;color:#28a745">$<?php echo number_format($totalPagado,2); ?></div>
                        <div style="color:#666;font-size:14px">Total Pagado</div>
                    </div>
                    <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid <?php echo $saldo > 0 ? '#dc3545' : '#28a745'; ?>">
                        <div style="font-size:24px;font-weight:bold;color:<?php echo $saldo > 0 ? '#dc3545' : '#28a745'; ?>">$<?php echo number_format($saldo,2); ?></div>
                        <div style="color:#666;font-size:14px">Saldo Pendiente</div>
                    </div>
                    <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #007bff">
                        <div style="font-size:24px;font-weight:bold;color:#007bff"><?php echo $facturasPagadas; ?> / <?php echo count($history); ?></div>
                        <div style="color:#666;font-size:14px">Facturas Pagadas</div>
                    </div>
                    <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #ffc107">
                        <div style="font-size:24px;font-weight:bold;color:#ffc107"><?php echo $facturasDeudas; ?></div>
                        <div style="color:#666;font-size:14px">Facturas con Deuda</div>
                    </div>
                </div>
            </div>
            <div style="margin-bottom:8px">
                <a class="bento-btn bento-btn-primary" href="create_invoice.php?resident_id=<?php echo $histResident; ?>">Crear nueva factura</a>
                <a class="bento-btn bento-btn-outline" href="api/export_history.php?resident_id=<?php echo $histResident; ?>&from=<?php echo urlencode($histFrom); ?>&to=<?php echo urlencode($histTo); ?>&type=<?php echo urlencode($histType); ?>">Exportar CSV</a>
            </div>

            <h4>Facturas</h4>
            <div class="bento-table-container"><table class="bento-table"><thead><tr><th>Ref</th><th>Fecha Emisión</th><th>Tipo</th><th>Monto</th><th>Pagado</th><th>Deuda</th><th>Estado</th><th>Fecha Pago</th><th>Acciones</th></tr></thead><tbody>
                <?php if (empty($history)): ?><tr><td colspan="9">No hay facturas</td></tr><?php else: ?>
                    <?php foreach ($history as $hf): 
                        $m = []; 
                        if (!empty($hf['meta'])) { 
                            if (is_string($hf['meta'])) $m = json_decode($hf['meta'],true)?:[]; 
                            else $m = $hf['meta']; 
                        }
                        $deuda = max(0, (float)$hf['amount'] - (float)$hf['paid']);
                        
                        // Obtener fecha del último pago si existe
                        $lastPaymentDate = null;
                        if ((float)$hf['paid'] > 0) {
                            try {
                                $payStmt = $pdo->prepare("SELECT created_at FROM payments WHERE invoice_id = :id ORDER BY created_at DESC LIMIT 1");
                                $payStmt->execute([':id'=>$hf['id']]);
                                $lastPay = $payStmt->fetch(PDO::FETCH_ASSOC);
                                if ($lastPay) $lastPaymentDate = $lastPay['created_at'];
                            } catch (Exception $e) {}
                        }
                        
                        // Determinar color de fila según estado
                        $rowStyle = '';
                        if ($hf['status'] === 'paid') {
                            $rowStyle = 'background:#e8f5e9';
                        } elseif ($deuda > 0) {
                            $rowStyle = 'background:#ffebee';
                        }
                    ?>
                        <tr style="<?php echo $rowStyle; ?>">
                            <td class="bento-table-code"><?php echo htmlspecialchars($hf['reference']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($hf['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($m['type'] ?? ''); if (!empty($m['month'])) echo '<br><small>' . htmlspecialchars($m['month']) . '</small>'; ?></td>
                            <td><strong>$<?php echo number_format($hf['amount'],2); ?></strong></td>
                            <td style="color:#28a745"><strong>$<?php echo number_format($hf['paid'],2); ?></strong></td>
                            <td style="color:<?php echo $deuda > 0 ? '#dc3545' : '#28a745'; ?>;font-weight:bold">
                                <?php if ($deuda > 0): ?>
                                    <i class="fas fa-exclamation-triangle"></i> $<?php echo number_format($deuda,2); ?>
                                <?php else: ?>
                                    <i class="fas fa-check-circle"></i> $0.00
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hf['status'] === 'paid'): ?>
                                    <span class="status-badge" style="background:#28a745;color:white;padding:4px 8px;border-radius:4px">
                                        <i class="fas fa-check"></i> Pagada
                                    </span>
                                <?php elseif ($deuda > 0): ?>
                                    <span class="status-badge" style="background:#dc3545;color:white;padding:4px 8px;border-radius:4px">
                                        <i class="fas fa-clock"></i> Pendiente
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge" style="background:#ffc107;color:#000;padding:4px 8px;border-radius:4px">
                                        <?php echo htmlspecialchars($hf['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($lastPaymentDate): ?>
                                    <i class="fas fa-calendar-check" style="color:#28a745"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($lastPaymentDate)); ?>
                                <?php else: ?>
                                    <small style="color:#999">Sin pagos</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="bento-btn bento-btn-small" href="api/invoice_pdf.php?id=<?php echo intval($hf['id']); ?>" target="_blank" title="Ver/Descargar Factura PDF con QR">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <?php if ($deuda > 0): ?>
                                    <button class="bento-btn bento-btn-small bento-btn-primary" onclick="document.getElementById('pay_invoice_id').value=<?php echo intval($hf['id']); ?>;document.getElementById('pay_amount').value=<?php echo $deuda; ?>;document.getElementById('manualPay').scrollIntoView();" title="Registrar Pago">
                                        <i class="fas fa-dollar-sign"></i> Pagar
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($hf['items'])): ?>
                            <tr style="<?php echo $rowStyle; ?>"><td colspan="9"><strong>Desglose:</strong>
                                <ul style="margin:4px 0;padding-left:20px">
                                <?php foreach ($hf['items'] as $it): ?><li><?php echo htmlspecialchars($it['description']); ?> — <?php echo number_format($it['qty'],2); ?> x $<?php echo number_format($it['unit_price'],2); ?> = $<?php echo number_format(round($it['qty']*$it['unit_price'],2),2); ?></li><?php endforeach; ?>
                                </ul>
                            </td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody></table></div>

            <h4 id="manualPay">Registrar pago manual</h4>
            <form method="post" style="max-width:420px">
                <input type="hidden" name="manual_payment" value="1">
                <div class="bento-form-group"><label>Invoice ID</label><input id="pay_invoice_id" name="invoice_id" class="bento-form-input" required readonly></div>
                <div class="bento-form-group"><label>Monto</label><input id="pay_amount" type="number" step="0.01" name="pay_amount" class="bento-form-input" required></div>
                <div class="bento-form-group"><label>Método</label><select name="pay_method" class="bento-form-input"><option value="manual">Manual</option><option value="transfer">Transferencia</option><option value="card">Tarjeta</option><option value="efectivo">Efectivo</option></select></div>
                <div class="bento-form-actions"><button class="bento-btn bento-btn-primary" type="submit">Registrar Pago</button></div>
            </form>

            <h4>Historial de pagos</h4>
            <div class="bento-table-container"><table class="bento-table"><thead><tr><th>Fecha</th><th>Factura</th><th>Monto</th><th>Método</th><th>Meta</th></tr></thead><tbody>
                <?php if (empty($paymentsHist)): ?><tr><td colspan="5">Sin pagos registrados</td></tr><?php else: ?>
                    <?php foreach ($paymentsHist as $ph): ?><tr><td><?php echo htmlspecialchars($ph['created_at']); ?></td><td><?php echo htmlspecialchars($ph['invoice_reference']); ?></td><td>$<?php echo number_format($ph['amount'],2); ?></td><td><?php echo htmlspecialchars($ph['method']); ?></td><td><?php echo htmlspecialchars($ph['metadata']); ?></td></tr><?php endforeach; ?>
                <?php endif; ?>
            </tbody></table></div>

            <?php } // end histResident > 0 ?>

        </div>

        <div class="bento-card">
            <h2 class="bento-card-title"><i class="fas fa-chart-line"></i> Reportes Financieros</h2>
            <div class="bento-report-period">
                <i class="fas fa-calendar-alt"></i>
                <span>Periodo: <?php echo $report['from']; ?> — <?php echo $report['to']; ?></span>
            </div>

            <div class="bento-stats-grid">
                <div class="bento-stat-card">
                    <div class="bento-stat-number bento-income">$<?php echo number_format($report['income'], 2); ?></div>
                    <div class="bento-stat-label">Ingresos</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number bento-expenses">$<?php echo number_format($report['expenses'], 2); ?></div>
                    <div class="bento-stat-label">Gastos</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number bento-net <?php echo $report['net'] >= 0 ? 'bento-positive' : 'bento-negative'; ?>">
                        $<?php echo number_format($report['net'], 2); ?>
                    </div>
                    <div class="bento-stat-label">Balance Neto</div>
                </div>
            </div>

            <hr style="margin:18px 0">
            <div class="bento-stats-grid">
                <div class="bento-stat-card">
                    <div class="bento-stat-number">$<?php echo number_format($totalInvoiced,2); ?></div>
                    <div class="bento-stat-label">Total Facturado</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number">$<?php echo number_format($totalCollected,2); ?></div>
                    <div class="bento-stat-label">Total Cobrado</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number bento-negative">$<?php echo number_format($totalOutstanding,2); ?></div>
                    <div class="bento-stat-label">Total Pendiente</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number"><?php echo $invoicesThisMonth; ?></div>
                    <div class="bento-stat-label">Facturas este mes</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number bento-negative"><?php echo $overdueCount; ?></div>
                    <div class="bento-stat-label">Facturas Vencidas</div>
                </div>
            </div>

            <h3 style="margin-top:18px">Últimas facturas</h3>
            <div class="bento-table-container">
                <table class="bento-table">
                    <thead>
                        <tr>
                            <th>Referencia</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Pagado</th>
                            <th>Vence</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentInvoices)): ?>
                        <tr><td colspan="7">No hay facturas recientes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentInvoices as $ri): ?>
                            <?php
                                $meta = [];
                                if (!empty($ri['meta'])) {
                                    if (is_string($ri['meta'])) $meta = json_decode($ri['meta'], true) ?: [];
                                    elseif (is_array($ri['meta'])) $meta = $ri['meta'];
                                }
                                $client = $meta['user_name'] ?? ($ri['resident_id'] ? 'Residente #' . $ri['resident_id'] : '');
                                $paid = number_format((float)$ri['paid'],2);
                            ?>
                            <tr>
                                <td class="bento-table-code"><?php echo htmlspecialchars($ri['reference']); ?></td>
                                <td><?php echo htmlspecialchars($client); ?></td>
                                <td>$<?php echo number_format($ri['amount'],2); ?></td>
                                <td>$<?php echo $paid; ?></td>
                                <td><?php echo htmlspecialchars($ri['due_date']); ?></td>
                                <td><?php echo htmlspecialchars($ri['status']); ?></td>
                                <td>
                                    <a class="bento-btn bento-btn-small" href="api/invoice_pdf.php?ref=<?php echo urlencode($ri['reference']); ?>" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    <a class="bento-btn bento-btn-small bento-btn-outline" href="pay_invoice.php?ref=<?php echo urlencode($ri['reference']); ?>" target="_blank"><i class="fas fa-qrcode"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bento-card">
            <h2 class="bento-card-title"><i class="fas fa-users"></i> Empleados / Planilla</h2>
            <p class="bento-card-description">Gestiona pagos a empleados y genera la planilla por periodo</p>

            <?php
                // Cargar empleados del proyecto
                try {
                    $empStmt = $pdo->query("SELECT e.*, u.name, u.email FROM empleados e LEFT JOIN users u ON e.user_id = u.id WHERE e.estado = 'activo' ORDER BY u.name ASC");
                    $empleadosProyecto = $empStmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $ex) {
                    $empleadosProyecto = [];
                }
                
                // Seleccionar empleado para ver historial
                $selectedEmpId = intval($_GET['emp_id'] ?? 0);
                $empHistorial = [];
                if ($selectedEmpId > 0) {
                    try {
                        // Obtener historial de pagos de planilla para este empleado
                        $hempStmt = $pdo->prepare("SELECT * FROM payroll WHERE staff_id = :sid ORDER BY period DESC, created_at DESC");
                        $hempStmt->execute([':sid'=>$selectedEmpId]);
                        $empHistorial = $hempStmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $ex) {
                        $empHistorial = [];
                    }
                }
            ?>

            <form method="get" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <div>
                    <label><i class="fas fa-user"></i> Ver historial de empleado</label>
                    <select name="emp_id" class="bento-form-input">
                        <option value="">-- Seleccione un empleado --</option>
                        <?php foreach ($empleadosProyecto as $emp): ?>
                            <option value="<?php echo intval($emp['id']); ?>" <?php echo ($selectedEmpId == intval($emp['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(($emp['name'] ?? 'Empleado #' . $emp['id']) . ' - ' . $emp['cargo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-top:20px">
                    <button class="bento-btn bento-btn-primary" type="submit">Ver Historial</button>
                    <a class="bento-btn bento-btn-outline" href="?">Limpiar</a>
                </div>
            </form>

            <?php if ($selectedEmpId > 0 && !empty($empleadosProyecto)): ?>
                <?php 
                    $empInfo = array_values(array_filter($empleadosProyecto, function($e) use ($selectedEmpId) { return intval($e['id']) === $selectedEmpId; }))[0] ?? null;
                    if ($empInfo):
                        // Calcular estadísticas
                        $totalPagado = 0;
                        $totalPendiente = 0;
                        $pagosPagados = 0;
                        $pagosPendientes = 0;
                        foreach ($empHistorial as $eh) {
                            if ($eh['paid']) {
                                $totalPagado += (float)$eh['net'];
                                $pagosPagados++;
                            } else {
                                $totalPendiente += (float)$eh['net'];
                                $pagosPendientes++;
                            }
                        }
                        
                        // Calcular años de servicio y bono
                        $fechaContratacion = $empInfo['fecha_contratacion'] ?? null;
                        $añosServicio = 0;
                        $bonoAnual = 0;
                        if ($fechaContratacion) {
                            $fechaContr = new DateTime($fechaContratacion);
                            $hoy = new DateTime();
                            $diff = $hoy->diff($fechaContr);
                            $añosServicio = $diff->y;
                            // Bono: 5% del salario por cada año de servicio
                            $bonoAnual = round(($empInfo['salario'] ?? 0) * 0.05 * $añosServicio, 2);
                        }
                ?>
                    <div style="background:#f8f9fa;padding:16px;border-radius:8px;margin-bottom:16px">
                        <h3 style="margin:0 0 12px 0">
                            <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($empInfo['name'] ?? 'Empleado'); ?>
                        </h3>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;margin-bottom:12px">
                            <div><strong>Cargo:</strong> <?php echo htmlspecialchars($empInfo['cargo']); ?></div>
                            <div><strong>DNI:</strong> <?php echo htmlspecialchars($empInfo['dni']); ?></div>
                            <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($empInfo['telefono'] ?? '-'); ?></div>
                            <div><strong>Salario:</strong> $<?php echo number_format($empInfo['salario'] ?? 0, 2); ?></div>
                            <div><strong>Fecha Contratación:</strong> <?php echo date('d/m/Y', strtotime($empInfo['fecha_contratacion'])); ?></div>
                            <div><strong>Años de Servicio:</strong> <?php echo $añosServicio; ?> año(s)</div>
                        </div>
                        <div style="background:#fff3cd;padding:12px;border-radius:6px;border-left:4px solid #ffc107;margin-bottom:12px">
                            <strong><i class="fas fa-gift"></i> Bono por Antigüedad:</strong> $<?php echo number_format($bonoAnual, 2); ?> anual
                            <small style="display:block;color:#666;margin-top:4px">(5% del salario base × <?php echo $añosServicio; ?> años)</small>
                        </div>
                        
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
                            <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #28a745">
                                <div style="font-size:24px;font-weight:bold;color:#28a745">$<?php echo number_format($totalPagado,2); ?></div>
                                <div style="color:#666;font-size:14px">Total Pagado</div>
                            </div>
                            <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #dc3545">
                                <div style="font-size:24px;font-weight:bold;color:#dc3545">$<?php echo number_format($totalPendiente,2); ?></div>
                                <div style="color:#666;font-size:14px">Total Pendiente</div>
                            </div>
                            <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #007bff">
                                <div style="font-size:24px;font-weight:bold;color:#007bff"><?php echo $pagosPagados; ?> / <?php echo count($empHistorial); ?></div>
                                <div style="color:#666;font-size:14px">Pagos Realizados</div>
                            </div>
                            <div style="background:white;padding:12px;border-radius:6px;border-left:4px solid #ffc107">
                                <div style="font-size:24px;font-weight:bold;color:#ffc107"><?php echo $pagosPendientes; ?></div>
                                <div style="color:#666;font-size:14px">Pagos Pendientes</div>
                            </div>
                        </div>
                    </div>

                    <h4>Historial de Pagos</h4>
                    <div class="bento-table-container">
                        <table class="bento-table">
                            <thead>
                                <tr><th>Periodo</th><th>Fecha Registro</th><th>Gross</th><th>Deducciones</th><th>Net</th><th>Estado</th><th>Fecha Pago</th><th>Acción</th></tr>
                            </thead>
                            <tbody>
                            <?php if (empty($empHistorial)): ?>
                                <tr><td colspan="8" style="text-align:center;color:#999">No hay registros de pagos</td></tr>
                            <?php else: ?>
                                <?php foreach ($empHistorial as $eh): 
                                    $rowBg = $eh['paid'] ? 'background:#e8f5e9' : 'background:#ffebee';
                                ?>
                                    <tr style="<?php echo $rowBg; ?>">
                                        <td><strong><?php echo htmlspecialchars($eh['period']); ?></strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($eh['created_at'])); ?></td>
                                        <td>$<?php echo number_format($eh['gross'],2); ?></td>
                                        <td style="color:#dc3545">$<?php echo number_format($eh['deductions'],2); ?></td>
                                        <td><strong>$<?php echo number_format($eh['net'],2); ?></strong></td>
                                        <td>
                                            <?php if ($eh['paid']): ?>
                                                <span class="status-badge" style="background:#28a745;color:white;padding:4px 8px;border-radius:4px">
                                                    <i class="fas fa-check"></i> Pagado
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge" style="background:#dc3545;color:white;padding:4px 8px;border-radius:4px">
                                                    <i class="fas fa-clock"></i> Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($eh['paid']): ?>
                                                <i class="fas fa-calendar-check" style="color:#28a745"></i> 
                                                <?php echo date('d/m/Y', strtotime($eh['updated_at'] ?? $eh['created_at'])); ?>
                                            <?php else: ?>
                                                <small style="color:#999">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$eh['paid']): ?>
                                                <form method="post" style="display:inline;margin-right:4px">
                                                    <input type="hidden" name="payroll_id" value="<?php echo intval($eh['id']); ?>">
                                                    <input type="hidden" name="mark_payroll_paid" value="1">
                                                    <button class="bento-btn bento-btn-small bento-btn-primary" type="submit" style="margin-right:4px">
                                                        <i class="fas fa-money-bill-wave"></i> Marcar Pagado
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a class="bento-btn bento-btn-small" href="api/payroll_pdf.php?id=<?php echo intval($eh['id']); ?>" target="_blank" 
                                               title="Ver/Descargar comprobante PDF con QR">
                                                <i class="fas fa-file-pdf"></i> Ver Comprobante
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <hr style="margin:24px 0">
            
            <div id="create_payroll_form" style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:20px">
                <h4><i class="fas fa-money-bill-wave"></i> Generar Pago de Nómina para Empleado</h4>
                <form method="post">
                    <input type="hidden" name="create_payroll_entry" value="1">
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-bottom:16px">
                        <div class="bento-form-group">
                            <label>Empleado</label>
                            <select id="payroll_emp_id" name="pay_staff_id" class="bento-form-input" required onchange="updatePayrollSalary(this)">
                                <option value="">-- Seleccionar empleado --</option>
                                <?php foreach ($empleadosProyecto as $emp): ?>
                                    <option value="<?php echo intval($emp['id']); ?>" 
                                            data-salary="<?php echo $emp['salario'] ?? 0; ?>"
                                            data-name="<?php echo htmlspecialchars($emp['name'] ?? ''); ?>">
                                        <?php echo htmlspecialchars(($emp['name'] ?? 'Empleado #' . $emp['id']) . ' - ' . $emp['cargo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="payroll_emp_name" value="">
                        </div>
                        
                        <div class="bento-form-group">
                            <label>Período (Mes/Año)</label>
                            <input type="month" name="pay_period" value="<?php echo date('Y-m'); ?>" class="bento-form-input" required>
                        </div>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:16px">
                        <div class="bento-form-group">
                            <label>Salario Bruto</label>
                            <input type="number" step="0.01" id="payroll_gross" name="pay_gross" class="bento-form-input" required placeholder="0.00">
                        </div>
                        
                        <div class="bento-form-group">
                            <label>Deducciones (Impuestos, etc.)</label>
                            <input type="number" step="0.01" name="pay_deductions" class="bento-form-input" value="0.00" placeholder="0.00">
                        </div>
                        
                        <div class="bento-form-group">
                            <label>Días Trabajados</label>
                            <input type="number" name="pay_days_worked" class="bento-form-input" value="30" min="1" max="31">
                        </div>
                        
                        <div class="bento-form-group">
                            <label><strong>Neto a Pagar</strong></label>
                            <div style="background:#e7f5ff;padding:12px;border-radius:6px;border-left:4px solid #007bff">
                                <span style="font-size:20px;font-weight:bold;color:#007bff" id="payroll_net_display">$0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bento-form-actions">
                        <button class="bento-btn bento-btn-success" type="submit">
                            <i class="fas fa-check-circle"></i> Generar Pago de Nómina
                        </button>
                        <button class="bento-btn bento-btn-outline" type="button" onclick="this.form.reset();document.getElementById('payroll_net_display').textContent='$0.00';">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </form>
            </div>
            
            <script>
            function updatePayrollSalary(select) {
                const option = select.options[select.selectedIndex];
                const salary = option.getAttribute('data-salary') || 0;
                const grossInput = document.getElementById('payroll_gross');
                grossInput.value = salary;
                calculateNet();
            }
            
            function calculateNet() {
                const gross = parseFloat(document.getElementById('payroll_gross').value) || 0;
                const deductions = parseFloat(document.querySelector('input[name="pay_deductions"]').value) || 0;
                const net = gross - deductions;
                document.getElementById('payroll_net_display').textContent = '$' + net.toFixed(2);
            }
            
            // Actualizar neto cuando cambian los valores
            document.getElementById('payroll_gross').addEventListener('input', calculateNet);
            document.querySelector('input[name="pay_deductions"]').addEventListener('input', calculateNet);
            </script>

            <hr style="margin:24px 0">
            
            <form method="post" style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
                <label for="payroll_period">Periodo (YYYY-MM)</label>
                <input type="month" id="payroll_period" name="payroll_period" value="<?php echo htmlspecialchars($payrollPeriod); ?>">
                <a class="bento-btn bento-btn-primary" href="api/payroll_pdf.php?period=<?php echo urlencode($payrollPeriod); ?>" target="_blank"><i class="fas fa-file-pdf"></i> Generar PDF Planilla</a>
            </form>

            <h4>Lista de Empleados del Edificio</h4>
            <div class="bento-table-container">
                <table class="bento-table">
                    <thead>
                        <tr><th>Nombre</th><th>Cargo</th><th>Salario</th><th>Antigüedad</th><th>Bono Anual</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($empleadosProyecto)): ?>
                        <tr><td colspan="7" style="text-align:center;color:#999">No hay empleados registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($empleadosProyecto as $emp): 
                            $fechaContr = $emp['fecha_contratacion'] ? new DateTime($emp['fecha_contratacion']) : null;
                            $añosServ = 0;
                            if ($fechaContr) {
                                $diff = (new DateTime())->diff($fechaContr);
                                $añosServ = $diff->y;
                            }
                            $bonoEmp = round(($emp['salario'] ?? 0) * 0.05 * $añosServ, 2);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($emp['name'] ?? 'Empleado #' . $emp['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($emp['cargo']); ?></td>
                                <td>$<?php echo number_format($emp['salario'] ?? 0, 2); ?></td>
                                <td><?php echo $añosServ; ?> año(s)</td>
                                <td style="color:#ffc107;font-weight:bold">$<?php echo number_format($bonoEmp, 2); ?></td>
                                <td>
                                    <span class="status-badge" style="background:<?php echo $emp['estado'] === 'activo' ? '#28a745' : '#999'; ?>;color:white;padding:4px 8px;border-radius:4px">
                                        <?php echo ucfirst($emp['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="bento-btn bento-btn-small bento-btn-primary" href="?emp_id=<?php echo intval($emp['id']); ?>" style="margin-right:4px">
                                        <i class="fas fa-history"></i> Ver Historial
                                    </a>
                                    <button class="bento-btn bento-btn-small bento-btn-success" 
                                            onclick="document.getElementById('payroll_emp_id').value=<?php echo intval($emp['id']); ?>;
                                                     document.getElementById('payroll_emp_name').value='<?php echo htmlspecialchars($emp['name'] ?? ''); ?>';
                                                     document.getElementById('payroll_gross').value=<?php echo $emp['salario'] ?? 0; ?>;
                                                     document.getElementById('create_payroll_form').scrollIntoView({behavior:'smooth'});">
                                        <i class="fas fa-dollar-sign"></i> Generar Pago
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr style="margin:24px 0">

            <div style="display:flex;gap:16px;margin-bottom:12px;flex-wrap:wrap">
                <div style="flex:1">
                    <h4>Añadir empleado</h4>
                    <form method="post">
                        <input type="hidden" name="create_staff" value="1">
                        <div class="bento-form-group"><label>Nombre</label><input class="bento-form-input" name="staff_name" required></div>
                        <div class="bento-form-group"><label>Tipo</label>
                            <select name="staff_type" class="bento-form-input"><option value="empleado">Empleado</option><option value="contratista">Contratista</option><option value="administrativo">Administrativo</option></select>
                        </div>
                        <div class="bento-form-group"><label>Área</label><input class="bento-form-input" name="staff_area"></div>
                        <div class="bento-form-row">
                            <div class="bento-form-group"><label>Días / mes</label><input class="bento-form-input" name="staff_days" type="number" value="30"></div>
                            <div class="bento-form-group"><label>Tarifa mensual</label><input class="bento-form-input" name="staff_monthly" type="number" step="0.01" value="0"></div>
                            <div class="bento-form-group"><label>Tarifa diaria</label><input class="bento-form-input" name="staff_daily" type="number" step="0.01" value="0"></div>
                        </div>
                        <div class="bento-form-actions"><button class="bento-btn bento-btn-primary" type="submit">Crear Empleado</button></div>
                    </form>
                </div>

                <div style="flex:1">
                    <h4>Añadir entrada de planilla</h4>
                    <form method="post">
                        <input type="hidden" name="create_payroll_entry" value="1">
                        <div class="bento-form-group"><label>Empleado</label>
                            <select name="pay_staff_id" class="bento-form-input" required>
                                <option value="">-- seleccionar --</option>
                                <?php foreach ($staffList as $s): ?>
                                    <option value="<?php echo intval($s['id']); ?>"><?php echo htmlspecialchars($s['name'] . ' (' . $s['type'] . ' - ' . ($s['area'] ?? '-') . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="bento-form-group"><label>Periodo</label><input type="month" name="pay_period" value="<?php echo htmlspecialchars($payrollPeriod); ?>" class="bento-form-input"></div>
                        <div class="bento-form-row">
                            <div class="bento-form-group"><label>Gross</label><input type="number" step="0.01" name="pay_gross" class="bento-form-input" required></div>
                            <div class="bento-form-group"><label>Deducciones</label><input type="number" step="0.01" name="pay_deductions" class="bento-form-input" value="0"></div>
                            <div class="bento-form-group"><label>Días trabajados</label><input type="number" name="pay_days_worked" class="bento-form-input" value="0"></div>
                        </div>
                        <div class="bento-form-actions"><button class="bento-btn bento-btn-primary" type="submit">Agregar entrada</button></div>
                    </form>
                </div>
            </div>

            <?php if (empty($payrollRows)): ?>
                <div class="bento-empty-state"><i class="fas fa-info-circle"></i><h3>Sin entradas</h3><p>No hay registros de planilla para el periodo seleccionado.</p></div>
            <?php else: ?>
                <div class="bento-table-container">
                    <table class="bento-table">
                        <thead>
                            <tr><th>Empleado</th><th>Gross</th><th>Deducciones</th><th>Net</th><th>Estado</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($payrollRows as $pr): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pr['staff_name'] ?? $pr['staff_id']); ?></td>
                                <td>$<?php echo number_format($pr['gross'],2); ?></td>
                                <td>$<?php echo number_format($pr['deductions'],2); ?></td>
                                <td>$<?php echo number_format($pr['net'],2); ?></td>
                                <td><?php echo $pr['paid'] ? '<span class="status-badge" style="background:#dff0d8;color:#2e7d32">Pagada</span>' : '<span class="status-badge" style="background:#ffe7e7;color:#9a1c1c">Pendiente</span>'; ?></td>
                                <td>
                                    <?php if (!$pr['paid']): ?>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="payroll_id" value="<?php echo intval($pr['id']); ?>">
                                            <input type="hidden" name="mark_payroll_paid" value="1">
                                            <button class="bento-btn bento-btn-small bento-btn-primary" type="submit"><i class="fas fa-money-bill-wave"></i> Marcar Pagada</button>
                                        </form>
                                    <?php else: ?>
                                        <a class="bento-btn bento-btn-small" href="api/payroll_pdf.php?period=<?php echo urlencode($pr['period']); ?>" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Función para limpiar el formulario
        function resetForm() {
            document.getElementById('resident_id').value = '';
            document.getElementById('amount').value = '';
            document.getElementById('due_date').value = '<?php echo date('Y-m-d', strtotime('+30 days')); ?>';
            
            // Quitar foco de los campos
            document.getElementById('resident_id').blur();
            document.getElementById('amount').blur();
            document.getElementById('due_date').blur();
        }

        // Auto-formateo del campo de monto
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value;
            // Asegurar que no sea negativo
            if (value < 0) {
                e.target.value = 0;
            }
            // Limitar a 2 decimales
            if (value.includes('.')) {
                const parts = value.split('.');
                if (parts[1].length > 2) {
                    e.target.value = parts[0] + '.' + parts[1].substring(0, 2);
                }
            }
        });

        // Validación visual en tiempo real
        document.querySelectorAll('.bento-form-input').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else {
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                }
            });
        });
    </script>
</body>
</html>
