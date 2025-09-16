-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2025 at 04:13 PM
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
(1, 658797145, 161901449, 146431969, 'Cruz, Juan X', '2025-02-05', '2025-02-20', '2025-02-22', '2025-02-05', 216.00, 230.00, 230.00, 'February 2025', 'Walk-In'),
(2, 356861103, 639845515, 404196504, 'Lloren, Kevin ', '2025-02-05', '2025-02-20', '2025-02-22', '2025-02-05', 414.00, 414.00, 414.00, 'February 2025', 'Walk-In'),
(3, 202743864, 719141656, 811655412, 'Bohr, Neils ', '2024-10-24', '2024-12-11', '2024-12-13', '2025-03-03', 90.00, 90.00, 20.00, 'October 2024', 'Walk-In'),
(4, 802992535, 693382583, 463578460, 'Tesla, Nikola ', '2024-09-19', '2024-12-11', '2024-12-13', '2025-03-14', 612.00, 612.00, 612.00, 'September 2024', 'Walk-In'),
(5, 196094140, 766603194, 254028151, 'Robert , Boyle ', '2024-04-02', '2024-12-11', '2024-12-13', '2025-03-14', 1278.00, 1278.00, 1278.00, 'April 2024', 'Walk-In'),
(6, 202743864, 271006491, 308614023, 'Bohr, Neils ', '2025-03-03', '2025-03-18', '2025-03-20', '2025-03-27', 18.00, 88.00, 100.00, 'March 2025', 'Walk-In'),
(7, 473072709, 598513734, 234607116, 'Laraño, Ritz ', '2025-04-30', '2025-05-15', '2025-05-17', '2025-06-08', 900.00, 900.00, 800.00, 'April 2025', 'Walk-In'),
(8, 209309531, 429245647, 949076843, 'test, t t', '2025-07-22', '2025-08-06', '2025-08-08', '2025-07-22', 18.00, 18.00, 20.00, 'July 2025', 'Walk-In');

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
(14, 658797145, 658797145, 'Cruz', 'Juan', 'X', '2', '09277603828', 3, 3, 'jc', '$2y$10$7mZu7x.A4D798YQfuAdCde6nUf.zreXMLEm5iGl4AzXuelJz/g.im', 'Member', 'Not Done'),
(38, 356861103, 356861103, 'Lloren', 'Kevin', '', '1', '09277606828', 2, 121313, 'kl', '$2y$10$sgMcdZdgKNqXLjPefr7.Hu/FXk7.zYCWSv15FGqnFJmGd3El9R0Im', 'Member', 'Not Done'),
(39, 580277208, 580277208, 'Bulalaque', 'Claire', '', '3', '09277603828', 3, 453453, 'cb', '$2y$10$L6.EcHHRsBvJyJxSw6LsgeDffpdoe27Vaj/7G/BwGDqqJcaBBofTW', 'Member', 'Done'),
(40, 202743864, 202743864, 'Bohr', 'Neils', '', '3', '09277603828', 2, 4646, 'nb', '$2y$10$LDBuwrr18HmhTdPtPLyazOb9Q/fo/IIibH4/R4y9lzn8vn..wFYey', 'Member', 'Not Done'),
(41, 184309635, 184309635, 'Newton', 'Isaac', '', '2', '09277603828', 1, 54646, 'in', '$2y$10$q8tRUI.ck313i7E1sx0Ys.8g7zHtvzuRkI.w/3OklK5WQFlAtsfMG', 'Member', 'Not Done'),
(42, 589909318, 589909318, 'Curie', 'Marie', '', '1', '09277603828', 3, 456456, 'mc', '$2y$10$LvrZ33ewPT2LCbkdYFYLS.w/13DpUhPns/E4l.In2dDa5Cdto8gne', 'Member', 'Done'),
(43, 196094140, 196094140, 'Robert ', 'Boyle', '', '2', '09277603828', 2, 57556, 'rb', '$2y$10$8zTI87aN2wutktKlZyz1rOwIfXfBicWVFsR6FX8HjdJUhFOIx/S9q', 'Member', 'Not Done'),
(44, 802992535, 802992535, 'Tesla', 'Nikola', '', '3', '09277603828', 1, 369, 'nt', '$2y$10$R5WUpIo.CklQ.zl51EXYIOxmO9Ae2Y482zK3tYXFGRglfVcquC3ai', 'Member', 'Not Done'),
(45, 473072709, 473072709, 'Laraño', 'Ritz', '', '2', '09277603828', 3, 34535, 'r', '$2y$10$7RdgIGW0FwLIMk07Gq4mMucfWHGpwz5Swl751tPZPmPionFs0rGvi', 'Member', 'Done'),
(46, 771538306, 771538306, 'a', 'a', 'a', '2', '09277603828', 2, 2, 'a', '$2y$10$dY.1RYfv0XJ1lwBYue.TO.16cGMzDjnkrUbR3r0HAsZ/LHudbfEea', 'Member', 'Done'),
(48, 209309531, 209309531, 'test', 't', 't', '3', '09277603828', 2, 2, 'test', '$2y$10$7LBRn47tHGktrzzpVdLN4e9ZxQzk05xaVJz3IFEgGbO43YNcVerne', 'Member', 'Not Done'),
(49, 298254319, 298254319, 'test2', 'a', '', '4', '09277603828', 4, 7777, 'aaa', '$2y$10$IEKpyd7OZ6Yxoou7so8goeXsy2sXT8.CW1IryvmEoZ0gvXc4iCntS', 'Member', 'Done');

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
  `status` enum('Paid','Not Paid','Pending') NOT NULL DEFAULT 'Not Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meter_reading`
--

INSERT INTO `meter_reading` (`reading_id`, `user_id`, `member_id`, `reading_date`, `time`, `previous_reading`, `current_reading`, `total_usage`, `current_charges`, `arrears_amount`, `total_amount_due`, `due_date`, `disconnection_date`, `billing_month`, `status`) VALUES
(161901449, 601672633, 658797145, '2025-02-05', '21:31:59', 23, 35, 12, 216.00, 14.00, 230.00, '2025-02-20', '2025-02-22', 'February 2025', 'Paid'),
(271006491, 601672633, 202743864, '2025-03-03', '12:20:55', 5, 6, 1, 18.00, 70.00, 88.00, '2025-03-18', '2025-03-20', 'March 2025', 'Paid'),
(306887664, 409614432, 589909318, '2024-07-17', '10:21:39', 0, 21, 21, 378.00, 0.00, 378.00, '2024-12-11', '2024-12-13', 'July 2024', 'Not Paid'),
(429245647, 601672633, 209309531, '2025-07-22', '22:01:54', 0, 1, 1, 18.00, 0.00, 18.00, '2025-08-06', '2025-08-08', 'July 2025', 'Paid'),
(441828404, 497364803, 658797145, '2024-06-03', '10:17:58', 0, 23, 23, 414.00, 0.00, 414.00, '2024-12-11', '2024-12-13', 'June 2024', 'Paid'),
(449448876, 601672633, 473072709, '2025-02-05', '21:21:25', 0, 13, 13, 234.00, 0.00, 234.00, '2025-02-20', '2025-02-22', 'February 2025', 'Paid'),
(598513734, 601672633, 473072709, '2025-04-30', '08:37:38', 13, 63, 50, 900.00, 0.00, 900.00, '2025-05-15', '2025-05-17', 'April 2025', 'Paid'),
(639845515, 601672633, 356861103, '2025-02-05', '21:33:47', 2, 25, 23, 414.00, 0.00, 414.00, '2025-02-20', '2025-02-22', 'February 2025', 'Paid'),
(673068509, 409614432, 356861103, '2024-06-12', '10:20:00', 0, 2, 2, 36.00, 0.00, 36.00, '2024-12-11', '2024-12-13', 'August 2024', 'Paid'),
(693382583, 409614432, 802992535, '2024-09-19', '10:22:04', 0, 34, 34, 612.00, 0.00, 612.00, '2024-12-11', '2024-12-13', 'September 2024', 'Paid'),
(719141656, 409614432, 202743864, '2024-10-24', '10:21:22', 0, 5, 5, 90.00, 0.00, 90.00, '2024-12-11', '2024-12-13', 'October 2024', 'Paid'),
(747282112, 601672633, 298254319, '2025-07-19', '19:52:47', 0, 12, 12, 216.00, 0.00, 216.00, '2025-08-03', '2025-08-05', 'July 2025', 'Not Paid'),
(747780578, 409614432, 184309635, '2024-05-14', '10:21:29', 0, 7, 7, 126.00, 0.00, 126.00, '2024-12-11', '2024-12-13', 'May 2024', 'Paid'),
(755255125, 601672633, 771538306, '2025-04-03', '10:45:14', 0, 123, 123, 2214.00, 0.00, 2214.00, '2025-04-18', '2025-04-20', 'April 2025', 'Not Paid'),
(766603194, 409614432, 196094140, '2024-04-02', '10:21:53', 0, 71, 71, 1278.00, 0.00, 1278.00, '2024-12-11', '2024-12-13', 'April 2024', 'Paid'),
(952846428, 601672633, 473072709, '2025-06-08', '11:59:56', 63, 88, 25, 450.00, 100.00, 550.00, '2025-06-23', '2025-06-25', 'June 2025', 'Not Paid');

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

--
-- Dumping data for table `pending`
--

INSERT INTO `pending` (`pending_id`, `member_id`, `reading_id`, `date_received`, `current_charges`, `arrears_amount`, `total_amount_due`, `billing_month`, `proof_image_path`) VALUES
(3, 140022718, 763866228, '2024-11-17', 18.00, 0.00, 18.00, 'October 2024', 'uploads/lg2.png'),
(5, 650894394, 667557148, '2024-11-28', 216.00, 0.00, 216.00, 'November 2024', 'uploads/462564866_566964909366043_1975193849161993134_n.jpg'),
(8, 473072709, 295440327, '2024-11-28', 90.00, 0.00, 90.00, 'November 2024', 'uploads/462564866_566964909366043_1975193849161993134_n.jpg'),
(10, 650894394, 505731081, '2024-11-29', 234.00, 0.00, 234.00, 'November 2024', 'uploads/462564866_566964909366043_1975193849161993134_n.jpg');

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
(25, 409614432, 0, 'z', '$2y$10$ErUHoCdUYqxBnpaw1kliHuPQcPDHs8TvCFe0Ff9RAlPJnUD9DoMf6', 'President'),
(43, 497364803, 0, 'tr', '$2y$10$Kam/K8KiJNPThZBDjfKwBecg119yPivBRo8PdfPmTiXsCpiTbSAue', 'Treasurer'),
(44, 601672633, 0, 'mr', '$2y$10$fi23RSj37aHWqfymugSunOxrM6YqiXJFAQBggeK4km/GwBS4zRYIS', 'Meter Reader'),
(47, 356861103, 356861103, 'kl', '$2y$10$sgMcdZdgKNqXLjPefr7.Hu/FXk7.zYCWSv15FGqnFJmGd3El9R0Im', 'Member'),
(48, 580277208, 580277208, 'cb', '$2y$10$L6.EcHHRsBvJyJxSw6LsgeDffpdoe27Vaj/7G/BwGDqqJcaBBofTW', 'Member'),
(49, 202743864, 202743864, 'nb', '$2y$10$LDBuwrr18HmhTdPtPLyazOb9Q/fo/IIibH4/R4y9lzn8vn..wFYey', 'Member'),
(50, 184309635, 184309635, 'in', '$2y$10$q8tRUI.ck313i7E1sx0Ys.8g7zHtvzuRkI.w/3OklK5WQFlAtsfMG', 'Member'),
(51, 589909318, 589909318, 'mc', '$2y$10$LvrZ33ewPT2LCbkdYFYLS.w/13DpUhPns/E4l.In2dDa5Cdto8gne', 'Member'),
(52, 196094140, 196094140, 'rb', '$2y$10$8zTI87aN2wutktKlZyz1rOwIfXfBicWVFsR6FX8HjdJUhFOIx/S9q', 'Member'),
(53, 802992535, 802992535, 'nt', '$2y$10$R5WUpIo.CklQ.zl51EXYIOxmO9Ae2Y482zK3tYXFGRglfVcquC3ai', 'Member'),
(54, 473072709, 473072709, 'r', '$2y$10$7RdgIGW0FwLIMk07Gq4mMucfWHGpwz5Swl751tPZPmPionFs0rGvi', 'Member'),
(55, 771538306, 771538306, 'a', '$2y$10$dY.1RYfv0XJ1lwBYue.TO.16cGMzDjnkrUbR3r0HAsZ/LHudbfEea', 'Member'),
(57, 209309531, 209309531, 'test', '$2y$10$7LBRn47tHGktrzzpVdLN4e9ZxQzk05xaVJz3IFEgGbO43YNcVerne', 'Member'),
(58, 298254319, 298254319, 'aaa', '$2y$10$IEKpyd7OZ6Yxoou7so8goeXsy2sXT8.CW1IryvmEoZ0gvXc4iCntS', 'Member');

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
  MODIFY `arrears_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `pending`
--
ALTER TABLE `pending`
  MODIFY `pending_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
