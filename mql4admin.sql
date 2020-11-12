-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 24, 2020 at 09:17 PM
-- Server version: 5.7.31-0ubuntu0.16.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `mql4messages`
--

CREATE TABLE `mql4message` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `account` varchar(55) NOT NULL,
  `server` varchar(55) NOT NULL,
  `balance` varchar(55) NOT NULL,
  `credit` varchar(55) NOT NULL,
  `company` varchar(255) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `equity` varchar(55) NOT NULL,
  `margin` varchar(55) NOT NULL,
  `margin_level` varchar(55) NOT NULL,
  `free_margin` varchar(55) NOT NULL,
  `leverage` varchar(55) NOT NULL,
  `name` varchar(55) NOT NULL,
  `profit` varchar(55) NOT NULL,
  `stopout_call` varchar(55) NOT NULL,
  `stopout_stopout` varchar(55) NOT NULL,
  `trade_permitted` varchar(55) NOT NULL,
  `ea_permitted` varchar(55) NOT NULL,
  `dll_allowed` varchar(55) NOT NULL,
  `account_type` varchar(55) NOT NULL,
  `stopout_type` varchar(100) DEFAULT NULL,
  `vps_id` varchar(100) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  
--
-- Indexes for table `mql4message`
--
ALTER TABLE `mql4message`
  ADD PRIMARY KEY (`id`);


--
-- AUTO_INCREMENT for table `mql4message`
--
ALTER TABLE `mql4message`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
