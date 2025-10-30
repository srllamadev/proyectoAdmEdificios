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
