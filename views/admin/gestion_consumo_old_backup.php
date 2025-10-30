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
        <div class="consumo-header">
            <h1>💡 Gestión de Recursos y Consumo</h1>
            <p>Registro manual o simulación de lecturas de agua, luz y gas por departamento con alertas de consumo anómalo.</p>
        </div>

        <div class="form-container">
            <form id="consumoForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="departamento">Departamento:</label>
                            <select class="form-control" id="departamento" required>
                                <option value="101">101</option>
                                <option value="201">201</option>
                                <option value="301">301</option>
                                <option value="401">401</option>
                                <option value="501">501</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="recurso">Recurso:</label>
                            <select class="form-control" id="recurso" required>
                                <option value="agua">Agua</option>
                                <option value="luz">Luz</option>
                                <option value="gas">Gas</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lectura">Lectura:</label>
                            <input type="number" class="form-control" id="lectura" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha">Fecha:</label>
                            <input type="date" class="form-control" id="fecha" required>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <button type="submit" class="btn-gradient">Guardar Lectura</button>
                        <button type="button" class="btn-gradient" onclick="generarLecturaSimulada()">Generar Simulada</button>
                    </div>
                </div>
            </form>
            
            <div class="alert-anomaly" id="alertaConsumo">
                <strong>⚠️ Consumo anómalo detectado</strong>
                <p>Se ha detectado un consumo fuera de los rangos normales.</p>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="consumoChart"></canvas>
        </div>

        <div class="table-responsive">
            <table class="consumo-table" id="registrosTable">
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th>Recurso</th>
                        <th>Lectura</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Inicializar registros
        let registros = [];
        
        // Establecer fecha actual por defecto
        document.getElementById('fecha').valueAsDate = new Date();
        
        // Función para generar lectura simulada
        function generarLecturaSimulada() {
            const recurso = document.getElementById('recurso').value;
            let lectura;
            
            switch(recurso) {
                case 'agua':
                    lectura = Math.random() * (500 - 10) + 10;
                    break;
                case 'luz':
                    lectura = Math.random() * (100 - 1) + 1;
                    break;
                case 'gas':
                    lectura = Math.random() * (50 - 0.5) + 0.5;
                    break;
            }
            
            document.getElementById('lectura').value = lectura.toFixed(2);
            verificarConsumoAnomalo(lectura, recurso);
        }
        
        // Función para verificar consumo anómalo
        function verificarConsumoAnomalo(lectura, recurso) {
            const limites = {
                'agua': 400,
                'luz': 80,
                'gas': 40
            };
            
            const alertaElement = document.getElementById('alertaConsumo');
            if (lectura > limites[recurso]) {
                alertaElement.style.display = 'block';
            } else {
                alertaElement.style.display = 'none';
            }
        }
        
        // Gráfico de consumo
        let consumoChart;
        
        function actualizarGrafico() {
            const ctx = document.getElementById('consumoChart').getContext('2d');
            
            if (consumoChart) {
                consumoChart.destroy();
            }
            
            const recursoSeleccionado = document.getElementById('recurso').value;
            const registrosFiltrados = registros
                .filter(r => r.recurso === recursoSeleccionado)
                .slice(-10);
            
            consumoChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: registrosFiltrados.map(r => r.fecha),
                    datasets: [{
                        label: `Consumo de ${recursoSeleccionado}`,
                        data: registrosFiltrados.map(r => r.lectura),
                        borderColor: '#F48FB1',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: `Últimas 10 lecturas de ${recursoSeleccionado}`
                        }
                    }
                }
            });
        }
        
        // Manejar envío del formulario
        document.getElementById('consumoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const registro = {
                departamento: document.getElementById('departamento').value,
                recurso: document.getElementById('recurso').value,
                lectura: parseFloat(document.getElementById('lectura').value),
                fecha: document.getElementById('fecha').value
            };
            
            registros.push(registro);
            actualizarTabla();
            actualizarGrafico();
            
            // Limpiar formulario
            document.getElementById('lectura').value = '';
            verificarConsumoAnomalo(registro.lectura, registro.recurso);
        });
        
        // Función para actualizar la tabla
        function actualizarTabla() {
            const tbody = document.getElementById('registrosTable').getElementsByTagName('tbody')[0];
            tbody.innerHTML = '';
            
            registros.forEach(registro => {
                const row = tbody.insertRow();
                const esAnomalo = verificarSiEsAnomalo(registro.lectura, registro.recurso);
                
                if (esAnomalo) {
                    row.classList.add('anomalo');
                }
                
                row.insertCell(0).textContent = registro.departamento;
                row.insertCell(1).textContent = registro.recurso;
                row.insertCell(2).textContent = registro.lectura.toFixed(2);
                row.insertCell(3).textContent = registro.fecha;
                row.insertCell(4).textContent = esAnomalo ? 'Anómalo' : 'Normal';
            });
        }
        
        function verificarSiEsAnomalo(lectura, recurso) {
            const limites = {
                'agua': 400,
                'luz': 80,
                'gas': 40
            };
            return lectura > limites[recurso];
        }
        
        // Inicializar gráfico
        actualizarGrafico();
        
        // Event listener para cambio de recurso
        document.getElementById('recurso').addEventListener('change', actualizarGrafico);
    </script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>