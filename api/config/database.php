<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'edificio_admin');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP por defecto no tiene contraseña para root

// Función para obtener conexión PDO
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]);
        exit();
    }
}
?>
