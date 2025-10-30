<?php
require_once '../../includes/functions.php';
require_once '../../includes/db.php';
require_once '../../includes/financial.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php'); exit;
}

$months = ['2025-01'=>'Enero 2025','2025-02'=>'Febrero 2025','2025-03'=>'Marzo 2025','2025-04'=>'Abril 2025','2025-05'=>'Mayo 2025'];

?>
<!doctype html>
<html><head><meta charset='utf-8'><title>Planilla - Admin</title>
<link rel="stylesheet" href="../../assets/css/bento-style.css">
</head><body>
<div class="bento-container">
  <h1>Planilla Mensual</h1>
  <p>Seleccione un periodo para ver y descargar la planilla</p>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <?php foreach($months as $k=>$label): ?>
      <div style="border:1px solid #ddd;padding:12px;border-radius:6px;min-width:180px">
        <h4><?php echo $label; ?></h4>
        <a class="bento-btn bento-btn-primary" href="../../api/payroll_pdf.php?period=<?php echo urlencode($k); ?>" target="_blank">Descargar PDF</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body></html>
