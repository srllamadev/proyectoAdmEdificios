-- Script SQL para crear la tabla de usuarios e insertar el admin
-- Base de datos: edificio_admin

-- Crear tabla de usuarios si no existe
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(255),
    rol VARCHAR(50) DEFAULT 'user',
    token VARCHAR(255),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario admin
-- Email: admin@admin.com
-- Password: ko87K#adm-0
INSERT INTO usuarios (email, password, nombre, rol) 
VALUES (
    'admin@admin.com',
    '$2y$10$YourHashedPasswordHere',
    'Administrador',
    'admin'
);

-- Nota: Necesitas ejecutar este PHP para generar el hash de la contraseña:
-- <?php echo password_hash('ko87K#adm-0', PASSWORD_DEFAULT); ?>

-- Tabla para mensajes de chat (opcional)
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
