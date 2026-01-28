-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 28, 2026 at 05:17 AM
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
-- Database: `repair_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `office`
--

CREATE TABLE `office` (
  `office_id` int(11) NOT NULL,
  `office_name` varchar(255) NOT NULL,
  `office_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `office`
--

INSERT INTO `office` (`office_id`, `office_name`, `office_code`) VALUES
(1, 'คณะครุศาสตร์', 'EDU'),
(2, 'คณะเทคโนโลยีการเกษตร', 'AGRI'),
(3, 'คณะเทคโนโลยีสารสนเทศ', 'IT'),
(4, 'คณะพยาบาลศาสตร์และวิทยาการสุขภาพ', 'NURSE'),
(5, 'คณะมนุษยศาสตร์และสังคมศาสตร์', 'HUMAN'),
(6, 'คณะวิทยาการจัดการ', 'BA'),
(7, 'คณะวิทยาศาสตร์และเทคโนโลยี', 'SCI'),
(8, 'คณะวิศวกรรมศาสตร์และเทคโนโลยีอุตสาหกรรม', 'ENG'),
(9, 'วิทยาลัยนวัตกรรมอาหารและอุตสาหกรรมบริการ', 'ARC'),
(10, 'สถาบันวิจัยและส่งเสริมศิลปวัฒนธรรม', 'CULTURE'),
(11, 'สำนักงานอธิการบดี', 'ADMIN'),
(12, 'สำนักส่งเสริมวิชาการและงานทะเบียน', 'REG'),
(13, 'สำนักวิทยบริการและเทคโนโลยีสารสนเทศ', 'LIB');

-- --------------------------------------------------------

--
-- Table structure for table `office_issues`
--

CREATE TABLE `office_issues` (
  `id` int(10) UNSIGNED NOT NULL,
  `office_id` int(11) NOT NULL,
  `issue_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `office_issues`
--

INSERT INTO `office_issues` (`id`, `office_id`, `issue_name`) VALUES
(1, 3, 'คอมพิวเตอร์ / โน้ตบุ๊ค'),
(2, 3, 'ปริ้นเตอร์'),
(3, 3, 'ระบบอินเทอร์เน็ต'),
(4, 3, 'ระบบเครือข่ายภายใน'),
(5, 3, 'โปรเจคเตอร์'),
(6, 3, 'ซอฟต์แวร์เฉพาะทาง'),
(7, 6, 'คอมพิวเตอร์สำนักงาน'),
(8, 6, 'ปริ้นเตอร์ส่วนกลาง'),
(9, 6, 'ระบบโปรเจคเตอร์ห้องเรียน'),
(10, 6, 'ระบบอินเทอร์เน็ต'),
(11, 1, 'ระบบ Smart Classroom'),
(12, 1, 'โปรเจคเตอร์'),
(13, 1, 'Tablet สำหรับสอน'),
(14, 1, 'ระบบไมโครโฟน'),
(15, 7, 'คอมพิวเตอร์ Lab'),
(16, 7, 'อุปกรณ์ทดลอง'),
(17, 7, 'เครื่องพิมพ์ Lab'),
(18, 8, 'คอมพิวเตอร์'),
(19, 8, 'อุปกรณ์วิศวกรรม'),
(20, 8, 'IoT Device'),
(21, 8, 'โปรเจคเตอร์'),
(22, 9, 'เครื่อง Plotter'),
(23, 9, 'คอมพิวเตอร์ออกแบบ'),
(24, 9, 'โปรเจคเตอร์'),
(25, 2, 'คอมพิวเตอร์งานเอกสาร'),
(26, 2, 'ปริ้นเตอร์'),
(27, 2, 'ระบบอินเทอร์เน็ต'),
(28, 4, 'คอมพิวเตอร์สำนักงาน'),
(29, 4, 'เครื่องวัดสัญญาณ Electronic'),
(30, 4, 'ปริ้นเตอร์งานวิจัย'),
(31, 5, 'คอมพิวเตอร์'),
(32, 5, 'โปรเจคเตอร์'),
(33, 5, 'ระบบอินเทอร์เน็ต'),
(34, 13, 'ระบบยืม-คืน'),
(35, 13, 'คอมพิวเตอร์บริการ'),
(36, 13, 'ปริ้นเตอร์เอกสาร'),
(37, 11, 'คอมพิวเตอร์สำนักงาน'),
(38, 11, 'ปริ้นเตอร์'),
(39, 11, 'ระบบอินเทอร์เน็ต'),
(40, 10, 'อุปกรณ์จัดแสดง'),
(41, 10, 'คอมพิวเตอร์สำนักงาน'),
(42, 10, 'ระบบโปรเจคเตอร์'),
(43, 12, 'คอมพิวเตอร์สำนักงาน'),
(44, 12, 'ปริ้นเตอร์งานทะเบียน'),
(45, 12, 'ระบบเครือข่าย');

-- --------------------------------------------------------

--
-- Table structure for table `repair_tickets`
--

CREATE TABLE `repair_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_path` longtext DEFAULT NULL,
  `status_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `repair_tickets`
--

INSERT INTO `repair_tickets` (`id`, `user_id`, `office_id`, `title`, `description`, `image_path`, `status_id`, `created_at`, `updated_at`) VALUES
(52, 10, 1, 'ระบบ Smart Classroom', '8*898', '[\"uploads\\/repair_1765270209_1506.jpg\",\"uploads\\/repair_1765270209_8861.jpg\",\"uploads\\/repair_1765270209_5771.jpg\",\"uploads\\/repair_1765270209_1768.png\",\"uploads\\/repair_1765270209_5682.jpg\"]', 4, '2025-12-09 08:50:09', '2026-01-23 07:23:14'),
(53, 10, 4, 'คอมพิวเตอร์สำนักงาน', '95', '[\"uploads\\/repair_1765270285_7693.jpeg\"]', 1, '2025-12-09 08:51:25', '2025-12-09 08:51:25'),
(55, 10, 1, 'ระบบ Smart Classroom', 'กก', '[\"uploads\\/repair_1765270831_1240.png\"]', 2, '2025-12-09 09:00:31', '2026-01-22 16:07:27'),
(56, 10, 1, 'ระบบ Smart Classroom', 'ๆห', 'uploads/repair_1765270881_9792.jpg', 1, '2025-12-09 09:01:21', '2025-12-09 09:01:21'),
(57, 10, 1, 'ระบบ Smart Classroom', '8686', '[\"uploads\\/repair_1765272078_6571.jpg\"]', 1, '2025-12-09 09:21:18', '2025-12-09 09:21:18'),
(58, 10, 4, 'คอมพิวเตอร์สำนักงาน', 'gnd', 'uploads/repair_1765272146_7949.jpg', 4, '2025-12-09 09:22:26', '2025-12-25 07:55:17'),
(59, 10, 1, 'ระบบ Smart Classroom', '+5', '[\"uploads\\/repair_1765272832_3582.jpg\"]', 1, '2025-12-09 09:33:52', '2025-12-09 09:33:52'),
(60, 10, 1, 'ระบบ Smart Classroom', 'หๆฟห', 'uploads/repair_1765272859_6786.jpg', 1, '2025-12-09 09:34:19', '2025-12-09 09:34:19'),
(61, 10, 8, 'IoT Device', 'Keycap', '[\"uploads\\/repair_1765523735_7639.jpg\"]', 1, '2025-12-12 07:15:35', '2025-12-12 07:15:35'),
(62, 10, 1, 'ระบบ Smart Classroom', 'Zz', NULL, 2, '2025-12-12 07:19:13', '2025-12-25 08:05:43'),
(63, 10, 1, 'ระบบ Smart Classroom', 'Wwww', NULL, 1, '2025-12-12 07:32:25', '2025-12-12 07:32:25'),
(64, 10, 4, 'คอมพิวเตอร์สำนักงาน', 'Ttt', '[\"uploads\\/repair_1765526162_6716.jpg\",\"uploads\\/repair_1765526162_7850.jpg\",\"uploads\\/repair_1765526162_9519.jpg\",\"uploads\\/repair_1765526162_9753.jpg\",\"uploads\\/repair_1765526162_2388.jpg\"]', 2, '2025-12-12 07:56:02', '2026-01-05 09:25:08'),
(65, 10, 7, 'คอมพิวเตอร์ Lab', 'This was my favorite', '[\"uploads\\/repair_1765526811_2442.jpeg\",\"uploads\\/repair_1765526811_5761.jpeg\",\"uploads\\/repair_1765526811_2730.jpeg\",\"uploads\\/repair_1765526811_3815.jpeg\",\"uploads\\/repair_1765526811_1344.jpeg\"]', 4, '2025-12-12 08:06:50', '2025-12-25 09:51:01'),
(66, 10, 1, 'ระบบ Smart Classroom', 'ปผ', '[\"uploads\\/repair_1765527172_7177.jpeg\",\"uploads\\/repair_1765527172_2805.jpeg\"]', 4, '2025-12-12 08:12:52', '2025-12-25 09:55:10'),
(68, 10, 1, 'ระบบ Smart Classroom', 'ฟกฟกฟห', '[\"uploads\\/repair_1765528029_9407.jpeg\",\"uploads\\/repair_1765528029_3265.jpg\",\"uploads\\/repair_1765528029_1164.jpg\",\"uploads\\/repair_1765528029_4541.jpg\",\"uploads\\/repair_1765528029_1824.jpg\"]', 4, '2025-12-12 08:27:09', '2025-12-25 09:59:39'),
(69, 8, 1, 'ระบบ Smart Classroom', '12345', '[\"uploads\\/repair_1765787030_6797.jpg\",\"uploads\\/repair_1765787030_5738.jpg\"]', 1, '2025-12-15 08:23:50', '2025-12-15 08:23:50'),
(70, 8, 1, 'ระบบ Smart Classroom', 'ฟหหฟ', '[\"uploads\\/repair_1765793025_9671.jpg\"]', 1, '2025-12-15 10:03:45', '2025-12-25 09:50:57'),
(71, 8, 1, 'ระบบ Smart Classroom', 'ฟหฟห', '[\"uploads\\/repair_1765793032_3195.jpg\"]', 4, '2025-12-15 10:03:52', '2025-12-26 08:45:30'),
(72, 8, 5, 'คอมพิวเตอร์', 'ฟหห', '[\"uploads\\/repair_1765793039_2565.jpeg\"]', 4, '2025-12-15 10:03:59', '2025-12-25 09:45:51'),
(73, 8, 5, 'โปรเจคเตอร์', 'ฟหฟห', '[\"uploads\\/repair_1765793047_5900.jpg\"]', 1, '2025-12-15 10:04:07', '2026-01-05 09:24:58'),
(74, 8, 1, 'ระบบ Smart Classroom', 'ฟหฟห', NULL, 4, '2025-12-15 10:04:12', '2026-01-22 16:45:22'),
(75, 8, 1, 'โปรเจคเตอร์', 'ฟหฟ', '[\"uploads\\/repair_1765793080_7450.jpg\"]', 4, '2025-12-15 10:04:40', '2025-12-25 09:45:39'),
(76, 8, 4, 'คอมพิวเตอร์สำนักงาน', 'ฟหฟห', NULL, 3, '2025-12-15 10:04:50', '2025-12-29 04:42:31'),
(77, 8, 1, 'ระบบ Smart Classroom', 'หหฟหฟห', NULL, 2, '2025-12-15 10:05:08', '2026-01-20 04:52:56'),
(78, 11, 1, 'ระบบ Smart Classroom', '4', '[\"uploads\\/repair_1765861658_2922.jpeg\"]', 3, '2025-12-16 05:07:38', '2026-01-05 10:23:29'),
(80, 8, 1, 'ระบบ Smart Classroom', '555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555', '[\"uploads\\/repair_1766633091_9189.jpg\"]', 1, '2025-12-25 03:24:51', '2026-01-06 07:39:46'),
(81, 10, 2, 'คอมพิวเตอร์งานเอกสาร', 'ะึีันสี้ยวรี่ยน่', '[\"uploads\\/repair_1766657729_2079.jpg\",\"uploads\\/repair_1766657729_5943.jpg\"]', 1, '2025-12-25 10:15:29', '2026-01-05 10:23:24'),
(82, 8, 1, 'Tablet สำหรับสอน', '555', '[\"uploads\\/repair_1769068805_5764.jpg\",\"uploads\\/repair_1769068805_7107.jpg\",\"uploads\\/repair_1769068805_1400.jpg\",\"uploads\\/repair_1769068805_3460.jpg\"]', 4, '2026-01-22 08:00:05', '2026-01-22 16:54:33'),
(83, 8, 1, 'Tablet สำหรับสอน', '5555555', '[\"uploads\\/repair_1769070661_3871.jpg\"]', 4, '2026-01-22 08:31:01', '2026-01-23 07:23:47'),
(84, 8, 4, 'คอมพิวเตอร์สำนักงาน', '554', NULL, 4, '2026-01-22 08:31:36', '2026-01-23 04:52:37'),
(85, 8, 4, 'ปริ้นเตอร์งานวิจัย', 'l,,;;', '[\"uploads\\/repair_1769090505_3383.jpg\"]', 4, '2026-01-22 14:01:45', '2026-01-23 07:34:35'),
(86, 10, 2, 'คอมพิวเตอร์งานเอกสาร', '5555', NULL, 1, '2026-01-23 07:28:32', '2026-01-23 07:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `tableroles`
--

CREATE TABLE `tableroles` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_label` varchar(50) NOT NULL,
  `role_color` varchar(20) NOT NULL DEFAULT 'secondary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tableroles`
--

INSERT INTO `tableroles` (`id`, `role_name`, `role_label`, `role_color`) VALUES
(1, 'user', 'ผู้ใช้', 'secondary'),
(2, 'staff', 'สตาฟ', 'success'),
(4, 'admin', 'แอดมิน', 'danger');

-- --------------------------------------------------------

--
-- Table structure for table `tablestatus`
--

CREATE TABLE `tablestatus` (
  `id` int(10) UNSIGNED NOT NULL,
  `status_name` varchar(50) NOT NULL,
  `status_label` varchar(50) DEFAULT NULL,
  `status_color` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tablestatus`
--

INSERT INTO `tablestatus` (`id`, `status_name`, `status_label`, `status_color`) VALUES
(1, 'new', 'ใหม่', 'secondary'),
(2, 'pending', 'รอดำเนินการ', 'danger'),
(3, 'in_progress', 'กำลังดำเนินการ', 'warning'),
(4, 'completed', 'เสร็จสิ้น', 'success');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `line_id` varchar(255) DEFAULT NULL,
  `role_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `office_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_to_edit` tinyint(1) DEFAULT 0,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'profile.png',
  `login_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `line_id`, `role_id`, `office_id`, `created_at`, `approved_to_edit`, `avatar`, `login_count`) VALUES
(8, 'สุดหล่อ บางประอิน', '654274118', '08555555555', '$2y$10$Uz86FGFGxmGSCdSLbBMonu61Ew54h3n4ocSWxVga9wYUYGTUO7b6.', '', 1, 1, '2025-12-04 07:02:21', 0, 'profile.png', 8),
(10, 'บารมี จระเข้', '654274117', '069555555', '$2y$10$MrDmaXKifxfMfpk/3PX7Mux03qJONgiNGFgHjWuTFcPRM0jw.37Au', '', 2, 2, '2025-12-04 07:51:53', 0, 'profile.png', 5),
(11, '654274116', '654274116', '08555555', '$2y$10$uyg5yoRnCl2uTqid4oX0HuejLrNoOwM7Qm1jNWm5hv.nyPhK0QWtO', '', 1, 1, '2025-12-04 08:41:10', 0, 'profile.png', 0),
(12, 'addddmin', '654274120', '065588888', '$2y$10$53AG2dNyioJ02S85vlhGtuJmygACvxA5VOdBlNOuls2yNPhpFN5K6', '', 4, 1, '2025-12-24 09:36:39', 0, 'uploads/avatars/avatar_12_1769094751.jpeg', 18),
(13, 'SuperAdmin', '654274111', '08-88888888', '$2y$10$UEF6yFP.BaiEZIMZCzk7.Oqq.yVEI9MZM5AF0AiqA97RTKXL.F.r.', '', 4, 12, '2025-12-25 06:41:03', 0, 'uploads/avatars/avatar_13_1766646713.jpg', 0),
(14, 'กบ แมว', '694274101', '098757777777777', '$2y$10$4n10fSfFjDftab0inUDDcOOaxeIppIhH/zhDp9vqifEEJPAl0KKda', '', 1, 1, '2026-01-22 13:18:48', 0, 'profile.png', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `office`
--
ALTER TABLE `office`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `unique_office_code` (`office_code`),
  ADD UNIQUE KEY `office_code` (`office_code`),
  ADD UNIQUE KEY `office_code_2` (`office_code`);

--
-- Indexes for table `office_issues`
--
ALTER TABLE `office_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_office_issues_office` (`office_id`);

--
-- Indexes for table `repair_tickets`
--
ALTER TABLE `repair_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `fk_repair_tickets_status` (`status_id`);

--
-- Indexes for table `tableroles`
--
ALTER TABLE `tableroles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `tablestatus`
--
ALTER TABLE `tablestatus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_role` (`role_id`),
  ADD KEY `fk_users_office` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `office`
--
ALTER TABLE `office`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `office_issues`
--
ALTER TABLE `office_issues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `repair_tickets`
--
ALTER TABLE `repair_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `tableroles`
--
ALTER TABLE `tableroles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tablestatus`
--
ALTER TABLE `tablestatus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `office_issues`
--
ALTER TABLE `office_issues`
  ADD CONSTRAINT `fk_office_issues_office` FOREIGN KEY (`office_id`) REFERENCES `office` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `repair_tickets`
--
ALTER TABLE `repair_tickets`
  ADD CONSTRAINT `fk_repair_office` FOREIGN KEY (`office_id`) REFERENCES `office` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repair_tickets_status` FOREIGN KEY (`status_id`) REFERENCES `tablestatus` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `repair_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `office` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `tableroles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
