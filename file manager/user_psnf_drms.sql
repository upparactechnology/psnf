-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2026 at 12:39 PM
-- Server version: 8.0.46-0ubuntu0.24.04.2
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_psnf_drms`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint NOT NULL,
  `admin_id` int DEFAULT NULL,
  `staff_id` int DEFAULT NULL,
  `event` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `staff_id`, `event`, `meta`, `ip`, `user_agent`, `created_at`) VALUES
(1, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:18:06'),
(2, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:18:24'),
(3, 1, NULL, 'admin_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:18:42'),
(4, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:30'),
(5, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:32'),
(6, 1, NULL, 'folder_created', 'test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:46'),
(7, 1, NULL, 'resource_uploaded', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:52'),
(8, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:57'),
(9, NULL, 1, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:20:33'),
(10, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:22:05'),
(11, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:22:11'),
(12, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:27:48'),
(13, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:27:53'),
(14, NULL, 1, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:28:26'),
(15, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:28:28'),
(16, 1, NULL, 'staff_created', 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:29:44'),
(17, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:42:31'),
(18, NULL, NULL, 'staff_login_failed', 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:32'),
(19, NULL, NULL, 'staff_login_failed', 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:40'),
(20, NULL, NULL, 'staff_login_failed', 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:46'),
(21, 1, NULL, 'staff_password_reset', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:56'),
(22, NULL, 2, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:07'),
(23, NULL, 2, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:08'),
(24, NULL, 2, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:24'),
(25, 1, NULL, 'resource_uploaded', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:46'),
(26, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:53'),
(27, NULL, 2, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:56'),
(28, NULL, 2, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:46:33'),
(29, NULL, 2, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:46:36'),
(30, 1, NULL, 'resource_uploaded', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:48:18'),
(31, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:48:27'),
(32, NULL, 2, 'resource_viewed', '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:48:30'),
(33, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:49:25'),
(34, 1, NULL, 'admin_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:37:28'),
(35, 1, NULL, 'folder_created', 'test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:37:38'),
(36, NULL, 2, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:32'),
(37, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:41'),
(38, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:42'),
(39, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:44'),
(40, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:46'),
(41, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:54'),
(42, NULL, 2, 'resource_viewed', '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:58'),
(43, NULL, 2, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:39:01'),
(44, NULL, 2, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:39:18'),
(45, 1, NULL, 'resource_uploaded', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:40:35'),
(46, NULL, 2, 'resource_viewed', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:40:44'),
(47, NULL, 2, 'resource_viewed', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:48:28'),
(48, NULL, 2, 'resource_viewed', '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:48:47'),
(49, NULL, 2, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:48:55'),
(50, NULL, 2, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:49:00'),
(51, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:57:57'),
(52, 1, NULL, 'assignment_saved', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:07'),
(53, NULL, 2, 'staff_logout', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:22'),
(54, NULL, 1, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:33'),
(55, NULL, 1, 'resource_viewed', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:41'),
(56, NULL, 1, 'resource_viewed', '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:42'),
(57, NULL, 1, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:43'),
(58, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:45'),
(59, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:46'),
(60, NULL, 1, 'resource_viewed', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:59:50'),
(61, NULL, 1, 'resource_viewed', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:01:42'),
(62, NULL, 1, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:01:43'),
(63, 1, NULL, 'folder_created', 'test 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:10:06'),
(64, NULL, 1, 'resource_viewed', '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:23:52'),
(65, 1, NULL, 'admin_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:00:27'),
(66, 1, NULL, 'admin_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:53:19'),
(67, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:54:53'),
(68, NULL, NULL, 'staff_login_failed', 'staff@psnf.local', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:57:43'),
(69, NULL, NULL, 'staff_login_failed', 'staff@psnf.local', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:57:54'),
(70, NULL, NULL, 'staff_login_failed', 'test@test.com', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:58:24'),
(71, NULL, 2, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:58:36'),
(72, NULL, 2, 'staff_login_success', '', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:59:06'),
(73, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:59:18'),
(74, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:59:30'),
(75, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:06:38'),
(76, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:07:02'),
(77, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:01'),
(78, NULL, 2, 'resource_viewed', '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:09'),
(79, NULL, 2, 'resource_viewed', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:14'),
(80, NULL, 2, 'resource_viewed', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:25'),
(81, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:57'),
(82, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:10:44'),
(83, 1, NULL, 'admin_logout', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:11:04'),
(84, NULL, NULL, 'staff_login_failed', 'staff@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:11:22'),
(85, NULL, 2, 'staff_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:11:28'),
(86, 1, NULL, 'admin_login_success', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:12:16'),
(87, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:12:40'),
(88, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:12:55'),
(89, 1, NULL, 'settings_updated', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:13:12'),
(90, 1, NULL, 'admin_login_success', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:52:50'),
(91, NULL, NULL, 'staff_login_failed', 'test@test.com', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:56:49'),
(92, NULL, NULL, 'staff_login_failed', 'test@test.com', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:56:57'),
(93, NULL, NULL, 'staff_login_failed', 'test@test.com', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:57:08'),
(94, 1, NULL, 'staff_password_reset', '2', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:57:31'),
(95, NULL, 2, 'staff_login_success', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:57:37'),
(96, NULL, 2, 'resource_viewed', '3', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:57:51'),
(97, NULL, 2, 'resource_viewed', '2', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:57:55'),
(98, NULL, 2, 'resource_viewed', '1', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:58:05'),
(99, 1, NULL, 'settings_updated', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:58:33'),
(100, 1, NULL, 'settings_updated', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:58:45'),
(101, 1, NULL, 'settings_updated', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:59:09'),
(102, 1, NULL, 'settings_updated', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 10:59:14'),
(103, 1, NULL, 'admin_login_success', '', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:57:33'),
(104, 1, NULL, 'resource_updated', '4', '117.198.166.158', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:57:46'),
(105, 1, NULL, 'admin_login_success', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 08:58:48'),
(106, 1, NULL, 'folder_created', 'test 123', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 08:59:11'),
(107, 1, NULL, 'resource_uploaded', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 08:59:28'),
(108, 1, NULL, 'assignment_saved', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:01:32'),
(109, 1, NULL, 'resource_updated', '5', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:02:08'),
(110, 1, NULL, 'resource_updated', '5', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:02:16'),
(111, 1, NULL, 'resource_updated', '5', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:02:51'),
(112, 1, NULL, 'assignment_saved', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:03:51'),
(113, 1, NULL, 'assignment_saved', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:03:55'),
(114, 1, NULL, 'resource_deleted', '4', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:05:27'),
(115, 1, NULL, 'settings_updated', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:07:31'),
(116, 1, NULL, 'settings_updated', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:07:37'),
(117, NULL, NULL, 'staff_login_failed', 'test@test.com', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:08:34'),
(118, 1, NULL, 'staff_password_reset', '2', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:08:39'),
(119, NULL, 2, 'staff_login_success', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:12:02'),
(120, NULL, 2, 'favorite_added', 'folder:1', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:12:11'),
(121, NULL, 2, 'resource_viewed', '3', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:12:41'),
(122, NULL, 2, 'resource_viewed', '2', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:12:51'),
(123, 1, NULL, 'settings_updated', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:13:13'),
(124, 1, NULL, 'settings_updated', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:13:48'),
(125, 1, NULL, 'settings_updated', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:13:56'),
(126, 1, NULL, 'settings_updated', '', '117.198.89.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 09:15:04'),
(127, NULL, NULL, 'admin_login_failed', 'admin@psnflocal', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:22:41'),
(128, NULL, NULL, 'admin_login_failed', 'admin@psnflocal', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:23:16'),
(129, NULL, NULL, 'admin_login_failed', 'admin@psnflocal', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:23:47'),
(130, NULL, NULL, 'admin_login_failed', 'admin@psnflocal', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:24:44'),
(131, 1, NULL, 'admin_login_success', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:26:03'),
(132, 1, NULL, 'staff_created', 'chhaya@psnf.com', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:31:57'),
(133, 1, NULL, 'staff_deleted', '2', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:32:10'),
(134, 1, NULL, 'staff_deleted', '1', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:32:17'),
(135, 1, NULL, 'folder_created', 'PVC Elementary 2026', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:33:26'),
(136, 1, NULL, 'folder_deleted', '2', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:33:50'),
(137, 1, NULL, 'folder_deleted', '1', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:33:58'),
(138, 1, NULL, 'folder_deleted', '3', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:34:04'),
(139, 1, NULL, 'folder_deleted', '4', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:34:08'),
(140, 1, NULL, 'folder_updated', '5', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:34:15'),
(141, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:36:46'),
(142, 1, NULL, 'resource_updated', '7', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:36:54'),
(143, 1, NULL, 'resource_deleted', '5', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:37:17'),
(144, 1, NULL, 'resource_deleted', '1', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:37:38'),
(145, 1, NULL, 'resource_deleted', '3', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:37:44'),
(146, 1, NULL, 'resource_deleted', '2', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:37:52'),
(147, 1, NULL, 'staff_created', 'pinky@psnf.com', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:54:08'),
(148, 1, NULL, 'staff_created', 'deepa@psnf.com', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:55:09'),
(149, 1, NULL, 'staff_created', 'vaishali@psnf.com', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:55:56'),
(150, 1, NULL, 'staff_created', 'ashini@psnf.com', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:59:48'),
(151, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:02:54'),
(152, 1, NULL, 'folder_updated', '5', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:02:59'),
(153, 1, NULL, 'settings_updated', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:14:18'),
(154, 1, NULL, 'settings_updated', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:14:57'),
(155, 1, NULL, 'folder_updated', '5', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:15:30'),
(156, 1, NULL, 'resource_updated', '7', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:16:23'),
(157, 1, NULL, 'resource_updated', '6', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:16:28'),
(158, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:17:37'),
(159, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:17:42'),
(160, 1, NULL, 'resource_updated', '7', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:17:44'),
(161, 1, NULL, 'resource_updated', '6', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:17:46'),
(162, 1, NULL, 'folder_created', 'PVC Advance/Intermediate/PV1/PV2', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:19:32'),
(163, 1, NULL, 'folder_updated', '6', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:20:07'),
(164, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:21:45'),
(165, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:21:55'),
(166, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:04'),
(167, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:22'),
(168, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:25'),
(169, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:30'),
(170, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:33'),
(171, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:37'),
(172, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:44'),
(173, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:48'),
(174, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:22:56'),
(175, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:00'),
(176, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:04'),
(177, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:15'),
(178, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:18'),
(179, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:21'),
(180, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:25'),
(181, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:32'),
(182, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:37'),
(183, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:41'),
(184, 1, NULL, 'resource_updated', '27', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:49'),
(185, 1, NULL, 'resource_updated', '26', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:51'),
(186, 1, NULL, 'resource_updated', '25', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:55'),
(187, 1, NULL, 'resource_updated', '24', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:56'),
(188, 1, NULL, 'resource_updated', '23', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:23:57'),
(189, 1, NULL, 'resource_updated', '24', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:02'),
(190, 1, NULL, 'resource_updated', '23', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:04'),
(191, 1, NULL, 'resource_updated', '21', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:07'),
(192, 1, NULL, 'resource_updated', '20', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:08'),
(193, 1, NULL, 'resource_updated', '19', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:09'),
(194, 1, NULL, 'resource_updated', '18', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:13'),
(195, 1, NULL, 'resource_updated', '17', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:14'),
(196, 1, NULL, 'resource_updated', '15', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:17'),
(197, 1, NULL, 'resource_updated', '14', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:20'),
(198, 1, NULL, 'resource_updated', '12', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:23'),
(199, 1, NULL, 'resource_updated', '11', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:25'),
(200, 1, NULL, 'resource_updated', '9', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:29'),
(201, 1, NULL, 'resource_updated', '8', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:24:31'),
(202, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:25:05'),
(203, 1, NULL, 'resource_deleted', '27', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:30:59'),
(204, 1, NULL, 'resource_deleted', '26', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:12'),
(205, 1, NULL, 'resource_deleted', '25', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:15'),
(206, 1, NULL, 'resource_deleted', '24', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:18'),
(207, 1, NULL, 'resource_deleted', '23', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:21'),
(208, 1, NULL, 'resource_deleted', '22', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:24'),
(209, 1, NULL, 'resource_deleted', '21', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:27'),
(210, 1, NULL, 'resource_deleted', '21', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:29'),
(211, 1, NULL, 'resource_deleted', '20', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:31'),
(212, 1, NULL, 'resource_deleted', '19', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:41'),
(213, 1, NULL, 'resource_deleted', '18', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:44'),
(214, 1, NULL, 'resource_deleted', '17', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:46'),
(215, 1, NULL, 'resource_deleted', '16', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:48'),
(216, 1, NULL, 'resource_deleted', '15', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:51'),
(217, 1, NULL, 'resource_deleted', '14', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:53'),
(218, 1, NULL, 'resource_deleted', '13', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:56'),
(219, 1, NULL, 'resource_deleted', '12', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:31:59'),
(220, 1, NULL, 'resource_deleted', '11', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:32:03'),
(221, 1, NULL, 'resource_deleted', '10', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:32:07'),
(222, 1, NULL, 'resource_deleted', '9', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:32:09'),
(223, 1, NULL, 'resource_deleted', '8', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:32:13'),
(224, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:36:14'),
(225, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:36:36'),
(226, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:37:29'),
(227, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:38:20'),
(228, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:02'),
(229, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:06'),
(230, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:10'),
(231, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:22'),
(232, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:26'),
(233, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:29'),
(234, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:32'),
(235, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:37'),
(236, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:44'),
(237, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:47'),
(238, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:51'),
(239, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:56'),
(240, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:39:58'),
(241, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:01'),
(242, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:05'),
(243, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:08'),
(244, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:11'),
(245, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:15'),
(246, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:18'),
(247, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:24'),
(248, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:27'),
(249, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:30'),
(250, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:33'),
(251, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:36'),
(252, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:41'),
(253, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:45'),
(254, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:49'),
(255, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:40:53'),
(256, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:41:37'),
(257, 1, NULL, 'folder_created', 'Functional Class 2026', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:49:42'),
(258, 1, NULL, 'folder_created', 'Shared Material 2026', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:49:59'),
(259, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:50:27'),
(260, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:50:52');
INSERT INTO `activity_logs` (`id`, `admin_id`, `staff_id`, `event`, `meta`, `ip`, `user_agent`, `created_at`) VALUES
(261, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:51:41'),
(262, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:51:44'),
(263, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:52:11'),
(264, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:52:22'),
(265, 1, NULL, 'folder_updated', '8', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:52:43'),
(266, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:52:51'),
(267, 1, NULL, 'folder_updated', '8', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:52:54'),
(268, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:53:17'),
(269, 1, NULL, 'assignment_saved', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:53:21'),
(270, 1, NULL, 'resource_deleted', '57', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:53:31'),
(271, 1, NULL, 'folder_deleted', '8', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:53:55'),
(272, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:54:21'),
(273, 1, NULL, 'resource_uploaded', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 16:54:42'),
(274, 1, NULL, 'admin_login_success', '', '49.36.90.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 17:13:30'),
(275, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:32:57'),
(276, 1, NULL, 'admin_login_success', '', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:33:11'),
(277, 1, NULL, 'admin_logout', '', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:35:06'),
(278, NULL, 3, 'staff_login_success', '', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:35:37'),
(279, 1, NULL, 'admin_login_success', '', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:36:04'),
(280, 1, NULL, 'settings_updated', '', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:36:53'),
(281, NULL, 3, 'resource_viewed', '7', '152.58.34.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:37:15'),
(282, NULL, 3, 'resource_viewed', '7', '152.58.35.196', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:40:46'),
(283, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '49.36.125.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:44:41'),
(284, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '49.36.125.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:44:47'),
(285, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '49.36.125.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:44:57'),
(286, 1, NULL, 'admin_login_success', '', '49.36.125.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:45:06'),
(287, 1, NULL, 'admin_logout', '', '49.36.125.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 15:49:49'),
(288, NULL, 6, 'staff_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:51:35'),
(289, NULL, 6, 'staff_logout', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:52:55'),
(290, NULL, 4, 'staff_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:53:14'),
(291, NULL, 4, 'resource_viewed', '55', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:53:37'),
(292, NULL, 4, 'resource_viewed', '55', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:55:14'),
(293, NULL, 4, 'staff_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:56:38'),
(294, NULL, 4, 'resource_viewed', '54', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-12 09:56:42'),
(295, 1, NULL, 'admin_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:00:29'),
(296, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:03:59'),
(297, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:04:27'),
(298, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:04:37'),
(299, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:04:47'),
(300, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:05:18'),
(301, 1, NULL, 'resource_updated', '58', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:05:25'),
(302, 1, NULL, 'admin_login_success', '', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:05:41'),
(303, 1, NULL, 'assignment_saved', '', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:06:25'),
(304, 1, NULL, 'assignment_saved', '', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:06:35'),
(305, 1, NULL, 'assignment_saved', '', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:07:31'),
(306, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:08:34'),
(307, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 07:08:38'),
(308, NULL, NULL, 'staff_login_failed', 'test@test.com', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:17:31'),
(309, NULL, NULL, 'admin_login_failed', 'admin@psnf.local', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:48:17'),
(310, 1, NULL, 'admin_login_success', '', '117.198.164.241', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:48:25'),
(311, 1, NULL, 'admin_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 11:11:46'),
(312, NULL, 6, 'staff_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-15 06:07:47'),
(313, NULL, 6, 'resource_viewed', '56', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-15 06:07:55'),
(314, NULL, 6, 'resource_viewed', '56', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-15 06:08:30'),
(315, NULL, 5, 'staff_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 09:50:07'),
(316, NULL, 4, 'staff_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-06-15 09:56:35'),
(317, 1, NULL, 'admin_login_success', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:47:46'),
(318, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:49:44'),
(319, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:49:57'),
(320, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:50:03'),
(321, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:50:12'),
(322, 1, NULL, 'folder_created', 'DM Pre primary Classes 2026-27', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:50:59'),
(323, 1, NULL, 'folder_created', 'Atharva', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:51:48'),
(324, 1, NULL, 'folder_updated', '9', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:51:56'),
(325, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:52:38'),
(326, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:52:45'),
(327, 1, NULL, 'folder_updated', '10', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:53:04'),
(328, 1, NULL, 'folder_created', 'English', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:53:58'),
(329, 1, NULL, 'folder_updated', '9', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:54:12'),
(330, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:54:47'),
(331, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:55:14'),
(332, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:55:35'),
(333, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:56:07'),
(334, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:56:33'),
(335, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:56:39'),
(336, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:56:46'),
(337, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:56:51'),
(338, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:56:56'),
(339, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:02'),
(340, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:05'),
(341, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:11'),
(342, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:18'),
(343, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:23'),
(344, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:28'),
(345, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:33'),
(346, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:39'),
(347, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:43'),
(348, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:48'),
(349, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:52'),
(350, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:57:57'),
(351, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:01'),
(352, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:06'),
(353, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:12'),
(354, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:17'),
(355, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:22'),
(356, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:28'),
(357, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:33'),
(358, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:48'),
(359, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:53'),
(360, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:58:58'),
(361, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:59:02'),
(362, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:59:07'),
(363, 1, NULL, 'folder_created', 'Maths', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:59:25'),
(364, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 11:59:50'),
(365, 1, NULL, 'folder_created', 'GK', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:00:02'),
(366, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:00:34'),
(367, 1, NULL, 'folder_created', 'Social', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:01:06'),
(368, 1, NULL, 'resource_uploaded', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:01:39'),
(369, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:02:23'),
(370, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:02:30'),
(371, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:02:39'),
(372, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:02:53'),
(373, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:02:58'),
(374, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:01'),
(375, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:07'),
(376, 1, NULL, 'folder_updated', '12', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:18'),
(377, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:29'),
(378, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:34'),
(379, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:40'),
(380, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:45'),
(381, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:50'),
(382, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:03:56'),
(383, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:04:01'),
(384, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:04:06'),
(385, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:04:13'),
(386, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:04:19'),
(387, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:04:23'),
(388, 1, NULL, 'assignment_saved', '', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:04:29'),
(389, 1, NULL, 'resource_updated', '62', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:07:05'),
(390, 1, NULL, 'resource_updated', '61', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:07:20'),
(391, 1, NULL, 'resource_updated', '61', '103.238.107.250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 12:07:30'),
(392, NULL, NULL, 'admin_login_failed', 'admin@erp.local', '117.198.90.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:10:02'),
(393, 1, NULL, 'admin_login_success', '', '117.198.90.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:10:15'),
(394, 1, NULL, 'assignment_saved', '', '117.198.90.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `is_active`, `created_at`) VALUES
(1, 'Super Admin', 'admin@psnf.local', '$2y$10$sqGPxsTidqeSFeo7Y6H2ce3KUf20aR5xFDdtJeHrciLBCNOi/92Qi', 1, '2026-05-21 07:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `parent_id`, `name`, `created_at`) VALUES
(5, NULL, 'PVC Elementary 2026', '2026-06-08 15:33:26'),
(6, NULL, 'PVC Advance/Intermediate/PV1/PV2 [2026]', '2026-06-08 16:19:32'),
(7, NULL, 'Functional Class 2026', '2026-06-08 16:49:42'),
(9, NULL, 'DM Pre primary Classes 2026-27', '2026-06-15 11:50:59'),
(10, 9, 'Atharva', '2026-06-15 11:51:48'),
(11, 9, 'English', '2026-06-15 11:53:58'),
(12, 9, 'Maths', '2026-06-15 11:59:25'),
(13, 9, 'GK', '2026-06-15 12:00:02'),
(14, 9, 'Social', '2026-06-15 12:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `folder_staff`
--

CREATE TABLE `folder_staff` (
  `folder_id` int NOT NULL,
  `staff_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folder_staff`
--

INSERT INTO `folder_staff` (`folder_id`, `staff_id`) VALUES
(5, 3),
(6, 4),
(7, 4),
(7, 5),
(7, 6);

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int NOT NULL,
  `folder_id` int DEFAULT NULL,
  `title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `folder_id`, `title`, `file_name`, `stored_name`, `mime_type`, `file_size`, `created_at`) VALUES
(6, 5, 'PVC Ele Class SEP-1 26-27  Money Transaction Sheet', 'PVC Ele Class SEP-1 26-27  Money Transaction Sheet.pdf', 'd5d347cc7b6318a7b95c732ca14d745f8fd3.pdf', 'application/pdf', 59104, '2026-06-08 15:36:46'),
(7, 5, 'Elementary book', 'Elementary book.pdf', 'c53c637e359c539d8e076578732cbea426d1.pdf', 'application/pdf', 7745546, '2026-06-08 15:36:46'),
(28, 6, 'Pearl SEP-1 Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2 (1)', 'Pearl SEP-1 Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2 (1).pdf', '5c958b222091053f75faab176ab23ac4aa40.pdf', 'application/pdf', 62570, '2026-06-08 16:36:14'),
(29, 6, 'PVC PV-2 [A] 26-27 SEP-1  -NEW Product Price Estimation Book', 'PVC PV-2 [A] 26-27 SEP-1  -NEW Product Price Estimation Book.pdf', 'bb65eb6da83b190d76f56a51da6c644702d7.pdf', 'application/pdf', 476657, '2026-06-08 16:36:14'),
(30, 6, 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Ya,Kr,Ak,Yug,Ni 3,000 Limit (1)', 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Ya,Kr,Ak,Yug,Ni 3,000 Limit (1).pdf', '9bf076335c5556d3e7d36b8c33182eecb6e6.pdf', 'application/pdf', 405802, '2026-06-08 16:36:36'),
(31, 6, 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Ya,Kr,Ak,Yug,Ni 3,000 Limit', 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Ya,Kr,Ak,Yug,Ni 3,000 Limit.pdf', 'ec7932ac9e67079fa2783ba1b805a4e55ea5.pdf', 'application/pdf', 405802, '2026-06-08 16:36:36'),
(32, 6, 'PVC PV-2 [A] 26-27 SEP-1 Money Transaction Sheet', 'PVC PV-2 [A] 26-27 SEP-1 Money Transaction Sheet.pdf', '8a108109d7c31a9a91f75a5ac7ec57d5a447.pdf', 'application/pdf', 59787, '2026-06-08 16:36:36'),
(33, 6, 'Pearl SEP-1 PV-1 [A] Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2', 'Pearl SEP-1 PV-1 [A] Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2.pdf', '65cb38e67c4d5f45c2e1d9f410ef299007e0.pdf', 'application/pdf', 62567, '2026-06-08 16:36:36'),
(34, 6, 'PVC PV-1 [A[ 26-27 SEP-1  - NEW  Product Price Estimation Book', 'PVC PV-1 [A[ 26-27 SEP-1  - NEW  Product Price Estimation Book.pdf', '14c69ac7e8a2c8f3cdd808f56016a02faaed.pdf', 'application/pdf', 476660, '2026-06-08 16:36:36'),
(35, 6, 'PVC PV-1 [A]  26-27 SEP -1    3 Addition Book 3,000 limit', 'PVC PV-1 [A]  26-27 SEP -1    3 Addition Book 3,000 limit.pdf', '7196c4c3ba3bbb2ad1039eb0a8928f9dac59.pdf', 'application/pdf', 516730, '2026-06-08 16:36:36'),
(36, 6, 'PVC  PV-1 [A] 26-27 SEP -1 Money Transaction HTO Skill Book 3,000 Limit', 'PVC  PV-1 [A] 26-27 SEP -1 Money Transaction HTO Skill Book 3,000 Limit.pdf', 'b0d1382d655223e50173893196c4528285c2.pdf', 'application/pdf', 405234, '2026-06-08 16:37:29'),
(37, 6, 'PVC PV-1 [A] 26-27 SEP-1 Money Transaction Sheet', 'PVC PV-1 [A] 26-27 SEP-1 Money Transaction Sheet.pdf', '55f299c37181f22f7f216387d7e2d9014165.pdf', 'application/pdf', 59756, '2026-06-08 16:37:29'),
(38, 6, 'PVC PV-1 26-27 SEP-1  - NEW Product Price Estimation Book', 'PVC PV-1 26-27 SEP-1  - NEW Product Price Estimation Book.pdf', '6d03715e94d2c1f68be7f2ec5a2babbaff63.pdf', 'application/pdf', 476531, '2026-06-08 16:37:29'),
(39, 6, 'Pearl SEP-1 Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2', 'Pearl SEP-1 Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2.pdf', '3e0111e975c5884fe5a0b1742407a7b4ebfb.pdf', 'application/pdf', 62539, '2026-06-08 16:37:29'),
(40, 6, 'PVC PV-1  26-27 SEP -1    3 Addition Book 1000 limit (1)', 'PVC PV-1  26-27 SEP -1    3 Addition Book 1000 limit (1).pdf', 'f9acede5398fdf1a88a2e52959ab8fc1396c.pdf', 'application/pdf', 515803, '2026-06-08 16:37:29'),
(41, 6, 'PVC PV-1  26-27 SEP -1    3 Addition Book 1000 limit', 'PVC PV-1  26-27 SEP -1    3 Addition Book 1000 limit.pdf', 'ebf604d58bfc0150dc49601e152bd68383c5.pdf', 'application/pdf', 515803, '2026-06-08 16:37:29'),
(42, 6, 'PVC  PV-1 SEP-1 26-27  Money Transaction HTO Skill Book 1000 limit 0,1,2,5', 'PVC  PV-1 SEP-1 26-27  Money Transaction HTO Skill Book 1000 limit 0,1,2,5.pdf', 'ad32014b623bce2824cae4fcd4a82888513f.pdf', 'application/pdf', 403022, '2026-06-08 16:37:29'),
(43, 6, 'PVC Niharika 26-27 SEP-1  -NEW Product Price Estimation Book', 'PVC Niharika 26-27 SEP-1  -NEW Product Price Estimation Book.pdf', 'ef6d859f9bfae1062426bb4e9184d617ed29.pdf', 'application/pdf', 1509628, '2026-06-08 16:37:29'),
(44, 6, 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Niharika 3,000 Limit (1)', 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Niharika 3,000 Limit (1).pdf', 'b793fd76e949a03b994346a13f199083e4de.pdf', 'application/pdf', 1216821, '2026-06-08 16:37:29'),
(45, 6, 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Niharika 3,000 Limit', 'PVC PV-2 [A] 26-27 SEP -1 Money Transaction HTO Skill  Niharika 3,000 Limit.pdf', '4037d70659ddd133c6d4d9ba1e41db34c79c.pdf', 'application/pdf', 1216821, '2026-06-08 16:37:29'),
(46, 6, 'Pearl Product Price Estimation Resource Sheet Niharika', 'Pearl Product Price Estimation Resource Sheet Niharika.pdf', '2a98fb64a974900be92f073f2516415c8b31.pdf', 'application/pdf', 439506, '2026-06-08 16:37:29'),
(47, 6, 'PVC PV-2 [A] 26-27 SEP-1 Money Transaction Sheet Niharika', 'PVC PV-2 [A] 26-27 SEP-1 Money Transaction Sheet Niharika.pdf', '2d8641230e34194f14cfd685874bd2e9a311.pdf', 'application/pdf', 452255, '2026-06-08 16:37:29'),
(48, 6, 'Intermediate Pearl SEP-1 Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2', 'Intermediate Pearl SEP-1 Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2.pdf', 'f7205a7b158437d01a6020f791378162b009.pdf', 'application/pdf', 62573, '2026-06-08 16:38:20'),
(49, 6, 'PVC INTERMEDIATE 26-27 SEP-1  - NEW Product Price Estimation Book', 'PVC INTERMEDIATE 26-27 SEP-1  - NEW Product Price Estimation Book.pdf', '6b3066756169c7265154003a3777c424840b.pdf', 'application/pdf', 476678, '2026-06-08 16:38:20'),
(50, 6, 'PVC- SEP-1 Intermediate 3 Addition upto-15,000 2026-27', 'PVC- SEP-1 Intermediate 3 Addition upto-15,000 2026-27.pdf', '1ec09b542b8241bfbdda78885dc7553e9a2c.pdf', 'application/pdf', 534828, '2026-06-08 16:38:20'),
(51, 6, 'PVC 26-27 SEP-1 Intermediate Place value+HTO-combine  15,000 limit', 'PVC 26-27 SEP-1 Intermediate Place value+HTO-combine  15,000 limit.pdf', 'b665f980c0763142be04cb27acaaaf6fa018.pdf', 'application/pdf', 403718, '2026-06-08 16:38:20'),
(52, 6, 'PVC 26-27 SEP-1 Intermediate Money Transaction Sheet 15,000 limit', 'PVC 26-27 SEP-1 Intermediate Money Transaction Sheet 15,000 limit.pdf', 'e46a03e09ca2c6054bdceba0b7a5dd864e55.pdf', 'application/pdf', 61422, '2026-06-08 16:38:20'),
(53, 6, 'Advance Pearl Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2', 'Advance Pearl Product  Price Estimation Resource Sheet -1 [ 50 Product ] done  number 1,2.pdf', '3b955f1129feb46e37c11fea6e012c4d49c6.pdf', 'application/pdf', 62547, '2026-06-08 16:38:20'),
(54, 6, 'PVC  26-27 SEP-1 Advance - Product Price Estimation Book', 'PVC  26-27 SEP-1 Advance - Product Price Estimation Book.pdf', '069dde35319c67f908ca662001d81213c5e0.pdf', 'application/pdf', 476980, '2026-06-08 16:38:20'),
(55, 6, 'PVC- SEP-1 Advance 3 Addition upto-15,000 2026-27', 'PVC- SEP-1 Advance 3 Addition upto-15,000 2026-27.pdf', '7a2a489add27191224000dba5feabc28daee.pdf', 'application/pdf', 534507, '2026-06-08 16:38:20'),
(56, 7, 'GK book', 'GK book.pdf', '319b2470045af2c2e8a8148f7745ae75a43a.pdf', 'application/pdf', 15308817, '2026-06-08 16:50:27'),
(58, 7, 'Money Skill sheets for class work SEP-1 26-27', 'Money Skill sheets for class work SEP-1 26-27.pdf', '9bd2dadc3e1ae360e061ccd64d0660e9f778.pdf', 'application/pdf', 501389, '2026-06-08 16:54:21'),
(59, 5, 'Money Skill sheets for class work SEP-1 26-27', 'Money Skill sheets for class work SEP-1 26-27.pdf', 'c992cb6391f0235dd9b37931d783d100cb34.pdf', 'application/pdf', 501389, '2026-06-08 16:54:42'),
(60, NULL, 'GK book', 'GK book.pdf', '42d73718619e3b4b14c048b2baf72363ec02.pdf', 'application/pdf', 15308817, '2026-06-15 11:49:44'),
(61, 7, 'VM ENG', 'VM ENG.pdf', '444109185ae7a1c34b7f478996027a7a3395.pdf', 'application/pdf', 11206708, '2026-06-15 11:49:44'),
(62, 7, 'VM MATHS BOOK', 'VM MATHS BOOK.pdf', '98569acfbbd315762ae1edbbaad4c87a7f3a.pdf', 'application/pdf', 14501620, '2026-06-15 11:49:44'),
(63, 10, 'Atharv emotion cut flashcard Sheet', 'Atharv emotion cut flashcard Sheet.pdf', 'd1fdd0b6a61508d34e85944ed01d50d31e8e.pdf', 'application/pdf', 496101, '2026-06-15 11:52:38'),
(64, 10, 'Atharv emotion Sheet', 'Atharv emotion Sheet.pdf', '46b113713f428543fb702fbd7e5fb4689a08.pdf', 'application/pdf', 380837, '2026-06-15 11:52:38'),
(65, 10, 'Atharv Pre academic mental skill cut flashcard Sheet', 'Atharv Pre academic mental skill cut flashcard Sheet.pdf', '62e3fb5daff5bab102f01b85dd711881aaea.pdf', 'application/pdf', 461457, '2026-06-15 11:52:38'),
(66, 10, 'Atharv Pre academic mental skill Sheet', 'Atharv Pre academic mental skill Sheet.pdf', '24dd8994fce4589d752c7371a795c6080963.pdf', 'application/pdf', 387312, '2026-06-15 11:52:38'),
(67, 11, 'Aayush comprehension sheet [Shopping center] sep-1 2026-27', 'Aayush comprehension sheet [Shopping center] sep-1 2026-27.pdf', '1634bcc754b536bc4fddd1568bf5c15eaafc.pdf', 'application/pdf', 646116, '2026-06-15 11:54:47'),
(68, 11, 'Aayush Picture To Word Matching sheet-1 SEP-1 2026-27', 'Aayush Picture To Word Matching sheet-1 SEP-1 2026-27.pdf', '10a4bdbef0a72972577c24899f43e0b681de.pdf', 'application/pdf', 683488, '2026-06-15 11:54:47'),
(69, 11, 'Aayush vikas apple picture to word cut and velcro', 'Aayush vikas apple picture to word cut and velcro.pdf', 'e3189e1d7c55d0e5f21da171672f96d00d0a.pdf', 'application/pdf', 415303, '2026-06-15 11:54:47'),
(70, 11, 'Aayush vikas apple picture to word sheet [day to pray]', 'Aayush vikas apple picture to word sheet [day to pray].pdf', '65b0fe911c748006425f840a92c1cf1540a7.pdf', 'application/pdf', 554254, '2026-06-15 11:54:47'),
(71, 11, 'Aayush vikas apple RSPI sheet [day to pray] pg-8', 'Aayush vikas apple RSPI sheet [day to pray] pg-8.pdf', '15f05c5425dc16bcb683332fbd9084fad116.pdf', 'application/pdf', 539404, '2026-06-15 11:54:47'),
(72, 11, 'Aayush Vikas Apple Word to word sheet [day to pray]', 'Aayush Vikas Apple Word to word sheet [day to pray].pdf', 'f4d3919e28c1c0744b702a6b18f93d4bd8e0.pdf', 'application/pdf', 270683, '2026-06-15 11:54:47'),
(73, 11, 'Alphabet sheet-5 Q to T', 'Alphabet sheet-5 Q to T.pdf', 'a3008a60582eb5ebe857099732192f8eef04.pdf', 'application/pdf', 454703, '2026-06-15 11:55:14'),
(74, 11, 'Atharv Alphabet sheet-1', 'Atharv Alphabet sheet-1.pdf', '2995c296913434f24447af5edf2135590347.pdf', 'application/pdf', 437265, '2026-06-15 11:55:14'),
(75, 11, 'Atharv Alphabet sheet-2', 'Atharv Alphabet sheet-2.pdf', 'b1a581ef2a5dda60f0571ccdac8ab48e6988.pdf', 'application/pdf', 440520, '2026-06-15 11:55:14'),
(76, 11, 'Atharv comprehension sheet [Living Room] sep-1 2026-27', 'Atharv comprehension sheet [Living Room] sep-1 2026-27.pdf', '3be56b553a57469b6798733964226db41dad.pdf', 'application/pdf', 645301, '2026-06-15 11:55:14'),
(77, 11, 'Avaz word to picture Sheet cut flashcard SEP-1 2026-27', 'Avaz word to picture Sheet cut flashcard SEP-1 2026-27.pdf', '1e50be3046b133c34da254e1ba48f0ced89e.pdf', 'application/pdf', 362249, '2026-06-15 11:55:14'),
(78, 11, 'Avaz word to picture Sheet SEP-1 2026-27', 'Avaz word to picture Sheet SEP-1 2026-27.pdf', 'b0ef7817186115023975704c9bffb9ab3a3b.pdf', 'application/pdf', 237098, '2026-06-15 11:55:14'),
(79, 11, 'body part picture to word cut and velcro', 'body part picture to word cut and velcro.pdf', '20aeaa07b28be2d164a05f50bae560947b65.pdf', 'application/pdf', 387668, '2026-06-15 11:55:14'),
(80, 11, 'body part picture to word sheet', 'body part picture to word sheet.pdf', '9a3b9e78dcd23f14361f5f223badc94ff0bc.pdf', 'application/pdf', 437102, '2026-06-15 11:55:14'),
(81, 11, 'DM Sentence Starter cut & velcro Sheet', 'DM Sentence Starter cut & velcro Sheet.pdf', 'abc2aab975579a707893edb93bf964da567c.pdf', 'application/pdf', 828450, '2026-06-15 11:55:14'),
(82, 11, 'Mahipal Picture To Word Matching sheet-1 SEP-1 2026-27', 'Mahipal Picture To Word Matching sheet-1 SEP-1 2026-27.pdf', '8437f49d14f3fa797e72c141f3396c991743.pdf', 'application/pdf', 726710, '2026-06-15 11:55:35'),
(83, 11, 'Mahipal vikas apple picture to word cut and velcro', 'Mahipal vikas apple picture to word cut and velcro.pdf', '10d95a36e83a4b410be490e01027a4a81307.pdf', 'application/pdf', 416981, '2026-06-15 11:55:35'),
(84, 11, 'Mahipal vikas apple picture to word sheet [cob to top]', 'Mahipal vikas apple picture to word sheet [cob to top].pdf', 'd9797ba43fffb02ac765f6f061e3448ccf7a.pdf', 'application/pdf', 701927, '2026-06-15 11:55:35'),
(85, 11, 'Mahipal vikas apple RSPI sheet [cob to top] pg-18', 'Mahipal vikas apple RSPI sheet [cob to top] pg-18.pdf', '0301390a62adf8844aabfac69d3e8e60c22d.pdf', 'application/pdf', 651270, '2026-06-15 11:55:35'),
(86, 11, 'Mahipal Vikas Apple Word to word sheet [ cob to top]', 'Mahipal Vikas Apple Word to word sheet [ cob to top].pdf', '78e34a4e321a0786d237d17e6d6378ba031f.pdf', 'application/pdf', 270630, '2026-06-15 11:55:35'),
(87, 11, 'Neeraj Picture To Word Matching sheet-1 SEP-1 2026-27', 'Neeraj Picture To Word Matching sheet-1 SEP-1 2026-27.pdf', '07acc96d0190ea483b097dc4fcb352773eed.pdf', 'application/pdf', 679382, '2026-06-15 11:56:07'),
(88, 11, 'Neeraj vikas apple picture to word cut and velcro', 'Neeraj vikas apple picture to word cut and velcro.pdf', 'adb4739be10b363e29ad1c0e9b1d025a1985.pdf', 'application/pdf', 416294, '2026-06-15 11:56:07'),
(89, 11, 'Neeraj vikas apple picture to word sheet [cot to rod]', 'Neeraj vikas apple picture to word sheet [cot to rod].pdf', '89557e448817415fab00fd109d14b523893c.pdf', 'application/pdf', 558986, '2026-06-15 11:56:07'),
(90, 11, 'Neeraj vikas apple RSPI sheet [bat to sat] pg-16', 'Neeraj vikas apple RSPI sheet [bat to sat] pg-16.pdf', 'eee5c0d9984518276ebd400414fec600cc95.pdf', 'application/pdf', 548860, '2026-06-15 11:56:07'),
(91, 11, 'Neeraj Vikas Apple Word to word sheet [cot to rod]', 'Neeraj Vikas Apple Word to word sheet [cot to rod].pdf', '65037f5031a90d1f7e4b533334f3f883e080.pdf', 'application/pdf', 383385, '2026-06-15 11:56:07'),
(92, 11, 'spelling sheet cutout for 4 kids sep-1 2026-27', 'spelling sheet cutout for 4 kids sep-1 2026-27.pdf', 'a788b7b50b8ba7aa10529e0cb4913a5e00cc.pdf', 'application/pdf', 280358, '2026-06-15 11:56:07'),
(93, 11, 'Spelling sheet-2 sep-1 2026-27', 'Spelling sheet-2 sep-1 2026-27.pdf', '2080fb9b4d7f4b25da3c30518172cf1a40e0.pdf', 'application/pdf', 556141, '2026-06-15 11:56:07'),
(94, 11, 'What question cut flashcard Sheet', 'What question cut flashcard Sheet.pdf', '232cf630f4823b917fcb47259407425252e0.pdf', 'application/pdf', 598336, '2026-06-15 11:56:07'),
(95, 11, 'What question sheet', 'What question sheet.pdf', 'e7c30a35e6424c7763dee59269f7bd207087.pdf', 'application/pdf', 251704, '2026-06-15 11:56:07'),
(96, 12, 'clock Sheet  Basic(SEP 1) 2025-26', 'clock Sheet  Basic(SEP 1) 2025-26.pdf', '4b1929c73c056ba613fa41cec4544996a559.pdf', 'application/pdf', 382200, '2026-06-15 11:59:50'),
(97, 12, 'clock Sheet flashcard Basic(SEP 2) 2025-26 Jr', 'clock Sheet flashcard Basic(SEP 2) 2025-26 Jr.pdf', '4ccdb1af772ac1470d9ca799c60e8b5f461d.pdf', 'application/pdf', 381895, '2026-06-15 11:59:50'),
(98, 12, 'cut & Velcro Number Name Sheet SEP-1 21 to 25 [26-27]', 'cut & Velcro Number Name Sheet SEP-1 21 to 25 [26-27].pdf', '8be957d24ab27cab918597b509fba7527dcd.pdf', 'application/pdf', 396286, '2026-06-15 11:59:50'),
(99, 12, 'Number Name Sheet SEP-1 21 to 25 [26-27]', 'Number Name Sheet SEP-1 21 to 25 [26-27].pdf', 'bd89b1f68c8c4cccfa20387a3ad1de599d64.pdf', 'application/pdf', 381582, '2026-06-15 11:59:50'),
(100, 13, 'Aayush G.K. Direction Sheet (one steps) SEP-1', 'Aayush G.K. Direction Sheet (one steps) SEP-1.pdf', 'cc4d8532972e7d4ae6e7efc7ac2107a63867.pdf', 'application/pdf', 150133, '2026-06-15 12:00:33'),
(101, 13, 'Aayush Pre academic mental skill SEP-1 2026-27', 'Aayush Pre academic mental skill SEP-1 2026-27.pdf', 'd09cb12e94f22dcfa5e7f7e8c3990b69421a.pdf', 'application/pdf', 206383, '2026-06-15 12:00:33'),
(102, 13, 'GK sheet-1 (SEP-1) 2026-27 Jr - Copy', 'GK sheet-1 (SEP-1) 2026-27 Jr - Copy.pdf', '9b1a0d99a0ed4a567e1a826f85fb1cc97e53.pdf', 'application/pdf', 248283, '2026-06-15 12:00:33'),
(103, 13, 'GK sheet-1 (SEP-1) 2026-27 Jr', 'GK sheet-1 (SEP-1) 2026-27 Jr.pdf', '4006193b31cfabbebac3cc8c6ab44b2946c9.pdf', 'application/pdf', 262617, '2026-06-15 12:00:33'),
(104, 13, 'GK sheet-2 (SEP-1) 2026-27 Jr', 'GK sheet-2 (SEP-1) 2026-27 Jr.pdf', 'cf8321b285964d1dffec01879297fab6cb69.pdf', 'application/pdf', 278003, '2026-06-15 12:00:33'),
(105, 13, 'Reletionship sheet SEP-1 2026-27', 'Reletionship sheet SEP-1 2026-27.pdf', 'c7616e4e47cb443dedb6ffb6bcafa3233e56.pdf', 'application/pdf', 384638, '2026-06-15 12:00:33'),
(106, 13, 'SORTING SHEET cut out sep-1 2026-27', 'SORTING SHEET cut out sep-1 2026-27.pdf', 'da0735b428762461d5fb2bcadd8f72cc6561.pdf', 'application/pdf', 553560, '2026-06-15 12:00:33'),
(107, 13, 'SORTING SHEET sep-1 2026-27', 'SORTING SHEET sep-1 2026-27.pdf', '3b0722a3cf2a54881f2e4d7eba8f18a35732.pdf', 'application/pdf', 384087, '2026-06-15 12:00:33'),
(108, 13, 'Use Of Object Cut & Velcro', 'Use Of Object Cut & Velcro.pdf', 'ce3036a1d26cd36ee956ecd167cdf14bcd93.pdf', 'application/pdf', 583107, '2026-06-15 12:00:33'),
(109, 13, 'USE OF OBJECTS sep-1 2026-27', 'USE OF OBJECTS sep-1 2026-27.pdf', 'bb78967a121e659f42311149df50212c0138.pdf', 'application/pdf', 430872, '2026-06-15 12:00:33'),
(110, 13, 'Vaani G.K. Sheet-1 Domestic animals  SEP-1 2026-27', 'Vaani G.K. Sheet-1 Domestic animals  SEP-1 2026-27.pdf', '4dd08e746c471e3ca48781566e92c0432a80.pdf', 'application/pdf', 621334, '2026-06-15 12:00:33'),
(111, 13, 'Vaani G.K. Sheet-2 fruits  SEP-1 2026-27', 'Vaani G.K. Sheet-2 fruits  SEP-1 2026-27.pdf', 'c421d227f1684a31e433b0ae653b55180f4b.pdf', 'application/pdf', 498054, '2026-06-15 12:00:34'),
(112, 14, 'Community Survival Sign SEP-1 2026-27', 'Community Survival Sign SEP-1 2026-27.pdf', 'ee8c5a53941034eea3d5a5bd36b53dcde0ea.pdf', 'application/pdf', 419052, '2026-06-15 12:01:39'),
(113, 14, 'gender identification cut & velcro Sheet', 'gender identification cut & velcro Sheet.pdf', 'd65ed42ea22edc44a724323671691796fe8a.pdf', 'application/pdf', 686604, '2026-06-15 12:01:39'),
(114, 14, 'gender identification sheet', 'gender identification sheet.pdf', 'd2a7e598a36fef71e6db5fa4eee445c6f791.pdf', 'application/pdf', 240815, '2026-06-15 12:01:39');

-- --------------------------------------------------------

--
-- Table structure for table `resource_staff`
--

CREATE TABLE `resource_staff` (
  `resource_id` int NOT NULL,
  `staff_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resource_staff`
--

INSERT INTO `resource_staff` (`resource_id`, `staff_id`) VALUES
(6, 3),
(7, 3),
(58, 3),
(28, 4),
(29, 4),
(30, 4),
(31, 4),
(32, 4),
(33, 4),
(34, 4),
(35, 4),
(36, 4),
(37, 4),
(38, 4),
(39, 4),
(40, 4),
(41, 4),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(46, 4),
(47, 4),
(48, 4),
(49, 4),
(50, 4),
(51, 4),
(52, 4),
(53, 4),
(54, 4),
(55, 4),
(66, 5),
(67, 5),
(68, 5),
(69, 5),
(70, 5),
(71, 5),
(72, 5),
(73, 5),
(74, 5),
(75, 5),
(76, 5),
(77, 5),
(78, 5),
(79, 5),
(80, 5),
(81, 5),
(82, 5),
(83, 5),
(84, 5),
(85, 5),
(86, 5),
(87, 5),
(88, 5),
(89, 5),
(90, 5),
(91, 5),
(92, 5),
(93, 5),
(94, 5),
(95, 5),
(96, 5),
(97, 5),
(98, 5),
(99, 5),
(100, 5),
(101, 5),
(102, 5),
(103, 5),
(104, 5),
(105, 5),
(106, 5),
(107, 5),
(108, 5),
(109, 5),
(110, 5),
(111, 5),
(112, 5),
(113, 5),
(114, 5),
(56, 6),
(60, 6),
(61, 6),
(62, 6);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
(1, 'deployment_mode', 'vps'),
(2, 'global_access_start', '01:00'),
(3, 'global_access_end', '17:00'),
(4, 'block_mobile', '1'),
(5, 'debug_mode', '0'),
(6, 'download_restriction', '1'),
(7, 'allowed_file_types', 'pdf,jpg,jpeg,png,mp4,mov,webm'),
(8, 'max_upload_mb', '20000'),
(58, 'access_mon_start', '09:30'),
(59, 'access_mon_end', '16:00'),
(60, 'access_tue_start', '09:30'),
(61, 'access_tue_end', '20:00'),
(62, 'access_wed_start', '09:30'),
(63, 'access_wed_end', '16:00'),
(64, 'access_thu_start', '09:30'),
(65, 'access_thu_end', '16:00'),
(66, 'access_fri_start', '09:30'),
(67, 'access_fri_end', '16:00'),
(68, 'access_sat_start', '09:30'),
(69, 'access_sat_end', '15:00'),
(70, 'access_sun_start', '00:00'),
(71, 'access_sun_end', '00:01'),
(73, 'block_laptop', '0'),
(137, 'allow_smartboard', '1'),
(138, 'smartboard_min_width', '3340'),
(139, 'smartboard_min_height', '2060');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `email`, `password`, `is_active`, `created_at`) VALUES
(3, 'Chhaya Fadia', 'chhaya@psnf.com', '$2y$10$YmJIjAvlz.bYOxuIcdq/SevInD.7KzrFTeYr7NytfAtMHp/KQKat.', 1, '2026-06-08 15:31:57'),
(4, 'Pinky shah', 'pinky@psnf.com', '$2y$10$JaYBDXwegmwY3kfS3HOuJOoO4.MJAxKghnymzs0.hBPBCVDGT6s8G', 1, '2026-06-08 15:54:08'),
(5, 'Deepa Shah', 'deepa@psnf.com', '$2y$10$iNW28mp19KoDx6KJLRgrh.1zsj0PHFW91MrDdz4dS9ci1dRYb2pTm', 1, '2026-06-08 15:55:09'),
(6, 'Vaishali Mehta', 'vaishali@psnf.com', '$2y$10$X8C6NzLn9YXPvkIhVbr6eeQU.Z5NimiUpADrvVcYpFMDplvwtxJXW', 1, '2026-06-08 15:55:56'),
(7, 'Ashini Shah', 'ashini@psnf.com', '$2y$10$Ub9oB2972L.O3.Him1/o0.QWcuIheb32/qPCI1LZlaXs7KM4klqKu', 1, '2026-06-08 15:59:48');

-- --------------------------------------------------------

--
-- Table structure for table `staff_favorites`
--

CREATE TABLE `staff_favorites` (
  `id` int NOT NULL,
  `staff_id` int NOT NULL,
  `resource_id` int DEFAULT NULL,
  `folder_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event` (`event`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `folder_staff`
--
ALTER TABLE `folder_staff`
  ADD PRIMARY KEY (`folder_id`,`staff_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `resource_staff`
--
ALTER TABLE `resource_staff`
  ADD PRIMARY KEY (`resource_id`,`staff_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff_favorites`
--
ALTER TABLE `staff_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_staff_resource` (`staff_id`,`resource_id`),
  ADD UNIQUE KEY `uniq_staff_folder` (`staff_id`,`folder_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=395;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=669;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff_favorites`
--
ALTER TABLE `staff_favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `folder_staff`
--
ALTER TABLE `folder_staff`
  ADD CONSTRAINT `folder_staff_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `folder_staff_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `resource_staff`
--
ALTER TABLE `resource_staff`
  ADD CONSTRAINT `resource_staff_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resource_staff_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_favorites`
--
ALTER TABLE `staff_favorites`
  ADD CONSTRAINT `staff_favorites_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_favorites_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_favorites_ibfk_3` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
