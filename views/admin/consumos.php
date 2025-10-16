<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Monitoreo de Consumos - Admin';
require_once __DIR__ . '/../../includes/header.php';

// Proteger acceso básico
if (!isLoggedIn() || !hasRole('admin')) {
	header('Location: ../../login.php');
	exit();
}

// Obtener últimas lecturas
$conn = get_db_connection();
$stmt = $conn->prepare('SELECT l.id, l.sensor_id, l.departamento_id, l.valor, l.tipo, l.recibido_en, s.tipo AS sensor_tipo FROM lecturas l LEFT JOIN sensores s ON s.id = l.sensor_id ORDER BY l.recibido_en DESC LIMIT 20');
$stmt->execute();
$lecturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Monitoreo de Consumos (Admin)</h1>

<p>Últimas 20 lecturas recibidas:</p>

<button id="btn-refresh" class="btn">Actualizar</button>

<table class="table">
	<thead>
		<tr>
			<th>ID</th>
			<th>Departamento</th>
			<th>Sensor</th>
			<th>Tipo</th>
			<th>Valor</th>
			<th>Recibido en</th>
		</tr>
	</thead>
	<tbody id="lecturas-body">
		<?php foreach ($lecturas as $l): ?>
			<tr>
				<td><?php echo htmlspecialchars($l['id']); ?></td>
				<td><?php echo htmlspecialchars($l['departamento_id']); ?></td>
				<td><?php echo htmlspecialchars($l['sensor_id']); ?></td>
				<td><?php echo htmlspecialchars($l['sensor_tipo']); ?></td>
				<td><?php echo htmlspecialchars($l['valor']); ?></td>
				<td><?php echo htmlspecialchars($l['recibido_en']); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<script>
// Botón que recarga la tabla mediante fetch a un endpoint simple
document.getElementById('btn-refresh').addEventListener('click', function() {
	fetch('consumos_data.php')
		.then(r => r.json())
		.then(data => {
			const tbody = document.getElementById('lecturas-body');
			tbody.innerHTML = '';
			data.forEach(l => {
				const tr = document.createElement('tr');
				tr.innerHTML = `<td>${l.id}</td><td>${l.departamento_id}</td><td>${l.sensor_id}</td><td>${l.sensor_tipo}</td><td>${l.valor}</td><td>${l.recibido_en}</td>`;
				tbody.appendChild(tr);
			})
		})
		.catch(err => console.error(err));
});

// TODO: Implementar SSE para actualizaciones en tiempo real
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

