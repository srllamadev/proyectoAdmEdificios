<?php
require_once '../../includes/functions.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Gestión de Recursos y Consumo';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// --- Almacenamiento temporal en sesión para lecturas del módulo ---
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Inicializar contenedor en sesión
if (!isset($_SESSION['consumo_readings']) || !is_array($_SESSION['consumo_readings'])) {
    $_SESSION['consumo_readings'] = [];
}

// Valores umbral (constantes del módulo)
$THRESHOLDS = [
    'agua' => 400.0,
    'luz'  => 80.0,
    'gas'  => 40.0
];

// Manejo de POST para guardar lectura simulada o manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_lectura') {
    $departamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
    $recurso = isset($_POST['recurso']) ? strtolower(trim($_POST['recurso'])) : '';
    $lectura = isset($_POST['lectura']) ? floatval($_POST['lectura']) : null;
    $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : date('Y-m-d');

    // Validaciones mínimas
    $allowed_depts = ['101','201','301','401','501'];
    $allowed_recs = ['agua','luz','gas'];

    if (!in_array($departamento, $allowed_depts) || !in_array($recurso, $allowed_recs) || $lectura === null || $lectura < 0) {
        $new_error = 'Datos inválidos. Verifique departamento, recurso y lectura.';
    } else {
        // Determinar estado (normal / anómalo / posible fuga)
        $estado = 'Normal';
        $alertType = null;
        $threshold = $THRESHOLDS[$recurso];
        // Si supera umbral -> Anómalo
        if ($lectura > $threshold) {
            $estado = 'Anómalo';
            $alertType = 'anomalo';
            // Si excede 20% adicional consideramos posible fuga (regla inferida)
            if ($lectura > $threshold * 1.2) {
                $estado = 'Posible Fuga';
                $alertType = 'fuga';
            }
        }

        // Guardar en sesión (apilar al inicio)
        $entry = [
            'departamento' => $departamento,
            'recurso' => $recurso,
            'lectura' => $lectura,
            'fecha' => $fecha,
            'estado' => $estado,
            'alertType' => $alertType
        ];

        array_unshift($_SESSION['consumo_readings'], $entry);
        // Limitar a 200 registros para no crecer indefinidamente
        if (count($_SESSION['consumo_readings']) > 200) {
            $_SESSION['consumo_readings'] = array_slice($_SESSION['consumo_readings'], 0, 200);
        }

        // Preparar mensaje para mostrar en la página
        if ($alertType === 'fuga') {
            $new_alert = '🚨 Posible fuga detectada';
        } elseif ($alertType === 'anomalo') {
            $new_alert = '⚠️ Consumo anómalo detectado';
        } else {
            $new_success = 'Lectura guardada correctamente.';
        }
    }

    // Evitar reenvío de formulario al recargar
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// Variables
$stats = [];
$lecturas_recientes = [];
$alertas_anomalas = [];
$consumo_por_depto = [];
$comparativa_mensual = [];

try {
    // Estadísticas generales
    $stats['total_lecturas'] = $conn->query("SELECT COUNT(*) FROM lecturas_consumo")->fetchColumn();
    $stats['lecturas_mes'] = $conn->query("SELECT COUNT(*) FROM lecturas_consumo WHERE MONTH(fecha_lectura) = MONTH(CURDATE())")->fetchColumn();
    $stats['consumo_total'] = $conn->query("SELECT SUM(consumo) FROM lecturas_consumo")->fetchColumn();
    $stats['costo_total'] = $conn->query("SELECT COALESCE(SUM(costo_total), 0) FROM lecturas_consumo")->fetchColumn();
    
    // Promedios por servicio
    $stmt = $conn->query("
        SELECT tipo_servicio, 
               AVG(consumo) as promedio,
               MAX(consumo) as maximo,
               MIN(consumo) as minimo
        FROM lecturas_consumo 
        GROUP BY tipo_servicio
    ");
    $stats['promedios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lecturas recientes (últimas 20)
    $stmt = $conn->query("
        SELECT l.*, d.nombre as departamento_nombre
        FROM lecturas_consumo l
        JOIN departamentos d ON d.id = l.departamento_id
        ORDER BY l.fecha_lectura DESC, l.id DESC
        LIMIT 20
    ");
    $lecturas_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Detectar consumos anómalos (>2x el promedio)
    $stmt = $conn->query("
        SELECT l.*, d.nombre as departamento_nombre,
               (SELECT AVG(consumo) FROM lecturas_consumo WHERE tipo_servicio = l.tipo_servicio) as promedio_servicio
        FROM lecturas_consumo l
        JOIN departamentos d ON d.id = l.departamento_id
        WHERE l.consumo > (SELECT AVG(consumo) * 2 FROM lecturas_consumo WHERE tipo_servicio = l.tipo_servicio)
        ORDER BY l.fecha_lectura DESC
        LIMIT 10
    ");
    $alertas_anomalas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Consumo por departamento
    $stmt = $conn->query("
        SELECT d.nombre as departamento,
               SUM(CASE WHEN l.tipo_servicio = 'agua' THEN l.consumo ELSE 0 END) as agua,
               SUM(CASE WHEN l.tipo_servicio = 'luz' THEN l.consumo ELSE 0 END) as luz,
               SUM(CASE WHEN l.tipo_servicio = 'gas' THEN l.consumo ELSE 0 END) as gas,
               SUM(l.costo_total) as costo_total
        FROM lecturas_consumo l
        JOIN departamentos d ON d.id = l.departamento_id
        GROUP BY d.id, d.nombre
        ORDER BY costo_total DESC
    ");
    $consumo_por_depto = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Comparativa mensual (últimos 6 meses)
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(fecha_lectura, '%Y-%m') as mes,
            DATE_FORMAT(fecha_lectura, '%b %Y') as mes_nombre,
            tipo_servicio,
            SUM(consumo) as total_consumo,
            SUM(costo_total) as total_costo
        FROM lecturas_consumo
        WHERE fecha_lectura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mes, tipo_servicio
        ORDER BY mes, tipo_servicio
    ");
    $comparativa_mensual = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<style>
:root {
    --color-agua: #00bcd4;
    --color-luz: #ffc107;
    --color-gas: #9c27b0;
    --gradient-recursos: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.dashboard-recursos {
    padding: 30px;
    max-width: 1800px;
    margin: 0 auto;
}

.header-recursos {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.8s;
}

.header-recursos h1 {
    font-size: 2.8rem;
    background: var(--gradient-recursos);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    margin-bottom: 10px;
}

.header-recursos p {
    color: #666;
    font-size: 1.1rem;
}

/* Tarjetas de estadísticas */
.stats-recursos {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card-recurso {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s;
}

.stat-card-recurso::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-recursos);
}

.stat-card-recurso:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-icon-recurso {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 15px;
    background: var(--gradient-recursos);
    color: white;
}

.stat-value-recurso {
    font-size: 2rem;
    font-weight: 800;
    color: #2c3e50;
    margin: 10px 0;
}

.stat-label-recurso {
    color: #7f8c8d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

/* Alertas */
.alertas-section {
    margin-bottom: 30px;
}

.alert-card {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(255,107,107,0.3);
    animation: pulse 2s infinite;
}

.alert-card strong {
    font-size: 1.1rem;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

/* Gráficas */
.charts-section {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 25px;
    margin-bottom: 40px;
}

.chart-box {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.chart-box.full { grid-column: span 12; }
.chart-box.half { grid-column: span 6; }
.chart-box.third { grid-column: span 4; }
.chart-box.two-third { grid-column: span 8; }

.chart-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Tabla mejorada */
.table-recursos {
    width: 100%;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.table-recursos thead {
    background: var(--gradient-recursos);
    color: white;
}

.table-recursos th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.table-recursos td {
    padding: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.table-recursos tbody tr {
    transition: all 0.3s;
}

.table-recursos tbody tr:hover {
    background: #f8f9fa;
}

.badge-servicio {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-agua {
    background: linear-gradient(135deg, #00bcd4, #00acc1);
    color: white;
}

.badge-luz {
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: white;
}

.badge-gas {
    background: linear-gradient(135deg, #9c27b0, #8e24aa);
    color: white;
}

.badge-anomalo {
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.badge-normal {
    background: linear-gradient(135deg, #4caf50, #43a047);
    color: white;
}

.badge-estado {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-pagado {
    background: linear-gradient(135deg, #4caf50, #43a047);
    color: white;
}

.badge-pendiente {
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: white;
}

.badge-vencido {
    background: linear-gradient(135deg, #f44336, #e53935);
    color: white;
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 1200px) {
    .chart-box.half, .chart-box.third, .chart-box.two-third {
        grid-column: span 12;
    }
}
</style>

<!-- Módulo: Gestión de Recursos y Consumo (interfaz principal solicitada) -->
<style>
    .gestion-module {
        background: linear-gradient(135deg, #FDECEC 0%, #FFF7F7 100%);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.06);
        margin-bottom: 25px;
        font-family: 'Poppins', 'Open Sans', sans-serif;
        color: #333;
    }
    .gestion-header {
        background: linear-gradient(135deg, #F48FB1 0%, #FFAB91 60%, #FFD7CE 100%);
        color: white;
        padding: 18px;
        border-radius: 10px;
        box-shadow: 0 6px 18px rgba(244,143,177,0.18);
        margin-bottom: 18px;
    }
    .gestion-header h2 { margin: 0 0 6px 0; font-size: 1.6rem; }
    .gestion-header p { margin: 0; opacity: 0.95; }
    .gestion-form { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
    .gestion-form .form-group { display: flex; flex-direction: column; min-width: 160px; }
    .gestion-form label { font-weight: 600; margin-bottom: 6px; }
    .gestion-input, .gestion-select, .gestion-date { padding: 10px 12px; border-radius: 8px; border: 1px solid #e6e6e6; }
    .gestion-actions { display: flex; gap: 10px; align-items: center; }
    .btn-guardar {
        background: linear-gradient(135deg, #F48FB1 0%, #9575CD 100%);
        color: white; padding: 10px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 700;
        box-shadow: 0 6px 18px rgba(149,117,205,0.18);
    }
    .btn-generar { background: #fff; border: 1px solid #ffd6e7; padding: 8px 12px; border-radius: 8px; cursor: pointer; }
    .alert-module { padding: 12px 14px; border-radius: 10px; margin-top: 12px; font-weight: 700; }
    .alert-anomalo { background: linear-gradient(135deg, #fff3cd, #ffe8a1); color: #7a4b00; }
    .alert-fuga { background: linear-gradient(135deg, #ffcccc, #ff9a9a); color: #7a0000; }
    .module-chart-card { background: white; padding: 14px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); margin-top: 14px; }
    .module-table { margin-top: 12px; width: 100%; border-collapse: collapse; }
    .module-table th, .module-table td { padding: 8px 10px; border-bottom: 1px solid #f1f1f1; }
    .estado-anomalo { background: #fff0f0; color: #a00000; font-weight: 700; padding: 6px 8px; border-radius: 6px; }
    @media (max-width: 900px) { .gestion-form { flex-direction: column; } }
</style>

<div class="gestion-module">
    <div class="gestion-header">
        <h2>💡 Gestión de Recursos y Consumo</h2>
        <p>Registro manual o simulación de lecturas de agua, luz y gas por departamento con alertas de consumo anómalo.</p>
    </div>

    <?php if (!empty($new_error)): ?>
        <div class="alert-module alert-anomalo"><?php echo htmlspecialchars($new_error); ?></div>
    <?php endif; ?>
    <?php if (!empty($new_success)): ?>
        <div class="alert-module" style="background:#e7fff4;color:#006b3c"><?php echo htmlspecialchars($new_success); ?></div>
    <?php endif; ?>
    <?php if (!empty($new_alert)): ?>
        <div class="alert-module <?php echo ($new_alert === '🚨 Posible fuga detectada') ? 'alert-fuga' : 'alert-anomalo'; ?>">
            <?php echo $new_alert; ?>
        </div>
    <?php endif; ?>

    <form id="gestionForm" method="post" class="gestion-form">
        <input type="hidden" name="accion" value="guardar_lectura" />
        <div class="form-group">
            <label for="departamento">Departamento</label>
            <select name="departamento" id="departamento" class="gestion-select">
                <option value="101">101</option>
                <option value="201">201</option>
                <option value="301">301</option>
                <option value="401">401</option>
                <option value="501">501</option>
            </select>
        </div>

        <div class="form-group">
            <label for="recurso">Recurso</label>
            <select name="recurso" id="recurso" class="gestion-select">
                <option value="agua">Agua</option>
                <option value="luz">Luz</option>
                <option value="gas">Gas</option>
            </select>
        </div>

        <div class="form-group">
            <label for="lectura">Lectura (valor numérico)</label>
            <input type="number" step="0.01" name="lectura" id="lectura" class="gestion-input" />
        </div>

        <div class="form-group">
            <label for="fecha">Fecha</label>
            <input type="date" name="fecha" id="fecha" class="gestion-date" value="<?php echo date('Y-m-d'); ?>" />
        </div>

        <div class="gestion-actions">
            <button type="button" id="btnGenerar" class="btn-generar">Generar Simulada</button>
            <button type="submit" class="btn-guardar">Guardar Lectura</button>
        </div>
    </form>

    <div class="module-chart-card">
        <h4 style="margin:0 0 8px 0;">Últimas 10 lecturas (recurso seleccionado)</h4>
        <canvas id="moduleChart" height="80"></canvas>
        <div style="overflow:auto; margin-top:10px;">
            <table class="module-table">
                <thead>
                    <tr><th>Departamento</th><th>Recurso</th><th>Lectura</th><th>Fecha</th><th>Estado</th></tr>
                </thead>
                <tbody id="moduleTableBody">
                    <!-- Filas generadas por PHP/JS -->
                    <?php
                        $sess = $_SESSION['consumo_readings'];
                        $count = 0;
                        foreach ($sess as $row) {
                            if ($count >= 100) break; // límite de impresión
                            $count++;
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="dashboard-recursos">
    <!-- Header -->
    <div class="header-recursos">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard de Recursos y Consumo</h1>
        <p>Monitoreo en tiempo real de agua, luz y gas - Control de consumos por departamento</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Alertas de Consumos Anómalos -->
    <?php if (!empty($alertas_anomalas)): ?>
        <div class="alertas-section">
            <h3 style="color: #e74c3c; margin-bottom: 15px;"><i class="fas fa-exclamation-triangle"></i> Alertas de Consumo Anómalo (<?php echo count($alertas_anomalas); ?>)</h3>
            <?php foreach (array_slice($alertas_anomalas, 0, 3) as $alerta): ?>
                <div class="alert-card">
                    <strong><i class="fas fa-bell"></i> ¡Consumo Anormal Detectado!</strong>
                    <p style="margin: 10px 0 0 0;">
                        Depto <strong><?php echo htmlspecialchars($alerta['departamento_nombre']); ?></strong> - 
                        <?php echo ucfirst($alerta['tipo_servicio']); ?>: 
                        <strong><?php echo number_format($alerta['consumo'], 2); ?></strong> unidades
                        (Promedio: <?php echo number_format($alerta['promedio_servicio'], 2); ?>)
                        - Fecha: <?php echo date('d/m/Y', strtotime($alerta['fecha_lectura'])); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas principales -->
    <div class="stats-recursos">
        <div class="stat-card-recurso">
            <div class="stat-icon-recurso"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-value-recurso"><?php echo number_format($stats['total_lecturas']); ?></div>
            <div class="stat-label-recurso">Lecturas Totales</div>
        </div>

        <div class="stat-card-recurso">
            <div class="stat-icon-recurso"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-value-recurso"><?php echo number_format($stats['lecturas_mes']); ?></div>
            <div class="stat-label-recurso">Lecturas este Mes</div>
        </div>

        <div class="stat-card-recurso">
            <div class="stat-icon-recurso"><i class="fas fa-chart-line"></i></div>
            <div class="stat-value-recurso"><?php echo number_format($stats['consumo_total'], 0); ?></div>
            <div class="stat-label-recurso">Consumo Total (unidades)</div>
        </div>

        <div class="stat-card-recurso">
            <div class="stat-icon-recurso"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-value-recurso">$<?php echo number_format($stats['costo_total'], 2); ?></div>
            <div class="stat-label-recurso">Costo Total Generado</div>
        </div>

        <?php foreach ($stats['promedios'] as $prom): ?>
            <div class="stat-card-recurso">
                <div class="stat-icon-recurso" style="background: <?php 
                    echo $prom['tipo_servicio'] == 'agua' ? 'var(--color-agua)' : 
                        ($prom['tipo_servicio'] == 'luz' ? 'var(--color-luz)' : 'var(--color-gas)'); 
                ?>;">
                    <i class="fas fa-<?php 
                        echo $prom['tipo_servicio'] == 'agua' ? 'tint' : 
                            ($prom['tipo_servicio'] == 'luz' ? 'bolt' : 'fire'); 
                    ?>"></i>
                </div>
                <div class="stat-value-recurso"><?php echo number_format($prom['promedio'], 2); ?></div>
                <div class="stat-label-recurso">Promedio <?php echo ucfirst($prom['tipo_servicio']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Gráficas -->
    <div class="charts-section">
        <!-- Gráfica de consumo por departamento -->
        <div class="chart-box two-third">
            <div class="chart-title">
                <i class="fas fa-building"></i> Consumo por Departamento
            </div>
            <canvas id="chartDepartamentos" height="100"></canvas>
        </div>

        <!-- Gráfica PIE de distribución -->
        <div class="chart-box third">
            <div class="chart-title">
                <i class="fas fa-chart-pie"></i> Distribución por Servicio
            </div>
            <canvas id="chartDistribucion"></canvas>
        </div>

        <!-- Comparativa mensual -->
        <div class="chart-box full">
            <div class="chart-title">
                <i class="fas fa-chart-area"></i> Evolución Mensual de Consumo
            </div>
            <canvas id="chartMensual" height="80"></canvas>
        </div>
    </div>

    <!-- Tabla de lecturas recientes -->
    <div style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 20px; color: #2c3e50;"><i class="fas fa-list"></i> Lecturas Recientes</h3>
        <table class="table-recursos">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Servicio</th>
                    <th>Lectura Anterior</th>
                    <th>Lectura Actual</th>
                    <th>Consumo</th>
                    <th>Costo</th>
                    <th>Estado Pago</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lecturas_recientes as $lectura): 
                    // Calcular si es anómalo
                    $promedio_servicio = 0;
                    foreach ($stats['promedios'] as $p) {
                        if ($p['tipo_servicio'] == $lectura['tipo_servicio']) {
                            $promedio_servicio = $p['promedio'];
                            break;
                        }
                    }
                    $es_anomalo = $lectura['consumo'] > ($promedio_servicio * 2);
                ?>
                    <tr style="<?php echo $es_anomalo ? 'background: #fff3cd;' : ''; ?>">
                        <td><strong><?php echo htmlspecialchars($lectura['departamento_nombre']); ?></strong></td>
                        <td>
                            <span class="badge-servicio badge-<?php echo $lectura['tipo_servicio']; ?>">
                                <i class="fas fa-<?php 
                                    echo $lectura['tipo_servicio'] == 'agua' ? 'tint' : 
                                        ($lectura['tipo_servicio'] == 'luz' ? 'bolt' : 'fire'); 
                                ?>"></i>
                                <?php echo ucfirst($lectura['tipo_servicio']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($lectura['lectura_anterior'], 2); ?></td>
                        <td><?php echo number_format($lectura['lectura_actual'], 2); ?></td>
                        <td><strong><?php echo number_format($lectura['consumo'], 2); ?></strong></td>
                        <td><strong style="color: #27ae60;">$<?php echo number_format($lectura['costo_total'], 2); ?></strong></td>
                        <td>
                            <span class="badge-estado badge-<?php echo $lectura['estado_pago']; ?>">
                                <?php echo ucfirst($lectura['estado_pago']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($lectura['fecha_lectura'])); ?></td>
                        <td>
                            <?php if ($es_anomalo): ?>
                                <span class="badge-anomalo"><i class="fas fa-exclamation-triangle"></i> Anómalo</span>
                            <?php else: ?>
                                <span class="badge-normal"><i class="fas fa-check"></i> Normal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabla de consumo por departamento -->
    <div>
        <h3 style="margin-bottom: 20px; color: #2c3e50;"><i class="fas fa-trophy"></i> Ranking de Consumo por Departamento</h3>
        <table class="table-recursos">
            <thead>
                <tr>
                    <th>Posición</th>
                    <th>Departamento</th>
                    <th>Agua (m³)</th>
                    <th>Luz (kWh)</th>
                    <th>Gas (m³)</th>
                    <th>Costo Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $pos = 1; ?>
                <?php foreach ($consumo_por_depto as $dept): ?>
                    <tr>
                        <td>
                            <?php if ($pos == 1): ?>
                                <span style="font-size: 1.3rem;">🥇</span>
                            <?php elseif ($pos == 2): ?>
                                <span style="font-size: 1.3rem;">🥈</span>
                            <?php elseif ($pos == 3): ?>
                                <span style="font-size: 1.3rem;">🥉</span>
                            <?php else: ?>
                                <strong>#<?php echo $pos; ?></strong>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($dept['departamento']); ?></strong></td>
                        <td><?php echo number_format($dept['agua'], 2); ?></td>
                        <td><?php echo number_format($dept['luz'], 2); ?></td>
                        <td><?php echo number_format($dept['gas'], 2); ?></td>
                        <td><strong style="color: #27ae60; font-size: 1.1rem;">$<?php echo number_format($dept['costo_total'], 2); ?></strong></td>
                    </tr>
                    <?php $pos++; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const colors = {
    agua: '#00bcd4',
    luz: '#ffc107',
    gas: '#9c27b0'
};

// Datos PHP de sesión para uso en JS
const sessionReadings = <?php echo json_encode(array_slice($_SESSION['consumo_readings'], 0, 200)); ?>;

// Rellenar tabla inicial y preparar datos para gráfica
function renderModuleTableAndChart(selectedRecurso) {
    const tbody = document.getElementById('moduleTableBody');
    tbody.innerHTML = '';

    const filtered = sessionReadings.filter(r => r.recurso === selectedRecurso).slice(0, 10);

    // Tabla
    filtered.forEach(r => {
        const tr = document.createElement('tr');
        const estadoHtml = (r.estado && r.estado.toLowerCase().includes('anom')) ?
            '<span class="estado-anomalo">' + r.estado + '</span>' :
            (r.estado ? r.estado : 'Normal');
        tr.innerHTML = '<td><strong>' + r.departamento + '</strong></td>' +
                       '<td>' + r.recurso.charAt(0).toUpperCase() + r.recurso.slice(1) + '</td>' +
                       '<td>' + parseFloat(r.lectura).toFixed(2) + '</td>' +
                       '<td>' + r.fecha + '</td>' +
                       '<td>' + estadoHtml + '</td>';
        tbody.appendChild(tr);
    });

    // Gráfica
    const labels = filtered.map(r => r.fecha).reverse();
    const dataPoints = filtered.map(r => parseFloat(r.lectura)).reverse();

    const ctx = document.getElementById('moduleChart').getContext('2d');
    if (window._moduleChart) {
        window._moduleChart.destroy();
    }
    window._moduleChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: selectedRecurso.charAt(0).toUpperCase() + selectedRecurso.slice(1),
                data: dataPoints,
                borderColor: colors[selectedRecurso],
                backgroundColor: colors[selectedRecurso] + '20',
                fill: true,
                tension: 0.3,
                borderWidth: 2
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
}

// Cuando cambie el recurso seleccionado, re-renderizar
document.getElementById('recurso').addEventListener('change', function() {
    renderModuleTableAndChart(this.value);
});

// Generar simulada
document.getElementById('btnGenerar').addEventListener('click', function() {
    const recurso = document.getElementById('recurso').value;
    let val = 0;
    if (recurso === 'agua') val = (Math.random() * (500 - 10) + 10).toFixed(2);
    if (recurso === 'luz') val = (Math.random() * (100 - 1) + 1).toFixed(2);
    if (recurso === 'gas') val = (Math.random() * (50 - 0.5) + 0.5).toFixed(2);
    document.getElementById('lectura').value = val;
    // Mostrar alerta local si supera umbrales
    const thresholds = { agua:400, luz:80, gas:40 };
    if (parseFloat(val) > thresholds[recurso]) {
        const alertText = parseFloat(val) > thresholds[recurso] * 1.2 ? '🚨 Posible fuga detectada' : '⚠️ Consumo anómalo detectado';
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert-module';
        alertDiv.style.marginTop = '8px';
        alertDiv.innerText = alertText;
        document.querySelector('.gestion-module').insertBefore(alertDiv, document.querySelector('.module-chart-card'));
        setTimeout(() => alertDiv.remove(), 6000);
    }
});

// Inicial render
renderModuleTableAndChart(document.getElementById('recurso').value);


// 1. Gráfica de consumo por departamento
const deptoData = <?php echo json_encode($consumo_por_depto); ?>;
const ctxDepto = document.getElementById('chartDepartamentos').getContext('2d');

new Chart(ctxDepto, {
    type: 'bar',
    data: {
        labels: deptoData.map(d => d.departamento),
        datasets: [
            {
                label: 'Agua (m³)',
                data: deptoData.map(d => parseFloat(d.agua)),
                backgroundColor: colors.agua,
                borderRadius: 8
            },
            {
                label: 'Luz (kWh)',
                data: deptoData.map(d => parseFloat(d.luz)),
                backgroundColor: colors.luz,
                borderRadius: 8
            },
            {
                label: 'Gas (m³)',
                data: deptoData.map(d => parseFloat(d.gas)),
                backgroundColor: colors.gas,
                borderRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// 2. Gráfica PIE de distribución
const ctxPie = document.getElementById('chartDistribucion').getContext('2d');
const totalAgua = deptoData.reduce((sum, d) => sum + parseFloat(d.agua), 0);
const totalLuz = deptoData.reduce((sum, d) => sum + parseFloat(d.luz), 0);
const totalGas = deptoData.reduce((sum, d) => sum + parseFloat(d.gas), 0);

new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Agua', 'Luz', 'Gas'],
        datasets: [{
            data: [totalAgua, totalLuz, totalGas],
            backgroundColor: [colors.agua, colors.luz, colors.gas],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15
            }
        }
    }
});

// 3. Gráfica mensual
const mensualData = <?php echo json_encode($comparativa_mensual); ?>;
const ctxMensual = document.getElementById('chartMensual').getContext('2d');

// Agrupar datos por mes y servicio
const meses = [...new Set(mensualData.map(d => d.mes_nombre))];
const aguaMensual = [];
const luzMensual = [];
const gasMensual = [];

meses.forEach(mes => {
    const aguaData = mensualData.find(d => d.mes_nombre === mes && d.tipo_servicio === 'agua');
    const luzData = mensualData.find(d => d.mes_nombre === mes && d.tipo_servicio === 'luz');
    const gasData = mensualData.find(d => d.mes_nombre === mes && d.tipo_servicio === 'gas');
    
    aguaMensual.push(aguaData ? parseFloat(aguaData.total_consumo) : 0);
    luzMensual.push(luzData ? parseFloat(luzData.total_consumo) : 0);
    gasMensual.push(gasData ? parseFloat(gasData.total_consumo) : 0);
});

new Chart(ctxMensual, {
    type: 'line',
    data: {
        labels: meses,
        datasets: [
            {
                label: 'Agua',
                data: aguaMensual,
                borderColor: colors.agua,
                backgroundColor: colors.agua + '20',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            },
            {
                label: 'Luz',
                data: luzMensual,
                borderColor: colors.luz,
                backgroundColor: colors.luz + '20',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            },
            {
                label: 'Gas',
                data: gasMensual,
                borderColor: colors.gas,
                backgroundColor: colors.gas + '20',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

console.log('Dashboard de Recursos cargado! 🚀');
</script>

<?php require_once '../../includes/footer.php'; ?>
