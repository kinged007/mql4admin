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
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `account` varchar(55) DEFAULT NULL,
  `server` varchar(55) DEFAULT NULL,
  `friendly_name` varchar(55) DEFAULT NULL,
  `balance` float DEFAULT NULL,
  `credit` float DEFAULT NULL,
  `company` varchar(55) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `equity` float DEFAULT NULL,
  `margin` float DEFAULT NULL,
  `margin_level` float DEFAULT NULL,
  `free_margin` float DEFAULT NULL,
  `leverage` int(5) DEFAULT NULL,
  `name` varchar(55) DEFAULT NULL,
  `profit` float DEFAULT NULL,
  `stopout_call` int(5) DEFAULT NULL,
  `stopout_stopout` int(5) DEFAULT NULL,
  `trade_permitted` tinyint(1) DEFAULT NULL,
  `ea_permitted` tinyint(1) DEFAULT NULL,
  `dll_allowed` tinyint(1) DEFAULT NULL,
  `account_type` varchar(55) DEFAULT NULL,
  `stopout_type` varchar(55) DEFAULT NULL,
  `vps_id` varchar(55) DEFAULT NULL,
  `open_trades` int(10) DEFAULT NULL,  
  `timestamp` timestamp NULL DEFAULT NULL,
  `start_balance_day` float DEFAULT NULL,
  `start_balance_week` float DEFAULT NULL,
  `start_balance_month` float DEFAULT NULL,
  `start_balance_3month` float DEFAULT NULL,
  `start_balance_year` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ignore_account` tinyint(1) DEFAULT NULL,
  `last_notification` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

  
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
