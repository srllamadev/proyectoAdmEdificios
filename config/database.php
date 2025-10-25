<?php
// Configuración de la base de datos para XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'edificio_admin');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

// Modo de desarrollo - en true los correos se guardan en archivos en lugar de enviarse
define('DEVELOPMENT_MODE', true);

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Usar puerto específico y opciones adicionales
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
            return null;
        }
        
        return $this->conn;
    }
}
?>