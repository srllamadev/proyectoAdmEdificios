-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-10-2025 a las 05:08:02
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `edificio_admin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id` bigint(20) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `sensor_id` int(11) DEFAULT NULL,
  `tipo` enum('consumo_alto','posible_fuga','corte','info') NOT NULL,
  `prioridad` enum('baja','media','alta') DEFAULT 'media',
  `mensaje` varchar(512) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `leido` tinyint(1) DEFAULT 0,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres`
--

CREATE TABLE `alquileres` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inquilino_id` bigint(20) UNSIGNED NOT NULL,
  `numero_departamento` varchar(10) NOT NULL,
  `precio_mensual` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `deposito` decimal(10,2) DEFAULT NULL,
  `estado` enum('activo','finalizado','suspendido') NOT NULL DEFAULT 'activo',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alquileres`
--

INSERT INTO `alquileres` (`id`, `inquilino_id`, `numero_departamento`, `precio_mensual`, `fecha_inicio`, `fecha_fin`, `deposito`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, '101', 1200.00, '2024-01-01', NULL, 2400.00, 'activo', 'Contrato a 2 años', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 2, '201', 1350.00, '2024-02-15', NULL, 2700.00, 'activo', 'Departamento con balcón', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 3, '301', 1180.00, '2024-03-01', NULL, 2360.00, 'activo', 'Piso alto con vista', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 4, '401', 1400.00, '2024-04-10', NULL, 2800.00, 'activo', 'Departamento renovado', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 5, '501', 1250.00, '2024-05-01', NULL, 2500.00, 'activo', 'Último piso disponible', '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anomalias_consumo`
--

CREATE TABLE `anomalias_consumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lectura_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_anomalia` varchar(50) NOT NULL,
  `severidad` enum('baja','media','alta') DEFAULT 'media',
  `descripcion` text NOT NULL,
  `estado` enum('pendiente','revisada','resuelta','falsa_alarma') DEFAULT 'pendiente',
  `resolucion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas_comunes`
--

CREATE TABLE `areas_comunes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `capacidad` int(11) DEFAULT NULL,
  `precio_hora` decimal(8,2) DEFAULT NULL,
  `estado` enum('disponible','mantenimiento','fuera_de_servicio') NOT NULL DEFAULT 'disponible',
  `horario_apertura` time DEFAULT NULL,
  `horario_cierre` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `areas_comunes`
--

INSERT INTO `areas_comunes` (`id`, `nombre`, `descripcion`, `capacidad`, `precio_hora`, `estado`, `horario_apertura`, `horario_cierre`, `created_at`, `updated_at`) VALUES
(1, 'Salón de Eventos', 'Amplio salón para celebraciones y reuniones', 80, 50.00, 'disponible', '08:00:00', '22:00:00', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 'Gimnasio', 'Equipamiento completo para ejercicios', 20, 15.00, 'disponible', '06:00:00', '23:00:00', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 'Piscina', 'Piscina climatizada con zona de descanso', 30, 25.00, 'disponible', '07:00:00', '21:00:00', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 'Terraza BBQ', 'Terraza con parrillas para asados', 15, 20.00, 'disponible', '10:00:00', '20:00:00', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 'Sala de Reuniones', 'Sala equipada para juntas de consorcio', 12, 30.00, 'disponible', '08:00:00', '20:00:00', '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicacion`
--

CREATE TABLE `comunicacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `remitente_id` bigint(20) UNSIGNED NOT NULL,
  `destinatario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `asunto` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('aviso_general','mensaje_personal','notificacion') NOT NULL DEFAULT 'mensaje_personal',
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comunicacion`
--

INSERT INTO `comunicacion` (`id`, `remitente_id`, `destinatario_id`, `asunto`, `mensaje`, `tipo`, `prioridad`, `leido`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Mantenimiento programado de ascensores', 'Se informa que el próximo lunes 7 de octubre se realizará mantenimiento preventivo de los ascensores desde las 9:00 hasta las 17:00 horas.', 'aviso_general', 'alta', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 1, 5, 'Recordatorio de pago', 'Estimada Ana, le recordamos que su pago de alquiler vence el 10 de septiembre. Puede realizar el pago por transferencia bancaria.', 'mensaje_personal', 'media', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 1, NULL, 'Nuevas normas de convivencia', 'Se han actualizado las normas de convivencia del edificio. Pueden consultarlas en la administración o en el sitio web.', 'aviso_general', 'media', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `piso` varchar(50) DEFAULT NULL,
  `propietario` varchar(150) DEFAULT NULL,
  `creado_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `piso`, `propietario`, `creado_at`) VALUES
(1, '101', '1', 'Juan Garc??a', '2025-10-24 22:27:29'),
(2, '102', '1', 'Mar??a L??pez', '2025-10-24 22:27:29'),
(3, '201', '2', 'Carlos Rodr??guez', '2025-10-24 22:27:29'),
(4, '202', '2', 'Ana Mart??nez', '2025-10-24 22:27:29'),
(5, '301', '3', 'Pedro S??nchez', '2025-10-24 22:27:29'),
(6, '302', '3', NULL, '2025-10-24 22:27:29'),
(7, '401', '4', NULL, '2025-10-24 22:27:29'),
(8, '402', '4', NULL, '2025-10-24 22:27:29'),
(9, '101', '1', 'Juan García', '2025-10-24 22:29:45'),
(10, '102', '1', 'María López', '2025-10-24 22:29:45'),
(11, '201', '2', 'Carlos Rodríguez', '2025-10-24 22:29:45'),
(12, '202', '2', 'Ana Martínez', '2025-10-24 22:29:45'),
(13, '301', '3', 'Pedro Sánchez', '2025-10-24 22:29:45'),
(14, '302', '3', NULL, '2025-10-24 22:29:45'),
(15, '401', '4', NULL, '2025-10-24 22:29:45'),
(16, '402', '4', NULL, '2025-10-24 22:29:45'),
(17, '101', '1', 'Juan García', '2025-10-24 22:39:40'),
(18, '102', '1', 'María López', '2025-10-24 22:39:40'),
(19, '201', '2', 'Carlos Rodríguez', '2025-10-24 22:39:40'),
(20, '202', '2', 'Ana Martínez', '2025-10-24 22:39:40'),
(21, '301', '3', 'Pedro Sánchez', '2025-10-24 22:39:40'),
(22, '302', '3', NULL, '2025-10-24 22:39:40'),
(23, '401', '4', NULL, '2025-10-24 22:39:40'),
(24, '402', '4', NULL, '2025-10-24 22:39:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `device_tokens`
--

CREATE TABLE `device_tokens` (
  `id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `identificador` varchar(128) NOT NULL,
  `tipo` enum('medidor','gateway','sensor') NOT NULL DEFAULT 'medidor',
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dni` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) NOT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `user_id`, `dni`, `telefono`, `cargo`, `salario`, `fecha_contratacion`, `estado`, `created_at`, `updated_at`) VALUES
(1, 2, '12345678', '+1234567890', 'Conserje', 2500.00, '2024-01-15', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 3, '23456789', '+1234567891', 'Supervisora de Limpieza', 2800.00, '2024-02-01', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 4, '34567890', '+1234567892', 'Técnico de Mantenimiento', 3200.00, '2024-03-10', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inquilinos`
--

CREATE TABLE `inquilinos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dni` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inquilinos`
--

INSERT INTO `inquilinos` (`id`, `user_id`, `dni`, `telefono`, `direccion`, `fecha_ingreso`, `estado`, `created_at`, `updated_at`) VALUES
(1, 5, '45678901', '+1234567893', 'Departamento 101', '2024-01-01', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 6, '56789012', '+1234567894', 'Departamento 201', '2024-02-15', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 7, '67890123', '+1234567895', 'Departamento 301', '2024-03-01', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 8, '78901234', '+1234567896', 'Departamento 401', '2024-04-10', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 9, '89012345', '+1234567897', 'Departamento 501', '2024-05-01', 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `reference` varchar(64) NOT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue','cancelled') DEFAULT 'pending',
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `invoices`
--

INSERT INTO `invoices` (`id`, `reference`, `resident_id`, `amount`, `due_date`, `status`, `meta`, `created_at`) VALUES
(1, 'INV-D5BCC7AE', 1, 80.00, '2030-11-20', 'pending', NULL, '2025-10-16 16:52:40'),
(2, 'INV-ED54A1C7', 1, 80.00, '2025-11-24', 'pending', NULL, '2025-10-25 00:06:00'),
(3, 'INV-A79840FA', 1, 80.00, '2025-11-24', 'pending', NULL, '2025-10-25 00:07:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT 1,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) GENERATED ALWAYS AS (`qty` * `unit_price`) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas_consumo`
--

CREATE TABLE `lecturas_consumo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `departamento_id` bigint(20) UNSIGNED NOT NULL,
  `tipo_servicio` enum('agua','luz','gas') NOT NULL,
  `lectura_anterior` decimal(10,2) DEFAULT 0.00,
  `lectura_actual` decimal(10,2) NOT NULL,
  `consumo` decimal(10,2) GENERATED ALWAYS AS (`lectura_actual` - `lectura_anterior`) STORED,
  `costo_unitario` decimal(10,2) DEFAULT 0.00,
  `costo_total` decimal(10,2) GENERATED ALWAYS AS (`consumo` * `costo_unitario`) STORED,
  `estado_pago` enum('pendiente','pagado','vencido') DEFAULT 'pendiente',
  `fecha_lectura` date NOT NULL,
  `periodo` varchar(7) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lecturas_consumo`
--

INSERT INTO `lecturas_consumo` (`id`, `departamento_id`, `tipo_servicio`, `lectura_anterior`, `lectura_actual`, `costo_unitario`, `estado_pago`, `fecha_lectura`, `periodo`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 'agua', 100.00, 112.50, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(2, 2, 'agua', 95.00, 110.30, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(3, 3, 'agua', 105.00, 123.20, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(4, 4, 'agua', 98.00, 112.00, 2.50, 'pendiente', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(5, 5, 'agua', 110.00, 126.80, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(6, 1, 'luz', 1000.00, 1250.00, 0.15, 'pendiente', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(7, 2, 'luz', 980.00, 1260.50, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(8, 3, 'luz', 1050.00, 1370.00, 0.15, 'vencido', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(9, 4, 'luz', 990.00, 1280.00, 0.15, 'pendiente', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(10, 5, 'luz', 1100.00, 1405.50, 0.15, 'vencido', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(11, 1, 'gas', 200.00, 245.00, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(12, 2, 'gas', 190.00, 242.30, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(13, 3, 'gas', 210.00, 270.00, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(14, 4, 'gas', 195.00, 243.50, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(15, 5, 'gas', 220.00, 275.20, 1.80, 'pendiente', '2025-05-24', '2025-05', 'Lectura autom??tica', '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(16, 1, 'agua', 112.50, 125.70, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(17, 2, 'agua', 110.30, 125.10, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(18, 3, 'agua', 123.20, 142.20, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(19, 4, 'agua', 112.00, 127.50, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(20, 5, 'agua', 126.80, 144.10, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(21, 1, 'luz', 1250.00, 1515.00, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(22, 2, 'luz', 1260.50, 1555.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(23, 3, 'luz', 1370.00, 1710.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(24, 4, 'luz', 1280.00, 1580.00, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(25, 5, 'luz', 1405.50, 1720.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(26, 1, 'gas', 245.00, 287.00, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(27, 2, 'gas', 242.30, 290.80, 1.80, 'pendiente', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(28, 3, 'gas', 270.00, 328.00, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(29, 4, 'gas', 243.50, 289.50, 1.80, 'pendiente', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(30, 5, 'gas', 275.20, 327.70, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(31, 1, 'agua', 125.70, 139.70, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(32, 2, 'agua', 125.10, 141.30, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(33, 3, 'agua', 142.20, 162.70, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(34, 4, 'agua', 127.50, 144.30, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(35, 5, 'agua', 144.10, 162.10, 2.50, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(36, 1, 'luz', 1515.00, 1795.00, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(37, 2, 'luz', 1555.50, 1865.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(38, 3, 'luz', 1710.50, 2070.50, 0.15, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(39, 4, 'luz', 1580.00, 1895.50, 0.15, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(40, 5, 'luz', 1720.50, 2050.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(41, 1, 'gas', 287.00, 325.00, 1.80, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(42, 2, 'gas', 290.80, 334.80, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(43, 3, 'gas', 328.00, 380.00, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(44, 4, 'gas', 289.50, 331.50, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(45, 5, 'gas', 327.70, 375.70, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(46, 1, 'agua', 139.70, 153.20, 2.50, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(47, 2, 'agua', 141.30, 157.10, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(48, 3, 'agua', 162.70, 182.20, 2.50, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(49, 4, 'agua', 144.30, 160.30, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(50, 5, 'agua', 162.10, 179.80, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(51, 1, 'luz', 1795.00, 2065.00, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(52, 2, 'luz', 1865.50, 2165.50, 0.15, 'vencido', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(53, 3, 'luz', 2070.50, 2420.50, 0.15, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(54, 4, 'luz', 1895.50, 2200.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(55, 5, 'luz', 2050.50, 2370.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(56, 1, 'gas', 325.00, 365.00, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(57, 2, 'gas', 334.80, 381.30, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(58, 3, 'gas', 380.00, 435.00, 1.80, 'vencido', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(59, 4, 'gas', 331.50, 375.50, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(60, 5, 'gas', 375.70, 426.20, 1.80, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(61, 1, 'agua', 153.20, 168.00, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(62, 2, 'agua', 157.10, 174.10, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(63, 3, 'agua', 182.20, 203.20, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(64, 4, 'agua', 160.30, 178.80, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(65, 5, 'agua', 179.80, 199.00, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(66, 1, 'luz', 2065.00, 2355.00, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(67, 2, 'luz', 2165.50, 2485.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(68, 3, 'luz', 2420.50, 2795.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(69, 4, 'luz', 2200.50, 2525.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(70, 5, 'luz', 2370.50, 2710.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(71, 1, 'gas', 365.00, 408.00, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(72, 2, 'gas', 381.30, 431.30, 1.80, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(73, 3, 'gas', 435.00, 494.00, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(74, 4, 'gas', 375.50, 422.50, 1.80, 'pendiente', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(75, 5, 'gas', 426.20, 480.20, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(76, 1, 'agua', 168.00, 183.20, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(77, 2, 'agua', 174.10, 191.90, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(78, 3, 'agua', 203.20, 225.20, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(79, 4, 'agua', 178.80, 197.00, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(80, 5, 'agua', 199.00, 219.00, 2.50, 'pendiente', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(81, 1, 'luz', 2355.00, 2655.00, 0.15, 'pendiente', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:31'),
(82, 2, 'luz', 2485.50, 2815.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(83, 3, 'luz', 2795.50, 3185.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(84, 4, 'luz', 2525.50, 2860.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(85, 5, 'luz', 2710.50, 3060.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(86, 1, 'gas', 408.00, 454.00, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(87, 2, 'gas', 431.30, 484.30, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(88, 3, 'gas', 494.00, 556.00, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(89, 4, 'gas', 422.50, 472.50, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(90, 5, 'gas', 480.20, 537.20, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:27:29', '2025-10-25 02:44:40'),
(91, 1, 'agua', 100.00, 112.50, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(92, 2, 'agua', 95.00, 110.30, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(93, 3, 'agua', 105.00, 123.20, 2.50, 'pendiente', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(94, 4, 'agua', 98.00, 112.00, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(95, 5, 'agua', 110.00, 126.80, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(96, 1, 'luz', 1000.00, 1250.00, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(97, 2, 'luz', 980.00, 1260.50, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(98, 3, 'luz', 1050.00, 1370.00, 0.15, 'pendiente', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(99, 4, 'luz', 990.00, 1280.00, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(100, 5, 'luz', 1100.00, 1405.50, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(101, 1, 'gas', 200.00, 245.00, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(102, 2, 'gas', 190.00, 242.30, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(103, 3, 'gas', 210.00, 270.00, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(104, 4, 'gas', 195.00, 243.50, 1.80, 'vencido', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(105, 5, 'gas', 220.00, 275.20, 1.80, 'vencido', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(106, 1, 'agua', 112.50, 125.70, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(107, 2, 'agua', 110.30, 125.10, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(108, 3, 'agua', 123.20, 142.20, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(109, 4, 'agua', 112.00, 127.50, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(110, 5, 'agua', 126.80, 144.10, 2.50, 'pendiente', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(111, 1, 'luz', 1250.00, 1515.00, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(112, 2, 'luz', 1260.50, 1555.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(113, 3, 'luz', 1370.00, 1710.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(114, 4, 'luz', 1280.00, 1580.00, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(115, 5, 'luz', 1405.50, 1720.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(116, 1, 'gas', 245.00, 287.00, 1.80, 'pendiente', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(117, 2, 'gas', 242.30, 290.80, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(118, 3, 'gas', 270.00, 328.00, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(119, 4, 'gas', 243.50, 289.50, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(120, 5, 'gas', 275.20, 327.70, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(121, 1, 'agua', 125.70, 139.70, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(122, 2, 'agua', 125.10, 141.30, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(123, 3, 'agua', 142.20, 162.70, 2.50, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(124, 4, 'agua', 127.50, 144.30, 2.50, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(125, 5, 'agua', 144.10, 162.10, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(126, 1, 'luz', 1515.00, 1795.00, 0.15, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(127, 2, 'luz', 1555.50, 1865.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(128, 3, 'luz', 1710.50, 2070.50, 0.15, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(129, 4, 'luz', 1580.00, 1895.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(130, 5, 'luz', 1720.50, 2050.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(131, 1, 'gas', 287.00, 325.00, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(132, 2, 'gas', 290.80, 334.80, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(133, 3, 'gas', 328.00, 380.00, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(134, 4, 'gas', 289.50, 331.50, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(135, 5, 'gas', 327.70, 375.70, 1.80, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(136, 1, 'agua', 139.70, 153.20, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(137, 2, 'agua', 141.30, 157.10, 2.50, 'vencido', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(138, 3, 'agua', 162.70, 182.20, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(139, 4, 'agua', 144.30, 160.30, 2.50, 'vencido', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(140, 5, 'agua', 162.10, 179.80, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(141, 1, 'luz', 1795.00, 2065.00, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(142, 2, 'luz', 1865.50, 2165.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(143, 3, 'luz', 2070.50, 2420.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(144, 4, 'luz', 1895.50, 2200.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(145, 5, 'luz', 2050.50, 2370.50, 0.15, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:31'),
(146, 1, 'gas', 325.00, 365.00, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(147, 2, 'gas', 334.80, 381.30, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(148, 3, 'gas', 380.00, 435.00, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(149, 4, 'gas', 331.50, 375.50, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(150, 5, 'gas', 375.70, 426.20, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(151, 1, 'agua', 153.20, 168.00, 2.50, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(152, 2, 'agua', 157.10, 174.10, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(153, 3, 'agua', 182.20, 203.20, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(154, 4, 'agua', 160.30, 178.80, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(155, 5, 'agua', 179.80, 199.00, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(156, 1, 'luz', 2065.00, 2355.00, 0.15, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(157, 2, 'luz', 2165.50, 2485.50, 0.15, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(158, 3, 'luz', 2420.50, 2795.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(159, 4, 'luz', 2200.50, 2525.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(160, 5, 'luz', 2370.50, 2710.50, 0.15, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(161, 1, 'gas', 365.00, 408.00, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(162, 2, 'gas', 381.30, 431.30, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(163, 3, 'gas', 435.00, 494.00, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(164, 4, 'gas', 375.50, 422.50, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(165, 5, 'gas', 426.20, 480.20, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(166, 1, 'agua', 168.00, 183.20, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(167, 2, 'agua', 174.10, 191.90, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(168, 3, 'agua', 203.20, 225.20, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(169, 4, 'agua', 178.80, 197.00, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(170, 5, 'agua', 199.00, 219.00, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(171, 1, 'luz', 2355.00, 2655.00, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(172, 2, 'luz', 2485.50, 2815.50, 0.15, 'vencido', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(173, 3, 'luz', 2795.50, 3185.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(174, 4, 'luz', 2525.50, 2860.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(175, 5, 'luz', 2710.50, 3060.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(176, 1, 'gas', 408.00, 454.00, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(177, 2, 'gas', 431.30, 484.30, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(178, 3, 'gas', 494.00, 556.00, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(179, 4, 'gas', 422.50, 472.50, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(180, 5, 'gas', 480.20, 537.20, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:29:45', '2025-10-25 02:44:40'),
(181, 1, 'agua', 100.00, 112.50, 2.50, 'pendiente', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(182, 2, 'agua', 95.00, 110.30, 2.50, 'pendiente', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(183, 3, 'agua', 105.00, 123.20, 2.50, 'vencido', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(184, 4, 'agua', 98.00, 112.00, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(185, 5, 'agua', 110.00, 126.80, 2.50, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(186, 1, 'luz', 1000.00, 1250.00, 0.15, 'pendiente', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(187, 2, 'luz', 980.00, 1260.50, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(188, 3, 'luz', 1050.00, 1370.00, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(189, 4, 'luz', 990.00, 1280.00, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(190, 5, 'luz', 1100.00, 1405.50, 0.15, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(191, 1, 'gas', 200.00, 245.00, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(192, 2, 'gas', 190.00, 242.30, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(193, 3, 'gas', 210.00, 270.00, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(194, 4, 'gas', 195.00, 243.50, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(195, 5, 'gas', 220.00, 275.20, 1.80, 'pagado', '2025-05-24', '2025-05', 'Lectura automática', '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(196, 1, 'agua', 112.50, 125.70, 2.50, 'pendiente', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(197, 2, 'agua', 110.30, 125.10, 2.50, 'vencido', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(198, 3, 'agua', 123.20, 142.20, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(199, 4, 'agua', 112.00, 127.50, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(200, 5, 'agua', 126.80, 144.10, 2.50, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(201, 1, 'luz', 1250.00, 1515.00, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(202, 2, 'luz', 1260.50, 1555.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(203, 3, 'luz', 1370.00, 1710.50, 0.15, 'pendiente', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(204, 4, 'luz', 1280.00, 1580.00, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(205, 5, 'luz', 1405.50, 1720.50, 0.15, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(206, 1, 'gas', 245.00, 287.00, 1.80, 'vencido', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(207, 2, 'gas', 242.30, 290.80, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(208, 3, 'gas', 270.00, 328.00, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(209, 4, 'gas', 243.50, 289.50, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(210, 5, 'gas', 275.20, 327.70, 1.80, 'pagado', '2025-06-24', '2025-06', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(211, 1, 'agua', 125.70, 139.70, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(212, 2, 'agua', 125.10, 141.30, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(213, 3, 'agua', 142.20, 162.70, 2.50, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(214, 4, 'agua', 127.50, 144.30, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(215, 5, 'agua', 144.10, 162.10, 2.50, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(216, 1, 'luz', 1515.00, 1795.00, 0.15, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(217, 2, 'luz', 1555.50, 1865.50, 0.15, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(218, 3, 'luz', 1710.50, 2070.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(219, 4, 'luz', 1580.00, 1895.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(220, 5, 'luz', 1720.50, 2050.50, 0.15, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(221, 1, 'gas', 287.00, 325.00, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(222, 2, 'gas', 290.80, 334.80, 1.80, 'vencido', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(223, 3, 'gas', 328.00, 380.00, 1.80, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(224, 4, 'gas', 289.50, 331.50, 1.80, 'pagado', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(225, 5, 'gas', 327.70, 375.70, 1.80, 'pendiente', '2025-07-24', '2025-07', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(226, 1, 'agua', 139.70, 153.20, 2.50, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(227, 2, 'agua', 141.30, 157.10, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(228, 3, 'agua', 162.70, 182.20, 2.50, 'vencido', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(229, 4, 'agua', 144.30, 160.30, 2.50, 'vencido', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(230, 5, 'agua', 162.10, 179.80, 2.50, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(231, 1, 'luz', 1795.00, 2065.00, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(232, 2, 'luz', 1865.50, 2165.50, 0.15, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(233, 3, 'luz', 2070.50, 2420.50, 0.15, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(234, 4, 'luz', 1895.50, 2200.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(235, 5, 'luz', 2050.50, 2370.50, 0.15, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(236, 1, 'gas', 325.00, 365.00, 1.80, 'pendiente', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:31'),
(237, 2, 'gas', 334.80, 381.30, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(238, 3, 'gas', 380.00, 435.00, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(239, 4, 'gas', 331.50, 375.50, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(240, 5, 'gas', 375.70, 426.20, 1.80, 'pagado', '2025-08-24', '2025-08', NULL, '2025-10-25 02:39:40', '2025-10-25 02:44:40'),
(241, 1, 'agua', 153.20, 168.00, 2.50, 'pendiente', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:31'),
(242, 2, 'agua', 157.10, 174.10, 2.50, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(243, 3, 'agua', 182.20, 203.20, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(244, 4, 'agua', 160.30, 178.80, 2.50, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(245, 5, 'agua', 179.80, 199.00, 2.50, 'pendiente', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:31'),
(246, 1, 'luz', 2065.00, 2355.00, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(247, 2, 'luz', 2165.50, 2485.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(248, 3, 'luz', 2420.50, 2795.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(249, 4, 'luz', 2200.50, 2525.50, 0.15, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(250, 5, 'luz', 2370.50, 2710.50, 0.15, 'pendiente', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:31'),
(251, 1, 'gas', 365.00, 408.00, 1.80, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(252, 2, 'gas', 381.30, 431.30, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(253, 3, 'gas', 435.00, 494.00, 1.80, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(254, 4, 'gas', 375.50, 422.50, 1.80, 'vencido', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(255, 5, 'gas', 426.20, 480.20, 1.80, 'pagado', '2025-09-24', '2025-09', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(256, 1, 'agua', 168.00, 183.20, 2.50, 'vencido', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(257, 2, 'agua', 174.10, 191.90, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(258, 3, 'agua', 203.20, 225.20, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(259, 4, 'agua', 178.80, 197.00, 2.50, 'vencido', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(260, 5, 'agua', 199.00, 219.00, 2.50, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(261, 1, 'luz', 2355.00, 2655.00, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(262, 2, 'luz', 2485.50, 2815.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(263, 3, 'luz', 2795.50, 3185.50, 0.15, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(264, 4, 'luz', 2525.50, 2860.50, 0.15, 'vencido', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(265, 5, 'luz', 2710.50, 3060.50, 0.15, 'vencido', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(266, 1, 'gas', 408.00, 454.00, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(267, 2, 'gas', 431.30, 484.30, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(268, 3, 'gas', 494.00, 556.00, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(269, 4, 'gas', 422.50, 472.50, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40'),
(270, 5, 'gas', 480.20, 537.20, 1.80, 'pagado', '2025-10-24', '2025-10', NULL, '2025-10-25 02:39:41', '2025-10-25 02:44:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `alquiler_id` bigint(20) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `estado` enum('pendiente','pagado','vencido') NOT NULL DEFAULT 'pendiente',
  `descripcion` text DEFAULT NULL,
  `recargo` decimal(8,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `alquiler_id`, `monto`, `fecha_vencimiento`, `fecha_pago`, `metodo_pago`, `estado`, `descripcion`, `recargo`, `created_at`, `updated_at`) VALUES
(1, 1, 1200.00, '2024-08-10', '2024-08-08', 'Transferencia', 'pagado', 'Alquiler Agosto 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 1, 1200.00, '2024-09-10', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 2, 1350.00, '2024-08-15', '2024-08-14', 'Efectivo', 'pagado', 'Alquiler Agosto 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 2, 1350.00, '2024-09-15', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 3, 1180.00, '2024-08-01', '2024-07-30', 'Débito automático', 'pagado', 'Alquiler Agosto 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(6, 3, 1180.00, '2024-09-01', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(7, 4, 1400.00, '2024-08-10', '2024-08-09', 'Transferencia', 'pagado', 'Alquiler Agosto 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(8, 4, 1400.00, '2024-09-10', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(9, 5, 1250.00, '2024-08-01', '2024-07-28', 'Efectivo', 'pagado', 'Alquiler Agosto 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(10, 5, 1250.00, '2024-09-01', NULL, NULL, 'pendiente', 'Alquiler Septiembre 2024', 0.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `tx_ref` varchar(128) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `period` varchar(20) NOT NULL,
  `gross` decimal(12,2) NOT NULL,
  `deductions` decimal(12,2) DEFAULT 0.00,
  `net` decimal(12,2) GENERATED ALWAYS AS (`gross` - `deductions`) VIRTUAL,
  `paid` tinyint(1) DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inquilino_id` bigint(20) UNSIGNED NOT NULL,
  `area_comun_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `descripcion` text DEFAULT NULL,
  `precio_total` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `inquilino_id`, `area_comun_id`, `fecha_inicio`, `fecha_fin`, `estado`, `descripcion`, `precio_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2024-10-15 18:00:00', '2024-10-15 22:00:00', 'confirmada', 'Cumpleaños familiar', 200.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 2, 2, '2024-10-20 07:00:00', '2024-10-20 08:00:00', 'confirmada', 'Rutina de ejercicios matutina', 15.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 3, 3, '2024-10-25 14:00:00', '2024-10-25 17:00:00', 'pendiente', 'Reunión de amigos', 75.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 4, 4, '2024-11-01 12:00:00', '2024-11-01 16:00:00', 'confirmada', 'Asado familiar', 80.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 5, 5, '2024-11-05 19:00:00', '2024-11-05 21:00:00', 'pendiente', 'Reunión de consorcio', 60.00, '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_logs`
--

CREATE TABLE `security_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `security_logs`
--

INSERT INTO `security_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, 1, 'account_created', NULL, NULL, 'Cuenta de administrador creada durante setup inicial', '2025-10-16 13:34:01'),
(2, 2, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:34:01'),
(3, 3, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:34:01'),
(4, 4, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:34:01'),
(5, 5, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:34:01'),
(6, 6, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:34:01'),
(7, 7, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:34:01'),
(8, 8, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:34:01'),
(9, 9, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:34:01'),
(10, 1, 'account_created', NULL, NULL, 'Cuenta de administrador creada durante setup inicial', '2025-10-16 13:40:50'),
(11, 2, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:40:50'),
(12, 3, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:40:50'),
(13, 4, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:40:50'),
(14, 5, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:40:50'),
(15, 6, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:40:50'),
(16, 7, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:40:50'),
(17, 8, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:40:50'),
(18, 9, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:40:50'),
(19, 1, 'account_created', NULL, NULL, 'Cuenta de administrador creada durante setup inicial', '2025-10-16 13:43:23'),
(20, 2, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:43:23'),
(21, 3, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:43:23'),
(22, 4, 'account_created', NULL, NULL, 'Cuenta de empleado creada durante setup inicial', '2025-10-16 13:43:23'),
(23, 5, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:43:23'),
(24, 6, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:43:23'),
(25, 7, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:43:23'),
(26, 8, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:43:23'),
(27, 9, 'account_created', NULL, NULL, 'Cuenta de inquilino creada durante setup inicial', '2025-10-16 13:43:23'),
(28, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:45:50'),
(29, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:46:41'),
(30, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:47:18'),
(31, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:47:45'),
(32, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:49:20'),
(33, 2, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:50:21'),
(34, 2, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:50:29'),
(35, 2, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:50:35'),
(36, 2, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 13:50:41'),
(37, 10, 'account_created', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', 'Cuenta creada mediante registro', '2025-10-16 13:59:40'),
(38, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:06:00'),
(39, NULL, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:06:27'),
(40, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:07:29'),
(41, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:07:40'),
(42, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:08:56'),
(43, 2, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:10:20'),
(44, 10, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-16 14:11:08'),
(45, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:13:39'),
(46, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-16 14:18:40'),
(47, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-16 14:19:06'),
(48, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-16 16:49:00'),
(49, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-18 01:34:21'),
(50, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-18 01:38:00'),
(51, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-23 05:05:49'),
(52, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-25 00:05:46'),
(53, 1, 'password_reset_requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Solicitud de recuperación de contraseña', '2025-10-25 00:53:00'),
(54, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:14:46'),
(55, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:14:59'),
(56, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:15:05'),
(57, 1, 'security_notification_sent', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Notificación de 3 intentos fallidos enviada', '2025-10-25 01:15:05'),
(58, 1, 'password_reset_requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Solicitud de recuperación de contraseña', '2025-10-25 01:20:02'),
(59, 1, 'password_reset_requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Solicitud de recuperación de contraseña', '2025-10-25 01:23:08'),
(60, 1, 'password_reset_requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Solicitud de recuperación de contraseña', '2025-10-25 01:23:44'),
(61, 1, 'password_reset_requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Solicitud de recuperación de contraseña', '2025-10-25 01:25:49'),
(62, 1, 'password_reset_requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Solicitud de recuperación de contraseña', '2025-10-25 01:26:18'),
(63, 1, 'password_reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'Contraseña cambiada mediante token de recuperación', '2025-10-25 01:33:38'),
(64, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:33:55'),
(65, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-25 01:34:07'),
(66, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:34:20'),
(67, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:34:26'),
(68, 1, 'failed_login_attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Intento fallido desde IP: ::1', '2025-10-25 01:34:35'),
(69, 1, 'security_notification_sent', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Notificación de 3 intentos fallidos enviada', '2025-10-25 01:34:35'),
(70, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-25 01:35:42'),
(71, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-25 02:38:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sensores`
--

CREATE TABLE `sensores` (
  `id` int(11) NOT NULL,
  `dispositivo_id` int(11) NOT NULL,
  `canal` varchar(64) NOT NULL,
  `tipo` enum('agua','luz','gas') NOT NULL,
  `unidad` varchar(16) NOT NULL DEFAULT 'kWh',
  `descripcion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_mensual` decimal(8,2) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio_mensual`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Agua', 'Servicio de agua potable', 85.00, 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 'Electricidad', 'Suministro eléctrico', 120.00, 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 'Gas Natural', 'Servicio de gas para calefacción y cocina', 95.00, 'activo', '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empleado_id` bigint(20) UNSIGNED NOT NULL,
  `asignado_por` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_asignacion` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completada') NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `empleado_id`, `asignado_por`, `titulo`, `descripcion`, `fecha_asignacion`, `fecha_vencimiento`, `estado`, `prioridad`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Revisar sistema de iluminación del hall', 'Verificar y cambiar bombillas quemadas en el hall principal', '2024-09-25', '2024-09-30', 'pendiente', 'media', NULL, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 2, 1, 'Limpieza profunda del gimnasio', 'Realizar limpieza completa de equipos y espejos del gimnasio', '2024-09-26', '2024-09-29', 'en_progreso', 'alta', 'Coordinar con inquilinos que usan el gimnasio', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 3, 1, 'Reparar grifo del área de BBQ', 'Cambiar grifo dañado en la terraza de asados', '2024-09-27', '2024-10-02', 'pendiente', 'alta', 'Comprar repuestos necesarios', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 1, 1, 'Inspección mensual de extintores', 'Verificar fecha de vencimiento y estado de todos los extintores', '2024-09-28', '2024-10-05', 'pendiente', 'alta', NULL, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 2, 1, 'Organizar depósito de limpieza', 'Reorganizar y hacer inventario de productos de limpieza', '2024-09-29', '2024-10-10', 'pendiente', 'baja', NULL, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(6, 3, 1, 'Mantenimiento preventivo de bombas de agua', 'Revisión y lubricación de bombas en sala de máquinas', '2024-09-30', '2024-10-07', 'pendiente', 'media', 'Coordinar con empresa de mantenimiento', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(7, 1, 1, 'Pintura de barandas del estacionamiento', 'Lijar y pintar barandas oxidadas del subsuelo', '2024-10-01', '2024-10-15', 'pendiente', 'media', 'Necesita pintura antióxido', '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(8, 2, 1, 'Limpieza de vidrios fachada principal', 'Limpiar cristales de la entrada principal del edificio', '2024-10-02', '2024-10-08', 'pendiente', 'media', 'Usar equipos de seguridad para altura', '2025-10-16 07:47:16', '2025-10-16 07:47:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `type` enum('income','expense','payout') NOT NULL,
  `reference` varchar(128) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `category` varchar(64) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `umbrales`
--

CREATE TABLE `umbrales` (
  `id` int(11) NOT NULL,
  `sensor_id` int(11) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `tipo_alerta` enum('consumo_alto','posible_fuga','corte') NOT NULL,
  `valor` double NOT NULL,
  `ventana_minutos` int(11) DEFAULT 60,
  `activo` tinyint(1) DEFAULT 1,
  `creado_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','empleado','inquilino') NOT NULL DEFAULT 'inquilino',
  `remember_token` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_failed_login` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `account_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `password_reset_token`, `password_reset_expires`, `failed_login_attempts`, `locked_until`, `last_failed_login`, `password_changed_at`, `account_locked`, `created_at`, `updated_at`, `last_login_at`, `reset_token`, `reset_token_expires`) VALUES
(1, 'Administrador Principal', 'admin@admin.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$MzN1UXVXZ05rRS5GZEFmNg$JAHqnUPkkvysQZFCLGwp4nf77G3BIzBYjXl/u40EJGE', 'admin', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-25 01:33:38', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(2, 'Carlos Mendoza', 'empleado1@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$NnQ0YzNhWGdIMHhwMDJzdg$xpaGbHF1uIwIh+AaaPxwYF0/J3p0JWBlal33hT5RJnA', 'empleado', NULL, NULL, NULL, 5, '2025-10-16 14:25:20', '2025-10-16 14:10:20', '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(3, 'María González', 'empleado2@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$a2l2RUYucTdSd0lCVzVZYQ$BLtrLFMDlKm28YpJw3Ak/bM5Zt3X7CTU06livBTBpO4', 'empleado', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(4, 'Luis Rodríguez', 'empleado3@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$VC56ZGpqSk4zQ2FmaUZ4cg$0g5UNEW6yf2+SHZzi8J3vLNEzquRXOdCKJcSBTWgQ8s', 'empleado', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(5, 'Ana Pérez', 'inquilino1@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$UXQ4LzNZWFd0Q2tneEZwZQ$z2IgBbhekk0g7jTksK56naB9BebCtKZDZVVnZP23BSQ', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(6, 'Roberto Silva', 'inquilino2@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$TjRGTmk1Y2tSNmZnTHF6aA$tajnmhcSBLr15Je3KG9tqE6Lo9PklWgTDdJ/IM0fAdg', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(7, 'Laura Martínez', 'inquilino3@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$VG8zYlJWMjZCZnlTMzJybA$lsJ3uALR7EenteBYX65YWvj5CFEckPvIar3ASyK3KLQ', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(8, 'Diego Torres', 'inquilino4@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$dE9zR0lzbklOYTZpYmFIeQ$V5f1ME9KBouvZ6eXNMBy4Rhpp5dbHzLeZyHvLag0adY', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(9, 'Carmen López', 'inquilino5@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$Y2QySXZmOEE2eUdzYk41OQ$mgPt+zoAHHBddVSQ/LflimukDzvwLLSnF9EdeFHdD1s', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16', NULL, NULL, NULL),
(10, 'UUUU', 'uuuu@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=3$VHVIZzdmRWx6a3VEd3VQaA$UvEpZVtIWz2nMJoLoS2zew91czkpME/3b9zYIH8gOug', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 13:59:40', 0, '2025-10-16 13:59:40', '2025-10-16 13:59:40', NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `sensor_id` (`sensor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
