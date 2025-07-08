-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 08 Tem 2025, 07:17:38
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `room_reservation`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `created_at`) VALUES
(1, 'admin', 'admin@baskent.edu.tr', '$2y$10$bMiM5pJGeW25NjBFwdCocOsYayqUWlbVjemhhatIDQu0yvN29oP16', '2025-07-06 17:43:29'),
(2, 'Yeni Admin', 'yeni.admin@baskent.edu.tr', '$2y$10$aJ4pw6rhR77iAbJqrfr/YO8mgp8byZklXCnRzmZG1vQTOHdAkbJwW', '2025-07-06 21:32:24'),
(3, '1', '1@1.1', '$2y$10$41qc7HTrnOzUtuHzjay6IeI59Lh64PnCbGMyb3/NW/yAxxOudk3Ci', '2025-07-06 21:32:40');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `lecturers`
--

CREATE TABLE `lecturers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `lecturers`
--

INSERT INTO `lecturers` (`id`, `name`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Dr. Ayşe Yılmaz', 'ayse.yilmaz@baskent.edu.tr', '$2y$10$R7S4Rx..kYMW5CvHMfkZbeID4KJKq2Ht3E5vUPCBHf8GvJqep8.NO', '2025-07-06 17:43:29'),
(2, 'Prof. Mehmet Demir', 'mehmet.demir@baskent.edu.tr', '2a8e3b75e49b8c5386837d3744c282451b8191cfab267d0eeaea4cc37c09f601', '2025-07-06 17:43:29'),
(3, 'Dr. Selin Kılıç', 'selin.kilic@baskent.edu.tr', '$2y$10$bwGxC2YT3wJ1QUt7CeMkdexbc9TA77.hgP/iWzupaSdGtW.Ye.wEa', '2025-07-06 20:59:16'),
(4, '12', '12@12.12', '$2y$10$WdnlyjxD/0epVkZZ9O8bSuTbnuL0okXvk.B6IOqP4BmcghI5ATk8e', '2025-07-06 23:06:19');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `building` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `features` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `capacity`, `building`, `is_active`, `features`) VALUES
(1, 'B202', 40, 'Mühendislik Binası', 1, NULL),
(2, 'Güncellenmiş Oda', 30, 'B Blok', 1, 'klima'),
(4, 'Güncellenmiş Oda', 30, 'B Blok', 1, NULL),
(5, 'Güncellenmiş Oda', 30, 'C Blok', 1, NULL),
(6, 'Güncellenmiş Oda', 30, 'C Blok', 1, NULL),
(7, 'Güncellenmiş Oda', 30, 'C Blok', 1, 'klima, balkon');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `room_reservations`
--

CREATE TABLE `room_reservations` (
  `id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `room_reservations`
--

INSERT INTO `room_reservations` (`id`, `lecturer_id`, `room_id`, `date`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 1, 2, '2025-07-10', '09:00:00', '11:00:00', 'approved', '2025-07-06 17:57:32'),
(2, 1, 2, '2025-07-12', '14:00:00', '16:00:00', 'pending', '2025-07-06 18:02:29'),
(4, 1, 2, '2025-12-01', '10:00:00', '12:00:00', 'pending', '2025-07-06 18:15:57'),
(5, 2, 2, '2025-12-01', '12:00:00', '14:00:00', 'pending', '2025-07-06 18:19:18');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `room_unavailable_times`
--

CREATE TABLE `room_unavailable_times` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `room_reservations`
--
ALTER TABLE `room_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `idx_room_time` (`room_id`,`date`,`start_time`,`end_time`),
  ADD KEY `idx_status` (`status`);

--
-- Tablo için indeksler `room_unavailable_times`
--
ALTER TABLE `room_unavailable_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_block` (`room_id`,`date`,`start_time`,`end_time`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `room_reservations`
--
ALTER TABLE `room_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `room_unavailable_times`
--
ALTER TABLE `room_unavailable_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `room_reservations`
--
ALTER TABLE `room_reservations`
  ADD CONSTRAINT `room_reservations_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `room_unavailable_times`
--
ALTER TABLE `room_unavailable_times`
  ADD CONSTRAINT `room_unavailable_times_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
