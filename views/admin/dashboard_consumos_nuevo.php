<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Dashboard de Consumos';
require_once '../../includes/header.php';

require_once '../../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Variables iniciales
$totalDepartamentos = 0;
$totalLecturas = 0;
$consumoAgua = 0;
$consumoLuz = 0;
$consumoGas = 0;
$datosGrafica = [];
$lecturasRecientes = [];

try {
    // 1. Contar departamentos
    $stmt = $conn->query("SELECT COUNT(*) FROM departamentos");
    $totalDepartamentos = $stmt->fetchColumn();
    
    // 2. Contar lecturas totales
    $stmt = $conn->query("SELECT COUNT(*) FROM lecturas_consumo");
    $totalLecturas = $stmt->fetchColumn();
    
    // 3. Calcular consumo promedio por tipo
    $stmt = $conn->query("
        SELECT tipo_servicio, AVG(consumo) as promedio, SUM(consumo) as total
        FROM lecturas_consumo 
        GROUP BY tipo_servicio
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['tipo_servicio'] == 'agua') {
            $consumoAgua = round($row['promedio'], 2);
        } elseif ($row['tipo_servicio'] == 'luz') {
            $consumoLuz = round($row['promedio'], 2);
        } elseif ($row['tipo_servicio'] == 'gas') {
            $consumoGas = round($row['promedio'], 2);
        }
    }
    
    // 4. Datos para gráfica mensual (últimos 6 meses)
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(fecha_lectura, '%Y-%m') as mes,
            tipo_servicio,
            SUM(consumo) as total
        FROM lecturas_consumo
        WHERE fecha_lectura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mes, tipo_servicio
        ORDER BY mes, tipo_servicio
    ");
    
    $datosGrafica = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mes = $row['mes'];
        if (!isset($datosGrafica[$mes])) {
            $datosGrafica[$mes] = ['agua' => 0, 'luz' => 0, 'gas' => 0];
        }
        $datosGrafica[$mes][$row['tipo_servicio']] = (float)$row['total'];
    }
    
    // 5. Lecturas recientes
    $stmt = $conn->query("
        SELECT 
            d.nombre as departamento,
            l.tipo_servicio,
            l.consumo,
            l.fecha_lectura
        FROM lecturas_consumo l
        JOIN departamentos d ON d.id = l.departamento_id
        ORDER BY l.fecha_lectura DESC
        LIMIT 20
    ");
    $lecturasRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}
?>

<style>
    .consumos-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green));
        padding: 25px;
        border-radius: 12px;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-card.agua {
        background: linear-gradient(135deg, #4fc3f7, #0288d1);
    }
    
    .stat-card.luz {
        background: linear-gradient(135deg, #ffeb3b, #f57c00);
    }
    
    .stat-card.gas {
        background: linear-gradient(135deg, #9575cd, #5e35b1);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .chart-container {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .chart-container h3 {
        margin-bottom: 20px;
        color: var(--dark-blue);
    }
    
    .table-container {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow-x: auto;
    }
    
    .consumos-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .consumos-table th {
        background: var(--primary-blue);
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }
    
    .consumos-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }
    
    .consumos-table tr:hover {
        background: #f5f5f5;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge-agua {
        background: #e3f2fd;
        color: #0288d1;
    }
    
    .badge-luz {
        background: #fff9c4;
        color: #f57c00;
    }
    
    .badge-gas {
        background: #f3e5f5;
        color: #5e35b1;
    }
</style>

<div class="consumos-container">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard de Consumos</h1>
        <p>Análisis de consumo de servicios por departamento</p>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Estadísticas principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalDepartamentos; ?></div>
            <div class="stat-label"><i class="fas fa-building"></i> Departamentos</div>
        </div>
        
        <div class="stat-card agua">
            <div class="stat-number"><?php echo $consumoAgua; ?> m³</div>
            <div class="stat-label"><i class="fas fa-tint"></i> Consumo Promedio Agua</div>
        </div>
        
        <div class="stat-card luz">
            <div class="stat-number"><?php echo $consumoLuz; ?> kWh</div>
            <div class="stat-label"><i class="fas fa-bolt"></i> Consumo Promedio Luz</div>
        </div>
        
        <div class="stat-card gas">
            <div class="stat-number"><?php echo $consumoGas; ?> m³</div>
            <div class="stat-label"><i class="fas fa-fire"></i> Consumo Promedio Gas</div>
        </div>
    </div>
    
    <!-- Gráfica -->
    <div class="chart-container">
        <h3><i class="fas fa-chart-bar"></i> Consumo Mensual por Servicio (Últimos 6 Meses)</h3>
        <canvas id="consumoChart" height="80"></canvas>
    </div>
    
    <!-- Tabla de lecturas recientes -->
    <div class="table-container">
        <h3><i class="fas fa-list"></i> Lecturas Recientes</h3>
        <table class="consumos-table">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Servicio</th>
                    <th>Consumo</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lecturasRecientes)): ?>
                    <?php foreach ($lecturasRecientes as $lectura): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($lectura['departamento']); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo $lectura['tipo_servicio']; ?>">
                                    <?php 
                                    $iconos = ['agua' => 'tint', 'luz' => 'bolt', 'gas' => 'fire'];
                                    echo '<i class="fas fa-' . $iconos[$lectura['tipo_servicio']] . '"></i> ';
                                    echo ucfirst($lectura['tipo_servicio']); 
                                    ?>
                                </span>
                            </td>
                            <td><strong><?php echo number_format($lectura['consumo'], 2); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($lectura['fecha_lectura'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--dark-gray);"></i>
                            <p style="margin-top: 10px;">No hay lecturas registradas</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos de PHP a JavaScript
const datosGrafica = <?php echo json_encode($datosGrafica); ?>;

console.log('Datos de gráfica:', datosGrafica);

// Preparar labels (meses)
const meses = Object.keys(datosGrafica);
const agua = meses.map(m => datosGrafica[m].agua || 0);
const luz = meses.map(m => datosGrafica[m].luz || 0);
const gas = meses.map(m => datosGrafica[m].gas || 0);

console.log('Meses:', meses);
console.log('Agua:', agua);
console.log('Luz:', luz);
console.log('Gas:', gas);

// Crear gráfica
const ctx = document.getElementById('consumoChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: meses,
        datasets: [
            {
                label: 'Agua (m³)',
                data: agua,
                backgroundColor: '#4fc3f7',
                borderColor: '#0288d1',
                borderWidth: 2
            },
            {
                label: 'Luz (kWh)',
                data: luz,
                backgroundColor: '#ffeb3b',
                borderColor: '#f57c00',
                borderWidth: 2
            },
            {
                label: 'Gas (m³)',
                data: gas,
                backgroundColor: '#9575cd',
                borderColor: '#5e35b1',
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Consumo Total'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Mes'
                }
            }
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
