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
-- Структура таблицы `site_testers`
--

CREATE TABLE `site_testers` (
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
-- Дамп данных таблицы `site_testers`
--

INSERT INTO `site_testers` (`id`, `name`, `level`, `prefix`, `appointed_date`, `vk_link`, `vk_name`, `inactive_until`) VALUES
(20, 'Agafia_Chuma', 2, 'Администратор', '2025-03-26', 'https://vk.com/chupupk', 'Margarita Margaritau', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `site_testers`
--
ALTER TABLE `site_testers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `site_testers`
--
ALTER TABLE `site_testers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
