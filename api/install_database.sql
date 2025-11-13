-- Script SQL para configurar la base de datos edificio_admin
-- Ejecutar en phpMyAdmin o MySQL Workbench

USE edificio_admin;

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(255),
    rol VARCHAR(50) DEFAULT 'user',
    token VARCHAR(255),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador
-- Email: admin@admin.com
-- Password: ko87K#adm-0
INSERT INTO usuarios (email, password, nombre, rol) VALUES
('admin@admin.com', '$2y$10$dM69IOwrWyy3c83i43eYwOtATtDR6J4xisFLn2XDG3OFJJYmxT3yu', 'Administrador', 'admin');

-- Tabla para mensajes de chat
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para apartamentos (ejemplo)
CREATE TABLE IF NOT EXISTS apartamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    piso INT NOT NULL,
    estado ENUM('ocupado', 'vacante', 'mantenimiento') DEFAULT 'vacante',
    propietario_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que el usuario fue creado correctamente
SELECT id, email, nombre, rol, created_at FROM usuarios WHERE email = 'admin@admin.com';
