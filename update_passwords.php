<?php
require_once 'includes/functions.php';

// Configuración de las nuevas contraseñas
$newPasswords = [
    'admin@edificio.com' => 'ko87K#adm-0',
    'empleado1@edificio.com' => 'ko87K#emp-1',
    'empleado2@edificio.com' => 'ko87K#emp-2',
    'empleado3@edificio.com' => 'ko87K#emp-3',
    'inquilino1@edificio.com' => 'ko87K#fo-inq-1',
    'inquilino2@edificio.com' => 'ko87K#fo-inq-2',
    'inquilino3@edificio.com' => 'ko87K#fo-inq-3',
    'inquilino4@edificio.com' => 'ko87K#fo-inq-4',
    'inquilino5@edificio.com' => 'ko87K#fo-inq-5',
];

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=edificio_admin;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Actualizando contraseñas de usuarios...</h2>";
    echo "<pre>";

    foreach ($newPasswords as $email => $plainPassword) {
        // Generar hash de la nueva contraseña
        $hashedPassword = hashPassword($plainPassword);

        // Actualizar en la base de datos
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        if ($stmt->rowCount() > 0) {
            echo "✓ Contraseña actualizada para: $email\n";
        } else {
            echo "✗ No se encontró el usuario: $email\n";
        }
    }

    echo "\nActualización completada exitosamente!";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<h2>Error en la actualización:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>