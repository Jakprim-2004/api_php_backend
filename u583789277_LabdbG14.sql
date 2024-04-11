-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 11, 2024 at 05:08 AM
-- Server version: 10.11.7-MariaDB-cll-lve
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u583789277_LabdbG14`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `booth_id` int(11) NOT NULL,
  `booking_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `slip_path` varchar(255) DEFAULT NULL,
  `booking_status` varchar(50) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `seller_info` varchar(255) DEFAULT NULL,
  `booking_code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `member_id`, `booth_id`, `booking_date`, `payment_date`, `price`, `slip_path`, `booking_status`, `event_id`, `zone_id`, `seller_info`, `booking_code`) VALUES
(106, 17, 21, '2024-03-29', '2024-03-29', 1000.00, 'path/imgs/1', 'อนุมัติแล้ว', 13, 2, 'ตำกุ้ง', '1'),
(107, 17, 22, '2024-03-29', '0000-00-00', 1000.00, '', 'ยกเลิกการจอง', 13, 2, 'ตำกุ้ง', '2'),
(108, 17, 22, '2024-03-29', '2024-03-29', 1000.00, 'path/imgs/1', 'ชำระเงิน', 13, 2, 'ตำกุ้ง', '3'),
(109, 17, 23, '2024-03-29', '2024-03-29', 1000.00, 'path/pay1', 'จอง', 13, 2, 'ตำกุ้ง', '4'),
(110, 17, 20, '2024-03-29', '2024-03-29', 1000.00, 'path/pay1', 'จอง', 13, 2, 'ตำกุ้ง', '4'),
(111, 16, 24, '2024-03-29', '2024-03-29', 1000.00, 'path/pay1', 'จอง', 13, 2, 'ตำกุ้ง', '4');

-- --------------------------------------------------------

--
-- Table structure for table `Booths`
--

CREATE TABLE `Booths` (
  `booth_id` int(11) NOT NULL,
  `booth_name` varchar(100) DEFAULT NULL,
  `booth_size` varchar(50) DEFAULT NULL,
  `booth_status` varchar(255) DEFAULT NULL,
  `booth_price` decimal(10,2) DEFAULT NULL,
  `product` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Booths`
--

INSERT INTO `Booths` (`booth_id`, `booth_name`, `booth_size`, `booth_status`, `booth_price`, `product`) VALUES
(21, 'A1', '3x3', 'จองแล้ว', 1000.00, 'ผลไม้'),
(22, 'A5', '3x4', 'ชำระเงิน', 350.00, 'ดนตรี'),
(23, 'A6', '3x4', 'อยู่ระหว่างตรวจสอบ', 350.00, 'ดนตรี'),
(24, 'A7', '3x4', 'อยู่ระหว่างตรวจสอบ', 350.00, 'ดนตรี');

-- --------------------------------------------------------

--
-- Table structure for table `Events`
--

CREATE TABLE `Events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Events`
--

INSERT INTO `Events` (`event_id`, `event_name`, `start_date`, `end_date`) VALUES
(13, 'สุดมันส์', '2024-04-05', '2024-04-28'),
(15, 'สุดมันส์2', '2024-03-31', '2024-04-07');

-- --------------------------------------------------------

--
-- Table structure for table `Members`
--

CREATE TABLE `Members` (
  `member_id` int(11) NOT NULL,
  `title_name` varchar(10) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Members`
--

INSERT INTO `Members` (`member_id`, `title_name`, `first_name`, `last_name`, `phone_number`, `email`, `password`) VALUES
(16, 'นาย', 'ไข่มุข', 'จันทร์พริ้ม', '065692293xxx', 'kaimuk.j@ku.th', '1'),
(17, 'นาง', 'ไข่นุ้ย', 'จุ๊กกรู๊ว', '05555', 'caniyaya21@gmail.com', '1');

-- --------------------------------------------------------

--
-- Table structure for table `Zones`
--

CREATE TABLE `Zones` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(100) DEFAULT NULL,
  `zone_info` text DEFAULT NULL,
  `booth_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Zones`
--

INSERT INTO `Zones` (`zone_id`, `zone_name`, `zone_info`, `booth_count`) VALUES
(2, 'Zone A', 'A', 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_event_id` (`event_id`);

--
-- Indexes for table `Booths`
--
ALTER TABLE `Booths`
  ADD PRIMARY KEY (`booth_id`);

--
-- Indexes for table `Events`
--
ALTER TABLE `Events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `Members`
--
ALTER TABLE `Members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Zones`
--
ALTER TABLE `Zones`
  ADD PRIMARY KEY (`zone_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `Booths`
--
ALTER TABLE `Booths`
  MODIFY `booth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `Events`
--
ALTER TABLE `Events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Members`
--
ALTER TABLE `Members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Zones`
--
ALTER TABLE `Zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_event_id` FOREIGN KEY (`event_id`) REFERENCES `Events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
