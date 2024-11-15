-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 14, 2024 at 08:09 PM
-- Server version: 10.6.19-MariaDB-cll-lve
-- PHP Version: 8.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rrgaming_webstats`
--

-- --------------------------------------------------------

--
-- Table structure for table `squad_stats`
--

CREATE TABLE `squad_stats` (
  `id` int(11) NOT NULL,
  `steamid` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `deathcount` int(11) DEFAULT 0,
  `killcount` int(11) DEFAULT 0,
  `wasrevivedcount` int(11) DEFAULT 0,
  `revivecount` int(11) DEFAULT 0,
  `tkcount` int(11) DEFAULT 0,
  `suicidecount` int(11) DEFAULT 0,
  `elo` decimal(6,2) DEFAULT 1000.00,
  `knockedcount` int(11) DEFAULT 0,
  `totaldamage` decimal(10,2) DEFAULT 0.00,
  `elo2` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `squad_stats`
--
ALTER TABLE `squad_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `steamid` (`steamid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `squad_stats`
--
ALTER TABLE `squad_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
