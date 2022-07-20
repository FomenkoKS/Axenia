-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 20, 2022 at 07:02 AM
-- Server version: 8.0.29-0ubuntu0.20.04.3
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `axenia_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `Chats`
--

CREATE TABLE `Chats` (
  `id` bigint NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lang` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8_general_ci DEFAULT NULL,
  `username` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8_general_ci DEFAULT NULL,
  `silent_mode` tinyint(1) NOT NULL DEFAULT '0',
  `cooldown` double NOT NULL DEFAULT '1',
  `isPresented` tinyint(1) DEFAULT '1' COMMENT 'Bot in chat or not',
  `date_add` datetime DEFAULT NULL,
  `date_remove` datetime DEFAULT NULL,
  `ariphmeticGrowth` tinyint(1) NOT NULL DEFAULT '0',
  `forAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `showcase` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Karma`
--

CREATE TABLE `Karma` (
  `user_id` bigint NOT NULL,
  `chat_id` bigint DEFAULT NULL,
  `level` float DEFAULT NULL,
  `last_updated` date DEFAULT NULL,
  `last_time_voted` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `toofast_showed` tinyint DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `Rights`
--

CREATE TABLE `Rights` (
  `user_id` int NOT NULL,
  `erase_self` tinyint(1) DEFAULT NULL,
  `erase_anybody` tinyint(1) DEFAULT NULL,
  `edit_self` tinyint(1) DEFAULT NULL,
  `edit_anybody` tinyint(1) DEFAULT NULL,
  `full` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `Showcase`
--

CREATE TABLE `Showcase` (
  `id` int NOT NULL,
  `title` varchar(50) NOT NULL,
  `price` int NOT NULL,
  `censor` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` bigint NOT NULL,
  `username` varchar(50) NOT NULL,
  `firstname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lang` varchar(5) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `title` varchar(30) DEFAULT NULL,
  `cookies` int DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Chats`
--
ALTER TABLE `Chats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `Karma`
--
ALTER TABLE `Karma`
  ADD UNIQUE KEY `id` (`user_id`,`chat_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `chat_id` (`chat_id`);

--
-- Indexes for table `Rights`
--
ALTER TABLE `Rights`
  ADD UNIQUE KEY `user_id_un` (`user_id`);

--
-- Indexes for table `Showcase`
--
ALTER TABLE `Showcase`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`,`username`,`firstname`,`lastname`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Chats`
--
ALTER TABLE `Chats`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Showcase`
--
ALTER TABLE `Showcase`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
