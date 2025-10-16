-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2025 a las 19:08:37
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
(1, 'INV-D5BCC7AE', 1, 80.00, '2030-11-20', 'pending', NULL, '2025-10-16 16:52:40');

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
-- Estructura de tabla para la tabla `lecturas`
--

CREATE TABLE `lecturas` (
  `id` bigint(20) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `valor` double NOT NULL,
  `tipo` enum('instantaneo','acumulado') NOT NULL DEFAULT 'instantaneo',
  `recibido_en` datetime NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `procesado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(48, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Login exitoso', '2025-10-16 16:49:00');

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `password_reset_token`, `password_reset_expires`, `failed_login_attempts`, `locked_until`, `last_failed_login`, `password_changed_at`, `account_locked`, `created_at`, `updated_at`) VALUES
(1, 'Administrador Principal', 'admin@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$ZldIZDJlZ0Y2YVM1SlVMQQ$Y+22NM0518cnyrHd3u0B2xBeERT6nTJr3bDJ4/yO0YU', 'admin', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(2, 'Carlos Mendoza', 'empleado1@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$NnQ0YzNhWGdIMHhwMDJzdg$xpaGbHF1uIwIh+AaaPxwYF0/J3p0JWBlal33hT5RJnA', 'empleado', NULL, NULL, NULL, 5, '2025-10-16 14:25:20', '2025-10-16 14:10:20', '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(3, 'María González', 'empleado2@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$a2l2RUYucTdSd0lCVzVZYQ$BLtrLFMDlKm28YpJw3Ak/bM5Zt3X7CTU06livBTBpO4', 'empleado', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(4, 'Luis Rodríguez', 'empleado3@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$VC56ZGpqSk4zQ2FmaUZ4cg$0g5UNEW6yf2+SHZzi8J3vLNEzquRXOdCKJcSBTWgQ8s', 'empleado', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(5, 'Ana Pérez', 'inquilino1@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$UXQ4LzNZWFd0Q2tneEZwZQ$z2IgBbhekk0g7jTksK56naB9BebCtKZDZVVnZP23BSQ', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(6, 'Roberto Silva', 'inquilino2@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$TjRGTmk1Y2tSNmZnTHF6aA$tajnmhcSBLr15Je3KG9tqE6Lo9PklWgTDdJ/IM0fAdg', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(7, 'Laura Martínez', 'inquilino3@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$VG8zYlJWMjZCZnlTMzJybA$lsJ3uALR7EenteBYX65YWvj5CFEckPvIar3ASyK3KLQ', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(8, 'Diego Torres', 'inquilino4@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$dE9zR0lzbklOYTZpYmFIeQ$V5f1ME9KBouvZ6eXNMBy4Rhpp5dbHzLeZyHvLag0adY', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(9, 'Carmen López', 'inquilino5@edificio.com', '2025-10-16 07:47:16', '$argon2id$v=19$m=65536,t=4,p=3$Y2QySXZmOEE2eUdzYk41OQ$mgPt+zoAHHBddVSQ/LflimukDzvwLLSnF9EdeFHdD1s', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 07:47:16', 0, '2025-10-16 07:47:16', '2025-10-16 07:47:16'),
(10, 'UUUU', 'uuuu@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=3$VHVIZzdmRWx6a3VEd3VQaA$UvEpZVtIWz2nMJoLoS2zew91czkpME/3b9zYIH8gOug', 'inquilino', NULL, NULL, NULL, 0, NULL, NULL, '2025-10-16 13:59:40', 0, '2025-10-16 13:59:40', '2025-10-16 13:59:40');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_consumo_por_hora`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_consumo_por_hora` (
`departamento_id` int(11)
,`sensor_id` int(11)
,`hora` varchar(24)
,`avg_valor` double
,`min_valor` double
,`max_valor` double
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_consumo_por_hora`
--
DROP TABLE IF EXISTS `vw_consumo_por_hora`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_consumo_por_hora`  AS SELECT `lecturas`.`departamento_id` AS `departamento_id`, `lecturas`.`sensor_id` AS `sensor_id`, date_format(`lecturas`.`recibido_en`,'%Y-%m-%d %H:00:00') AS `hora`, avg(`lecturas`.`valor`) AS `avg_valor`, min(`lecturas`.`valor`) AS `min_valor`, max(`lecturas`.`valor`) AS `max_valor` FROM `lecturas` GROUP BY `lecturas`.`departamento_id`, `lecturas`.`sensor_id`, date_format(`lecturas`.`recibido_en`,'%Y-%m-%d %H') ;

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

--
-- Indices de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alquileres_inquilino_id_foreign` (`inquilino_id`);

--
-- Indices de la tabla `areas_comunes`
--
ALTER TABLE `areas_comunes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunicacion`
--
ALTER TABLE `comunicacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comunicacion_remitente_id_foreign` (`remitente_id`),
  ADD KEY `comunicacion_destinatario_id_foreign` (`destinatario_id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `device_tokens`
--
ALTER TABLE `device_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `dispositivo_id` (`dispositivo_id`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificador` (`identificador`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empleados_dni_unique` (`dni`),
  ADD KEY `empleados_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `inquilinos`
--
ALTER TABLE `inquilinos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inquilinos_dni_unique` (`dni`),
  ADD KEY `inquilinos_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Indices de la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sensor_id` (`sensor_id`),
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `recibido_en` (`recibido_en`),
  ADD KEY `idx_lecturas_dep_time` (`departamento_id`,`recibido_en`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pagos_alquiler_id_foreign` (`alquiler_id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indices de la tabla `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservas_inquilino_id_foreign` (`inquilino_id`),
  ADD KEY `reservas_area_comun_id_foreign` (`area_comun_id`);

--
-- Indices de la tabla `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `sensores`
--
ALTER TABLE `sensores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispositivo_id` (`dispositivo_id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tareas_empleado_id_foreign` (`empleado_id`),
  ADD KEY `tareas_asignado_por_foreign` (`asignado_por`);

--
-- Indices de la tabla `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `umbrales`
--
ALTER TABLE `umbrales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sensor_id` (`sensor_id`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_password_reset_token` (`password_reset_token`),
  ADD KEY `idx_locked_until` (`locked_until`),
  ADD KEY `idx_failed_attempts` (`failed_login_attempts`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `areas_comunes`
--
ALTER TABLE `areas_comunes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `comunicacion`
--
ALTER TABLE `comunicacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `device_tokens`
--
ALTER TABLE `device_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inquilinos`
--
ALTER TABLE `inquilinos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `sensores`
--
ALTER TABLE `sensores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `umbrales`
--
ALTER TABLE `umbrales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertas_ibfk_2` FOREIGN KEY (`sensor_id`) REFERENCES `sensores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD CONSTRAINT `alquileres_inquilino_id_foreign` FOREIGN KEY (`inquilino_id`) REFERENCES `inquilinos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comunicacion`
--
ALTER TABLE `comunicacion`
  ADD CONSTRAINT `comunicacion_destinatario_id_foreign` FOREIGN KEY (`destinatario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comunicacion_remitente_id_foreign` FOREIGN KEY (`remitente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `device_tokens`
--
ALTER TABLE `device_tokens`
  ADD CONSTRAINT `device_tokens_ibfk_1` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `dispositivos_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inquilinos`
--
ALTER TABLE `inquilinos`
  ADD CONSTRAINT `inquilinos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD CONSTRAINT `lecturas_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `sensores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturas_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_alquiler_id_foreign` FOREIGN KEY (`alquiler_id`) REFERENCES `alquileres` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_area_comun_id_foreign` FOREIGN KEY (`area_comun_id`) REFERENCES `areas_comunes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_inquilino_id_foreign` FOREIGN KEY (`inquilino_id`) REFERENCES `inquilinos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sensores`
--
ALTER TABLE `sensores`
  ADD CONSTRAINT `sensores_ibfk_1` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_asignado_por_foreign` FOREIGN KEY (`asignado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tareas_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `umbrales`
--
ALTER TABLE `umbrales`
  ADD CONSTRAINT `umbrales_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `sensores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `umbrales_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
