<?php
require_once 'includes/db.php';
require_once 'includes/financial.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_invoice'])) {
    $data = [
        'resident_id' => $_POST['resident_id'] ?: null,
        'items' => [],
        'amount' => floatval($_POST['amount'] ?? 0),
        'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'))
    ];
    $res = createInvoice($data);
    if ($res['status'] === 'ok') {
        $message = "Factura creada: {$res['reference']}";
        $createdRef = $res['reference'];
        // conservar invoice id/ref para mostrar enlaces
    } else {
        $message = "Error: {$res['message']}";
    }
}

// Obtener morosos y reportes
$overdues = getOverdues();
$report = reportIncomeExpenses(date('Y-m-01'), date('Y-m-t'));

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

        <div class="bento-card">
            <h2 class="bento-card-title"><i class="fas fa-plus-circle"></i> Crear Nueva Factura</h2>
            <p class="bento-card-description">Genera una nueva factura para un residente del edificio</p>
            
            <form method="post" class="bento-form">
                <div class="bento-form-row">
                    <div class="bento-form-group">
                        <label for="resident_id" class="bento-form-label">
                            <i class="fas fa-user"></i> ID del Residente
                        </label>
                        <input type="text" id="resident_id" name="resident_id" class="bento-form-input" 
                               required placeholder="Ingrese el ID del residente" 
                               title="ID único del residente en el sistema">
                    </div>

                    <div class="bento-form-group">
                        <label for="amount" class="bento-form-label">
                            <i class="fas fa-dollar-sign"></i> Monto
                        </label>
                        <div class="bento-input-group">
                            <span class="bento-input-prefix">$</span>
                            <input type="number" id="amount" name="amount" step="0.01" min="0" 
                                   class="bento-form-input" required placeholder="0.00" 
                                   title="Monto total de la factura">
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

                <input type="hidden" name="create_invoice" value="1">

                <div class="bento-form-actions">
                    <button type="button" class="bento-btn bento-btn-ghost" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Limpiar
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
