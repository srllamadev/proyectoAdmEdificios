-- ===================================================================
-- SISTEMA DE ADMINISTRACIÓN DE EDIFICIOS
-- Base de datos completa con datos de prueba
-- Archivo para importar en phpMyAdmin
-- ===================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `edificio_admin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `edificio_admin`;

-- ===================================================================
-- ESTRUCTURA DE TABLAS
-- ===================================================================

-- Tabla: users
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','empleado','inquilino') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inquilino',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: personal_access_tokens
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: inquilinos
CREATE TABLE `inquilinos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dni` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inquilinos_dni_unique` (`dni`),
  KEY `inquilinos_user_id_foreign` (`user_id`),
  CONSTRAINT `inquilinos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: empleados
CREATE TABLE `empleados` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dni` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleados_dni_unique` (`dni`),
  KEY `empleados_user_id_foreign` (`user_id`),
  CONSTRAINT `empleados_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: areas_comunes
CREATE TABLE `areas_comunes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `precio_hora` decimal(8,2) DEFAULT NULL,
  `estado` enum('disponible','mantenimiento','fuera_de_servicio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `horario_apertura` time DEFAULT NULL,
  `horario_cierre` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: servicios
CREATE TABLE `servicios` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_mensual` decimal(8,2) NOT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: alquileres
CREATE TABLE `alquileres` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inquilino_id` bigint(20) UNSIGNED NOT NULL,
  `numero_departamento` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio_mensual` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `deposito` decimal(10,2) DEFAULT NULL,
  `estado` enum('activo','finalizado','suspendido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `observaciones` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alquileres_inquilino_id_foreign` (`inquilino_id`),
  CONSTRAINT `alquileres_inquilino_id_foreign` FOREIGN KEY (`inquilino_id`) REFERENCES `inquilinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: reservas
CREATE TABLE `reservas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inquilino_id` bigint(20) UNSIGNED NOT NULL,
  `area_comun_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_total` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservas_inquilino_id_foreign` (`inquilino_id`),
  KEY `reservas_area_comun_id_foreign` (`area_comun_id`),
  CONSTRAINT `reservas_inquilino_id_foreign` FOREIGN KEY (`inquilino_id`) REFERENCES `inquilinos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_area_comun_id_foreign` FOREIGN KEY (`area_comun_id`) REFERENCES `areas_comunes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: pagos
CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `alquiler_id` bigint(20) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `metodo_pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('pendiente','pagado','vencido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recargo` decimal(8,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_alquiler_id_foreign` (`alquiler_id`),
  CONSTRAINT `pagos_alquiler_id_foreign` FOREIGN KEY (`alquiler_id`) REFERENCES `alquileres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: comunicacion
CREATE TABLE `comunicacion` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `remitente_id` bigint(20) UNSIGNED NOT NULL,
  `destinatario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `asunto` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('aviso_general','mensaje_personal','notificacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensaje_personal',
  `prioridad` enum('baja','media','alta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `leido` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comunicacion_remitente_id_foreign` (`remitente_id`),
  KEY `comunicacion_destinatario_id_foreign` (`destinatario_id`),
  CONSTRAINT `comunicacion_remitente_id_foreign` FOREIGN KEY (`remitente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comunicacion_destinatario_id_foreign` FOREIGN KEY (`destinatario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tareas
CREATE TABLE `tareas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint(20) UNSIGNED NOT NULL,
  `asignado_por` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_asignacion` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `observaciones` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tareas_empleado_id_foreign` (`empleado_id`),
  KEY `tareas_asignado_por_foreign` (`asignado_por`),
  CONSTRAINT `tareas_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tareas_asignado_por_foreign` FOREIGN KEY (`asignado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- DATOS DE PRUEBA
-- ===================================================================

-- Insertar usuarios
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador Principal', 'admin@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NOW(), NOW()),
(2, 'Carlos Mendoza', 'empleado1@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', NULL, NOW(), NOW()),
(3, 'María González', 'empleado2@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', NULL, NOW(), NOW()),
(4, 'Luis Rodríguez', 'empleado3@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', NULL, NOW(), NOW()),
(5, 'Ana Pérez', 'inquilino1@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inquilino', NULL, NOW(), NOW()),
(6, 'Roberto Silva', 'inquilino2@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inquilino', NULL, NOW(), NOW()),
(7, 'Laura Martínez', 'inquilino3@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inquilino', NULL, NOW(), NOW()),
(8, 'Diego Torres', 'inquilino4@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inquilino', NULL, NOW(), NOW()),
(9, 'Carmen López', 'inquilino5@edificio.com', NOW(), '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inquilino', NULL, NOW(), NOW());

-- Insertar empleados
INSERT INTO `empleados` (`id`, `user_id`, `dni`, `telefono`, `cargo`, `salario`, `fecha_contratacion`, `estado`, `created_at`, `updated_at`) VALUES
(1, 2, '12345678', '+1234567890', 'Conserje', 2500.00, '2024-01-15', 'activo', NOW(), NOW()),
(2, 3, '23456789', '+1234567891', 'Supervisora de Limpieza', 2800.00, '2024-02-01', 'activo', NOW(), NOW()),
(3, 4, '34567890', '+1234567892', 'Técnico de Mantenimiento', 3200.00, '2024-03-10', 'activo', NOW(), NOW());

-- Insertar inquilinos
INSERT INTO `inquilinos` (`id`, `user_id`, `dni`, `telefono`, `direccion`, `fecha_ingreso`, `estado`, `created_at`, `updated_at`) VALUES
(1, 5, '45678901', '+1234567893', 'Departamento 101', '2024-01-01', 'activo', NOW(), NOW()),
(2, 6, '56789012', '+1234567894', 'Departamento 201', '2024-02-15', 'activo', NOW(), NOW()),
(3, 7, '67890123', '+1234567895', 'Departamento 301', '2024-03-01', 'activo', NOW(), NOW()),
(4, 8, '78901234', '+1234567896', 'Departamento 401', '2024-04-10', 'activo', NOW(), NOW()),
(5, 9, '89012345', '+1234567897', 'Departamento 501', '2024-05-01', 'activo', NOW(), NOW());

-- Insertar áreas comunes
INSERT INTO `areas_comunes` (`id`, `nombre`, `descripcion`, `capacidad`, `precio_hora`, `estado`, `horario_apertura`, `horario_cierre`, `created_at`, `updated_at`) VALUES
(1, 'Salón de Eventos', 'Amplio salón para celebraciones y reuniones', 80, 50.00, 'disponible', '08:00:00', '22:00:00', NOW(), NOW()),
(2, 'Gimnasio', 'Equipamiento completo para ejercicios', 20, 15.00, 'disponible', '06:00:00', '23:00:00', NOW(), NOW()),
(3, 'Piscina', 'Piscina climatizada con zona de descanso', 30, 25.00, 'disponible', '07:00:00', '21:00:00', NOW(), NOW()),
(4, 'Terraza BBQ', 'Terraza con parrillas para asados', 15, 20.00, 'disponible', '10:00:00', '20:00:00', NOW(), NOW()),
(5, 'Sala de Reuniones', 'Sala equipada para juntas de consorcio', 12, 30.00, 'disponible', '08:00:00', '20:00:00', NOW(), NOW());

-- Insertar servicios
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio_mensual`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Agua', 'Servicio de agua potable', 85.00, 'activo', NOW(), NOW()),
(2, 'Electricidad', 'Suministro eléctrico', 120.00, 'activo', NOW(), NOW()),
(3, 'Gas Natural', 'Servicio de gas para calefacción y cocina', 95.00, 'activo', NOW(), NOW());

-- Insertar alquileres
INSERT INTO `alquileres` (`id`, `inquilino_id`, `numero_departamento`, `precio_mensual`, `fecha_inicio`, `fecha_fin`, `deposito`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, '101', 1200.00, '2024-01-01', NULL, 2400.00, 'activo', 'Contrato a 2 años', NOW(), NOW()),
(2, 2, '201', 1350.00, '2024-02-15', NULL, 2700.00, 'activo', 'Departamento con balcón', NOW(), NOW()),
(3, 3, '301', 1180.00, '2024-03-01', NULL, 2360.00, 'activo', 'Piso alto con vista', NOW(), NOW()),
(4, 4, '401', 1400.00, '2024-04-10', NULL, 2800.00, 'activo', 'Departamento renovado', NOW(), NOW()),
(5, 5, '501', 1250.00, '2024-05-01', NULL, 2500.00, 'activo', 'Último piso disponible', NOW(), NOW());

-- Insertar pagos
INSERT INTO `pagos` (`id`, `alquiler_id`, `monto`, `fecha_vencimiento`, `fecha_pago`, `metodo_pago`, `estado`, `descripcion`, `recargo`, `created_at`, `updated_at`) VALUES
(1, 1, 1200.00, '2024-08-10', '2024-08-08', 'Transferencia', 'pagado', 'Alquiler Agosto 2024', 0.00, NOW(), NOW()),
(2, 1, 1200.00, '2024-09-10', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, NOW(), NOW()),
(3, 2, 1350.00, '2024-08-15', '2024-08-14', 'Efectivo', 'pagado', 'Alquiler Agosto 2024', 0.00, NOW(), NOW()),
(4, 2, 1350.00, '2024-09-15', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, NOW(), NOW()),
(5, 3, 1180.00, '2024-08-01', '2024-07-30', 'Débito automático', 'pagado', 'Alquiler Agosto 2024', 0.00, NOW(), NOW()),
(6, 3, 1180.00, '2024-09-01', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, NOW(), NOW()),
(7, 4, 1400.00, '2024-08-10', '2024-08-09', 'Transferencia', 'pagado', 'Alquiler Agosto 2024', 0.00, NOW(), NOW()),
(8, 4, 1400.00, '2024-09-10', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, NOW(), NOW()),
(9, 5, 1250.00, '2024-08-01', '2024-07-28', 'Efectivo', 'pagado', 'Alquiler Agosto 2024', 0.00, NOW(), NOW()),
(10, 5, 1250.00, '2024-09-01', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, NOW(), NOW());

-- Insertar reservas
INSERT INTO `reservas` (`id`, `inquilino_id`, `area_comun_id`, `fecha_inicio`, `fecha_fin`, `estado`, `descripcion`, `precio_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2024-10-15 18:00:00', '2024-10-15 22:00:00', 'confirmada', 'Cumpleaños familiar', 200.00, NOW(), NOW()),
(2, 2, 2, '2024-10-20 07:00:00', '2024-10-20 08:00:00', 'confirmada', 'Rutina de ejercicios matutina', 15.00, NOW(), NOW()),
(3, 3, 3, '2024-10-25 14:00:00', '2024-10-25 17:00:00', 'pendiente', 'Reunión de amigos', 75.00, NOW(), NOW()),
(4, 4, 4, '2024-11-01 12:00:00', '2024-11-01 16:00:00', 'confirmada', 'Asado familiar', 80.00, NOW(), NOW()),
(5, 5, 5, '2024-11-05 19:00:00', '2024-11-05 21:00:00', 'pendiente', 'Reunión de consorcio', 60.00, NOW(), NOW());

-- Insertar comunicaciones
INSERT INTO `comunicacion` (`id`, `remitente_id`, `destinatario_id`, `asunto`, `mensaje`, `tipo`, `prioridad`, `leido`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Mantenimiento programado de ascensores', 'Se informa que el próximo lunes 7 de octubre se realizará mantenimiento preventivo de los ascensores desde las 9:00 hasta las 17:00 horas.', 'aviso_general', 'alta', 0, NOW(), NOW()),
(2, 1, 5, 'Recordatorio de pago', 'Estimada Ana, le recordamos que su pago de alquiler vence el 10 de septiembre. Puede realizar el pago por transferencia bancaria.', 'mensaje_personal', 'media', 0, NOW(), NOW()),
(3, 1, NULL, 'Nuevas normas de convivencia', 'Se han actualizado las normas de convivencia del edificio. Pueden consultarlas en la administración o en el sitio web.', 'aviso_general', 'media', 0, NOW(), NOW());

-- Insertar tareas
INSERT INTO `tareas` (`id`, `empleado_id`, `asignado_por`, `titulo`, `descripcion`, `fecha_asignacion`, `fecha_vencimiento`, `estado`, `prioridad`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Revisar sistema de iluminación del hall', 'Verificar y cambiar bombillas quemadas en el hall principal', '2024-09-25', '2024-09-30', 'pendiente', 'media', NULL, NOW(), NOW()),
(2, 2, 1, 'Limpieza profunda del gimnasio', 'Realizar limpieza completa de equipos y espejos del gimnasio', '2024-09-26', '2024-09-29', 'en_progreso', 'alta', 'Coordinar con inquilinos que usan el gimnasio', NOW(), NOW()),
(3, 3, 1, 'Reparar grifo del área de BBQ', 'Cambiar grifo dañado en la terraza de asados', '2024-09-27', '2024-10-02', 'pendiente', 'alta', 'Comprar repuestos necesarios', NOW(), NOW()),
(4, 1, 1, 'Inspección mensual de extintores', 'Verificar fecha de vencimiento y estado de todos los extintores', '2024-09-28', '2024-10-05', 'pendiente', 'alta', NULL, NOW(), NOW()),
(5, 2, 1, 'Organizar depósito de limpieza', 'Reorganizar y hacer inventario de productos de limpieza', '2024-09-29', '2024-10-10', 'pendiente', 'baja', NULL, NOW(), NOW()),
(6, 3, 1, 'Mantenimiento preventivo de bombas de agua', 'Revisión y lubricación de bombas en sala de máquinas', '2024-09-30', '2024-10-07', 'pendiente', 'media', 'Coordinar con empresa de mantenimiento', NOW(), NOW()),
(7, 1, 1, 'Pintura de barandas del estacionamiento', 'Lijar y pintar barandas oxidadas del subsuelo', '2024-10-01', '2024-10-15', 'pendiente', 'media', 'Necesita pintura antióxido', NOW(), NOW()),
(8, 2, 1, 'Limpieza de vidrios fachada principal', 'Limpiar cristales de la entrada principal del edificio', '2024-10-02', '2024-10-08', 'pendiente', 'media', 'Usar equipos de seguridad para altura', NOW(), NOW());

-- ===================================================================
-- COMMITS Y CONFIGURACIONES FINALES
-- ===================================================================

-- Autoincrement ajustes
ALTER TABLE `users` AUTO_INCREMENT = 10;
ALTER TABLE `inquilinos` AUTO_INCREMENT = 6;
ALTER TABLE `empleados` AUTO_INCREMENT = 4;
ALTER TABLE `areas_comunes` AUTO_INCREMENT = 6;
ALTER TABLE `servicios` AUTO_INCREMENT = 4;
ALTER TABLE `alquileres` AUTO_INCREMENT = 6;
ALTER TABLE `reservas` AUTO_INCREMENT = 6;
ALTER TABLE `pagos` AUTO_INCREMENT = 11;
ALTER TABLE `comunicacion` AUTO_INCREMENT = 4;
ALTER TABLE `tareas` AUTO_INCREMENT = 9;

COMMIT;

-- ===================================================================
-- INFORMACIÓN DE USUARIOS DE PRUEBA
-- ===================================================================
-- 
-- TODOS LOS USUARIOS TIENEN LA CONTRASEÑA: password
-- 
-- ADMINISTRADOR:
-- Email: admin@edificio.com
-- Contraseña: password
-- 
-- EMPLEADOS:
-- Email: empleado1@edificio.com (Carlos Mendoza - Conserje)
-- Email: empleado2@edificio.com (María González - Supervisora)
-- Email: empleado3@edificio.com (Luis Rodríguez - Técnico)
-- Contraseña: password
-- 
-- INQUILINOS:
-- Email: inquilino1@edificio.com (Ana Pérez - Depto 101)
-- Email: inquilino2@edificio.com (Roberto Silva - Depto 201)
-- Email: inquilino3@edificio.com (Laura Martínez - Depto 301)
-- Email: inquilino4@edificio.com (Diego Torres - Depto 401)
-- Email: inquilino5@edificio.com (Carmen López - Depto 501)
-- Contraseña: password
-- 
-- ===================================================================