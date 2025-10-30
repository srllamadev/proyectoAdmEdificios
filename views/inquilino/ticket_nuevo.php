<?php
require_once '../../includes/functions.php';
if (!isLoggedIn() || !hasRole('inquilino')) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = clean_input($_POST['titulo']);
    $descripcion = clean_input($_POST['descripcion']);
    $imagen = null;

    // Obtener ID del inquilino
    $stmt = $db->prepare("SELECT id FROM inquilinos WHERE user_id = :uid");
    $stmt->bindParam(':uid', $_SESSION['user_id']);
    $stmt->execute();
    $inq = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inq) {
        // Subir imagen
        if (!empty($_FILES['imagen']['name'])) {
            $nombreArchivo = time() . "_" . basename($_FILES["imagen"]["name"]);
            $rutaDestino = "../../uploads/" . $nombreArchivo;

            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino)) {
                $imagen = $nombreArchivo;
            }
        }

        $stmt = $db->prepare("INSERT INTO tickets (inquilino_id, titulo, descripcion, imagen) VALUES (:inq, :titulo, :desc, :img)");
        $stmt->bindParam(':inq', $inq['id']);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':desc', $descripcion);
        $stmt->bindParam(':img', $imagen);
        $stmt->execute();

        header("Location: tickets.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Ticket</title>
    <link rel="stylesheet" href="tickets.css">
</head>
<body>
<div class="ticket-form">
    <h2><i class="fas fa-plus-circle"></i> Crear Nuevo Ticket</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Título del Problema:</label>
        <input type="text" name="titulo" required>

        <label>Descripción Detallada:</label>
        <textarea name="descripcion" rows="5" required></textarea>

        <label>Imagen de la Incidencia:</label>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Enviar Ticket</button>
        <a href="tickets.php" class="btn-cancel">Cancelar</a>
    </form>
</div>
</body>
</html>
