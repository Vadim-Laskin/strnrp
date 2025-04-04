-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 04 2025 г., 14:49
-- Версия сервера: 10.11.6-MariaDB-0+deb12u1
-- Версия PHP: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `gs80717`
--

-- --------------------------------------------------------

--
-- Структура таблицы `site_admins`
--

CREATE TABLE `site_admins` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `level` int(11) NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `appointed_date` date NOT NULL DEFAULT curdate(),
  `vk_link` varchar(255) NOT NULL,
  `vk_name` varchar(255) NOT NULL,
  `inactive_until` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `site_admins`
--

INSERT INTO `site_admins` (`id`, `name`, `level`, `prefix`, `appointed_date`, `vk_link`, `vk_name`, `inactive_until`) VALUES
(14, 'Christian_Allford', 6, 'Гл. Админ', '2025-03-26', 'https://vk.com/vlad19283', 'Vlad Karpovich', NULL),
(15, 'Nikita_Dorian', 5, 'Куратор', '2025-03-26', 'https://vk.com/nuchto_ne_ustuna', 'Nikita Alexandrovich', NULL),
(16, 'Zhan_Maev', 3, 'ГС Гос', '2025-03-26', 'https://vk.com/id829093885', 'Nikita Efimov', NULL),
(17, 'Vova_Relax', 3, 'ГС ОПГ', '2025-03-26', 'https://vk.com/glrelax', 'Qwe Relax', NULL),
(18, 'Volodya_Sakurai', 3, 'зГС Гос', '2025-03-26', 'https://vk.com/volodya_sakurai', 'Vladimir Pyatinov', NULL),
(19, 'Rosa_Gallagher', 2, 'Администратор', '2025-03-26', 'https://vk.com/sigmamahahaha', 'Gaga Ledi', NULL),
(20, 'Agafia_Chuma', 2, 'Администратор', '2025-03-26', 'https://vk.com/chupupk', 'Margarita Margaritau', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `site_admins`
--
ALTER TABLE `site_admins`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `site_admins`
--
ALTER TABLE `site_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
