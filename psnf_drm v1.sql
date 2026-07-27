-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 12:09 PM
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
-- Database: `psnf_drm`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) NOT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `school_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `event` varchar(190) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `model_id` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(64) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `tenant_id`, `school_id`, `branch_id`, `user_id`, `admin_id`, `staff_id`, `event`, `model`, `model_id`, `description`, `properties`, `ip_address`, `meta`, `ip`, `user_agent`, `created_at`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:18:06'),
(2, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:18:24'),
(3, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:18:42'),
(4, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:30'),
(5, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:32'),
(6, NULL, NULL, NULL, NULL, 1, NULL, 'folder_created', NULL, NULL, NULL, NULL, NULL, 'test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:46'),
(7, NULL, NULL, NULL, NULL, 1, NULL, 'resource_uploaded', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:52'),
(8, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:19:57'),
(9, NULL, NULL, NULL, NULL, NULL, 1, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:20:33'),
(10, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:22:05'),
(11, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:22:11'),
(12, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:27:48'),
(13, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', '2026-05-21 07:27:53'),
(14, NULL, NULL, NULL, NULL, NULL, 1, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:28:26'),
(15, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:28:28'),
(16, NULL, NULL, NULL, NULL, 1, NULL, 'staff_created', NULL, NULL, NULL, NULL, NULL, 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:29:44'),
(17, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:42:31'),
(18, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:32'),
(19, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:40'),
(20, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'test@test.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:46'),
(21, NULL, NULL, NULL, NULL, 1, NULL, 'staff_password_reset', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:44:56'),
(22, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:07'),
(23, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:08'),
(24, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:24'),
(25, NULL, NULL, NULL, NULL, 1, NULL, 'resource_uploaded', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:46'),
(26, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:53'),
(27, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:45:56'),
(28, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:46:33'),
(29, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:46:36'),
(30, NULL, NULL, NULL, NULL, 1, NULL, 'resource_uploaded', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:48:18'),
(31, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:48:27'),
(32, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:48:30'),
(33, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 07:49:25'),
(34, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:37:28'),
(35, NULL, NULL, NULL, NULL, 1, NULL, 'folder_created', NULL, NULL, NULL, NULL, NULL, 'test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:37:38'),
(36, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:32'),
(37, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:41'),
(38, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:42'),
(39, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:44'),
(40, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:46'),
(41, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:54'),
(42, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:38:58'),
(43, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:39:01'),
(44, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:39:18'),
(45, NULL, NULL, NULL, NULL, 1, NULL, 'resource_uploaded', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:40:35'),
(46, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:40:44'),
(47, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:48:28'),
(48, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:48:47'),
(49, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:48:55'),
(50, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:49:00'),
(51, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:57:57'),
(52, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:07'),
(53, NULL, NULL, NULL, NULL, NULL, 2, 'staff_logout', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:22'),
(54, NULL, NULL, NULL, NULL, NULL, 1, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:33'),
(55, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:41'),
(56, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:42'),
(57, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:43'),
(58, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:45'),
(59, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:58:46'),
(60, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 08:59:50'),
(61, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:01:42'),
(62, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:01:43'),
(63, NULL, NULL, NULL, NULL, 1, NULL, 'folder_created', NULL, NULL, NULL, NULL, NULL, 'test 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:10:06'),
(64, NULL, NULL, NULL, NULL, NULL, 1, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 09:23:52'),
(65, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:00:27'),
(66, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:53:19'),
(67, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:54:53'),
(68, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'staff@psnf.local', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:57:43'),
(69, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'staff@psnf.local', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:57:54'),
(70, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'test@test.com', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:58:24'),
(71, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:58:36'),
(72, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '192.168.1.12', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-22 09:59:06'),
(73, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:59:18'),
(74, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 09:59:30'),
(75, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:06:38'),
(76, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:07:02'),
(77, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:01'),
(78, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:09'),
(79, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:14'),
(80, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:25'),
(81, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:09:57'),
(82, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:10:44'),
(83, NULL, NULL, NULL, NULL, 1, NULL, 'admin_logout', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:11:04'),
(84, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'staff@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:11:22'),
(85, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:11:28'),
(86, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:12:16'),
(87, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:12:40'),
(88, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:12:55'),
(89, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-22 10:13:12'),
(90, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:15:43'),
(91, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:36:47'),
(92, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:37:12'),
(93, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_added', NULL, NULL, NULL, NULL, NULL, 'folder:1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:37:23'),
(94, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_added', NULL, NULL, NULL, NULL, NULL, 'resource:3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:37:42'),
(95, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:37:47'),
(96, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:38:38'),
(97, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_removed', NULL, NULL, NULL, NULL, NULL, 'resource:3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:39:23'),
(98, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_added', NULL, NULL, NULL, NULL, NULL, 'resource:3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:39:25'),
(99, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_removed', NULL, NULL, NULL, NULL, NULL, 'folder:1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:39:36'),
(100, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_removed', NULL, NULL, NULL, NULL, NULL, 'resource:3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:46:54'),
(101, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_added', NULL, NULL, NULL, NULL, NULL, 'folder:1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:47:07'),
(102, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:48:00'),
(103, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_removed', NULL, NULL, NULL, NULL, NULL, 'folder:1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:48:57'),
(104, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_added', NULL, NULL, NULL, NULL, NULL, 'folder:1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:49:00'),
(105, NULL, NULL, NULL, NULL, NULL, 2, 'favorite_added', NULL, NULL, NULL, NULL, NULL, 'resource:3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-26 11:49:02'),
(106, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 05:09:57'),
(107, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 05:10:05'),
(108, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 05:10:17'),
(109, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:27:22'),
(110, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:27:30'),
(111, NULL, NULL, NULL, NULL, 1, NULL, 'staff_password_reset', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:27:46'),
(112, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:29:20'),
(113, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:29:51'),
(114, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:33:11'),
(115, NULL, NULL, NULL, NULL, 1, NULL, 'settings_updated', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:33:26'),
(116, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:33:27'),
(117, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:34:57'),
(118, NULL, NULL, NULL, NULL, NULL, 2, 'resource_viewed', NULL, NULL, NULL, NULL, NULL, '3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 10:35:27'),
(119, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-12 11:11:54'),
(120, NULL, NULL, NULL, NULL, NULL, NULL, 'staff_login_failed', NULL, NULL, NULL, NULL, NULL, 'staff@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:19:28'),
(121, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:19:53'),
(122, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:20:03'),
(123, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:20:10'),
(124, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:22:03'),
(125, NULL, NULL, NULL, NULL, 1, NULL, 'staff_password_reset', NULL, NULL, NULL, NULL, NULL, '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:22:16'),
(126, NULL, NULL, NULL, NULL, NULL, 2, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:22:26'),
(127, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:28:46'),
(128, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:28:50'),
(129, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:29:18'),
(130, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:29:55'),
(131, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:30:28'),
(132, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:30:32'),
(133, NULL, NULL, NULL, NULL, 1, NULL, 'assignment_saved', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-13 07:30:56'),
(134, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:20:48'),
(135, NULL, NULL, NULL, NULL, 1, NULL, 'admin_logout', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:23:01'),
(136, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'teacher@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:23:20'),
(137, NULL, NULL, NULL, NULL, NULL, 1, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:24:33'),
(138, NULL, NULL, NULL, NULL, NULL, 1, 'staff_logout', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:33:15'),
(139, NULL, NULL, NULL, NULL, NULL, 1, 'staff_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:33:53'),
(140, NULL, NULL, NULL, NULL, NULL, 1, 'attendance_saved', NULL, NULL, NULL, NULL, NULL, '{\"date\":\"2026-06-14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:44:12'),
(141, NULL, NULL, NULL, NULL, NULL, 1, 'staff_logout', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:44:43'),
(142, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:44:57'),
(143, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:45:09'),
(144, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:45:38'),
(145, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:45:46'),
(146, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:45:53'),
(147, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:53:56'),
(148, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:55:19'),
(149, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:58:41'),
(150, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:58:53'),
(151, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:59:07'),
(152, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, NULL, NULL, 'adminadmin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 16:59:48'),
(153, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-14 17:00:19'),
(154, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:29:03'),
(155, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:29:17'),
(156, NULL, NULL, NULL, NULL, NULL, NULL, 'admin_login_failed', NULL, NULL, NULL, NULL, NULL, 'admin@psnf.local', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:29:25'),
(157, NULL, NULL, NULL, NULL, 1, NULL, 'admin_login_success', NULL, NULL, NULL, NULL, NULL, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-15 12:31:49'),
(158, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:15:03'),
(159, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:16:14'),
(160, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":3}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:18:45'),
(161, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":4}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:19:14'),
(162, NULL, NULL, NULL, 1, NULL, NULL, 'login_account_locked', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:20:08'),
(163, NULL, NULL, NULL, 1, NULL, NULL, 'login_blocked_locked', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:20:35'),
(164, NULL, NULL, NULL, 1, NULL, NULL, 'login_blocked_locked', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:21:15'),
(165, NULL, NULL, NULL, 1, NULL, NULL, 'login_blocked_locked', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:22:22'),
(166, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:26:39'),
(167, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:28:16'),
(168, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:31:49'),
(169, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:32:19'),
(170, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:37:01'),
(171, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:38:02'),
(172, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":3}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:38:16'),
(173, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":4}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:38:35'),
(174, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:39:41'),
(175, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:53:37'),
(176, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:53:56'),
(177, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:54:11'),
(178, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 03:54:52'),
(179, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:02:03'),
(180, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:02:25'),
(181, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:02:49'),
(182, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":3}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:03:16'),
(183, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:03:24'),
(184, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:03:44'),
(185, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:08:54'),
(186, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:09:26'),
(187, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":3}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:09:53'),
(188, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:11:55'),
(189, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:45:19'),
(190, 1, 1, 1, 1, NULL, NULL, 'user_updated', NULL, NULL, NULL, '{\"user_id\":\"1\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:46:53'),
(191, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:47:17'),
(192, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:47:35'),
(193, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:47:46'),
(194, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:48:04'),
(195, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:48:40'),
(196, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:48:59'),
(197, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:58:19'),
(198, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 06:35:15'),
(199, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 06:45:13'),
(200, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 06:58:00'),
(201, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 06:58:17'),
(202, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 06:58:56'),
(203, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:02:39'),
(204, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parents@psnf.edu\",\"reason\":\"user_not_found\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:03:03');
INSERT INTO `activity_logs` (`id`, `tenant_id`, `school_id`, `branch_id`, `user_id`, `admin_id`, `staff_id`, `event`, `model`, `model_id`, `description`, `properties`, `ip_address`, `meta`, `ip`, `user_agent`, `created_at`) VALUES
(205, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parents@psnf.edu\",\"reason\":\"user_not_found\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:03:20'),
(206, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:03:42'),
(207, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:03:57'),
(208, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:06:10'),
(209, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:06:23'),
(210, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:23:45'),
(211, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:23:49'),
(212, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:24:26'),
(213, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:24:34'),
(214, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:41:59'),
(215, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:46:04'),
(216, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":3}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:46:54'),
(217, NULL, NULL, NULL, 2, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@psnf.edu\",\"attempts\":4}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:47:44'),
(218, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:04:19'),
(219, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:04:26'),
(220, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:05:45'),
(221, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:14:49'),
(222, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:15:03'),
(223, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:47:37'),
(224, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:47:42'),
(225, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:47:53'),
(226, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:47:59'),
(227, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:48:59'),
(228, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:49:28'),
(229, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:56:22'),
(230, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:56:27'),
(231, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:58:34'),
(232, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 08:58:43'),
(233, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 13:13:07'),
(234, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 13:13:20'),
(235, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 05:42:15'),
(236, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 05:55:32'),
(237, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 05:55:42'),
(238, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 06:20:16'),
(239, NULL, NULL, NULL, 3, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"driver@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 06:21:41'),
(240, 1, 1, 1, 3, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 06:22:09'),
(241, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 07:42:07'),
(242, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 06:38:23'),
(243, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 06:38:32'),
(244, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 06:38:45'),
(245, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:00:24'),
(246, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:00:30'),
(247, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:33:50\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:03:50'),
(248, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:33:50\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:03:50'),
(249, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:36:09\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:06:09'),
(250, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:36:09\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:06:09'),
(251, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:39:39\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:09:39'),
(252, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:39:39\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:09:39'),
(253, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:11:13'),
(254, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:11:47'),
(255, 1, 1, 1, 1, NULL, NULL, 'role_updated', NULL, NULL, NULL, '{\"role_id\":\"4\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:12:40'),
(256, 1, 1, 1, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"staff@psnf.edu\",\"reason\":\"user_not_found\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:12:59'),
(257, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:16:07'),
(258, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:16:08'),
(259, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:16:25'),
(260, NULL, NULL, NULL, 4, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"teacher@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:16:43'),
(261, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:16:57'),
(262, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:17:27'),
(263, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:18:51'),
(264, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:51:27\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:21:27'),
(265, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 12:51:27\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:21:27'),
(266, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 13:05:04\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:35:04'),
(267, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 13:05:04\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:35:04'),
(268, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:40:51'),
(269, 1, 1, 1, 4, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:45:13'),
(270, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 13:23:16\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:53:16'),
(271, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 13:23:16\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:53:16'),
(272, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 13:25:29\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:55:29'),
(273, 1, 1, 1, 4, NULL, NULL, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-06-20 13:25:29\"}', '127.0.0.1', NULL, NULL, 'Go-http-client/1.1', '2026-06-20 07:55:29'),
(274, 1, 1, 1, 4, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:57:05'),
(275, 1, 1, 1, 4, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:57:10'),
(276, 1, 1, 1, 4, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:57:27'),
(277, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:57:33'),
(278, 1, 1, 1, 1, NULL, NULL, 'role_updated', NULL, NULL, NULL, '{\"role_id\":\"4\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 07:57:58'),
(279, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 08:06:31'),
(280, 1, 1, 1, 4, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 08:06:36'),
(281, 1, 1, 1, 4, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 08:15:31'),
(282, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-20 08:15:38'),
(283, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 06:30:24'),
(284, 1, 1, 1, 1, NULL, NULL, 'student_created', NULL, NULL, NULL, '{\"student_id\":5}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-21 07:30:12'),
(285, 1, 1, 1, 1, NULL, NULL, 'student_updated', NULL, NULL, NULL, '{\"student_id\":5}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-21 07:30:12'),
(286, 1, 1, 1, 1, NULL, NULL, 'student_deleted', NULL, NULL, NULL, '{\"student_id\":5,\"name\":\"John Student\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-21 07:30:12'),
(287, 1, 1, 1, 1, NULL, NULL, 'student_created', NULL, NULL, NULL, '{\"student_id\":6}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-21 07:30:12'),
(288, 1, 1, 1, 1, NULL, NULL, 'student_updated', NULL, NULL, NULL, '{\"student_id\":6}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-21 07:30:12'),
(289, 1, 1, 1, 1, NULL, NULL, 'student_deleted', NULL, NULL, NULL, '{\"student_id\":6,\"name\":\"John Student\"}', '::1', NULL, NULL, 'Go-http-client/1.1', '2026-06-21 07:30:12'),
(290, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 08:35:51'),
(291, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 06:03:01'),
(292, 1, 1, 1, 1, NULL, NULL, 'role_updated', NULL, NULL, NULL, '{\"role_id\":\"7\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 06:35:28'),
(293, 1, 1, 1, 1, NULL, NULL, 'role_updated', NULL, NULL, NULL, '{\"role_id\":\"8\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 06:36:01'),
(294, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 07:15:34'),
(295, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 07:39:56'),
(296, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 08:26:31'),
(297, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 06:40:31'),
(298, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 07:07:16'),
(299, 1, 1, 1, 1, NULL, NULL, 'transport_route_created', NULL, NULL, NULL, '{\"route_id\":\"2\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 07:08:26'),
(300, 1, 1, 1, 1, NULL, NULL, 'student_assigned_transport', NULL, NULL, NULL, '{\"student_id\":3,\"route_id\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 07:22:15'),
(301, 1, 1, 1, 1, NULL, NULL, 'student_assigned_transport', NULL, NULL, NULL, '{\"student_id\":4,\"route_id\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 07:23:11'),
(302, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@example.com\",\"reason\":\"user_not_found\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 08:17:16'),
(303, NULL, NULL, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@example.com\",\"reason\":\"user_not_found\"}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 08:17:34'),
(304, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 08:17:42'),
(305, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 09:40:56'),
(306, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 07:44:44'),
(307, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 10:03:20'),
(308, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 10:07:29'),
(309, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:28:50'),
(310, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 12:57:27'),
(311, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 15:03:22'),
(312, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-02 15:36:41'),
(313, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-03 07:03:31'),
(314, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-03 07:04:18'),
(315, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-10 08:13:38'),
(316, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-10 08:40:01'),
(317, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-10 08:49:47'),
(318, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-10 09:14:43'),
(319, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-13 07:03:08'),
(320, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Unknown', '2026-07-13 07:42:52'),
(321, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Unknown', '2026-07-13 07:42:53'),
(322, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Unknown', '2026-07-13 07:43:56'),
(323, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Unknown', '2026-07-13 07:43:57'),
(324, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-13 07:53:28'),
(325, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":1}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-13 07:56:02'),
(326, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":2}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-13 07:56:08'),
(327, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":3}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-13 07:56:13'),
(328, NULL, NULL, NULL, 1, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.edu\",\"attempts\":4}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-13 07:56:19'),
(329, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-17 10:22:26'),
(330, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-17 10:24:46'),
(331, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-17 10:24:50'),
(332, 1, 1, 1, 2, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 09:50:29'),
(333, 1, 1, 1, 2, NULL, NULL, 'student_created', NULL, NULL, NULL, '{\"student_id\":31}', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 09:51:25'),
(334, 1, 1, 1, 2, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 09:51:36'),
(335, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-24 09:51:40'),
(336, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 09:41:25'),
(337, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 09:42:12'),
(338, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 09:42:16'),
(339, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:23:12'),
(340, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:40:04'),
(341, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:40:06'),
(342, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:42:59'),
(343, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:43:01'),
(344, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 09:07:38'),
(345, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 09:07:41'),
(346, 1, 1, 1, 1, NULL, NULL, 'logout', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 09:15:45'),
(347, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-27 09:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `is_active`, `created_at`) VALUES
(1, 'Super Admin', 'admin@psnf.local', '$2y$10$BqbsyA21xeg9BbtvXY9z8eQ377QKRyli5kXSYe8UoV1zk8kWefoWS', 1, '2026-05-21 07:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `target_audience` enum('all','parents','teachers','staff') NOT NULL DEFAULT 'all',
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `tenant_id`, `school_id`, `branch_id`, `title`, `content`, `target_audience`, `published_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Annual Sensory Integration Workshop', 'Dear Parents, we are hosting a workshop on Sensory Integration Strategies at home. Speakers include Dr. Rajesh Verma (Lead Therapist). Join us this Saturday at 10 AM in the auditorium.', 'parents', '2026-06-16 03:17:31', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 'School Reopening & Safety Protocols Update', 'Please note that new thermal checks and drop-off protocols are active starting Monday. Kindly review the safety handbook shared in the documents tab.', 'all', '2026-06-16 03:17:31', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 'Special Olympics Registration Open', 'Registration is open for the upcoming PSNF Special Olympics events. Sports include Bocce, Athletics, and Unified Soccer. Contact the sports coordinator for signup details.', 'parents', '2026-06-16 03:17:31', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `arrival_time` time DEFAULT NULL,
  `arrival_status` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `medical_certificate` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `employee_id` int(11) NOT NULL,
  `attendance_date` date DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `confidence` float DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `date`, `status`, `arrival_time`, `arrival_status`, `remarks`, `medical_certificate`, `created_by`, `created_at`, `updated_at`, `employee_id`, `attendance_date`, `check_in`, `confidence`, `image_path`, `ip_address`, `user_agent`) VALUES
(1, 1, 1, 1, 1, '2026-05-18', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(2, 1, 1, 1, 1, '2026-05-19', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(3, 1, 1, 1, 1, '2026-05-20', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(4, 1, 1, 1, 1, '2026-05-21', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(5, 1, 1, 1, 1, '2026-05-22', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(6, 1, 1, 1, 1, '2026-05-23', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(7, 1, 1, 1, 1, '2026-05-25', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(8, 1, 1, 1, 1, '2026-05-26', 'absent', NULL, NULL, 'Sick leave - notified by parent', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(9, 1, 1, 1, 1, '2026-05-27', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(10, 1, 1, 1, 1, '2026-05-28', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(11, 1, 1, 1, 1, '2026-05-29', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(12, 1, 1, 1, 1, '2026-05-30', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(13, 1, 1, 1, 1, '2026-06-01', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(14, 1, 1, 1, 1, '2026-06-02', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(15, 1, 1, 1, 1, '2026-06-03', 'late', NULL, NULL, 'Late by 15 mins due to traffic', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(16, 1, 1, 1, 1, '2026-06-04', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(17, 1, 1, 1, 1, '2026-06-05', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(18, 1, 1, 1, 1, '2026-06-06', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(19, 1, 1, 1, 1, '2026-06-08', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(20, 1, 1, 1, 1, '2026-06-09', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(21, 1, 1, 1, 1, '2026-06-10', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(22, 1, 1, 1, 1, '2026-06-11', 'absent', NULL, NULL, 'Sick leave - notified by parent', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(23, 1, 1, 1, 1, '2026-06-12', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(24, 1, 1, 1, 1, '2026-06-13', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(25, 1, 1, 1, 1, '2026-06-15', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(26, 1, 1, 1, 2, '2026-05-18', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(27, 1, 1, 1, 2, '2026-05-19', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(28, 1, 1, 1, 2, '2026-05-20', 'absent', NULL, NULL, 'Sick leave - notified by parent', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(29, 1, 1, 1, 2, '2026-05-21', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(30, 1, 1, 1, 2, '2026-05-22', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(31, 1, 1, 1, 2, '2026-05-23', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(32, 1, 1, 1, 2, '2026-05-25', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(33, 1, 1, 1, 2, '2026-05-26', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(34, 1, 1, 1, 2, '2026-05-27', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(35, 1, 1, 1, 2, '2026-05-28', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(36, 1, 1, 1, 2, '2026-05-29', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(37, 1, 1, 1, 2, '2026-05-30', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(38, 1, 1, 1, 2, '2026-06-01', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(39, 1, 1, 1, 2, '2026-06-02', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(40, 1, 1, 1, 2, '2026-06-03', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(41, 1, 1, 1, 2, '2026-06-04', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(42, 1, 1, 1, 2, '2026-06-05', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(43, 1, 1, 1, 2, '2026-06-06', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(44, 1, 1, 1, 2, '2026-06-08', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(45, 1, 1, 1, 2, '2026-06-09', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(46, 1, 1, 1, 2, '2026-06-10', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(47, 1, 1, 1, 2, '2026-06-11', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(48, 1, 1, 1, 2, '2026-06-12', 'late', NULL, NULL, 'Late by 15 mins due to traffic', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(49, 1, 1, 1, 2, '2026-06-13', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(50, 1, 1, 1, 2, '2026-06-15', 'present', NULL, NULL, 'Attended class', NULL, NULL, '2026-06-16 06:47:31', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(51, 1, 1, 1, 1, '2026-06-19', 'absent', NULL, NULL, 'medical reson', NULL, 2, '2026-06-18 11:07:01', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(52, 1, 1, 1, 7, '2026-07-13', 'present', '08:15:00', 'early', 'Arrived via school van.', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(53, 1, 1, 1, 8, '2026-07-13', 'present', '08:20:00', 'early', 'Arrived via school van.', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(54, 1, 1, 1, 9, '2026-07-13', 'present', '08:25:00', 'early', 'Arrived via school van.', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(55, 1, 1, 1, 2, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(56, 1, 1, 1, 3, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(57, 1, 1, 1, 4, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(58, 1, 1, 1, 10, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(59, 1, 1, 1, 11, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(60, 1, 1, 1, 12, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(61, 1, 1, 1, 13, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(62, 1, 1, 1, 14, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(63, 1, 1, 1, 15, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(64, 1, 1, 1, 16, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(65, 1, 1, 1, 17, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(66, 1, 1, 1, 18, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(67, 1, 1, 1, 19, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(68, 1, 1, 1, 20, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(69, 1, 1, 1, 21, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(70, 1, 1, 1, 22, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(71, 1, 1, 1, 23, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(72, 1, 1, 1, 24, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(73, 1, 1, 1, 25, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(74, 1, 1, 1, 26, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(75, 1, 1, 1, 27, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(76, 1, 1, 1, 28, '2026-07-13', 'present', NULL, NULL, 'Attended school', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(77, 1, 1, 1, 29, '2026-07-13', 'absent', NULL, NULL, 'Sick leave', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(78, 1, 1, 1, 30, '2026-07-13', 'absent', NULL, NULL, 'Sick leave', NULL, NULL, '2026-07-13 07:25:11', '2026-07-25 09:08:36', 0, '2026-07-25', NULL, 0, NULL, NULL, NULL),
(111, NULL, NULL, 0, NULL, '2026-07-27', 'present', NULL, NULL, NULL, NULL, NULL, '2026-07-27 08:52:11', '2026-07-27 08:52:11', 12, '2026-07-27', '2026-07-27 14:22:11', 0.7598, 'uploads/attendance/att_12_1785142331.jpg', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36'),
(112, NULL, NULL, 0, NULL, '2026-07-27', 'present', NULL, NULL, NULL, NULL, NULL, '2026-07-27 09:05:37', '2026-07-27 09:05:37', 14, '2026-07-27', '2026-07-27 14:35:37', 0.7548, 'uploads/attendance/att_14_1785143137.jpg', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36'),
(113, NULL, NULL, 0, NULL, '2026-07-27', 'present', NULL, NULL, NULL, NULL, NULL, '2026-07-27 09:05:44', '2026-07-27 09:05:44', 13, '2026-07-27', '2026-07-27 14:35:44', 0.7586, 'uploads/attendance/att_13_1785143144.jpg', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36'),
(114, NULL, NULL, 0, NULL, '2026-07-27', 'present', NULL, NULL, NULL, NULL, NULL, '2026-07-27 09:05:50', '2026-07-27 09:05:50', 12, '2026-07-27', '2026-07-27 14:35:50', 0.7615, 'uploads/attendance/att_12_1785143150.jpg', '::1', 'Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36'),
(115, NULL, NULL, 0, NULL, '2026-07-27', 'present', NULL, NULL, NULL, NULL, NULL, '2026-07-27 09:07:04', '2026-07-27 09:07:04', 15, '2026-07-27', '2026-07-27 14:37:04', 0.7654, 'uploads/attendance/att_15_1785143224.jpg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `clock_time` datetime NOT NULL,
  `clock_type` enum('CHECK_IN','CHECK_OUT') NOT NULL,
  `status` enum('PRESENT','LATE','EARLY_LEAVE','OVERTIME','REGULAR','INCOMPLETE') NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `is_synced` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'USER_LOGIN', 'Successful login for user: admin@psnf.edu', '2026-07-02 12:05:58'),
(2, 1, 'USER_LOGIN', 'Successful login for user: admin@psnf.edu', '2026-07-02 12:10:27'),
(3, 1, 'USER_LOGIN', 'Successful login for user: admin@psnf.edu', '2026-07-02 12:26:15'),
(4, 1, 'USER_LOGIN', 'Successful login for user: admin@psnf.edu', '2026-07-10 09:14:52'),
(5, 1, 'USER_LOGIN', 'Successful login for user: admin@psnf.edu', '2026-07-13 13:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `tenant_id`, `school_id`, `name`, `code`, `email`, `phone`, `address`, `city`, `is_main`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Main Campus', 'MAIN', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, '2026-06-16 03:13:12', '2026-06-16 06:48:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `certificate_type` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `issued_at` date NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `title`, `certificate_type`, `file_path`, `issued_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'Outstanding Progress in Sensory Integration', 'academic', '/uploads/certificates/sensory_cert.pdf', '2026-06-06', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 1, 'Active Participation Certificate — Special Sports Meet', 'sports', '/uploads/certificates/sports_cert.pdf', '2026-06-01', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 2, 'Outstanding Progress in Sensory Integration', 'academic', '/uploads/certificates/sensory_cert.pdf', '2026-06-06', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(4, 1, 1, 1, 2, 'Active Participation Certificate — Special Sports Meet', 'sports', '/uploads/certificates/sports_cert.pdf', '2026-06-01', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) NOT NULL,
  `school_id` varchar(36) NOT NULL,
  `sender_id` varchar(100) NOT NULL,
  `sender_role` enum('Parent','Teacher') NOT NULL,
  `receiver_id` varchar(100) NOT NULL,
  `receiver_role` enum('Parent','Teacher') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `school_id`, `sender_id`, `sender_role`, `receiver_id`, `receiver_role`, `message`, `is_read`, `created_at`) VALUES
(1, 'sch-greenwood', '1', 'Teacher', 'par-rajesh', 'Parent', 'Hello Mr. Rajesh, Arjun had a wonderful day in class. He excelled at the money-counting exercise today!', 0, '2026-06-14 16:09:05'),
(2, 'sch-greenwood', 'par-rajesh', 'Parent', '1', 'Teacher', 'That is wonderful news Mrs. Preeti! Thank you for the update. Did he face any sensory issues today?', 0, '2026-06-14 16:09:05'),
(3, 'sch-greenwood', '1', 'Teacher', 'par-rajesh', 'Parent', 'He was a bit sensitive during the morning bell, but recovered quickly in the quiet corner with his earmuffs.', 0, '2026-06-14 16:09:05');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `tenant_id`, `school_id`, `branch_id`, `name`, `section`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Class A', 'S1', '2026-07-01 08:40:45', '2026-07-01 08:40:45'),
(2, 1, 1, 1, 'Class B', 'S2', '2026-07-01 08:40:45', '2026-07-01 08:40:45');

-- --------------------------------------------------------

--
-- Table structure for table `communication_messages`
--

CREATE TABLE `communication_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `receiver_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `communication_messages`
--

INSERT INTO `communication_messages` (`id`, `tenant_id`, `school_id`, `branch_id`, `sender_id`, `receiver_id`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 1, 1, 2, 1, 'Aarav Sensory Update', 'Hello Ms. Sarah, Aarav seemed a bit sensitive to noise this morning. I have given him his noise-canceling headphones. Please guide the class helper to assist him if it gets noisy.', 1, '2026-06-16 01:17:31'),
(2, 1, 1, 1, 1, 2, 'Aarav Sensory Update', 'Hello Mr. Rajesh, thank you for letting us know! We have briefed Mrs. Nair (our class helper) to keep an eye on Aarav and make sure he has his headphones on during group activities.', 1, '2026-06-16 02:17:31'),
(3, 1, 1, 1, 2, 1, 'Aarav Sensory Update', 'Excellent! Thanks for the quick update. Have a great day.', 1, '2026-06-16 02:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `school_id` varchar(36) NOT NULL,
  `name` varchar(120) NOT NULL,
  `license_number` varchar(90) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `school_id`, `name`, `license_number`, `phone`, `email`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'sch-greenwood', 'Sohan Singh', 'DL-142018009876', '+91 91234 56789', 'driver@psnf.local', '$2y$10$sqGPxsTidqeSFeo7Y6H2ce3KUf20aR5xFDdtJeHrciLBCNOi/92Qi', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `alt_phone` varchar(30) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `priority` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('ACTIVE','SUSPENDED','TERMINATED') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `name`, `employee_id`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `status`, `created_at`, `department`, `designation`) VALUES
(12, '1', 'het shah', '1', '', '', NULL, NULL, NULL, 'ACTIVE', '2026-07-27 14:21:57', '', ''),
(13, '2', 'B.P.K', '2', '', '', NULL, NULL, NULL, 'ACTIVE', '2026-07-27 14:29:27', '', ''),
(14, '420', 'akshat', '420', '', '', NULL, NULL, NULL, 'ACTIVE', '2026-07-27 14:35:26', '', ''),
(15, '234', 'het lodu', '234', '', '', NULL, NULL, NULL, 'ACTIVE', '2026-07-27 14:36:59', '', ''),
(16, '2325', 'vegr', '2325', '', '', NULL, NULL, NULL, 'ACTIVE', '2026-07-27 15:15:10', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `employee_shifts`
--

CREATE TABLE `employee_shifts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `marks_obtained` decimal(5,2) NOT NULL,
  `max_marks` decimal(5,2) NOT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `date_published` date DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `exam_name`, `subject`, `marks_obtained`, `max_marks`, `grade`, `remarks`, `date_published`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'First Term Evaluation', 'Speech Therapy', 40.00, 50.00, 'A', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 1, 'First Term Evaluation', 'Sensory Integration', 35.00, 50.00, 'B', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 1, 'First Term Evaluation', 'Visual Arts', 47.00, 50.00, 'A+', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(4, 1, 1, 1, 1, 'First Term Evaluation', 'Math Foundations', 36.00, 50.00, 'B', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(5, 1, 1, 1, 1, 'First Term Evaluation', 'Life Skills', 43.00, 50.00, 'A', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(6, 1, 1, 1, 2, 'First Term Evaluation', 'Occupational Therapy', 35.00, 50.00, 'B', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(7, 1, 1, 1, 2, 'First Term Evaluation', 'Music Therapy', 35.00, 50.00, 'B', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(8, 1, 1, 1, 2, 'First Term Evaluation', 'Social Communication', 48.00, 50.00, 'A+', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(9, 1, 1, 1, 2, 'First Term Evaluation', 'Basic Literacy', 47.00, 50.00, 'A+', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(10, 1, 1, 1, 2, 'First Term Evaluation', 'Motor Coordination', 37.00, 50.00, 'B', 'Shows positive focus and excellent participation.', '2026-06-11', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `face_embeddings`
--

CREATE TABLE `face_embeddings` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `embedding_vector` text NOT NULL,
  `capture_angle` varchar(30) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `embedding` longtext NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `face_embeddings`
--

INSERT INTO `face_embeddings` (`id`, `employee_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(14, 12, '', '', '2026-07-27 14:21:57', '[0.07568433138097391,0,0.0029680129953323104,0.0008904038985996931,0.05372103521551482,0.040068175436986195,0.07123231188797545,0.04244258583325204,0.015433667575728014,0.008013635087397239,0.05312743261644835,0.0008904038985996931,0.007420032488330776,0.03146093775052249,0.05965706120617944,0.05372103521551482,0.021963296165459098,0.04125538063511911,0.038584168939320033,0.012465654580395703,0.009794442884596626,0.021963296165459098,0.05906345860711298,0.04926901572251635,0.0563922469113139,0.03146093775052249,0.06737389499404345,0.04956581702204958,0.05579864431224744,0.04956581702204958,0.038584168939320033,0.043036188432318496,0.04837861182391666,0.01157525068179601,0.029680129953323104,0.04273938713278527,0.0014840064976661552,0.0382873676397868,0.021963296165459098,0.019885687068726482,0.027305719557057257,0.05936025990664621,0.07390352358377453,0.06054746510477914,0.06945150409077606,0.0344289507458548,0.006826429889264314,0.03710016244165388,0.06559308719684406,0.06381227939964468,0.02849292475519018,0.04125538063511911,0.06707709369451022,0.07004510668984253,0.014543263677128321,0.05342423391598159,0.01127844938226278,0.02018248836825971,0.05401783651504805,0.024040905262191714,0.03353854684725511,0.032648142948655416,0.04273938713278527,0.021072892266859405,0.027602520856590484,0.020776090967326175,0.024634507861258176,0.0602506638052459,0.058469856008046515,0.05401783651504805,0.06529628589731083,0.05995386250571267,0.024040905262191714,0.046894605326250505,0.015136866276194785,0.0035616155943987726,0.0649994845977776,0.04244258583325204,0.03947457283791973,0.013356058478995398,0.007123231188797545,0.004452019492998466,0.016324071474327708,0.006826429889264314,0.019885687068726482,0.013356058478995398,0.029383328653789873,0.05490824041364774,0.06618668979591052,0.058766657307579746,0.046597804026717274,0.010684846783196318,0.07063870928890899,0.04897221442298313,0.032351341649122185,0.0005936025990664621,0.07568433138097391,0.04362979103138496,0.05787625340898005,0.0005936025990664621,0.027602520856590484,0.0023744103962658482,0.051643426118782206,0.010684846783196318,0.07301311968517483,0.07509072878190745,0.0697483053903093,0.05431463781458128,0.016324071474327708,0.06410908069917791,0.00029680129953323103,0.07063870928890899,0.014840064976661552,0.04155218193465235,0.06767069629357668,0.012465654580395703,0.046894605326250505,0.043926592330918196,0.0477850092248502,0.07420032488330776,0.02433770656172495,0.04095857933558588,0.0029680129953323104,0.04481699622951789,0.006826429889264314,0.06232827290197852,0.04333298973185173,0.009200840285530162,0.010388045483663087,0.03502255334492126,0.05431463781458128,0.07568433138097391,0.07301311968517483,0.04155218193465235,0.0649994845977776,0.050456220920649275,0.07152911318750868,0.029680129953323104,0.018104879271527093,0.06232827290197852,0.025524911759857872,0.010091244184129855,0.05936025990664621,0.027305719557057257,0.06054746510477914,0.0658898884963773,0.07568433138097391,0.07568433138097391,0.012168853280862474,0.019885687068726482,0.03146093775052249,0.029680129953323104,0.07123231188797545,0.04155218193465235,0.06737389499404345,0.005342423391598159,0.06470268329824437,0.00563922469113139,0.01751127667246063,0.04837861182391666,0.03116413645098926,0.0023744103962658482,0.06915470279124283,0.05342423391598159,0.024931309160791407,0.035616155943987725,0.010684846783196318,0.05223702871784866,0.024634507861258176,0.06262507420151174,0.04630100272718404,0.02285370006405879,0.012168853280862474,0.04808181052438343,0.051346624819248975,0.030867335151456027,0.05698584951038036,0.0026712116957990795,0.06796749759310991,0.03680336114212065,0.019292084469660017,0.04956581702204958,0.012168853280862474,0.04125538063511911,0.07212271578657514,0.025821713059391103,0.023447302663125252,0.05995386250571267,0.07390352358377453,0.05283063131691512,0.0563922469113139,0.051940227418315436,0.051643426118782206,0.020776090967326175,0.04956581702204958,0.03680336114212065,0,0.015433667575728014,0.018401680571060324,0.013652859778528629,0.06054746510477914,0.0023744103962658482,0.01751127667246063,0.02878972605472341,0.024040905262191714,0.025228110460324638,0.015730468875261246,0.05668904821084713,0.07212271578657514,0.06915470279124283,0.031757739050055724,0.043036188432318496,0.03383534814678834,0.01662087277386094,0.02255689876452556,0.04570740012811758,0.051346624819248975,0.06084426640431237,0.03353854684725511,0.02047928966779294,0.0047488207925316965,0.04956581702204958,0.006232827290197852,0.027008918257524026,0.04184898323418558,0.05490824041364774,0.04570740012811758,0.061437869003378824,0.03383534814678834,0.04125538063511911,0.06084426640431237,0.03146093775052249,0.04184898323418558,0.07568433138097391,0.010981648082729549,0.04333298973185173,0.029086527354256642,0.07330992098470807,0.058766657307579746,0.021072892266859405,0.07063870928890899,0.0017808077971993863,0.04184898323418558,0.0017808077971993863,0.009200840285530162,0.0658898884963773,0.07538753008144068,0.016324071474327708,0.05312743261644835,0.010684846783196318,0.07212271578657514,0.07063870928890899,0.027305719557057257,0.07093551058844222,0.02018248836825971,0.026712116957990795,0.06559308719684406,0.05579864431224744,0.030570533851922797,0.024040905262191714,0.04986261832158281,0.07212271578657514,0.02433770656172495,0.01157525068179601,0.011872051981329242,0.026712116957990795,0.03472575204538803,0.04897221442298313,0.014840064976661552,0.04333298973185173,0.013356058478995398,0.051346624819248975,0.035319354644454494,0.006826429889264314,0.01958888576919325,0.03116413645098926,0.05490824041364774,0.035912957243520956,0.054611439114114514,0.03799056634025357,0.04956581702204958,0.007420032488330776,0.018104879271527093,0.04066177803605265,0.004155218193465235,0.013652859778528629,0.013356058478995398,0.02285370006405879,0.004452019492998466,0.05965706120617944,0.019292084469660017,0.051049823519715744,0.018104879271527093,0.02315050136359202,0.013059257179462165,0.07123231188797545,0.051346624819248975,0.0047488207925316965,0.03294494424818865,0.0563922469113139,0.02315050136359202,0.04541059882858435,0.04897221442298313,0.021072892266859405,0.027602520856590484,0.06678029239497699,0.00831043638693047,0.04036497673651942,0.018995283170126786,0.07568433138097391,0.04184898323418558,0.0602506638052459,0.06767069629357668,0.01751127667246063,0.062031471602445286,0.07330992098470807,0.035616155943987725,0.0172144753729274,0.01127844938226278,0.021072892266859405,0.050753022220182506,0.050456220920649275,0.06915470279124283,0.06826429889264314,0.013356058478995398,0.0029680129953323104,0.02315050136359202,0.025228110460324638,0.01394966107806186,0.015433667575728014,0.06856110019217637,0.027305719557057257,0.02285370006405879,0.046597804026717274,0.04422339363045143,0.06826429889264314,0.050456220920649275,0.019885687068726482,0.02789932215612372,0.029383328653789873,0.05372103521551482,0.02789932215612372,0.06796749759310991,0.06262507420151174,0.0649994845977776,0.06826429889264314,0.058766657307579746,0.020776090967326175,0.046597804026717274,0.05965706120617944,0.07123231188797545,0.06054746510477914,0.006826429889264314,0.0382873676397868,0.04897221442298313,0.00563922469113139,0.05312743261644835,0.015433667575728014,0.005342423391598159,0.03383534814678834,0.01394966107806186,0.02433770656172495,0.07093551058844222,0.03710016244165388,0.010981648082729549,0.02226009746499233,0.0555018430127142,0.06648349109544376,0.04452019492998466,0.024040905262191714,0.05283063131691512,0.004452019492998466,0.0023744103962658482,0.07034190798937576,0.035616155943987725,0.04362979103138496,0.005045622092064927,0.012762455879928936,0.029383328653789873,0.07538753008144068,0.01662087277386094,0.07449712618284099,0.07063870928890899,0.0035616155943987726,0.016027270174794477,0.04808181052438343,0.02641531565845756,0.004452019492998466,0.06292187550104499,0.0649994845977776,0.021072892266859405,0.013356058478995398,0.035616155943987725,0.0005936025990664621,0.01958888576919325,0.013652859778528629,0.0391777715383865,0.06321867680057822,0.0391777715383865,0.051346624819248975,0.05668904821084713,0.04214578453371881,0.04422339363045143,0.07063870928890899,0.032351341649122185,0.06470268329824437,0.03502255334492126,0.05520504171318097,0.010684846783196318,0.019885687068726482,0.021072892266859405,0.020776090967326175,0.04986261832158281,0.04333298973185173,0.05757945210944682,0.06321867680057822,0.025228110460324638,0.01751127667246063,0.06856110019217637,0.04897221442298313,0.01662087277386094,0.005342423391598159,0.05995386250571267,0.07509072878190745,0.03353854684725511,0.05372103521551482,0.008013635087397239,0.06737389499404345,0.05936025990664621,0.0008904038985996931,0.04155218193465235,0.0602506638052459,0.043926592330918196,0.02611851435892433,0.04541059882858435,0.029086527354256642,0.05312743261644835,0.032648142948655416,0.026712116957990795,0.06381227939964468,0.005342423391598159,0.06618668979591052,0.05936025990664621,0.03324174554772188,0.07093551058844222,0.04095857933558588,0.05579864431224744,0.04244258583325204,0.030570533851922797,0.04362979103138496,0.01394966107806186,0.05787625340898005,0.07182591448704191,0.012762455879928936,0.04273938713278527,0.06440588199871114,0.04333298973185173,0.07182591448704191,0.046004201427650805,0.05757945210944682,0.029086527354256642,0.06292187550104499,0.06054746510477914,0.03116413645098926,0.0032648142948655413,0.018104879271527093,0.06321867680057822,0.016324071474327708,0.03027373255238957,0.054611439114114514,0.0649994845977776,0.05223702871784866,0.061734670302912055,0.009200840285530162,0.07093551058844222,0.02315050136359202,0.07063870928890899,0.01394966107806186,0.05965706120617944,0.026712116957990795,0.0032648142948655413,0.035319354644454494,0.051643426118782206,0.06529628589731083,0.051049823519715744,0.04452019492998466,0.0026712116957990795,0.014543263677128321,0.050753022220182506,0.06381227939964468,0.02047928966779294,0.06321867680057822,0.018104879271527093,0.01751127667246063,0.06351547810011145,0.020776090967326175,0.027008918257524026,0.005936025990664621,0.07123231188797545,0.007420032488330776,0.03947457283791973,0.024634507861258176,0.04926901572251635,0.027602520856590484,0.027008918257524026,0.04214578453371881,0.02611851435892433,0.0727163183856416,0.03146093775052249,0.04570740012811758,0.009200840285530162,0.07479392748237422]', 'uploads/faces/emp_1_1785142317.jpg'),
(15, 13, '', '', '2026-07-27 14:29:27', '[0.07748448023020406,0,0.000607721413570228,0.07626903740306361,0.026132020783519803,0.0030386070678511397,0.045882966724552214,0.010331264030693876,0.056214230755246085,0.06107600206380791,0.03312081703957742,0.07444587316235293,0.04679454884490755,0.014889174632470586,0.05408720580775028,0.0018231642407106838,0.05287176298060983,0.02278955300888355,0.006684935549272507,0.035551702693858336,0.02704360290387514,0,0.021877970888528206,0.05682195216881631,0.02734746361066026,0.038286449054924364,0.0030386070678511397,0.05287176298060983,0.008508099789983192,0.07444587316235293,0.03312081703957742,0.04679454884490755,0.05287176298060983,0.030386070678511398,0.06715321619951019,0.04922543449918846,0.04162891682956062,0.02734746361066026,0.036463284814213674,0.056214230755246085,0.06016441994345257,0.034640120573503,0.025524299369949575,0.030993792092081626,0.010331264030693876,0.0717111268012869,0.034640120573503,0.020358667354602636,0.0695841018537911,0.07444587316235293,0.03068993138529651,0.07626903740306361,0.04466752389741175,0.025524299369949575,0.038286449054924364,0.015193035339255699,0.010331264030693876,0.05408720580775028,0.045882966724552214,0.04223663824313084,0.05378334510096517,0.03068993138529651,0.011242846151049217,0.045882966724552214,0.0282590457310156,0.054391066514535395,0.042844359656701074,0.07323043033521247,0.04497138460419687,0.0282590457310156,0.03767872764135413,0.015800756752825926,0.0498331559127587,0.03068993138529651,0.0027347463610660257,0.004861771308561824,0.011546706857834332,0.037982588348139244,0.017623920993536612,0.013977592512115243,0.013369871098545014,0.04041347400242016,0.06715321619951019,0.006684935549272507,0.05287176298060983,0.027955185024230486,0.023701135129238892,0.04922543449918846,0.0695841018537911,0.002127024947495798,0.006077214135702279,0.060772141357022795,0.008204239083198077,0.07566131598949338,0.06441846983844417,0.07626903740306361,0.048617713085618235,0.0009115821203553419,0.0565180914620312,0.047402270258477784,0.006077214135702279,0.017016199579966383,0.004861771308561824,0.05530264863489075,0.04375594177705641,0.06593777337236974,0.060772141357022795,0.027651324317445374,0.06381074842487393,0.021574110181743093,0.005773353428917166,0.017623920993536612,0.021574110181743093,0.02734746361066026,0.05287176298060983,0.0030386070678511397,0.019750945941032407,0.04466752389741175,0.012762149684974787,0.0695841018537911,0.035551702693858336,0.007292656962842735,0.007596517669627849,0.03281695633279231,0.04497138460419687,0.04497138460419687,0.025220438663164462,0.069280241147006,0.011546706857834332,0.05591037004846097,0.010027403323908762,0.04071733470920527,0.06137986277059302,0.035247841987073224,0.02218183159531332,0.05347948439418006,0.013066010391759902,0.044059802483841524,0.019447085234247295,0.0282590457310156,0.0173200602867515,0.05560650934167586,0.011850567564619446,0.0628991663045186,0.07079954468093155,0.05530264863489075,0.0565180914620312,0.025828160076734687,0.0282590457310156,0.032209234919222084,0.035551702693858336,0.02673974219709003,0.030082209971726285,0.07140726609450178,0.06137986277059302,0.000607721413570228,0.010331264030693876,0.003950189188206481,0.06259530559773348,0.034032399159932766,0.054998787928105634,0.047098409551692665,0.040109613295635047,0.06016441994345257,0.03312081703957742,0.010331264030693876,0.020358667354602636,0.04892157379240335,0.015193035339255699,0.05864511640952699,0.06107600206380791,0.040109613295635047,0.029778349264941172,0.054391066514535395,0.008204239083198077,0.07231884821485712,0.0477061309652629,0.03160151350565185,0.04922543449918846,0.057733534289171655,0.07079954468093155,0.031905374212436964,0.041325056122775504,0.009115821203553419,0.025524299369949575,0.008811960496768306,0.04071733470920527,0.016104617459611042,0.04071733470920527,0.020358667354602636,0.04223663824313084,0.044059802483841524,0.07079954468093155,0.004557910601776709,0.059556698529882345,0.03068993138529651,0.07687675881663383,0.013066010391759902,0.0695841018537911,0.03129765279886674,0.028866767144585827,0.05712581287560143,0.04649068813812244,0.003950189188206481,0,0.030386070678511398,0.07718061952341895,0.06776093761308041,0.042844359656701074,0.06350688771808882,0,0.004254049894991596,0.0498331559127587,0.05196018086025449,0.014281453218900357,0.05378334510096517,0.018231642407106837,0.059252837823097225,0.044059802483841524,0.04527524531098199,0.03342467774636254,0.007292656962842735,0.035551702693858336,0.05682195216881631,0.044059802483841524,0.05378334510096517,0.06836865902665064,0.005773353428917166,0.06533005195879951,0.062291444890948365,0.031905374212436964,0.017016199579966383,0.04314822036348619,0.04314822036348619,0.019447085234247295,0.012154428271404559,0.01063512473747899,0.06593777337236974,0.048617713085618235,0.016104617459611042,0.06198758418416325,0.017016199579966383,0.010331264030693876,0.07444587316235293,0.05104859873989915,0.07474973386913804,0.01458531392568547,0.001215442827140456,0.03342467774636254,0.04892157379240335,0.05591037004846097,0.038286449054924364,0.045882966724552214,0.04892157379240335,0.06776093761308041,0.05104859873989915,0.000303860706785114,0.007596517669627849,0.027651324317445374,0.01671233887318127,0.04618682743133733,0.03980575258884993,0.037071006227783906,0.04223663824313084,0.05104859873989915,0.06381074842487393,0.0282590457310156,0.013066010391759902,0.034032399159932766,0.006077214135702279,0.029474488558156056,0.026132020783519803,0.002430885654280912,0.027955185024230486,0.0565180914620312,0.048617713085618235,0.03889417046849459,0.02734746361066026,0.027955185024230486,0.06533005195879951,0.01367373180533013,0.07657289810984873,0.06502619125201439,0.017623920993536612,0.0030386070678511397,0.07049568397414645,0.06806479831986553,0.06441846983844417,0.04375594177705641,0.07201498750807202,0.032513095626007196,0.06472233054522927,0.0282590457310156,0.04071733470920527,0.06624163407915484,0.002430885654280912,0.05986055923666746,0.018535503113891953,0.013977592512115243,0.05013701661954381,0.041021195415990384,0.02278955300888355,0.018535503113891953,0.051656320153469375,0.03433625986671788,0.06350688771808882,0.021574110181743093,0.06684935549272508,0.04254049894991596,0.019143224527462182,0.03160151350565185,0.05986055923666746,0.06867251973343576,0.030082209971726285,0.013977592512115243,0.0015193035339255698,0.04618682743133733,0.020662528061387752,0.006381074842487394,0.054998787928105634,0.06533005195879951,0.06381074842487393,0.03433625986671788,0.007596517669627849,0.05287176298060983,0.016408478166396154,0.059252837823097225,0.004557910601776709,0.01367373180533013,0.05135245944668426,0.06684935549272508,0.04314822036348619,0.014889174632470586,0.04649068813812244,0.06684935549272508,0.07596517669627849,0.05530264863489075,0.016408478166396154,0.01671233887318127,0.025524299369949575,0.037982588348139244,0.015193035339255699,0.009115821203553419,0.01458531392568547,0.011242846151049217,0.03312081703957742,0.000303860706785114,0.042844359656701074,0.02218183159531332,0.0455791060177671,0.07323043033521247,0.004861771308561824,0.0628991663045186,0.02278955300888355,0.014889174632470586,0.045882966724552214,0.01458531392568547,0.035247841987073224,0.036463284814213674,0.01367373180533013,0.06988796256057622,0.07323043033521247,0.050440877326328924,0.045882966724552214,0.006381074842487394,0.04162891682956062,0.011850567564619446,0.0632030270113037,0.034032399159932766,0.07474973386913804,0.02218183159531332,0.040109613295635047,0.03342467774636254,0.034032399159932766,0.021877970888528206,0.03433625986671788,0.026132020783519803,0.011546706857834332,0.009115821203553419,0.001215442827140456,0.05530264863489075,0.03342467774636254,0.04649068813812244,0.07566131598949338,0.025828160076734687,0.04162891682956062,0.02127024947495798,0.034032399159932766,0.07353429104199757,0.04679454884490755,0.0009115821203553419,0.0282590457310156,0.033728538453147654,0.06198758418416325,0.0434520810702713,0.036463284814213674,0.020054806647817523,0.06806479831986553,0.07474973386913804,0.06533005195879951,0.06988796256057622,0.06168372347737813,0.05013701661954381,0.06016441994345257,0.04922543449918846,0.04254049894991596,0.0628991663045186,0.056214230755246085,0.07535745528270826,0.011242846151049217,0.036463284814213674,0.02461271724959423,0.06745707690629531,0.0455791060177671,0.016408478166396154,0.06441846983844417,0.038286449054924364,0.020054806647817523,0.05135245944668426,0.04193277753634573,0.006381074842487394,0.04527524531098199,0.05742967358238654,0.04375594177705641,0.007596517669627849,0.0738381517487827,0.06684935549272508,0.06107600206380791,0.01367373180533013,0.032209234919222084,0.0282590457310156,0.014281453218900357,0.02917062785137094,0.023397274422453776,0.056214230755246085,0.010331264030693876,0.010938985444264103,0.02491657795637935,0.06411460913165905,0.06411460913165905,0.0009115821203553419,0.03676714552099879,0.013066010391759902,0.07292656962842735,0.0018231642407106838,0.07566131598949338,0.020054806647817523,0.06381074842487393,0.062291444890948365,0.025220438663164462,0.020966388768172865,0.034640120573503,0.05074473803311404,0.02704360290387514,0.02734746361066026,0.06745707690629531,0.035247841987073224,0.06381074842487393,0.032209234919222084,0.07231884821485712,0.05256790227382472,0.07535745528270826,0.04618682743133733,0.016408478166396154,0.027955185024230486,0.016408478166396154,0.07262270892164224,0.06472233054522927,0.02461271724959423,0.07626903740306361,0.07505359457592316,0.032209234919222084,0.05469492722132052,0.06350688771808882,0,0.05287176298060983,0.0069887962560576215,0.051656320153469375,0.013977592512115243,0.026132020783519803,0.023397274422453776,0.06624163407915484,0.06533005195879951,0.02673974219709003,0.05469492722132052,0.0632030270113037,0.045882966724552214,0.07019182326736133,0,0.027955185024230486,0.031905374212436964,0.04679454884490755,0.018535503113891953,0.023093413715668663,0.07231884821485712,0.0695841018537911,0.025828160076734687,0.032209234919222084,0.07414201245556781,0.012154428271404559,0.05074473803311404,0.04892157379240335,0.036463284814213674,0.07201498750807202,0.06350688771808882,0.07201498750807202,0.05226404156703961,0.054391066514535395,0.045882966724552214,0.05013701661954381,0.045882966724552214,0.014281453218900357]', 'uploads/faces/emp_2_1785142767.jpg'),
(16, 14, '', '', '2026-07-27 14:35:26', '[0.07571688786913364,0,0.010392514021253638,0.017518809350113275,0.037116121504477274,0.010986371965325275,0.0537441439384831,0.026129749539152,0.03236525795190418,0.04157005608501455,0.03949155328076382,0.01930038318232818,0.010392514021253638,0.010986371965325275,0.010392514021253638,0.04186698505705037,0.025238962623044547,0.04958713832998164,0.05433800188255473,0.03147447103579673,0.008017082244967092,0.02761439439933109,0.0668090187080591,0.07096602431656054,0.05285335702237563,0.029989826175617636,0.05255642805033982,0.06829366356823818,0.06621516076398745,0.006829366356823819,0.062058155155486,0.06829366356823818,0.062058155155486,0.026723607483223638,0.042460843001122,0.014846448601790909,0.05077485421812491,0.03325604486801164,0.02494203365100873,0.06324587104362928,0.01692495140604164,0.020488099070471454,0.04097619814094291,0.07512302992506201,0.03949155328076382,0.002969289720358182,0.02494203365100873,0.03860076636465636,0.07126295328859636,0.06443358693177255,0.07126295328859636,0.037116121504477274,0.023160459818793822,0.006532437384788,0.029395968231546003,0.033552973840047455,0.006829366356823819,0.06354280001566509,0.035334547672262365,0.03770997944854891,0.04038234019687127,0.06502744487584418,0.0035631476644298185,0.021675814958614727,0.07126295328859636,0.061464297211414366,0.052259499078304,0.05819807851902037,0.03236525795190418,0.04424241683333691,0.058791936463092005,0.01930038318232818,0.04869635141387419,0.022863530846758,0.0668090187080591,0.029099039259510184,0.0668090187080591,0.04038234019687127,0.03949155328076382,0.04483627477740855,0.014252590657719274,0.04305470094519363,0.04424241683333691,0.058791936463092005,0.06265201309955763,0.009798656077182001,0.06740287665213072,0.060573510295306915,0.07541995889709782,0.04008541122483546,0.022566601874722182,0.05077485421812491,0.06116736823937855,0.03652226356040564,0.03414683178411909,0.04008541122483546,0.04275777197315782,0.013658732713647637,0.02791132337136691,0.0044539345805372724,0.015737235517898365,0.037116121504477274,0.02494203365100873,0.06502744487584418,0.0668090187080591,0.04216391402908618,0.016034164489934184,0.011580229909396911,0.05404107291051891,0.03147447103579673,0.017518809350113275,0.004157005608501455,0.01692495140604164,0.0700752374004531,0.03978848225279964,0.040679269168907094,0.04038234019687127,0.039194624308728006,0.014252590657719274,0.048993280385910006,0.008610940189038728,0.06265201309955763,0.06799673459620237,0.027020536455259456,0.01989424112639982,0.053447214966447276,0.04038234019687127,0.039194624308728006,0.00831401121700291,0.03860076636465636,0.05433800188255473,0.0537441439384831,0.028802110287474363,0.06235508412752182,0.06087043926734273,0.007423224300895454,0.06562130281991582,0.06294894207159346,0.068590592540274,0.040679269168907094,0.068590592540274,0.007720153272931273,0.07423224300895455,0.005047792524608909,0.001484644860179091,0.05433800188255473,0.05641650468680546,0.0008907869161074546,0.03503761870022655,0.03295911589597582,0.06948137945638146,0.013064874769576,0.03295911589597582,0.06473051590380836,0.007720153272931273,0.05285335702237563,0.013955661685683456,0.021675814958614727,0.02108195701454309,0.05196257010626818,0.03384990281208328,0.05582264674273382,0.06651208973602328,0.024051246734901274,0.030286755147653457,0.012471016825504364,0.03117754206376091,0.06562130281991582,0.024051246734901274,0.010986371965325275,0.01781573832214909,0.020191170098435636,0.008610940189038728,0.045727061693516,0.015737235517898365,0.013955661685683456,0.057604220574948725,0.004750863552573091,0.06116736823937855,0.0023754317762865454,0.06651208973602328,0.05908886543512782,0.039194624308728006,0.053150285994411454,0.010986371965325275,0.03088061309172509,0.055525717770698,0.02761439439933109,0.05463493085459055,0.01811266729418491,0.03978848225279964,0.01662802243400582,0.06116736823937855,0.017221880378077457,0.06888752151230983,0.008907869161074545,0.006829366356823819,0.013361803741611819,0.010986371965325275,0.05196257010626818,0.05018099627405327,0.016034164489934184,0.05433800188255473,0.024348175706937095,0.02791132337136691,0.03503761870022655,0.068590592540274,0.023754317762865455,0.060276581323271086,0.06562130281991582,0.06324587104362928,0.046320919637587644,0.07155988226063219,0.037116121504477274,0.04661784860962346,0.014252590657719274,0.05908886543512782,0.03414683178411909,0.06740287665213072,0,0.03325604486801164,0.013064874769576,0.07304452712081128,0.006532437384788,0.07334145609284709,0.03236525795190418,0.04988406730201746,0.06621516076398745,0.06354280001566509,0.06116736823937855,0.03949155328076382,0.05849500749105618,0.02494203365100873,0.06918445048434564,0.056713433658841274,0.03474068972819073,0.03444376075615491,0.025535891595080365,0.05077485421812491,0.0044539345805372724,0.017221880378077457,0.03830383739262055,0.040679269168907094,0.07037216637248891,0.015737235517898365,0.04424241683333691,0.045727061693516,0.07066909534452473,0.06948137945638146,0.047805564497766725,0.05641650468680546,0.06176122618345018,0.025832820567116183,0.057010362630877096,0.04424241683333691,0.06769980562416655,0.03860076636465636,0.03295911589597582,0.03474068972819073,0.07393531403691873,0.014252590657719274,0.04275777197315782,0.015737235517898365,0.05522878879866218,0.033552973840047455,0.04127312711297872,0.025238962623044547,0.03147447103579673,0.035928405616334,0.009204798133110363,0.011877158881432728,0.023754317762865455,0.059385794407163635,0.05285335702237563,0.022269672902686364,0.04127312711297872,0.02820825234340273,0.03088061309172509,0.04661784860962346,0.07126295328859636,0.07541995889709782,0.0736383850648829,0.053447214966447276,0.010095585049217818,0.05463493085459055,0.011283300937361091,0.07155988226063219,0.04661784860962346,0.017221880378077457,0.013955661685683456,0.03147447103579673,0.03236525795190418,0.011877158881432728,0.045727061693516,0.03295911589597582,0.032662186923940004,0.06591823179195164,0.007126295328859637,0.02197274393065055,0.06532437384788001,0.0011877158881432727,0.07126295328859636,0.054931859826626364,0.010095585049217818,0.03088061309172509,0.03236525795190418,0.055525717770698,0.05641650468680546,0.04157005608501455,0.06710594768009491,0.04038234019687127,0.06502744487584418,0.061464297211414366,0.04424241683333691,0.04038234019687127,0.046914777581659274,0.010095585049217818,0.029395968231546003,0.0005938579440716363,0.06294894207159346,0.024348175706937095,0.023754317762865455,0.03681919253244145,0.053150285994411454,0.013361803741611819,0.07215374020470382,0.024348175706937095,0.030286755147653457,0.02791132337136691,0.06888752151230983,0.05997965235123527,0.055525717770698,0.03681919253244145,0.07126295328859636,0.026426678511187816,0.02791132337136691,0.06413665795973673,0.007126295328859637,0.05404107291051891,0.054931859826626364,0.032662186923940004,0.027317465427295275,0.07245066917673965,0.022566601874722182,0.047805564497766725,0.013064874769576,0.05047792524608909,0.05582264674273382,0.005344721496644727,0.04038234019687127,0.05522878879866218,0.0056416504686805456,0.020785028042507276,0.03088061309172509,0.05849500749105618,0.06443358693177255,0.022566601874722182,0.017221880378077457,0.04810249346980255,0.07452917198099036,0.03117754206376091,0.04157005608501455,0.020191170098435636,0.046320919637587644,0.029099039259510184,0.03503761870022655,0.07096602431656054,0.014846448601790909,0.019597312154364003,0.07096602431656054,0.06977830842841727,0.028505181315438548,0.06235508412752182,0.06502744487584418,0.029692897203581817,0.06948137945638146,0.07423224300895455,0.02464510467897291,0.0700752374004531,0.07393531403691873,0.035928405616334,0.0023754317762865454,0.02137888598657891,0.012174087853468548,0.05255642805033982,0.05582264674273382,0.06621516076398745,0.053150285994411454,0.03147447103579673,0.03622533458836982,0.03978848225279964,0.04988406730201746,0.05522878879866218,0.07037216637248891,0.0700752374004531,0.06532437384788001,0.010689442993289455,0.05819807851902037,0.004157005608501455,0.022269672902686364,0.031771400007832545,0.010095585049217818,0.07452917198099036,0.0668090187080591,0.029395968231546003,0.010986371965325275,0.0017815738322149092,0.015143377573826729,0.005047792524608909,0.005047792524608909,0.04839942244183836,0.005938579440716364,0.06443358693177255,0.03978848225279964,0.068590592540274,0.007126295328859637,0.04810249346980255,0.04483627477740855,0.047805564497766725,0.008017082244967092,0.06354280001566509,0.029692897203581817,0.06710594768009491,0.009798656077182001,0.07423224300895455,0.023754317762865455,0.053447214966447276,0.03117754206376091,0.019003454210292363,0.042460843001122,0.008017082244967092,0.04038234019687127,0.068590592540274,0.05404107291051891,0.006235508412752182,0.025238962623044547,0.005047792524608909,0.05018099627405327,0.016034164489934184,0.03681919253244145,0.020191170098435636,0.0700752374004531,0.06918445048434564,0.058791936463092005,0.06532437384788001,0.023457388790829637,0.054931859826626364,0.0038600766364656363,0.029989826175617636,0.0700752374004531,0.029099039259510184,0.07155988226063219,0.06473051590380836,0.014252590657719274,0.02791132337136691,0.013658732713647637,0.00831401121700291,0.07037216637248891,0.019597312154364003,0.028505181315438548,0.046320919637587644,0.0020785028042507275,0.06948137945638146,0.06740287665213072,0.06354280001566509,0.015440306545862545,0.025238962623044547,0.013658732713647637,0.037116121504477274,0.03444376075615491,0.012471016825504364,0.03978848225279964,0.025832820567116183,0.039194624308728006,0.03563147664429818,0.04127312711297872,0.03474068972819073,0.054931859826626364,0.014846448601790909,0.022269672902686364,0.06977830842841727,0.027020536455259456,0.042460843001122,0.015737235517898365,0.04483627477740855,0.05047792524608909,0.06562130281991582,0.005938579440716364,0.04364855888926527,0.05522878879866218,0.029692897203581817,0.006235508412752182,0.03474068972819073,0.010095585049217818,0.0537441439384831,0.04810249346980255,0.05166564113423237,0.022269672902686364,0.04305470094519363,0.0044539345805372724,0.022269672902686364,0.024348175706937095,0.010986371965325275,0.047211706553695096,0.03444376075615491,0.03295911589597582,0.060573510295306915,0.008907869161074545,0.04661784860962346,0.05522878879866218,0.0038600766364656363,0.03474068972819073]', 'uploads/faces/emp_420_1785143126.jpg'),
(17, 15, '', '', '2026-07-27 14:36:59', '[0.07621704167589999,0.03467128170354666,0.0020922325165933326,0.0002988903595133333,0.05678916830753332,0.0020922325165933326,0.06276697549779998,0.06665255017147331,0.010760052942479998,0.0038855746736733325,0.05649027794801999,0.06067474298120665,0.03497017206305999,0.01673786013274666,0.034372391344033325,0.029590145591819994,0.018830092649339998,0.06605476945244665,0.06575587909293332,0.05977807190266665,0.050811361117266654,0.07472258987833331,0.053800264712399994,0.06515809837390665,0.017933421570799996,0.011656724021019998,0.03526906242257333,0.06994034412611998,0.03556795278208666,0.01374895653761333,0.07382591879979332,0.04154575997235332,0.020623434806419993,0.04931690931969999,0.06276697549779998,0.04274132141040666,0.039453527455759994,0.04005130817478666,0.07382591879979332,0.046626896084079994,0.0011955614380533331,0.060973633340719986,0.015841189054206666,0.039154637096246656,0.026003461277659994,0.013450066178099998,0.07412480915930665,0.05499582615045332,0.04333910212943332,0.060973633340719986,0.04722467680310666,0.07382591879979332,0.024210119120579993,0.06127252370023332,0.039453527455759994,0.058881400824126656,0.017036750492259996,0.07532037059735999,0.05499582615045332,0.03586684314159999,0.01673786013274666,0.013450066178099998,0.014645627616153331,0.041246869612839986,0.014047846897126664,0.04333910212943332,0.018531202289826663,0.05200692255531999,0.02002565408739333,0.07382591879979332,0.05977807190266665,0.055593606869479985,0.05021358039823999,0.008070039706859999,0.018531202289826663,0.008070039706859999,0.05499582615045332,0.005380026471239999,0.007173368628319998,0.005081136111726665,0.03526906242257333,0.025405680558633327,0.024509009480093328,0.008667820425886664,0.050811361117266654,0.024807899839606663,0.05947918154315332,0.022117886603986663,0.04304021176991999,0.04064908889381332,0.049018018960186656,0.07472258987833331,0.014645627616153331,0.04035019853429999,0.005678916830753332,0.018531202289826663,0.046925786443593326,0.053202483993373316,0.03407350098451999,0.04543133464602665,0.06306586585731332,0.0020922325165933326,0.053202483993373316,0.060375852621693316,0.07263035736173998,0.017933421570799996,0.04931690931969999,0.025106790199119995,0.058582510464613324,0.06187030441925999,0.007173368628319998,0.06216919477877332,0.06366364657634,0.07442369951881998,0.058582510464613324,0.039453527455759994,0.012254504740046664,0.05918029118363999,0.04184465033186666,0.025106790199119995,0.04453466356748666,0.03198126846792666,0.04752356716261999,0.038855746736733324,0.008966710785399998,0.021221215525446663,0.06844589232855332,0.033774610625006654,0.05260470327434665,0.02391122876106666,0.022416776963499994,0.043637992488946654,0.050811361117266654,0.0026900132356199994,0.06276697549779998,0.04483355392699999,0.0017933421570799995,0.044235773207973325,0.06575587909293332,0.058881400824126656,0.014645627616153331,0.015841189054206666,0.07442369951881998,0.01912898300885333,0.02032454444690666,0.04154575997235332,0.07591815131638666,0.0002988903595133333,0.06246808513828665,0.029590145591819994,0.026601241996686658,0.03736129493916666,0.030187926310846658,0,0.032280158827439995,0.03347572026549332,0.06784811160952664,0.05977807190266665,0.07621704167589999,0.03497017206305999,0.02032454444690666,0.058283620105099986,0.06515809837390665,0.014047846897126664,0.02391122876106666,0.03078570702987333,0.04752356716261999,0.03646462386062666,0.03407350098451999,0.011058943301993331,0.024807899839606663,0.055593606869479985,0.027796803434739992,0.030187926310846658,0.019726763727879997,0.05230581291483332,0.06515809837390665,0.06994034412611998,0.015243408335179997,0.06306586585731332,0.005380026471239999,0.012553395099559998,0.02032454444690666,0.05170803219580666,0.07322813808076666,0.07442369951881998,0.017036750492259996,0.01374895653761333,0.044235773207973325,0.027796803434739992,0.048420238241159985,0.05529471650996665,0.005380026471239999,0.026003461277659994,0.021818996244473327,0.07621704167589999,0.021520105884959995,0.044235773207973325,0.04961579967921333,0.022117886603986663,0.008070039706859999,0.023014557682526658,0.07472258987833331,0.03257904918695333,0.055593606869479985,0.07621704167589999,0.006276697549779999,0.06964145376660665,0.018830092649339998,0.039752417815273326,0.06964145376660665,0.05260470327434665,0.07173368628319998,0.05469693579093999,0,0.004483355392699999,0.006276697549779999,0.05977807190266665,0.046328005724566655,0.006276697549779999,0.04483355392699999,0.03108459738938666,0.06456031765487999,0.028992364872793323,0.030486816670359993,0.07412480915930665,0.04064908889381332,0.03407350098451999,0,0.07143479592368665,0.05051247075775332,0.054099155071913325,0.01823231193031333,0.045730225005539984,0.014944517975666663,0.06515809837390665,0.06545698873341999,0.06665255017147331,0.04483355392699999,0.026601241996686658,0.06456031765487999,0.03825796601770666,0.052903593633859984,0.0032877939546466658,0.03108459738938666,0.03526906242257333,0.055593606869479985,0.07203257664271331,0.037660185298679996,0.028992364872793323,0.02839458415376666,0.05469693579093999,0.03795907565819333,0.04483355392699999,0.04812134788164665,0.07083701520465999,0.0020922325165933326,0.041246869612839986,0.0065755879092933315,0.021818996244473327,0.04543133464602665,0.04513244428651332,0.03257904918695333,0.0008966710785399997,0.07173368628319998,0.03586684314159999,0.01195561438053333,0.027796803434739992,0.05200692255531999,0.04931690931969999,0.055593606869479985,0.04064908889381332,0.039453527455759994,0.005380026471239999,0.041246869612839986,0.039154637096246656,0.05469693579093999,0.07382591879979332,0.014047846897126664,0.0014944517975666663,0.03168237810841333,0.017933421570799996,0.07203257664271331,0.039453527455759994,0.07203257664271331,0.010760052942479998,0.022416776963499994,0.04333910212943332,0,0.02391122876106666,0.024210119120579993,0.026900132356199997,0.042143540691379995,0.01195561438053333,0.014047846897126664,0.0047822457522133325,0.03855685637721999,0.0020922325165933326,0.06515809837390665,0.03407350098451999,0.06665255017147331,0.0038855746736733325,0.02361233840155333,0.03736129493916666,0.039453527455759994,0.05499582615045332,0.03257904918695333,0.02361233840155333,0.06216919477877332,0.032280158827439995,0.046029115365053316,0.02032454444690666,0.04871912860067332,0.05738694902655998,0.05977807190266665,0.0011955614380533331,0.06187030441925999,0.07621704167589999,0.015841189054206666,0.05021358039823999,0.04064908889381332,0.03168237810841333,0.05738694902655998,0.05708805866704665,0.003586684314159999,0.029889035951333326,0.04453466356748666,0.04154575997235332,0.004483355392699999,0.06874478268806665,0.024807899839606663,0.06396253693585333,0.06366364657634,0.05947918154315332,0.005081136111726665,0.05439804543142666,0.07113590556417332,0.026601241996686658,0.009265601144913331,0.011058943301993331,0.060076962262179984,0.03736129493916666,0.022117886603986663,0.010461162582966666,0.05439804543142666,0.012254504740046664,0.005977807190266665,0.07083701520465999,0.029291255232306662,0.04722467680310666,0.07502148023784665,0.060076962262179984,0.04722467680310666,0.025106790199119995,0.04483355392699999,0.05798472974558665,0.06784811160952664,0.053202483993373316,0.033774610625006654,0.03556795278208666,0.053800264712399994,0.027796803434739992,0.04184465033186666,0.07591815131638666,0.028095693794253328,0.01643896977323333,0.06157141405974666,0.06545698873341999,0.05260470327434665,0.022416776963499994,0.04513244428651332,0.04931690931969999,0.051409141836293325,0.024807899839606663,0.05678916830753332,0.03168237810841333,0.011058943301993331,0.06216919477877332,0.03556795278208666,0.027796803434739992,0.05708805866704665,0.022416776963499994,0.046029115365053316,0.020623434806419993,0.05170803219580666,0.03198126846792666,0.024509009480093328,0.026003461277659994,0.043936882848459986,0.07023923448563331,0.01673786013274666,0.06904367304757998,0.01733564085177333,0.021818996244473327,0.0008966710785399997,0.03138348774889999,0.01195561438053333,0.05798472974558665,0.06067474298120665,0.021818996244473327,0.07143479592368665,0.03586684314159999,0.05918029118363999,0.03317682990597999,0.026601241996686658,0.036763514220139994,0.06306586585731332,0.0011955614380533331,0.06336475621682666,0.03257904918695333,0.03138348774889999,0.03795907565819333,0.01016227222345333,0.018830092649339998,0.039154637096246656,0.041246869612839986,0.017933421570799996,0.03287793954646666,0.04961579967921333,0.06426142729536666,0.05947918154315332,0.04184465033186666,0.029291255232306662,0.05947918154315332,0.03138348774889999,0.042143540691379995,0.027796803434739992,0.0074722589878333315,0.05051247075775332,0.021221215525446663,0.04483355392699999,0.005678916830753332,0.03795907565819333,0.049018018960186656,0.05051247075775332,0.03078570702987333,0.04005130817478666,0.009863381863939999,0.06994034412611998,0.04005130817478666,0.06515809837390665,0.05529471650996665,0.029590145591819994,0.030486816670359993,0.0047822457522133325,0.006276697549779999,0.03138348774889999,0.037062404579653326,0.023313448042039997,0.011058943301993331,0.005678916830753332,0.03168237810841333,0.008667820425886664,0.009265601144913331,0.058283620105099986,0.07591815131638666,0.0011955614380533331,0.056191387588506655,0.04035019853429999,0.06545698873341999,0.03347572026549332,0.06157141405974666,0.0017933421570799995,0.046328005724566655,0.029889035951333326,0.07292924772125332,0.004184465033186665,0.051409141836293325,0.009863381863939999,0.029291255232306662,0.07053812484514665,0.06246808513828665,0.037660185298679996,0.07352702844027999,0.06336475621682666,0.028992364872793323,0.026601241996686658,0.06814700196903999,0.048420238241159985,0.03108459738938666,0.053202483993373316,0.06276697549779998,0.03257904918695333,0.06994034412611998,0.05021358039823999,0.045730225005539984,0.03497017206305999,0.02719902271571333,0.013151175818586663,0.011656724021019998,0.03257904918695333,0.021221215525446663,0.022117886603986663,0.027796803434739992,0.032280158827439995,0.01195561438053333,0.043936882848459986,0.048420238241159985,0.07591815131638666,0.043936882848459986,0.06306586585731332,0.0038855746736733325,0.029590145591819994,0.030187926310846658,0.06485920801439332,0.07561926095687332,0.04035019853429999,0.026601241996686658,0.024509009480093328,0.048420238241159985,0.05170803219580666,0.07292924772125332]', 'uploads/faces/emp_234_1785143219.jpg');
INSERT INTO `face_embeddings` (`id`, `employee_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(18, 16, '', '', '2026-07-27 15:15:10', '[0.08025435463056124,0,0.003147229593355343,0.005035567349368548,0.02360422195016507,0.010700580617408166,0.02328949899082954,0.0789954627932191,0.07647767911853483,0.027695620421527015,0.06451820666378454,0.07868073983388356,0.02863978929953362,0.05192928829036316,0.05476179492438297,0.0339900796082377,0.05885319339574491,0.07521878728119269,0.021086438275480797,0.06577709850112666,0.04437593726631033,0.018253931641460986,0.003147229593355343,0.06577709850112666,0.03682258624225751,0.021086438275480797,0.05570596380238956,0.06262986890777132,0.006609182146046219,0.04122870767295499,0.03461952552690877,0.05476179492438297,0.05885319339574491,0.021715884194151863,0.011330026536079233,0.02863978929953362,0.04563482910365247,0.05350290308704082,0.053817626046376354,0.06294459186710685,0.0110153035767437,0.06640654441979772,0.010071134698737097,0.0541323490057119,0.07490406432185716,0.006294459186710686,0.0040913984713619456,0.04374649134763926,0.014162533170099044,0.03241646481156003,0.05350290308704082,0.05790902451773831,0.04374649134763926,0.07742184799654143,0.006294459186710686,0.043117045428968194,0.013847810210763508,0.04122870767295499,0.06640654441979772,0.02863978929953362,0.021086438275480797,0.04185815359162606,0.005035567349368548,0.022974776031494,0.068924328094482,0.027695620421527015,0.08025435463056124,0.07112738880983074,0.04280232246963266,0.011330026536079233,0.04280232246963266,0.03682258624225751,0.05318818012770529,0.07018321993182414,0.007553351024052823,0.011330026536079233,0.02863978929953362,0.026436728584184877,0.05633540972106063,0.0560206867617251,0.07018321993182414,0.05822374747707384,0.034304802567573234,0,0.014162533170099044,0.023918944909500604,0.07490406432185716,0.049096781656343354,0.07679240207787037,0.07395989544385055,0.02675145154352041,0.02423366786883614,0.07773657095587697,0.012588918373421371,0.012274195414085838,0.042172876550961594,0.0789954627932191,0.06829488217581094,0.018568654600796524,0.016680316844783317,0.0541323490057119,0.002517783674684274,0.06231514594843579,0.03776675512026411,0.0056650132680396165,0.021086438275480797,0.05822374747707384,0.07144211176916627,0.06137097707042918,0.018253931641460986,0.0006294459186710685,0.0037766755120264115,0.02423366786883614,0.012274195414085838,0.038396201038935186,0.032731187770895565,0.011959472454750302,0.07018321993182414,0.021086438275480797,0.06955377401315307,0.03084285001488236,0.06483292962312007,0.04783788981900121,0.050670396453021016,0.043431768388303725,0.04657899798165908,0.06168570002976472,0.007553351024052823,0.005035567349368548,0.026122005624849346,0.043117045428968194,0.06357403778577791,0.0028325066340198083,0.013533087251427975,0.003461952552690877,0.0679801592164754,0.005350290308704083,0.022974776031494,0.0028325066340198083,0.068924328094482,0.07458934136252163,0.011959472454750302,0.04752316685966568,0.029269235218204685,0.034304802567573234,0.07175683472850182,0.06168570002976472,0.0040913984713619456,0.053817626046376354,0.00031472295933553427,0.07521878728119269,0.0056650132680396165,0.007238628064717288,0.06388876074511346,0.052873457168369754,0.017309762763454386,0.02706617450285595,0.060741531151758114,0.04815261277833674,0.008497519902059426,0.00881224286139496,0.03713730920159305,0.0742746184031861,0.05727957859906724,0.021086438275480797,0.020142269397474193,0.07490406432185716,0.049411504615678885,0.07333044952517949,0.0789954627932191,0.02140116123481633,0.01699503980411885,0.0037766755120264115,0.010071134698737097,0.0450053831849814,0.012274195414085838,0.05696485563973171,0.02863978929953362,0.05570596380238956,0.042487599510297125,0.016365593885447782,0.07458934136252163,0.0550765178837185,0.02801034338086255,0.02360422195016507,0.04783788981900121,0.07301572656584394,0.014791979088770111,0.002517783674684274,0.040913984713619456,0.03084285001488236,0.03713730920159305,0.011959472454750302,0.032101741852224495,0.04469066022564587,0.07993963167122571,0.06609182146046219,0.05948263931441598,0.07144211176916627,0.02140116123481633,0.04783788981900121,0.020142269397474193,0.019512823478803124,0.07112738880983074,0.01762448572278992,0.07931018575255463,0.04878205869700781,0.050670396453021016,0.0028325066340198083,0.020456992356809728,0.0056650132680396165,0.014162533170099044,0.07490406432185716,0.04122870767295499,0.05350290308704082,0.04563482910365247,0.05727957859906724,0.05790902451773831,0.025492559706178274,0.0037766755120264115,0.034304802567573234,0.02328949899082954,0.05727957859906724,0.022974776031494,0.020142269397474193,0.010071134698737097,0.06955377401315307,0.017309762763454386,0.05444707196504743,0.06546237554179113,0.03556369440491537,0.005350290308704083,0.017309762763454386,0.012588918373421371,0.007238628064717288,0.032731187770895565,0.0006294459186710685,0.010700580617408166,0.0220306071534874,0.03902564695760625,0.043117045428968194,0.03776675512026411,0.006294459186710686,0.018883377560132055,0.019512823478803124,0.01164474949541477,0.0006294459186710685,0.06829488217581094,0.012274195414085838,0.02706617450285595,0.04752316685966568,0.059167916355080445,0.039969815835612856,0.030528127055546823,0.024863113787507208,0.0037766755120264115,0.02580728266551381,0.03871092399827072,0.049726227575014416,0.05161456533102762,0.012274195414085838,0.022345330112822935,0.06955377401315307,0.006609182146046219,0.03461952552690877,0.00031472295933553427,0.061056254111093645,0.03587841736425091,0.0220306071534874,0.006923905105381754,0.05318818012770529,0.05790902451773831,0.034304802567573234,0.0028325066340198083,0.045949552062988,0.0450053831849814,0.04563482910365247,0.04878205869700781,0.05350290308704082,0.061056254111093645,0.0541323490057119,0.016365593885447782,0.049096781656343354,0.011959472454750302,0.038396201038935186,0,0.005979736227375151,0.0670359903384688,0.03524897144557984,0.04563482910365247,0.05539124084305403,0.0771071250372059,0.012588918373421371,0.03682258624225751,0.031472295933553426,0.019198100519467593,0.07364517248451502,0.06829488217581094,0.0056650132680396165,0.056650132680396176,0.03934036991694178,0.049726227575014416,0.015736147966776713,0.02580728266551381,0.07679240207787037,0.022974776031494,0.05318818012770529,0.06388876074511346,0.022660053072158466,0.07458934136252163,0.017939208682125455,0.02580728266551381,0.0450053831849814,0.03776675512026411,0.011330026536079233,0.051299842371692085,0.05161456533102762,0.03556369440491537,0.024863113787507208,0.06262986890777132,0.0450053831849814,0.07144211176916627,0.034304802567573234,0.04185815359162606,0.0028325066340198083,0.03241646481156003,0.04185815359162606,0.0440612143069748,0.033045910730231096,0.053817626046376354,0.07175683472850182,0.03650786328292197,0.0761629561591993,0.020771715316145266,0.01164474949541477,0.0220306071534874,0.06640654441979772,0.006294459186710686,0.0006294459186710685,0.013218364292092439,0.05570596380238956,0.028954512258869154,0.049411504615678885,0.024548390828171677,0.0789954627932191,0.04028453879494839,0.020771715316145266,0.06609182146046219,0.01762448572278992,0.011959472454750302,0.07333044952517949,0.01699503980411885,0.060112085233087045,0.039969815835612856,0.02328949899082954,0.007553351024052823,0.0742746184031861,0.03745203216092858,0.023918944909500604,0.04437593726631033,0.03084285001488236,0.06451820666378454,0.06137097707042918,0.03461952552690877,0.06609182146046219,0.056650132680396176,0.016365593885447782,0.04752316685966568,0.0651476525824556,0.06483292962312007,0.07395989544385055,0.040913984713619456,0.04374649134763926,0.033360633689566634,0.034304802567573234,0.007868073983388357,0.026436728584184877,0.05476179492438297,0.021086438275480797,0.006294459186710686,0.051299842371692085,0.018253931641460986,0.007238628064717288,0.04154343063229053,0.01699503980411885,0.05004095053434995,0.02140116123481633,0.05444707196504743,0.013218364292092439,0.029583958177540223,0.04437593726631033,0.010071134698737097,0.03461952552690877,0.0761629561591993,0.06137097707042918,0.032731187770895565,0.042172876550961594,0.016050870926112248,0.04469066022564587,0.011959472454750302,0.033675356648902165,0.002517783674684274,0.020456992356809728,0.07395989544385055,0.05224401124969869,0.043117045428968194,0.06042680819242258,0.07490406432185716,0.052873457168369754,0.027695620421527015,0.007868073983388357,0.04815261277833674,0.0009441688780066029,0.0651476525824556,0.031157572974217895,0.02863978929953362,0.05161456533102762,0.05570596380238956,0.00440612143069748,0.0780512939152125,0.04720844390033014,0.019512823478803124,0.025492559706178274,0.031157572974217895,0.0220306071534874,0.006294459186710686,0.043117045428968194,0.06923905105381754,0.07679240207787037,0.001258891837342137,0.012903641332756906,0.03241646481156003,0.07081266585049521,0.029898681136875757,0.0056650132680396165,0.012903641332756906,0.022660053072158466,0.012274195414085838,0.03650786328292197,0.06388876074511346,0.0056650132680396165,0.03745203216092858,0.02360422195016507,0.06640654441979772,0.038396201038935186,0.02140116123481633,0.022345330112822935,0.04783788981900121,0.01762448572278992,0.06640654441979772,0.04846733573767228,0.07679240207787037,0.005035567349368548,0.05885319339574491,0.016680316844783317,0.08025435463056124,0.010700580617408166,0.06388876074511346,0.009441688780066028,0.012274195414085838,0.02140116123481633,0.07175683472850182,0.03178701889288896,0.06923905105381754,0.07962490871189018,0.02328949899082954,0.07301572656584394,0.05727957859906724,0.03524897144557984,0.010385857658072633,0.04563482910365247,0.02863978929953362,0.052873457168369754,0.024863113787507208,0.033360633689566634,0.012588918373421371,0.0056650132680396165,0.04563482910365247,0.005979736227375151,0.018568654600796524,0.060741531151758114,0.04783788981900121,0.02423366786883614,0.043431768388303725,0.0670359903384688,0.05004095053434995,0,0.05161456533102762,0.0560206867617251,0.018253931641460986,0.03965509287627732,0.0015736147966776714,0.029898681136875757,0.04532010614431693,0.0349342484862443,0.056650132680396176,0.02675145154352041,0.04626427502232353,0.068924328094482,0.06420348370444899,0.033360633689566634,0.03556369440491537,0.07018321993182414,0.04469066022564587,0.06483292962312007,0.07490406432185716,0.03461952552690877,0.05696485563973171,0.017939208682125455,0.05098511941235655,0.007553351024052823,0.04028453879494839]', 'uploads/faces/emp_2325_1785145510.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `fees_invoices`
--

CREATE TABLE `fees_invoices` (
  `id` int(11) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `title` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fees_invoices`
--

INSERT INTO `fees_invoices` (`id`, `student_id`, `title`, `amount`, `due_date`, `status`, `paid_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', 'Term 1 Tuition Fee', 45000.00, '2026-05-15', 'Paid', '2026-05-10 06:00:00', '2026-06-14 16:09:05', NULL, NULL),
(2, 'stud-arjun', 'Term 2 Tuition Fee', 30500.00, '2026-06-30', 'Unpaid', NULL, '2026-06-14 16:09:05', NULL, NULL),
(3, 'stud-ananya', 'Term 1 Tuition Fee', 40000.00, '2026-05-15', 'Paid', '2026-05-10 06:02:00', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoices`
--

CREATE TABLE `fee_invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','paid','partially_paid','void') NOT NULL DEFAULT 'unpaid',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoices`
--

INSERT INTO `fee_invoices` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `invoice_number`, `title`, `description`, `amount`, `due_date`, `status`, `paid_amount`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'INV-2026-1-01', 'Term 1 Tuition & Therapy Fee', 'Tuition fees, speech therapy services, and resource room access charges.', 15000.00, '2026-06-06', 'paid', 15000.00, '2026-06-04 08:47:31', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 1, 'INV-2026-1-02', 'Monthly Transport & Bus Fee', 'Bus transport pick-and-drop service charges for this month.', 2500.00, '2026-07-01', 'paid', 2500.00, '2026-06-22 13:57:07', '2026-06-16 06:47:31', '2026-06-22 08:27:07'),
(3, 1, 1, 1, 2, 'INV-2026-2-01', 'Term 1 Tuition & Therapy Fee', 'Tuition fees, speech therapy services, and resource room access charges.', 15000.00, '2026-06-06', 'paid', 15000.00, '2026-06-04 08:47:31', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(4, 1, 1, 1, 2, 'INV-2026-2-02', 'Monthly Transport & Bus Fee', 'Bus transport pick-and-drop service charges for this month.', 2500.00, '2026-07-01', 'paid', 2500.00, '2026-06-16 09:55:54', '2026-06-16 06:47:31', '2026-06-16 07:55:54');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_ref` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_source` varchar(20) NOT NULL DEFAULT 'staff' COMMENT 'staff or parent_online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `tenant_id`, `school_id`, `branch_id`, `invoice_id`, `amount`, `payment_method`, `payment_ref`, `paid_at`, `created_at`, `payment_source`) VALUES
(1, 1, 1, 1, 1, 15000.00, 'Online', 'PAY-REF-TXN88910', '2026-06-16 06:47:31', '2026-06-16 06:47:31', 'staff'),
(2, 1, 1, 1, 3, 15000.00, 'Online', 'PAY-REF-TXN88910', '2026-06-16 06:47:31', '2026-06-16 06:47:31', 'staff'),
(3, 1, 1, 1, 4, 2500.00, 'Cash', 'Manual-D93D2166', '2026-06-16 04:25:54', '2026-06-16 07:55:54', 'staff'),
(4, 1, 1, 1, 2, 2500.00, 'Cash', 'Manual-DD1DF685', '2026-06-22 08:27:07', '2026-06-22 08:27:07', 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `parent_id`, `name`, `created_at`) VALUES
(1, NULL, 'test', '2026-05-21 07:19:46'),
(2, NULL, 'test', '2026-05-21 08:37:38'),
(3, 2, 'test 2', '2026-05-21 09:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `folder_staff`
--

CREATE TABLE `folder_staff` (
  `folder_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folder_staff`
--

INSERT INTO `folder_staff` (`folder_id`, `staff_id`) VALUES
(1, 1),
(2, 1),
(3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `game_sessions`
--

CREATE TABLE `game_sessions` (
  `id` bigint(20) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `game_key` varchar(60) NOT NULL,
  `score` int(11) NOT NULL,
  `time_spent_seconds` int(11) NOT NULL,
  `total_attempts` int(11) NOT NULL,
  `wrong_answers_log` text DEFAULT NULL,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `game_sessions`
--

INSERT INTO `game_sessions` (`id`, `student_id`, `game_key`, `score`, `time_spent_seconds`, `total_attempts`, `wrong_answers_log`, `played_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', 'money-counting', 90, 150, 10, '{\"wrong_coins\": 2}', '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL),
(2, 'stud-arjun', 'safe-vs-unsafe', 100, 120, 5, '[]', '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL),
(3, 'stud-ananya', 'safety-signs', 80, 200, 15, '{\"wrong_sign_yield\": 3}', '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gps_logs`
--

CREATE TABLE `gps_logs` (
  `id` bigint(20) NOT NULL,
  `route_id` int(11) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `speed` double NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gps_logs`
--

INSERT INTO `gps_logs` (`id`, `route_id`, `latitude`, `longitude`, `speed`, `logged_at`) VALUES
(1, 1, 18.5308, 73.8474, 32.5, '2026-06-14 16:09:05'),
(2, 1, 18.5312, 73.848, 35, '2026-06-14 16:09:05');

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `relationship` varchar(100) NOT NULL DEFAULT 'parent',
  `gender` enum('male','female','other') DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `alt_phone` varchar(30) DEFAULT NULL,
  `occupation` varchar(191) DEFAULT NULL,
  `aadhar` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`id`, `tenant_id`, `user_id`, `name`, `relationship`, `gender`, `email`, `phone`, `alt_phone`, `occupation`, `aadhar`, `address`, `city`, `state`, `pincode`, `photo`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 'Rajesh Kumar', 'Father', 'male', 'parent@psnf.edu', '+91-9876543210', NULL, 'Software Engineer', '1234-5678-9012', 'Apt 405, Pearl Heights, Mumbai, India', NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-06-16 03:15:58', NULL, NULL),
(4, 1, NULL, 'Suresh Patel', 'Father', NULL, NULL, '+91-9876543201', NULL, NULL, NULL, 'Flat 101, Nilgiri Heights, Mumbai', NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(5, 1, NULL, 'Kirit Shah', 'Father', NULL, NULL, '+91-9876543202', NULL, NULL, NULL, 'Apt 302, Shivalik Villa, Mumbai', NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(6, 1, NULL, 'Meena Joshi', 'Mother', NULL, NULL, '+91-9876543203', NULL, NULL, NULL, 'Building 5B, Green Valley, Mumbai', NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guardian_student`
--

CREATE TABLE `guardian_student` (
  `guardian_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `can_pickup` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guardian_student`
--

INSERT INTO `guardian_student` (`guardian_id`, `student_id`, `is_primary`, `can_pickup`, `created_at`) VALUES
(1, 1, 1, 1, '2026-06-18 10:31:49'),
(1, 2, 1, 1, '2026-06-18 10:31:49'),
(1, 31, 1, 1, '2026-07-24 09:51:25'),
(4, 7, 1, 1, '2026-07-13 07:25:11'),
(5, 8, 1, 1, '2026-07-13 07:25:11'),
(6, 9, 1, 1, '2026-07-13 07:25:11');

-- --------------------------------------------------------

--
-- Table structure for table `homeworks`
--

CREATE TABLE `homeworks` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `class` varchar(50) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `due_date` date NOT NULL,
  `assigned_at` date NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homeworks`
--

INSERT INTO `homeworks` (`id`, `tenant_id`, `school_id`, `branch_id`, `class`, `section`, `subject`, `title`, `description`, `file_path`, `due_date`, `assigned_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Class A', 'S1', 'Speech Therapy', 'Daily Phonics Practice (Visual Cards)', 'Spend 10 minutes practicing the phonics sheets attached. Record a video of the exercise.', '/uploads/homework/worksheet.pdf', '2026-06-18', '2026-06-16', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 'Class A', 'S1', 'Life Skills', 'Color Matching & Vocabulary', 'Match the visual items to their colors using the worksheet. Practice naming each item out loud.', '/uploads/homework/worksheet.pdf', '2026-06-21', '2026-06-16', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 'Class B', 'S2', 'Music Therapy', 'Daily Phonics Practice (Rhythm Clapping)', 'Spend 10 minutes practicing the phonics sheets attached. Record a video of the exercise.', '/uploads/homework/worksheet.pdf', '2026-06-18', '2026-06-16', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(4, 1, 1, 1, 'Class B', 'S2', 'Basic Literacy', 'Color Matching & Vocabulary', 'Match the visual items to their colors using the worksheet. Practice naming each item out loud.', '/uploads/homework/worksheet.pdf', '2026-06-21', '2026-06-16', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `iep_progress`
--

CREATE TABLE `iep_progress` (
  `id` int(11) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `milestone` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `iep_progress`
--

INSERT INTO `iep_progress` (`id`, `student_id`, `milestone`, `rating`, `comments`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', 'Cognitive Skills - Money Counting', 4, 'Arjun can consistently identify Indian currency up to Ôé╣100 and calculate correct totals.', '2026-06-14 16:09:05', NULL, NULL),
(2, 'stud-arjun', 'Social Integration - Comm Center', 3, 'Improving. Starting to use AAC device to initiate peer conversations.', '2026-06-14 16:09:05', NULL, NULL),
(3, 'stud-arjun', 'Fine Motor Skills - Writing', 2, 'Requires steady physical support. Working on grip stabilizers.', '2026-06-14 16:09:05', NULL, NULL),
(4, 'stud-ananya', 'Focus & Attention - Class tasking', 3, 'Sustains focus for 15 minutes before requiring sensory stretch breaks.', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `student_id`, `start_date`, `end_date`, `leave_type`, `reason`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', '2026-06-10', '2026-06-10', 'Medical Leave', 'Dental extraction appointment', 'Approved', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_incidents`
--

CREATE TABLE `medical_incidents` (
  `id` int(11) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `incident_type` varchar(90) NOT NULL,
  `description` text NOT NULL,
  `action_taken` text NOT NULL,
  `logged_by` int(11) NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_incidents`
--

INSERT INTO `medical_incidents` (`id`, `student_id`, `incident_type`, `description`, `action_taken`, `logged_by`, `logged_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', 'Sensory Overload', 'Arjun experienced distress during music class decibel spikes.', 'Escorted to Quiet Room, applied sensory earmuffs, did calming breathing exercises. Calm after 10 minutes.', 1, '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medication_administration`
--

CREATE TABLE `medication_administration` (
  `id` int(11) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `medication_name` varchar(120) NOT NULL,
  `dosage` varchar(60) NOT NULL,
  `administered_by` int(11) NOT NULL,
  `witnessed_by` int(11) DEFAULT NULL,
  `administered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medication_administration`
--

INSERT INTO `medication_administration` (`id`, `student_id`, `medication_name`, `dosage`, `administered_by`, `witnessed_by`, `administered_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', 'Methylphenidate', '10mg Tablet', 1, 3, '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL DEFAULT 1,
  `ran_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`, `ran_at`) VALUES
(1, '000_create_rate_limits_table', 1, '2026-06-16 06:39:34'),
(2, '001_create_plans_table', 1, '2026-06-16 06:39:34'),
(3, '002_create_tenants_table', 1, '2026-06-16 06:39:34'),
(4, '003_create_schools_table', 2, '2026-06-16 06:42:28'),
(5, '004_create_branches_table', 2, '2026-06-16 06:42:28'),
(6, '005_create_users_table', 2, '2026-06-16 06:42:28'),
(7, '006_create_roles_table', 2, '2026-06-16 06:42:28'),
(8, '007_create_permissions_table', 2, '2026-06-16 06:42:28'),
(9, '008_create_role_permissions_table', 2, '2026-06-16 06:42:28'),
(10, '009_create_user_roles_table', 2, '2026-06-16 06:42:28'),
(11, '010_create_activity_logs_table', 2, '2026-06-16 06:42:28'),
(12, '011_create_sessions_table', 2, '2026-06-16 06:42:28'),
(13, '012_create_password_resets_table', 2, '2026-06-16 06:42:28'),
(14, '013_create_otp_codes_table', 2, '2026-06-16 06:42:29'),
(15, '014_create_subscriptions_table', 2, '2026-06-16 06:42:29'),
(16, '015_create_students_table', 3, '2026-06-16 06:43:12'),
(17, '016_create_student_medical_table', 3, '2026-06-16 06:43:12'),
(18, '017_create_emergency_contacts_table', 3, '2026-06-16 06:43:12'),
(19, '018_create_student_documents_table', 3, '2026-06-16 06:43:12'),
(20, '019_create_student_timeline_table', 3, '2026-06-16 06:43:12'),
(21, '020_create_guardians_table', 3, '2026-06-16 06:43:12'),
(22, '021_create_parent_portal_tables', 3, '2026-06-16 06:43:12'),
(23, '022_create_leave_applications_table', 4, '2026-06-18 10:50:32'),
(24, '023_add_medical_certificate_to_attendance_table', 5, '2026-06-18 11:03:08'),
(25, '024_add_teacher_scheduling_and_apps_tables', 6, '2026-06-20 06:50:46'),
(26, '025_create_scholarships_table', 7, '2026-06-20 08:46:37'),
(27, '026_create_student_report_cards', 8, '2026-07-01 06:40:12'),
(28, '026_create_student_report_cards', 8, '2026-07-01 06:40:12'),
(29, '027_create_report_card_settings', 8, '2026-07-01 06:40:12'),
(30, '027_create_report_card_settings', 8, '2026-07-01 06:40:12'),
(31, '028_add_fields_config_to_report_card_settings', 8, '2026-07-01 06:40:12'),
(32, '030_add_checkout_to_teacher_attendance', 9, '2026-07-13 06:47:10'),
(33, '029_add_arrival_times_and_checkout_columns', 10, '2026-07-13 07:25:10'),
(34, '031_create_online_enrollments_table', 11, '2026-07-27 09:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `school_id` varchar(36) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `school_id`, `title`, `content`, `category`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'sch-greenwood', 'Summer Vacation Camp Registration Open', 'We are organizing a special needs adaptive summer camp from July 1st. Please register your child via the portal before June 25th.', 'Announcements', '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL),
(2, 'sch-greenwood', 'Mid-Term Progress Review PTM Scheduled', 'The quarterly Parent-Teacher Meeting (PTM) to discuss IEP revisions and progress reports will be held on June 24th.', 'Urgent', '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL),
(3, 'sch-greenwood', 'Monsoon Season Bus Safety Protocols', 'In light of heavy rains, school buses will follow optimized safety routes. Minor delays of 10-15 minutes may occur during dropoff loops.', 'Transport', '2026-06-14 16:09:05', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `online_enrollments`
--

CREATE TABLE `online_enrollments` (
  `id` int(11) NOT NULL,
  `application_code` varchar(50) NOT NULL,
  `student_full_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `student_aadhar` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `student_photo` varchar(255) DEFAULT NULL,
  `student_aadhar_doc` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_phone` varchar(30) DEFAULT NULL,
  `father_aadhar` varchar(20) DEFAULT NULL,
  `father_photo` varchar(255) DEFAULT NULL,
  `father_aadhar_doc` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_phone` varchar(30) DEFAULT NULL,
  `mother_aadhar` varchar(20) DEFAULT NULL,
  `mother_photo` varchar(255) DEFAULT NULL,
  `mother_aadhar_doc` varchar(255) DEFAULT NULL,
  `pickup_persons_json` longtext DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `identifier` varchar(191) NOT NULL,
  `code` varchar(10) NOT NULL,
  `type` enum('login','email_verify','phone_verify','2fa','password_reset') NOT NULL DEFAULT 'login',
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` varchar(36) NOT NULL,
  `first_name` varchar(90) NOT NULL,
  `last_name` varchar(90) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `first_name`, `last_name`, `phone`, `email`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
('par-rajesh', 'Rajesh', 'Sharma', '+91 98765 01234', 'rajesh.sharma@example.com', '$2y$10$sqGPxsTidqeSFeo7Y6H2ce3KUf20aR5xFDdtJeHrciLBCNOi/92Qi', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parent_student`
--

CREATE TABLE `parent_student` (
  `parent_id` varchar(36) NOT NULL,
  `student_id` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parent_student`
--

INSERT INTO `parent_student` (`parent_id`, `student_id`) VALUES
('par-rajesh', 'stud-ananya'),
('par-rajesh', 'stud-arjun');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `token` varchar(255) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `module` varchar(100) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Login', 'login', 'auth', NULL, '2026-06-16 03:13:12', NULL),
(2, 'Logout', 'logout', 'auth', NULL, '2026-06-16 03:13:12', NULL),
(3, 'Manage sessions', 'manage_sessions', 'auth', NULL, '2026-06-16 03:13:12', NULL),
(4, 'View users', 'view_users', 'users', NULL, '2026-06-16 03:13:12', NULL),
(5, 'Create users', 'create_users', 'users', NULL, '2026-06-16 03:13:12', NULL),
(6, 'Edit users', 'edit_users', 'users', NULL, '2026-06-16 03:13:12', NULL),
(7, 'Delete users', 'delete_users', 'users', NULL, '2026-06-16 03:13:12', NULL),
(8, 'Assign roles', 'assign_roles', 'users', NULL, '2026-06-16 03:13:12', NULL),
(9, 'View roles', 'view_roles', 'roles', NULL, '2026-06-16 03:13:12', NULL),
(10, 'Create roles', 'create_roles', 'roles', NULL, '2026-06-16 03:13:12', NULL),
(11, 'Edit roles', 'edit_roles', 'roles', NULL, '2026-06-16 03:13:12', NULL),
(12, 'Delete roles', 'delete_roles', 'roles', NULL, '2026-06-16 03:13:12', NULL),
(13, 'View permissions', 'view_permissions', 'permissions', NULL, '2026-06-16 03:13:12', NULL),
(14, 'Assign permissions', 'assign_permissions', 'permissions', NULL, '2026-06-16 03:13:12', NULL),
(15, 'View tenants', 'view_tenants', 'tenants', NULL, '2026-06-16 03:13:12', NULL),
(16, 'Create tenants', 'create_tenants', 'tenants', NULL, '2026-06-16 03:13:12', NULL),
(17, 'Edit tenants', 'edit_tenants', 'tenants', NULL, '2026-06-16 03:13:12', NULL),
(18, 'Delete tenants', 'delete_tenants', 'tenants', NULL, '2026-06-16 03:13:12', NULL),
(19, 'View schools', 'view_schools', 'schools', NULL, '2026-06-16 03:13:12', NULL),
(20, 'Create schools', 'create_schools', 'schools', NULL, '2026-06-16 03:13:12', NULL),
(21, 'Edit schools', 'edit_schools', 'schools', NULL, '2026-06-16 03:13:12', NULL),
(22, 'Delete schools', 'delete_schools', 'schools', NULL, '2026-06-16 03:13:12', NULL),
(23, 'View branches', 'view_branches', 'branches', NULL, '2026-06-16 03:13:12', NULL),
(24, 'Create branches', 'create_branches', 'branches', NULL, '2026-06-16 03:13:12', NULL),
(25, 'Edit branches', 'edit_branches', 'branches', NULL, '2026-06-16 03:13:12', NULL),
(26, 'Delete branches', 'delete_branches', 'branches', NULL, '2026-06-16 03:13:12', NULL),
(27, 'View students', 'view_students', 'students', NULL, '2026-06-16 03:13:12', NULL),
(28, 'Create students', 'create_students', 'students', NULL, '2026-06-16 03:13:12', NULL),
(29, 'Edit students', 'edit_students', 'students', NULL, '2026-06-16 03:13:12', NULL),
(30, 'Delete students', 'delete_students', 'students', NULL, '2026-06-16 03:13:12', NULL),
(31, 'Approve admissions', 'approve_admissions', 'students', NULL, '2026-06-16 03:13:12', NULL),
(32, 'View documents', 'view_documents', 'documents', NULL, '2026-06-16 03:13:12', NULL),
(33, 'Upload documents', 'upload_documents', 'documents', NULL, '2026-06-16 03:13:12', NULL),
(34, 'Delete documents', 'delete_documents', 'documents', NULL, '2026-06-16 03:13:12', NULL),
(35, 'Verify documents', 'verify_documents', 'documents', NULL, '2026-06-16 03:13:12', NULL),
(36, 'View reports', 'view_reports', 'reports', NULL, '2026-06-16 03:13:12', NULL),
(37, 'Export reports', 'export_reports', 'reports', NULL, '2026-06-16 03:13:12', NULL),
(38, 'View audit logs', 'view_audit_logs', 'audit', NULL, '2026-06-16 03:13:12', NULL),
(39, 'View settings', 'view_settings', 'settings', NULL, '2026-06-16 03:13:12', NULL),
(40, 'Edit settings', 'edit_settings', 'settings', NULL, '2026-06-16 03:13:12', NULL),
(41, 'Teacher portal', 'teacher_portal', 'portal_access', NULL, '2026-06-21 07:25:35', NULL),
(42, 'Parents portal', 'parents_portal', 'portal_access', NULL, '2026-06-21 07:25:35', NULL),
(43, 'Driver app', 'driver_app', 'portal_access', NULL, '2026-06-21 07:25:35', NULL),
(44, 'Teacher staff app', 'teacher_staff_app', 'portal_access', NULL, '2026-06-21 07:25:35', NULL),
(45, 'See student name', 'see_student_name', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(46, 'See student medical', 'see_student_medical', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(47, 'See student guardian', 'see_student_guardian', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(48, 'Access exams app', 'access_exams_app', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(49, 'Teacher app student details', 'teacher_app_student_details', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(50, 'Teacher app half leave notification', 'teacher_app_half_leave_notification', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(51, 'Teacher app timetables', 'teacher_app_timetables', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(52, 'Staff app student details', 'staff_app_student_details', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(53, 'Staff app half leave notification', 'staff_app_half_leave_notification', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(54, 'Staff app half leave details', 'staff_app_half_leave_details', 'portal_access', NULL, '2026-06-21 07:53:30', NULL),
(55, 'Driver app pickup route', 'driver_app_pickup_route', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(56, 'Driver app drop route', 'driver_app_drop_route', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(57, 'Driver app students onboard', 'driver_app_students_onboard', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(58, 'Driver app safety center', 'driver_app_safety_center', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(59, 'Driver app speed monitor', 'driver_app_speed_monitor', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(60, 'Driver app sos', 'driver_app_sos', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(61, 'Driver app trip logs', 'driver_app_trip_logs', 'driver_app', NULL, '2026-07-01 07:25:19', NULL),
(62, 'Driver app early leave', 'driver_app_early_leave', 'driver_app', NULL, '2026-07-01 07:25:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
  `max_users` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `max_students` int(10) UNSIGNED NOT NULL DEFAULT 500,
  `max_branches` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `slug`, `description`, `price`, `billing_cycle`, `max_users`, `max_students`, `max_branches`, `features`, `is_active`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Starter', 'starter', NULL, 0.00, 'monthly', 10, 100, 1, NULL, 1, 0, '2026-06-16 06:43:12', NULL, NULL),
(2, 'School', 'school', NULL, 2999.00, 'monthly', 50, 500, 3, NULL, 1, 0, '2026-06-16 06:43:12', NULL, NULL),
(3, 'Enterprise', 'enterprise', NULL, 7999.00, 'monthly', 500, 5000, 20, NULL, 1, 0, '2026-06-16 06:43:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `attempts` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `key`, `attempts`, `expires_at`, `created_at`, `updated_at`) VALUES
(58, 'login:ee376459608418e11cb82f846a2cf4e8ac896a87', 1, '2026-06-18 12:48:20', '2026-06-18 07:03:20', '2026-06-18 07:03:20'),
(91, 'login:6e67484e2d3c8b14d5f3ecd51ccaac18e879d4a0', 1, '2026-06-20 12:57:59', '2026-06-20 07:12:59', '2026-06-20 07:12:59'),
(109, 'login:43e957a2b8e3fbbed45da95ea147fb8926f2adca', 2, '2026-07-01 14:02:16', '2026-07-01 08:17:16', '2026-07-01 08:17:34'),
(136, 'route:363baea9cba210afac6d7a556fca596e30c46333', 1, '2026-07-27 14:46:47', '2026-07-27 09:15:47', '2026-07-27 09:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `report_card_settings`
--

CREATE TABLE `report_card_settings` (
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_name` varchar(255) DEFAULT 'Pearl Special Needs Foundation',
  `school_subtitle` varchar(255) DEFAULT 'Center for Special Education & Care',
  `school_address` text DEFAULT NULL,
  `stamp_text` varchar(255) DEFAULT 'Pearl Special Needs Foundation',
  `pdf_font` varchar(50) DEFAULT 'Inter',
  `primary_color` varchar(10) DEFAULT '#0d3827',
  `class_teacher_name` varchar(255) DEFAULT 'Class Teacher',
  `class_teacher_sig` longtext DEFAULT NULL,
  `trustees_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trustees_config`)),
  `fields_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fields_config`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(190) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `folder_id`, `title`, `file_name`, `stored_name`, `mime_type`, `file_size`, `created_at`) VALUES
(1, 1, 'Hinal_Prajapati_521', 'Hinal_Prajapati_521.pdf', '9d12bbc6f2f34425e9a21fe18a52530b49f7.pdf', 'application/pdf', 1425016, '2026-05-21 07:19:52'),
(2, 1, 'Project proposal for psnf', 'Project proposal for psnf.pdf', '8c66d9ddef902f48ade50132c105d2449b3e.pdf', 'application/pdf', 101251, '2026-05-21 07:45:46'),
(3, 1, 'Bheda_Sejal_514', 'Bheda_Sejal_514.jpg', '9354f030696eb6056fec00321f7cff142983.jpg', 'image/jpeg', 1418508, '2026-05-21 07:48:18'),
(4, 1, 'annual video', 'annual video.mp4', '5c4389e9f1fa8ccb358e252e98e57cdaabc6.mp4', 'video/mp4', 33713128, '2026-05-21 08:40:35');

-- --------------------------------------------------------

--
-- Table structure for table `resource_staff`
--

CREATE TABLE `resource_staff` (
  `resource_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resource_staff`
--

INSERT INTO `resource_staff` (`resource_id`, `staff_id`) VALUES
(1, 1),
(2, 2),
(3, 2),
(4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `tenant_id`, `name`, `slug`, `description`, `is_system`, `is_active`, `sort_order`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Super Admin', 'super_admin', 'Full system access', 1, 1, 1, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(2, NULL, 'School Admin', 'school_admin', 'School level administrator', 1, 1, 2, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(3, NULL, 'Manager', 'manager', 'Branch manager', 1, 1, 3, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(4, NULL, 'Teacher', 'teacher', 'Classroom teacher', 1, 1, 4, NULL, NULL, '2026-06-16 03:13:12', '2026-06-20 07:57:58', NULL),
(5, NULL, 'Therapist', 'therapist', 'Therapy specialist', 1, 1, 5, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(6, NULL, 'Staff', 'staff', 'General staff member', 1, 1, 6, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(7, NULL, 'Driver', 'driver', 'Transport driver', 1, 1, 7, NULL, NULL, '2026-06-16 03:13:12', '2026-06-22 06:35:28', NULL),
(8, NULL, 'Parent', 'parent', 'Parent / guardian', 1, 1, 8, NULL, NULL, '2026-06-16 03:13:12', '2026-06-22 06:36:01', NULL),
(9, NULL, 'Student', 'student', 'Student user', 1, 1, 9, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES
(1, 1, '2026-06-16 06:43:12'),
(1, 2, '2026-06-16 06:43:12'),
(1, 3, '2026-06-16 06:43:12'),
(1, 4, '2026-06-16 06:43:12'),
(1, 5, '2026-06-16 06:43:12'),
(1, 6, '2026-06-16 06:43:12'),
(1, 7, '2026-06-16 06:43:12'),
(1, 8, '2026-06-16 06:43:12'),
(1, 9, '2026-06-16 06:43:12'),
(1, 10, '2026-06-16 06:43:12'),
(1, 11, '2026-06-16 06:43:12'),
(1, 12, '2026-06-16 06:43:12'),
(1, 13, '2026-06-16 06:43:12'),
(1, 14, '2026-06-16 06:43:12'),
(1, 15, '2026-06-16 06:43:12'),
(1, 16, '2026-06-16 06:43:12'),
(1, 17, '2026-06-16 06:43:12'),
(1, 18, '2026-06-16 06:43:12'),
(1, 19, '2026-06-16 06:43:12'),
(1, 20, '2026-06-16 06:43:12'),
(1, 21, '2026-06-16 06:43:12'),
(1, 22, '2026-06-16 06:43:12'),
(1, 23, '2026-06-16 06:43:12'),
(1, 24, '2026-06-16 06:43:12'),
(1, 25, '2026-06-16 06:43:12'),
(1, 26, '2026-06-16 06:43:12'),
(1, 27, '2026-06-16 06:43:12'),
(1, 28, '2026-06-16 06:43:12'),
(1, 29, '2026-06-16 06:43:12'),
(1, 30, '2026-06-16 06:43:12'),
(1, 31, '2026-06-16 06:43:12'),
(1, 32, '2026-06-16 06:43:12'),
(1, 33, '2026-06-16 06:43:12'),
(1, 34, '2026-06-16 06:43:12'),
(1, 35, '2026-06-16 06:43:12'),
(1, 36, '2026-06-16 06:43:12'),
(1, 37, '2026-06-16 06:43:12'),
(1, 38, '2026-06-16 06:43:12'),
(1, 39, '2026-06-16 06:43:12'),
(1, 40, '2026-06-16 06:43:12'),
(1, 41, '2026-06-21 07:25:35'),
(1, 42, '2026-06-21 07:25:35'),
(1, 43, '2026-06-21 07:25:36'),
(1, 44, '2026-06-21 07:25:36'),
(1, 45, '2026-06-21 07:53:30'),
(1, 46, '2026-06-21 07:53:31'),
(1, 47, '2026-06-21 07:53:31'),
(1, 48, '2026-06-21 07:53:31'),
(1, 49, '2026-06-21 07:53:31'),
(1, 50, '2026-06-21 07:53:31'),
(1, 51, '2026-06-21 07:53:31'),
(1, 52, '2026-06-21 07:53:31'),
(1, 53, '2026-06-21 07:53:31'),
(1, 54, '2026-06-21 07:53:31'),
(1, 55, '2026-07-01 07:25:20'),
(1, 56, '2026-07-01 07:25:20'),
(1, 57, '2026-07-01 07:25:20'),
(1, 58, '2026-07-01 07:25:20'),
(1, 59, '2026-07-01 07:25:20'),
(1, 60, '2026-07-01 07:25:20'),
(1, 61, '2026-07-01 07:25:20'),
(1, 62, '2026-07-01 07:25:20'),
(4, 1, '2026-06-20 07:57:58'),
(4, 2, '2026-06-20 07:57:58'),
(4, 8, '2026-06-20 07:57:58'),
(4, 35, '2026-06-20 07:57:58'),
(4, 41, '2026-06-21 07:53:31'),
(4, 44, '2026-06-21 07:53:31'),
(4, 45, '2026-06-21 07:53:31'),
(4, 46, '2026-06-21 07:53:31'),
(4, 47, '2026-06-21 07:53:31'),
(4, 48, '2026-06-21 07:53:31'),
(4, 49, '2026-06-21 07:53:31'),
(4, 50, '2026-06-21 07:53:31'),
(4, 51, '2026-06-21 07:53:31'),
(6, 44, '2026-06-21 07:53:31'),
(6, 45, '2026-06-21 07:53:31'),
(6, 46, '2026-06-21 07:53:31'),
(6, 47, '2026-06-21 07:53:31'),
(6, 52, '2026-06-21 07:53:31'),
(6, 53, '2026-06-21 07:53:31'),
(6, 54, '2026-06-21 07:53:31'),
(7, 43, '2026-06-22 06:35:28'),
(7, 45, '2026-06-22 06:35:28'),
(7, 46, '2026-06-22 06:35:28'),
(7, 47, '2026-06-22 06:35:28'),
(7, 55, '2026-07-01 07:25:20'),
(7, 56, '2026-07-01 07:25:20'),
(7, 57, '2026-07-01 07:25:20'),
(7, 58, '2026-07-01 07:25:20'),
(7, 59, '2026-07-01 07:25:20'),
(7, 60, '2026-07-01 07:25:20'),
(7, 61, '2026-07-01 07:25:20'),
(7, 62, '2026-07-01 07:25:20'),
(8, 1, '2026-06-22 06:36:01'),
(8, 2, '2026-06-22 06:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `school_id` varchar(36) NOT NULL,
  `route_name` varchar(120) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `geofence_polygon` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `school_id`, `route_name`, `vehicle_id`, `driver_id`, `geofence_polygon`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'sch-greenwood', 'Route 101 - Shivaji Nagar Loop', 1, 1, '[[18.53, 73.85], [18.54, 73.86], [18.55, 73.85], [18.53, 73.84]]', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `name`, `amount`, `type`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'NGO Financial Aid Program', 5000.00, 'fixed', 'active', 'Awarded based on NGO sponsorship.', '2026-06-20 08:46:37', '2026-06-20 08:46:37'),
(2, 1, 1, 1, 2, 'Special Needs Merit Grant', 50.00, 'percentage', 'active', 'Merit award for active speech therapy progress.', '2026-06-20 08:46:37', '2026-06-20 08:46:37');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `established_year` year(4) DEFAULT NULL,
  `type` enum('special_needs','regular','both') NOT NULL DEFAULT 'special_needs',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`id`, `tenant_id`, `name`, `code`, `email`, `phone`, `address`, `city`, `state`, `pincode`, `logo`, `established_year`, `type`, `is_active`, `settings`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Greenwood International School', 'GREENWOOD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'special_needs', 1, NULL, NULL, NULL, '2026-06-16 03:13:12', '2026-06-16 06:48:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(100) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
(1, 'deployment_mode', 'local'),
(2, 'global_access_start', '01:00'),
(3, 'global_access_end', '17:00'),
(4, 'block_mobile', '1'),
(5, 'debug_mode', '0'),
(6, 'download_restriction', '1'),
(7, 'allowed_file_types', 'pdf,jpg,jpeg,png,mp4,mov,webm'),
(8, 'max_upload_mb', '20000'),
(58, 'access_mon_start', '01:00'),
(59, 'access_mon_end', '22:00'),
(60, 'access_tue_start', '01:00'),
(61, 'access_tue_end', '22:00'),
(62, 'access_wed_start', '01:00'),
(63, 'access_wed_end', '22:00'),
(64, 'access_thu_start', '01:00'),
(65, 'access_thu_end', '22:00'),
(66, 'access_fri_start', '01:00'),
(67, 'access_fri_end', '22:00'),
(68, 'access_sat_start', '01:00'),
(69, 'access_sat_end', '22:00'),
(70, 'access_sun_start', '01:00'),
(71, 'access_sun_end', '17:00'),
(73, 'block_laptop', '0'),
(137, 'allow_smartboard', '0'),
(138, 'smartboard_min_width', '3340'),
(139, 'smartboard_min_height', '2060'),
(265, 'access_mon_status', '1'),
(268, 'access_tue_status', '1'),
(271, 'access_wed_status', '1'),
(274, 'access_thu_status', '1'),
(277, 'access_fri_status', '1'),
(278, 'access_sat_status', '1'),
(281, 'access_sun_status', '1'),
(287, 'screenshot_protection', '1');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_period_minutes` int(11) NOT NULL,
  `half_day_minutes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `grace_period_minutes`, `half_day_minutes`) VALUES
(1, 'Default Shift', '09:00:00', '18:00:00', 15, 240);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `role` varchar(50) DEFAULT 'Teacher'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `email`, `password`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `role`) VALUES
(1, 'Mrs. Preeti Sen', 'teacher@psnf.local', '$2y$10$QV2GPabBGGtcIyaUtJYEse7C3CejjZNz1xx19PEq/xCG9qxz/lDO6', 1, '2026-05-21 07:15:38', '2026-06-14 16:24:21', NULL, 'Teacher'),
(2, 'test', 'test@test.com', '$2y$10$Nn.94EqEUA/GO1vs/FWeX.vEanhkzrUtr/tXnVydoKMW1hjhnI7Em', 1, '2026-05-21 07:29:44', NULL, NULL, 'Teacher'),
(3, 'Mr. Suresh Iyer', 'suresh@psnf.local', '$2y$10$sqGPxsTidqeSFeo7Y6H2ce3KUf20aR5xFDdtJeHrciLBCNOi/92Qi', 1, '2026-06-14 16:09:05', NULL, NULL, 'Teacher');

-- --------------------------------------------------------

--
-- Table structure for table `staff_favorites`
--

CREATE TABLE `staff_favorites` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_favorites`
--

INSERT INTO `staff_favorites` (`id`, `staff_id`, `resource_id`, `folder_id`, `created_at`) VALUES
(5, 2, NULL, 1, '2026-05-26 11:49:00'),
(6, 2, 3, NULL, '2026-05-26 11:49:02');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `admission_number` varchar(50) DEFAULT NULL,
  `gr_number` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `dob` date NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown') DEFAULT 'Unknown',
  `nationality` varchar(100) DEFAULT 'Indian',
  `religion` varchar(100) DEFAULT NULL,
  `mother_tongue` varchar(100) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `disability_type` enum('ASD','ADHD','Down Syndrome','Cerebral Palsy','Dyslexia','Intellectual Disability','Hearing Impairment','Visual Impairment','Multiple Disabilities','Other') NOT NULL,
  `disability_detail` text DEFAULT NULL,
  `disability_certificate` varchar(255) DEFAULT NULL,
  `care_instructions` text DEFAULT NULL,
  `special_needs_summary` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `admission_status` enum('applied','review','assessment','approved','enrolled','withdrawn','graduated') NOT NULL DEFAULT 'applied',
  `admission_date` date DEFAULT NULL,
  `enrolled_date` date DEFAULT NULL,
  `withdrawal_date` date DEFAULT NULL,
  `withdrawal_reason` text DEFAULT NULL,
  `class` varchar(50) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `uuid`, `tenant_id`, `school_id`, `branch_id`, `admission_number`, `gr_number`, `first_name`, `middle_name`, `last_name`, `gender`, `dob`, `photo`, `blood_group`, `nationality`, `religion`, `mother_tongue`, `aadhar_number`, `disability_type`, `disability_detail`, `disability_certificate`, `care_instructions`, `special_needs_summary`, `address`, `city`, `state`, `pincode`, `admission_status`, `admission_date`, `enrolled_date`, `withdrawal_date`, `withdrawal_reason`, `class`, `section`, `academic_year`, `is_active`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'b3857bfe-7d28-4a17-bca0-f5f04abc1518', 1, 1, 1, 'ADM-2026-1-4112', 'GR-1-89345', 'Aarav', 'Rajesh', 'Kumar', 'male', '2018-05-15', NULL, 'O+', 'Indian', NULL, NULL, NULL, 'ASD', 'Autism Spectrum Disorder. Learns well visually. Sensitive to loud noises.', NULL, 'Use quiet zones if overwhelmed. Visual prompts for transitions.', 'Speech therapy and behavior modification plan active.', NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class A', 'S1', NULL, 1, NULL, NULL, NULL, '2026-06-16 03:15:58', NULL, NULL),
(2, '8a64abd9-02d1-4ee5-8ecc-4f8c0185e553', 1, 1, 1, 'ADM-2026-1-5866', 'GR-1-39776', 'Diya', 'Rajesh', 'Kumar', 'female', '2020-09-20', NULL, 'B+', 'Indian', NULL, NULL, NULL, 'Down Syndrome', 'Down Syndrome. Hypotonia. Highly social and responsive to music therapy.', NULL, 'Support during motor activities. Encourage speech repetitions.', 'Physical therapy and speech therapy goals established.', NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class B', 'S2', NULL, 1, NULL, NULL, NULL, '2026-06-16 03:17:00', NULL, NULL),
(3, 'stud-ananya', 1, 1, 1, 'GIS/2024/1054', NULL, 'Ananya', NULL, 'Sharma', 'female', '2015-05-20', NULL, 'O+', 'Indian', NULL, NULL, NULL, 'ADHD', NULL, NULL, 'Requires reminders to stay seated. Highly verbal.', NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-06-16 06:48:22', NULL, NULL),
(4, 'stud-arjun', 1, 1, 1, 'GIS/2021/8946', NULL, 'Arjun', NULL, 'Sharma', 'male', '2012-08-14', NULL, 'O+', 'Indian', NULL, NULL, NULL, 'ASD', NULL, NULL, 'Keep sensory overload headphones in bag. Verbal but benefits from visual cards. Allergic to peanuts.', NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-06-16 06:48:22', NULL, NULL),
(6, 'f3acd0cc-47b9-4927-a5db-0b269ef913c6', 1, 1, 1, 'ADM-2026-1-7424', 'GR-1-62356', 'John', 'Updated', 'Student', 'male', '2012-05-15', NULL, 'O+', 'Indian', NULL, 'English', '1234-5678-9012', 'ASD', 'Communication struggles', NULL, 'Needs quiet space', 'ASD Profile Updated', '456 Updated Street', NULL, NULL, NULL, 'applied', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 1, 1, '2026-06-21 07:30:12', '2026-06-21 07:30:12', '2026-06-21 07:30:12'),
(7, 'f6285655-b98f-4f2a-baa1-a9ce79608aaa', 1, 1, 1, 'ADM-2026-1-5618', 'GR-1-90551', 'Rohan', NULL, 'Patel', 'male', '2018-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, '3-C', 'A', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(8, 'a7c33e33-b360-426c-9fb4-72289c420a23', 1, 1, 1, 'ADM-2026-1-3612', 'GR-1-56092', 'Aarav', NULL, 'Shah', 'male', '2018-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, '2-A', 'A', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(9, '6a67dab3-f947-4c7f-8534-cce0dc2c5988', 1, 1, 1, 'ADM-2026-1-4434', 'GR-1-58312', 'Neha', NULL, 'Joshi', 'male', '2018-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, '4-B', 'B', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(10, '68d66bd3-ed12-41a9-8192-9f88964aba67', 1, 1, 1, 'ADM-2026-1-3719', 'GR-1-95004', 'Student_1', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(11, '1664b2c9-7e83-4c94-81c0-b5d4f997063a', 1, 1, 1, 'ADM-2026-1-9194', 'GR-1-99184', 'Student_2', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(12, 'cfe8155d-0989-4154-8354-371991c6f1e7', 1, 1, 1, 'ADM-2026-1-3683', 'GR-1-44901', 'Student_3', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(13, '8cb77219-63cc-46c6-86e5-4ed49747ebb4', 1, 1, 1, 'ADM-2026-1-3658', 'GR-1-03592', 'Student_4', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(14, '81ee6f1e-26f3-4829-bde8-f209aaf50089', 1, 1, 1, 'ADM-2026-1-0659', 'GR-1-95588', 'Student_5', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(15, '88767efa-87f2-4b8f-ac9d-3b4ba4f1ae43', 1, 1, 1, 'ADM-2026-1-5275', 'GR-1-71105', 'Student_6', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(16, '08b0157c-60a0-4fc6-ae36-b0357461a600', 1, 1, 1, 'ADM-2026-1-9619', 'GR-1-56882', 'Student_7', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(17, '7dfb0b03-5ba3-4174-a728-01070be68625', 1, 1, 1, 'ADM-2026-1-8477', 'GR-1-61028', 'Student_8', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(18, '11774a85-f224-4271-aab8-9037dc9b3d23', 1, 1, 1, 'ADM-2026-1-4247', 'GR-1-34926', 'Student_9', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(19, '334f6058-fd46-47cb-8e0b-44811c951120', 1, 1, 1, 'ADM-2026-1-2573', 'GR-1-79811', 'Student_10', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(20, '2ee28815-c601-48c3-a12d-b0eaef74708d', 1, 1, 1, 'ADM-2026-1-0902', 'GR-1-06092', 'Student_11', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(21, '43a4589e-11ba-4f9f-979b-b9b4c1d17abf', 1, 1, 1, 'ADM-2026-1-4700', 'GR-1-39721', 'Student_12', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(22, 'a103f9e4-6b9f-4d2a-9428-3f815d96afd4', 1, 1, 1, 'ADM-2026-1-8361', 'GR-1-45619', 'Student_13', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(23, '7ca66100-3bdf-46fe-ab04-78867f716e8b', 1, 1, 1, 'ADM-2026-1-2216', 'GR-1-23821', 'Student_14', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(24, 'c2c198ae-9a3d-4daa-8cb9-69982d7463b2', 1, 1, 1, 'ADM-2026-1-3232', 'GR-1-42421', 'Student_15', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(25, 'bbaeec87-1e2b-473f-a532-475c8a30d1e9', 1, 1, 1, 'ADM-2026-1-6510', 'GR-1-63950', 'Student_16', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(26, '2fddf31b-8ac8-4dbd-9697-2165254198c6', 1, 1, 1, 'ADM-2026-1-9602', 'GR-1-24190', 'Student_17', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(27, 'db8f7fb0-51ea-4a5c-8374-85fef77f4ae4', 1, 1, 1, 'ADM-2026-1-4227', 'GR-1-57505', 'Student_18', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(28, 'cb5abb86-c70e-4f80-b011-cbc48aa90160', 1, 1, 1, 'ADM-2026-1-4900', 'GR-1-12273', 'Student_19', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(29, 'f98d4f37-2ba8-4847-afbc-2d63ba5ac9ae', 1, 1, 1, 'ADM-2026-1-7640', 'GR-1-99051', 'Student_20', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(30, 'd5197e13-842e-4e9b-9a17-66126f89edb7', 1, 1, 1, 'ADM-2026-1-2000', 'GR-1-24484', 'Student_21', NULL, 'PSNF', 'male', '2019-01-01', NULL, 'Unknown', 'Indian', NULL, NULL, NULL, 'Other', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class C', 'S1', NULL, 1, NULL, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(31, '152f3b71-b169-4a46-aa10-44e775bdbe32', 1, 1, 1, 'ADM-2026-1-6089', 'GR-1-01195', 'het shah', '', 'shah', 'male', '2026-07-23', NULL, 'A-', 'Indian', NULL, '', '', 'ADHD', '', NULL, '', '', '', NULL, NULL, NULL, 'applied', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, '2026-07-24 09:51:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` bigint(20) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `absence_reason` varchar(190) DEFAULT NULL,
  `checked_in_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`id`, `student_id`, `date`, `status`, `absence_reason`, `checked_in_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', '2026-06-12', 'late', NULL, 'Driver', '2026-06-14 16:09:05', NULL, NULL),
(2, 'stud-ananya', '2026-06-12', 'present', NULL, 'Driver', '2026-06-14 16:09:05', NULL, NULL),
(3, 'stud-arjun', '2026-06-11', 'present', NULL, 'Teacher: 1', '2026-06-14 16:09:05', NULL, NULL),
(4, 'stud-ananya', '2026-06-11', 'present', NULL, 'Teacher: 1', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `type` enum('birth_certificate','aadhar','medical_report','disability_certificate','transfer_certificate','photo','other') NOT NULL DEFAULT 'other',
  `title` varchar(191) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_documents`
--

INSERT INTO `student_documents` (`id`, `student_id`, `type`, `title`, `file_name`, `stored_name`, `mime_type`, `file_size`, `status`, `verified_by`, `verified_at`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'birth_certificate', 'asd', 'Untitled design (5).png', '2cc2f48b76cead3dc47deaf55aa873a3.png', 'image/png', 777898, 'pending', NULL, NULL, NULL, 1, '2026-06-16 03:43:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_medical`
--

CREATE TABLE `student_medical` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `allergies` text DEFAULT NULL,
  `allergy_severity` enum('mild','moderate','severe') DEFAULT NULL,
  `triggers` text DEFAULT NULL,
  `current_medications` text DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `care_instructions` text DEFAULT NULL,
  `emergency_protocols` text DEFAULT NULL,
  `doctor_name` varchar(191) DEFAULT NULL,
  `doctor_phone` varchar(30) DEFAULT NULL,
  `hospital_name` varchar(191) DEFAULT NULL,
  `insurance_provider` varchar(191) DEFAULT NULL,
  `insurance_number` varchar(100) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `vision` varchar(100) DEFAULT NULL,
  `hearing` varchar(100) DEFAULT NULL,
  `last_checkup_date` date DEFAULT NULL,
  `dietary_restrictions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_medical`
--

INSERT INTO `student_medical` (`id`, `student_id`, `allergies`, `allergy_severity`, `triggers`, `current_medications`, `medical_conditions`, `care_instructions`, `emergency_protocols`, `doctor_name`, `doctor_phone`, `hospital_name`, `insurance_provider`, `insurance_number`, `blood_pressure`, `weight_kg`, `height_cm`, `vision`, `hearing`, `last_checkup_date`, `dietary_restrictions`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'Dust, Pollen', NULL, 'Sudden changes in temperature', 'None', NULL, 'Support during motor activities. Encourage speech repetitions.', 'Contact father immediately and relocate to quiet room.', 'Dr. Anjali Mehta', '+91-9892011223', 'Children Specialty Hospital', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-16 06:47:00', NULL, NULL),
(3, 6, 'Peanuts and Gluten', NULL, 'Loud sounds', 'Ritalin 20mg daily', NULL, 'Needs quiet space', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-06-21 07:30:12', '2026-06-21 07:30:12', NULL),
(4, 31, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, '2026-07-24 09:51:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_report_cards`
--

CREATE TABLE `student_report_cards` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `routine_profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`routine_profile`)),
  `learning_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`learning_skills`)),
  `academic_profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`academic_profile`)),
  `cocurriculum_profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`cocurriculum_profile`)),
  `attendance_profile` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attendance_profile`)),
  `feedback_text` text DEFAULT NULL,
  `authorized_by` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`authorized_by`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `id` int(11) NOT NULL,
  `student_id` varchar(36) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `score` int(11) NOT NULL,
  `max_score` int(11) NOT NULL,
  `grade` varchar(5) NOT NULL,
  `term` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_results`
--

INSERT INTO `student_results` (`id`, `student_id`, `subject`, `score`, `max_score`, `grade`, `term`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'stud-arjun', 'Mathematics', 92, 100, 'A', 'Term 1', '2026-06-14 16:09:05', NULL, NULL),
(2, 'stud-arjun', 'English Language', 88, 100, 'B', 'Term 1', '2026-06-14 16:09:05', NULL, NULL),
(3, 'stud-arjun', 'General Science', 85, 100, 'B', 'Term 1', '2026-06-14 16:09:05', NULL, NULL),
(4, 'stud-arjun', 'Social Studies', 90, 100, 'A', 'Term 1', '2026-06-14 16:09:05', NULL, NULL),
(5, 'stud-arjun', 'Computer Science', 95, 100, 'A+', 'Term 1', '2026-06-14 16:09:05', NULL, NULL),
(6, 'stud-ananya', 'Mathematics', 78, 100, 'C', 'Term 1', '2026-06-14 16:09:05', NULL, NULL),
(7, 'stud-ananya', 'English Language', 85, 100, 'B', 'Term 1', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_timeline`
--

CREATE TABLE `student_timeline` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `icon` varchar(50) DEFAULT 'circle',
  `color` varchar(20) DEFAULT 'blue',
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(191) DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_timeline`
--

INSERT INTO `student_timeline` (`id`, `student_id`, `event_type`, `title`, `description`, `meta`, `icon`, `color`, `actor_id`, `actor_name`, `occurred_at`, `created_at`) VALUES
(1, 3, 'document_upload', 'Document uploaded: asd', NULL, '[]', 'document', 'green', 1, 'Super Admin', '2026-06-16 09:13:15', '2026-06-16 03:43:15'),
(2, 1, 'attendance_declaration', 'Parent declared tomorrow\'s attendance as: Absent', NULL, '{\"status\":\"absent\",\"date\":\"2026-06-19\"}', 'calendar', 'red', 2, 'Rajesh Kumar', '2026-06-18 13:07:01', '2026-06-18 07:37:01'),
(4, 6, 'admission', 'Application submitted', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 1, 'Super Admin', '2026-06-21 13:00:12', '2026-06-21 07:30:12'),
(5, 3, 'transport', 'Transport Assigned', 'Assigned to route \'TRAGAD\' (GJ01XP2354). Pickup: TRAGAD at 08:00.', NULL, 'truck', 'indigo', NULL, 'Super Admin', '2026-07-01 12:52:15', '2026-07-01 07:22:15'),
(6, 4, 'transport', 'Transport Assigned', 'Assigned to route \'TRAGAD\' (GJ01XP2354). Pickup: AHMEDABAD  at 08:00.', NULL, 'truck', 'indigo', NULL, 'Super Admin', '2026-07-01 12:53:11', '2026-07-01 07:23:11'),
(7, 31, 'admission', 'Application submitted by Parent', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 2, 'Rajesh Kumar', '2026-07-24 15:21:25', '2026-07-24 09:51:25');

-- --------------------------------------------------------

--
-- Table structure for table `student_transport`
--

CREATE TABLE `student_transport` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `route_id` int(10) UNSIGNED NOT NULL,
  `pickup_point` varchar(255) DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_transport`
--

INSERT INTO `student_transport` (`id`, `student_id`, `route_id`, `pickup_point`, `pickup_time`, `created_at`) VALUES
(1, 1, 1, 'Society Main Gate, Pearl Heights', '08:15:00', '2026-06-16 06:47:31'),
(2, 2, 1, 'Society Main Gate, Pearl Heights', '08:15:00', '2026-06-16 06:47:31'),
(3, 3, 2, 'TRAGAD', '08:00:00', '2026-07-01 07:22:15'),
(4, 4, 2, 'AHMEDABAD ', '08:00:00', '2026-07-01 07:23:11');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','expired','cancelled','trialing','past_due') NOT NULL DEFAULT 'active',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_ref` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`) VALUES
(1, 'company_name', 'My Company'),
(2, 'similarity_threshold', '0.65'),
(3, 'liveness_threshold', '0.85'),
(4, 'cooldown_seconds', '10'),
(5, 'voice_enabled', 'true');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `opened_at` datetime NOT NULL,
  `checkout_at` datetime DEFAULT NULL,
  `checked_out_at` datetime DEFAULT NULL,
  `total_working` varchar(30) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `status` enum('on_time','late') NOT NULL,
  `lecture_time` time NOT NULL,
  `grace_period` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_attendance`
--

INSERT INTO `teacher_attendance` (`id`, `tenant_id`, `school_id`, `branch_id`, `user_id`, `attendance_date`, `opened_at`, `checkout_at`, `checked_out_at`, `total_working`, `closed_at`, `status`, `lecture_time`, `grace_period`, `created_at`, `updated_at`) VALUES
(14, 1, 1, 1, 4, '2026-06-20', '2026-06-20 13:25:29', NULL, NULL, NULL, NULL, 'late', '08:00:00', 5, '2026-06-20 07:55:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(10) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `domain` varchar(191) DEFAULT NULL,
  `subdomain` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `timezone` varchar(50) NOT NULL DEFAULT 'Asia/Kolkata',
  `plan_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `trial_ends_at` datetime DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `uuid`, `name`, `slug`, `domain`, `subdomain`, `logo`, `email`, `phone`, `address`, `city`, `state`, `country`, `timezone`, `plan_id`, `is_active`, `trial_ends_at`, `settings`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '349ed798-cb8b-4656-a850-969ac5258d66', 'Pearl Special Needs Foundation', 'psnf', NULL, NULL, NULL, 'admin@psnf.edu', '+91-9000000000', NULL, NULL, NULL, 'India', 'Asia/Kolkata', NULL, 1, NULL, NULL, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `school_id` varchar(36) NOT NULL,
  `branch_id` varchar(36) NOT NULL,
  `grade` varchar(20) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `period_no` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `school_id`, `branch_id`, `grade`, `day_of_week`, `period_no`, `subject`, `teacher_id`, `start_time`, `end_time`, `room`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'sch-greenwood', 'br-main', '8-A', 'Monday', 1, 'Mathematics', 3, '08:30:00', '09:15:00', 'Room 104', '2026-06-14 16:09:05', NULL, NULL),
(2, 'sch-greenwood', 'br-main', '8-A', 'Monday', 2, 'English', 1, '09:15:00', '10:00:00', 'Room 104', '2026-06-14 16:09:05', NULL, NULL),
(3, 'sch-greenwood', 'br-main', '8-A', 'Monday', 3, 'Science', 3, '10:00:00', '10:45:00', 'Lab A', '2026-06-14 16:09:05', NULL, NULL),
(4, 'sch-greenwood', 'br-main', '8-A', 'Monday', 4, 'Social Studies', 1, '11:15:00', '12:00:00', 'Room 104', '2026-06-14 16:09:05', NULL, NULL),
(5, 'sch-greenwood', 'br-main', '8-A', 'Monday', 5, 'Computer Science', 3, '12:00:00', '12:45:00', 'IT Center', '2026-06-14 16:09:05', NULL, NULL),
(6, 'sch-greenwood', 'br-main', '5-B', 'Monday', 1, 'English', 1, '08:30:00', '09:15:00', 'Room 102', '2026-06-14 16:09:05', NULL, NULL),
(7, 'sch-greenwood', 'br-main', '5-B', 'Monday', 2, 'Mathematics', 3, '09:15:00', '10:00:00', 'Room 102', '2026-06-14 16:09:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `class` varchar(50) NOT NULL,
  `section` varchar(20) DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `teacher_name` varchar(100) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`id`, `tenant_id`, `school_id`, `branch_id`, `class`, `section`, `day_of_week`, `subject`, `teacher_name`, `room`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Class A', 'S1', 'Monday', 'Speech Therapy', 'Ms. Sarah D\'Souza', 'Room 101', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 'Class A', 'S1', 'Monday', 'Sensory Integration', 'Mrs. Priya Nair', 'Room 101', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 'Class A', 'S1', 'Monday', 'Visual Arts', 'Mr. Amit Sharma', 'Room 101', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(4, 1, 1, 1, 'Class A', 'S1', 'Monday', 'Math Foundations', 'Dr. Kiran Patel', 'Room 101', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(5, 1, 1, 1, 'Class A', 'S1', 'Tuesday', 'Math Foundations', 'Ms. Sarah D\'Souza', 'Room 101', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(6, 1, 1, 1, 'Class A', 'S1', 'Tuesday', 'Visual Arts', 'Mrs. Priya Nair', 'Room 101', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(7, 1, 1, 1, 'Class A', 'S1', 'Tuesday', 'Speech Therapy', 'Mr. Amit Sharma', 'Room 101', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(8, 1, 1, 1, 'Class A', 'S1', 'Tuesday', 'Life Skills', 'Dr. Kiran Patel', 'Room 101', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(9, 1, 1, 1, 'Class A', 'S1', 'Wednesday', 'Visual Arts', 'Ms. Sarah D\'Souza', 'Room 101', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(10, 1, 1, 1, 'Class A', 'S1', 'Wednesday', 'Math Foundations', 'Mrs. Priya Nair', 'Room 101', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(11, 1, 1, 1, 'Class A', 'S1', 'Wednesday', 'Life Skills', 'Mr. Amit Sharma', 'Room 101', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(12, 1, 1, 1, 'Class A', 'S1', 'Wednesday', 'Life Skills', 'Dr. Kiran Patel', 'Room 101', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(13, 1, 1, 1, 'Class A', 'S1', 'Thursday', 'Visual Arts', 'Ms. Sarah D\'Souza', 'Room 101', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(14, 1, 1, 1, 'Class A', 'S1', 'Thursday', 'Speech Therapy', 'Mrs. Priya Nair', 'Room 101', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(15, 1, 1, 1, 'Class A', 'S1', 'Thursday', 'Life Skills', 'Mr. Amit Sharma', 'Room 101', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(16, 1, 1, 1, 'Class A', 'S1', 'Thursday', 'Sensory Integration', 'Dr. Kiran Patel', 'Room 101', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(17, 1, 1, 1, 'Class A', 'S1', 'Friday', 'Life Skills', 'Ms. Sarah D\'Souza', 'Room 101', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(18, 1, 1, 1, 'Class A', 'S1', 'Friday', 'Math Foundations', 'Mrs. Priya Nair', 'Room 101', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(19, 1, 1, 1, 'Class A', 'S1', 'Friday', 'Speech Therapy', 'Mr. Amit Sharma', 'Room 101', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(20, 1, 1, 1, 'Class A', 'S1', 'Friday', 'Speech Therapy', 'Dr. Kiran Patel', 'Room 101', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(21, 1, 1, 1, 'Class B', 'S2', 'Monday', 'Occupational Therapy', 'Ms. Sarah D\'Souza', 'Room 102', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(22, 1, 1, 1, 'Class B', 'S2', 'Monday', 'Music Therapy', 'Mrs. Priya Nair', 'Room 102', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(23, 1, 1, 1, 'Class B', 'S2', 'Monday', 'Social Communication', 'Mr. Amit Sharma', 'Room 102', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(24, 1, 1, 1, 'Class B', 'S2', 'Monday', 'Basic Literacy', 'Dr. Kiran Patel', 'Room 102', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(25, 1, 1, 1, 'Class B', 'S2', 'Tuesday', 'Social Communication', 'Ms. Sarah D\'Souza', 'Room 102', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(26, 1, 1, 1, 'Class B', 'S2', 'Tuesday', 'Basic Literacy', 'Mrs. Priya Nair', 'Room 102', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(27, 1, 1, 1, 'Class B', 'S2', 'Tuesday', 'Music Therapy', 'Mr. Amit Sharma', 'Room 102', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(28, 1, 1, 1, 'Class B', 'S2', 'Tuesday', 'Motor Coordination', 'Dr. Kiran Patel', 'Room 102', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(29, 1, 1, 1, 'Class B', 'S2', 'Wednesday', 'Music Therapy', 'Ms. Sarah D\'Souza', 'Room 102', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(30, 1, 1, 1, 'Class B', 'S2', 'Wednesday', 'Motor Coordination', 'Mrs. Priya Nair', 'Room 102', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(31, 1, 1, 1, 'Class B', 'S2', 'Wednesday', 'Music Therapy', 'Mr. Amit Sharma', 'Room 102', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(32, 1, 1, 1, 'Class B', 'S2', 'Wednesday', 'Music Therapy', 'Dr. Kiran Patel', 'Room 102', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(33, 1, 1, 1, 'Class B', 'S2', 'Thursday', 'Motor Coordination', 'Ms. Sarah D\'Souza', 'Room 102', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(34, 1, 1, 1, 'Class B', 'S2', 'Thursday', 'Occupational Therapy', 'Mrs. Priya Nair', 'Room 102', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(35, 1, 1, 1, 'Class B', 'S2', 'Thursday', 'Basic Literacy', 'Mr. Amit Sharma', 'Room 102', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(36, 1, 1, 1, 'Class B', 'S2', 'Thursday', 'Occupational Therapy', 'Dr. Kiran Patel', 'Room 102', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(37, 1, 1, 1, 'Class B', 'S2', 'Friday', 'Social Communication', 'Ms. Sarah D\'Souza', 'Room 102', '09:00:00', '10:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(38, 1, 1, 1, 'Class B', 'S2', 'Friday', 'Occupational Therapy', 'Mrs. Priya Nair', 'Room 102', '10:30:00', '11:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(39, 1, 1, 1, 'Class B', 'S2', 'Friday', 'Motor Coordination', 'Mr. Amit Sharma', 'Room 102', '12:00:00', '13:15:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(40, 1, 1, 1, 'Class B', 'S2', 'Friday', 'Occupational Therapy', 'Dr. Kiran Patel', 'Room 102', '13:30:00', '14:45:00', '2026-06-16 06:47:31', '2026-06-16 06:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `transport_routes`
--

CREATE TABLE `transport_routes` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `route_name` varchar(255) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `driver_name` varchar(255) NOT NULL,
  `driver_phone` varchar(30) NOT NULL,
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('inactive','en_route','completed') NOT NULL DEFAULT 'inactive',
  `last_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_speed` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transport_routes`
--

INSERT INTO `transport_routes` (`id`, `tenant_id`, `school_id`, `branch_id`, `route_name`, `bus_number`, `driver_name`, `driver_phone`, `current_latitude`, `current_longitude`, `status`, `last_updated_at`, `created_at`, `updated_at`, `current_speed`) VALUES
(1, 1, 1, 1, 'Mumbai East Route - Route 5', 'MH-12-AB-5678', 'Rajendra Singh', '+91-9123456789', 19.07609000, 72.87742600, 'en_route', '2026-06-16 03:17:31', '2026-06-16 06:47:31', '2026-06-16 06:47:31', 0.00),
(2, 1, 1, 1, 'TRAGAD', 'GJ01XP2354', 'AKSHAT', '9328738282', 13.08270000, 80.27070000, 'en_route', '2026-07-01 07:08:26', '2026-07-01 07:08:26', '2026-07-01 07:08:26', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `phone_verified_at` datetime DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(64) DEFAULT NULL,
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `lecture_time` time DEFAULT NULL,
  `grace_period` int(10) UNSIGNED DEFAULT 5,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `tenant_id`, `school_id`, `branch_id`, `name`, `email`, `phone`, `password`, `avatar`, `gender`, `dob`, `designation`, `employee_id`, `email_verified_at`, `phone_verified_at`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `last_login_at`, `last_login_ip`, `login_attempts`, `locked_until`, `is_active`, `settings`, `lecture_time`, `grace_period`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '50aefdd5-6cdc-42f0-be4e-0f1d713cc1aa', 1, 1, 1, 'Super Admin', 'admin@psnf.edu', '', '$2y$10$Xkg9mGcE9FMCltTCL2tBKugfO1Fd7q6XX2kJ9Yj84kUuGDInPNG/6', NULL, NULL, NULL, '', NULL, '2026-06-16 08:43:13', NULL, 0, NULL, NULL, '2026-07-27 14:45:47', '::1', 0, NULL, 1, NULL, NULL, 5, NULL, 1, '2026-06-16 03:13:13', '2026-07-27 09:15:47', NULL),
(2, '8920fdf8-ac09-4305-b294-5eb84f19e36d', 1, 1, 1, 'Rajesh Kumar', 'parent@psnf.edu', '+91-9876543210', '$2y$10$Xkg9mGcE9FMCltTCL2tBKugfO1Fd7q6XX2kJ9Yj84kUuGDInPNG/6', NULL, NULL, NULL, 'Guardian', NULL, '2026-06-16 08:45:37', NULL, 0, NULL, NULL, '2026-07-24 15:20:29', '::1', 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-06-16 03:15:37', '2026-07-24 09:50:29', NULL),
(3, 'a5aeb1f1-c027-41db-945e-75f4e2ac17b9', 1, 1, 1, 'John Driver', 'driver@psnf.edu', '+91-9876543210', '$2y$12$An/UPT1DQNRHnglteZgIGuaLfVIVIFA0zcJvivXc.sA1v36fEyv8u', NULL, NULL, NULL, 'Driver', NULL, '2026-06-19 11:49:06', NULL, 0, NULL, NULL, '2026-06-19 11:52:09', '::1', 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-06-19 06:19:06', '2026-06-19 06:22:09', NULL),
(4, 'e6107c0a-b84f-404f-9d5e-e8783295fe04', 1, 1, 1, 'Sarah Jenkins', 'teacher@psnf.edu', NULL, '$2y$12$v938eZnW94.t8RhO..R30e.db1TVJsffWhghOCQ9DIrWJ2Dehq.O6', NULL, NULL, NULL, 'Senior Teacher', NULL, NULL, NULL, 0, NULL, NULL, '2026-06-20 13:36:36', '::1', 0, NULL, 1, NULL, '08:00:00', 5, NULL, NULL, '2026-06-20 06:54:45', '2026-06-20 08:06:36', NULL),
(5, '62e3b466-c37d-4b7d-a3de-a6172ccd5216', 1, 1, 1, 'Priya Mehta', 'priya@psnf.edu', '+91-9876543222', '$2y$12$Nut6vDw5zU14ofPGSc.gKuHOwT4y0BwK/stWUZLeqsJ8ONMIYWX5C', NULL, NULL, NULL, 'Educator', 'ST1025', '2026-07-13 12:55:11', NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, '08:30:00', 15, NULL, NULL, '2026-07-13 07:25:11', NULL, NULL),
(6, '75e185fd-1091-413c-ad77-57f7eebaf6dc', 1, 1, 1, 'vegr', '2325@psnf.edu', NULL, '$2y$10$AvitQ.FNAc9GvHGNdd1TZu0BhZ8qnQMezlB6/Wpx8x5Z5Z4NlRFty', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-07-27 09:45:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_apps`
--

CREATE TABLE `user_apps` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `app_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_apps`
--

INSERT INTO `user_apps` (`user_id`, `app_name`) VALUES
(4, 'academic'),
(4, 'games'),
(4, 'medical'),
(5, 'teacher_app'),
(6, 'teacher_app');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `created_at`) VALUES
(1, 1, '2026-06-16 08:16:53'),
(2, 8, '2026-06-16 06:45:37'),
(3, 7, '2026-06-19 06:19:06'),
(4, 4, '2026-06-20 06:54:45'),
(5, 4, '2026-07-13 07:25:11'),
(6, 4, '2026-07-27 09:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `school_id` varchar(36) NOT NULL,
  `plate_number` varchar(30) NOT NULL,
  `model` varchar(90) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `school_id`, `plate_number`, `model`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'sch-greenwood', 'MH-12-AB-1234', 'Tata Starbus 40-Seater', '2026-06-14 16:09:05', NULL, NULL);

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
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_student_date` (`student_id`,`date`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attendance_employee_time` (`employee_id`,`clock_time`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_tenant` (`tenant_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_class_section` (`tenant_id`,`name`,`section`);

--
-- Indexes for table `communication_messages`
--
ALTER TABLE `communication_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ix_departments_code` (`code`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `ix_employees_status` (`status`);

--
-- Indexes for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `face_embeddings`
--
ALTER TABLE `face_embeddings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `fees_invoices`
--
ALTER TABLE `fees_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `invoice_id` (`invoice_id`);

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
-- Indexes for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `guardian_student`
--
ALTER TABLE `guardian_student`
  ADD PRIMARY KEY (`guardian_id`,`student_id`),
  ADD KEY `fk_gs_student` (`student_id`);

--
-- Indexes for table `homeworks`
--
ALTER TABLE `homeworks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `iep_progress`
--
ALTER TABLE `iep_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `medical_incidents`
--
ALTER TABLE `medical_incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `logged_by` (`logged_by`);

--
-- Indexes for table `medication_administration`
--
ALTER TABLE `medication_administration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `administered_by` (`administered_by`),
  ADD KEY `witnessed_by` (`witnessed_by`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `online_enrollments`
--
ALTER TABLE `online_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_code` (`application_code`),
  ADD KEY `application_code_2` (`application_code`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `parent_student`
--
ALTER TABLE `parent_student`
  ADD PRIMARY KEY (`parent_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_key` (`key`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `report_card_settings`
--
ALTER TABLE `report_card_settings`
  ADD PRIMARY KEY (`tenant_id`);

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
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_tenant` (`slug`,`tenant_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_activity` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `admission_number` (`admission_number`),
  ADD UNIQUE KEY `gr_number` (`gr_number`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_branch` (`branch_id`),
  ADD KEY `idx_status` (`admission_status`),
  ADD KEY `idx_uuid` (`uuid`);
ALTER TABLE `students` ADD FULLTEXT KEY `ft_name` (`first_name`,`last_name`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `student_medical`
--
ALTER TABLE `student_medical`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `student_report_cards`
--
ALTER TABLE `student_report_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_semester` (`student_id`,`academic_year`,`semester`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_timeline`
--
ALTER TABLE `student_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_event` (`event_type`),
  ADD KEY `idx_date` (`occurred_at`);

--
-- Indexes for table `student_transport`
--
ALTER TABLE `student_transport`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_student_route` (`student_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_sub_plan` (`plan_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ix_system_settings_key` (`key`);

--
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teacher_date` (`user_id`,`attendance_date`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `subdomain` (`subdomain`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_uuid` (`uuid`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `uq_email_tenant` (`email`,`tenant_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_school` (`school_id`),
  ADD KEY `idx_branch` (`branch_id`),
  ADD KEY `idx_uuid` (`uuid`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `user_apps`
--
ALTER TABLE `user_apps`
  ADD PRIMARY KEY (`user_id`,`app_name`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_ur_role` (`role_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=348;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `communication_messages`
--
ALTER TABLE `communication_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `face_embeddings`
--
ALTER TABLE `face_embeddings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `fees_invoices`
--
ALTER TABLE `fees_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gps_logs`
--
ALTER TABLE `gps_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `homeworks`
--
ALTER TABLE `homeworks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `iep_progress`
--
ALTER TABLE `iep_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_incidents`
--
ALTER TABLE `medical_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medication_administration`
--
ALTER TABLE `medication_administration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `online_enrollments`
--
ALTER TABLE `online_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=326;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_favorites`
--
ALTER TABLE `staff_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_medical`
--
ALTER TABLE `student_medical`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_report_cards`
--
ALTER TABLE `student_report_cards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_timeline`
--
ALTER TABLE `student_timeline`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_transport`
--
ALTER TABLE `student_transport`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `transport_routes`
--
ALTER TABLE `transport_routes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `fk_branches_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_branches_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `communication_messages`
--
ALTER TABLE `communication_messages`
  ADD CONSTRAINT `communication_messages_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communication_messages_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communication_messages_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communication_messages_ibfk_4` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communication_messages_ibfk_5` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `fk_ec_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD CONSTRAINT `employee_shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_shifts_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `face_embeddings`
--
ALTER TABLE `face_embeddings`
  ADD CONSTRAINT `face_embeddings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD CONSTRAINT `fee_invoices_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_4` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD CONSTRAINT `gps_logs_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `fk_guardian_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guardian_student`
--
ALTER TABLE `guardian_student`
  ADD CONSTRAINT `fk_gs_guardian` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_gs_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `homeworks`
--
ALTER TABLE `homeworks`
  ADD CONSTRAINT `homeworks_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homeworks_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homeworks_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_incidents`
--
ALTER TABLE `medical_incidents`
  ADD CONSTRAINT `medical_incidents_ibfk_2` FOREIGN KEY (`logged_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `medication_administration`
--
ALTER TABLE `medication_administration`
  ADD CONSTRAINT `medication_administration_ibfk_2` FOREIGN KEY (`administered_by`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `medication_administration_ibfk_3` FOREIGN KEY (`witnessed_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `report_card_settings`
--
ALTER TABLE `report_card_settings`
  ADD CONSTRAINT `report_card_settings_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `routes_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD CONSTRAINT `scholarships_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scholarships_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scholarships_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scholarships_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schools`
--
ALTER TABLE `schools`
  ADD CONSTRAINT `fk_schools_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_favorites`
--
ALTER TABLE `staff_favorites`
  ADD CONSTRAINT `staff_favorites_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_favorites_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_favorites_ibfk_3` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_students_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_students_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `fk_doc_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_medical`
--
ALTER TABLE `student_medical`
  ADD CONSTRAINT `fk_med_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_report_cards`
--
ALTER TABLE `student_report_cards`
  ADD CONSTRAINT `student_report_cards_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_report_cards_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_report_cards_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_report_cards_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_timeline`
--
ALTER TABLE `student_timeline`
  ADD CONSTRAINT `fk_tl_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_transport`
--
ALTER TABLE `student_transport`
  ADD CONSTRAINT `student_transport_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_transport_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  ADD CONSTRAINT `fk_sub_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD CONSTRAINT `fk_teacher_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `timetable_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `timetables_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetables_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetables_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD CONSTRAINT `transport_routes_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_routes_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_routes_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_users_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_apps`
--
ALTER TABLE `user_apps`
  ADD CONSTRAINT `fk_user_apps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
