<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Mis Consumos - Inquilino';
require_once __DIR__ . '/../../includes/header.php';

if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

// Asumimos que la session contiene departamento_id del inquilino
$departamento_id = isset($_SESSION['departamento_id']) ? (int)$_SESSION['departamento_id'] : null;
if (!$departamento_id) {
    echo '<p>No se encontró departamento asociado a su cuenta.</p>';
    require_once __DIR__ . '/../../includes/footer.php';
    exit();
}

$conn = get_db_connection();
$stmt = $conn->prepare('SELECT l.id, l.sensor_id, l.departamento_id, l.valor, l.tipo, l.recibido_en, s.tipo AS sensor_tipo FROM lecturas l LEFT JOIN sensores s ON s.id = l.sensor_id WHERE l.departamento_id = :dep ORDER BY l.recibido_en DESC LIMIT 50');
$stmt->execute([':dep' => $departamento_id]);
$lecturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Mis Consumos</h1>
<p>Últimas lecturas de su departamento (<?php echo $departamento_id; ?>):</p>
<table class="table">
    <thead>
        <tr><th>ID</th><th>Sensor</th><th>Tipo</th><th>Valor</th><th>Recibido en</th></tr>
    </thead>
    <tbody>
        <?php foreach ($lecturas as $l): ?>
            <tr>
                <td><?php echo htmlspecialchars($l['id']); ?></td>
                <td><?php echo htmlspecialchars($l['sensor_id']); ?></td>
                <td><?php echo htmlspecialchars($l['sensor_tipo']); ?></td>
                <td><?php echo htmlspecialchars($l['valor']); ?></td>
                <td><?php echo htmlspecialchars($l['recibido_en']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// Conectar SSE para recibir nuevas lecturas
const evtSource = new EventSource('../../includes/stream_lecturas.php');
evtSource.addEventListener('lectura', function(e) {
    const data = JSON.parse(e.data);
    // Añadir a la tabla solo si pertenece al mismo departamento
    if (parseInt(data.departamento_id) === <?php echo $departamento_id; ?>) {
        const tbody = document.querySelector('table tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${data.id}</td><td>${data.sensor_id}</td><td>${data.sensor_tipo}</td><td>${data.valor}</td><td>${data.recibido_en}</td>`;
        tbody.insertBefore(tr, tbody.firstChild);
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
