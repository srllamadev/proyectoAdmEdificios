-- Script SQL simplificado para actualizar tabla users
-- Versión compatible que no depende de remember_token

USE edificio_admin;

-- Agregar campos de seguridad uno por uno con verificación
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'password_reset_token') = 0,
    'ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(255) NULL',
    'SELECT "Columna password_reset_token ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'password_reset_expires') = 0,
    'ALTER TABLE users ADD COLUMN password_reset_expires TIMESTAMP NULL',
    'SELECT "Columna password_reset_expires ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'failed_login_attempts') = 0,
    'ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0',
    'SELECT "Columna failed_login_attempts ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'locked_until') = 0,
    'ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL',
    'SELECT "Columna locked_until ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'last_failed_login') = 0,
    'ALTER TABLE users ADD COLUMN last_failed_login TIMESTAMP NULL',
    'SELECT "Columna last_failed_login ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'password_changed_at') = 0,
    'ALTER TABLE users ADD COLUMN password_changed_at TIMESTAMP NULL',
    'SELECT "Columna password_changed_at ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'users'
     AND COLUMN_NAME = 'account_locked') = 0,
    'ALTER TABLE users ADD COLUMN account_locked TINYINT(1) DEFAULT 0',
    'SELECT "Columna account_locked ya existe"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar valores por defecto de forma segura
UPDATE users SET
    failed_login_attempts = COALESCE(failed_login_attempts, 0),
    account_locked = COALESCE(account_locked, 0),
    password_changed_at = COALESCE(password_changed_at, created_at)
WHERE failed_login_attempts IS NULL
   OR account_locked IS NULL
   OR password_changed_at IS NULL;

-- Crear tabla de logs de seguridad si no existe
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

-- Insertar logs iniciales solo si no existen
INSERT IGNORE INTO security_logs (user_id, action, details, created_at) VALUES
(1, 'account_created', 'Cuenta de administrador creada durante setup inicial', NOW()),
(2, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
(3, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
(4, 'account_created', 'Cuenta de empleado creada durante setup inicial', NOW()),
(5, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(6, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(7, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(8, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW()),
(9, 'account_created', 'Cuenta de inquilino creada durante setup inicial', NOW());

SELECT '✅ Actualización de esquema completada exitosamente' as resultado;