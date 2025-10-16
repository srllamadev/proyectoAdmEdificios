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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body{font-family:Segoe UI,Arial;background:#0a0a0a;color:#fff;padding:20px}
        .card{background:rgba(255,255,255,0.03);padding:20px;border-radius:8px;margin-bottom:20px}
        input,button{padding:8px;margin:4px 0}
        table{width:100%;border-collapse:collapse}
        th,td{padding:8px;border-bottom:1px solid rgba(255,255,255,0.05)}
    </style>
</head>
<body>
    <h1>Gestión Financiera</h1>
    <?php if ($message): ?>
        <div style="padding:10px;background:#0f0f0f;border-left:4px solid #00ffff;margin-bottom:10px"><?=htmlspecialchars($message)?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Crear Factura</h2>
        <form method="post">
            <label>Resident ID: <input name="resident_id" required></label><br>
            <label>Monto: <input name="amount" type="number" step="0.01" required></label><br>
            <label>Vencimiento: <input name="due_date" type="date"></label><br>
            <input type="hidden" name="create_invoice" value="1">
            <button type="submit">Crear Factura</button>
        </form>
    </div>

    <div class="card">
        <h2>Morosidad</h2>
        <?php if (empty($overdues)): ?>
            <p>No hay facturas vencidas.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Ref</th><th>Resident</th><th>Monto</th><th>Vence</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($overdues as $o): ?>
                    <tr>
                        <td><?=htmlspecialchars($o['reference'])?></td>
                        <td><?=htmlspecialchars($o['resident_id'])?></td>
                        <td><?=htmlspecialchars($o['amount'])?></td>
                        <td><?=htmlspecialchars($o['due_date'])?></td>
                        <td><?=htmlspecialchars($o['status'])?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Reportes (ejemplo)</h2>
        <p>Periodo: <?=$report['from']?> — <?=$report['to']?></p>
        <p>Ingresos: <?=$report['income']?></p>
        <p>Gastos: <?=$report['expenses']?></p>
        <p>Neto: <?=$report['net']?></p>
    </div>

</body>
</html>
