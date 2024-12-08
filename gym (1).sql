-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 08, 2024 lúc 09:56 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `gym`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
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
-- Cấu trúc bảng cho bảng `packages`
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
-- Đang đổ dữ liệu cho bảng `packages`
--

INSERT INTO `packages` (`id`, `name`, `type`, `price`, `duration`, `status`, `createdAt`, `updatedAt`) VALUES
(1, 'Updated Gym Package', 'Yearly', 1000000.00, 365, 'inactive', '2024-11-28 13:46:35', '2024-11-28 14:12:57'),
(2, 'Premium Package', 'Monthly', 500000.00, 30, 'active', '2024-11-28 13:53:05', '2024-11-28 13:53:05'),
(3, 'Premium Package', 'Monthly', 500000.00, 30, 'active', '2024-11-30 07:41:30', '2024-11-30 07:41:30'),
(4, 'Premium', 'Yearly', 4000000.00, 2, 'active', '2024-11-30 07:42:12', '2024-12-06 23:06:55'),
(5, 'Special Package', 'Yearly', 2000000.00, 1, 'active', '2024-12-06 22:40:04', '2024-12-06 22:40:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `pts`
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
-- Cấu trúc bảng cho bảng `ptschedules`
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
-- Cấu trúc bảng cho bảng `pttimeslots`
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
-- Cấu trúc bảng cho bảng `settings`
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
-- Cấu trúc bảng cho bảng `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `package_id`, `price`, `payment_method`, `status`, `transaction_date`, `createdAt`, `updatedAt`) VALUES
(5, 15, 1, 300.00, 'cash', 'completed', '2024-12-07 19:31:21', '2024-12-07 19:31:21', '2024-12-07 23:13:20'),
(6, 15, 3, 500000.00, 'Cash', 'completed', '2024-12-08 01:12:46', '2024-12-08 01:12:46', '2024-12-08 01:13:45'),
(7, 15, 2, 500000.00, 'Cash', 'completed', '2024-12-08 01:15:11', '2024-12-08 01:15:11', '2024-12-08 01:15:45'),
(8, 15, 4, 4000000.00, 'Cash', 'completed', '2024-12-08 01:23:16', '2024-12-08 01:23:16', '2024-12-08 01:23:51'),
(9, 15, 4, 4000000.00, 'Cash', 'completed', '2024-12-08 01:25:13', '2024-12-08 01:25:13', '2024-12-08 01:25:35'),
(10, 15, 2, 500000.00, 'Cash', 'completed', '2024-12-08 10:46:06', '2024-12-08 10:46:06', '2024-12-08 10:47:21'),
(11, 15, 4, 4000000.00, 'Cash', 'completed', '2024-12-08 10:48:39', '2024-12-08 10:48:39', '2024-12-08 10:49:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `usermemberships`
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
-- Đang đổ dữ liệu cho bảng `usermemberships`
--

INSERT INTO `usermemberships` (`id`, `userId`, `membershipStart`, `membershipEnd`, `createdAt`, `updatedAt`) VALUES
(6, 15, '2024-12-08', '2029-06-08', '2024-12-08 08:47:21', '2024-12-08 08:49:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `userpackages`
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
-- Cấu trúc bảng cho bảng `users`
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
-- Đang đổ dữ liệu cho bảng `users`
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

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slotId` (`slotId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `ptId` (`ptId`);

--
-- Chỉ mục cho bảng `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `pts`
--
ALTER TABLE `pts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `ptschedules`
--
ALTER TABLE `ptschedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ptId` (`ptId`);

--
-- Chỉ mục cho bảng `pttimeslots`
--
ALTER TABLE `pttimeslots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`userId`),
  ADD KEY `scheduleId` (`scheduleId`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Chỉ mục cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `usermemberships`
--
ALTER TABLE `usermemberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Chỉ mục cho bảng `userpackages`
--
ALTER TABLE `userpackages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `packageId` (`packageId`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `pts`
--
ALTER TABLE `pts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `ptschedules`
--
ALTER TABLE `ptschedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `pttimeslots`
--
ALTER TABLE `pttimeslots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `usermemberships`
--
ALTER TABLE `usermemberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `userpackages`
--
ALTER TABLE `userpackages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`ptId`) REFERENCES `pts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`slotId`) REFERENCES `pttimeslots` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ptschedules`
--
ALTER TABLE `ptschedules`
  ADD CONSTRAINT `ptschedules_ibfk_1` FOREIGN KEY (`ptId`) REFERENCES `pts` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `pttimeslots`
--
ALTER TABLE `pttimeslots`
  ADD CONSTRAINT `pttimeslots_ibfk_1` FOREIGN KEY (`scheduleId`) REFERENCES `ptschedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pttimeslots_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `usermemberships`
--
ALTER TABLE `usermemberships`
  ADD CONSTRAINT `usermemberships_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `userpackages`
--
ALTER TABLE `userpackages`
  ADD CONSTRAINT `userpackages_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `userpackages_ibfk_2` FOREIGN KEY (`packageId`) REFERENCES `packages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
