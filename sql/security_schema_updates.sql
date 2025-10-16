-- Extensión de tabla users para funcionalidades de seguridad avanzadas
-- Ejecutar después de la creación inicial de la tabla

USE edificio_admin;

-- Agregar campos de seguridad para recuperación de contraseña
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(255) NULL AFTER remember_token;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_expires TIMESTAMP NULL AFTER password_reset_token;

-- Agregar campos para bloqueo de cuenta por intentos fallidos
ALTER TABLE users ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0 AFTER password_reset_expires;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL AFTER failed_login_attempts;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_failed_login TIMESTAMP NULL AFTER locked_until;

-- Agregar campos para política de contraseñas
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP NULL AFTER last_failed_login;
ALTER TABLE users ADD COLUMN IF NOT EXISTS account_locked TINYINT(1) DEFAULT 0 AFTER password_changed_at;

-- Agregar índices para mejor rendimiento
ALTER TABLE users ADD INDEX idx_password_reset_token (password_reset_token);
ALTER TABLE users ADD INDEX idx_locked_until (locked_until);
ALTER TABLE users ADD INDEX idx_failed_attempts (failed_login_attempts);

-- Actualizar registros existentes con valores por defecto
UPDATE users SET
    failed_login_attempts = 0,
    account_locked = 0,
    password_changed_at = created_at
WHERE password_changed_at IS NULL;

-- Crear tabla para logs de seguridad (opcional pero recomendado)
CREATE TABLE IF NOT EXISTS security_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar algunos logs de ejemplo para usuarios existentes
INSERT INTO security_logs (user_id, action, details, created_at) VALUES
(1, 'account_created', 'Cuenta de administrador creada durante setup inicial', NOW()),
(2, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
(3, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
(4, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
(5, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(6, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(7, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(8, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(9, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW());