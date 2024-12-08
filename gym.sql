-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2024 at 05:09 PM
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
(4, 'Premium', 'Yearly', 4000000.00, 2, 'active', '2024-11-30 07:42:12', '2024-12-06 23:06:55'),
(5, 'Special Package', 'Yearly', 2000000.00, 1, 'active', '2024-12-06 22:40:04', '2024-12-06 22:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `pt_requests`
--

CREATE TABLE `pt_requests` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `ptId` int(11) NOT NULL,
  `date` date NOT NULL,
  `slotId` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pt_requests`
--

INSERT INTO `pt_requests` (`id`, `userId`, `ptId`, `date`, `slotId`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 15, 25, '2024-12-10', 5, 'approved', '2024-12-08 15:50:27', '2024-12-08 15:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `pt_slots`
--

CREATE TABLE `pt_slots` (
  `id` int(11) NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pt_slots`
--

INSERT INTO `pt_slots` (`id`, `startTime`, `endTime`, `createdAt`, `updatedAt`) VALUES
(1, '08:00:00', '09:30:00', '2024-12-08 15:31:24', '2024-12-08 15:31:24'),
(2, '09:30:00', '11:00:00', '2024-12-08 15:31:24', '2024-12-08 15:31:24'),
(3, '11:00:00', '12:30:00', '2024-12-08 15:31:24', '2024-12-08 15:31:24'),
(4, '12:30:00', '14:00:00', '2024-12-08 15:31:24', '2024-12-08 15:31:24'),
(5, '14:00:00', '15:30:00', '2024-12-08 15:31:24', '2024-12-08 15:31:24'),
(6, '15:30:00', '17:00:00', '2024-12-08 15:31:24', '2024-12-08 15:31:24');

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
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `pt_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `sessions` int(11) DEFAULT NULL,
  `type` enum('pt','package') NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `package_id`, `pt_id`, `price`, `payment_method`, `status`, `sessions`, `type`, `transaction_date`, `createdAt`, `updatedAt`) VALUES
(1, 1, 3, NULL, 500000.00, 'cash', 'pending', NULL, 'package', '2024-12-08 22:35:43', '2024-12-08 15:35:43', '2024-12-08 15:35:43'),
(2, 15, NULL, 25, 500000.00, 'credit_card', 'pending', 5, 'pt', '2024-12-08 22:39:10', '2024-12-08 15:39:10', '2024-12-08 15:39:10'),
(3, 15, NULL, 25, 500000.00, 'credit_card', 'pending', 5, 'pt', '2024-12-08 22:39:22', '2024-12-08 15:39:22', '2024-12-08 15:39:22');

-- --------------------------------------------------------

--
-- Table structure for table `usermemberships`
--

CREATE TABLE `usermemberships` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `membershipStart` date NOT NULL,
  `membershipEnd` date NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usermemberships`
--

INSERT INTO `usermemberships` (`id`, `userId`, `membershipStart`, `membershipEnd`, `createdAt`, `updatedAt`) VALUES
(6, 15, '2024-12-08', '2029-06-08', '2024-12-08 08:47:21', '2024-12-08 14:50:33');

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
(3, 'JohnDoe', '0987654344', 'datnd10.dev@gmail.com', '$2y$10$7GYZodSLoIkxNz2zy6hH6.wPWqEeVLiJbKnBOGakrZVXZG9HTypYG', 'Admin', NULL, '974641', '2024-12-02 17:57:24', '2024-12-02 10:42:24', '2024-12-04 15:29:21', 0),
(14, 'datngu', '0363738383', 'dat@gmail.com', '$2y$10$fhTW6m7aV4AJu/bMv2hb6esqgcYq5POUqLRei6pga7RnBpmySNzP.', 'pt', '../userimage/datngu_1733350635.JPG', NULL, NULL, '2024-12-04 22:17:15', '2024-12-04 22:17:15', 1),
(15, 'Minh Đưc', '0987654314', 'ductao28102003@gmail.com', '$2y$10$4.5LpDxVpEjiRFodvXnlceOKf6CXVpNY8nWWAt2WTXG4xj5GsohYS', 'User', 'icon-user-15.jpg', NULL, NULL, '2024-12-04 22:29:29', '2024-12-05 21:23:10', 1),
(24, 'Tào Đức', '0353293030', 'ductao213@gmail.com', '$2y$10$.bhGod/FPPUofc0CIhPGb.4xD5f5vC2i16tJCyf8v4G.RNxNrqxc6', 'Admin', 'icon-user-15.jpg', NULL, NULL, '2024-12-05 08:13:00', '2024-12-05 22:26:15', 1),
(25, 'Đạt Đân', '0353293832', 'datdan@gmail.com', '$2y$10$P361IXUoPlhdcwI75XdsQezuEYFFqv/CpX7PEyZklsJi9KdGd5cae', 'pt', '../userimage/Đạt Đân_1733430026.png', NULL, NULL, '2024-12-05 20:20:26', '2024-12-05 20:49:16', 0),
(26, 'Hong Nguyen', '0363823838', 'kitnguyen278@gmail.com', '$2y$10$2udwiG3voehcYpG.FFw3ROMMCuKLKJSG0SKFHW3IQz.jCppPzKO8u', 'User', 'icon-user-15.jpg', NULL, NULL, '2024-12-06 08:57:10', '2024-12-06 08:57:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_pt_relations`
--

CREATE TABLE `user_pt_relations` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `ptId` int(11) NOT NULL,
  `sessionsLeft` int(11) NOT NULL DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_pt_relations`
--

INSERT INTO `user_pt_relations` (`id`, `userId`, `ptId`, `sessionsLeft`, `createdAt`, `updatedAt`) VALUES
(2, 15, 25, 9, '2024-12-08 15:39:10', '2024-12-08 15:58:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pt_requests`
--
ALTER TABLE `pt_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `ptId` (`ptId`),
  ADD KEY `slotId` (`slotId`);

--
-- Indexes for table `pt_slots`
--
ALTER TABLE `pt_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `pt_id` (`pt_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `usermemberships`
--
ALTER TABLE `usermemberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_pt_relations`
--
ALTER TABLE `user_pt_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `ptId` (`ptId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pt_requests`
--
ALTER TABLE `pt_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pt_slots`
--
ALTER TABLE `pt_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usermemberships`
--
ALTER TABLE `usermemberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_pt_relations`
--
ALTER TABLE `user_pt_relations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pt_requests`
--
ALTER TABLE `pt_requests`
  ADD CONSTRAINT `pt_requests_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pt_requests_ibfk_2` FOREIGN KEY (`ptId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pt_requests_ibfk_3` FOREIGN KEY (`slotId`) REFERENCES `pt_slots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`pt_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usermemberships`
--
ALTER TABLE `usermemberships`
  ADD CONSTRAINT `usermemberships_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_pt_relations`
--
ALTER TABLE `user_pt_relations`
  ADD CONSTRAINT `user_pt_relations_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_pt_relations_ibfk_2` FOREIGN KEY (`ptId`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
