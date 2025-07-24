-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2024 at 01:28 PM
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
-- Database: `bawasla`
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
  `reading_date` date NOT NULL,
  `due_date` date NOT NULL,
  `disconnection_date` date NOT NULL,
  `payment_date` date NOT NULL DEFAULT current_timestamp(),
  `current_charges` double(40,2) NOT NULL,
  `total_amount_due` double(40,2) NOT NULL,
  `amount_paid` double(40,2) NOT NULL,
  `billing_month` varchar(255) NOT NULL,
  `payment_method` enum('Walk-In','G-Cash') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`history_id`, `member_id`, `reading_id`, `transaction_id`, `fullname`, `reading_date`, `due_date`, `disconnection_date`, `payment_date`, `current_charges`, `total_amount_due`, `amount_paid`, `billing_month`, `payment_method`) VALUES
(4, 140022718, 339902791, 440756637, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 36.00, 36.00, 30.00, 'October 2024', 'Walk-In'),
(5, 140022718, 633422948, 474630678, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 18.00, 24.00, 20.00, 'October 2024', 'Walk-In'),
(6, 140022718, 143184973, 246298736, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 36.00, 40.00, 30.00, 'October 2024', 'Walk-In'),
(7, 140022718, 700913464, 589368425, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 36.00, 36.00, 36.00, 'October 2024', 'G-Cash'),
(8, 140022718, 142394100, 472226960, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 18.00, 18.00, 18.00, 'October 2024', 'G-Cash'),
(9, 140022718, 477282277, 582901217, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 18.00, 18.00, 10.00, 'October 2024', 'G-Cash'),
(10, 140022718, 264987355, 996885673, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 36.00, 36.00, 30.00, 'October 2024', 'G-Cash'),
(11, 140022718, 344742069, 233788396, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 18.00, 18.00, 10.00, 'October 2024', 'G-Cash'),
(12, 140022718, 741922082, 607226403, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 18.00, 26.00, 20.00, 'October 2024', 'G-Cash'),
(13, 140022718, 634767797, 783138098, 'Laraño, Ritz ', '2024-11-12', '2024-11-27', '2024-11-29', '2024-11-12', 18.00, 24.00, 24.00, 'October 2024', 'Walk-In');

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
  `address` varchar(255) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `tank_no` int(255) NOT NULL,
  `meter_no` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Member') NOT NULL,
  `isDone` enum('Done','Not Done') NOT NULL DEFAULT 'Not Done'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `user_id`, `member_id`, `last_name`, `first_name`, `middle_name`, `address`, `mobile_number`, `tank_no`, `meter_no`, `username`, `password`, `user_type`, `isDone`) VALUES
(14, 658797145, 658797145, 'Cruz', 'Juan', 'X', '1', '09277603828', 3, 3, 'jc', '$2y$10$7mZu7x.A4D798YQfuAdCde6nUf.zreXMLEm5iGl4AzXuelJz/g.im', 'Member', 'Not Done'),
(15, 658214392, 658214392, 'a', 'a', 'a', '3', '09277603828', 1, 1, 'a', '$2y$10$2TJvx953Wl3T7/hT/G/pq.6hnhTwE0I0WuktBvQMN6Ewp6FPcJKdO', 'Member', 'Not Done'),
(17, 378616290, 378616290, 'test', 't', 't', '2', '09277603828', 1, 1121212, 't', '$2y$10$2HqmoV4RNFvHID2oyK59bOXWQWi1jBna4DWzRXqEvVyzUqGATLElO', 'Member', 'Done'),
(20, 140022718, 140022718, 'Laraño', 'Ritz', '', '2', '+639277603828', 4, 34525, 'r', '$2y$10$jiAgflYhHfwfvu84LZUSaO7bLjKMPDQQR3eXheBn8QwVClLbflTNq', 'Member', 'Not Done');

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
  `current_charges` double(40,2) NOT NULL,
  `previous_reading` int(255) NOT NULL,
  `current_reading` int(255) NOT NULL,
  `total_usage` int(50) NOT NULL,
  `due_date` date NOT NULL,
  `disconnection_date` date NOT NULL,
  `billing_month` varchar(20) NOT NULL,
  `status` enum('Paid','Not Paid','Pending') NOT NULL DEFAULT 'Not Paid'
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
  `billing_month` varchar(255) NOT NULL,
  `proof_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending`
--

INSERT INTO `pending` (`pending_id`, `member_id`, `reading_id`, `date_received`, `current_charges`, `billing_month`, `proof_image_path`) VALUES
(1, 140022718, 312789611, '2024-11-12', 54.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213529.png'),
(2, 140022718, 815885628, '2024-11-12', 36.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213425.png'),
(3, 140022718, 995739073, '2024-11-12', 216.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213425.png'),
(4, 140022718, 255082811, '2024-11-12', 54.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213341.png'),
(5, 140022718, 700913464, '2024-11-12', 36.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213341.png'),
(6, 140022718, 142394100, '2024-11-12', 18.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213341.png'),
(7, 140022718, 477282277, '2024-11-12', 18.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213425.png'),
(8, 140022718, 264987355, '2024-11-12', 36.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213425.png'),
(9, 140022718, 344742069, '2024-11-12', 18.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213425.png'),
(10, 140022718, 741922082, '2024-11-12', 18.00, 'October 2024', 'uploads/Screenshot 2024-11-06 213529.png');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(255) NOT NULL,
  `member_id` int(255) NOT NULL,
  `reading_id` int(255) NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` date NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('Walk-In','G-Cash') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `member_id`, `reading_id`, `due_date`, `payment_date`, `payment_method`) VALUES
(143175741, 658797145, 128909084, '2024-11-19', '2024-11-04', 'G-Cash'),
(350424541, 378616290, 144054894, '2024-11-19', '2024-11-04', 'G-Cash'),
(356176716, 658214392, 697217916, '2024-11-11', '2024-11-03', 'G-Cash'),
(776346480, 658797145, 746222343, '2024-11-19', '2024-11-04', 'G-Cash'),
(796993124, 746677455, 283061561, '2024-11-09', '2024-11-04', 'Walk-In'),
(849102833, 378616290, 317100590, '2024-11-19', '2024-11-04', 'G-Cash');

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
  `user_type` enum('President','Treasurer','Meter Reader','Member') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `member_id`, `username`, `password`, `user_type`) VALUES
(17, 658797145, 658797145, 'jc', '$2y$10$7mZu7x.A4D798YQfuAdCde6nUf.zreXMLEm5iGl4AzXuelJz/g.im', 'Member'),
(18, 658214392, 658214392, 'a', '$2y$10$2TJvx953Wl3T7/hT/G/pq.6hnhTwE0I0WuktBvQMN6Ewp6FPcJKdO', 'Member'),
(20, 378616290, 378616290, 't', '$2y$10$2HqmoV4RNFvHID2oyK59bOXWQWi1jBna4DWzRXqEvVyzUqGATLElO', 'Member'),
(23, 140022718, 140022718, 'r', '$2y$10$jiAgflYhHfwfvu84LZUSaO7bLjKMPDQQR3eXheBn8QwVClLbflTNq', 'Member'),
(25, 409614432, 0, 'z', '$2y$10$1ILmacd6vWLvYjsDbrYg/O.jCEvDU6Mh1rig2tLQ.01mGJrSnCDBi', 'President'),
(43, 497364803, 0, 'tr', '$2y$10$Kam/K8KiJNPThZBDjfKwBecg119yPivBRo8PdfPmTiXsCpiTbSAue', 'Treasurer'),
(44, 601672633, 0, 'mr', '$2y$10$ixTe7tS/YQp5AYi6IBGGluFc3/Qb7JHfcGPPgyG1s5vzHOzXSfTe6', 'Meter Reader');

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
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`);

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
  MODIFY `arrears_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `pending`
--
ALTER TABLE `pending`
  MODIFY `pending_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
