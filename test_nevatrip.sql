-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 17 2024 г., 09:56
-- Версия сервера: 8.2.0
-- Версия PHP: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `test_nevatrip`
--

-- --------------------------------------------------------

--
-- Структура таблицы `tb_events`
--

DROP TABLE IF EXISTS `tb_events`;
CREATE TABLE IF NOT EXISTS `tb_events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `event_desc` text COLLATE utf8mb4_general_ci NOT NULL,
  `event_start` date NOT NULL,
  `event_end` date NOT NULL,
  `event_price` int NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tb_tickets`
--

DROP TABLE IF EXISTS `tb_tickets`;
CREATE TABLE IF NOT EXISTS `tb_tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `ticket_type_id` int NOT NULL,
  `ticket_adult_price` int NOT NULL,
  `ticket_adult_quantity` int NOT NULL,
  `ticket_kid_price` int NOT NULL,
  `ticket_kid_quantity` int NOT NULL,
  `equal_price` int NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tb_ tickets_barcodes`
--

DROP TABLE IF EXISTS `tb_ tickets_barcodes`;
CREATE TABLE IF NOT EXISTS `tb_ tickets_barcodes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `barcode` int NOT NULL,
  `check_success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `barcode` (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tb_tickets_types`
--

DROP TABLE IF EXISTS `tb_tickets_types`;
CREATE TABLE IF NOT EXISTS `tb_tickets_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `ticket_type_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ticket_adult_price` int NOT NULL,
  `ticket_kid_price` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
