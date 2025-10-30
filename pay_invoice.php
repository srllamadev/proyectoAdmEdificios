<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/financial.php';

if (empty($_GET['ref'])) {
    echo 'Referencia de factura requerida'; exit;
}
$ref = $_GET['ref'];
$inv = getInvoice($ref);
if (!$inv) { echo 'Factura no encontrada'; exit; }

$payUrl = 'api/gateway_tigo.php';
$cardGateway = 'api/gateway_card.php';

?>
<!doctype html>
<html><head><meta charset='utf-8'><title>Pagar Factura <?php echo htmlspecialchars($inv['reference']); ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<div style="max-width:800px;margin:30px auto;font-family:Arial,Helvetica,sans-serif">
  <h2>Factura <?php echo htmlspecialchars($inv['reference']); ?></h2>
  <p><strong>Monto:</strong> $<?php echo number_format($inv['amount'],2); ?></p>
  <p><strong>Vence:</strong> <?php echo htmlspecialchars($inv['due_date']); ?></p>
  <p><a href="api/invoice_pdf.php?ref=<?php echo urlencode($inv['reference']); ?>" class="btn">Descargar PDF</a></p>

  <h3>Pagar con Tigo Money (mock)</h3>
  <form id="tigoForm">
    <label>Teléfono (Tigo): <input type="text" name="phone" required placeholder="+502XXXXXXXX"></label><br><br>
    <input type="hidden" name="invoice_ref" value="<?php echo htmlspecialchars($inv['reference']); ?>">
    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($inv['amount']); ?>">
    <button type="submit">Iniciar Pago</button>
  </form>

  <h3>Pagar con Tarjeta (mock)</h3>
  <form id="cardForm">
    <label>Tarjeta (número): <input type="text" name="card_number" required placeholder="4111111111111111"></label><br><br>
    <label>Nombre en la tarjeta: <input type="text" name="card_name" required></label><br><br>
    <input type="hidden" name="invoice_ref" value="<?php echo htmlspecialchars($inv['reference']); ?>">
    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($inv['amount']); ?>">
    <button type="submit">Pagar con Tarjeta</button>
  </form>

  <div id="cardResult" style="margin-top:16px"></div>

  <div id="result" style="margin-top:16px"></div>
</div>

<script>
document.getElementById('tigoForm').addEventListener('submit', function(e){
  e.preventDefault();
  const data = new FormData(this);
  fetch('<?php echo $payUrl; ?>', {method:'POST', body: JSON.stringify(Object.fromEntries(data.entries())), headers:{'Content-Type':'application/json'}})
    .then(r=>r.json()).then(j=>{
      document.getElementById('result').innerText = JSON.stringify(j);
      if (j.tx_ref) {
        const link = document.createElement('a');
        link.href = '<?php echo dirname($_SERVER['PHP_SELF']); ?>/api/gateway_tigo.php?tx=' + encodeURIComponent(j.tx_ref);
        link.innerText = 'Simular confirmación de pago (solo demo)';
        document.getElementById('result').appendChild(document.createElement('br'));
        document.getElementById('result').appendChild(link);
      }
    }).catch(err=>{ document.getElementById('result').innerText = err; });
});

document.getElementById('cardForm').addEventListener('submit', function(e){
  e.preventDefault();
  const data = new FormData(this);
  fetch('<?php echo $cardGateway; ?>', {method:'POST', body: JSON.stringify(Object.fromEntries(data.entries())), headers:{'Content-Type':'application/json'}})
    .then(r=>r.json()).then(j=>{
      const el = document.getElementById('cardResult');
      el.innerText = JSON.stringify(j);
      if (j.tx_ref && j.confirm_url) {
        const link = document.createElement('a');
        link.href = j.confirm_url;
        link.innerText = 'Simular confirmación de pago con tarjeta';
        el.appendChild(document.createElement('br'));
        el.appendChild(link);
        // Generar QR que apunte a la confirm_url
        const qr = document.createElement('img');
        qr.src = 'https://chart.googleapis.com/chart?cht=qr&chs=180x180&chl=' + encodeURIComponent(j.confirm_url);
        el.appendChild(document.createElement('br'));
        el.appendChild(qr);
      }
    }).catch(err=>{ document.getElementById('cardResult').innerText = err; });
});
</script>
</body></html>
