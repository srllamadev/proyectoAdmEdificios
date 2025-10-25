<?php
include_once '../../includes/header.php';
include_once '../../includes/functions.php';
include_once '../../includes/anomaly_detector.php';

// Verificar si el usuario está logueado y es administrador
checkUserRole('admin');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Recursos y Consumo - Panel de Administración</title>
    
    <!-- Chart.js para los gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Estilos propios -->
    <style>
        .consumo-header {
            background: linear-gradient(135deg, #F48FB1, #FFA07A);
            padding: 2rem;
            border-radius: 15px;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .alert-anomaly {
            background: linear-gradient(135deg, #FFE5E5, #FFD1D1);
            border-left: 4px solid #FF4444;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            display: none;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #F48FB1, #9575CD);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .consumo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        
        .consumo-table th, .consumo-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .consumo-table .anomalo {
            background-color: #FFE5E5;
        }
        
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
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