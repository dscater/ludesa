-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 26-08-2026 a las 17:15:57
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ludesa_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campeonatos`
--

CREATE TABLE `campeonatos` (
  `id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gestion` int NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campeonato_inscripcions`
--

CREATE TABLE `campeonato_inscripcions` (
  `id` bigint UNSIGNED NOT NULL,
  `campeonato_id` bigint UNSIGNED NOT NULL,
  `carrera_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera_jugadors`
--

CREATE TABLE `carrera_jugadors` (
  `id` bigint UNSIGNED NOT NULL,
  `campeonato_id` bigint UNSIGNED NOT NULL,
  `carrera_id` bigint UNSIGNED NOT NULL,
  `jugador_id` bigint UNSIGNED NOT NULL,
  `posicion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracions`
--

CREATE TABLE `configuracions` (
  `id` bigint UNSIGNED NOT NULL,
  `nombre_sistema` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actividad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracions`
--

INSERT INTO `configuracions` (`id`, `nombre_sistema`, `alias`, `razon_social`, `nit`, `dir`, `fono`, `actividad`, `correo`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'MEDINTER', 'MD', 'MEDINTER S.A.', '1111111111', 'LOS OLIVOS #111', '67676767', 'ACTIVIDAD', 'correo@gmail.com', '11777153391.jpeg', '2026-02-16 22:21:27', '2026-04-25 17:43:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_accions`
--

CREATE TABLE `historial_accions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `accion` varchar(155) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos_original` json DEFAULT NULL,
  `datos_nuevo` json DEFAULT NULL,
  `modulo` varchar(155) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadors`
--

CREATE TABLE `jugadors` (
  `id` bigint UNSIGNED NOT NULL,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_31_165641_create_configuracions_table', 1),
(2, '2024_11_02_153317_create_users_table', 1),
(3, '2024_11_02_153318_create_historial_accions_table', 1),
(4, '2026_08_26_125053_create_carreras_table', 1),
(5, '2026_08_26_125149_create_campeonatos_table', 1),
(6, '2026_08_26_125239_create_jugadors_table', 1),
(7, '2026_08_26_125254_create_campeonato_inscripcions_table', 1),
(8, '2026_08_26_125416_create_carrera_jugadors_table', 1),
(9, '2026_08_26_125444_create_partidos_table', 1),
(10, '2026_08_26_125452_create_partido_detalles_table', 1),
(11, '2026_08_26_125527_create_partido_gols_table', 1),
(12, '2026_08_26_130819_create_partido_jugadors_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` bigint UNSIGNED NOT NULL,
  `campeonato_id` bigint UNSIGNED NOT NULL,
  `local_id` bigint UNSIGNED NOT NULL,
  `visitante_id` bigint UNSIGNED NOT NULL,
  `goles_local` int NOT NULL DEFAULT '0',
  `goles_visitante` int NOT NULL DEFAULT '0',
  `ganador_id` bigint UNSIGNED DEFAULT NULL,
  `total_local` decimal(24,2) NOT NULL,
  `pago_local` tinyint(1) NOT NULL,
  `total_visitante` decimal(24,2) NOT NULL,
  `pago_visitante` tinyint(1) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partido_detalles`
--

CREATE TABLE `partido_detalles` (
  `id` bigint UNSIGNED NOT NULL,
  `campeonato_id` bigint UNSIGNED NOT NULL,
  `partido_id` bigint UNSIGNED NOT NULL,
  `carrera_id` bigint UNSIGNED NOT NULL,
  `tarjeta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `carrera_jugador_id` bigint UNSIGNED NOT NULL,
  `total` decimal(24,2) NOT NULL,
  `pagado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partido_gols`
--

CREATE TABLE `partido_gols` (
  `id` bigint UNSIGNED NOT NULL,
  `campeonato_id` bigint UNSIGNED NOT NULL,
  `partido_id` bigint UNSIGNED NOT NULL,
  `carrera_jugador_id` bigint UNSIGNED NOT NULL,
  `goles` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partido_jugadors`
--

CREATE TABLE `partido_jugadors` (
  `id` bigint UNSIGNED NOT NULL,
  `campeonato_id` bigint UNSIGNED NOT NULL,
  `partido_id` bigint UNSIGNED NOT NULL,
  `carrera_jugador_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `usuario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paterno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `materno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ci_exp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dir` varchar(600) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fono` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acceso` int NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `usuario`, `nombre`, `paterno`, `materno`, `ci`, `ci_exp`, `dir`, `correo`, `fono`, `password`, `acceso`, `tipo`, `foto`, `fecha_registro`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'admin', '', '0', '', '', '', '', '$2y$12$65d4fgZsvBV5Lc/AxNKh4eoUdbGyaczQ4sSco20feSQANshNLuxSC', 1, 'ADMINISTRADOR', NULL, '2025-10-01', 1, '2026-02-17 22:21:27', '2026-02-17 22:21:27');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `campeonatos`
--
ALTER TABLE `campeonatos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `campeonato_inscripcions`
--
ALTER TABLE `campeonato_inscripcions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campeonato_inscripcions_campeonato_id_foreign` (`campeonato_id`),
  ADD KEY `campeonato_inscripcions_carrera_id_foreign` (`carrera_id`);

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `carrera_jugadors`
--
ALTER TABLE `carrera_jugadors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carrera_jugadors_campeonato_id_foreign` (`campeonato_id`),
  ADD KEY `carrera_jugadors_carrera_id_foreign` (`carrera_id`),
  ADD KEY `carrera_jugadors_jugador_id_foreign` (`jugador_id`);

--
-- Indices de la tabla `configuracions`
--
ALTER TABLE `configuracions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_accions`
--
ALTER TABLE `historial_accions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `historial_accions_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `jugadors`
--
ALTER TABLE `jugadors`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partidos_campeonato_id_foreign` (`campeonato_id`),
  ADD KEY `partidos_local_id_foreign` (`local_id`),
  ADD KEY `partidos_visitante_id_foreign` (`visitante_id`),
  ADD KEY `partidos_ganador_id_foreign` (`ganador_id`);

--
-- Indices de la tabla `partido_detalles`
--
ALTER TABLE `partido_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_detalles_campeonato_id_foreign` (`campeonato_id`),
  ADD KEY `partido_detalles_partido_id_foreign` (`partido_id`),
  ADD KEY `partido_detalles_carrera_id_foreign` (`carrera_id`),
  ADD KEY `partido_detalles_carrera_jugador_id_foreign` (`carrera_jugador_id`);

--
-- Indices de la tabla `partido_gols`
--
ALTER TABLE `partido_gols`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_gols_campeonato_id_foreign` (`campeonato_id`),
  ADD KEY `partido_gols_partido_id_foreign` (`partido_id`),
  ADD KEY `partido_gols_carrera_jugador_id_foreign` (`carrera_jugador_id`);

--
-- Indices de la tabla `partido_jugadors`
--
ALTER TABLE `partido_jugadors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_jugadors_campeonato_id_foreign` (`campeonato_id`),
  ADD KEY `partido_jugadors_partido_id_foreign` (`partido_id`),
  ADD KEY `partido_jugadors_carrera_jugador_id_foreign` (`carrera_jugador_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `campeonatos`
--
ALTER TABLE `campeonatos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `campeonato_inscripcions`
--
ALTER TABLE `campeonato_inscripcions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrera_jugadors`
--
ALTER TABLE `carrera_jugadors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracions`
--
ALTER TABLE `configuracions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `historial_accions`
--
ALTER TABLE `historial_accions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jugadors`
--
ALTER TABLE `jugadors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `partido_detalles`
--
ALTER TABLE `partido_detalles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `partido_gols`
--
ALTER TABLE `partido_gols`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `partido_jugadors`
--
ALTER TABLE `partido_jugadors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `campeonato_inscripcions`
--
ALTER TABLE `campeonato_inscripcions`
  ADD CONSTRAINT `campeonato_inscripcions_campeonato_id_foreign` FOREIGN KEY (`campeonato_id`) REFERENCES `campeonatos` (`id`),
  ADD CONSTRAINT `campeonato_inscripcions_carrera_id_foreign` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`);

--
-- Filtros para la tabla `carrera_jugadors`
--
ALTER TABLE `carrera_jugadors`
  ADD CONSTRAINT `carrera_jugadors_campeonato_id_foreign` FOREIGN KEY (`campeonato_id`) REFERENCES `campeonatos` (`id`),
  ADD CONSTRAINT `carrera_jugadors_carrera_id_foreign` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`),
  ADD CONSTRAINT `carrera_jugadors_jugador_id_foreign` FOREIGN KEY (`jugador_id`) REFERENCES `jugadors` (`id`);

--
-- Filtros para la tabla `historial_accions`
--
ALTER TABLE `historial_accions`
  ADD CONSTRAINT `historial_accions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `partidos_campeonato_id_foreign` FOREIGN KEY (`campeonato_id`) REFERENCES `campeonatos` (`id`),
  ADD CONSTRAINT `partidos_ganador_id_foreign` FOREIGN KEY (`ganador_id`) REFERENCES `carreras` (`id`),
  ADD CONSTRAINT `partidos_local_id_foreign` FOREIGN KEY (`local_id`) REFERENCES `carreras` (`id`),
  ADD CONSTRAINT `partidos_visitante_id_foreign` FOREIGN KEY (`visitante_id`) REFERENCES `carreras` (`id`);

--
-- Filtros para la tabla `partido_detalles`
--
ALTER TABLE `partido_detalles`
  ADD CONSTRAINT `partido_detalles_campeonato_id_foreign` FOREIGN KEY (`campeonato_id`) REFERENCES `campeonatos` (`id`),
  ADD CONSTRAINT `partido_detalles_carrera_id_foreign` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`),
  ADD CONSTRAINT `partido_detalles_carrera_jugador_id_foreign` FOREIGN KEY (`carrera_jugador_id`) REFERENCES `carrera_jugadors` (`id`),
  ADD CONSTRAINT `partido_detalles_partido_id_foreign` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`);

--
-- Filtros para la tabla `partido_gols`
--
ALTER TABLE `partido_gols`
  ADD CONSTRAINT `partido_gols_campeonato_id_foreign` FOREIGN KEY (`campeonato_id`) REFERENCES `campeonatos` (`id`),
  ADD CONSTRAINT `partido_gols_carrera_jugador_id_foreign` FOREIGN KEY (`carrera_jugador_id`) REFERENCES `carrera_jugadors` (`id`),
  ADD CONSTRAINT `partido_gols_partido_id_foreign` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`);

--
-- Filtros para la tabla `partido_jugadors`
--
ALTER TABLE `partido_jugadors`
  ADD CONSTRAINT `partido_jugadors_campeonato_id_foreign` FOREIGN KEY (`campeonato_id`) REFERENCES `campeonatos` (`id`),
  ADD CONSTRAINT `partido_jugadors_carrera_jugador_id_foreign` FOREIGN KEY (`carrera_jugador_id`) REFERENCES `carrera_jugadors` (`id`),
  ADD CONSTRAINT `partido_jugadors_partido_id_foreign` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
