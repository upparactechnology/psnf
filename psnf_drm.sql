-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 16, 2026 at 10:39 AM
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
(197, 1, 1, 1, 1, NULL, NULL, 'login_success', NULL, NULL, NULL, '[]', '::1', NULL, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-16 04:58:19');

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
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `date`, `status`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2026-05-18', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 1, '2026-05-19', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 1, '2026-05-20', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(4, 1, 1, 1, 1, '2026-05-21', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(5, 1, 1, 1, 1, '2026-05-22', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(6, 1, 1, 1, 1, '2026-05-23', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(7, 1, 1, 1, 1, '2026-05-25', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(8, 1, 1, 1, 1, '2026-05-26', 'absent', 'Sick leave - notified by parent', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(9, 1, 1, 1, 1, '2026-05-27', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(10, 1, 1, 1, 1, '2026-05-28', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(11, 1, 1, 1, 1, '2026-05-29', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(12, 1, 1, 1, 1, '2026-05-30', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(13, 1, 1, 1, 1, '2026-06-01', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(14, 1, 1, 1, 1, '2026-06-02', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(15, 1, 1, 1, 1, '2026-06-03', 'late', 'Late by 15 mins due to traffic', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(16, 1, 1, 1, 1, '2026-06-04', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(17, 1, 1, 1, 1, '2026-06-05', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(18, 1, 1, 1, 1, '2026-06-06', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(19, 1, 1, 1, 1, '2026-06-08', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(20, 1, 1, 1, 1, '2026-06-09', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(21, 1, 1, 1, 1, '2026-06-10', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(22, 1, 1, 1, 1, '2026-06-11', 'absent', 'Sick leave - notified by parent', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(23, 1, 1, 1, 1, '2026-06-12', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(24, 1, 1, 1, 1, '2026-06-13', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(25, 1, 1, 1, 1, '2026-06-15', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(26, 1, 1, 1, 2, '2026-05-18', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(27, 1, 1, 1, 2, '2026-05-19', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(28, 1, 1, 1, 2, '2026-05-20', 'absent', 'Sick leave - notified by parent', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(29, 1, 1, 1, 2, '2026-05-21', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(30, 1, 1, 1, 2, '2026-05-22', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(31, 1, 1, 1, 2, '2026-05-23', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(32, 1, 1, 1, 2, '2026-05-25', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(33, 1, 1, 1, 2, '2026-05-26', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(34, 1, 1, 1, 2, '2026-05-27', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(35, 1, 1, 1, 2, '2026-05-28', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(36, 1, 1, 1, 2, '2026-05-29', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(37, 1, 1, 1, 2, '2026-05-30', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(38, 1, 1, 1, 2, '2026-06-01', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(39, 1, 1, 1, 2, '2026-06-02', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(40, 1, 1, 1, 2, '2026-06-03', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(41, 1, 1, 1, 2, '2026-06-04', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(42, 1, 1, 1, 2, '2026-06-05', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(43, 1, 1, 1, 2, '2026-06-06', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(44, 1, 1, 1, 2, '2026-06-08', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(45, 1, 1, 1, 2, '2026-06-09', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(46, 1, 1, 1, 2, '2026-06-10', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(47, 1, 1, 1, 2, '2026-06-11', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(48, 1, 1, 1, 2, '2026-06-12', 'late', 'Late by 15 mins due to traffic', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(49, 1, 1, 1, 2, '2026-06-13', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(50, 1, 1, 1, 2, '2026-06-15', 'present', 'Attended class', NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31');

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
(2, 1, 1, 1, 1, 2, 'Aarav Sensory Update', 'Hello Mr. Rajesh, thank you for letting us know! We have briefed Mrs. Nair (our class helper) to keep an eye on Aarav and make sure he has his headphones on during group activities.', 0, '2026-06-16 02:17:31'),
(3, 1, 1, 1, 2, 1, 'Aarav Sensory Update', 'Excellent! Thanks for the quick update. Have a great day.', 1, '2026-06-16 02:47:31');

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
(2, 1, 1, 1, 1, 'INV-2026-1-02', 'Monthly Transport & Bus Fee', 'Bus transport pick-and-drop service charges for this month.', 2500.00, '2026-07-01', 'unpaid', 0.00, NULL, '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `tenant_id`, `school_id`, `branch_id`, `invoice_id`, `amount`, `payment_method`, `payment_ref`, `paid_at`, `created_at`) VALUES
(1, 1, 1, 1, 1, 15000.00, 'Online', 'PAY-REF-TXN88910', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(2, 1, 1, 1, 3, 15000.00, 'Online', 'PAY-REF-TXN88910', '2026-06-16 06:47:31', '2026-06-16 06:47:31'),
(3, 1, 1, 1, 4, 2500.00, 'Cash', 'Manual-D93D2166', '2026-06-16 04:25:54', '2026-06-16 07:55:54');

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
(1, 1, 2, 'Rajesh Kumar', 'Father', 'male', 'parent@psnf.edu', '+91-9876543210', NULL, 'Software Engineer', '1234-5678-9012', 'Apt 405, Pearl Heights, Mumbai, India', NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-06-16 03:15:58', NULL, NULL);

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
(22, '021_create_parent_portal_tables', 3, '2026-06-16 06:43:12');

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
(40, 'Edit settings', 'edit_settings', 'settings', NULL, '2026-06-16 03:13:12', NULL);

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
(49, 'route:363baea9cba210afac6d7a556fca596e30c46333', 1, '2026-06-16 10:29:19', '2026-06-16 04:58:19', '2026-06-16 04:58:19');

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
(4, NULL, 'Teacher', 'teacher', 'Classroom teacher', 1, 1, 4, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(5, NULL, 'Therapist', 'therapist', 'Therapy specialist', 1, 1, 5, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(6, NULL, 'Staff', 'staff', 'General staff member', 1, 1, 6, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(7, NULL, 'Driver', 'driver', 'Transport driver', 1, 1, 7, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(8, NULL, 'Parent', 'parent', 'Parent / guardian', 1, 1, 8, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
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
(1, 40, '2026-06-16 06:43:12');

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
(4, 'stud-arjun', 1, 1, 1, 'GIS/2021/8946', NULL, 'Arjun', NULL, 'Sharma', 'male', '2012-08-14', NULL, 'O+', 'Indian', NULL, NULL, NULL, 'ASD', NULL, NULL, 'Keep sensory overload headphones in bag. Verbal but benefits from visual cards. Allergic to peanuts.', NULL, NULL, NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-06-16 06:48:22', NULL, NULL);

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
(1, 2, 'Dust, Pollen', NULL, 'Sudden changes in temperature', 'None', NULL, 'Support during motor activities. Encourage speech repetitions.', 'Contact father immediately and relocate to quiet room.', 'Dr. Anjali Mehta', '+91-9892011223', 'Children Specialty Hospital', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-16 06:47:00', NULL, NULL);

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
(1, 3, 'document_upload', 'Document uploaded: asd', NULL, '[]', 'document', 'green', 1, 'Super Admin', '2026-06-16 09:13:15', '2026-06-16 03:43:15');

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
(2, 2, 1, 'Society Main Gate, Pearl Heights', '08:15:00', '2026-06-16 06:47:31');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transport_routes`
--

INSERT INTO `transport_routes` (`id`, `tenant_id`, `school_id`, `branch_id`, `route_name`, `bus_number`, `driver_name`, `driver_phone`, `current_latitude`, `current_longitude`, `status`, `last_updated_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Mumbai East Route - Route 5', 'MH-12-AB-5678', 'Rajendra Singh', '+91-9123456789', 19.07609000, 72.87742600, 'en_route', '2026-06-16 03:17:31', '2026-06-16 06:47:31', '2026-06-16 06:47:31');

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
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `tenant_id`, `school_id`, `branch_id`, `name`, `email`, `phone`, `password`, `avatar`, `gender`, `dob`, `designation`, `employee_id`, `email_verified_at`, `phone_verified_at`, `two_factor_enabled`, `two_factor_secret`, `remember_token`, `last_login_at`, `last_login_ip`, `login_attempts`, `locked_until`, `is_active`, `settings`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '50aefdd5-6cdc-42f0-be4e-0f1d713cc1aa', 1, 1, 1, 'Super Admin', 'admin@psnf.edu', '', '$2y$10$Xkg9mGcE9FMCltTCL2tBKugfO1Fd7q6XX2kJ9Yj84kUuGDInPNG/6', NULL, NULL, NULL, '', NULL, '2026-06-16 08:43:13', NULL, 0, NULL, NULL, '2026-06-16 10:28:19', '::1', 0, NULL, 1, NULL, NULL, 1, '2026-06-16 03:13:13', '2026-06-16 08:28:19', NULL),
(2, '8920fdf8-ac09-4305-b294-5eb84f19e36d', 1, 1, 1, 'Rajesh Kumar', 'parent@psnf.edu', '+91-9876543210', '$2y$10$Xkg9mGcE9FMCltTCL2tBKugfO1Fd7q6XX2kJ9Yj84kUuGDInPNG/6', NULL, NULL, NULL, 'Guardian', NULL, '2026-06-16 08:45:37', NULL, 0, NULL, NULL, '2026-06-16 10:18:40', '::1', 0, NULL, 1, NULL, NULL, NULL, '2026-06-16 03:15:37', '2026-06-16 08:18:40', NULL);

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
(1, 8, '2026-06-16 08:16:53'),
(2, 8, '2026-06-16 06:45:37');

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
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `student_id` (`student_id`);

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

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
-- AUTO_INCREMENT for table `communication_messages`
--
ALTER TABLE `communication_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_timeline`
--
ALTER TABLE `student_timeline`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_transport`
--
ALTER TABLE `student_transport`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
