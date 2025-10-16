-- Migración: Tablas para módulo de consumos y monitoreo
-- Creado: 2025-10-10
-- Notas: Ajustar tipos y tamaños según motor (MySQL/MariaDB)

-- Tabla de departamentos (si ya existe en el proyecto, omitir)
CREATE TABLE IF NOT EXISTS departamentos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	piso VARCHAR(50) DEFAULT NULL,
	propietario VARCHAR(150) DEFAULT NULL,
	creado_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Dispositivos (medidores físicos / gateways) asociados a un departamento
CREATE TABLE IF NOT EXISTS dispositivos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	departamento_id INT NOT NULL,
	identificador VARCHAR(128) NOT NULL UNIQUE, -- id del dispositivo / serial
	tipo ENUM('medidor','gateway','sensor') NOT NULL DEFAULT 'medidor',
	descripcion VARCHAR(255) DEFAULT NULL,
	activo TINYINT(1) DEFAULT 1,
	creado_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- Sensores físicos conectados a un dispositivo: agua, luz, gas
CREATE TABLE IF NOT EXISTS sensores (
	id INT AUTO_INCREMENT PRIMARY KEY,
	dispositivo_id INT NOT NULL,
	canal VARCHAR(64) NOT NULL, -- p.ej. 'agua_main', 'luz_fase1'
	tipo ENUM('agua','luz','gas') NOT NULL,
	unidad VARCHAR(16) NOT NULL DEFAULT 'kWh',
	descripcion VARCHAR(255) DEFAULT NULL,
	activo TINYINT(1) DEFAULT 1,
	creado_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE CASCADE
);

-- Lecturas temporales / históricas de los sensores
-- Se guarda valor, tipo de valor (instantáneo, acumulado) y flags
CREATE TABLE IF NOT EXISTS lecturas (
	id BIGINT AUTO_INCREMENT PRIMARY KEY,
	sensor_id INT NOT NULL,
	departamento_id INT NOT NULL,
	valor DOUBLE NOT NULL,
	tipo ENUM('instantaneo','acumulado') NOT NULL DEFAULT 'instantaneo',
	recibido_en DATETIME NOT NULL,
	creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
	procesado TINYINT(1) DEFAULT 0,
	INDEX (sensor_id),
	INDEX (departamento_id),
	INDEX (recibido_en),
	FOREIGN KEY (sensor_id) REFERENCES sensores(id) ON DELETE CASCADE,
	FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
);

-- Umbrales configurables por sensor/por departamento
CREATE TABLE IF NOT EXISTS umbrales (
	id INT AUTO_INCREMENT PRIMARY KEY,
	sensor_id INT NULL,
	departamento_id INT NULL,
	tipo_alerta ENUM('consumo_alto','posible_fuga','corte') NOT NULL,
	valor DOUBLE NOT NULL, -- umbral específico (interpretación depende del tipo)
	ventana_minutos INT DEFAULT 60, -- ventana para evaluar consumo
	activo TINYINT(1) DEFAULT 1,
	creado_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (sensor_id) REFERENCES sensores(id) ON DELETE SET NULL,
	FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
);

-- Alertas generadas por el sistema
CREATE TABLE IF NOT EXISTS alertas (
	id BIGINT AUTO_INCREMENT PRIMARY KEY,
	departamento_id INT NOT NULL,
	sensor_id INT NULL,
	tipo ENUM('consumo_alto','posible_fuga','corte','info') NOT NULL,
	prioridad ENUM('baja','media','alta') DEFAULT 'media',
	mensaje VARCHAR(512) NOT NULL,
	metadata JSON DEFAULT NULL,
	leido TINYINT(1) DEFAULT 0,
	creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
	FOREIGN KEY (sensor_id) REFERENCES sensores(id) ON DELETE SET NULL
);

-- Tabla de tokens para dispositivos que envían lecturas (autenticación simple)
CREATE TABLE IF NOT EXISTS device_tokens (
	id INT AUTO_INCREMENT PRIMARY KEY,
	dispositivo_id INT NOT NULL,
	token VARCHAR(255) NOT NULL UNIQUE,
	activo TINYINT(1) DEFAULT 1,
	creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE CASCADE
);

-- Índices recomendados para rendimiento de consultas por departamento y tiempo
CREATE INDEX IF NOT EXISTS idx_lecturas_dep_time ON lecturas (departamento_id, recibido_en);

-- Ejemplo: vista agregada para consumo por departamento por hora
DROP VIEW IF EXISTS vw_consumo_por_hora;
CREATE VIEW vw_consumo_por_hora AS
SELECT
	departamento_id,
	sensor_id,
	DATE_FORMAT(recibido_en, '%Y-%m-%d %H:00:00') AS hora,
	AVG(valor) AS avg_valor,
	MIN(valor) AS min_valor,
	MAX(valor) AS max_valor
FROM lecturas
GROUP BY departamento_id, sensor_id, DATE_FORMAT(recibido_en, '%Y-%m-%d %H');

-- Fin de migración

