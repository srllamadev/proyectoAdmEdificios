<?php
require_once '../../includes/functions.php';
require_once '../../includes/header.php';
checkUserRole('admin');
require_once __DIR__ . '/../../config/database.php';

// Leer filtros (GET)
$filter_dep = isset($_GET['departamento']) && $_GET['departamento'] !== '' ? $_GET['departamento'] : null;
$filter_recurso = isset($_GET['recurso']) && $_GET['recurso'] !== '' ? $_GET['recurso'] : null;
$filter_start = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : null;
$filter_end = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : null;

$db = new Database();
$conn = $db->getConnection();

// Helpers para construir WHERE según filtros
$whereParts = [];
$params = [];
if ($filter_dep) {
    $whereParts[] = 'd.nombre = :dep';
    $params[':dep'] = $filter_dep;
}
if ($filter_recurso) {
    $whereParts[] = 's.tipo = :recurso';
    $params[':recurso'] = $filter_recurso;
}
if ($filter_start) {
    $whereParts[] = 'l.recibido_en >= :start';
    $params[':start'] = $filter_start . ' 00:00:00';
}
if ($filter_end) {
    $whereParts[] = 'l.recibido_en <= :end';
    $params[':end'] = $filter_end . ' 23:59:59';
}
$whereSQL = '';
if (count($whereParts) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereParts);
}

// Variables por defecto
$totalFacturado = 0.0;
$consumoPromedio = ['agua' => 0, 'luz' => 0, 'gas' => 0];
$porcentajeMorosidad = 0;
$totalDepartamentosActivos = 0;
$tablaRows = [];
$chart_monthly = [];
$chart_pie_status = ['Pagado'=>0,'Pendiente'=>0,'Vencido'=>0];
$line_trend = [];

try {
    // Total facturado mes actual - alinear con el dashboard principal
    $monthStart = date('Y-m-01 00:00:00');
    $monthEnd = date('Y-m-t 23:59:59');
    // Preferir tabla 'pagos' (monto pagado), luego 'finanzas', luego 'invoices' (status = paid)
    if (tableExists($conn, 'pagos')) {
        try {
            $stmt = $conn->prepare("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado_pago IN ('pagado','paid') AND fecha BETWEEN :s AND :e");
            $stmt->execute([':s'=>$monthStart,':e'=>$monthEnd]);
            $totalFacturado = (float)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }
    }
    if (empty($totalFacturado) && tableExists($conn, 'finanzas')) {
        try {
            $stmt = $conn->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM finanzas WHERE fecha BETWEEN :s AND :e");
            $stmt->execute([':s'=>$monthStart,':e'=>$monthEnd]);
            $totalFacturado = (float)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }
    }
    if (empty($totalFacturado) && tableExists($conn, 'invoices')) {
        try {
            $stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE status IN ('paid','Pagado') AND due_date BETWEEN :s AND :e");
            $stmt->execute([':s'=>$monthStart,':e'=>$monthEnd]);
            $totalFacturado = (float)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }
    }

    // Consumo promedio por recurso desde lecturas/consumos
    if (tableExists($conn, 'lecturas')) {
        $stmt = $conn->prepare("SELECT s.tipo AS recurso, AVG(l.valor) AS promedio FROM lecturas l JOIN sensores s ON s.id = l.sensor_id GROUP BY s.tipo");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $consumoPromedio[$r['recurso']] = round((float)$r['promedio'],2);
        }
    } elseif (tableExists($conn, 'consumos')) {
        $stmt = $conn->prepare("SELECT recurso, AVG(lectura) as promedio FROM consumos GROUP BY recurso");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $consumoPromedio[$r['recurso']] = round((float)$r['promedio'],2);
        }
    }

    // Morosidad: porcentaje de departamentos con pagos atrasados (usar invoices o pagos)
    $totalDeps = 0;
    if (tableExists($conn,'departamentos')) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM departamentos");
        $stmt->execute();
        $totalDeps = (int)$stmt->fetchColumn();
    } elseif (tableExists($conn,'inquilinos')) {
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT departamento) FROM inquilinos WHERE estado = 'activo'");
        $stmt->execute();
        $totalDeps = (int)$stmt->fetchColumn();
    }
    $deptsMorosos = 0;
    if (tableExists($conn,'invoices')) {
        try {
            // Consistente con dashboard principal: facturas vencidas y no pagadas
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT departamento_id) FROM invoices WHERE status <> 'paid' AND due_date IS NOT NULL AND due_date < CURDATE()");
            $stmt->execute();
            $deptsMorosos = (int)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }
    } elseif (tableExists($conn,'pagos')) {
        try {
            // Usar pagos para estimar morosidad: departamentos con último pago hace más de 30 días o sin pagos pendientes
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT departamento) FROM pagos WHERE estado_pago <> 'pagado' AND fecha < DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
            $stmt->execute();
            $deptsMorosos = (int)$stmt->fetchColumn();
        } catch (Exception $e) { /* ignore */ }
    }
    if ($totalDeps > 0) {
        $porcentajeMorosidad = round(($deptsMorosos / $totalDeps) * 100,2);
    }
    $totalDepartamentosActivos = $totalDeps;

    // Tabla detallada: combinar lecturas y estado de pago (si posible)
    $selectSQL = "SELECT d.nombre AS departamento, s.tipo AS recurso, l.valor AS consumo, l.recibido_en AS fecha, NULL AS estado_pago FROM lecturas l JOIN departamentos d ON d.id = l.departamento_id JOIN sensores s ON s.id = l.sensor_id $whereSQL ORDER BY l.recibido_en DESC LIMIT 100";
    if (tableExists($conn,'lecturas')) {
        $stmt = $conn->prepare($selectSQL);
        $stmt->execute($params);
        $tablaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif (tableExists($conn,'consumos')) {
        $sql2 = "SELECT departamento, recurso, lectura AS consumo, fecha, NULL AS estado_pago FROM consumos $whereSQL ORDER BY fecha DESC LIMIT 100";
        $stmt = $conn->prepare($sql2);
        $stmt->execute($params);
        $tablaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Gráficos: consumo mensual por recurso (últimos 6 meses)
    $months = [];
    for ($i=5;$i>=0;$i--) {
        $m = date('Y-m', strtotime("-{$i} months"));
        $months[] = $m;
        $chart_monthly[$m] = ['agua'=>0,'luz'=>0,'gas'=>0];
    }
    if (tableExists($conn,'lecturas')) {
        $stmt = $conn->prepare("SELECT DATE_FORMAT(l.recibido_en,'%Y-%m') AS ym, s.tipo AS recurso, SUM(l.valor) as total FROM lecturas l JOIN sensores s ON s.id = l.sensor_id WHERE l.recibido_en >= :since GROUP BY ym, recurso");
        $since = date('Y-m-01', strtotime('-5 months')) . ' 00:00:00';
        $stmt->execute([':since'=>$since]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            if (isset($chart_monthly[$r['ym']])) {
                $chart_monthly[$r['ym']][$r['recurso']] = (float)$r['total'];
            }
        }
    }

    // Pie chart: pagos status
    if (tableExists($conn,'invoices')) {
        $stmt = $conn->prepare("SELECT status, COUNT(*) as cnt FROM invoices GROUP BY status");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $k = ucfirst($r['status']);
            if (!isset($chart_pie_status[$k])) $chart_pie_status[$k] = 0;
            $chart_pie_status[$k] += (int)$r['cnt'];
        }
    } elseif (tableExists($conn,'pagos')) {
        $stmt = $conn->prepare("SELECT estado_pago, COUNT(*) as cnt FROM pagos GROUP BY estado_pago");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $k = ucfirst($r['estado_pago']);
            if (!isset($chart_pie_status[$k])) $chart_pie_status[$k] = 0;
            $chart_pie_status[$k] += (int)$r['cnt'];
        }
    }

    // Line trend: total consumo últimos 6 meses
    foreach ($months as $m) {
        $sum = array_sum($chart_monthly[$m]);
        $line_trend[] = ['month'=>$m,'total'=>$sum];
    }

} catch (PDOException $e) {
    // manejar silenciosamente mostrando 0s y mensaje opcional
    $errorMsg = $e->getMessage();
}

// Helper para comprobar existencia de tabla
function tableExists($conn, $table) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE :t");
        $stmt->execute([':t'=>$table]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

// Datos JSON para JS
$js_chart_monthly = json_encode($chart_monthly);
$js_pie_status = json_encode($chart_pie_status);
$js_line_trend = json_encode($line_trend);
$js_table = json_encode($tablaRows);
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-chart-pie"></i> Dashboard Administrativo — Gestión de Recursos y Consumo</h1>
    <p class="bento-page-subtitle">Métricas clave de finanzas, consumos y morosidad</p>
</div>

<?php if (isset($errorMsg)): ?>
    <?php showAlert("Error cargando métricas: " . htmlspecialchars($errorMsg), 'warning'); ?>
<?php endif; ?>

<div class="bento-stats-grid">
    <div class="bento-stat-card">
        <div class="bento-stat-number">💰 <?php echo number_format($totalFacturado,2); ?></div>
        <div class="bento-stat-label">Total facturado (mes actual)</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number">🔌 Agua: <?php echo $consumoPromedio['agua']; ?> — Luz: <?php echo $consumoPromedio['luz']; ?> — Gas: <?php echo $consumoPromedio['gas']; ?></div>
        <div class="bento-stat-label">Consumo promedio (por recurso)</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number">🧾 <?php echo $porcentajeMorosidad; ?>%</div>
        <div class="bento-stat-label">Porcentaje de morosidad</div>
    </div>
    <div class="bento-stat-card">
        <div class="bento-stat-number">🏠 <?php echo $totalDepartamentosActivos; ?></div>
        <div class="bento-stat-label">Departamentos activos</div>
    </div>
</div>

<div class="bento-card">
    <form method="get" class="filters-form" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
        <label>Departamento:
            <select name="departamento">
                <option value="">Todos</option>
                <?php
                // listar departamentos: mostrar primero los por defecto y luego los de la BD (sin duplicados)
                $defaultDeps = ['101','201','301','401','501'];
                $allDeps = [];
                // Empezar con los por defecto
                foreach ($defaultDeps as $dd) {
                    $allDeps[$dd] = $dd;
                }
                // Añadir los de la base de datos si existen (evitar duplicados)
                if (tableExists($conn,'departamentos')) {
                    try {
                        $st = $conn->query('SELECT nombre FROM departamentos ORDER BY nombre');
                        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($rows as $d) {
                            $name = trim((string)$d['nombre']);
                            if ($name === '') continue;
                            $allDeps[$name] = $name;
                        }
                    } catch (Exception $e) {
                        // ignorar errores y usar solo los por defecto
                    }
                }
                // Imprimir opciones en orden: por defecto primero, luego el resto alfabéticamente
                // Ya tenemos por defecto en $defaultDeps; imprimimos esos y luego los adicionales
                $printed = [];
                foreach ($defaultDeps as $depVal) {
                    $sel = ($filter_dep && $filter_dep == $depVal) ? 'selected' : '';
                    echo "<option value=\"".htmlspecialchars($depVal)."\" $sel>".htmlspecialchars($depVal)."</option>";
                    $printed[$depVal] = true;
                }
                // imprimir resto ordenados
                $extra = array_diff(array_values($allDeps), $defaultDeps);
                sort($extra, SORT_NATURAL);
                foreach ($extra as $depVal) {
                    if (isset($printed[$depVal])) continue;
                    $sel = ($filter_dep && $filter_dep == $depVal) ? 'selected' : '';
                    echo "<option value=\"".htmlspecialchars($depVal)."\" $sel>".htmlspecialchars($depVal)."</option>";
                }
                ?>
            </select>
        </label>
        <label>Recurso:
            <select name="recurso">
                <option value="">Todos</option>
                <option value="agua" <?php echo ($filter_recurso=='agua')? 'selected':''; ?>>Agua</option>
                <option value="luz" <?php echo ($filter_recurso=='luz')? 'selected':''; ?>>Luz</option>
                <option value="gas" <?php echo ($filter_recurso=='gas')? 'selected':''; ?>>Gas</option>
            </select>
        </label>
        <label>Desde: <input type="date" name="start_date" value="<?php echo htmlspecialchars($filter_start); ?>"></label>
        <label>Hasta: <input type="date" name="end_date" value="<?php echo htmlspecialchars($filter_end); ?>"></label>
        <button type="submit" class="bento-btn bento-btn-primary">Aplicar filtros</button>
        <a href="../../tools/export_dashboard.php?type=csv&departamento=<?php echo urlencode($filter_dep); ?>&recurso=<?php echo urlencode($filter_recurso); ?>&start_date=<?php echo urlencode($filter_start); ?>&end_date=<?php echo urlencode($filter_end); ?>" class="bento-btn bento-btn-secondary">📊 Exportar Excel (CSV)</a>
        <a href="../../tools/export_dashboard.php?type=pdf&departamento=<?php echo urlencode($filter_dep); ?>&recurso=<?php echo urlencode($filter_recurso); ?>&start_date=<?php echo urlencode($filter_start); ?>&end_date=<?php echo urlencode($filter_end); ?>" class="bento-btn bento-btn-secondary">📄 Exportar PDF</a>
    </form>
</div>

<div class="bento-grid">
    <div class="bento-card" style="flex:2;">
        <h3>Consumo mensual por recurso</h3>
        <canvas id="chartMonthly"></canvas>
    </div>
    <div class="bento-card" style="flex:1;">
        <h3>Proporción de pagos</h3>
        <canvas id="chartPie"></canvas>
        <h3>Tendencia (últimos 6 meses)</h3>
        <canvas id="chartLine"></canvas>
    </div>
</div>

<div class="bento-card">
    <h3>Detalle de consumos</h3>
    <div style="overflow:auto;">
        <table class="bento-table" id="detalleTabla" style="width:100%;border-collapse:collapse;border:1px solid #eee;">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Recurso</th>
                    <th>Consumo</th>
                    <th>Estado Pago</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tablaRows as $tr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tr['departamento']); ?></td>
                        <td><?php echo htmlspecialchars($tr['recurso']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($tr['consumo'],2)); ?></td>
                        <td><?php echo htmlspecialchars($tr['estado_pago'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($tr['fecha']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bento-card" style="width:300px;position:fixed;right:20px;top:120px;padding:1rem;">
    <h4>Alertas recientes</h4>
    <ul>
        <?php
        // Alertas: departamentos con consumo mensual por encima del promedio (simple ejemplo)
        try {
            if (tableExists($conn,'lecturas')) {
                $stmt = $conn->prepare("SELECT d.nombre AS departamento, s.tipo AS recurso, SUM(l.valor) as total_mes FROM lecturas l JOIN departamentos d ON d.id = l.departamento_id JOIN sensores s ON s.id = l.sensor_id WHERE l.recibido_en >= :since GROUP BY d.nombre, s.tipo HAVING total_mes > (SELECT AVG(valor) FROM lecturas WHERE sensor_id = l.sensor_id)");
                $since = date('Y-m-01 00:00:00');
                $stmt->execute([':since'=>$since]);
                $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($alerts as $a) {
                    echo "<li>Dep " . htmlspecialchars($a['departamento']) . " (".htmlspecialchars($a['recurso']).") — " . number_format($a['total_mes'],2) . "</li>";
                }
            }
            // Morosos >30 dias
            if (tableExists($conn,'invoices')) {
                $stmt = $conn->prepare("SELECT departamento_id, COUNT(*) as cnt FROM invoices WHERE status <> 'paid' AND DATEDIFF(CURDATE(), due_date) > 30 GROUP BY departamento_id ORDER BY cnt DESC LIMIT 5");
                $stmt->execute();
                $mor = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($mor as $m) {
                    // intentar resolver nombre del departamento
                    $st = $conn->prepare('SELECT nombre FROM departamentos WHERE id = :id');
                    $st->execute([':id'=>$m['departamento_id']]);
                    $dn = $st->fetchColumn();
                    echo "<li style=\"color:#d9534f;\">Moroso: " . htmlspecialchars($dn ?: $m['departamento_id']) . " ({$m['cnt']})</li>";
                }
            }
        } catch (Exception $e) {
            // ignorar
        }
        ?>
    </ul>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartMonthlyData = <?php echo $js_chart_monthly; ?>;
    const chartPieData = <?php echo $js_pie_status; ?>;
    const lineTrend = <?php echo $js_line_trend; ?>;

    // Preparar datos para chartMonthly
    const labels = Object.keys(chartMonthlyData);
    const agua = labels.map(l => chartMonthlyData[l].agua || 0);
    const luz = labels.map(l => chartMonthlyData[l].luz || 0);
    const gas = labels.map(l => chartMonthlyData[l].gas || 0);

    const ctxM = document.getElementById('chartMonthly').getContext('2d');
    new Chart(ctxM, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Agua', data: agua, backgroundColor: '#4fc3f7' },
                { label: 'Luz', data: luz, backgroundColor: '#f48fb1' },
                { label: 'Gas', data: gas, backgroundColor: '#9575cd' }
            ]
        },
        options: { responsive:true }
    });

    // Pie
    const ctxP = document.getElementById('chartPie').getContext('2d');
    new Chart(ctxP, {
        type: 'pie',
        data: {
            labels: Object.keys(chartPieData),
            datasets: [{ data: Object.values(chartPieData), backgroundColor: ['#66bb6a','#ffca28','#ef5350'] }]
        }
    });

    // Line trend
    const ctxL = document.getElementById('chartLine').getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: lineTrend.map(x=>x.month),
            datasets: [{ label: 'Consumo total', data: lineTrend.map(x=>x.total), borderColor:'#f48fb1', fill:false }]
        }
    });

    // Simple ordenable de tabla (hacer click en encabezado)
    document.querySelectorAll('#detalleTabla th').forEach((th, idx)=>{
        th.style.cursor = 'pointer';
        th.addEventListener('click', ()=>{
            const tbody = document.querySelector('#detalleTabla tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const asc = th.dataset.asc !== 'true';
            rows.sort((a,b)=>{
                const aText = a.children[idx].innerText.trim();
                const bText = b.children[idx].innerText.trim();
                return aText.localeCompare(bText, undefined, {numeric:true}) * (asc?1:-1);
            });
            th.dataset.asc = asc;
            tbody.innerHTML = '';
            rows.forEach(r=>tbody.appendChild(r));
        });
    });

</script>

<?php require_once '../../includes/footer.php'; ?>
