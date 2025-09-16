-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 06, 2025 at 03:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bawasla 2.0`
--

-- --------------------------------------------------------

--
-- Table structure for table `arrears`
--

CREATE TABLE `arrears` (
  `arrears_id` int(11) NOT NULL,
  `transaction_id` int(255) NOT NULL,
  `member_id` int(25) NOT NULL,
  `arrears_amount` double(40,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `history_id` int(11) NOT NULL,
  `member_id` int(255) NOT NULL,
  `reading_id` int(255) NOT NULL,
  `transaction_id` int(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `or_number` int(255) NOT NULL,
  `reading_date` date NOT NULL,
  `due_date` date NOT NULL,
  `disconnection_date` date NOT NULL,
  `payment_date` date NOT NULL DEFAULT current_timestamp(),
  `total_usage` int(255) NOT NULL,
  `current_charges` double(40,2) NOT NULL,
  `discount` varchar(255) NOT NULL,
  `total_amount_due` double(40,2) NOT NULL,
  `amount_paid` double(40,2) NOT NULL,
  `billing_month` varchar(255) NOT NULL,
  `payment_method` enum('Walk-In','G-Cash') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `user_id` int(255) NOT NULL,
  `member_id` int(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `address` varchar(255) NOT NULL,
  `tank_no` int(255) NOT NULL,
  `user_type` enum('Member') NOT NULL,
  `isDone` enum('Done','Not Done') NOT NULL DEFAULT 'Not Done'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meter_reading`
--

CREATE TABLE `meter_reading` (
  `reading_id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `member_id` int(255) NOT NULL,
  `reading_date` date NOT NULL DEFAULT current_timestamp(),
  `time` time NOT NULL DEFAULT current_timestamp(),
  `previous_reading` int(255) NOT NULL,
  `current_reading` int(255) NOT NULL,
  `total_usage` int(50) NOT NULL,
  `current_charges` double(40,2) NOT NULL,
  `arrears_amount` double(40,2) NOT NULL,
  `total_amount_due` double(40,2) NOT NULL,
  `due_date` date NOT NULL,
  `disconnection_date` date NOT NULL,
  `billing_month` varchar(20) NOT NULL,
  `status` enum('Paid','Not Paid','Pending') NOT NULL DEFAULT 'Not Paid',
  `arrears_processed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending`
--

CREATE TABLE `pending` (
  `pending_id` int(11) NOT NULL,
  `member_id` int(255) NOT NULL,
  `reading_id` int(255) NOT NULL,
  `date_received` date NOT NULL DEFAULT current_timestamp(),
  `current_charges` double(40,2) NOT NULL,
  `arrears_amount` double(40,2) NOT NULL,
  `total_amount_due` double(40,2) NOT NULL,
  `billing_month` varchar(255) NOT NULL,
  `proof_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` int(255) NOT NULL,
  `member_id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Secretary','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `member_id`, `username`, `password`, `user_type`) VALUES
(1, 895194267, 0, 'a', '$2y$10$wh8.7slZsD46xQ8jujiMjOLY.pTUTaHMu/BCh5y4aCDV2NZwWlyIS', 'Admin'),
(2, 178891474, 0, '1', '$2y$10$Uxkeoq53Hw6GVcIjLTdDZ.1Pb9AdmyiY0kW6rG66tfHsZW.7tzdWW', 'Secretary');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arrears`
--
ALTER TABLE `arrears`
  ADD PRIMARY KEY (`arrears_id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meter_reading`
--
ALTER TABLE `meter_reading`
  ADD PRIMARY KEY (`reading_id`);

--
-- Indexes for table `pending`
--
ALTER TABLE `pending`
  ADD PRIMARY KEY (`pending_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arrears`
--
ALTER TABLE `arrears`
  MODIFY `arrears_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pending`
--
ALTER TABLE `pending`
  MODIFY `pending_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
