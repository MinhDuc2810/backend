-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2024 at 03:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `ptId` int(11) NOT NULL,
  `slotId` int(11) NOT NULL,
  `bookingDate` date NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `type`, `price`, `duration`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'Updated Gym Package', 'Yearly', 1000000.00, 365, 'inactive', '2024-11-28 13:46:35', '2024-11-28 14:12:57'),
(2, 'Premium Package', 'Monthly', 500000.00, 30, 'active', '2024-11-28 13:53:05', '2024-11-28 13:53:05'),
(3, 'Premium Package', 'Monthly', 500000.00, 30, 'active', '2024-11-30 07:41:30', '2024-11-30 07:41:30'),
(4, 'Premium Package', 'Monthly', 500000.00, 30, 'active', '2024-11-30 07:42:12', '2024-11-30 07:42:12');

-- --------------------------------------------------------

--
-- Table structure for table `pts`
--

CREATE TABLE `pts` (
  `id` int(11) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `maxSessionsPerDay` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ptschedules`
--

CREATE TABLE `ptschedules` (
  `id` int(11) NOT NULL,
  `ptId` int(11) NOT NULL,
  `availableStart` time NOT NULL,
  `availableEnd` time NOT NULL,
  `slotDuration` int(11) DEFAULT 60,
  `date` date NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pttimeslots`
--

CREATE TABLE `pttimeslots` (
  `id` int(11) NOT NULL,
  `scheduleId` int(11) NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Available',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usermemberships`
--

CREATE TABLE `usermemberships` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `packageId` int(11) NOT NULL,
  `membershipStart` date NOT NULL,
  `membershipEnd` date NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userpackages`
--

CREATE TABLE `userpackages` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `packageId` int(11) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `remainingSessions` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `userName` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otpExpiresAt` datetime DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isActive` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `userName`, `phoneNumber`, `email`, `password`, `role`, `avatar`, `otp`, `otpExpiresAt`, `createdAt`, `updatedAt`, `isActive`) VALUES
(1, 'JaneDoe', '9876543210', 'janedoe@example.com', '$2y$10$/0wxjmuLjbFb20mRrZFqD./3EX1pW5sFAb7Ypq5ApIJEQJXrwp5ba', 'Admin', NULL, NULL, NULL, '2024-11-27 14:23:50', '2024-11-30 07:43:29', 0),
(2, 'JohnDoe1', '1234567890', 'johndoe1@example.com', '$2y$10$M5VrWj7DmJ8OXwlLtcIvNeJZLAv6x.NZYDysqWx2kA6wIUWSEHfrK', 'User', NULL, NULL, NULL, '2024-11-30 07:43:13', '2024-11-30 07:43:13', 0),
(3, 'JohnDoe', '0987654344', 'datnd10.dev@gmail.com', '$2y$10$7GYZodSLoIkxNz2zy6hH6.wPWqEeVLiJbKnBOGakrZVXZG9HTypYG', 'User', NULL, '974641', '2024-12-02 17:57:24', '2024-12-02 10:42:24', '2024-12-02 10:42:24', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slotId` (`slotId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `ptId` (`ptId`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pts`
--
ALTER TABLE `pts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ptschedules`
--
ALTER TABLE `ptschedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ptId` (`ptId`);

--
-- Indexes for table `pttimeslots`
--
ALTER TABLE `pttimeslots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`),
  ADD KEY `scheduleId` (`scheduleId`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `usermemberships`
--
ALTER TABLE `usermemberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `packageId` (`packageId`);

--
-- Indexes for table `userpackages`
--
ALTER TABLE `userpackages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `packageId` (`packageId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pts`
--
ALTER TABLE `pts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ptschedules`
--
ALTER TABLE `ptschedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pttimeslots`
--
ALTER TABLE `pttimeslots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usermemberships`
--
ALTER TABLE `usermemberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userpackages`
--
ALTER TABLE `userpackages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`ptId`) REFERENCES `pts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`slotId`) REFERENCES `pttimeslots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ptschedules`
--
ALTER TABLE `ptschedules`
  ADD CONSTRAINT `ptschedules_ibfk_1` FOREIGN KEY (`ptId`) REFERENCES `pts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pttimeslots`
--
ALTER TABLE `pttimeslots`
  ADD CONSTRAINT `pttimeslots_ibfk_1` FOREIGN KEY (`scheduleId`) REFERENCES `ptschedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pttimeslots_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `usermemberships`
--
ALTER TABLE `usermemberships`
  ADD CONSTRAINT `usermemberships_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usermemberships_ibfk_2` FOREIGN KEY (`packageId`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `userpackages`
--
ALTER TABLE `userpackages`
  ADD CONSTRAINT `userpackages_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `userpackages_ibfk_2` FOREIGN KEY (`packageId`) REFERENCES `packages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
