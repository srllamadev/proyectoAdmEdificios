-- ============================================
-- Script para INSERTAR DATOS DE CONSUMO
-- Usando las tablas existentes del sistema
-- ============================================

USE edificio_admin;

-- ============================================
-- 1. Insertar datos en DEPARTAMENTOS (si están vacíos)
-- ============================================
INSERT INTO `departamentos` (`nombre`, `piso`, `propietario`) 
VALUES
('101', '1', 'Juan García'),
('102', '1', 'María López'),
('201', '2', 'Carlos Rodríguez'),
('202', '2', 'Ana Martínez'),
('301', '3', 'Pedro Sánchez'),
('302', '3', NULL),
('401', '4', NULL),
('402', '4', NULL)
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Obtener los IDs de los departamentos para usar en las inserciones
SET @dept101 = (SELECT id FROM departamentos WHERE nombre = '101' LIMIT 1);
SET @dept102 = (SELECT id FROM departamentos WHERE nombre = '102' LIMIT 1);
SET @dept201 = (SELECT id FROM departamentos WHERE nombre = '201' LIMIT 1);
SET @dept202 = (SELECT id FROM departamentos WHERE nombre = '202' LIMIT 1);
SET @dept301 = (SELECT id FROM departamentos WHERE nombre = '301' LIMIT 1);

-- ============================================
-- 2. Insertar LECTURAS DE CONSUMO (últimos 6 meses)
-- ============================================

-- Mes actual - 5 meses (AGUA)
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`, `observaciones`)
VALUES
(@dept101, 'agua', 100.00, 112.50, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept102, 'agua', 95.00, 110.30, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept201, 'agua', 105.00, 123.20, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept202, 'agua', 98.00, 112.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept301, 'agua', 110.00, 126.80, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática');

-- Mes actual - 5 meses (LUZ)
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`, `observaciones`)
VALUES
(@dept101, 'luz', 1000.00, 1250.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept102, 'luz', 980.00, 1260.50, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept201, 'luz', 1050.00, 1370.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept202, 'luz', 990.00, 1280.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept301, 'luz', 1100.00, 1405.50, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática');

-- Mes actual - 5 meses (GAS)
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`, `observaciones`)
VALUES
(@dept101, 'gas', 200.00, 245.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept102, 'gas', 190.00, 242.30, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept201, 'gas', 210.00, 270.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept202, 'gas', 195.00, 243.50, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática'),
(@dept301, 'gas', 220.00, 275.20, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m'), 'Lectura automática');

-- Mes actual - 4 meses (AGUA, LUZ, GAS)
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`)
VALUES
(@dept101, 'agua', 112.50, 125.70, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept102, 'agua', 110.30, 125.10, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept201, 'agua', 123.20, 142.20, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept202, 'agua', 112.00, 127.50, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept301, 'agua', 126.80, 144.10, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),

(@dept101, 'luz', 1250.00, 1515.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept102, 'luz', 1260.50, 1555.50, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept201, 'luz', 1370.00, 1710.50, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept202, 'luz', 1280.00, 1580.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept301, 'luz', 1405.50, 1720.50, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),

(@dept101, 'gas', 245.00, 287.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept102, 'gas', 242.30, 290.80, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept201, 'gas', 270.00, 328.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept202, 'gas', 243.50, 289.50, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m')),
(@dept301, 'gas', 275.20, 327.70, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m'));

-- Mes actual - 3 meses
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`)
VALUES
(@dept101, 'agua', 125.70, 139.70, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept102, 'agua', 125.10, 141.30, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept201, 'agua', 142.20, 162.70, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept202, 'agua', 127.50, 144.30, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept301, 'agua', 144.10, 162.10, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),

(@dept101, 'luz', 1515.00, 1795.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept102, 'luz', 1555.50, 1865.50, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept201, 'luz', 1710.50, 2070.50, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept202, 'luz', 1580.00, 1895.50, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept301, 'luz', 1720.50, 2050.50, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),

(@dept101, 'gas', 287.00, 325.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept102, 'gas', 290.80, 334.80, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept201, 'gas', 328.00, 380.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept202, 'gas', 289.50, 331.50, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m')),
(@dept301, 'gas', 327.70, 375.70, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m'));

-- Mes actual - 2 meses
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`)
VALUES
(@dept101, 'agua', 139.70, 153.20, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept102, 'agua', 141.30, 157.10, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept201, 'agua', 162.70, 182.20, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept202, 'agua', 144.30, 160.30, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept301, 'agua', 162.10, 179.80, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),

(@dept101, 'luz', 1795.00, 2065.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept102, 'luz', 1865.50, 2165.50, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept201, 'luz', 2070.50, 2420.50, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept202, 'luz', 1895.50, 2200.50, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept301, 'luz', 2050.50, 2370.50, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),

(@dept101, 'gas', 325.00, 365.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept102, 'gas', 334.80, 381.30, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept201, 'gas', 380.00, 435.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept202, 'gas', 331.50, 375.50, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m')),
(@dept301, 'gas', 375.70, 426.20, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m'));

-- Mes actual - 1 mes
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`)
VALUES
(@dept101, 'agua', 153.20, 168.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept102, 'agua', 157.10, 174.10, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept201, 'agua', 182.20, 203.20, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept202, 'agua', 160.30, 178.80, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept301, 'agua', 179.80, 199.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),

(@dept101, 'luz', 2065.00, 2355.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept102, 'luz', 2165.50, 2485.50, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept201, 'luz', 2420.50, 2795.50, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept202, 'luz', 2200.50, 2525.50, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept301, 'luz', 2370.50, 2710.50, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),

(@dept101, 'gas', 365.00, 408.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept102, 'gas', 381.30, 431.30, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept201, 'gas', 435.00, 494.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept202, 'gas', 375.50, 422.50, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')),
(@dept301, 'gas', 426.20, 480.20, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m'));

-- Mes actual (lecturas recientes)
INSERT INTO `lecturas_consumo` (`departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `fecha_lectura`, `periodo`)
VALUES
(@dept101, 'agua', 168.00, 183.20, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept102, 'agua', 174.10, 191.90, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept201, 'agua', 203.20, 225.20, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept202, 'agua', 178.80, 197.00, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept301, 'agua', 199.00, 219.00, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),

(@dept101, 'luz', 2355.00, 2655.00, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept102, 'luz', 2485.50, 2815.50, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept201, 'luz', 2795.50, 3185.50, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept202, 'luz', 2525.50, 2860.50, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept301, 'luz', 2710.50, 3060.50, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),

(@dept101, 'gas', 408.00, 454.00, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept102, 'gas', 431.30, 484.30, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept201, 'gas', 494.00, 556.00, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept202, 'gas', 422.50, 472.50, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m')),
(@dept301, 'gas', 480.20, 537.20, CURDATE(), DATE_FORMAT(CURDATE(), '%Y-%m'));

-- ============================================
-- RESUMEN DE DATOS INSERTADOS
-- ============================================
SELECT '✅ Datos de consumo insertados exitosamente!' AS mensaje;
SELECT COUNT(*) AS 'Total Lecturas Insertadas' FROM lecturas_consumo;
SELECT tipo_servicio, COUNT(*) AS total FROM lecturas_consumo GROUP BY tipo_servicio;
