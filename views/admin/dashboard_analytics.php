<?php
require_once '../../includes/functions.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Dashboard Analytics - Consumos';
require_once '../../includes/header.php';
require_once '../../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Variables
$stats = [
    'total_departamentos' => 0,
    'total_lecturas' => 0,
    'ingreso_total' => 0,
    'ingreso_mes_actual' => 0,
    'deuda_pendiente' => 0,
    'consumo_agua' => 0,
    'consumo_luz' => 0,
    'consumo_gas' => 0,
    'pagado' => 0,
    'pendiente' => 0,
    'vencido' => 0
];

$grafica_mensual = [];
$grafica_servicios_pie = [];
$grafica_ingresos = [];
$top_consumidores = [];
$depto_comparacion = [];

try {
    // Estadísticas generales
    $stats['total_departamentos'] = $conn->query("SELECT COUNT(*) FROM departamentos")->fetchColumn();
    $stats['total_lecturas'] = $conn->query("SELECT COUNT(*) FROM lecturas_consumo")->fetchColumn();
    $stats['ingreso_total'] = $conn->query("SELECT COALESCE(SUM(costo_total), 0) FROM lecturas_consumo")->fetchColumn();
    $stats['ingreso_mes_actual'] = $conn->query("SELECT COALESCE(SUM(costo_total), 0) FROM lecturas_consumo WHERE MONTH(fecha_lectura) = MONTH(CURDATE()) AND YEAR(fecha_lectura) = YEAR(CURDATE())")->fetchColumn();
    $stats['deuda_pendiente'] = $conn->query("SELECT COALESCE(SUM(costo_total), 0) FROM lecturas_consumo WHERE estado_pago IN ('pendiente', 'vencido')")->fetchColumn();
    
    // Consumos totales por servicio
    $stmt = $conn->query("SELECT tipo_servicio, SUM(consumo) as total FROM lecturas_consumo GROUP BY tipo_servicio");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stats['consumo_' . $row['tipo_servicio']] = round($row['total'], 2);
    }
    
    // Estados de pago
    $stmt = $conn->query("SELECT estado_pago, COUNT(*) as cantidad FROM lecturas_consumo GROUP BY estado_pago");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stats[$row['estado_pago']] = $row['cantidad'];
    }
    
    // Gráfica mensual de ingresos (últimos 6 meses)
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(fecha_lectura, '%Y-%m') as mes,
            DATE_FORMAT(fecha_lectura, '%b') as mes_corto,
            SUM(costo_total) as ingreso
        FROM lecturas_consumo
        WHERE fecha_lectura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mes
        ORDER BY mes
    ");
    $grafica_ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Distribución de servicios (PIE)
    $stmt = $conn->query("
        SELECT tipo_servicio, SUM(costo_total) as total 
        FROM lecturas_consumo 
        GROUP BY tipo_servicio
    ");
    $grafica_servicios_pie = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 departamentos con mayor consumo
    $stmt = $conn->query("
        SELECT 
            d.nombre as departamento,
            SUM(l.consumo) as consumo_total,
            SUM(l.costo_total) as costo_total
        FROM lecturas_consumo l
        JOIN departamentos d ON d.id = l.departamento_id
        GROUP BY d.id, d.nombre
        ORDER BY consumo_total DESC
        LIMIT 5
    ");
    $top_consumidores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Comparación por departamento (consumo mensual promedio)
    $stmt = $conn->query("
        SELECT 
            d.nombre as departamento,
            AVG(l.consumo) as promedio,
            l.tipo_servicio
        FROM lecturas_consumo l
        JOIN departamentos d ON d.id = l.departamento_id
        GROUP BY d.id, d.nombre, l.tipo_servicio
        ORDER BY d.nombre, l.tipo_servicio
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $depto = $row['departamento'];
        if (!isset($depto_comparacion[$depto])) {
            $depto_comparacion[$depto] = ['agua' => 0, 'luz' => 0, 'gas' => 0];
        }
        $depto_comparacion[$depto][$row['tipo_servicio']] = round($row['promedio'], 2);
    }
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<style>
:root {
    --color-agua: #009B77;
    --color-luz: #D4AF37;
    --color-gas: #7ED957;
    --color-success: #7ED957;
    --color-warning: #D4AF37;
    --color-danger: #f44336;
    --gradient-1: linear-gradient(135deg, #001F54 0%, #009B77 100%);
    --gradient-2: linear-gradient(135deg, #D4AF37 0%, #F4D03F 100%);
    --gradient-3: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
    --gradient-4: linear-gradient(135deg, #7ED957 0%, #A8E063 100%);
    --gradient-5: linear-gradient(135deg, #001F54 0%, #009B77 100%);
    --glass-white: rgba(255, 255, 255, 0.15);
    --glass-emerald: rgba(0, 155, 119, 0.15);
}

body {
    background: linear-gradient(135deg, #E8F5F1 0%, #D4F1E8 30%, #C8EFE0 60%, #E1F0FF 100%);
    background-attachment: fixed;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard-premium {
    padding: 30px;
    max-width: 1600px;
    margin: 0 auto;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.8s;
}

.dashboard-header h1 {
    font-size: 3rem;
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
    font-weight: 800;
}

.dashboard-header p {
    color: #666;
    font-size: 1.1rem;
}

/* Tarjetas de estadísticas mejoradas */
.stats-premium {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card-premium {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 155, 119, 0.15);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s;
}

.stat-card-premium:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 155, 119, 0.25);
    border: 1px solid rgba(0, 155, 119, 0.4);
}

.stat-card-premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--gradient-1);
}

.stat-card-premium.agua::before { background: var(--gradient-3); }
.stat-card-premium.luz::before { background: var(--gradient-5); }
.stat-card-premium.gas::before { background: var(--gradient-2); }
.stat-card-premium.ingreso::before { background: var(--gradient-4); }

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 15px;
    background: var(--gradient-1);
    color: white;
}

.stat-card-premium.agua .stat-icon { background: var(--gradient-3); }
.stat-card-premium.luz .stat-icon { background: var(--gradient-5); }
.stat-card-premium.gas .stat-icon { background: var(--gradient-2); }
.stat-card-premium.ingreso .stat-icon { background: var(--gradient-4); }

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2c3e50;
    margin: 10px 0;
    animation: countUp 1.5s;
}

.stat-label {
    color: #7f8c8d;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.stat-change {
    margin-top: 10px;
    font-size: 0.85rem;
}

.stat-change.positive {
    color: var(--color-success);
}

.stat-change.negative {
    color: var(--color-danger);
}

/* Grid de gráficas */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 25px;
    margin-bottom: 40px;
}

.chart-card {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 155, 119, 0.15);
    animation: fadeIn 0.8s;
    transition: all 0.3s ease;
}

.chart-card:hover {
    box-shadow: 0 15px 40px rgba(0, 155, 119, 0.25);
    border: 1px solid rgba(0, 155, 119, 0.4);
}

.chart-card.full { grid-column: span 12; }
.chart-card.half { grid-column: span 6; }
.chart-card.third { grid-column: span 4; }
.chart-card.two-third { grid-column: span 8; }

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.chart-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
}

.chart-subtitle {
    color: #95a5a6;
    font-size: 0.9rem;
    margin-top: 5px;
}

/* Tabla premium */
.table-premium {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}

.table-premium thead th {
    background: var(--gradient-1);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.table-premium thead th:first-child {
    border-radius: 10px 0 0 10px;
}

.table-premium thead th:last-child {
    border-radius: 0 10px 10px 0;
}

.table-premium tbody tr {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 10px rgba(0, 155, 119, 0.08);
    transition: all 0.3s;
}

.table-premium tbody tr:hover {
    transform: scale(1.02);
    box-shadow: 0 5px 20px rgba(0, 155, 119, 0.2);
    border: 1px solid rgba(0, 155, 119, 0.3);
}

.table-premium tbody td {
    padding: 20px 15px;
}

.table-premium tbody td:first-child {
    border-radius: 10px 0 0 10px;
    font-weight: 700;
}

.table-premium tbody td:last-child {
    border-radius: 0 10px 10px 0;
}

/* Progress bars */
.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #ecf0f1;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-bar {
    height: 100%;
    border-radius: 10px;
    transition: width 1.5s ease;
}

.progress-agua { background: var(--color-agua); }
.progress-luz { background: var(--color-luz); }
.progress-gas { background: var(--color-gas); }

/* Badges mejorados */
.badge-premium {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-pagado {
    background: linear-gradient(135deg, #7ED957 0%, #A8E063 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(126, 217, 87, 0.3);
}

.badge-pendiente {
    background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.badge-vencido {
    background: linear-gradient(135deg, #f44336 0%, #e91e63 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

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

@keyframes countUp {
    from { opacity: 0; transform: scale(0.5); }
    to { opacity: 1; transform: scale(1); }
}

/* Responsive */
@media (max-width: 1200px) {
    .chart-card.half, .chart-card.third, .chart-card.two-third {
        grid-column: span 12;
    }
}
</style>

<div class="dashboard-premium">
    <!-- Header -->
    <div class="dashboard-header">
        <h1><i class="fas fa-chart-pie"></i> Dashboard de analiticas</h1>
        <p>Análisis completo de consumos y facturación en tiempo real</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Estadísticas principales -->
    <div class="stats-premium">
        <div class="stat-card-premium ingreso">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-value">$<?php echo number_format($stats['ingreso_total'], 2); ?></div>
            <div class="stat-label">Ingresos Totales</div>
            <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +12.5% vs mes anterior</div>
        </div>

        <div class="stat-card-premium agua">
            <div class="stat-icon"><i class="fas fa-tint"></i></div>
            <div class="stat-value"><?php echo number_format($stats['consumo_agua'], 0); ?></div>
            <div class="stat-label">m³ Agua Consumidos</div>
            <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +5.2%</div>
        </div>

        <div class="stat-card-premium luz">
            <div class="stat-icon"><i class="fas fa-bolt"></i></div>
            <div class="stat-value"><?php echo number_format($stats['consumo_luz'], 0); ?></div>
            <div class="stat-label">kWh Energía</div>
            <div class="stat-change negative"><i class="fas fa-arrow-down"></i> -2.8%</div>
        </div>

        <div class="stat-card-premium gas">
            <div class="stat-icon"><i class="fas fa-fire"></i></div>
            <div class="stat-value"><?php echo number_format($stats['consumo_gas'], 0); ?></div>
            <div class="stat-label">m³ Gas Natural</div>
            <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +8.1%</div>
        </div>

        <div class="stat-card-premium">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div class="stat-value"><?php echo $stats['total_departamentos']; ?></div>
            <div class="stat-label">Departamentos</div>
        </div>

        <div class="stat-card-premium ingreso">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-value">$<?php echo number_format($stats['ingreso_mes_actual'], 2); ?></div>
            <div class="stat-label">Ingresos Mes Actual</div>
        </div>

        <div class="stat-card-premium">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value">$<?php echo number_format($stats['deuda_pendiente'], 2); ?></div>
            <div class="stat-label">Deuda Pendiente</div>
            <div class="stat-change negative"><i class="fas fa-exclamation-circle"></i> Requiere atención</div>
        </div>

        <div class="stat-card-premium">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-value"><?php echo $stats['total_lecturas']; ?></div>
            <div class="stat-label">Lecturas Totales</div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="charts-grid">
        <!-- Gráfica de ingresos mensuales -->
        <div class="chart-card two-third">
            <div class="chart-header">
                <div>
                    <div class="chart-title"><i class="fas fa-chart-line"></i> Evolución de Ingresos</div>
                    <div class="chart-subtitle">Últimos 6 meses</div>
                </div>
            </div>
            <canvas id="chartIngresos" height="80"></canvas>
        </div>

        <!-- Gráfica circular de distribución -->
        <div class="chart-card third">
            <div class="chart-header">
                <div>
                    <div class="chart-title"><i class="fas fa-chart-pie"></i> Distribución</div>
                    <div class="chart-subtitle">Por servicio</div>
                </div>
            </div>
            <canvas id="chartPie"></canvas>
        </div>

        <!-- Estado de pagos (Donut) -->
        <div class="chart-card third">
            <div class="chart-header">
                <div>
                    <div class="chart-title"><i class="fas fa-credit-card"></i> Estado de Pagos</div>
                    <div class="chart-subtitle">Distribución actual</div>
                </div>
            </div>
            <canvas id="chartPagos"></canvas>
        </div>

        <!-- Comparación por departamento -->
        <div class="chart-card two-third">
            <div class="chart-header">
                <div>
                    <div class="chart-title"><i class="fas fa-chart-bar"></i> Comparativa por Departamento</div>
                    <div class="chart-subtitle">Consumo promedio mensual</div>
                </div>
            </div>
            <canvas id="chartComparacion" height="80"></canvas>
        </div>

        <!-- Top consumidores -->
        <div class="chart-card full">
            <div class="chart-header">
                <div>
                    <div class="chart-title"><i class="fas fa-trophy"></i> Top 5 Departamentos con Mayor Consumo</div>
                    <div class="chart-subtitle">Ranking de consumo y facturación</div>
                </div>
            </div>
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Posición</th>
                        <th>Departamento</th>
                        <th>Consumo Total</th>
                        <th>Costo Total</th>
                        <th>Progreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $pos = 1; $max_consumo = !empty($top_consumidores) ? $top_consumidores[0]['consumo_total'] : 1; ?>
                    <?php foreach ($top_consumidores as $dept): ?>
                        <tr>
                            <td>
                                <?php if ($pos == 1): ?>
                                    <span style="font-size: 1.5rem;">🥇</span>
                                <?php elseif ($pos == 2): ?>
                                    <span style="font-size: 1.5rem;">🥈</span>
                                <?php elseif ($pos == 3): ?>
                                    <span style="font-size: 1.5rem;">🥉</span>
                                <?php else: ?>
                                    <strong>#<?php echo $pos; ?></strong>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($dept['departamento']); ?></strong></td>
                            <td><?php echo number_format($dept['consumo_total'], 2); ?> unidades</td>
                            <td><strong style="color: var(--color-success);">$<?php echo number_format($dept['costo_total'], 2); ?></strong></td>
                            <td>
                                <div class="progress-bar-container">
                                    <div class="progress-bar progress-<?php echo ($pos % 3 == 0) ? 'gas' : (($pos % 2 == 0) ? 'luz' : 'agua'); ?>" 
                                         style="width: <?php echo ($dept['consumo_total'] / $max_consumo * 100); ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php $pos++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuración global de Chart.js
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.font.size = 12;

// Colores
const colors = {
    agua: '#009B77',
    luz: '#D4AF37',
    gas: '#7ED957',
    gradient1: 'rgba(0, 155, 119, 0.8)',
    gradient2: 'rgba(126, 217, 87, 0.8)',
    success: '#7ED957',
    warning: '#D4AF37',
    danger: '#f44336'
};

// 1. Gráfica de ingresos mensuales (LINE)
const ingresosData = <?php echo json_encode($grafica_ingresos); ?>;
const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');

const gradientIngresos = ctxIngresos.createLinearGradient(0, 0, 0, 400);
gradientIngresos.addColorStop(0, 'rgba(0, 155, 119, 0.8)');
gradientIngresos.addColorStop(1, 'rgba(126, 217, 87, 0.1)');

new Chart(ctxIngresos, {
    type: 'line',
    data: {
        labels: ingresosData.map(d => d.mes_corto || d.mes),
        datasets: [{
            label: 'Ingresos ($)',
            data: ingresosData.map(d => parseFloat(d.ingreso)),
            backgroundColor: gradientIngresos,
            borderColor: colors.gradient1,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: '#fff',
            pointBorderWidth: 3,
            pointBorderColor: colors.gradient1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 },
                callbacks: {
                    label: (context) => `Ingresos: $${context.parsed.y.toFixed(2)}`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    callback: (value) => '$' + value.toFixed(0)
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// 2. Gráfica PIE de servicios
const serviciosData = <?php echo json_encode($grafica_servicios_pie); ?>;
const ctxPie = document.getElementById('chartPie').getContext('2d');

new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: serviciosData.map(d => d.tipo_servicio.charAt(0).toUpperCase() + d.tipo_servicio.slice(1)),
        datasets: [{
            data: serviciosData.map(d => parseFloat(d.total)),
            backgroundColor: [colors.agua, colors.luz, colors.gas],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: { size: 13, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15,
                callbacks: {
                    label: (context) => `${context.label}: $${context.parsed.toFixed(2)}`
                }
            }
        }
    }
});

// 3. Gráfica DONUT de estado de pagos
const ctxPagos = document.getElementById('chartPagos').getContext('2d');

new Chart(ctxPagos, {
    type: 'doughnut',
    data: {
        labels: ['Pagado', 'Pendiente', 'Vencido'],
        datasets: [{
            data: [<?php echo $stats['pagado']; ?>, <?php echo $stats['pendiente']; ?>, <?php echo $stats['vencido']; ?>],
            backgroundColor: [colors.success, colors.warning, colors.danger],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: { size: 13, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15,
                callbacks: {
                    label: (context) => `${context.label}: ${context.parsed} lecturas`
                }
            }
        }
    }
});

// 4. Gráfica de comparación por departamento (BAR HORIZONTAL)
const deptoData = <?php echo json_encode($depto_comparacion); ?>;
const ctxComparacion = document.getElementById('chartComparacion').getContext('2d');

const deptos = Object.keys(deptoData);
const aguaData = deptos.map(d => deptoData[d].agua);
const luzData = deptos.map(d => deptoData[d].luz);
const gasData = deptos.map(d => deptoData[d].gas);

new Chart(ctxComparacion, {
    type: 'bar',
    data: {
        labels: deptos,
        datasets: [
            {
                label: 'Agua (m³)',
                data: aguaData,
                backgroundColor: colors.agua,
                borderRadius: 8
            },
            {
                label: 'Luz (kWh)',
                data: luzData,
                backgroundColor: colors.luz,
                borderRadius: 8
            },
            {
                label: 'Gas (m³)',
                data: gasData,
                backgroundColor: colors.gas,
                borderRadius: 8
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    padding: 15,
                    font: { size: 13, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 15
            }
        },
        scales: {
            y: {
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

console.log('Dashboard cargado exitosamente! 🚀');
</script>

<?php require_once '../../includes/footer.php'; ?>
