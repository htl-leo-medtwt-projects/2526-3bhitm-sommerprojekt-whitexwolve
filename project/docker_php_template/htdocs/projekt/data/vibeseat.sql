-- Adminer 5.4.2 MySQL 9.7.0 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `events` (`id`, `title`, `description`, `category`, `image_url`, `created_at`) VALUES
(1,	'Moonlight Cinema Night',	'Klassiker unter dem Sternenhimmel – mit Decken, Popcorn und Atmosphäre.',	'Kino',	'',	'2026-06-15 14:56:46'),
(2,	'Jazz im Stadtpark',	'Die besten Jazz-Musiker der Region spielen live unter freiem Himmel.',	'Musik',	'',	'2026-06-15 14:56:46'),
(3,	'Comedy Nacht',	'Stand-Up von lokalen und nationalen Comedians – Lachen garantiert.',	'Comedy',	'',	'2026-06-15 14:56:46'),
(4,	'Rockkonzert: Neon Wolves',	'Energiegeladener Auftritt der aufsteigenden Rockband Neon Wolves.',	'Musik',	'',	'2026-06-15 14:56:46'),
(5,	'Kindertheater: Drachenzähmung',	'Magisches Theaterstück für die ganze Familie ab 5 Jahren.',	'Theater',	'',	'2026-06-15 14:56:46');

DROP TABLE IF EXISTS `reservierungen`;
CREATE TABLE `reservierungen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `show_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show_display` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `seats` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vorname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `nachname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `price_per_seat` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktiv',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reservierungen` (`id`, `user_id`, `show_id`, `event_title`, `show_display`, `seats`, `vorname`, `nachname`, `email`, `price_per_seat`, `total_price`, `created_at`, `status`) VALUES
(12,	2,	'5-1',	'Moonlight Cinema Night',	'Morgen · 20:00',	'F11, F12, F13, F14',	'',	'',	'',	0.00,	67.60,	'2026-05-26 12:09:05',	'aktiv'),
(13,	1,	'2-1',	'John Pork Live',	'Fr · 19:30',	'A1, A2, A3, A4',	'',	'',	'',	0.00,	159.60,	'2026-05-26 13:16:04',	'aktiv');

DROP TABLE IF EXISTS `saele`;
CREATE TABLE `saele` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `anzahl_reihen` int NOT NULL,
  `anzahl_spalten` int NOT NULL,
  `beschreibung` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `saele` (`id`, `name`, `capacity`, `anzahl_reihen`, `anzahl_spalten`, `beschreibung`) VALUES
(1,	'Hauptsaal',	120,	10,	12,	'Großer Saal mit bester Akustik'),
(2,	'Kinosaal',	80,	8,	10,	'Gemütlicher Kinosaal mit Leinwand'),
(3,	'Kleiner Saal',	40,	5,	8,	'Intimer Rahmen für kleine Events');

DROP TABLE IF EXISTS `shows`;
CREATE TABLE `shows` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `show_date` date NOT NULL,
  `show_time` time NOT NULL,
  `hall` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Hauptsaal',
  `ticket_price` decimal(6,2) NOT NULL DEFAULT '12.00',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `shows_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `shows` (`id`, `event_id`, `show_date`, `show_time`, `hall`, `ticket_price`) VALUES
(1,	1,	'2026-06-20',	'20:00:00',	'Kinosaal',	9.50),
(2,	1,	'2026-06-21',	'21:00:00',	'Kinosaal',	9.50),
(3,	2,	'2026-06-22',	'19:00:00',	'Hauptsaal',	14.00),
(4,	3,	'2026-06-23',	'20:30:00',	'Hauptsaal',	18.00),
(5,	4,	'2026-06-25',	'21:00:00',	'Hauptsaal',	22.00),
(6,	5,	'2026-06-28',	'15:00:00',	'Kleiner Saal',	8.00),
(7,	5,	'2026-06-28',	'17:00:00',	'Kleiner Saal',	8.00);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profilbild` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `profilbild`) VALUES
(1,	'testUser',	'testUser@test.com',	'$2y$10$K5lpPHHDAVJ58sNXfeVUnecZgs6lIqD/Bl31EmeuRwta1XmuQFEjG',	'2026-05-26 08:24:51',	'uploads/user_1.webp'),
(2,	'Bernhard',	'bernhard@b.com',	'$2y$10$s6ML9ljpCAnp0fTZDPi51egO6vDzqpyFtMfuib.uglRCrv7WLqMAG',	'2026-05-26 12:08:16',	'uploads/user_2.webp'),
(3,	'test',	'test@test.com',	'$2y$10$lX48MOpBsMPR2w.ZekKMZOk7M.epUrC10sFfZJahme5bQmYLg.OY.',	'2026-05-26 13:13:12',	NULL),
(4,	'ichmagnimma',	'ichmag@nimma.com',	'$2y$10$Y8UQr25yP8kJLmiR5W0dLuKdNpp5dC6QFIQwKiicSM9RY8c.EXxK6',	'2026-06-15 19:42:04',	NULL),
(6,	'1',	'a@b.com',	'$2y$10$vuntEr6hm9.W9z9fP6eUQ.j.JOkhHrWQKD2NPKUtOuz7./kO741/m',	'2026-06-15 20:21:33',	'uploads/user_6.png');

-- 2026-06-15 21:33:03 UTC
