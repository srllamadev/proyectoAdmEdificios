-- ============================================
-- Script para crear tablas de CONSUMOS
-- Sistema de Gestión de Edificios
-- ============================================

USE edificio_admin;

-- ============================================
-- 1. TABLA: departamentos
-- ============================================
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: 101, 201, 301',
  `piso` int(11) DEFAULT NULL,
  `estado` enum('ocupado','desocupado','mantenimiento') NOT NULL DEFAULT 'desocupado',
  `superficie_m2` decimal(8,2) DEFAULT NULL,
  `habitaciones` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABLA: sensores
-- ============================================
CREATE TABLE IF NOT EXISTS `sensores` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL COMMENT 'Código único del sensor',
  `tipo` enum('agua','luz','gas') NOT NULL,
  `departamento_id` bigint(20) UNSIGNED NOT NULL,
  `ubicacion` varchar(100) DEFAULT NULL COMMENT 'Ej: Medidor principal, Cocina, Baño',
  `estado` enum('activo','inactivo','mantenimiento') NOT NULL DEFAULT 'activo',
  `ultima_lectura` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `departamento_id` (`departamento_id`),
  KEY `tipo` (`tipo`),
  CONSTRAINT `fk_sensor_dept` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABLA: lecturas
-- ============================================
CREATE TABLE IF NOT EXISTS `lecturas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sensor_id` bigint(20) UNSIGNED NOT NULL,
  `departamento_id` bigint(20) UNSIGNED NOT NULL,
  `valor` decimal(10,2) NOT NULL COMMENT 'Consumo en unidades (kWh, m3, etc)',
  `unidad` varchar(20) DEFAULT 'kWh' COMMENT 'kWh, m3, etc',
  `recibido_en` datetime NOT NULL DEFAULT current_timestamp(),
  `facturado` tinyint(1) DEFAULT 0 COMMENT 'Si ya se facturó',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sensor_id` (`sensor_id`),
  KEY `departamento_id` (`departamento_id`),
  KEY `recibido_en` (`recibido_en`),
  CONSTRAINT `fk_lectura_sensor` FOREIGN KEY (`sensor_id`) REFERENCES `sensores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lectura_dept` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE EJEMPLO
-- ============================================

-- Insertar departamentos
INSERT INTO `departamentos` (`nombre`, `piso`, `estado`, `superficie_m2`, `habitaciones`) VALUES
('101', 1, 'ocupado', 65.50, 2),
('102', 1, 'ocupado', 58.00, 2),
('201', 2, 'ocupado', 72.00, 3),
('202', 2, 'ocupado', 68.50, 2),
('301', 3, 'ocupado', 75.00, 3),
('302', 3, 'desocupado', 70.00, 3),
('401', 4, 'desocupado', 80.00, 3),
('402', 4, 'mantenimiento', 78.00, 3);

-- Insertar sensores (agua, luz, gas) para cada departamento ocupado
INSERT INTO `sensores` (`codigo`, `tipo`, `departamento_id`, `ubicacion`, `estado`, `ultima_lectura`) VALUES
-- Departamento 101
('AGUA-101', 'agua', 1, 'Medidor principal', 'activo', NOW()),
('LUZ-101', 'luz', 1, 'Contador eléctrico', 'activo', NOW()),
('GAS-101', 'gas', 1, 'Medidor de gas', 'activo', NOW()),

-- Departamento 102
('AGUA-102', 'agua', 2, 'Medidor principal', 'activo', NOW()),
('LUZ-102', 'luz', 2, 'Contador eléctrico', 'activo', NOW()),
('GAS-102', 'gas', 2, 'Medidor de gas', 'activo', NOW()),

-- Departamento 201
('AGUA-201', 'agua', 3, 'Medidor principal', 'activo', NOW()),
('LUZ-201', 'luz', 3, 'Contador eléctrico', 'activo', NOW()),
('GAS-201', 'gas', 3, 'Medidor de gas', 'activo', NOW()),

-- Departamento 202
('AGUA-202', 'agua', 4, 'Medidor principal', 'activo', NOW()),
('LUZ-202', 'luz', 4, 'Contador eléctrico', 'activo', NOW()),
('GAS-202', 'gas', 4, 'Medidor de gas', 'activo', NOW()),

-- Departamento 301
('AGUA-301', 'agua', 5, 'Medidor principal', 'activo', NOW()),
('LUZ-301', 'luz', 5, 'Contador eléctrico', 'activo', NOW()),
('GAS-301', 'gas', 5, 'Medidor de gas', 'activo', NOW());

-- ============================================
-- Insertar LECTURAS de consumo (últimos 6 meses)
-- Generando datos realistas para visualizar gráficas
-- ============================================

-- Mes 1: Hace 5 meses
INSERT INTO `lecturas` (`sensor_id`, `departamento_id`, `valor`, `unidad`, `recibido_en`, `facturado`) VALUES
-- Agua (m3)
(1, 1, 12.50, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(4, 2, 15.30, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(7, 3, 18.20, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(10, 4, 14.00, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(13, 5, 16.80, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),

-- Luz (kWh)
(2, 1, 250.00, 'kWh', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(5, 2, 280.50, 'kWh', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(8, 3, 320.00, 'kWh', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(11, 4, 290.00, 'kWh', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(14, 5, 305.50, 'kWh', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),

-- Gas (m3)
(3, 1, 45.00, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(6, 2, 52.30, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(9, 3, 60.00, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(12, 4, 48.50, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1),
(15, 5, 55.20, 'm3', DATE_SUB(NOW(), INTERVAL 5 MONTH), 1);

-- Mes 2: Hace 4 meses
INSERT INTO `lecturas` (`sensor_id`, `departamento_id`, `valor`, `unidad`, `recibido_en`, `facturado`) VALUES
(1, 1, 13.20, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(4, 2, 14.80, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(7, 3, 19.00, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(10, 4, 15.50, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(13, 5, 17.30, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),

(2, 1, 265.00, 'kWh', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(5, 2, 295.00, 'kWh', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(8, 3, 340.50, 'kWh', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(11, 4, 300.00, 'kWh', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(14, 5, 315.00, 'kWh', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),

(3, 1, 42.00, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(6, 2, 48.50, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(9, 3, 58.00, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(12, 4, 46.00, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1),
(15, 5, 52.50, 'm3', DATE_SUB(NOW(), INTERVAL 4 MONTH), 1);

-- Mes 3: Hace 3 meses
INSERT INTO `lecturas` (`sensor_id`, `departamento_id`, `valor`, `unidad`, `recibido_en`, `facturado`) VALUES
(1, 1, 14.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(4, 2, 16.20, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(7, 3, 20.50, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(10, 4, 16.80, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(13, 5, 18.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),

(2, 1, 280.00, 'kWh', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(5, 2, 310.00, 'kWh', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(8, 3, 360.00, 'kWh', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(11, 4, 315.50, 'kWh', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(14, 5, 330.00, 'kWh', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),

(3, 1, 38.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(6, 2, 44.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(9, 3, 52.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(12, 4, 42.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1),
(15, 5, 48.00, 'm3', DATE_SUB(NOW(), INTERVAL 3 MONTH), 1);

-- Mes 4: Hace 2 meses
INSERT INTO `lecturas` (`sensor_id`, `departamento_id`, `valor`, `unidad`, `recibido_en`, `facturado`) VALUES
(1, 1, 13.50, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(4, 2, 15.80, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(7, 3, 19.50, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(10, 4, 16.00, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(13, 5, 17.70, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),

(2, 1, 270.00, 'kWh', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(5, 2, 300.00, 'kWh', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(8, 3, 350.00, 'kWh', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(11, 4, 305.00, 'kWh', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(14, 5, 320.00, 'kWh', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),

(3, 1, 40.00, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(6, 2, 46.50, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(9, 3, 55.00, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(12, 4, 44.00, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1),
(15, 5, 50.50, 'm3', DATE_SUB(NOW(), INTERVAL 2 MONTH), 1);

-- Mes 5: Hace 1 mes
INSERT INTO `lecturas` (`sensor_id`, `departamento_id`, `valor`, `unidad`, `recibido_en`, `facturado`) VALUES
(1, 1, 14.80, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(4, 2, 17.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(7, 3, 21.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(10, 4, 17.50, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(13, 5, 19.20, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),

(2, 1, 290.00, 'kWh', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(5, 2, 320.00, 'kWh', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(8, 3, 375.00, 'kWh', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(11, 4, 325.00, 'kWh', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(14, 5, 340.00, 'kWh', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),

(3, 1, 43.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(6, 2, 50.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(9, 3, 59.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(12, 4, 47.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1),
(15, 5, 54.00, 'm3', DATE_SUB(NOW(), INTERVAL 1 MONTH), 1);

-- Mes 6: Mes actual (lecturas recientes, no facturadas)
INSERT INTO `lecturas` (`sensor_id`, `departamento_id`, `valor`, `unidad`, `recibido_en`, `facturado`) VALUES
(1, 1, 15.20, 'm3', NOW(), 0),
(4, 2, 17.80, 'm3', NOW(), 0),
(7, 3, 22.00, 'm3', NOW(), 0),
(10, 4, 18.20, 'm3', NOW(), 0),
(13, 5, 20.00, 'm3', NOW(), 0),

(2, 1, 300.00, 'kWh', NOW(), 0),
(5, 2, 330.00, 'kWh', NOW(), 0),
(8, 3, 390.00, 'kWh', NOW(), 0),
(11, 4, 335.00, 'kWh', NOW(), 0),
(14, 5, 350.00, 'kWh', NOW(), 0),

(3, 1, 46.00, 'm3', NOW(), 0),
(6, 2, 53.00, 'm3', NOW(), 0),
(9, 3, 62.00, 'm3', NOW(), 0),
(12, 4, 50.00, 'm3', NOW(), 0),
(15, 5, 57.00, 'm3', NOW(), 0);

-- ============================================
-- FIN DEL SCRIPT
-- ============================================

SELECT '✅ Tablas de consumos creadas exitosamente!' AS mensaje;
SELECT COUNT(*) AS 'Total Departamentos' FROM departamentos;
SELECT COUNT(*) AS 'Total Sensores' FROM sensores;
SELECT COUNT(*) AS 'Total Lecturas' FROM lecturas;
