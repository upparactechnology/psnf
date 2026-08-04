-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 04, 2026 at 09:33 AM
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
-- Table structure for table `academic_semesters`
--

CREATE TABLE `academic_semesters` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` enum('OPEN','LOCKED') DEFAULT 'OPEN',
  `date_range` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_terms`
--

CREATE TABLE `academic_terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `term_name` varchar(50) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `year_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('current','unlocked','locked','archived') DEFAULT 'unlocked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `tenant_id`, `school_id`, `year_name`, `start_date`, `end_date`, `is_active`, `is_locked`, `created_at`, `updated_at`, `status`) VALUES
(1, 1, 0, '2024-25', '0000-00-00', '0000-00-00', 0, 0, '2026-07-28 11:54:29', '2026-07-28 11:54:29', 'archived'),
(2, 1, 0, '2025-26', '0000-00-00', '0000-00-00', 0, 0, '2026-07-28 11:54:29', '2026-07-28 11:54:29', 'unlocked'),
(3, 1, 0, '2026-27', '0000-00-00', '0000-00-00', 0, 0, '2026-07-28 11:54:29', '2026-07-28 11:54:29', 'current');

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
  `event` varchar(191) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `model_id` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `tenant_id`, `school_id`, `branch_id`, `user_id`, `event`, `model`, `model_id`, `description`, `properties`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 1, 1, 7, 'user_created', NULL, NULL, NULL, '{\"user_id\":8}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 11:39:57'),
(2, 1, 1, 1, 7, 'user_updated', NULL, NULL, NULL, '{\"user_id\":\"8\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 11:40:50'),
(3, 1, 1, 1, 7, 'class_created', NULL, NULL, NULL, '{\"class\":\"Class A\",\"section\":\"\",\"assigned_count\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 11:42:19'),
(4, 1, 1, 1, 7, 'class_created', NULL, NULL, NULL, '{\"class\":\"Class B\",\"section\":\"\",\"assigned_count\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 11:42:33'),
(5, 1, 1, 1, 7, 'timetable_slot_added', NULL, NULL, NULL, '{\"class\":\"Class A\",\"subject\":\"test\",\"day\":\"Monday\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 11:48:13'),
(6, 1, 1, 1, 7, 'user_updated', NULL, NULL, NULL, '{\"user_id\":\"8\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 12:08:30'),
(7, 1, 1, 1, 7, 'timetable_slot_added', NULL, NULL, NULL, '{\"class\":\"Class A\",\"subject\":\"tesad\",\"day\":\"Saturday\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 12:11:07'),
(8, 1, 1, 1, 7, 'student_created', NULL, NULL, NULL, '{\"student_id\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-28 12:26:35'),
(9, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 05:48:52'),
(10, NULL, NULL, NULL, 7, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.local\",\"attempts\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 06:51:53'),
(11, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 06:51:58'),
(12, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:22:27'),
(13, 1, 1, 1, 7, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:26:19'),
(14, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:26:26'),
(15, 1, 1, 1, 7, 'user_updated', NULL, NULL, NULL, '{\"user_id\":\"8\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:27:19'),
(16, 1, 1, 1, 7, 'role_updated', NULL, NULL, NULL, '{\"role_id\":\"4\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:28:14'),
(17, 1, 1, 1, 7, 'role_updated', NULL, NULL, NULL, '{\"role_id\":\"8\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:28:51'),
(18, NULL, NULL, NULL, 8, 'login_failed', NULL, NULL, NULL, '{\"email\":\"hetshah6315@gmail.com\",\"attempts\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:29:46'),
(19, 1, 1, 1, 8, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:30:05'),
(20, 1, 1, 1, 8, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"late\",\"opened_at\":\"2026-07-29 14:00:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 08:30:05'),
(21, 1, 1, 1, 7, 'timetable_slot_added', NULL, NULL, NULL, '{\"class\":\"Class A\",\"subject\":\"test\",\"day\":\"Wednesday\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:17:26'),
(22, 1, 1, 1, 8, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:17:34'),
(23, 1, 1, 1, 8, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:17:41'),
(24, 1, 1, 1, 8, 'teacher_attendance_checkin', NULL, NULL, NULL, '{\"status\":\"on_time\",\"opened_at\":\"2026-07-29 14:52:01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:22:01'),
(25, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"9\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:31'),
(26, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"9\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:34'),
(27, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:39'),
(28, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:43'),
(29, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:48'),
(30, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:53'),
(31, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:50:56'),
(32, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"9\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 09:56:10'),
(33, 1, 1, 1, 7, 'user_deleted', NULL, NULL, NULL, '{\"user_id\":\"9\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 10:41:23'),
(34, 1, 1, 1, 7, 'user_created', NULL, NULL, NULL, '{\"user_id\":12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 11:25:56'),
(35, 1, 1, 1, 7, 'user_created', NULL, NULL, NULL, '{\"user_id\":13}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 12:35:30'),
(36, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.1.21', 'Dart/3.9 (dart:io)', '2026-07-29 13:00:02'),
(37, 1, 1, 1, 7, 'student_created', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 13:31:39'),
(38, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"driver@psnf.edu\",\"reason\":\"user_not_found\"}', '192.168.1.21', 'Dart/3.9 (dart:io)', '2026-07-29 13:35:05'),
(39, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 13:45:13'),
(40, 1, 1, 1, 7, 'timetable_slot_added', NULL, NULL, NULL, '{\"class\":\"Class A\",\"subject\":\"asd\",\"day\":\"Monday\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 13:46:47'),
(41, 1, 1, 1, 7, 'student_created', NULL, NULL, NULL, '{\"student_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 13:55:53'),
(42, 1, 1, 1, 14, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 13:56:36'),
(43, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@gmail.com\",\"reason\":\"user_not_found\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:10:37'),
(44, NULL, NULL, NULL, NULL, 'login_failed', NULL, NULL, NULL, '{\"email\":\"parent@gmail.com\",\"reason\":\"user_not_found\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:10:47'),
(45, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:11:08'),
(46, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:12:16'),
(47, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:13:44'),
(48, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:14:53'),
(49, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:23:29'),
(50, 1, NULL, NULL, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:35:22'),
(51, 1, NULL, NULL, 15, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:36:10'),
(52, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 08:36:22'),
(53, 1, 1, 1, 7, 'class_created', NULL, NULL, NULL, '{\"class\":\"Class A\",\"section\":\"\",\"assigned_count\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 09:45:36'),
(54, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 10:40:34'),
(55, 1, 1, 1, 7, 'student_assigned_transport', NULL, NULL, NULL, '{\"student_id\":4,\"driver_id\":7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 10:44:00'),
(56, NULL, NULL, NULL, 13, 'login_failed', NULL, NULL, NULL, '{\"email\":\"driver@gmail.com\",\"attempts\":1}', '192.168.1.21', 'Dart/3.9 (dart:io)', '2026-07-30 11:05:27'),
(57, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.1.21', 'Dart/3.9 (dart:io)', '2026-07-30 11:06:48'),
(58, 1, 1, 1, 7, 'student_removed_transport', NULL, NULL, NULL, '{\"assignment_id\":\"1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 11:43:21'),
(59, 1, 1, 1, 7, 'student_assigned_transport', NULL, NULL, NULL, '{\"student_id\":4,\"driver_id\":7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 11:46:31'),
(60, 1, 1, 1, 7, 'student_assigned_transport', NULL, NULL, NULL, '{\"student_id\":3,\"driver_id\":7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 11:47:18'),
(61, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:38:15'),
(62, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 13:39:08'),
(63, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:48:59'),
(64, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.080890000000046,\"lng\":72.88126599999985,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:51:43'),
(65, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081040000000048,\"lng\":72.88138599999985,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:51:48'),
(66, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08119000000005,\"lng\":72.88150599999985,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:51:53'),
(67, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08134000000005,\"lng\":72.88162599999984,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:51:58'),
(68, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081490000000052,\"lng\":72.88174599999984,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:03'),
(69, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081640000000053,\"lng\":72.88186599999983,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:08'),
(70, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081790000000055,\"lng\":72.88198599999983,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:13'),
(71, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081940000000056,\"lng\":72.88210599999982,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:18'),
(72, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082090000000058,\"lng\":72.88222599999982,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:23'),
(73, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08224000000006,\"lng\":72.88234599999981,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:28'),
(74, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08239000000006,\"lng\":72.88246599999981,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:33'),
(75, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082540000000062,\"lng\":72.8825859999998,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:38'),
(76, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082690000000063,\"lng\":72.8827059999998,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:43'),
(77, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082840000000065,\"lng\":72.8828259999998,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:48'),
(78, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082990000000066,\"lng\":72.88294599999979,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:53'),
(79, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083140000000068,\"lng\":72.88306599999979,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:52:58'),
(80, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08329000000007,\"lng\":72.88318599999978,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:53:03'),
(81, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08344000000007,\"lng\":72.88330599999978,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:53:08'),
(82, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083590000000072,\"lng\":72.88342599999977,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:53:13'),
(83, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083740000000073,\"lng\":72.88354599999977,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:53:18'),
(84, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083890000000075,\"lng\":72.88366599999976,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:53:23'),
(85, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:21'),
(86, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.076240000000002,\"lng\":72.877546,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:27'),
(87, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.076390000000004,\"lng\":72.87766599999999,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:32'),
(88, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.076540000000005,\"lng\":72.87778599999999,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:37'),
(89, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.076690000000006,\"lng\":72.87790599999998,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:42'),
(90, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.076840000000008,\"lng\":72.87802599999998,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:47'),
(91, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.07699000000001,\"lng\":72.87814599999997,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:52'),
(92, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.07714000000001,\"lng\":72.87826599999997,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:54:57'),
(93, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.077290000000012,\"lng\":72.87838599999996,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:02'),
(94, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.077440000000013,\"lng\":72.87850599999996,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:07'),
(95, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.077590000000015,\"lng\":72.87862599999995,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:12'),
(96, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.077740000000016,\"lng\":72.87874599999995,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:17'),
(97, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.077890000000018,\"lng\":72.87886599999995,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:22'),
(98, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.07804000000002,\"lng\":72.87898599999994,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:27'),
(99, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.07819000000002,\"lng\":72.87910599999994,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:32'),
(100, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.078340000000022,\"lng\":72.87922599999993,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:37'),
(101, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.078490000000023,\"lng\":72.87934599999993,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:42'),
(102, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.078640000000025,\"lng\":72.87946599999992,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:47'),
(103, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.078790000000026,\"lng\":72.87958599999992,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:52'),
(104, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.078940000000028,\"lng\":72.87970599999991,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:55:57'),
(105, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.07909000000003,\"lng\":72.87982599999991,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:03'),
(106, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.07924000000003,\"lng\":72.8799459999999,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:07'),
(107, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.079390000000032,\"lng\":72.8800659999999,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:12'),
(108, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.079540000000033,\"lng\":72.8801859999999,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:17'),
(109, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.079690000000035,\"lng\":72.88030599999989,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:22'),
(110, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.079840000000036,\"lng\":72.88042599999989,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:27'),
(111, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.079990000000038,\"lng\":72.88054599999988,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:32'),
(112, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08014000000004,\"lng\":72.88066599999988,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:37'),
(113, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08029000000004,\"lng\":72.88078599999987,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:42'),
(114, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.080440000000042,\"lng\":72.88090599999987,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:47'),
(115, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.080590000000043,\"lng\":72.88102599999986,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:52'),
(116, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.080740000000045,\"lng\":72.88114599999986,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:56:57'),
(117, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.080890000000046,\"lng\":72.88126599999985,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:02'),
(118, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081040000000048,\"lng\":72.88138599999985,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:07'),
(119, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08119000000005,\"lng\":72.88150599999985,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:13'),
(120, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08134000000005,\"lng\":72.88162599999984,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:17'),
(121, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081490000000052,\"lng\":72.88174599999984,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:22'),
(122, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081640000000053,\"lng\":72.88186599999983,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:27'),
(123, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081790000000055,\"lng\":72.88198599999983,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:32'),
(124, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.081940000000056,\"lng\":72.88210599999982,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:37'),
(125, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082090000000058,\"lng\":72.88222599999982,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:42'),
(126, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08224000000006,\"lng\":72.88234599999981,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:47'),
(127, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08239000000006,\"lng\":72.88246599999981,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:52'),
(128, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082540000000062,\"lng\":72.8825859999998,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:57:57'),
(129, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082690000000063,\"lng\":72.8827059999998,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:02'),
(130, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082840000000065,\"lng\":72.8828259999998,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:07'),
(131, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.082990000000066,\"lng\":72.88294599999979,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:12'),
(132, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083140000000068,\"lng\":72.88306599999979,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:17'),
(133, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08329000000007,\"lng\":72.88318599999978,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:22'),
(134, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08344000000007,\"lng\":72.88330599999978,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:27'),
(135, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083590000000072,\"lng\":72.88342599999977,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:32'),
(136, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083740000000073,\"lng\":72.88354599999977,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:37'),
(137, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.083890000000075,\"lng\":72.88366599999976,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:42'),
(138, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.084040000000076,\"lng\":72.88378599999976,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:47'),
(139, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.084190000000078,\"lng\":72.88390599999975,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:52'),
(140, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08434000000008,\"lng\":72.88402599999975,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:58:57'),
(141, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08449000000008,\"lng\":72.88414599999975,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:02'),
(142, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.084640000000082,\"lng\":72.88426599999974,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:07'),
(143, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.084790000000083,\"lng\":72.88438599999974,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:12'),
(144, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.084940000000085,\"lng\":72.88450599999973,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:17'),
(145, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.085090000000086,\"lng\":72.88462599999973,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:22'),
(146, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.085240000000088,\"lng\":72.88474599999972,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:27'),
(147, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08539000000009,\"lng\":72.88486599999972,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:32'),
(148, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08554000000009,\"lng\":72.88498599999971,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:37'),
(149, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.085690000000092,\"lng\":72.88510599999971,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:42'),
(150, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.085840000000093,\"lng\":72.8852259999997,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:47'),
(151, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.085990000000095,\"lng\":72.8853459999997,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:52'),
(152, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.086140000000096,\"lng\":72.8854659999997,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 13:59:57'),
(153, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.086290000000098,\"lng\":72.88558599999969,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:02'),
(154, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.0864400000001,\"lng\":72.88570599999969,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:07'),
(155, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.0865900000001,\"lng\":72.88582599999968,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:12'),
(156, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.086740000000102,\"lng\":72.88594599999968,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:17'),
(157, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.086890000000103,\"lng\":72.88606599999967,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:22'),
(158, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.087040000000105,\"lng\":72.88618599999967,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:27'),
(159, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.087190000000106,\"lng\":72.88630599999966,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:32'),
(160, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.087340000000108,\"lng\":72.88642599999966,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:37'),
(161, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08749000000011,\"lng\":72.88654599999965,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:42'),
(162, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08764000000011,\"lng\":72.88666599999965,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:48'),
(163, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.087790000000112,\"lng\":72.88678599999965,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:52'),
(164, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.087940000000113,\"lng\":72.88690599999964,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:00:57'),
(165, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.088090000000115,\"lng\":72.88702599999964,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:02'),
(166, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.088240000000116,\"lng\":72.88714599999963,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:07'),
(167, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.088390000000118,\"lng\":72.88726599999963,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:12'),
(168, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08854000000012,\"lng\":72.88738599999962,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:17'),
(169, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08869000000012,\"lng\":72.88750599999962,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:22'),
(170, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.088840000000122,\"lng\":72.88762599999961,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:27'),
(171, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.088990000000123,\"lng\":72.88774599999961,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:32'),
(172, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.089140000000125,\"lng\":72.8878659999996,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:37'),
(173, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.089290000000126,\"lng\":72.8879859999996,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:42'),
(174, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.089440000000128,\"lng\":72.8881059999996,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:47'),
(175, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08959000000013,\"lng\":72.88822599999959,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:52'),
(176, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.08974000000013,\"lng\":72.88834599999959,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:01:57'),
(177, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.089890000000132,\"lng\":72.88846599999958,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:02'),
(178, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.090040000000133,\"lng\":72.88858599999958,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:07'),
(179, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.090190000000135,\"lng\":72.88870599999957,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:12'),
(180, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.090340000000136,\"lng\":72.88882599999957,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:17'),
(181, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.090490000000138,\"lng\":72.88894599999956,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:22'),
(182, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09064000000014,\"lng\":72.88906599999956,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:27'),
(183, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09079000000014,\"lng\":72.88918599999955,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:32'),
(184, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.090940000000142,\"lng\":72.88930599999955,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:37'),
(185, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.091090000000143,\"lng\":72.88942599999955,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:42'),
(186, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.091240000000145,\"lng\":72.88954599999954,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:47'),
(187, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.091390000000146,\"lng\":72.88966599999954,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:52'),
(188, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.091540000000148,\"lng\":72.88978599999953,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:02:57'),
(189, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09169000000015,\"lng\":72.88990599999953,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:02'),
(190, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09184000000015,\"lng\":72.89002599999952,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:07'),
(191, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.091990000000152,\"lng\":72.89014599999952,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:12'),
(192, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.092140000000153,\"lng\":72.89026599999951,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:17'),
(193, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.092290000000155,\"lng\":72.89038599999951,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:22'),
(194, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.092440000000156,\"lng\":72.8905059999995,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:27'),
(195, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.092590000000158,\"lng\":72.8906259999995,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:32'),
(196, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09274000000016,\"lng\":72.8907459999995,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:37'),
(197, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09289000000016,\"lng\":72.89086599999949,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:42'),
(198, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.093040000000162,\"lng\":72.89098599999949,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:47'),
(199, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.093190000000163,\"lng\":72.89110599999948,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:52'),
(200, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.093340000000165,\"lng\":72.89122599999948,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:03:57'),
(201, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.093490000000166,\"lng\":72.89134599999947,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:02'),
(202, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.093640000000168,\"lng\":72.89146599999947,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:07'),
(203, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09379000000017,\"lng\":72.89158599999946,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:12'),
(204, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09394000000017,\"lng\":72.89170599999946,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:17'),
(205, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.094090000000172,\"lng\":72.89182599999945,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:22'),
(206, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.094240000000173,\"lng\":72.89194599999945,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:27'),
(207, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.094390000000175,\"lng\":72.89206599999945,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:32'),
(208, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.094540000000176,\"lng\":72.89218599999944,\"speed\":45}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:37'),
(209, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.094690000000178,\"lng\":72.89230599999944,\"speed\":50}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:42'),
(210, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09484000000018,\"lng\":72.89242599999943,\"speed\":30}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:47'),
(211, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.09499000000018,\"lng\":72.89254599999943,\"speed\":35}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:52'),
(212, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.095140000000182,\"lng\":72.89266599999942,\"speed\":40}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:04:57'),
(213, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":19.097390000000203,\"lng\":72.89446599999935,\"speed\":30}', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 14:09:57'),
(214, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 14:26:46'),
(215, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 14:28:10'),
(216, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:30:25'),
(217, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:33:21'),
(218, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155929,\"lng\":72.5573758,\"speed\":0}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:33:31'),
(219, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155184,\"lng\":72.5571437,\"speed\":0}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:33:55'),
(220, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155238,\"lng\":72.5569671,\"speed\":0}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:34:00'),
(221, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155915,\"lng\":72.5573582,\"speed\":1.5828439593315125}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:34:07'),
(222, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.015573,\"lng\":72.557361,\"speed\":0}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:36:00'),
(223, 1, 1, 1, 13, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:38:36'),
(224, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155892,\"lng\":72.5573745,\"speed\":0}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:38:41'),
(225, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0157814,\"lng\":72.5571867,\"speed\":0}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:38:49'),
(226, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0156553,\"lng\":72.5573201,\"speed\":1.2288254141807557}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-30 14:38:56'),
(227, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 18:07:35'),
(228, NULL, NULL, NULL, 13, 'user_login', NULL, NULL, NULL, '{\"ip\":\"192.168.29.61\"}', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 18:15:37'),
(229, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155902,\"lng\":72.557371,\"speed\":0}', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 18:15:46'),
(230, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155938,\"lng\":72.5573736,\"speed\":0}', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 18:15:57'),
(231, 1, 1, 1, 13, 'transport_location_updated', NULL, NULL, NULL, '{\"driver_id\":7,\"lat\":23.0155893,\"lng\":72.5573702,\"speed\":0}', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 18:16:09'),
(232, 1, 1, 1, 7, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 18:17:40'),
(233, 1, 1, 1, 16, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 18:17:51'),
(234, 1, 1, 1, 16, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 18:18:03'),
(235, NULL, NULL, NULL, 13, 'user_login', NULL, NULL, NULL, '{\"ip\":\"192.168.29.61\"}', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-07-30 18:31:28');
INSERT INTO `activity_logs` (`id`, `tenant_id`, `school_id`, `branch_id`, `user_id`, `event`, `model`, `model_id`, `description`, `properties`, `ip_address`, `user_agent`, `created_at`) VALUES
(236, 1, 1, 1, 7, 'user_created', NULL, NULL, NULL, '{\"user_id\":18}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 19:38:31'),
(237, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', '2026-07-30 19:42:33'),
(238, 1, 1, 1, 16, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 20:09:30'),
(239, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-30 20:09:36'),
(240, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.6', 'Mozilla/5.0 (Linux; Android 4.4.4; SM-T561) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36', '2026-07-30 20:09:57'),
(241, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 07:12:40'),
(242, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '2405:201:2024:4101:414:e78c:2300:e7f0', 'Mozilla/5.0 (Linux; Android 4.4.4; SM-T561) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36', '2026-07-31 07:49:10'),
(243, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 09:17:50'),
(244, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 09:27:00'),
(245, 1, 1, 1, 15, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 09:31:02'),
(246, 1, 1, 1, 17, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 09:31:28'),
(247, 1, 1, 1, 7, 'student_assigned_transport', NULL, NULL, NULL, '{\"student_id\":2,\"driver_id\":7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 09:34:25'),
(248, NULL, NULL, NULL, 13, 'user_login', NULL, NULL, NULL, '{\"ip\":\"192.168.29.14\"}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-07-31 09:41:20'),
(249, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 15:07:36'),
(250, 1, 1, 1, 17, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 15:11:46'),
(251, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 17:48:31'),
(252, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-31 17:48:54'),
(253, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 04:16:02'),
(254, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 04:17:21'),
(255, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 06:39:24'),
(256, NULL, NULL, NULL, 8, 'login_failed', NULL, NULL, NULL, '{\"email\":\"hetshah6315@gmail.com\",\"attempts\":1}', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-08-01 06:48:34'),
(257, 1, 1, 1, 8, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-08-01 06:48:57'),
(258, 1, 1, 1, 8, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 07:15:37'),
(259, 1, 1, 1, 8, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 07:19:34'),
(260, 1, 1, 1, 8, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 07:21:25'),
(261, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 07:23:50'),
(262, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 07:24:59'),
(263, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.14', 'Dart/3.9 (dart:io)', '2026-08-01 07:32:23'),
(264, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 07:53:57'),
(265, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 08:08:25'),
(266, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 08:49:41'),
(267, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 10:07:27'),
(268, 1, 1, 1, 7, 'student_created', NULL, NULL, NULL, '{\"student_id\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 10:34:15'),
(269, 1, 1, 1, 7, 'attendance_marked', NULL, NULL, NULL, '{\"class\":\"Class A\",\"section\":\"\",\"date\":\"2026-08-01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 10:35:02'),
(270, 1, 1, 1, 7, 'attendance_marked', NULL, NULL, NULL, '{\"class\":\"Class A\",\"section\":\"\",\"date\":\"2026-08-01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 10:35:18'),
(271, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 10:51:56'),
(272, 1, 1, 1, 7, 'class_created', NULL, NULL, NULL, '{\"class\":\"Class A\",\"section\":\"\",\"assigned_count\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 10:52:30'),
(273, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 11:03:30'),
(274, 1, 1, 1, 15, 'logout', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 11:28:51'),
(275, 1, 1, 1, 17, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 11:29:06'),
(276, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-01 13:10:32'),
(277, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 13:21:06'),
(278, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 13:25:01'),
(279, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 13:41:09'),
(280, 1, 1, 1, 18, 'login_success', NULL, NULL, NULL, '[]', '192.168.29.61', 'Dart/3.9 (dart:io)', '2026-08-01 14:27:01'),
(281, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 05:47:53'),
(282, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:21:08'),
(283, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:22:21'),
(284, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:23:06'),
(285, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:25:46'),
(286, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:26:39'),
(287, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:27:44'),
(288, 1, 1, 1, 7, 'student_updated', NULL, NULL, NULL, '{\"student_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:27:50'),
(289, 1, 1, 1, 7, 'fee_invoice_created', NULL, NULL, NULL, '{\"invoice_id\":\"1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 07:45:47'),
(290, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 08:13:12'),
(291, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-02 10:13:55'),
(292, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 06:57:58'),
(293, 1, 1, 1, 7, 'settings_updated', NULL, NULL, NULL, '{\"tenant_id\":1,\"school_id\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 07:13:52'),
(294, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 09:12:10'),
(295, 1, 1, 1, 7, 'attendance_marked', NULL, NULL, NULL, '{\"class\":\"Class A\",\"section\":\"\",\"date\":\"2026-08-03\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 09:12:27'),
(296, 1, 1, 1, 7, 'bulk_exam_marks_saved', NULL, NULL, NULL, '{\"class\":\"Class A\",\"term\":\"First Term Evaluation\",\"count\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 10:13:09'),
(297, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 11:16:16'),
(298, 1, 1, 1, 7, 'student_created', NULL, NULL, NULL, '{\"student_id\":6}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-03 12:09:29'),
(299, NULL, NULL, NULL, 7, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.local\",\"attempts\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 06:53:13'),
(300, NULL, NULL, NULL, 7, 'login_failed', NULL, NULL, NULL, '{\"email\":\"admin@psnf.local\",\"attempts\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 06:53:21'),
(301, 1, 1, 1, 7, 'login_success', NULL, NULL, NULL, '[]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 06:53:27'),
(302, 1, 1, 1, 15, 'user_login', NULL, NULL, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 06:54:06');

-- --------------------------------------------------------

--
-- Table structure for table `admins_merged_backup`
--

CREATE TABLE `admins_merged_backup` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 1, 1, 'holiday', 'today holiday due to rain', 'parents', '2026-07-31 10:05:56', 7, '2026-07-31 10:05:56', '2026-07-31 10:05:56');

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
  `remarks` text DEFAULT NULL,
  `use_bus_transport` tinyint(1) NOT NULL DEFAULT 0,
  `medical_certificate` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `confidence` float DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `date`, `status`, `remarks`, `use_bus_transport`, `medical_certificate`, `created_by`, `created_at`, `updated_at`, `employee_id`, `attendance_date`, `check_in`, `confidence`, `image_path`, `ip_address`, `user_agent`, `user_id`) VALUES
(1, NULL, NULL, 0, NULL, '2026-07-29', 'present', NULL, 0, NULL, NULL, '2026-07-29 09:38:39', '2026-07-29 09:38:39', 5, '2026-07-29', '2026-07-29 15:08:39', 0.9827, 'uploads/attendance/att_5_1785317919.jpg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL),
(2, NULL, NULL, 0, NULL, '2026-07-29', 'present', NULL, 0, NULL, NULL, '2026-07-29 09:51:14', '2026-07-29 09:51:14', 5, '2026-07-29', '2026-07-29 15:21:14', 0.9637, 'uploads/attendance/att_5_1785318674.jpg', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL),
(3, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-07-29 05:07:02', '2026-07-29 10:37:02', NULL, '2026-07-29', '2026-07-29 16:07:02', 0.8947, 'uploads/attendance/att_user8_20260729_160702_445186.jpg', '127.0.0.1', 'Unknown Browser', 8),
(4, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-07-29 05:38:12', '2026-07-29 11:08:12', NULL, '2026-07-29', '2026-07-29 16:38:12', 0.8965, 'uploads/attendance/att_user8_20260729_163812_322454.jpg', '127.0.0.1', 'Unknown Browser', 8),
(5, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-07-29 05:38:45', '2026-07-29 11:08:45', NULL, '2026-07-29', '2026-07-29 16:38:45', 0.8654, 'uploads/attendance/att_user11_20260729_163845_544835.jpg', '127.0.0.1', 'Unknown Browser', 11),
(6, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-07-29 05:58:10', '2026-07-29 11:28:10', NULL, '2026-07-29', '2026-07-29 16:58:10', 0.8903, 'uploads/attendance/att_user12_20260729_165810_124524.jpg', '127.0.0.1', 'Unknown Browser', 12),
(9, 1, 1, 1, 4, '2026-07-31', 'present', NULL, 1, NULL, 15, '2026-07-30 09:49:14', '2026-07-30 09:49:14', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(10, 1, 1, 1, 3, '2026-08-01', 'present', NULL, 1, NULL, 16, '2026-07-30 18:48:05', '2026-08-01 14:09:24', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(11, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-07-31 02:28:12', '2026-07-31 07:58:12', NULL, '2026-07-31', '2026-07-31 13:28:12', 0.9087, 'uploads/attendance/att_user18_20260731_132812_114440.jpg', '127.0.0.1', 'Unknown Browser', 18),
(12, 1, 1, 1, 4, '2026-08-01', 'present', '', 1, NULL, 15, '2026-07-31 09:27:28', '2026-08-01 14:34:36', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(13, 1, 1, 1, 2, '2026-08-01', 'present', NULL, 1, NULL, 17, '2026-07-31 09:31:42', '2026-08-01 14:09:24', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(14, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-01 01:53:26', '2026-08-01 07:23:26', NULL, '2026-08-01', '2026-08-01 12:53:26', 0.8359, 'uploads/attendance/att_user18_20260801_125326_313675.jpg', '127.0.0.1', 'Unknown Browser', 18),
(15, 1, 1, 1, 5, '2026-08-01', 'absent', 'Marked by teacher', 0, NULL, 18, '2026-08-01 13:51:41', '2026-08-01 13:59:01', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(16, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-01 08:24:50', '2026-08-01 13:54:50', NULL, '2026-08-01', '2026-08-01 19:24:50', 0.8398, 'uploads/attendance/att_user18_20260801_192450_688209.jpg', '127.0.0.1', 'Unknown Browser', 18),
(17, 1, 1, 1, 5, '2026-08-03', 'present', '', 0, NULL, 7, '2026-08-03 09:12:27', '2026-08-03 09:12:27', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(18, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 03:55:36', '2026-08-03 09:25:36', NULL, '2026-08-03', '2026-08-03 14:55:36', 0.7738, 'uploads/attendance/att_user18_20260803_145536_260386.jpg', '127.0.0.1', 'Unknown Browser', 18),
(19, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 03:56:27', '2026-08-03 09:26:27', NULL, '2026-08-03', '2026-08-03 14:56:27', 0.8145, 'uploads/attendance/att_user11_20260803_145627_359892.jpg', '127.0.0.1', 'Unknown Browser', 11),
(20, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 03:56:57', '2026-08-03 09:26:57', NULL, '2026-08-03', '2026-08-03 14:56:57', 0.8201, 'uploads/attendance/att_user12_20260803_145657_162127.jpg', '127.0.0.1', 'Unknown Browser', 12),
(21, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 03:59:13', '2026-08-03 09:29:13', NULL, '2026-08-03', '2026-08-03 14:59:13', 0.8169, 'uploads/attendance/att_user8_20260803_145913_268025.jpg', '127.0.0.1', 'Unknown Browser', 8),
(22, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 04:05:41', '2026-08-03 09:35:41', NULL, '2026-08-03', '2026-08-03 15:05:41', 0.6895, 'uploads/attendance/att_user18_20260803_150541_017216.jpg', '127.0.0.1', 'Unknown Browser', 18),
(23, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 04:09:26', '2026-08-03 09:39:26', NULL, '2026-08-03', '2026-08-03 15:09:26', 0.7241, 'uploads/attendance/att_user8_20260803_150926_228545.jpg', '127.0.0.1', 'Unknown Browser', 8),
(24, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 04:19:07', '2026-08-03 09:49:07', NULL, '2026-08-03', '2026-08-03 15:19:07', 0.5848, 'uploads/attendance/att_user18_20260803_151907_695414.jpg', '127.0.0.1', 'Unknown Browser', 18),
(25, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 04:19:34', '2026-08-03 09:49:34', NULL, '2026-08-03', '2026-08-03 15:19:33', 0.8038, 'uploads/attendance/att_user8_20260803_151933_991774.jpg', '127.0.0.1', 'Unknown Browser', 8),
(26, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 04:29:19', '2026-08-03 09:59:19', NULL, '2026-08-03', '2026-08-03 15:29:19', 0.5763, 'uploads/attendance/att_user18_20260803_152919_153471.jpg', '127.0.0.1', 'Unknown Browser', 18),
(27, NULL, NULL, 0, NULL, NULL, 'present', NULL, 0, NULL, NULL, '2026-08-03 04:29:33', '2026-08-03 09:59:33', NULL, '2026-08-03', '2026-08-03 15:29:33', 0.722, 'uploads/attendance/att_user8_20260803_152933_097875.jpg', '127.0.0.1', 'Unknown Browser', 8);

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

-- --------------------------------------------------------

--
-- Table structure for table `certificate_types`
--

CREATE TABLE `certificate_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `conference_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `template_path` varchar(255) DEFAULT NULL,
  `is_custom` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cert_settings`
--

CREATE TABLE `cert_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cert_settings`
--

INSERT INTO `cert_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'default_font_path', 'C:/Windows/Fonts/arial.ttf', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(2, 'file_name_format', '{conference}_{year}_{category}_{name}_{id}', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(3, 'download_format', 'pdf', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(4, 'smtp_host', '', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(5, 'smtp_port', '587', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(6, 'smtp_from_email', 'no-reply@psnf.edu', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(7, 'smtp_from_name', 'PSNF Certificate System', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(8, 'email_subject_template', 'Your {{certificate_type}} Certificate - PSNF {{year}}', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(9, 'email_message_template', 'Dear {{name}},\n\nPlease find attached your {{certificate_type}} certificate.\n\nRegards,\nPSNF Team', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(10, 'bulk_email_delay_min_seconds', '1', '2026-07-28 17:02:30', '2026-07-28 17:02:30'),
(11, 'bulk_email_delay_max_seconds', '3', '2026-07-28 17:02:30', '2026-07-28 17:02:30');

-- --------------------------------------------------------

--
-- Table structure for table `cert_users`
--

CREATE TABLE `cert_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','sub_admin') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `academic_year_id` int(10) UNSIGNED DEFAULT NULL,
  `main_group_id` int(10) UNSIGNED DEFAULT NULL,
  `curriculum_template_id` int(10) UNSIGNED DEFAULT NULL,
  `class_teacher_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `tenant_id`, `school_id`, `branch_id`, `name`, `section`, `created_at`, `updated_at`, `academic_year_id`, `main_group_id`, `curriculum_template_id`, `class_teacher_id`) VALUES
(1, 1, 1, 1, 'Class A', '', '2026-07-28 11:42:19', '2026-08-03 10:17:57', 3, 3, NULL, 11),
(2, 1, 1, 1, 'Class B', '', '2026-07-28 11:42:33', '2026-08-03 12:06:40', 3, 3, 3, 12),
(3, 1, 1, 1, 'functional a', '', '2026-08-03 10:29:46', '2026-08-03 11:26:47', 3, 3, 2, 12);

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

-- --------------------------------------------------------

--
-- Table structure for table `conferences`
--

CREATE TABLE `conferences` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conferences`
--

INSERT INTO `conferences` (`id`, `name`, `year`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PSNF Academic Year 2026', 2026, 'Default certificate event for PSNF school', NULL, '2026-07-28 17:02:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `conference_admins`
--

CREATE TABLE `conference_admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `conference_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `conference_id` int(10) UNSIGNED DEFAULT NULL,
  `contact_name` varchar(190) NOT NULL,
  `contact_email` varchar(190) DEFAULT NULL,
  `mobile_number` varchar(30) DEFAULT NULL,
  `category` varchar(190) NOT NULL,
  `message_text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_sections`
--

CREATE TABLE `curriculum_sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `curriculum_template_id` int(10) UNSIGNED NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `curriculum_sections`
--

INSERT INTO `curriculum_sections` (`id`, `curriculum_template_id`, `section_name`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Academic', 1, '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(2, 1, 'Life Skills', 2, '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(3, 1, 'Co-Curricular', 3, '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(4, 2, 'Academic', 1, '2026-08-03 10:18:55', '2026-08-03 10:18:55'),
(5, 2, 'Life Skills', 2, '2026-08-03 10:18:55', '2026-08-03 10:18:55'),
(6, 2, 'Co-Curricular', 3, '2026-08-03 10:18:55', '2026-08-03 10:18:55'),
(7, 2, 'Vocational', 4, '2026-08-03 10:18:55', '2026-08-03 10:18:55'),
(8, 1, 'asdad', 4, '2026-08-03 11:25:15', '2026-08-03 11:25:15'),
(9, 3, 'Academic', 1, '2026-08-03 11:59:15', '2026-08-03 11:59:15'),
(10, 3, 'Life Skills', 2, '2026-08-03 11:59:15', '2026-08-03 11:59:15'),
(11, 3, 'Co-Curricular', 3, '2026-08-03 11:59:15', '2026-08-03 11:59:15'),
(12, 3, 'Vocational', 4, '2026-08-03 11:59:15', '2026-08-03 11:59:15'),
(13, 3, 'asdad', 5, '2026-08-03 11:59:19', '2026-08-03 11:59:19'),
(14, 3, 'asdads', 6, '2026-08-03 11:59:53', '2026-08-03 11:59:53');

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_subjects`
--

CREATE TABLE `curriculum_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `curriculum_section_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `default_grade` varchar(10) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `sequence` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `assessment_type` varchar(50) NOT NULL DEFAULT 'Marks',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `curriculum_subjects`
--

INSERT INTO `curriculum_subjects` (`id`, `curriculum_section_id`, `subject_id`, `is_required`, `default_grade`, `visible`, `sequence`, `assessment_type`, `created_at`, `updated_at`) VALUES
(1, 1, 13, 1, NULL, 1, 1, 'Marks', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(2, 1, 14, 1, NULL, 1, 2, 'Marks', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(3, 1, 15, 1, NULL, 1, 3, 'Marks', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(4, 1, 16, 1, NULL, 1, 4, 'Marks', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(5, 2, 17, 1, NULL, 1, 1, 'Rating', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(6, 2, 18, 1, NULL, 1, 2, 'Rating', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(7, 2, 19, 1, NULL, 1, 3, 'Rating', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(8, 2, 20, 1, NULL, 1, 4, 'Rating', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(9, 3, 21, 1, NULL, 1, 1, 'Grade', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(10, 3, 22, 1, NULL, 1, 2, 'Grade', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(11, 3, 23, 1, NULL, 1, 3, 'Grade', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(14, 9, 17, 1, NULL, 1, 1, 'Marks', '2026-08-03 12:01:54', '2026-08-03 12:01:54'),
(15, 11, 26, 1, NULL, 1, 1, 'Marks', '2026-08-03 12:02:07', '2026-08-03 12:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_templates`
--

CREATE TABLE `curriculum_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `main_group_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `curriculum_templates`
--

INSERT INTO `curriculum_templates` (`id`, `tenant_id`, `academic_year_id`, `main_group_id`, `name`, `version`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 3, 'Functional Curriculum 2026', 1, 'Core academic and life skills framework for Functional classes', 1, '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(2, 1, 3, 1, 'test', 1, '', 1, '2026-08-03 10:18:55', '2026-08-03 10:18:55'),
(3, 1, 3, 3, 'Het Shah b', 1, 'bv', 1, '2026-08-03 11:59:15', '2026-08-03 11:59:15');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `head_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `tenant_id`, `name`, `code`, `description`, `head_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Academics', 'ACAD', 'Academic and Teaching staff', NULL, 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49'),
(2, 1, 'Administration', 'ADMIN', 'School management and administrative staff', NULL, 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49'),
(3, 1, 'Support', 'SUPP', 'Operations, IT, and Transport Support staff', NULL, 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49');

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `title` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `tenant_id`, `title`, `code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Teacher', 'TCH', 'Class teacher / Subject teacher', 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49'),
(2, 1, 'Administrator', 'ADMIN', 'System / School Administrator', 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49'),
(3, 1, 'Staff Member', 'STAFF', 'General administration staff', 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49'),
(4, 1, 'Driver', 'DRV', 'Transport driver / operator', 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49'),
(5, 1, 'Therapist', 'THER', 'Therapy specialist', 1, '2026-07-29 07:03:49', '2026-07-29 07:03:49');

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `type` varchar(100) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `qr_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `signature_path` varchar(255) DEFAULT NULL,
  `watermark_path` varchar(255) DEFAULT NULL,
  `header_text` text DEFAULT NULL,
  `footer_text` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_templates`
--

INSERT INTO `document_templates` (`id`, `name`, `type`, `logo_path`, `qr_enabled`, `signature_path`, `watermark_path`, `header_text`, `footer_text`, `content`, `created_at`, `updated_at`) VALUES
(1, 'Standard Student ID Card', 'id_card', NULL, 1, NULL, NULL, 'PSNF Special School', 'Valid for Academic Year 2026', NULL, '2026-07-28 11:32:31', NULL),
(2, 'Sports Participation Template', 'certificate', NULL, 0, NULL, NULL, 'Sports Day 2026', 'Congratulations to all participants', NULL, '2026-07-28 11:32:31', NULL),
(3, 'Official Academic Fee Receipt', 'receipt', NULL, 1, NULL, NULL, 'Billing & Accounts Department', 'Non-refundable Fee Receipt', NULL, '2026-07-28 11:32:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `document_type` enum('student','staff','parent','driver') NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `file_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(255) DEFAULT NULL
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

-- --------------------------------------------------------

--
-- Table structure for table `driver_bus_manifest`
--

CREATE TABLE `driver_bus_manifest` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `route_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('scheduled','boarded','dropped','absent_notified','skipped') DEFAULT 'scheduled',
  `boarded_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_documents`
--

CREATE TABLE `driver_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(191) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
  `expiry_date` date DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` int(10) UNSIGNED NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(10,8) NOT NULL,
  `speed` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_locations`
--

INSERT INTO `driver_locations` (`id`, `trip_id`, `lat`, `lng`, `speed`, `created_at`) VALUES
(1, 1, 23.01559020, 72.55737100, 0.00, '2026-07-30 18:15:46'),
(2, 1, 23.01559380, 72.55737360, 0.00, '2026-07-30 18:15:57'),
(3, 1, 23.01558930, 72.55737020, 0.00, '2026-07-30 18:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `driver_trips`
--

CREATE TABLE `driver_trips` (
  `id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_trips`
--

INSERT INTO `driver_trips` (`id`, `driver_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 'completed', '2026-07-30 18:15:43', '2026-07-30 18:31:33'),
(2, 7, 'completed', '2026-07-30 18:31:34', '2026-07-30 18:39:56'),
(3, 7, 'completed', '2026-07-30 18:46:54', '2026-07-30 18:51:42'),
(4, 7, 'completed', '2026-07-30 18:51:43', '2026-07-30 18:57:06'),
(5, 7, 'completed', '2026-07-30 18:58:52', '2026-07-30 19:04:19'),
(6, 7, 'completed', '2026-07-30 19:05:12', '2026-07-31 09:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `participant_id` int(10) UNSIGNED NOT NULL,
  `to_email` varchar(190) NOT NULL,
  `subject_text` varchar(255) NOT NULL,
  `message_text` text DEFAULT NULL,
  `status` enum('sent','failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_schedules`
--

CREATE TABLE `email_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `conference_id` int(10) UNSIGNED NOT NULL,
  `certificate_type_id` int(10) UNSIGNED DEFAULT NULL,
  `template_key` varchar(120) NOT NULL,
  `subject_template` varchar(255) NOT NULL,
  `message_template` text NOT NULL,
  `cc_template` text DEFAULT NULL,
  `bcc_template` text DEFAULT NULL,
  `status` enum('active','paused') NOT NULL DEFAULT 'active',
  `interval_seconds` int(11) NOT NULL DEFAULT 60,
  `last_sent_at` datetime DEFAULT NULL,
  `last_participant_id` int(10) UNSIGNED DEFAULT NULL,
  `sent_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `tenant_id` int(11) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `emp_code` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT 1,
  `shift_template_id` int(11) DEFAULT NULL,
  `employment_type` varchar(30) DEFAULT 'full_time',
  `joining_date` date NOT NULL,
  `salary_basic` decimal(10,2) DEFAULT 0.00,
  `status` varchar(30) DEFAULT 'active',
  `photo` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `min_clock_in` time DEFAULT '09:00:00',
  `max_clock_out` time DEFAULT '17:00:00',
  `employee_code` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `current_speed` decimal(5,2) DEFAULT 0.00,
  `route_status` enum('inactive','en_route','completed') DEFAULT 'inactive',
  `last_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `tenant_id`, `user_id`, `emp_code`, `first_name`, `last_name`, `email`, `phone`, `department_id`, `designation_id`, `branch_id`, `shift_template_id`, `employment_type`, `joining_date`, `salary_basic`, `status`, `photo`, `created_at`, `updated_at`, `min_clock_in`, `max_clock_out`, `employee_code`, `name`, `department`, `designation`, `license_number`, `license_expiry`, `current_latitude`, `current_longitude`, `current_speed`, `route_status`, `last_updated_at`) VALUES
(1, 1, 7, 'EMP-101', 'System', 'Administrator', 'admin@psnf.local', NULL, 2, 2, 1, NULL, 'full_time', '2026-07-29', 35000.00, 'active', NULL, '2026-07-29 12:33:49', '2026-07-29 07:21:15', '09:00:00', '17:00:00', 'EMP-101', 'System Administrator', 'Administration', 'Administrator', NULL, NULL, NULL, NULL, 0.00, 'inactive', NULL),
(2, 1, 8, 'EMP-102', 'Het', 'Shah', 'hetshah6315@gmail.com', '+919427961426', 1, 1, 1, NULL, 'full_time', '2026-07-29', 35000.00, 'active', NULL, '2026-07-29 12:34:11', '2026-07-29 08:27:19', '09:00:00', '17:00:00', 'EMP-102', 'Het Shah', 'Academics', 'Teacher', NULL, NULL, NULL, NULL, 0.00, 'inactive', NULL),
(5, 1, 11, '105', 'heeeet', 'ssss', '105@psnf.edu', NULL, NULL, NULL, 1, NULL, 'full_time', '2026-07-29', 10000.00, 'active', NULL, '2026-07-29 15:08:24', '2026-07-29 12:02:07', '09:00:00', '17:00:00', '105', 'heeeet ssss', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'inactive', NULL),
(6, 1, 12, 'EMP-104', 'akshat', 'shah', 'akshat@gmail.com', '94279614262', NULL, NULL, 1, NULL, 'full_time', '2026-07-29', 15000.00, 'active', NULL, '2026-07-29 16:55:56', '2026-07-29 12:01:49', '09:00:00', '17:00:00', 'EMP-104', 'akshat shah', NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'inactive', NULL),
(7, 1, 13, 'EMP-105', 'test', 'driver', 'driver@gmail.com', '54654651654694', NULL, 4, 1, NULL, 'full_time', '2026-07-29', 0.00, 'active', NULL, '2026-07-29 18:05:30', '2026-07-31 09:41:45', '09:00:00', '17:00:00', 'EMP-105', 'test driver', NULL, 'Driver', NULL, NULL, 23.01558930, 72.55737020, 0.00, 'completed', '2026-07-30 18:16:09'),
(8, 1, 18, 'EMP-106', 'hiral', 'shah', 'hiral@gmail.com', '9413548546312', 1, 1, 1, NULL, 'full_time', '2026-07-31', 1200.00, 'active', NULL, '2026-07-31 01:08:31', '2026-08-01 08:09:49', '13:00:00', '17:00:00', 'EMP-106', 'hiral shah', 'Academics', 'Teacher', NULL, NULL, NULL, NULL, 0.00, 'inactive', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_profiles`
--

CREATE TABLE `employee_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT 'male',
  `emergency_contact` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_acc_no` varchar(50) DEFAULT NULL,
  `pan_no` varchar(30) DEFAULT NULL,
  `aadhaar_no` varchar(30) DEFAULT NULL,
  `reporting_manager_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `face_embeddings`
--

CREATE TABLE `face_embeddings` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `embedding_vector` text NOT NULL,
  `capture_angle` varchar(30) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `embedding` longtext NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `face_embeddings`
--

INSERT INTO `face_embeddings` (`id`, `employee_id`, `user_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(1, 5, NULL, '', '', '2026-07-29 15:08:24', '[0.03696968927077239,0.037894717491947325,0.038146505132577724,0.038769605009659806,0.03931166666871339,0.03996566406036154,0.04069771833091341,0.04132081820799549,0.04186287986704907,0.04270686987019549,0.0430589389177508,0.04210301318200981,0.04476318077381526,0.045034211603342054,0.04530524243286884,0.04495317338531354,0.043790993188302665,0.04267597235562944,0.04053862323398117,0.03837037659776684,0.036554199009107814,0.034656983202420284,0.033843890713839905,0.03401464013644179,0.04870911862089581,0.055646965795122606,0.05727315077228336,0.05990242084952274,0.06361581424486931,0.0665161151516355,0.06741024585824439,0.06732920764021588,0.0376545841769866,0.038034569399983155,0.038769605009659806,0.037925615006513376,0.03861050991272758,0.03923360978980967,0.03969463323083474,0.04061966145200969,0.04143275394059005,0.04197481559964364,0.042977900699722286,0.042214948914604374,0.044844218991843765,0.04549821638349192,0.045657311480424144,0.046199373139477716,0.046230270654043774,0.04592834230995094,0.04541717816546341,0.04376009567373661,0.04053862323398117,0.03861050991272758,0.036744191620606104,0.043039695728854396,0.05518594235409753,0.05735418899031186,0.0641608572430477,0.0665190964907603,0.06749128407627289,0.06822333834682476,0.0688773357384729,0.06874913815610673,0.03768548169155264,0.0384175359621045,0.042393287200433,0.03917181476067756,0.03912167405721511,0.039504640619336465,0.0397447739342972,0.040046702278390044,0.040348630622482895,0.04089069228153647,0.04116172311106326,0.040096842981852496,0.04275701057365796,0.0397255307454008,0.021527978789313004,0.015786190665787937,0.017253280546016454,0.03025110603763277,0.04324893152924908,0.04116172311106326,0.03999656157492759,0.04243583904066871,0.03891243825682043,0.04512690414704022,0.05894947645290655,0.061609644044712,0.06795528885642277,0.06822631968594955,0.06841631229744782,0.06857540739438005,0.06822333834682476,0.06696087674288898,0.03575736837029905,0.036810594173840167,0.03634957073281509,0.038187972849495315,0.04031773310791684,0.040348630622482895,0.040046702278390044,0.04058876393744363,0.04116172311106326,0.041784822988145356,0.04251687725869722,0.0434110079653061,0.03269038350337389,0.010365574075252118,0.006571142461877046,0.006923211509432346,0.007686163294550263,0.00985440993076459,0.026026548497798677,0.041734682284682904,0.04053862323398117,0.044604085676883024,0.04045758501595267,0.04523585854050999,0.06009539480014582,0.06218260321833164,0.06865644561240856,0.06868734312697462,0.06868734312697462,0.06830437656485328,0.06689908171375686,0.06387925621116936,0.037573545958958086,0.03936180737217585,0.04486346218074017,0.04031773310791684,0.04061966145200969,0.04085979476697042,0.04143275394059005,0.04197481559964364,0.04251687725869722,0.0430589389177508,0.04422410045388648,0.040108497307522153,0.007976437312973457,0.009074112172556959,0.012018862135365543,0.014047256863544045,0.015433308525744054,0.015581833420324735,0.02370218810377692,0.029549949281646958,0.04096874916044019,0.04603729670342071,0.04099964667500624,0.044422766051929616,0.0606374564591994,0.06253467226588694,0.06857540739438005,0.06884643822390685,0.06884643822390685,0.0688773357384729,0.06874913815610673,0.06764279030997837,0.038579612398161524,0.041641989740984736,0.045707452183886596,0.0411308255964972,0.04085979476697042,0.04089069228153647,0.041672887255550795,0.04197481559964364,0.04251687725869722,0.04313997713577931,0.04449513128341327,0.02783107176078805,0.011102506900735463,0.015443878728095599,0.01706139071871149,0.017905380721857916,0.02018556309066681,0.03130080844039003,0.052456661900762744,0.02836852589573968,0.04108068489303475,0.04661025587704034,0.04170080343099205,0.0433695402483885,0.0606374564591994,0.06307673392494051,0.06814528146792104,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.06878003567067278,0.06874913815610673,0.039342564183279444,0.04275701057365796,0.04557925460152042,0.04116172311106326,0.04124276132909177,0.04124276132909177,0.04197481559964364,0.04197481559964364,0.04275701057365796,0.0434110079653061,0.04476616211294005,0.03352271918085066,0.011884701874749782,0.011957067106233435,0.013845609926376113,0.01781268817815975,0.015374494835736741,0.024458364118156665,0.0580111677210848,0.03763073346398823,0.04077875654894192,0.046850389192001074,0.04181273916358662,0.042254519415715286,0.0609084872887262,0.0636187955839941,0.06803334573532646,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.06874913815610673,0.06878003567067278,0.03988462584233302,0.0443831955508187,0.04522718555396513,0.041401856426023995,0.04148289464405251,0.04151379215861856,0.04197481559964364,0.042326884647198935,0.04275701057365796,0.0434110079653061,0.04476616211294005,0.034799816449580905,0.017280112598139608,0.012068731807998473,0.0109683466401197,0.014209333299601066,0.013117350087437626,0.02351978435550539,0.0441829037677984,0.035160558483681055,0.04026759240445438,0.046420263265542064,0.04146067011603131,0.041042198515241955,0.060366425629672614,0.0636187955839941,0.06779321242036575,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.0688773357384729,0.06874913815610673,0.040426687501386616,0.045707452183886596,0.045277326257427586,0.04143275394059005,0.04151379215861856,0.04151379215861856,0.041784822988145356,0.042326884647198935,0.042597915476725735,0.043682038794832886,0.04476616211294005,0.04395306962435969,0.023988396659757213,0.015390756685508348,0.013072087938906652,0.019235600033175402,0.027601779679008388,0.025346803177345487,0.054121333255716296,0.04186884254529866,0.03945449991587401,0.04579716338845997,0.04122053680107058,0.04038820112359381,0.059201264093536946,0.0638898264135209,0.06592689412824426,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.04015565667185981,0.04692275442348473,0.044925257209872275,0.04151379215861856,0.04148289464405251,0.04148289464405251,0.04172302795901324,0.04194391808507759,0.042838048791686456,0.043682038794832886,0.044464233768847214,0.04519628803939907,0.02220609792478903,0.016276214405572375,0.013275632091881272,0.017506152309964954,0.029278376390461117,0.03480496603534191,0.048834876925796245,0.04615519511426486,0.03864140742729364,0.045014968414445654,0.040678475142016994,0.039304077805486635,0.05811714077542978,0.06380878819549238,0.06530379425116216,0.06841631229744782,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.06884643822390685,0.040205797375322275,0.04822776786765623,0.0443831955508187,0.04143275394059005,0.0417539254735793,0.04151379215861856,0.041784822988145356,0.042214948914604374,0.042597915476725735,0.043682038794832886,0.044545271986875724,0.04026488209615911,0.021588960725956532,0.013144453170390305,0.009931653717179726,0.013639355465106223,0.024131500937747356,0.048242674563280206,0.04495940709439266,0.04157856852687546,0.0370962606681614,0.044170978411299223,0.03983448513887057,0.03802996187588119,0.05625082248330829,0.06364969309856015,0.06587675342478179,0.0688773357384729,0.06890823325303896,0.06878003567067278,0.06890823325303896,0.0688773357384729,0.040205797375322275,0.04888176525930437,0.04360100057680438,0.041401856426023995,0.04151379215861856,0.04148289464405251,0.04151379215861856,0.042055853817672156,0.04102485254215224,0.0337517402318008,0.02702638122792301,0.019387377297710406,0.017196906133474882,0.01071683003031884,0.009993448746311834,0.01189662723124896,0.020697811358472443,0.03424067984826713,0.028206178428853132,0.03194775903047048,0.04102539460381129,0.04772554774054309,0.03910243086831871,0.03691494104320798,0.05400153762906545,0.06302659322147806,0.06452159927714783,0.0686063049089461,0.06874913815610673,0.06866809993807822,0.06785500744949784,0.06255499957810146,0.040127740496418554,0.04969485774788475,0.043109079621213256,0.04124276132909177,0.04105276871759349,0.04105276871759349,0.04124276132909177,0.03787872667300525,0.028733604423112263,0.026266410781929893,0.024450233193270863,0.01929007722991029,0.019278422904240637,0.012267939467700663,0.008573518230420976,0.009610482184190479,0.02385748876909577,0.031642578316423316,0.025037828031684944,0.02392578853813652,0.02929328308608509,0.04247053098684814,0.05277214178633193,0.036154970597214856,0.05110123672229927,0.061942469903370906,0.06200128359337821,0.06440966354455328,0.0628954142999871,0.0612190886193639,0.060127376438029984,0.04042235100811417,0.04039877132594534,0.04953278131182773,0.04229598713263288,0.04090993547043288,0.04039877132594534,0.04015863801098461,0.03953851947302732,0.027817249188482187,0.02529422319641729,0.022866600056345825,0.023648795030360138,0.01850788225589597,0.020826551002497667,0.015626553507196658,0.006067838211445794,0.008317123065688633,0.017838978168623846,0.02430848406942835,0.02393446152468138,0.021282966919420783,0.022232929976912185,0.02540724305232996,0.05031985484077353,0.04619828901615962,0.046574750838372335,0.05950319243762978,0.057362861976856715,0.05524475604410485,0.05313830443702263,0.05278623538946733,0.0532087724526996,0.04426583920163361,0.04071994285893459,0.04763556550514019,0.04031773310791684,0.038691548130756086,0.037576527298082876,0.03606227805351669,0.028172028544332758,0.023536859297765576,0.022514531008790525,0.022165443300360015,0.020218086790210022,0.01686543542896362,0.021237433740060284,0.022885843245242225,0.008381899433945536,0.0059897813325420785,0.008353983258504278,0.03230850106457064,0.02334225916216534,0.019877672068324373,0.019042355051722804,0.020428406713922813,0.04044051007369247,0.043628374690586585,0.04538763580504498,0.05616978426527978,0.05449345858465659,0.04633163618428679,0.0450073795512189,0.04425310075264584,0.045046950052329816,0.03939785447250291,0.039635819540827434,0.04283506745256167,0.036283168179581035,0.03432415734376138,0.03231798714360408,0.03120296631093086,0.025165754583221588,0.020679110231235094,0.020922224885320626,0.021174012525951014,0.016737237846597447,0.01695812797266178,0.019846774553758315,0.021271312593751136,0.021120890483363765,0.012394510865089676,0.01822058957659757,0.03348531692637596,0.021487595195713513,0.019219067152574272,0.015290475278583439,0.02149626818225837,0.027361646364047653,0.028920344664656223,0.04496943523508515,0.05208507863348151,0.0479887186760136,0.0360633621768348,0.02565333904554029,0.026799257392779562,0.023860470108220566,0.02140194945358305]', 'uploads/faces/emp_105_1785317904.jpg'),
(7, NULL, 8, '', '', '2026-07-29 11:01:02', '[0.09512755274772644, -0.043628644198179245, -0.013926219195127487, -0.017412977293133736, -0.00364057463593781, -0.02251613698899746, -0.05435377359390259, -0.012504969723522663, 0.04088321328163147, -0.015495349653065205, -0.002831985941156745, 0.03618039935827255, -0.015339422039687634, 0.02550644241273403, -0.03152307868003845, -0.05528739467263222, -0.026437005028128624, -0.01441655121743679, -0.01725316420197487, -0.009291050024330616, -0.08757901191711426, -0.015868140384554863, -0.0112145459279418, -0.03386959806084633, -0.0022130373399704695, -0.05471862480044365, -0.004407774191349745, 0.023733269423246384, -0.048493970185518265, -8.638498002255801e-06, -0.04214460402727127, -0.048424623906612396, -0.020959051325917244, 0.028982456773519516, -0.0469360314309597, -0.07821963727474213, -0.09337988495826721, -0.05506885424256325, 0.06582049280405045, 0.043441496789455414, -0.04630262032151222, -0.02603207156062126, -0.012655194848775864, 0.03493763133883476, -0.02895120531320572, -0.03762860968708992, 0.03350687399506569, -0.05161743611097336, 0.0007873208378441632, -0.028906047344207764, -0.03173741325736046, 0.06641640514135361, -0.021090837195515633, -0.009864509105682373, -0.07203184813261032, -0.003614912275224924, -0.07552274316549301, 0.01778908632695675, 0.08100350946187973, -0.08699870854616165, -0.02725817635655403, -0.018215611577033997, -0.05630854144692421, -0.04340849816799164, 0.036796487867832184, -0.0037359073758125305, 0.0071078198961913586, -0.0007756741833873093, -0.0889042466878891, 0.041091203689575195, -0.019850613549351692, 0.014451987110078335, 0.09769770503044128, -0.09420958906412125, -0.02192658931016922, 0.07087961584329605, 0.10020706057548523, 0.007672135718166828, 0.03854312002658844, 0.014739323407411575, 0.02011997438967228, -0.06486982852220535, 0.036830637603998184, -0.05905122682452202, 0.04982536658644676, -0.043665893375873566, 0.030363980680704117, 0.008823659271001816, -0.04841509833931923, 0.028754252940416336, -0.04150412231683731, 0.04483795538544655, -0.09078838676214218, -0.005426950287073851, 0.006968899630010128, -0.07452317327260971, -0.05938953533768654, 0.005621171556413174, -0.003081932896748185, 0.0637332946062088, 0.00722687691450119, -0.03078307956457138, 0.04228765144944191, 0.03572674095630646, -0.005153449717909098, -0.0005451729521155357, 0.05110640078783035, -0.03949293494224548, 0.04559475556015968, -0.022974999621510506, -0.034800395369529724, 0.011382055468857288, -0.07532662898302078, -0.0516321137547493, 0.026424670591950417, -0.048582740128040314, -0.049843061715364456, -0.10660974681377411, -0.03002169169485569, -0.019005153328180313, 0.08248486369848251, 0.014558037742972374, 0.04279818758368492, -0.022138139232993126, 0.04877873510122299, -0.04516701027750969, 0.02615683153271675, 0.07713659852743149, 0.001032722764648497, -0.05035976693034172, -0.022156428545713425, -0.05010150745511055, 0.03104499541223049, -0.014510269276797771, -0.05015400052070618, -0.0026562230195850134, 0.0018329804297536612, 0.025323258712887764, 0.01491023413836956, 0.012867051176726818, -0.007017084863036871, 0.008183971047401428, 0.02828057110309601, 0.046572908759117126, 0.019583383575081825, 0.002452040556818247, -0.019346745684742928, 0.0438251718878746, 0.032586924731731415, 0.033262208104133606, 0.04727725312113762, -0.045670922845602036, -0.06306607276201248, 0.020311003550887108, 0.05785737559199333, -0.04670697823166847, 0.08185407519340515, 0.006972871720790863, 0.005085402633994818, 0.023233091458678246, -0.07549150288105011, 0.04096204787492752, -0.046605464071035385, -0.060669321566820145, 0.0368310920894146, 0.05985039100050926, -0.027769992128014565, 0.04670090973377228, 0.0033564234618097544, -0.11930698156356812, -0.047172460705041885, -0.04547406733036041, -0.04420752823352814, 0.040266331285238266, -0.028725625947117805, -0.1265893280506134, 0.039141543209552765, 0.01356026902794838, -0.001974711660295725, -0.08085983246564865, 0.011007809080183506, -0.04507829621434212, 0.05386153608560562, -0.038509249687194824, 0.08004365116357803, -0.0033251368440687656, -0.05820273235440254, 0.052246104925870895, -0.04640199989080429, 0.030945315957069397, -0.14870759844779968, -0.0043353852815926075, 0.0561133474111557, 0.034115128219127655, 0.023715946823358536, -0.0004868319956585765, 0.016849391162395477, 0.02189038135111332, 0.0186284352093935, -0.03331423178315163, -0.022472387179732323, -0.03937795013189316, -0.015877297148108482, 0.011001931503415108, -0.060135383158922195, -0.07058106362819672, -0.0009083979530259967, -0.05243641138076782, 0.008613187819719315, 0.052769262343645096, 0.049394603818655014, 0.019467853009700775, -0.011562822386622429, -0.0024337766226381063, -0.10718277841806412, 0.01358015276491642, -0.0012322679394856095, 0.019700948148965836, -0.00835228618234396, -0.03032337874174118, -0.005290510132908821, 0.031820256263017654, 0.036071814596652985, 0.07353341579437256, 0.04936410114169121, 0.008623352274298668, -0.006856943480670452, -0.007743047084659338, 0.0026361269410699606, -0.03568916767835617, -0.005449430085718632, 0.004680147394537926, -0.02787834033370018, 0.042373474687337875, 0.04390978068113327, -0.05871044844388962, -0.02515685372054577, 0.0757506936788559, 0.04229356348514557, 0.013724429532885551, -0.08773909509181976, -0.01280178688466549, -0.023127904161810875, 0.011242798529565334, -0.04463221877813339, -0.04146982356905937, -0.007359685376286507, -0.015373117290437222, 0.0213985126465559, 0.03534611687064171, -0.035810623317956924, -0.007776459213346243, 0.08480753004550934, -0.0395759716629982, -0.07502877712249756, 0.013684600591659546, -0.0489785261452198, 0.04461509734392166, -0.062101785093545914, 0.026000438258051872, -0.03622716665267944, 0.027888689190149307, 0.026639599353075027, -0.03318826109170914, 0.0925130844116211, -0.04618644714355469, 0.03447040915489197, 0.013749773614108562, -0.08061790466308594, -0.005921373143792152, -0.018181180581450462, -0.052560705691576004, -0.06603946536779404, 0.032191142439842224, -0.07431518286466599, 0.0875418409705162, -0.018283657729625702, -0.07786126434803009, 0.022460024803876877, -0.006875723600387573, 0.07691939920186996, 0.017695821821689606, -0.013950121589004993, 0.045839764177799225, 0.010869072750210762, 0.03503108024597168, 0.04882045090198517, 0.007329160813242197, -0.020552629604935646, -0.09802612662315369, 0.0003529112145770341, -0.08505655080080032, 0.0015210312558338046, -0.10461687296628952, 0.061678651720285416, 0.01101753767579794, 0.026482820510864258, 0.04845467582345009, 0.013389366678893566, 0.04759039357304573, -0.004812351427972317, -0.05034021660685539, -0.0493013933300972, 0.06908261775970459, 0.0038385393563658, 0.018407117575407028, 0.012556949630379677, -0.009845142252743244, 0.0026016898918896914, -0.005857239477336407, -0.015652520582079887, 0.004636276513338089, -0.04006921127438545, 0.03793465346097946, -0.07253799587488174, -0.014149683527648449, 0.048184096813201904, -0.042957205325365067, -0.03131100907921791, 0.03279750049114227, 0.05700839310884476, -0.07073519378900528, -0.01720470003783703, -0.080380879342556, -0.014982747845351696, -0.06200402230024338, 0.008758483454585075, 0.024499543011188507, -0.008530016988515854, 0.08835098147392273, 0.05704542249441147, 0.03381117433309555, -0.03955931216478348, 0.005791163071990013, -0.06867315620183945, 0.046477582305669785, 0.009858346544206142, -0.004983861465007067, -0.007746907416731119, 0.01591055653989315, -0.011226440779864788, 0.01842588558793068, -0.058516912162303925, -0.01810448057949543, -0.019845804199576378, 0.015994468703866005, -0.02401176281273365, -0.014254958368837833, 0.0021005019079893827, -0.0283687524497509, -0.05374789610505104, 0.07054345309734344, 0.017107542604207993, -0.024356907233595848, -0.009208686649799347, -0.027507063001394272, 0.007703515700995922, 0.013385902158915997, 0.00560801150277257, -0.04037204757332802, -0.001994471298530698, 0.018353242427110672, -0.01716260425746441, 0.024459315463900566, 0.00654109800234437, -0.03783485293388367, -0.004453767091035843, -0.00997089222073555, 0.003232917981222272, 0.012282357551157475, -0.011419154703617096, 0.016027674078941345, -0.012957046739757061, 0.0330485925078392, 0.06477300077676773, -0.06168093904852867, -0.019880011677742004, -0.041440799832344055, 0.03003719076514244, 0.008341258391737938, -0.029055969789624214, 0.031742777675390244, 0.01594552956521511, -0.020977554842829704, -0.05936075747013092, -0.0745856910943985, -0.024708038195967674, 0.02581445686519146, -0.012066920287907124, 0.03821353614330292, 0.024822328239679337, -0.07947397977113724, -0.026835471391677856, -0.012774281203746796, -0.054468169808387756, -0.0463687963783741, 0.015320809558033943, -0.09645228832960129, -0.004003910347819328, 0.01269455999135971, -0.05709650740027428, -0.002512049861252308, -0.0037318693939596415, -0.027645599097013474, 0.004317158367484808, -0.017788568511605263, -0.050225164741277695, -0.0411544032394886, 0.028715165331959724, 0.014049225486814976, -0.04676993191242218, -0.08436186611652374, -0.01351150218397379, -0.01887568272650242, -0.008284347131848335, 0.01570138707756996, -0.033980000764131546, -0.027384739369153976, -0.052851688116788864, 0.014744305051863194, 0.005695417523384094, -0.0485430546104908, -0.025095146149396896, 0.016145220026373863, -0.058350570499897, 0.06996522843837738, -0.03592624142765999, -0.030697226524353027, -0.05155130475759506, 0.07328564673662186, 0.07656364887952805, 0.021740056574344635, 0.06567233800888062, -0.028271585702896118, 0.06710265576839447, -0.06009067967534065, 0.003804059699177742, -0.003360230941325426, -0.01031701173633337, 0.02467760071158409, -0.034498121589422226, -0.032701749354600906, -0.0672091394662857, 0.011051742359995842, -0.10762231051921844, 0.010451732203364372, -0.009892391972243786, 0.019292069599032402, 0.05761246383190155, -0.032121770083904266, 0.026553606614470482, 0.05654114484786987, -0.01933141238987446, -0.05244213342666626, -0.002482766518369317, 0.005206551868468523, -0.07326783239841461, 0.09168106317520142, -0.07814972847700119, -0.056083131581544876, 0.07045608758926392, -0.006963979918509722, 0.020996658131480217, 0.08999699354171753, 0.0008354263263754547, -0.0623769648373127, -0.035213734954595566, 0.04742976650595665, -0.047123733907938004, -0.012996293604373932, -0.021993888542056084, -0.020444050431251526, -0.08824032545089722, 0.047867551445961, -0.00520310178399086, -0.08169999718666077, -0.028369002044200897, 0.008967344649136066, 0.06564977020025253, 0.030306417495012283, 0.03193434327840805, 0.047027338296175, -0.03142949938774109, 0.007957135327160358, -0.032855141907930374, 0.040751442313194275, 0.03788880258798599, -0.05199128016829491, 0.010682369582355022, 0.06747093051671982, 0.03566131368279457, -0.0408269427716732, 0.04915542155504227, 0.0013061707140877843, -0.008842259645462036, 0.039765823632478714, 0.038644786924123764, -0.05181257054209709, -0.005586078390479088, -0.09502430260181427, 0.07475384324789047, 0.02405710332095623, 0.11223676800727844, 0.025882668793201447, 0.02621760591864586, 0.05314607918262482, 0.024459121748805046, 0.06296630203723907, 0.024257183074951172, 0.01778186485171318, -0.008402255363762379, 0.030288685113191605]', 'uploads/faces/user_8_angle0_20260729_163056_764189.jpg'),
(8, NULL, 8, '', '', '2026-07-29 11:01:02', '[0.053565360605716705, -0.028642330318689346, 0.06778202950954437, -0.009670214727520943, -0.0032718670554459095, -0.028865909203886986, -0.026293782517313957, -0.02350500039756298, 0.0526915080845356, -0.019467677921056747, -0.033422861248254776, 0.04577353224158287, -0.024855192750692368, -0.025304432958364487, -0.00612890999764204, -0.032929275184869766, -0.043289635330438614, -0.06278453022241592, 0.026655001565814018, -0.025835935026407242, -0.08890534937381744, -0.01729649491608143, -0.01316593773663044, -0.06806724518537521, 0.05815389007329941, -0.09834109246730804, 0.013476298190653324, 0.06459392607212067, 0.01198580302298069, -0.042448852211236954, -0.09184097498655319, -0.07530917972326279, -0.007164439652115107, 0.012065556831657887, 0.0029262369498610497, -0.09291933476924896, -0.07097063213586807, 0.006793879438191652, 0.06725450605154037, 0.08374422043561935, -0.011190439574420452, -0.021535547450184822, -0.004560479428619146, 0.088389091193676, -0.06122005358338356, 0.005408903583884239, -0.01952798292040825, -0.08774658292531967, 0.0431443452835083, 0.012346993200480938, 0.028032386675477028, 0.01024584099650383, 0.007649850565940142, -0.054268527776002884, -0.014040470123291016, 0.03514270856976509, -0.06139535829424858, -0.0020341286435723305, 0.05300123617053032, -0.028615863993763924, -0.003289963351562619, 0.025813810527324677, -0.08208408206701279, 0.03534147888422012, 0.021923435851931572, -0.009063977748155594, -0.04298000410199165, 0.03817857429385185, -0.021193403750658035, 0.03696250170469284, -0.007414430379867554, 0.019335033372044563, 0.06615132093429565, -0.0565837062895298, -0.005988933611661196, 0.07618394494056702, 0.047759898006916046, -0.01571093499660492, 0.003633008571341634, 0.016298336908221245, 0.0876619815826416, -0.06789370626211166, 0.06503545492887497, -0.0230052899569273, 0.043379493057727814, -0.011268068104982376, 0.05862675979733467, 0.002463645301759243, -0.08222608268260956, 0.029811576008796692, 0.0016184523701667786, 0.08479563146829605, -0.05971681699156761, 0.014094403944909573, -0.05325153097510338, 0.007979517802596092, -0.04646895080804825, 0.00973842479288578, -0.00030523486202582717, 0.0799635797739029, 0.04035753384232521, -0.03881378471851349, 0.03622107580304146, 0.020197179168462753, -0.014016245491802692, -0.00441941199824214, 0.05363478139042854, -0.00442607793956995, 0.03653346747159958, -0.034523628652095795, -0.06016470864415169, 0.08817002922296524, -0.12781469523906708, -0.025598442181944847, -0.011571569368243217, 0.0399731770157814, -0.022117892280220985, -0.05607643350958824, -0.009180717170238495, -0.06605353951454163, -0.07261726260185242, -0.022884037345647812, -0.011226102709770203, 0.0500287227332592, 0.05187494680285454, 0.003101597772911191, 0.04691193252801895, 0.06416428834199905, -0.024389196187257767, 0.008365319110453129, -0.034848652780056, 0.010064239613711834, 0.03672463074326515, 0.016567515209317207, -0.06512273102998734, -0.0025538094341754913, -0.019615165889263153, 0.04709119722247124, 0.04037574678659439, -0.03833416476845741, -0.1034867912530899, -0.047040484845638275, 0.015899015590548515, 0.05217244476079941, -0.004872612189501524, 0.006788318511098623, 0.04364185407757759, -0.00016072178550530225, -0.024362172931432724, 0.022889258340001106, -0.03170029819011688, -0.0406702421605587, -0.03669096529483795, 0.0068666087463498116, 0.005111573729664087, -0.00817699171602726, 0.05506538227200508, -0.030384059995412827, -0.005786656867712736, 0.0694621205329895, -0.1144605427980423, 0.026740584522485733, -0.048364315181970596, -0.01759462058544159, 0.0366462841629982, 0.004746987484395504, 0.0011966702295467257, -0.001333807478658855, -0.026737157255411148, -0.0383460707962513, -0.03184626251459122, 0.04082171246409416, -0.06089412793517113, 0.024012448266148567, -0.008508835919201374, -0.026037482544779778, -0.05557005852460861, -0.025022035464644432, -0.015380932949483395, -0.08173477649688721, -0.015209118835628033, -0.02539353258907795, 0.03079700842499733, -0.002961312420666218, 0.022187786176800728, -0.02341139130294323, -0.0051617915742099285, -0.035713598132133484, 0.02944180555641651, -0.01769571378827095, -0.055960193276405334, 0.024354862049221992, 0.023246146738529205, 0.03739173337817192, 0.056524816900491714, 0.0024236091412603855, -0.023701855912804604, -0.0568363331258297, 0.03600245714187622, -0.049883339554071426, -0.04996966943144798, -0.03954106196761131, 0.024555061012506485, -0.02000921219587326, -0.06120029464364052, 0.006180116906762123, 0.040030885487794876, 0.018528886139392853, 0.04574734345078468, 0.0026180902495980263, -0.004381935112178326, -0.028218109160661697, -0.0020242840982973576, 0.016543269157409668, -0.13086310029029846, -0.003492531133815646, -0.031194835901260376, 0.025575922802090645, -7.559233927167952e-05, -0.025735581293702126, -0.0335521325469017, 0.10057391226291656, 0.04335254058241844, 0.07892761379480362, 0.02710140123963356, -0.03111521527171135, -0.051963917911052704, -0.036337971687316895, 0.009249872528016567, 0.04067312926054001, 0.06852389127016068, -0.007559768855571747, -0.006369353737682104, 0.06761141121387482, 0.00892480555921793, -0.01706976443529129, -0.0012869342463091016, 0.019771670922636986, 0.10460465401411057, -0.007448459509760141, -0.015401355922222137, 0.012481246143579483, -0.08487605303525925, 0.06262326240539551, 0.006168689113110304, 0.0010142539395019412, 0.0447685532271862, -0.10868530720472336, 0.009313678368926048, -0.03282002732157707, 0.009570688009262085, 0.030931420624256134, 0.03470024839043617, -0.02492707408964634, -0.08699572086334229, 0.015079041942954063, -0.06974726915359497, 0.022822419181466103, -0.015178288333117962, 0.03394804522395134, -0.0691610649228096, 0.04110197350382805, -0.005919302813708782, -0.0216177087277174, 0.05466831848025322, -0.010153088718652725, 0.007878190837800503, 0.027455942705273628, -0.02487843669950962, 0.010546950623393059, -0.04109453037381172, -0.07192397117614746, -0.012492479756474495, 0.05283040925860405, -0.02080921083688736, 0.0069369962438941, -0.05469607934355736, -0.12239624559879303, 0.03371964395046234, -0.006143065635114908, 0.07432394474744797, -0.023092081770300865, -0.05614583566784859, 0.004202791023999453, 0.03517267107963562, 0.005972956772893667, 0.061722252517938614, -0.037209995090961456, -0.1055154800415039, -0.08721698820590973, 0.016790537163615227, -0.10784400999546051, -0.008400948718190193, -0.039957527071237564, 0.07288971543312073, -0.03989100828766823, -0.01969243958592415, 0.016002202406525612, -0.04806973785161972, -0.01323628518730402, 0.010566072538495064, 0.032108377665281296, -0.0019171044696122408, 0.046873703598976135, 0.03214199095964432, -0.031648170202970505, -0.013060222379863262, -0.001438411884009838, -0.03299620375037193, -0.04385041818022728, -0.0433913990855217, -0.03464983031153679, 0.018658816814422607, 0.06778571009635925, -0.10196708142757416, 0.018331404775381088, 0.02172086015343666, -0.08704257756471634, -0.05466697737574577, 0.05903054401278496, -0.012038961052894592, -0.06006351858377457, -0.021989256143569946, -0.06942280381917953, -0.04135661572217941, 0.003323274664580822, -0.015614953823387623, 0.052030034363269806, -0.020321732386946678, 0.016762834042310715, 0.025833923369646072, 0.02535531297326088, -0.010253878310322762, -0.024018917232751846, -0.05546166002750397, 0.0068463971838355064, -0.014431480318307877, -0.035864390432834625, 0.005512180272489786, 0.0322997011244297, -0.006683504208922386, 0.04824625328183174, -0.05048566684126854, 0.01814548298716545, -0.008691813796758652, -0.051971979439258575, -0.04400341212749481, -0.02889993228018284, -0.04607590287923813, 0.018645960837602615, -0.003565999446436763, 0.030701888725161552, -0.008823775686323643, -0.09256070852279663, -0.023648813366889954, -0.026078276336193085, 0.015641918405890465, 0.04281715676188469, 0.04019924998283386, -0.040147919207811356, 0.03081607259809971, -0.019895758479833603, -0.11824633181095123, 0.029198428615927696, 0.017080826684832573, -0.010695839300751686, 0.055569782853126526, 0.04026009887456894, -0.024886421859264374, -0.024276508018374443, 0.0368121936917305, 0.023656921461224556, -0.06622370332479477, 0.017341893166303635, 0.08116021007299423, -0.05162075534462929, 0.044917669147253036, -0.07170887291431427, 0.01548775378614664, 0.044079169631004333, -0.033382583409547806, 0.04843717813491821, -0.0050033447332680225, 0.0326119028031826, 0.012757094576954842, -0.041036613285541534, 0.04698988050222397, 0.006499783601611853, -0.021905258297920227, -0.02438191883265972, 0.03877970948815346, -0.0029806611128151417, -0.009925097227096558, 0.006914796773344278, -0.037762369960546494, 0.022621963173151016, 0.052380457520484924, -0.0273969154804945, -0.023684503510594368, -0.021476391702890396, -0.029926329851150513, 0.04163041710853577, -0.029140619561076164, -0.029207108542323112, -0.025235706940293312, 0.06570345163345337, 0.024764806032180786, -0.025852354243397713, 0.04815700277686119, -0.0007053129374980927, -0.0877159908413887, -0.028137575834989548, 0.025169527158141136, -0.05853034555912018, -0.03042876161634922, 0.002393803559243679, -0.026635490357875824, -0.007902846671640873, -0.05008912459015846, -0.06311783939599991, -0.01669902727007866, -0.08186764270067215, 0.01796162873506546, -0.014417396858334541, -0.047662075608968735, 0.10407865047454834, 0.057352788746356964, -0.022786172106862068, -0.01913919672369957, 0.021301213651895523, 0.05507924035191536, -0.0595846101641655, 0.02638213336467743, -0.0011514730285853148, 0.07911620289087296, -0.05051252618432045, 0.03374532610177994, -0.04700629785656929, -0.02119521237909794, -0.05708850920200348, -0.03416932746767998, 0.011343934573233128, -0.052050333470106125, 0.047513511031866074, -0.03482206538319588, -0.004835051950067282, 0.012200585566461086, 0.030225319787859917, 0.048580799251794815, 0.010587785392999649, 0.03371812775731087, 0.03901611641049385, 0.021364185959100723, 0.04247162491083145, -0.0900927484035492, -0.03711095452308655, -0.06074860319495201, 0.0267376396805048, 0.02562917396426201, -0.0463959239423275, 0.012314561754465103, 0.012007066048681736, 0.028765864670276642, 0.0657835602760315, 0.009646644815802574, -0.055406924337148666, -0.029464902356266975, -0.020097173750400543, -0.049368150532245636, 0.05660851672291756, -0.05857394263148308, -0.0639428198337555, 0.044835787266492844, 0.07455592602491379, 0.004961756523698568, -0.040945399552583694, -0.022617511451244354, -0.0004856388841290027, 0.07400451600551605, 0.020627696067094803, -0.0017815277678892016, 0.051142219454050064, -0.08098473399877548, -0.004048696253448725, -0.03895142301917076, 0.07176978886127472, 0.02151499316096306, -0.11529431492090225, 0.024875110015273094, 0.024267185479402542, -0.03303469717502594, -0.018591398373246193, 0.09167075157165527, -0.009123693220317364, -0.017694642767310143, 0.049533769488334656, 0.024971332401037216, -0.023393748328089714, 0.011479180306196213, -0.05932148918509483, 0.01462949812412262, 0.020731493830680847, 0.08653198182582855, -0.02252880483865738, -0.03330311179161072, 0.11356750130653381, -0.0208979994058609, 0.026598742231726646, -0.01356119941920042, -0.03178859502077103, -0.02734588459134102, -0.01940423808991909]', 'uploads/faces/user_8_angle1_20260729_163058_024930.jpg'),
(9, NULL, 8, '', '', '2026-07-29 11:01:02', '[0.10339713841676712, -0.034646499902009964, 0.030777273699641228, -0.003931999206542969, 0.02487311325967312, -0.011357503943145275, 0.028269076719880104, 0.051317550241947174, 0.032001398503780365, -0.03405889868736267, -0.05216468125581741, -0.028063906356692314, 0.01976269856095314, 0.02584916166961193, -0.05679118260741234, -0.0402463935315609, -0.0043367440812289715, -0.03676341846585274, 0.052215300500392914, 0.0878375992178917, -0.02921631559729576, -0.032935820519924164, -0.023288078606128693, -0.009189783595502377, 0.025318503379821777, -0.07637094706296921, -0.006447886116802692, 0.03094179928302765, -0.03141593933105469, -0.04257361218333244, -0.07542678713798523, 0.03676224127411842, 0.008648175746202469, 0.047637853771448135, 0.03520694747567177, -0.10197846591472626, -0.06080938130617142, -0.01385569293051958, 0.020924346521496773, 0.06939975917339325, -0.05241180956363678, -0.023949813097715378, -0.00305805541574955, 0.04993283003568649, -0.05131066218018532, -0.0009521308238618076, -0.005193566903471947, -0.08172710239887238, 0.030094066634774208, 0.04501015320420265, 0.042284879833459854, 0.033562518656253815, 0.0029210636857897043, -0.03495065122842789, -0.006660296581685543, 0.055491186678409576, 0.015779491513967514, -0.045264314860105515, 0.1031438484787941, -0.06381117552518845, 0.06021593511104584, 0.019169330596923828, -0.031118210405111313, 0.023028017953038216, 0.09438274800777435, -0.04713626950979233, -0.027290845289826393, 0.060758620500564575, -0.001664995332248509, 0.11399082839488983, 0.02745860256254673, 0.05915069952607155, 0.12151782959699631, -0.05991310998797417, -0.04816149175167084, 0.07288054376840591, 0.013966074213385582, -0.006168759427964687, 0.04493698477745056, 0.008885897696018219, 0.1085590198636055, -0.038162168115377426, 0.10023415833711624, 0.017878998070955276, 0.03537476807832718, -0.02963775396347046, 0.03577645868062973, -0.024189045652747154, -0.056762780994176865, 0.02244439348578453, -0.014885643497109413, 0.014383177272975445, -0.028255179524421692, 0.008781778626143932, -0.05233679711818695, -0.07196121662855148, -0.05491102114319801, -0.00944342277944088, 0.014627914875745773, 0.0038007088005542755, 0.022333992645144463, -0.026501284912228584, 0.056938111782073975, 0.020063400268554688, -0.003567860461771488, 0.026246564462780952, 0.041949570178985596, -0.004608482588082552, 0.0023288074880838394, 0.003017500974237919, -0.06384912133216858, 0.01206084806472063, -0.05971122160553932, -0.015112108550965786, -0.02261572889983654, 0.0405377633869648, -0.044826481491327286, -0.030798114836215973, -0.019870292395353317, -0.04209436476230621, -0.0366564579308033, 0.004108762368559837, -0.014535580761730671, -0.012485300190746784, 0.037632375955581665, 0.021929074078798294, -0.011228112503886223, -0.02591603808104992, 0.029513096436858177, 0.003597487462684512, -0.03401276469230652, 0.00576618779450655, 0.11055579781532288, 0.04300069808959961, -0.07287465035915375, -0.016854744404554367, 0.03846113756299019, 0.045070335268974304, 0.027411634102463722, -0.10955126583576202, -0.08338717371225357, -0.011986315250396729, 0.022990131750702858, 0.11637750267982483, 0.028184624388813972, 0.03752749785780907, 0.028420517221093178, 0.010355329141020775, -0.03877784684300423, 0.03620366379618645, 0.05787622928619385, -0.049647267907857895, -0.01248139888048172, -0.0010833942797034979, -0.05171705409884453, 0.02262977324426174, 0.031327858567237854, -0.03361467644572258, 0.02238478511571884, 0.048986852169036865, -0.1252158135175705, 0.05779879540205002, 0.003029938554391265, -0.03629595413804054, 0.039141420274972916, 0.020618872717022896, 0.022542554885149002, 0.004903276450932026, 0.0356246754527092, -0.08872679620981216, -0.01840786449611187, 0.01634039916098118, 0.051219817250967026, 0.037227388471364975, -0.06884410232305527, -0.06532887369394302, -0.03374876081943512, -0.02550765685737133, -0.049174632877111435, -0.08924247324466705, 0.00912256445735693, -0.054473113268613815, 0.06683357059955597, -0.0602559894323349, 0.022822370752692223, -0.004431900102645159, -0.05175214633345604, 0.0025734049268066883, -0.07006166130304337, 0.035792477428913116, -0.05602319911122322, -0.050557203590869904, 0.004073543474078178, 0.03179528936743736, 0.014286724850535393, -0.0677904412150383, 0.011360304430127144, 0.008971738629043102, -0.04312477260828018, -0.029860401526093483, -0.017322588711977005, -0.037066273391246796, -0.03632233664393425, -0.002154360059648752, -0.0314541719853878, -0.016980327665805817, 0.055758923292160034, 0.022140726447105408, 0.07258011400699615, -0.0037331480998545885, -0.039196424186229706, -0.04112628847360611, -0.011522267013788223, -0.016243653371930122, -0.09719154983758926, -0.043004680424928665, -0.02561067044734955, 0.030367743223905563, 0.018100840970873833, 0.010143222287297249, 0.02229446731507778, 0.02112165465950966, 0.016000913456082344, 0.08118623495101929, 0.07215507328510284, 0.025008386000990868, 0.059029169380664825, -0.03950413316488266, 0.008313574828207493, 0.029635408893227577, 0.02460070513188839, 0.0170295313000679, -0.027062006294727325, 0.03719659149646759, 0.06002426519989967, -0.04735765606164932, -0.011099003255367279, 0.039081815630197525, 0.009607147425413132, -0.01482435129582882, -0.03782445564866066, -0.01728207804262638, -0.06468722969293594, 0.02963714301586151, 0.0038083044346421957, 0.047698140144348145, 0.047472089529037476, -0.06134791299700737, 0.008768566884100437, -0.05084928125143051, 0.06336379796266556, 0.015342448838055134, 0.036214184015989304, -0.02807757630944252, -0.037217848002910614, 0.011340254917740822, -0.019608018919825554, 0.008515874855220318, -0.050431814044713974, 0.021621353924274445, -0.04000457376241684, 0.02577613666653633, 0.046026356518268585, -0.047477561980485916, 0.06890349835157394, -0.05628333240747452, 0.006293431855738163, 0.0347919799387455, -0.017121270298957825, -0.008949833922088146, -0.056994590908288956, -0.04388594254851341, -0.07127857208251953, 0.07853423804044724, -0.04971807077527046, -0.015871714800596237, -0.02173328772187233, -0.04577692225575447, -0.03454902395606041, 0.07186974585056305, 0.041168585419654846, 0.006308167241513729, 0.0015266082482412457, 0.06135471165180206, -0.012744286097586155, 0.04168261960148811, -0.017855558544397354, -0.018552765250205994, -0.08770963549613953, -0.08374562859535217, -0.019511517137289047, -0.0904683917760849, 0.03776884451508522, -0.0402296707034111, 0.08157047629356384, -0.019007183611392975, -0.019629064947366714, 0.046421561390161514, -0.005019781645387411, 0.0005991943762637675, -0.006451338063925505, 0.05149609595537186, -0.0615433044731617, 0.04729001224040985, 0.047992050647735596, -0.044218502938747406, -0.04983685165643692, 0.05429789423942566, 0.005178449675440788, -0.019069526344537735, -0.003552049631252885, -0.03641975298523903, 0.0634586289525032, 0.09127545356750488, -0.10395675897598267, 0.02058151736855507, 0.030954714864492416, -0.05720561742782593, -0.0740300714969635, -0.05497677996754646, -0.016735536977648735, -0.09073112159967422, 0.007758487481623888, -0.08122172951698303, -0.04742594435811043, -0.007832705043256283, 0.0018374011851847172, 0.02464805729687214, -0.020580919459462166, 0.01894831284880638, 0.04613589122891426, -0.017299622297286987, -0.06248074769973755, -0.02237989753484726, -0.07705332338809967, 0.004914855118840933, -0.03456856310367584, -0.009258684702217579, 0.0361022986471653, 0.055583205074071884, -0.037817392498254776, 0.0044084517285227776, -0.008783501572906971, 0.0035149455070495605, -0.01318269781768322, 0.0020541490521281958, -0.03314253315329552, -0.04226258769631386, -0.07280921936035156, -0.04612436145544052, 0.023424560204148293, 0.011864656582474709, -0.026170259341597557, -0.059553343802690506, -0.04292725771665573, -0.03225115314126015, 0.02059342712163925, 0.025835243985056877, 0.05117693915963173, -0.004267940763384104, 0.03780728206038475, 0.01006532646715641, -0.044393040239810944, 0.03369053825736046, 0.04735241457819939, -0.0347745418548584, 0.08260494470596313, 0.039474643766880035, -0.015006901696324348, -0.00503661809489131, -0.004230320453643799, -0.020024606958031654, -0.006065739318728447, 0.030996842309832573, 0.08729112893342972, 0.00165939808357507, -0.025071406736969948, -0.03067622147500515, 0.053854890167713165, 0.074469655752182, -0.06875303387641907, -0.01070170197635889, -0.015593335032463074, -0.03810937702655792, -0.05367356538772583, -0.06233973801136017, 0.03401222825050354, -0.020726630464196205, -0.04696677625179291, -0.04185306280851364, -0.02076037973165512, 0.001741854939609766, 0.02716607041656971, 0.019373353570699692, -0.006392731796950102, -0.005410395562648773, 0.0007314725080505013, -0.011108175851404667, -0.00019596100901253521, -0.032709479331970215, -0.027689052745699883, -0.018881874158978462, -0.09597737342119217, -0.004604790359735489, -0.022307641804218292, 0.05061836168169975, -0.031292904168367386, 0.07687173038721085, 0.05333283171057701, 0.044585660099983215, -0.04206974431872368, -0.10219917446374893, 0.04059164226055145, -0.012437239289283752, -0.06984621286392212, 0.005515594966709614, -0.028688963502645493, -0.015413214452564716, -0.022850580513477325, -0.04161091148853302, 0.005539268720895052, -0.02052515745162964, 0.01463212538510561, -0.03512877598404884, -0.1117686927318573, 0.08529545366764069, 0.022373773157596588, -0.013093716464936733, -0.022930674254894257, 0.004222889896482229, 0.06002124771475792, 0.03453313559293747, 0.051860783249139786, -0.02292005904018879, 0.05600040778517723, -0.06450722366571426, -0.0012335878564044833, -0.008737928234040737, 0.02161296457052231, -0.07710997015237808, -0.07575234770774841, -0.020507575944066048, -0.066049724817276, 0.00827085506170988, -0.0199982151389122, 0.0376189723610878, -0.029847055673599243, 0.010302890092134476, -0.06411772221326828, -0.0026943536940962076, 0.06510546058416367, 0.027028940618038177, 0.011155570857226849, 0.04335853084921837, -0.02055477164685726, -0.01198370847851038, -0.04957166314125061, -0.003364025615155697, 0.04311324656009674, -0.06783193349838257, 0.00914714764803648, -0.027595728635787964, 0.005341197829693556, 0.04673437774181366, -0.03680522367358208, -0.028408730402588844, -0.0610630102455616, -0.008454384282231331, -0.020085981115698814, 0.03018469363451004, 0.013797545805573463, -0.019384175539016724, 0.008792844600975513, 0.07433665543794632, 0.04249516874551773, -0.04802585020661354, -0.06144499406218529, 0.007472853176295757, 0.06818858534097672, -0.006362040061503649, 0.0385732427239418, 0.05232507362961769, -0.11568764597177505, 0.021024923771619797, -0.04535038024187088, 0.04773423820734024, -0.021192554384469986, 0.004226877354085445, 0.008622054941952229, 0.029357874765992165, -0.026894554495811462, -0.009972507134079933, 0.05265583470463753, 0.03871795907616615, -0.05003858357667923, 0.0031616867054253817, 0.045101433992385864, -0.0474868081510067, -0.018620511516928673, -0.03025633655488491, -0.009187186136841774, 0.01751198247075081, 0.09909997880458832, 0.002556027378886938, -0.014338292181491852, 0.044307269155979156, 0.05471571907401085, -0.0005866216961294413, 0.0032512887846678495, -0.05147814378142357, -0.04111608862876892, 0.005365921650081873]', 'uploads/faces/user_8_angle2_20260729_163059_541776.jpg');
INSERT INTO `face_embeddings` (`id`, `employee_id`, `user_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(10, NULL, 8, '', '', '2026-07-29 11:01:02', '[0.06483139842748642, -0.06114644557237625, -0.07528168708086014, -0.01838446781039238, -0.040922217071056366, 0.04914701357483864, 0.005075001623481512, 0.024510959163308144, 0.03273762762546539, -0.007307261228561401, -0.03038528561592102, 0.05765177682042122, -0.022543491795659065, 0.010941196233034134, -0.009439422748982906, -0.01708471216261387, -0.046608299016952515, -0.03330661728978157, -0.020810823887586594, -0.033733971416950226, -0.035947468131780624, -0.04214448854327202, 0.02593541517853737, 0.012153745628893375, 0.002333212411031127, -0.09562660753726959, -0.01687973365187645, 0.01987544447183609, 0.015504671260714531, 0.0010895294835790992, 0.033123619854450226, -0.06583763659000397, 0.0031220808159559965, 0.012219462543725967, 0.018843039870262146, -0.035454146564006805, -0.06210736557841301, 0.005746722221374512, 0.05486223101615906, 0.017168493941426277, -0.04516947269439697, -0.035229507833719254, 0.04032740741968155, 0.03419039398431778, -0.052352577447891235, -0.04381692782044411, 0.037416018545627594, -0.010293733328580856, 0.017361542209982872, 0.03396238386631012, 0.005479278974235058, 0.08923710137605667, 0.0022395551204681396, -0.021339265629649162, -0.06176025792956352, -0.026584399864077568, -0.024129876866936684, -0.03765978291630745, -0.059311654418706894, -0.053299859166145325, 0.023288797587156296, -0.03895563259720802, -0.06318952143192291, -0.03560934588313103, 0.030014581978321075, -0.015306304208934307, -0.01537368819117546, -0.01110526267439127, -0.15320472419261932, 0.04068775475025177, -0.08472270518541336, 0.02060553804039955, 0.05394147336483002, -0.03770715743303299, -0.10948661714792252, 0.05966317281126976, 0.07960328459739685, 0.004159495700150728, 0.015911471098661423, 0.05318305641412735, 0.004624765831977129, -0.04144992679357529, 0.06225084140896797, 0.032690830528736115, -0.0050876084715127945, 0.018223050981760025, 0.06499288976192474, -0.04714270308613777, -0.000484039424918592, 0.04488253593444824, -0.0571613572537899, 0.052071791142225266, -0.08384907990694046, -0.0335361510515213, 0.0192718543112278, -0.10593382269144058, -0.06309846043586731, 0.04563596472144127, 0.00995886791497469, 0.10713832825422287, -0.01221234817057848, -0.036850687116384506, 0.033619362860918045, 0.04701480641961098, -0.005950779654085636, -0.03392145782709122, 0.07599766552448273, -0.04290580376982689, -0.0024570266250520945, -0.011971671134233475, 0.0010343084577471018, -0.019813165068626404, -0.10429257154464722, 0.009167736396193504, 0.04543996974825859, -0.07077108323574066, -0.02569623477756977, -0.10022741556167603, 0.04617616906762123, -0.053045932203531265, 0.027653321623802185, 0.013290592469274998, 0.02996361441910267, -0.05269424244761467, 0.003762288950383663, -0.030663590878248215, 0.02371843159198761, 0.05403951182961464, -0.05158252269029617, 0.009159283712506294, -0.01431918703019619, 0.0056009721010923386, 0.015174786560237408, 0.0036053513176739216, -0.057713933289051056, -0.05947752669453621, -0.00868302583694458, 0.014323134906589985, 0.048973191529512405, -0.04860244318842888, -0.03685664385557175, -0.011737708933651447, -0.00410674512386322, -0.010865646414458752, 0.019701281562447548, 0.0008306615054607391, -0.04011800140142441, 0.039798617362976074, 0.0016416216967627406, 0.03380001336336136, -0.005987880751490593, -0.013224792666733265, -0.08189374953508377, 0.05506613850593567, 0.010268669575452805, -0.05403716117143631, 0.051026251167058945, 0.06436183303594589, 0.011491992510855198, 0.02456221543252468, -0.0018834189977496862, 0.011763259768486023, -0.04186257719993591, -0.005897124297916889, 0.06487086415290833, 0.01734207570552826, -0.016104713082313538, 0.011793195270001888, -0.02161196805536747, -0.05859933793544769, -0.01224586833268404, 0.025607101619243622, -0.0007727413321845233, 0.03790409117937088, -0.07852527499198914, -0.06613929569721222, 0.04414720833301544, 0.004246891010552645, -0.0019182235701009631, -0.027561437338590622, 0.03532395139336586, -0.010871031321585178, -0.007228723727166653, -0.058806490153074265, 0.0315825417637825, 0.002356228418648243, 0.010522465221583843, 0.01053412351757288, 0.009267117828130722, -0.0005783266387879848, -0.11128998547792435, -0.037453822791576385, 0.04119415581226349, 0.06331133842468262, -0.06593331694602966, -0.03009156323969364, 0.005830024369060993, -0.015301326289772987, 0.0639624148607254, -0.08692221343517303, -0.04368786886334419, -0.06261581182479858, -0.028231246396899223, 0.0650084912776947, -0.004538291599601507, -0.03415801376104355, -0.04237174242734909, -0.004537565633654594, 0.0316222608089447, 0.05326247587800026, -0.006721071433275938, 0.006270885933190584, -0.004834132269024849, 0.009473048150539398, -0.12995965778827667, -0.019033558666706085, -0.030091997236013412, -0.04016031697392464, -0.013356379233300686, -0.04611390456557274, -0.037030406296253204, 0.06481891870498657, -0.00043248344445601106, 0.06312115490436554, 0.04814097285270691, 0.014697014354169369, 0.026570754125714302, 9.521908395981882e-06, -0.03665539249777794, -0.040359191596508026, 0.017488883808255196, 0.005766736343502998, -0.021532949060201645, 0.03820880129933357, 0.008457479067146778, -0.09540776908397675, -0.07724554091691971, 0.076656274497509, 0.03478353098034859, 0.048304010182619095, -0.05352611839771271, 0.013086570426821709, 0.015652386471629143, 0.029613329097628593, -0.0075232405215501785, -0.05945055931806564, -0.0190621055662632, -0.010964789427816868, 0.0362972728908062, 0.02199508622288704, 0.004663004074245691, -0.011964447796344757, 0.06220083311200142, -0.037545256316661835, -0.041507914662361145, -0.06125476956367493, -0.03905768692493439, -0.01675221137702465, -0.034066636115312576, 0.02435414306819439, -0.07611546665430069, 0.00769624812528491, 0.0848551020026207, -0.07100123167037964, 0.018555641174316406, -0.005632030311971903, 0.006908046081662178, -0.0055297259241342545, -0.08888351172208786, -0.03223569318652153, -0.06726007163524628, -0.01219393964856863, -0.04564674198627472, 0.05271918699145317, -0.06179143488407135, 0.0691821426153183, 0.011431879363954067, -0.07978688925504684, 0.05844276025891304, -0.03847489133477211, 0.10611085593700409, -0.017817558720707893, -0.011330788023769855, 0.09660382568836212, 0.02229488082230091, -0.01182374358177185, 0.04761607199907303, -0.024450166150927544, 0.04371095821261406, -0.05985410511493683, 0.038965269923210144, -0.048969708383083344, -0.06493675708770752, -0.0896877646446228, 0.1172742024064064, -0.036316629499197006, 0.03996392339468002, 0.0649866983294487, 0.03842168673872948, -0.023712504655122757, 0.014678101986646652, -0.03549673408269882, -0.0009559061727486551, 0.06015339493751526, 0.026254240423440933, 0.07619985938072205, 0.029095632955431938, -0.033751651644706726, 0.03581675514578819, -0.014301915653049946, -0.044438425451517105, 0.04603435471653938, 0.011891841888427734, 0.058141861110925674, 0.02764269709587097, -0.06676334142684937, 0.01146682258695364, -0.004679266829043627, 0.0025969643611460924, 0.03611718863248825, -0.04108969122171402, -0.04096079617738724, -0.005616472568362951, -0.0945887342095375, 0.0655260980129242, 0.004121582489460707, 0.007337674964219332, 0.0234698373824358, -0.012017599307000637, 0.047488048672676086, 0.011694743297994137, 0.03143719583749771, -0.03596891835331917, 0.028992442414164543, 0.020005570724606514, 0.04200209304690361, 0.03905043005943298, 0.01565818302333355, -0.013279054313898087, 0.030143773183226585, -0.05161277949810028, 0.01098337210714817, -0.02689988911151886, -0.039483871310949326, 0.027336906641721725, -0.015899378806352615, -0.04728344827890396, -0.05713384225964546, -0.023479342460632324, -0.027034226804971695, 0.011216576211154461, 0.1438036412000656, 0.0032178221736103296, -0.06166895106434822, -0.01820106990635395, 0.01157503854483366, -0.0022787803318351507, -0.008855403400957584, -0.009197788313031197, -0.002269970253109932, 0.0412340946495533, -0.005612284876406193, -0.052139319479465485, 0.03918209299445152, 0.06450735777616501, -0.02808244712650776, -0.014859280548989773, -0.017405647784471512, 0.022825241088867188, 0.029997315257787704, 0.007993236184120178, 0.04152786731719971, -0.01224396750330925, -0.03317224979400635, 0.01408843882381916, -0.11431431770324707, -0.0027010811027139425, -0.007042501587420702, 0.07577494531869888, 0.02703557349741459, -0.03616227209568024, 0.034273382276296616, -0.030469544231891632, 0.005303418729454279, -0.0012820340925827622, -0.08303651958703995, -0.01664787344634533, 0.02029000036418438, -0.07515551149845123, 0.032739751040935516, -0.007496497128158808, -0.03279479593038559, -0.04291132465004921, -0.046809036284685135, -0.06297798454761505, 0.0307016484439373, 0.012766484171152115, -0.057875871658325195, -0.011257811449468136, 0.008509236387908459, -0.044404286891222, 0.02408233843743801, 0.03148852661252022, -0.05459650978446007, 0.01788332872092724, 0.0408405102789402, -0.009409700520336628, 0.0009153562714345753, -0.01782812364399433, 0.023216230794787407, -0.05048728734254837, -0.023923350498080254, 0.0113343121483922, -0.008175315335392952, -0.011682645417749882, 0.013839934952557087, 0.009038100019097328, -0.03794606029987335, -0.05566505715250969, -0.04062580317258835, -0.03341340646147728, -0.019211549311876297, -0.07616250216960907, 0.06234432011842728, -0.0569034069776535, 0.012230970896780491, 0.01956973411142826, -0.02244914323091507, -0.03878162056207657, 0.1069532036781311, 0.052813153713941574, 0.034992627799510956, 0.01474002469331026, 0.02373337931931019, 0.04855676740407944, -0.05228753387928009, 0.08084557950496674, -0.0016824724152684212, -0.015544240362942219, 0.00396233145147562, -0.021036000922322273, 0.0027237588074058294, -0.060337185859680176, 0.009135916829109192, -0.0810125395655632, 0.004549064673483372, 0.018311182036995888, -0.00645826943218708, 0.13103033602237701, -0.011314391158521175, 0.03717421740293503, 0.007363174110651016, -0.020765356719493866, 0.006082064472138882, -0.011002426035702229, 0.045744333416223526, -0.12130610644817352, 0.05544601008296013, -0.04906220734119415, -0.07055932283401489, 0.04682491347193718, -0.035659730434417725, -0.028818989172577858, 0.0364239402115345, 0.0011446216376498342, -0.04545788839459419, -0.017047686502337456, 0.012690975330770016, -0.08226163685321808, -0.024428363889455795, 0.0034228735603392124, -0.023592954501509666, -0.058654867112636566, 0.02548949606716633, 0.03632239252328873, -0.05033865571022034, 0.031188050284981728, -0.06126892566680908, 0.05270698666572571, -0.0012095161946490407, 0.04691915214061737, 0.014999225735664368, -0.07093212753534317, 0.07240463048219681, 0.010537616908550262, 0.04044210538268089, 0.08972042053937912, -0.0020829695276916027, 0.01784217357635498, 0.012581034563481808, 0.0042886328883469105, -0.03436267003417015, 0.06377048790454865, 0.019200056791305542, -0.047247666865587234, 0.0146805290132761, 0.02093110978603363, -0.03248746320605278, 0.010030518285930157, -0.052818551659584045, 0.07336865365505219, 0.06191754713654518, 0.09041254222393036, -0.017194930464029312, 0.025930408388376236, 0.03526847064495087, 0.017598703503608704, 0.01732831820845604, -0.04772169888019562, 0.03778064623475075, -0.0076132300309836864, 0.0005154777318239212]', 'uploads/faces/user_8_angle4_20260729_163101_982488.jpg'),
(11, NULL, 11, '', '', '2026-07-29 11:07:44', '[-0.00019667480955831707, 0.005605833604931831, -0.08670241385698318, 0.010746898129582405, -0.07508039474487305, -0.019850172102451324, -0.037312187254428864, 0.042804185301065445, 0.020829930901527405, 0.011957059614360332, -0.005080924369394779, -0.023609695956110954, -0.011206049472093582, 0.019609909504652023, -0.0023885087575763464, -0.16303853690624237, -0.07489462196826935, -0.03245916590094566, 0.03153427317738533, -0.02071322873234749, -0.0016467003151774406, 0.00032488341093994677, -0.035518188029527664, -0.028779659420251846, 0.027365202084183693, -0.03471310809254646, -0.06674697995185852, 0.02055494487285614, 0.021127359941601753, -0.03512638434767723, 0.027463825419545174, 0.028520138934254646, -0.04484210163354874, 0.0641467273235321, 0.04116980731487274, -0.015262730419635773, -0.006776695605367422, 0.02530595473945141, -0.013338089920580387, 0.010036658495664597, -0.046625617891550064, -0.06300001591444016, 0.011939777061343193, -0.01028379611670971, 0.0013676155358552933, 0.0034449647646397352, -0.009959923103451729, -0.02478787861764431, 0.04843428358435631, 0.08019702881574631, -0.022718360647559166, -0.03886969015002251, -0.0055328174494206905, 0.0029938500374555588, 0.029741736128926277, 0.0010442315833643079, 0.01742224581539631, -0.01620173268020153, 0.03550829365849495, 0.023275602608919144, 0.07000543922185898, 0.0018051372608169913, -0.03413164243102074, -0.040906697511672974, 0.02344581112265587, -0.02092679962515831, -0.015575602650642395, 0.012022323906421661, -0.032815221697092056, -0.015688924118876457, 0.02587365172803402, 0.060690104961395264, 0.05508186295628548, 0.004698300268501043, 0.026305291801691055, 0.02017425000667572, -0.009250323288142681, 0.033800363540649414, 0.06293224543333054, 0.06687617301940918, 0.12690521776676178, -0.029795367270708084, 0.0073141069151461124, -0.022006187587976456, 0.07194697856903076, -0.0538247786462307, 0.033377520740032196, 0.025775345042347908, -0.07728021591901779, -0.010540285147726536, 0.0815419852733612, 0.0907302051782608, -0.008887352421879768, -0.013093069195747375, -0.02755594253540039, -0.05457364767789841, -0.035849861800670624, 0.06853672116994858, 0.03360440209507942, 0.03438064083456993, 0.002121314173564315, -0.09025484323501587, 0.014931173995137215, 0.057392772287130356, 0.022570807486772537, -0.08529173582792282, 0.031632352620363235, 0.020468659698963165, 0.06923115998506546, 0.0626789927482605, 0.031054381281137466, -0.01812196895480156, 0.002228326862677932, 0.008069266565144062, -0.0115592572838068, 0.01569644920527935, -0.03767380863428116, 0.0384792760014534, -0.10625189542770386, 0.001531094778329134, -0.0544566847383976, -0.016147878021001816, -0.00033988652285188437, -0.09055730700492859, -0.016995446756482124, 0.01637563854455948, 0.06562405079603195, -0.03950520604848862, -0.01953679881989956, 0.021561821922659874, -0.05601201578974724, 0.009871508926153183, -0.0012048076605424285, -0.009109350852668285, 0.007106128614395857, 0.01155316922813654, -0.027149472385644913, 0.04237865284085274, -0.02097506634891033, -0.04690602049231529, -0.032604143023490906, -0.0642644464969635, -0.0229635089635849, 0.01684759370982647, -0.019142981618642807, 0.019707482308149338, 0.020831482484936714, -0.04946695640683174, 0.04523400217294693, 0.021753324195742607, 0.022475600242614746, -0.02567770704627037, -0.02053799480199814, -0.03454214334487915, -0.03314656764268875, -0.01763930916786194, -0.042109522968530655, -0.05829625204205513, 0.06377469003200531, 0.05076136812567711, -0.03679447993636131, -0.026310477405786514, 0.09975026547908783, 0.01788967289030552, 0.003973227459937334, 0.019873812794685364, -0.0362468883395195, -0.0325029231607914, -0.04049497842788696, -0.02690357156097889, -0.010956598445773125, -0.014797127805650234, 0.08804937452077866, 0.007854606956243515, 0.007560960482805967, -0.05465281009674072, 0.025041336193680763, 0.020433606579899788, 0.10079768300056458, 0.011300128884613514, -0.0373695082962513, 0.00902293436229229, 0.05650738999247551, -0.006402464117854834, 0.012706656940281391, 0.020359957590699196, -0.01623588241636753, 0.043183211237192154, 0.024377143010497093, -0.018455659970641136, 0.007978730835020542, 0.016511572524905205, -0.03359755501151085, 0.003238522447645664, -0.012917554937303066, 0.1323074847459793, 0.05850730463862419, 0.04225713759660721, -0.032689232379198074, -0.0769989863038063, -0.0012578410096466541, 0.023973630741238594, -0.03285463526844978, 0.030321680009365082, -0.052341628819704056, -0.0686173364520073, -0.02451394498348236, -0.0635032057762146, 0.01562838815152645, 0.000988891115412116, -0.02574200928211212, -0.02557419240474701, -0.06790182739496231, -0.009061314165592194, -0.04733559861779213, -0.011545231565833092, 0.02055935189127922, -0.00834763702005148, -0.045433491468429565, 0.020260991528630257, -0.0026254840195178986, 0.0549277737736702, -0.037614043802022934, 0.017572132870554924, -0.014309439808130264, 0.053108859807252884, 0.013827810063958168, 0.05849730223417282, -0.009949721395969391, 0.023180823773145676, -0.025814609602093697, 0.018383029848337173, -0.02532452717423439, 0.021720709279179573, 0.11320889741182327, -0.0623520128428936, -0.04182044789195061, 0.04693754389882088, -0.04246398061513901, 0.020728588104248047, -0.023658759891986847, -0.061681993305683136, -0.06324523687362671, 0.016589565202593803, 0.12486051768064499, 0.0289307814091444, 0.04432307928800583, 0.04186682775616646, 0.03844781965017319, -0.08552860468626022, 0.0762040913105011, 0.011349968612194061, -0.015878520905971527, 0.06482318788766861, 0.04712124541401863, 0.0008577745757065713, 0.09751313924789429, -0.05294385552406311, -0.0848710760474205, -0.0022088768891990185, 0.016355091705918312, -0.054260797798633575, 0.03205066919326782, 0.024212703108787537, -0.020249681547284126, 0.0008786242688074708, -0.026766814291477203, 0.08542325347661972, -0.09081390500068665, -0.04922264814376831, -0.0068435980938375, -0.029646489769220352, -0.06189616769552231, -0.0730171650648117, 0.04424676671624184, -0.0729803517460823, 0.05355755239725113, -0.03578444570302963, 0.017745744436979294, 0.0025942199863493443, -0.04795064032077789, -0.03600618243217468, -0.0027445503510534763, -0.00802155863493681, -0.09604578465223312, -0.0016084741801023483, 0.09039732068777084, -0.004960077814757824, -0.012611882761120796, 0.010554949752986431, -0.01593852788209915, -0.04130198433995247, 0.08385607600212097, 0.013965345919132233, 0.09805703163146973, -0.013353505171835423, -0.0290323868393898, -0.008645799942314625, -0.015678081661462784, -0.06032690405845642, -0.02397603541612625, -0.04127461090683937, -0.027218474075198174, 0.08065463602542877, -0.019693197682499886, 0.05432778224349022, -0.002186462050303817, 0.024498747661709785, -0.026465049013495445, 0.03149345517158508, 0.030093850567936897, -0.05164822190999985, -0.01597319170832634, 0.02308814600110054, -0.03438686951994896, 0.04585367068648338, 0.03415045887231827, -0.045738786458969116, 0.024674728512763977, -0.08048317581415176, -0.039309557527303696, -0.07320483028888702, 0.021483516320586205, 0.011477205902338028, -0.03340078890323639, -0.010697241872549057, 0.02504233829677105, 0.08244886994361877, -0.02296348474919796, 0.039782699197530746, 0.008510864339768887, 0.005759050138294697, 0.08423919230699539, -0.021002264693379402, -0.011666533537209034, -0.08936075866222382, -0.05797832831740379, 0.01674254983663559, -0.043352529406547546, 0.031344883143901825, 0.022338155657052994, 0.020962471142411232, -0.0340300053358078, 0.06334079802036285, 0.0085640549659729, 0.033582042902708054, -0.0886489748954773, 0.019767453894019127, -0.056206218898296356, -0.08052179962396622, -0.04045683890581131, 0.0025892755948007107, 0.04757808521389961, 0.005785604007542133, -0.07257118076086044, -0.06903547048568726, 0.07361926138401031, 0.03450951725244522, 0.042120691388845444, 0.04228495806455612, 0.06992624700069427, 0.006337577477097511, -0.0019664473365992308, 0.002293569501489401, -0.011928444728255272, -0.014178460463881493, -0.026182370260357857, 0.035765454173088074, -0.03747175261378288, -0.017289716750383377, -0.05641733109951019, -0.03443685173988342, -0.0053103710524737835, 0.002221120521426201, -0.00666348822414875, -0.01320281159132719, 0.04740287363529205, 0.016610832884907722, -0.003900039242580533, 0.00165354844648391, 0.04671698436141014, -0.02604583464562893, 0.05937223881483078, -0.020921921357512474, -0.027175521478056908, -0.0639391615986824, -0.0031554200686514378, 0.0010616118088364601, -0.0065444353967905045, 0.024706343188881874, -0.07305731624364853, 0.04291004687547684, -0.012263665907084942, -0.02469349279999733, -0.060666412115097046, -0.00013328449858818203, -0.03048841841518879, -0.058753084391355515, -0.04626350477337837, -0.030179902911186218, 0.022414619103074074, 0.02900221198797226, -0.01674710586667061, 0.026109911501407623, 0.04812078922986984, -0.07199729233980179, -0.02853451855480671, -0.024563809856772423, 0.006556485779583454, -0.03675837442278862, -0.016529181972146034, -0.010546146892011166, -0.03836758807301521, 0.045580070465803146, -0.05299726128578186, 0.01673944666981697, 0.02640935778617859, -0.008338088169693947, 0.08728012442588806, -0.01233130507171154, 0.020334988832473755, 0.007290435489267111, -0.03948590159416199, -0.05119005963206291, -0.10660313069820404, -0.001275244983844459, -0.030461404472589493, -0.07233304530382156, -0.04389859363436699, -0.08104611188173294, 0.028914684429764748, 0.02096194215118885, 0.03679840266704559, 0.002928808331489563, -0.02152092568576336, -0.015118355862796307, 0.013283336535096169, 0.002295937156304717, -0.0070733786560595036, -0.021784065291285515, -0.024146627634763718, 0.007227787747979164, -0.09831300377845764, -0.03697872906923294, 0.013364466838538647, 0.038455355912446976, -0.004018409177660942, -0.007922815158963203, -0.060683999210596085, 0.047724634408950806, 0.038492027670145035, 0.03784357011318207, 0.04396963119506836, 0.07581382244825363, 0.07086038589477539, 0.011282921768724918, 0.0030357653740793467, 0.060615815222263336, -0.05147944390773773, -0.012380293570458889, 0.024102885276079178, -0.02607349120080471, -0.006196413654834032, -0.01819959282875061, 0.010568148456513882, 0.0636013075709343, -0.014430107548832893, 0.024393901228904724, -0.09644725918769836, -0.07991234958171844, -0.02215457893908024, -0.009473477490246296, 0.03523006662726402, -0.05803319439291954, 0.08501110970973969, 0.10812503844499588, -0.07634942978620529, -0.07397157698869705, -0.0372789092361927, 0.008558741770684719, 0.057386577129364014, 0.017161475494503975, -0.03955372795462608, -0.040695108473300934, 0.0022988265845924616, 0.029520055279135704, -0.05905894562602043, 0.002929079346358776, -0.06720667332410812, 0.08606506884098053, -0.005164328496903181, 0.003465748857706785, 0.06645403802394867, 0.01584824174642563, -0.0472579225897789, 0.016189955174922943, 0.05214439705014229, -0.07328056544065475, 0.07920560240745544, -0.04849466681480408, -0.04283996298909187, -0.057444240897893906, 0.025349322706460953, -0.004642723593860865, -0.022955937311053276, 0.08450496941804886, 0.06547453254461288, 0.07889283448457718, -0.04067610204219818, 0.021237168461084366, 0.056431714445352554, 0.0030736227054148912]', 'uploads/faces/user_11_angle0_20260729_163740_162448.jpg'),
(12, NULL, 11, '', '', '2026-07-29 11:07:44', '[0.055044908076524734, -0.020621633157134056, -0.002506017219275236, -0.018397238105535507, -0.09684167802333832, -0.020540520548820496, -0.015067888423800468, 0.009953810833394527, 0.022924968972802162, 0.009589841589331627, -0.020446106791496277, -0.025201525539159775, 0.013931164517998695, 0.029189977794885635, -0.015751585364341736, -0.10041031986474991, -0.08345170319080353, -0.005774314049631357, 0.031835634261369705, -0.05200896039605141, 0.004386521875858307, 0.04681015387177467, -0.03989936783909798, 0.0001791611430235207, 0.036140602082014084, -0.002187720499932766, 0.01139927376061678, 0.028684113174676895, 0.044207412749528885, -0.021745067089796066, -0.02053033374249935, 0.04439441114664078, -0.057138800621032715, 0.025619257241487503, 0.04054680094122887, 0.006792097818106413, -0.040989480912685394, 0.02423432655632496, 0.022872820496559143, 0.05837401747703552, 0.006801435258239508, -0.025736123323440552, 0.02025008201599121, 0.036922257393598557, 0.04790080338716507, 0.0022428713273257017, 0.009825436398386955, 0.017238669097423553, 0.04081666097044945, 0.05408264324069023, 0.016396747902035713, -0.011923213489353657, -0.041756827384233475, -0.001816905802115798, 0.033201925456523895, 0.05919064208865166, 0.04025685787200928, 0.030263444408774376, 0.038860563188791275, 0.015829280018806458, -0.03591318801045418, -0.06881681829690933, 0.026905270293354988, -0.06933004409074783, 0.012933604419231415, -0.0015713619068264961, -0.012595638632774353, -0.033967193216085434, -0.006737804505974054, -0.015531053766608238, 0.05646796151995659, 0.027712807059288025, 0.07336671650409698, -0.03210237994790077, 0.025230398401618004, -0.05448835715651512, 0.026534060016274452, -0.0008850491722114384, 0.019553640857338905, 0.028658218681812286, 0.06446219235658646, -0.07316922396421432, 0.05413324758410454, 0.02137349359691143, -0.0014532717177644372, -0.06052536517381668, -0.060405854135751724, -0.006419123616069555, -0.03798283264040947, 0.014330361969769001, 0.0011543047148734331, 0.1356012374162674, -0.0016410868847742677, 0.025651128962635994, -0.04652886837720871, -0.07550165802240372, 0.01322018913924694, 0.12936504185199738, 0.03993283584713936, 0.035565175116062164, -0.02327125333249569, -0.05321112647652626, -0.003892337903380394, 0.047918565571308136, 0.036375466734170914, -0.06079549342393875, -0.0011937941890209913, -0.011624756269156933, 0.06383693963289261, 0.05063615366816521, 0.019308902323246002, 0.03420846909284592, -0.03614726662635803, -0.045956045389175415, -0.016698500141501427, 0.03320638835430145, 0.004971067421138287, 0.029721926897764206, -0.0613967701792717, 0.03905027359724045, -0.08179035037755966, 0.01879679039120674, 0.018922552466392517, -0.03711343929171562, -0.025090491399168968, 0.023344140499830246, 0.10718777030706406, 0.0044184159487485886, -0.008473425172269344, 0.009197544306516647, -0.03694350644946098, 0.059276316314935684, -0.023450741544365883, -0.017659520730376244, -0.022394809871912003, -0.02525247447192669, -0.008161047473549843, 0.04391546919941902, 0.015705009922385216, -0.019660012796521187, -0.01888306625187397, -0.009792882949113846, -0.06143808737397194, 0.013939094729721546, 0.05515236407518387, 0.0320785790681839, 0.03124593198299408, -0.04950379952788353, 0.07318594306707382, 0.027611413970589638, 0.0039832149632275105, 0.007273492403328419, -0.018340211361646652, 0.02984141744673252, -0.032340992242097855, 0.005701419431716204, -0.044837091118097305, -0.05374688655138016, 0.008505729958415031, 0.024127637967467308, 0.003379733767360449, -0.008362878113985062, 0.05522596091032028, 0.04135490953922272, 0.005677371751517057, -0.013549411669373512, -0.02687562257051468, -0.007091266103088856, -0.09190692007541656, -0.009474657475948334, -0.023168452084064484, -0.031057192012667656, 0.02713046967983246, -0.02203427627682686, 0.014464369043707848, -0.021166659891605377, -0.009005050174891949, -0.004612804390490055, 0.04608815908432007, 0.007389376405626535, 0.012367413379251957, 0.02902010828256607, 0.002326627029106021, -0.00783847738057375, 0.00996362417936325, 0.010257868096232414, 0.01202358864247799, 0.02863781899213791, 0.03847496956586838, 0.02140992134809494, 0.017926983535289764, -0.0353241041302681, -0.02964690513908863, -0.03752505034208298, -0.005392579361796379, 0.0912422239780426, 0.07781899720430374, 0.06261973083019257, -0.017274701967835426, -0.042996034026145935, 0.02325708046555519, -0.0625799149274826, -0.01465027965605259, 0.07228958606719971, -0.032298166304826736, 0.005853279493749142, -0.005157243460416794, -0.06742803007364273, -0.013182359747588634, -0.058658696711063385, -0.03105982020497322, -0.018645912408828735, -0.053642794489860535, 0.02207585796713829, -0.012424344196915627, 0.03126636892557144, 0.01593031734228134, -0.01949715055525303, -0.07748981565237045, -0.006515177432447672, 0.0032865190878510475, 0.04103171452879906, -0.035122960805892944, -0.011939981020987034, 0.07983913272619247, 0.05153860151767731, -0.017259960994124413, 0.09312161058187485, 0.044305458664894104, 0.025657741352915764, -0.003622563788667321, -0.03474784269928932, -0.043031979352235794, 0.06138211116194725, 0.07186709344387054, -0.07382699847221375, -0.07039954513311386, 0.04684952273964882, -0.0461137630045414, -0.020445441827178, -0.009420107118785381, -0.054259561002254486, -0.047841593623161316, 0.009741066955029964, 0.1346658617258072, 0.043369121849536896, -0.00014762916543986648, 0.054203759878873825, -0.02359781041741371, -0.10214512050151825, 0.07871083170175552, 0.07410944253206253, -0.04949767142534256, 0.053791746497154236, 0.045341748744249344, -0.006556888576596975, 0.0913601666688919, -0.08089423924684525, -0.028648966923356056, -0.047823067754507065, -0.009020688012242317, -0.07895371317863464, 0.011614720337092876, 0.019373269751667976, -0.06940898299217224, -0.005545791704207659, -0.014862005598843098, 0.07787328958511353, -0.11342337727546692, -0.03889606148004532, -0.00320150307379663, -0.06995692104101181, -0.0586412250995636, -0.03601538762450218, 0.01949983835220337, -0.07484111189842224, 0.012826734222471714, -0.023759454488754272, 0.0037549883127212524, -0.03355804458260536, -0.04312097653746605, -0.05695969611406326, 0.0016811391105875373, -0.07926960289478302, -0.036861758679151535, 0.056872088462114334, 0.07223257422447205, 0.015975303947925568, 0.024014219641685486, -0.014453769661486149, -0.01104289572685957, -0.03967709466814995, 0.021960824728012085, 0.03761642053723335, 0.031628403812646866, -0.03704986348748207, -0.028650647029280663, 0.0063256812281906605, 0.004050645977258682, 0.010645050555467606, -0.02690281718969345, -0.06100872904062271, -0.012640943750739098, 0.053479719907045364, -0.013477042317390442, 0.053173553198575974, -0.012559492141008377, 0.029665524140000343, -0.05133361741900444, 0.009106619283556938, 0.0373128205537796, -0.015150790102779865, -0.062445029616355896, 0.01510533969849348, -0.0005593523965217173, 0.02993740886449814, 0.03984449431300163, -0.07883404195308685, 0.07555948942899704, -0.017944147810339928, 0.0021362388506531715, -0.034194257110357285, 0.023629626259207726, -0.05601176992058754, -0.04889344424009323, -0.012900580652058125, 0.03445058688521385, 0.065064437687397, -0.013205816969275475, 0.03230847045779228, -0.03847485035657883, 0.021170224994421005, 0.052868615835905075, -0.008110176771879196, -0.015411661937832832, -0.05603322759270668, -0.08294738084077835, 0.02736918069422245, -0.021763920783996582, 0.06820660829544067, 0.03053789958357811, -0.045801687985658646, -0.02461203932762146, 0.04728216305375099, -0.04094057157635689, 0.03190494701266289, -0.10182412713766098, 0.06427177786827087, -0.09775606542825699, -0.03871241211891174, -0.07745916396379471, -0.03112099878489971, -0.008600170724093914, 0.0033661776687949896, -0.14178231358528137, -0.0366944782435894, 0.04018273204565048, 0.04188691824674606, 0.00043127237586304545, 0.1016000509262085, 0.05179210379719734, -7.54754580611916e-07, -0.030173439532518387, 0.054678626358509064, 0.002094545401632786, -0.005723454523831606, -0.030254848301410675, 0.025289159268140793, -0.04866078495979309, -0.03371873497962952, -0.008544187061488628, 0.01437386590987444, -0.02696734294295311, 0.016980674117803574, -0.0095290532335639, 0.014726518653333187, 0.03344481438398361, 0.043137967586517334, 0.02401324361562729, 0.032097939401865005, 0.011507825925946236, -0.02509377710521221, 0.03984283655881882, -0.002213258296251297, -0.03421992436051369, -0.09249462932348251, 0.03813305124640465, -0.005066032521426678, 0.057381656020879745, 0.013245970942080021, -0.11829616874456406, 0.027774685993790627, -0.022457031533122063, -0.014087078161537647, -0.0021423546131700277, 0.024360425770282745, -0.0631098523736, -0.06347508728504181, -0.019052481278777122, -0.06729727238416672, -0.007421191781759262, 0.039532069116830826, -0.051006875932216644, 0.03694990649819374, 0.03345237672328949, 0.0016624487470835447, -0.05841471254825592, -0.05112271010875702, 0.04633847996592522, 0.036003123968839645, 0.014297302812337875, -0.03495853394269943, -0.08695615082979202, 0.02723321132361889, -0.018889788538217545, 0.022343961521983147, 0.030197657644748688, 0.01985327899456024, 0.06282702833414078, -0.03999662026762962, 0.025299493223428726, 0.03289242461323738, -0.09199786931276321, -0.010823477059602737, -0.09122117608785629, 0.05486276000738144, 0.033996496349573135, 0.009319225326180458, -0.055337708443403244, -0.049603238701820374, 0.02652721479535103, 0.007246208842843771, 0.02420073002576828, 0.0026814283337444067, 0.010555827990174294, 0.010273867286741734, 0.009790284559130669, 0.006669575348496437, -0.029788970947265625, -0.07176173478364944, -0.012332799844443798, 0.037371132522821426, -0.10738646239042282, -0.03509274870157242, 0.04254873842000961, 0.07115400582551956, -0.009783681482076645, -0.03241337463259697, -0.05962103605270386, 0.050994712859392166, 0.09000596404075623, 0.013073774985969067, 0.03857598826289177, 0.034562528133392334, 0.009489113464951515, 0.04324960708618164, -0.0018628736725077033, 0.07566509395837784, -0.06928312033414841, -0.011127547360956669, 0.04063665494322777, 0.03813260421156883, -0.0663958266377449, 0.058604657649993896, 0.02433771826326847, 0.07094280421733856, -0.04209643974900246, 0.02605784684419632, -0.05337942764163017, -0.09429408609867096, -0.0032600450795143843, 0.00981565285474062, 0.05844005569815636, -0.0077045271173119545, 0.0617717020213604, 0.07078295946121216, -0.03634677827358246, -0.045258305966854095, -0.0288239698857069, 0.02082427218556404, 0.07594361156225204, 0.042822424322366714, -0.030935047194361687, -0.06425247341394424, 0.0005788613925687969, -0.0019276946550235152, 0.026608681306242943, 0.04110301658511162, -0.06742629408836365, 0.09026844054460526, 0.06877563148736954, -0.015666531398892403, -0.004044645931571722, -0.024563150480389595, -0.05683668330311775, -0.0045280447229743, 0.061691127717494965, -0.09678991138935089, 0.03502959758043289, -0.01150335744023323, -0.0503060519695282, -0.04708990827202797, 0.05701905116438866, 0.03014368750154972, -0.020273534581065178, 0.061384622007608414, 0.02280985750257969, -0.003392063546925783, -0.046749845147132874, 0.011587608605623245, 0.010887601412832737, -0.024861034005880356]', 'uploads/faces/user_11_angle1_20260729_163741_518958.jpg'),
(13, NULL, 11, '', '', '2026-07-29 11:07:44', '[-0.011946398764848709, -0.0007519670762121677, -0.052464649081230164, 0.022249815985560417, -0.067220538854599, 0.001118754968047142, -0.058049146085977554, 0.004274468868970871, 0.01101758237928152, 0.010181849822402, 0.005575088318437338, -0.05295514315366745, 0.004294510465115309, 0.03254197910428047, -0.036751121282577515, -0.13213196396827698, -0.019894186407327652, -0.022242387756705284, 0.01417852658778429, -0.008576901629567146, 0.033249035477638245, -0.0025115618482232094, -0.05492148548364639, -0.060144826769828796, 0.07469114661216736, -0.00853085145354271, -0.04367944970726967, -0.004730117041617632, 0.07248173654079437, -0.02322804182767868, -0.02142726443707943, 0.003811599686741829, -0.019480261951684952, -0.013653666712343693, 0.06177862361073494, -0.03421410545706749, -0.03891022130846977, 0.0024317221250385046, -0.001731443451717496, 0.04840368032455444, -0.02973695658147335, -0.04296912997961044, -0.009110367856919765, 0.06765161454677582, -0.00037033663829788566, 0.004760135430842638, -0.05554709583520889, 0.010783436708152294, 0.06571035832166672, 0.10821491479873657, -0.017937935888767242, -0.0290259700268507, -0.012219870463013649, -0.008426065556704998, 0.021228281781077385, 0.05987488850951195, 0.03807616978883743, 0.008059859275817871, 0.050144243985414505, 0.025605741888284683, 0.024423910304903984, -0.032960809767246246, -0.027335798367857933, -0.009493411518633366, 0.013055718503892422, -0.02191082015633583, -0.013371258042752743, -0.02900610864162445, 0.032068368047475815, 0.029998309910297394, 0.005518346559256315, -0.0050947717390954494, 0.06250255554914474, -4.778329639520962e-06, 0.09668722748756409, 0.007170842494815588, -0.02000150829553604, -0.026061715558171272, 0.013224523514509201, -0.020771214738488197, 0.08543828129768372, -0.07540173828601837, 0.011582602746784687, 0.029311057180166245, 0.0068372273817658424, -0.005710090044885874, -0.02277868054807186, -0.03543854132294655, -0.06888464838266373, -0.007861326448619366, 0.016607867553830147, 0.09392214566469193, 0.047365494072437286, 0.0015024786116555333, -0.07284672558307648, -0.0628667026758194, -0.027368854731321335, 0.09125019609928131, 0.06005152314901352, 0.051416754722595215, -0.0026365204248577356, -0.06036916375160217, 0.03680608049035072, 0.027880391106009483, 0.025523249059915543, -0.01935485191643238, 0.04199538752436638, -0.001987662399187684, -0.026687471196055412, 0.05994972214102745, 0.0020359279587864876, -0.0013478639302775264, -0.04512302577495575, -0.0005804635002277792, -0.024220973253250122, 0.049016356468200684, -0.005433510523289442, -0.024425745010375977, -0.013619608245790005, 0.01824289560317993, -0.08094047009944916, -0.004098666366189718, -0.04376863315701485, 0.013594334945082664, 0.012767812237143517, 0.06941256672143936, 0.06857933104038239, -0.08358965814113617, 0.029788494110107422, 8.681928193254862e-06, -0.049805596470832825, -0.008578900247812271, -0.001520379213616252, 0.007977952249348164, -0.06018199399113655, -0.01957377791404724, -0.01823030784726143, 0.08143173903226852, 0.010943721979856491, -0.09016235172748566, 0.005709709133952856, -0.042492110282182693, -0.03576173260807991, -0.04549599066376686, -0.008083086460828781, 0.01260646153241396, 0.007488912437111139, -0.022404847666621208, 0.04295414686203003, 0.00843131635338068, -0.02217954397201538, -0.011655556969344616, -0.02789311297237873, 0.0024371594190597534, 0.002023305743932724, 0.0737786591053009, -0.06436002254486084, -0.00018838503456208855, 0.0005428821314126253, 0.02727513574063778, -0.024268513545393944, -0.030738282948732376, 0.06833747029304504, -0.04330674186348915, -0.02043212205171585, 0.03978538513183594, -0.017309855669736862, -0.010416405275464058, -0.054926589131355286, -0.03249798342585564, 0.04893992841243744, -0.015720875933766365, 0.07831413298845291, -0.005176108330488205, 0.00782096292823553, -0.03990672528743744, 0.018585912883281708, -0.08985055983066559, 0.05242076888680458, -0.027829503640532494, 0.017306935042142868, 0.06656305491924286, 0.07952636480331421, -0.009306504391133785, 0.03296753019094467, -0.015274676494300365, 0.026079846546053886, 0.005467736162245274, 0.059681084007024765, -0.03418343514204025, 0.039943285286426544, -0.012698303908109665, -0.022891005501151085, -0.01943446882069111, 0.01449105329811573, 0.12447339296340942, 0.06257843226194382, 0.03065483085811138, -0.04595579206943512, -0.04471288621425629, 0.03366989642381668, -0.030154678970575333, 0.03208230808377266, 0.01700814627110958, -0.046006713062524796, -0.04924551025032997, -0.05234471336007118, -0.08568763732910156, 0.0017577954567968845, -0.0068394639529287815, -0.029112093150615692, 0.02245991863310337, -0.08352072536945343, 0.014234049245715141, -0.028010396286845207, -0.03764086216688156, 0.01646449789404869, 0.01686255633831024, -0.0766395851969719, 0.025803184136748314, -0.0021711571607738733, 0.01703624054789543, -0.049732767045497894, 0.07936989516019821, 0.05080723389983177, 0.008721531368792057, -0.006265605799853802, 0.08420322835445404, 0.05135960504412651, 0.00982241053134203, 0.03324560075998306, 0.04697948694229126, -0.03681251034140587, 0.03511840105056763, 0.022207388654351234, -0.06451383233070374, -0.04111123085021973, 0.014979596249759197, 0.0046347822062671185, 0.00790494680404663, -0.00016079677152447402, -0.028651494532823563, -0.03261829540133476, 0.031458742916584015, 0.16511572897434235, 0.032242413610219955, 0.0014703795313835144, 0.028718257322907448, -0.022704042494297028, -0.07769696414470673, 0.11476779729127884, 0.02448299713432789, -0.005892639048397541, 0.05267931520938873, 0.026906654238700867, -0.022009488195180893, 0.09115947037935257, -0.022114165127277374, -0.05307680740952492, -0.0625312402844429, 0.010049890726804733, -0.12057272344827652, 0.028104986995458603, 0.01693521998822689, -0.04295188561081886, -0.021136056631803513, -0.046985380351543427, 0.1306561529636383, -0.10579290241003036, -0.05896161124110222, -0.01463269628584385, -0.07587354630231857, -0.05207503214478493, -0.05101488158106804, 0.0014606057666242123, -0.07202045619487762, 0.011597431264817715, 0.010960289277136326, 0.03003276139497757, 0.01579848863184452, -0.074371337890625, -0.0750252902507782, 0.017158443108201027, -0.05277537554502487, -0.005315817426890135, 0.05266635864973068, 0.061568520963191986, -0.00068822962930426, -0.013319611549377441, 0.06945741921663284, -0.016744794324040413, -0.04515456035733223, -0.008159387856721878, -0.007038641255348921, 0.012275452725589275, -0.0008098912658169866, 0.00116880820132792, -0.0029422841034829617, -0.029148640111088753, -0.04089468717575073, -0.01420533936470747, -0.05520617589354515, -0.05921144038438797, 0.032635174691677094, -0.006940116174519062, 0.02770988829433918, -0.0178088191896677, 0.05070982873439789, -0.05821043998003006, 0.004229806363582611, 0.03159402683377266, -0.11772274971008301, -0.0689413771033287, 0.05871029198169708, -0.021042298525571823, 0.02343166247010231, 0.021561091765761375, -0.032217953354120255, 0.030831800773739815, -0.07877014577388763, -0.002198953414335847, -0.061592210084199905, 0.014869707636535168, -0.06599847227334976, -0.01191904116421938, -0.03952336311340332, 0.0018823550781235099, 0.09317192435264587, 0.0030591378454118967, 0.03132535144686699, -0.07884359359741211, 0.023257751017808914, -0.002536594169214368, -0.005392136517912149, 0.03357166424393654, -0.03757745027542114, -0.07996410131454468, 0.019411087036132812, -0.057648200541734695, 0.040421273559331894, 0.012569346465170383, -0.04863951727747917, 0.005374439526349306, 0.03487391397356987, 0.04684407263994217, 0.022122178226709366, -0.1092715859413147, 0.04402810335159302, -0.05782833695411682, -0.009522611275315285, -0.04454169049859047, -0.027156755328178406, 0.013329592533409595, 0.035669948905706406, -0.13057181239128113, -0.03755698725581169, -0.05947871506214142, 0.037543121725320816, 0.002119034295901656, 0.04116369038820267, 0.04270406439900398, -0.0675094947218895, -0.044643424451351166, 0.05589200183749199, 0.04406682029366493, -0.0047566271387040615, -0.031116560101509094, -0.006898445542901754, -0.0517699308693409, -0.02455388382077217, -0.031196191906929016, -0.005185829009860754, 0.010464038699865341, -0.01452663540840149, -0.01555166020989418, 0.011078203096985817, 0.062112875282764435, 0.03619028255343437, 0.0666596069931984, 0.007240845821797848, 0.027677567675709724, 0.02081138640642166, -0.01329506654292345, -0.004140674136579037, -0.0005749373231083155, -0.020821785554289818, -0.022568274289369583, -0.0041806804947555065, 0.00885358452796936, -0.022473158314824104, -0.08033661544322968, 0.05383818596601486, 0.02687196061015129, 0.03499680757522583, 0.007065241690725088, -0.0018271515145897865, -0.03727307170629501, -0.02712705358862877, -0.03687158599495888, -0.06312444806098938, -0.008734160102903843, 0.0431305430829525, 0.0032232925295829773, -0.030008994042873383, 0.013476397842168808, -0.010370546020567417, -0.0451500229537487, -0.03486892953515053, 0.04487926885485649, -0.04095084220170975, 0.02347266674041748, -0.06356675922870636, -0.03063649870455265, 0.027024131268262863, -0.009532223455607891, -0.00022762887238059193, -0.017243409529328346, -0.043827418237924576, 0.04563695192337036, -0.004451259970664978, 0.033932723104953766, 0.028889194130897522, -0.05667426064610481, -0.026312774047255516, -0.08341369032859802, 0.040962837636470795, -0.0012945090420544147, 0.011674167588353157, -0.0499957799911499, -0.06849513202905655, 0.04680236056447029, -0.05160609260201454, -0.03870625048875809, -0.058240920305252075, 0.03883696347475052, 0.018960144370794296, 0.0022005836945027113, -0.07213672250509262, -0.017580246552824974, -0.07481909543275833, -0.0003911374951712787, 0.03417101129889488, -0.09186305105686188, -0.03270570933818817, -0.002591814612969756, 0.04720255360007286, -0.028244607150554657, -0.04235609620809555, -0.09387487918138504, 0.01434781588613987, 0.0011838338105008006, 0.04710739850997925, 0.050530657172203064, 0.0703325942158699, 0.018773622810840607, -0.054620809853076935, -0.028600871562957764, 0.049835991114377975, -0.07574816793203354, 0.014777344651520252, 0.022083226591348648, 0.0043936725705862045, -0.029100485146045685, 0.011892693117260933, 0.019160835072398186, 0.07698307186365128, -0.08393409103155136, -0.0042628091759979725, -0.046243514865636826, -0.06589319556951523, -0.005460298620164394, 0.012242663651704788, 0.07343756407499313, 0.06063590571284294, 0.04400894418358803, 0.060694657266139984, 0.030882246792316437, -0.017422400414943695, -0.030941102653741837, 0.034693893045186996, 0.07528485357761383, 0.027843842282891273, -0.049072787165641785, -0.0326995812356472, 0.0056327818892896175, -0.01108314748853445, 0.005582836456596851, -0.005552608985453844, -0.033225372433662415, 0.01981380023062229, 0.020412076264619827, 0.06931251287460327, 0.039507072418928146, 0.013283120468258858, -0.07929553091526031, -0.044863950461149216, -0.019439734518527985, -0.05370766669511795, 0.08172348886728287, -0.05346947908401489, -0.058387238532304764, -0.0006258689099922776, 0.06063717603683472, -0.048933111131191254, -0.007042631506919861, 0.050109535455703735, 0.024457266554236412, 0.03984415903687477, -0.02468833513557911, 0.011687199585139751, 0.08996589481830597, -0.02377271093428135]', 'uploads/faces/user_11_angle3_20260729_163742_791065.jpg');
INSERT INTO `face_embeddings` (`id`, `employee_id`, `user_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(14, NULL, 11, '', '', '2026-07-29 11:07:44', '[-0.006666550878435373, 0.02612513117492199, -0.047824133187532425, 0.02605636790394783, -0.07545378804206848, -0.059251800179481506, -0.049714330583810806, 0.03851546719670296, 0.00022761482978239655, -0.009883674792945385, -0.02439791150391102, -0.0032186766620725393, 0.004776802379637957, -0.02521607279777527, -0.05211496353149414, -0.09320273995399475, -0.04668291285634041, 0.0003256624040659517, 0.06592582166194916, 0.006320937070995569, 0.015153405256569386, -0.0065085068345069885, -0.034329887479543686, -0.03058588318526745, 0.03704829141497612, -0.06548938900232315, 0.08163096010684967, 0.0006532950792461634, -0.017820831388235092, -0.09426962584257126, 0.0014405067777261138, 0.02117694541811943, -0.00887097418308258, 0.014291885308921337, 0.003960268571972847, 0.0016573553439229727, -0.029203593730926514, -0.06995231658220291, -0.007921619340777397, 0.05468358099460602, -0.046965304762125015, -0.017348578199744225, 0.009339462034404278, 0.032351598143577576, -0.0032311687245965004, -0.002969099674373865, 0.017934029921889305, 0.05522214621305466, 0.048035867512226105, 0.06360908597707748, 0.04111144691705704, -0.031880948692560196, 0.010381166823208332, -0.023769941180944443, 0.032708585262298584, -0.017212437465786934, 0.06792232394218445, -0.004011936951428652, 0.0028907854575663805, -0.042541082948446274, 0.018210291862487793, -0.060369279235601425, -0.06015726178884506, -0.03795577958226204, 0.09639700502157211, -0.062381990253925323, -0.010524028912186623, 0.01876392401754856, -0.04717903956770897, -0.006648814771324396, 0.020547157153487206, 0.013199527747929096, 0.038908474147319794, -0.01574178971350193, -0.012871535494923592, -0.013628502376377583, 0.01765754260122776, -0.04552089050412178, 0.03536064177751541, 0.07579365372657776, 0.09212186932563782, -0.04546906054019928, 0.021387267857789993, -0.05321750417351723, 0.05000291019678116, -0.007762771099805832, 0.02663557045161724, 0.07549087703227997, -0.044606681913137436, -0.009495181031525135, 0.04319294914603233, 0.07460646331310272, -0.04989511892199516, -0.008462871424853802, -0.05944138765335083, -0.034399453550577164, -0.044226303696632385, 0.03630619868636131, 0.00236091879196465, 0.007542524486780167, 0.002486670855432749, -0.09741367399692535, 0.019534572958946228, 0.02669052593410015, 0.0026610565837472677, -0.08015181124210358, 0.03263721242547035, -0.01446461770683527, 0.08177681267261505, 0.062369659543037415, -0.0065970574505627155, 0.005410120822489262, 0.03571344539523125, 0.03346848860383034, 0.017742926254868507, -0.014866482466459274, -0.001938928384333849, -0.03054894134402275, -0.061819855123758316, -0.044263582676649094, -0.1410093754529953, -0.002933406038209796, -0.018314331769943237, -0.056733932346105576, 0.01030962448567152, 0.03402560576796532, 0.07916183769702911, -0.09050388634204865, -0.001266650971956551, 0.026025235652923584, -0.07018307596445084, 0.03905453532934189, 0.028268728405237198, 0.02726052887737751, -0.04567715525627136, -0.025339048355817795, -0.047600891441106796, 0.061057135462760925, 0.028066294267773628, -0.0667240247130394, -0.004440330434590578, -0.05606786906719208, -0.05379875749349594, -0.025868890807032585, 0.0038867073599249125, 0.02321292832493782, 0.0015352582558989525, -0.03092328831553459, 0.020918842405080795, -0.0040436722338199615, -0.06008534133434296, -0.03543249890208244, -0.028980985283851624, 0.023101504892110825, -0.012946772389113903, 0.0073752738535404205, -0.004679146688431501, -0.06753817200660706, 0.05555364489555359, 0.04671737551689148, 0.0015620972262695432, 0.004404349718242884, 0.08562692254781723, 0.02229788713157177, 0.0438348688185215, 0.003662179922685027, -0.04356775060296059, -0.05045338347554207, -0.037188705056905746, -0.0330025851726532, 0.045385271310806274, 0.012991297990083694, 0.061389561742544174, 0.0023855429608374834, -0.01985270157456398, 0.006939969025552273, 0.051230691373348236, 0.05950574576854706, 0.0627676248550415, -0.04552648589015007, -0.08585973083972931, -0.01429616566747427, -0.021860823035240173, -0.017660589888691902, -0.022808993235230446, 0.0008454452618025243, 0.0038286636117845774, 0.014921369962394238, -0.006550144869834185, -0.021057572215795517, 0.038810256868600845, 0.0034495354630053043, -0.0018868519691750407, -0.027546368539333344, -0.031225034967064857, 0.041292522102594376, 0.07831865549087524, 0.010165916755795479, -0.0175949614495039, -0.06249909847974777, -0.034681111574172974, -0.03716132044792175, -0.059281766414642334, 0.018417101353406906, -0.052056945860385895, -0.05551161617040634, 0.010620799846947193, -0.06178611144423485, 0.008237161673605442, -0.027304423972964287, -0.03134722262620926, -0.0059648421593010426, 0.01735810749232769, 0.014210199937224388, -0.049492914229631424, -0.022989997640252113, -0.005986922420561314, 0.018057722598314285, -0.08246055245399475, -0.010662797838449478, 0.01623963937163353, 0.023078247904777527, -0.018586859107017517, -0.03152602165937424, 0.06380613148212433, 0.021320773288607597, 0.014962060377001762, 0.06307648122310638, -0.006119748577475548, -0.010042581707239151, -0.019652875140309334, -0.05316915735602379, -0.03282848745584488, 0.04048043116927147, 0.08522066473960876, -0.03613189235329628, -0.03886488452553749, 0.04739784076809883, -0.07647152245044708, 0.00491800531744957, 0.00836080964654684, -0.09615868330001831, -0.040186963975429535, 0.04253807291388512, 0.09372125566005707, 0.04995858296751976, 0.02160244807600975, 0.014557045884430408, 0.014245523139834404, -0.07454448938369751, 0.05071750283241272, 0.04289562255144119, -0.03338325396180153, 0.03378334268927574, 0.008530978113412857, -0.004577529616653919, 0.04440775141119957, -0.04700864106416702, -0.03879130259156227, -0.05962132662534714, 0.01986660622060299, -0.08301976323127747, 0.048229992389678955, 0.030807022005319595, 0.019150033593177795, 0.08364086598157883, -0.048591021448373795, 0.08259008079767227, -0.04060189798474312, -0.05415363982319832, 0.00816497765481472, -0.048511166125535965, -0.04052196070551872, -0.02668951265513897, 0.017931265756487846, -0.021907374262809753, -0.006011159624904394, -0.09533986449241638, 0.0035195087548345327, 0.007229643873870373, -0.06856740266084671, -0.06741248071193695, -0.02010495960712433, -0.0732966735959053, -0.09082067757844925, 0.0036664328072220087, 0.08075413107872009, 0.021303175017237663, 0.019847333431243896, 0.013603429310023785, 0.011873436160385609, -0.013678744435310364, -0.010682466439902782, 0.016646942123770714, 0.06692299991846085, -0.03915856406092644, -0.007898994721472263, 0.033386290073394775, 0.03710439056158066, -0.016628455370664597, -0.048885930329561234, -0.06537050008773804, -0.026206977665424347, 0.06545809656381607, 0.02888490818440914, 0.046669188886880875, -0.05411989986896515, 0.019076509401202202, 0.003896984737366438, 0.03187495097517967, 0.01970517262816429, -0.033577997237443924, -0.039025526493787766, 0.05821845680475235, -0.044564589858055115, -0.006352165248245001, 0.014850395731627941, -0.018378417938947678, 0.03432535380125046, -0.06091083958745003, 0.0009359130053780973, -0.11261426657438278, 0.016064463183283806, -0.06719133257865906, -0.06991587579250336, -0.0007075914763845503, -0.031233180314302444, 0.03405015170574188, -0.04106898233294487, 0.033690258860588074, -0.0008275302243418992, 0.04884902015328407, -0.018072886392474174, -0.0008236482390202582, 0.0037126399111002684, -0.04381338506937027, -0.014479291625320911, -0.02745679020881653, -0.013204156421124935, 0.02650238201022148, 0.018067741766572, 0.011181943118572235, -0.035901352763175964, 0.012844359502196312, -0.0017008602153509855, 0.02109185792505741, -0.16187846660614014, 0.0059559824876487255, -0.0013040322810411453, 0.012048304080963135, -0.04773988574743271, 0.03699725121259689, -0.012843860313296318, -0.03702767565846443, -0.08240531384944916, -0.002810755046084523, 0.05630139634013176, 0.013923431746661663, 0.052839308977127075, 0.07278215885162354, 0.05374632775783539, -0.04754027724266052, 0.03116450645029545, 0.03940621763467789, 0.07463592290878296, 0.010754735209047794, -0.028090359643101692, 0.05007913336157799, 0.018490644171833992, -0.00692872516810894, -0.0184771791100502, -0.025692682713270187, -0.04582170397043228, -0.021337497979402542, -0.0634922906756401, 0.014981045387685299, 0.06254590302705765, 0.024865230545401573, -0.00725875049829483, -0.06633242964744568, -0.0003443573077674955, -0.01033033151179552, 0.04375794529914856, 0.011112882755696774, 0.007913180626928806, -0.038581475615501404, 0.02841096930205822, 0.04029475525021553, -0.03390340879559517, 0.027697023004293442, -0.03428059443831444, 0.026990413665771484, -0.0380336195230484, 0.03494463115930557, -0.0222768671810627, 0.050769124180078506, -0.10494986921548843, -0.11209456622600555, -0.01764160394668579, -0.08218830078840256, 0.012191574089229107, 0.014571514911949635, -0.040399037301540375, 0.016248265281319618, 0.06816880404949188, -0.028276367112994194, -0.04339504987001419, -0.05884562432765961, 0.049625005573034286, -0.02329936996102333, -0.003390314057469368, -0.008636721409857273, -0.014493994414806366, 0.057199493050575256, -0.010335155762732029, -0.06402991712093353, 0.05033942684531212, 0.006908151786774397, 0.06536664813756943, -0.02171142026782036, -0.02067890204489231, -0.0196522269397974, -0.03635551407933235, -0.06531915068626404, -0.042466990649700165, 0.03645136207342148, -0.0031409410294145346, 0.00719434255734086, -0.07873331755399704, -0.014934754930436611, 0.047299470752477646, 0.09413586556911469, -0.03551201894879341, -0.03210679441690445, -0.011039640754461288, -0.004970263689756393, 0.02673523500561714, 0.03110666386783123, -0.04641406983137131, -0.016389653086662292, -0.025427479296922684, 0.00470774807035923, -0.09779053181409836, -0.07327637076377869, -0.015081968158483505, -0.0009386423625983298, 0.02243717573583126, 0.012115232646465302, 0.00795403029769659, 0.015004105865955353, 0.0263240747153759, -0.007854081690311432, 0.004198318347334862, 0.0707026869058609, -0.004336369689553976, 0.06157967448234558, -0.020542392507195473, 0.03965650871396065, -0.0027987174689769745, -0.0002325961977476254, 0.06284749507904053, -0.05297763645648956, -0.07982605695724487, -0.014535720460116863, 0.004738925024867058, 0.10270029306411743, -0.0756906047463417, 0.052700914442539215, -0.1413293331861496, -0.0553940050303936, 0.024929359555244446, -0.03893052041530609, 0.09249865263700485, 0.02480914629995823, 0.05168065056204796, 0.09062323719263077, 0.02548052743077278, -0.011366593651473522, -0.029586149379611015, -0.01962229609489441, 0.050961364060640335, 0.04775632545351982, -0.0827658399939537, -0.03496292978525162, 0.0013223403366282582, 0.03644879534840584, -0.06402422487735748, 0.014898198656737804, -0.0521031990647316, 0.06377818435430527, 0.020428471267223358, 0.010206175036728382, 0.0482950434088707, 0.06886669248342514, -0.043685052543878555, -0.014535539783537388, 0.057944875210523605, -0.08111916482448578, 0.07917909324169159, -0.03750501945614815, 0.03410990536212921, -0.006492977496236563, 0.03305427357554436, -0.02227943390607834, 0.04085616394877434, 0.01245371624827385, 0.026390882208943367, 0.05613599717617035, -0.06408613920211792, 0.07473567873239517, 0.008562703616917133, -0.005590781103819609]', 'uploads/faces/user_11_angle4_20260729_163744_391930.jpg'),
(15, NULL, 12, '', '', '2026-07-29 11:27:41', '[0.040010999888181686, -0.016178758814930916, 0.0043741934932768345, -0.01569591835141182, -0.05704234167933464, -0.0038078802172094584, 0.026599494740366936, 0.0488867461681366, -0.05594104900956154, 0.007410933263599873, 0.009814685210585594, 0.03220028430223465, 0.04690364748239517, 0.026247072964906693, -0.045944374054670334, -0.014031289145350456, 0.002035875106230378, -0.03325232118368149, 0.05278414860367775, 0.0013833586126565933, 0.02474815398454666, -0.012231271713972092, 0.004632764961570501, 0.031055232509970665, -0.044651973992586136, -0.005036504473537207, 0.014267788268625736, -0.0033775405026972294, -0.015905696898698807, 0.005502173211425543, -0.05557001382112503, -0.10499583929777145, -0.027152307331562042, 0.0033043925650417805, -0.010586021468043327, 0.004188690800219774, -0.05645567551255226, 0.026222387328743935, -0.08507933467626572, -0.009077554568648338, -0.035645078867673874, -0.05748303234577179, 0.029482856392860413, -0.020016243681311607, -0.035010889172554016, -0.00734142679721117, -0.056186493486166, 0.03197378292679787, -0.005504996981471777, 0.036503925919532776, 0.07268152385950089, 0.00932180043309927, -0.04464445635676384, 0.06046527251601219, -0.002434113062918186, 0.0793619453907013, 0.037523530423641205, 0.029775438830256462, 0.05227721855044365, -0.018132653087377548, 0.030228164047002792, 0.008162232115864754, -0.03670015558600426, -0.04048440605401993, 0.09726069122552872, -0.01794619858264923, 0.016436027362942696, -0.03290868178009987, 0.009487858042120934, -0.053993042558431625, 0.061403170228004456, 0.010971717536449432, 0.047461722046136856, -0.016173874959349632, 0.04689404368400574, -0.05889216065406799, 0.03425141051411629, 0.04808269441127777, 0.07529734820127487, 0.05405327305197716, 0.0047479961067438126, -0.06219862774014473, -0.07393406331539154, 0.0698077380657196, -0.0029307822696864605, -0.027633709833025932, -0.0027308764401823282, 0.07731052488088608, -0.028238631784915924, -0.08367661386728287, 0.12498017400503159, -0.0021451290231198072, -0.050743065774440765, 0.013253947719931602, 0.013883134350180626, 0.006075410172343254, 0.009733201004564762, 0.009999592788517475, -0.07602906227111816, 0.016860978677868843, -0.04137242212891579, 0.0030192926060408354, 0.0837491974234581, 0.05684110149741173, 0.017083214595913887, -0.040452029556035995, 0.02477625198662281, -0.03699720650911331, 0.07161616533994675, 0.03565111383795738, -0.009776215068995953, -0.05070295184850693, 0.015421674586832523, 0.012250134721398354, 0.011165299452841282, -0.047498222440481186, -0.016100820153951645, 0.045792125165462494, 0.007426286116242409, 0.03819708526134491, -0.08962682634592056, 0.018046673387289047, -0.011096080765128136, -0.11048085242509842, -0.014689537696540356, -0.026059679687023163, 0.024090666323900223, -0.04623178392648697, -0.028958706185221672, 0.005921466276049614, -0.0025294460356235504, 0.10417567938566208, -0.005371687468141317, -0.004148859065026045, -0.017668647691607475, -0.07300633192062378, 0.0008626561611890793, 0.02178359031677246, -0.08443795889616013, 0.03214142844080925, 0.00405398802831769, 0.02913074754178524, -0.020658276975154877, -0.03891381993889809, 0.005649616941809654, -0.012257648631930351, 0.04354330152273178, 0.029248951002955437, 0.02287398651242256, -0.13916903734207153, 0.007285002153366804, -0.058842066675424576, -0.03143267333507538, 0.031447768211364746, -0.020839042961597443, 0.0023438367061316967, 0.04486729949712753, -0.05919473245739937, 0.07665611058473587, -0.10713960230350494, 0.08942742645740509, -0.04256083071231842, 0.02648717723786831, -0.06727304309606552, 0.011765249073505402, -0.008458690717816353, 0.0636395812034607, -0.01829044334590435, 0.018059207126498222, -0.040698882192373276, 0.03790949285030365, 0.051247455179691315, -0.023210398852825165, 0.08989804238080978, 0.034106310456991196, -0.04269614815711975, 0.02548057958483696, 0.02529013529419899, 0.01572369411587715, 0.0621752105653286, -0.0041311983950436115, 0.018499573692679405, 0.05229274183511734, 0.0499379001557827, -0.06257345527410507, -0.06313206255435944, -0.02471967600286007, -0.0039176964201033115, -0.008635447360575199, 0.02903461642563343, 0.0391051284968853, 0.03982917591929436, -0.01584738865494728, -0.04400230944156647, -0.020810268819332123, -0.07905012369155884, 0.05403372272849083, -0.005133115220814943, -0.02717977948486805, -0.0680096372961998, 0.07014480233192444, -0.008129692636430264, -0.06344960629940033, -0.01786588504910469, 0.038287028670310974, 0.024469438940286636, -0.05367852747440338, -0.030098823830485344, -0.0033340819645673037, -0.04101654887199402, -0.07832421362400055, -0.0004625047149602324, -0.013874752447009087, -0.04015683755278587, -0.040286414325237274, 0.03686099871993065, -0.04652149975299835, -0.004940764047205448, -0.008032485842704773, 0.03140369802713394, 0.0053409128449857235, 0.01902148686349392, -0.04939665272831917, -0.05978556349873543, 0.022928617894649506, -0.02390650287270546, -0.015776026993989944, 0.10677485167980194, 0.02587973326444626, 0.023528562858700752, 0.0035910215228796005, -0.024107854813337326, -0.05540553852915764, 0.044926565140485764, 0.051294323056936264, -0.0009116855799220502, -0.04408608376979828, 0.04418157786130905, -0.07230643182992935, 0.045613400638103485, -0.020272357389330864, -0.055177196860313416, -0.009178653359413147, -0.0342756025493145, 0.08594857156276703, 0.05744636058807373, 0.035111747682094574, -0.06248844414949417, 0.031271032989025116, -0.0003422905574552715, 0.03036346472799778, -0.018801983445882797, 0.029544008895754814, 0.08116587996482849, 0.03186691552400589, 0.008212880231440067, 0.03371495380997658, 0.013885325752198696, 0.03493206202983856, 0.030806170776486397, 0.11677907407283783, -0.05065896734595299, 0.039761487394571304, -0.015238441526889801, -0.009996302425861359, 0.07142319530248642, -0.00850021094083786, 0.029018813744187355, -0.1016232967376709, -0.03498619422316551, 0.041045188903808594, 0.007252017501741648, -0.0744844526052475, -0.03536103665828705, -0.014531266875565052, 0.0703352615237236, -0.09442286193370819, 0.017807867377996445, -0.00828083511441946, -0.07710450142621994, -0.0638476312160492, -0.048818256705999374, 0.07554320245981216, 0.07733877748250961, -0.048131246119737625, 0.018321866169571877, -0.02333657629787922, -0.010585477575659752, 0.005035849753767252, -0.020151227712631226, -0.019632801413536072, 0.019681155681610107, -0.006648175418376923, -0.07761336117982864, -0.008311664685606956, 0.04030227288603783, -0.02865012362599373, 0.033088743686676025, 0.05696261301636696, -0.06505570560693741, 0.009026672691106796, -0.015399224124848843, 0.025061899796128273, 0.08287874609231949, 0.032502010464668274, 0.07270829379558563, 0.04311371222138405, -0.0641685351729393, -0.09875550121068954, 0.038728438317775726, -0.0075491503812372684, 0.05492386966943741, 0.014030366204679012, -0.047097135335206985, 0.06555400043725967, 0.03777798265218735, 0.08951610326766968, 0.009489825926721096, 0.05713362246751785, -0.05270858854055405, -0.011485470458865166, -0.023501142859458923, 0.015820981934666634, -0.08809804171323776, 0.03528280183672905, -0.0440031997859478, -0.029014872387051582, -0.02311241254210472, -0.023415807634592056, -0.0007444943184964359, 0.009616903029382229, -0.026126068085432053, 0.026846276596188545, -0.03388991951942444, 0.01921374723315239, -0.004535546060651541, 0.015941260382533073, -0.020890215411782265, 0.03166843578219414, 0.016088688746094704, 0.046597275882959366, -0.015347398817539215, 0.04966897889971733, -0.003274671034887433, -0.03389456123113632, 0.019383741542696953, -0.0361219123005867, -0.09318675100803375, -0.12969663739204407, -0.004985078703612089, 0.01593649946153164, 0.010813056491315365, -0.0793730691075325, 0.03634309023618698, -0.13842004537582397, 0.025458985939621925, 0.0407089963555336, -0.052055247128009796, 0.034877654165029526, 0.06309057772159576, 0.0006026345072314143, 0.015673816204071045, -0.031095733866095543, 0.05226956680417061, 0.05282880365848541, -0.02863267809152603, -0.09128444641828537, -0.033755335956811905, -0.037628863006830215, -0.0340304858982563, 0.03719627857208252, 0.03889117389917374, -0.0400647334754467, 0.050418514758348465, 0.09924989193677902, 0.05854618176817894, 0.01030512060970068, 0.057921141386032104, -0.031003562733530998, 0.039167389273643494, -0.05649500712752342, -0.03090885654091835, 0.003111983649432659, -0.022586623206734657, -0.026349831372499466, 0.008746101520955563, -0.01305975578725338, -0.020401788875460625, 0.019373951479792595, 0.04597974941134453, -0.05348982661962509, 0.032532017678022385, 0.0467163547873497, 0.03725975751876831, -0.006942206062376499, -0.004157082177698612, -0.056787487119436264, -0.055219560861587524, -0.016724539920687675, 0.07541441917419434, -0.06851331889629364, -0.022937465459108353, 0.017266876995563507, 0.004982590209692717, 0.028783222660422325, -0.04864150658249855, -0.08877138048410416, -0.03924630209803581, -0.009223147295415401, -0.03055705688893795, 0.128349170088768, 0.042809244245290756, 0.0003776881785597652, 0.03510936349630356, 0.025291135534644127, -0.00047818655730225146, 0.04401262104511261, 0.022057868540287018, 0.019528981298208237, 0.05338406190276146, -0.018723033368587494, 0.010205194354057312, -0.060898616909980774, -0.05955837666988373, 0.007084956392645836, -0.12023957073688507, -0.019793087616562843, 0.014193319715559483, -0.054911814630031586, 0.026035532355308533, 0.008781769312918186, -0.012056373991072178, -0.02587057277560234, -0.022929353639483452, 0.0035881735384464264, 0.02384933829307556, 0.013843031600117683, -0.04946112632751465, 0.046400777995586395, -0.013082127086818218, -0.03635379672050476, -0.04484358802437782, -0.0009351355256512761, -0.03646574169397354, 0.013713284395635128, 0.012692606076598167, 0.011277609504759312, 0.04603278636932373, -0.0013098829658702016, -0.061097607016563416, -0.008685336448252201, -0.018838951364159584, -0.07936786860227585, 0.06502629816532135, 0.003408636199310422, 0.028363171964883804, 0.003085861448198557, 0.001923432108014822, 0.004350855480879545, 0.033806394785642624, 0.015338588505983353, -0.02895025536417961, 0.01916675455868244, -0.001535072224214673, -0.043054427951574326, 0.010899845510721207, 0.0016535543836653233, 0.021241964772343636, -0.06348428130149841, 0.012464754283428192, -0.015011025592684746, 0.013947299681603909, 0.05646410211920738, 0.06027458980679512, 0.06731249392032623, 0.015548311173915863, 0.01974656991660595, -0.03522006422281265, 0.037779852747917175, 0.004114145878702402, 0.008148296736180782, -0.01506009977310896, -0.0021668612025678158, -0.049278680235147476, 0.04862484708428383, -0.018402233719825745, 0.0002175833360524848, 0.04193485528230667, -0.0005283087375573814, -0.023332037031650543, -0.013743727467954159, -0.005103465635329485, 0.015335053205490112, 0.061252471059560776, -0.05618484318256378, -0.020893141627311707, -0.046172257512807846, -0.012271983548998833, 0.06347677856683731, 0.017563655972480774, 0.09802016615867615, 0.04360407218337059, 0.013900906778872013, 0.03401903808116913, -0.010105270892381668, -0.0394081175327301, 0.013042407110333443, -0.0005553895025514066, -0.07084932178258896, 0.0007776498678140342, -0.06052666902542114, 0.019598018378019333]', 'uploads/faces/user_12_angle0_20260729_165736_874314.jpg'),
(16, NULL, 12, '', '', '2026-07-29 11:27:41', '[0.03604087978601456, -0.019548559561371803, 0.008557606488466263, -0.014684603549540043, -0.05068659037351608, 0.003456434700638056, 0.0282436553388834, 0.02468130737543106, 0.003224180545657873, 0.026315541937947273, 0.06006564944982529, 0.006678746081888676, -0.03467351198196411, 0.02066367119550705, -0.01273983996361494, -0.04240668565034866, 0.01753118634223938, -0.0037408615462481976, -0.01993451826274395, -0.0445573627948761, 0.026135530322790146, 0.0736512839794159, 0.0454370379447937, 0.043853893876075745, -0.03806184232234955, -0.030875911936163902, 0.06748563051223755, -0.0007405541837215424, 0.07765137404203415, 0.01528517808765173, -0.04120533540844917, -0.07559873908758163, 0.0006029955693520606, -0.05408244580030441, 0.04059818759560585, 0.026607435196638107, -0.06361626088619232, 0.07134726643562317, -0.04721729829907417, -0.036875028163194656, -0.10335808247327805, 0.0208098366856575, 0.042954519391059875, -0.04458353668451309, 0.025364166125655174, -0.010337666608393192, -0.022717483341693878, -0.0010026476811617613, 0.01542202290147543, 0.055497728288173676, 0.012785108759999275, 0.010777682065963745, -0.03036046214401722, 0.08879970014095306, 0.005124565679579973, 0.04354386776685715, -0.014994222670793533, 0.009273209609091282, 0.0062628542073071, 0.0020660392474383116, 0.04129290580749512, -0.03883527219295502, -0.017016656696796417, -0.0027917763218283653, 0.026384003460407257, -0.05599437654018402, -0.002416035858914256, -0.05087024345993996, -0.0105747627094388, -0.014093953184783459, 0.051587510854005814, 0.0017510345205664635, 0.03502321615815163, -0.06368831545114517, 0.06814239919185638, 0.014041982591152191, 0.03941623121500015, -0.014038600958883762, 0.04672622308135033, 0.015258307568728924, 0.038909271359443665, -0.03378104045987129, -0.011209685355424881, 0.05201258510351181, 0.029123883694410324, 0.02899075113236904, 0.03412945941090584, 0.027869125828146935, -0.03683323785662651, -0.0676855817437172, 0.028940489515662193, 0.019105462357401848, -0.06077523157000542, -0.01783866249024868, 0.016982361674308777, 0.0735694020986557, 0.01897973194718361, 0.053867850452661514, -0.07809046655893326, -0.026996469125151634, -0.044624652713537216, 0.03144178166985512, 0.10206613689661026, 0.03095461241900921, 0.005462717264890671, 0.014186630956828594, -0.001950229168869555, -0.00392136350274086, 0.05313766747713089, 0.05320439487695694, -0.03262382373213768, 0.06776365637779236, -0.018947409465909004, -0.060123637318611145, 0.03644837439060211, -0.07857920229434967, -0.023696325719356537, -0.0346016101539135, -0.05324453487992287, -0.0016237817471846938, -0.007919220253825188, 0.03216582536697388, -0.025720102712512016, -0.09208951890468597, 0.0002698144526220858, -0.0357266403734684, 0.06478500366210938, -0.016405785456299782, -0.017257092520594597, -0.009227678179740906, -0.02764296717941761, 0.07399195432662964, -0.014283756725490093, -0.010424777865409851, -0.030479377135634422, -0.06916087120771408, -0.03677075356245041, -0.06469257175922394, -0.11601123958826065, 0.043413374572992325, -0.045624859631061554, 0.026670308783650398, -0.032761700451374054, -0.06196143478155136, 0.002485648961737752, -0.033487945795059204, 0.03483228012919426, 0.04584536701440811, 0.041734810918569565, -0.07399957627058029, -0.012203877791762352, -0.05311601236462593, -0.020519550889730453, 0.011707554571330547, 0.020365849137306213, 0.00038943352410569787, 0.039166904985904694, -0.01969313994050026, 0.06509383022785187, -0.09966633468866348, 0.14014816284179688, 0.03858508914709091, -0.0542321503162384, 0.022089704871177673, -0.016823124140501022, -0.013292894698679447, 0.022579621523618698, -0.01304679550230503, 0.0025425406638532877, -0.03853922709822655, -0.030316194519400597, 0.10943981260061264, -0.0025706454180181026, 0.07117418199777603, 0.021607276052236557, 0.02501741796731949, 0.05564426630735397, -0.03384288027882576, 0.09544812887907028, 0.055014822632074356, 0.00721672922372818, 0.011178822256624699, 0.02410922199487686, 0.06153801828622818, -0.053242042660713196, -0.02387370355427265, -0.021589111536741257, 0.020280785858631134, -0.00047853236901573837, 0.007442174945026636, 0.02764703333377838, 0.05866927653551102, -0.027006475254893303, 0.002207151148468256, -0.0006691429298371077, -0.06755880266427994, 0.05771186202764511, 0.012967194430530071, -0.02349502220749855, -0.0598968081176281, 0.04174968600273132, -0.0258162934333086, -0.06543605774641037, -0.015406536869704723, 0.11926904320716858, 0.06254443526268005, -0.028657780960202217, -0.05089931562542915, -0.021165749058127403, -0.06638965755701065, 0.009368234314024448, 0.007305229548364878, -0.06905996054410934, -0.0169368926435709, -0.013033580034971237, 0.03508738800883293, -0.013219918124377728, -0.060930028557777405, 0.0007860198966227472, 0.0523432195186615, 0.004909595008939505, 0.06502638757228851, -0.007465253118425608, -0.0063429418951272964, 0.0021019503474235535, -0.05394051596522331, -0.025647785514593124, -0.011671395041048527, 0.05407247319817543, -0.05755225569009781, 0.016348903998732567, -0.0759740099310875, 0.006525056436657906, 0.07715922594070435, 0.031958311796188354, -0.033256225287914276, 0.04420897737145424, 0.025103596970438957, -0.04026191309094429, 0.07032522559165955, -0.0302024707198143, -0.06757230311632156, -0.0049425819888710976, -0.00116091244854033, 0.01607660949230194, 0.030592219904065132, 0.07315462082624435, 0.08424310386180878, 0.03638898581266403, 0.023851748555898666, 0.010035070590674877, -0.03144731745123863, -0.026697883382439613, 0.01976294256746769, 0.01868950016796589, 0.0007519526989199221, 0.027096569538116455, 0.09116144478321075, 0.00122278172057122, 0.03362482041120529, 0.07059943675994873, -0.016725096851587296, 0.006819018628448248, -0.079371377825737, 0.0011861175298690796, 0.1443863809108734, -0.04577726498246193, 0.07686541229486465, -0.13648496568202972, -0.03149235248565674, 0.02120867744088173, -0.03385671600699425, -0.058522000908851624, -0.04199369251728058, -0.009939445182681084, 0.059843987226486206, -0.02349708043038845, -0.01112337876111269, 0.03622882813215256, -0.05384284630417824, -0.017582958564162254, -0.07209724932909012, 0.029085244983434677, 0.0723191350698471, -0.04433598741889, 0.0011275120778009295, 0.019928766414523125, -0.007115762215107679, 0.02655847556889057, 0.000957069976720959, -0.038232170045375824, 0.051125213503837585, -0.0657026469707489, -0.07842666655778885, -0.040948666632175446, -0.03958872705698013, -0.02669217623770237, 0.020862173289060593, 0.037085894495248795, -0.04619700461626053, -0.02504439651966095, -0.039866041392087936, 0.03935316950082779, 0.07425639033317566, 0.05726984143257141, 0.05887747183442116, 0.006242628209292889, -0.04182405397295952, -0.05869868025183678, 0.0309965331107378, 0.010383115150034428, 0.04308895394206047, 0.01940510794520378, -0.0036541614681482315, 0.033699825406074524, -0.024946825578808784, 0.06103174388408661, -0.013225536793470383, 0.043706897646188736, -0.051943715661764145, -0.0013819935265928507, -0.004249599762260914, -0.018770933151245117, -0.06438279151916504, 0.09486807137727737, 0.03812132775783539, -0.026212237775325775, -0.10091165453195572, -0.0376039557158947, -0.06833752244710922, 0.0035651023499667645, -0.027287712320685387, -0.018815165385603905, -0.011909708380699158, -0.011382948607206345, -0.041321761906147, -0.04737751558423042, -0.02250733971595764, 0.060176897794008255, -0.019807646051049232, 0.03428717330098152, -0.012869603931903839, 0.07871752232313156, 0.03039063885807991, -0.014389966614544392, -0.02272847853600979, 0.024727288633584976, -0.10796727985143661, -0.1544395238161087, -0.03458712622523308, 0.03406683728098869, 0.06275282800197601, -0.05781535431742668, 0.018580827862024307, -0.10640907287597656, -0.02341337502002716, -0.026068102568387985, 0.011914927512407303, 0.014083193615078926, 0.06467291712760925, 0.021999351680278778, 0.004648867063224316, -0.03508424758911133, -0.01351846568286419, 0.05381891131401062, -0.04908785969018936, -0.1101241260766983, -0.015545010566711426, -0.030980831012129784, -0.05001426115632057, 0.01784427836537361, 0.05199068784713745, -0.0959368571639061, -0.03434650972485542, 0.06439723074436188, 0.031890761107206345, 0.014189017936587334, 0.04639824852347374, -0.0376729890704155, -0.016104429960250854, -0.06904064863920212, -0.02745772898197174, -0.030606303364038467, -0.002423082711175084, -0.006402124185115099, 0.0067868526093661785, 0.002046949462965131, -0.0077779642306268215, 0.009517582133412361, 0.09312479943037033, -0.029720867052674294, 0.039666175842285156, 0.041190579533576965, -0.0292224008589983, -0.016553891822695732, -0.03425492346286774, -0.03793652355670929, -0.08105019479990005, 0.02522915042936802, -0.01110482681542635, -0.054027486592531204, -0.04006204754114151, -0.017927851527929306, 0.04496358335018158, 0.01612474024295807, -0.02187654748558998, -0.07815990597009659, -0.04394525662064552, -0.015018326230347157, 0.005874943919479847, 0.07422522455453873, -0.026101158931851387, 0.006455173250287771, 0.0498727522790432, 0.033234212547540665, -0.039834555238485336, 0.039862874895334244, 0.08466018736362457, -0.02458730712532997, -0.006876563653349876, -0.007725785486400127, -0.028135305270552635, -0.03736799210309982, 0.04196326434612274, 0.0010371801909059286, -0.049675896763801575, 0.024874955415725708, 0.0007167780422605574, -0.0462072379887104, 0.0271870456635952, -0.0031007956713438034, 0.006136639975011349, -0.011136575601994991, 0.016737360507249832, -0.004174471367150545, 0.05011436343193054, 0.011426846496760845, 0.01775398850440979, 0.039861977100372314, -0.027855010703206062, -0.024494923651218414, -0.08079110831022263, -0.030440369620919228, -0.0521884448826313, -0.0919751524925232, -0.0005581409786827862, -0.006498714908957481, 0.04743866249918938, 0.035447247326374054, -0.05515962839126587, 0.07650703936815262, -0.011318512260913849, -0.05051126331090927, 0.022038646042346954, -0.0352359339594841, 0.09778975695371628, 0.019918901845812798, 0.06999383866786957, -0.004943247884511948, 0.020876100286841393, 0.013444763608276844, 0.0438990592956543, -0.007248010020703077, 0.010322194546461105, -0.063737653195858, -0.007828529924154282, 0.03112517111003399, 0.0034340214915573597, -0.06912777572870255, 0.006089396309107542, 0.015636583790183067, -0.008714130148291588, 0.015407133847475052, 0.0868411585688591, 0.046805936843156815, -0.029333557933568954, 0.0413782112300396, -0.06876429170370102, 0.02447701059281826, 0.021590355783700943, 0.053292278200387955, -0.015386609360575676, 0.02039359323680401, -0.03212304785847664, -0.012015876360237598, -0.02608587220311165, -0.03635416924953461, 0.023823048919439316, 0.0124009158462286, 0.009106173180043697, -0.015294313430786133, -0.0020700448658317327, 0.038924068212509155, -0.015143088065087795, -0.008330139331519604, 0.010225169360637665, -0.06601312756538391, 0.008602269925177097, 0.03234833478927612, -0.02010297402739525, 0.028883635997772217, -0.005065577104687691, -0.0206685159355402, 0.008746805600821972, -0.021119270473718643, -0.047673121094703674, -0.07272349298000336, -0.03449370339512825, -0.047520652413368225, 0.01059390977025032, -0.044445134699344635, -0.005780581384897232]', 'uploads/faces/user_12_angle1_20260729_165738_611208.jpg'),
(17, NULL, 12, '', '', '2026-07-29 11:27:41', '[-0.0018007224425673485, -0.006358698476105928, 0.007863840088248253, -0.004992223344743252, -0.05527385324239731, 0.0311875082552433, 0.014351757243275642, 0.03406256064772606, -0.06385704129934311, 0.013447131030261517, 0.028713906183838844, 0.02151910588145256, 0.052691273391246796, 0.030003614723682404, -0.06334777921438217, -0.05001332238316536, -0.003553066635504365, -0.024571718648076057, 0.012839716859161854, -0.0006253142491914332, 0.041462257504463196, -0.021370569244027138, 0.0045579830184578896, 0.017317363992333412, -0.061084311455488205, -0.012041397392749786, 0.030908504500985146, 0.026543475687503815, 0.00992312841117382, -0.007055087015032768, -0.04511648416519165, -0.1174006462097168, -0.02560913935303688, 0.0030891927890479565, 0.00657220184803009, 0.011439872905611992, -0.015159363858401775, 0.033347614109516144, -0.077687107026577, -0.046431783586740494, -0.059751443564891815, -0.032185062766075134, 0.03333829715847969, -0.015430347062647343, -0.03618296980857849, -0.01667514070868492, -0.0713297501206398, 0.02633833698928356, -0.0165069792419672, 0.045957501977682114, 0.07545141875743866, 0.025572458282113075, -0.03638482093811035, 0.0811886265873909, -0.002987294690683484, 0.03883938491344452, 0.010779310949146748, 0.01718292012810707, 0.039487794041633606, -0.010557961650192738, 0.027480246499180794, -0.0001316004927502945, -0.046434156596660614, -0.015541807748377323, 0.08656329661607742, -0.02138373628258705, 0.00017889015725813806, -0.008170394226908684, 0.030385460704565048, -0.04725717380642891, 0.05345151573419571, -0.010446162894368172, 0.03809266537427902, 0.008483560755848885, 0.03778240829706192, -0.04413428530097008, 0.016952931880950928, 0.04490365833044052, 0.07050791382789612, 0.05665336549282074, -0.0010657552629709244, -0.06226283684372902, -0.07690415531396866, 0.0655089020729065, -0.0011680219322443008, -0.037761129438877106, -0.0380655899643898, 0.1035614013671875, -0.01783263124525547, -0.05749040096998215, 0.08494232594966888, 0.013181955553591251, -0.07685427367687225, 0.014109152369201183, 0.012112675234675407, 0.02882891520857811, 0.02525441162288189, -0.01705033890902996, -0.043770451098680496, 0.0056056370958685875, -0.018676351755857468, 0.02057844214141369, 0.07602814584970474, 0.049571190029382706, -0.004263908136636019, -0.0424768403172493, 0.00433852756395936, -0.02854984626173973, 0.07463975250720978, 0.022463547065854073, -0.025493251159787178, -0.019779331982135773, 0.007806246168911457, 0.029539400711655617, 0.033807653933763504, -0.047411445528268814, -0.025192655622959137, 0.051225800067186356, -0.008214049972593784, 0.052379053086042404, -0.07616527378559113, 0.00880200881510973, -0.02902495302259922, -0.09930373728275299, -0.026590397581458092, 0.017266884446144104, 0.03621586784720421, -0.03632209077477455, -0.032223671674728394, 0.02450982667505741, -0.01225111074745655, 0.08063904196023941, 0.01944819465279579, 0.01394595019519329, -0.03268929943442345, -0.06481785327196121, 0.002742231357842684, 0.00815887376666069, -0.08489349484443665, 0.007993555627763271, 0.017969844862818718, 0.036538343876600266, 0.0025174396578222513, -0.018576493486762047, -0.020497936755418777, -0.016288163140416145, 0.04914333298802376, 0.06111413985490799, 0.048942774534225464, -0.10336002707481384, -0.021344564855098724, -0.0548769012093544, -0.03980657830834389, 0.03484107926487923, 0.006810283754020929, -0.021914973855018616, 0.04670020192861557, -0.03431421145796776, 0.05234337970614433, -0.12303214520215988, 0.08037033677101135, -0.04400648921728134, 0.016992080956697464, -0.026519879698753357, 0.011008821427822113, 0.0057874517515301704, 0.03797207027673721, -0.0232781283557415, 0.00700083002448082, -0.048380590975284576, 0.02180180884897709, 0.03445170819759369, -0.016852963715791702, 0.11774196475744247, 0.048169560730457306, -0.032615963369607925, 0.02725985459983349, 0.029323913156986237, 0.04164934158325195, 0.08089397102594376, -0.016112787649035454, 0.016964016482234, 0.033651161938905716, 0.05265328660607338, -0.04979482293128967, -0.06168483570218086, -0.00248089712113142, 0.03080735169351101, -0.007976234890520573, 0.042362190783023834, 0.043189793825149536, 0.05376205965876579, 0.022624552249908447, -0.04530337452888489, -0.022826239466667175, -0.05290772765874863, 0.04143150523304939, -0.020032593980431557, -0.02208481729030609, -0.06151258945465088, 0.0718575268983841, -0.008223357610404491, -0.03138621896505356, 0.004969556350260973, 0.04632740095257759, 0.0346548855304718, -0.019373636692762375, -0.02589874528348446, -0.009439432062208652, -0.052489448338747025, -0.052063122391700745, 0.0024110691156238317, -0.03794588893651962, -0.009443233720958233, -0.033299945294857025, 0.036631710827350616, -0.03319087624549866, -0.026889147236943245, 0.014941800385713577, 0.03608972579240799, 0.028039876371622086, 0.04583662748336792, -0.055751364678144455, -0.05489181727170944, 0.033335234969854355, -0.02219918556511402, -0.07146985083818436, 0.060675811022520065, 0.03149459883570671, -0.0019514907617121935, -0.00796802993863821, -0.03872530162334442, -0.07281471788883209, 0.04641975462436676, 0.007149974349886179, -0.014900333248078823, -0.003769551170989871, 0.048090510070323944, -0.1003512591123581, 0.05243942514061928, -0.006312836892902851, -0.042446788400411606, -0.004997333977371454, -0.04851948097348213, 0.05933106318116188, 0.04181232303380966, 0.06434949487447739, -0.03019672818481922, 0.02792888693511486, 0.036779507994651794, 0.0499875433743, -0.005597899202257395, 0.03412684053182602, 0.0751100406050682, 0.028245670720934868, -0.007171934470534325, 0.05011722818017006, 0.021138405427336693, 0.021043259650468826, -0.005941718816757202, 0.10016020387411118, -0.03203071281313896, 0.04801693931221962, -0.0317496620118618, -0.026889612898230553, 0.08897270262241364, -0.017158711329102516, 0.08032582700252533, -0.1287168264389038, -0.02408842369914055, 0.03856531158089638, 0.0011683957418426871, -0.07573579251766205, -0.04043788090348244, -0.03225230053067207, 0.05721364915370941, -0.09671604633331299, 0.007061194162815809, -0.01916470192372799, -0.08557651937007904, -0.05643706023693085, -0.020028002560138702, 0.1147579774260521, 0.08099721372127533, -0.06537073850631714, 0.02495153434574604, -0.027211351320147514, -0.002314607845619321, -0.011564976535737514, 0.0073439269326627254, -0.05164816230535507, 0.05737319216132164, -0.009658608585596085, -0.09727142751216888, -0.02338339015841484, 0.05740457400679588, -0.017684808000922203, 0.028986500576138496, 0.04607895389199257, -0.05928929150104523, -0.014115899801254272, -0.0316750705242157, -0.00037119502667337656, 0.10097964853048325, 0.031698159873485565, 0.07382576912641525, -0.0048724883235991, -0.05890771746635437, -0.11848005652427673, 0.02654927223920822, -0.006597807165235281, 0.032498788088560104, 0.015581702813506126, -0.03983702138066292, 0.042903218418359756, 0.02480466105043888, 0.08129721879959106, 0.04126744344830513, 0.07737787812948227, -0.04759645089507103, 0.057723868638277054, -0.010378530248999596, -0.012971710413694382, -0.053370505571365356, 0.03867032378911972, -0.03423662483692169, -0.017598353326320648, -0.013962220400571823, -0.02835407666862011, 0.017964908853173256, 0.02697138674557209, -0.023785477504134178, 0.01142497081309557, 0.008589615114033222, 0.014801295474171638, -0.017936984077095985, -0.02727065049111843, -0.008996724151074886, 0.04017939046025276, -0.002461661584675312, 0.0682884007692337, -0.011402870528399944, 0.0615384615957737, 0.015883462503552437, -0.029580075293779373, 0.04803360626101494, -0.04118702560663223, -0.07738793641328812, -0.11986802518367767, -0.0037774136289954185, 0.020939193665981293, 0.018544061109423637, -0.10304728150367737, 0.020617462694644928, -0.12007106095552444, 0.036905303597450256, 0.05141394957900047, -0.04117435961961746, 0.04557013139128685, 0.07657487690448761, 0.0620228536427021, 0.029114969074726105, -0.017920885235071182, 0.049309782683849335, 0.05761278048157692, -0.012707827612757683, -0.12116343528032303, -0.010154897347092628, -0.04632951691746712, -0.046528566628694534, 0.06851471215486526, 0.03834370896220207, -0.062305524945259094, 0.03268280252814293, 0.08824039250612259, 0.041577987372875214, 0.0024512289091944695, 0.04476297274231911, -0.044351838529109955, 0.028484802693128586, -0.016836700960993767, -0.060869213193655014, 0.013784414157271385, -0.007852299138903618, -0.02331826649606228, -0.008614261634647846, 0.0021443150471895933, -0.01668373867869377, 0.021597441285848618, 0.056083083152770996, -0.04180845245718956, 0.051323480904102325, 0.02681625261902809, 0.006898872554302216, 0.014725305140018463, 0.04385470226407051, -0.054430730640888214, -0.02866809070110321, 0.02834026701748371, 0.04536958411335945, -0.07982729375362396, -0.025606105104088783, 0.00902701448649168, 0.03710969537496567, 0.026650551706552505, -0.056265123188495636, -0.0864415392279625, -0.016965430229902267, -0.006803142372518778, 0.012255623005330563, 0.1205458790063858, 0.020258255302906036, 0.014186604879796505, 0.042855195701122284, 0.03566752001643181, 0.04264786094427109, 0.03494460508227348, 0.05480372533202171, 0.006657998543232679, 0.060648586601018906, 0.033350467681884766, 0.018567895516753197, -0.06711351126432419, -0.037692777812480927, 0.0045876698568463326, -0.12070727348327637, -0.02487063594162464, 0.04197069630026817, -0.031416572630405426, 0.01844118908047676, -0.008847832679748535, -0.0017598561244085431, -0.0005588842323049903, 0.0003549845132511109, 0.019949553534388542, 0.051631756126880646, -0.008865817449986935, -0.04895350709557533, 0.005495576653629541, 0.004586775321513414, -0.01932687871158123, -0.09310061484575272, -0.041266411542892456, -0.05071597546339035, 0.0026603771839290857, 0.0009458520798943937, 0.009533599950373173, -0.00704172533005476, 0.013056211173534393, -0.042675964534282684, -0.027148880064487457, 0.013758361339569092, -0.06007462739944458, 0.05422282591462135, 0.02640855312347412, 0.028475744649767876, 0.015108008868992329, 0.03298214077949524, -0.0034971730783581734, 0.043390944600105286, 0.016221025958657265, -0.015342820435762405, 0.0182156041264534, -0.0005281841149553657, -0.048843175172805786, 0.0007451839628629386, -0.0026587790343910456, 0.04713365063071251, -0.055286046117544174, -0.025957584381103516, -0.040128711611032486, 0.016493123024702072, 0.06513246893882751, 0.05056048184633255, 0.06826268881559372, 0.009750730358064175, 0.006414619740098715, -0.021065035834908485, 0.038708172738552094, -0.015379887074232101, 0.025219958275556564, -0.015585285611450672, 0.03764734044671059, -0.049967654049396515, 0.025921260938048363, -0.021914830431342125, 0.016585327684879303, 0.04244082048535347, 0.00371760968118906, -0.025966979563236237, -0.027159901335835457, -0.020542597398161888, 0.01970474049448967, 0.0621030256152153, -0.0301397442817688, 0.015864139422774315, -0.06763926893472672, -0.013045857660472393, 0.06408274918794632, 0.039589978754520416, 0.0838211178779602, 0.0500517301261425, -0.029777614399790764, 0.014960112050175667, -0.013157770968973637, -0.03240124136209488, 0.006647725123912096, -0.03093414008617401, -0.0751269981265068, -0.0005781527725048363, -0.03017665073275566, 0.02228069119155407]', 'uploads/faces/user_12_angle2_20260729_165739_846242.jpg');
INSERT INTO `face_embeddings` (`id`, `employee_id`, `user_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(18, NULL, 12, '', '', '2026-07-29 11:27:41', '[0.004710105713456869, -0.03870771825313568, 0.021786851808428764, 0.00976881105452776, -0.06615007668733597, 0.01832008734345436, 0.027955735102295876, 0.011617452837526798, -0.07810113579034805, 0.017593637108802795, 0.0009457718697376549, -0.01179372426122427, 0.026367178186774254, 0.008996430784463882, -0.04138771444559097, -0.03884505107998848, 0.005363314412534237, -0.02539387159049511, 0.006493795663118362, 0.009782453998923302, 0.03871557489037514, 0.023355627432465553, 0.01759202778339386, 0.0014356550527736545, -0.08479349315166473, 0.0048874132335186005, 0.03259415552020073, 0.049929846078157425, 0.012652254663407803, -0.009095453657209873, -0.05921940505504608, -0.13014477491378784, -0.011344337835907936, -0.007483405992388725, 0.026750406250357628, 0.024889063090085983, -0.038656413555145264, 0.03781137987971306, -0.0848059207201004, -0.047140151262283325, -0.054433759301900864, -0.05526440590620041, 0.017223069444298744, 0.028805484995245934, -0.011987240985035896, -0.022212065756320953, -0.052449487149715424, 0.025665318593382835, -0.015161084942519665, 0.05462981015443802, 0.06837985664606094, 0.03300970047712326, -0.04925281181931496, 0.06215992942452431, 0.02289891056716442, 0.009651134721934795, -0.004564265254884958, 0.03994109109044075, -0.0011011618189513683, -0.017609678208827972, 0.04558675363659859, -0.015923915430903435, -0.06549367308616638, -0.026668552309274673, 0.10828980803489685, -0.013568731024861336, -0.0007837059674784541, -0.02471732348203659, -0.012044635601341724, -0.023894280195236206, 0.07828821241855621, -0.016643688082695007, 0.018410982564091682, 0.011725364252924919, 0.031065531075000763, -0.015161559917032719, 0.006308618001639843, 0.05140318721532822, 0.05916804075241089, 0.06351950764656067, -0.025636402890086174, -0.03122308850288391, -0.037141554057598114, 0.0608079619705677, 0.006681783124804497, -0.03491990640759468, 0.0038617411628365517, 0.07513748854398727, -0.015673764050006866, -0.06548554450273514, 0.0488538034260273, 0.013184084556996822, -0.033754803240299225, 0.014026624150574207, -0.005010546650737524, 0.03736822307109833, 0.02676122821867466, 0.016626223921775818, -0.057042501866817474, -0.0064084879122674465, -0.009760678745806217, 0.008986266329884529, 0.05418657138943672, 0.07239780575037003, 0.0270456001162529, -0.005971365142613649, 0.04089267924427986, -0.015286908484995365, 0.07532300055027008, -0.01904369704425335, 0.016532965004444122, -0.003840280696749687, -0.008429262787103653, 0.0027776274364441633, 0.03443549945950508, -0.008004517294466496, -0.03938974067568779, 0.052773285657167435, -0.03572678565979004, 0.03092951886355877, -0.06478530913591385, 0.022526808083057404, -0.03320834040641785, -0.08519137650728226, -0.016614820808172226, 0.005019815638661385, 0.029742803424596786, -0.021230537444353104, -0.03253535181283951, 0.0037855743430554867, -0.006942423526197672, 0.06668216735124588, 0.03938738629221916, 0.01266731321811676, -0.015345441177487373, -0.06975848972797394, -0.01086391881108284, -0.010287804529070854, -0.08867698907852173, 0.04705643281340599, 0.05645628273487091, 0.03743954002857208, -0.00025799928698688745, -0.03419620916247368, 0.028755638748407364, -0.011285285465419292, 0.047557536512613297, 0.0590025931596756, 0.04030141979455948, -0.12897256016731262, -0.01153631042689085, -0.03857734799385071, -0.0355745293200016, 0.017442287877202034, -0.016949299722909927, -0.015343599021434784, 0.05567153915762901, -0.04578055813908577, 0.04196636378765106, -0.11070110648870468, 0.06521982699632645, -0.006050616968423128, 0.015272204764187336, -0.0642085149884224, 0.03055175207555294, -0.0035269916988909245, 0.05879314988851547, 0.029176775366067886, -0.0006244868272915483, -0.034679822623729706, 0.027959592640399933, 0.02740325592458248, -0.007192558143287897, 0.07839279621839523, 0.02413785830140114, -0.01777779497206211, 0.051279667764902115, 0.012413924559950829, 0.07165980339050293, 0.08177920430898666, -0.017928652465343475, 0.03284820541739464, 0.03629927337169647, 0.011689173988997936, -0.038273245096206665, -0.07206322997808456, -0.011280156672000885, 0.02752244472503662, -0.012904297560453415, 0.05878971144556999, 0.010776275768876076, 0.03842022642493248, -0.02324208989739418, -0.024084532633423805, 0.004872241988778114, -0.08345818519592285, 0.031773779541254044, -0.03582949936389923, -0.03407168760895729, -0.0549808070063591, 0.09505201876163483, -0.026420332491397858, -0.021553123369812965, 0.007414077874273062, 0.04626118391752243, 0.044245947152376175, -0.0067079742439091206, -0.00021152629051357508, -0.04009483754634857, -0.051128409802913666, -0.06548082083463669, 0.013573662377893925, -0.011385727673768997, -0.017294730991125107, -0.036064837127923965, 0.02949249930679798, 0.002556878374889493, -0.012924609705805779, 0.004686613101512194, 0.018543872982263565, 0.0260139349848032, 0.05568171665072441, -0.06362254172563553, -0.017214974388480186, 0.021374879404902458, -0.03761356323957443, -0.056606125086545944, 0.024728259071707726, 0.033152054995298386, 0.03385738283395767, 0.017818018794059753, -0.03547830879688263, -0.06654436141252518, 0.027467375621199608, 0.004248403944075108, -0.0008099558763206005, -0.007186060305684805, 0.05134839564561844, -0.1128094270825386, 0.006143336649984121, -0.028414251282811165, -0.047699809074401855, -0.006773363333195448, -0.04925183579325676, 0.056950587779283524, 0.03277422487735748, 0.07458318024873734, -0.05797109007835388, 0.05006847903132439, 0.055871181190013885, 0.015539737418293953, -0.025616919621825218, 0.023601148277521133, 0.09229101985692978, 0.04704247787594795, -0.033119574189186096, 0.029936209321022034, 0.0028631496243178844, 0.03194505348801613, -0.0016551243606954813, 0.11204563826322556, -0.037318333983421326, 0.07392904907464981, -0.02685678005218506, -0.05658942088484764, 0.0819123312830925, -0.03871127963066101, 0.051870767027139664, -0.14466261863708496, -0.032263707369565964, 0.03648848086595535, -0.012010450474917889, -0.08477367460727692, -0.030337046831846237, 0.00429595448076725, 0.0802672728896141, -0.08965142071247101, 0.017638256773352623, -0.01888662949204445, -0.06390491127967834, -0.06374437361955643, -0.040571171790361404, 0.09411764144897461, 0.08237935602664948, -0.026322826743125916, 0.050621334463357925, -0.027607880532741547, -0.006165080703794956, 0.011453749611973763, 0.014340098947286606, -0.027006864547729492, 0.032668646425008774, -0.025068294256925583, -0.10309429466724396, -0.019636666402220726, -0.005264912266284227, 0.01318510714918375, 0.015947822481393814, 0.07197590172290802, -0.061222776770591736, 0.005882557947188616, -0.006343072280287743, 0.002126828534528613, 0.05945216491818428, 0.03811293840408325, 0.0330798514187336, -0.0005822235834784806, -0.06640911102294922, -0.14343629777431488, 0.06986556202173233, 0.007056771777570248, 0.026284951716661453, 0.00202706316486001, -0.018969714641571045, 0.043020546436309814, 0.013554466888308525, 0.08722476661205292, 0.049793876707553864, 0.0694308802485466, -0.0383404977619648, 0.0013691103085875511, -0.022998211905360222, -0.05217299237847328, -0.04593024030327797, 0.055540621280670166, -0.04049953073263168, -0.05101463198661804, -0.0021050008945167065, -0.03389962762594223, 0.010771316476166248, 0.029826398938894272, -0.005188154987990856, 0.01770870015025139, 0.009562106803059578, 0.023045681416988373, 0.011567598208785057, -0.020207121968269348, 0.0004111136368010193, 0.037214815616607666, -0.01732616499066353, 0.10587328672409058, 0.013984271325170994, 0.06279412657022476, 0.032123975455760956, -0.007183436304330826, 0.0562329962849617, -0.006047088652849197, -0.05450344458222389, -0.06856777518987656, 0.019642142578959465, 0.023592665791511536, 0.011543293483555317, -0.05861620604991913, 0.013303318992257118, -0.12275812774896622, 0.013624181970953941, 0.031404200941324234, -0.02920270897448063, 0.05208582058548927, 0.057492081075906754, 0.06301245093345642, 0.0470438115298748, -0.017992908135056496, 0.05402308702468872, 0.04619931802153587, -0.030330199748277664, -0.11505372822284698, -0.011964390985667706, -0.05517639219760895, -0.04008941352367401, 0.0497247576713562, 0.011208507232367992, -0.055762700736522675, 0.04312090948224068, 0.07796712219715118, 0.03414187207818031, 0.019026611000299454, 0.0642586424946785, -0.027677075937390327, 0.01517876610159874, -0.021127108484506607, -0.05586392804980278, 0.028914913535118103, 0.010223780758678913, -0.008448352105915546, -0.019754478707909584, 0.011221805587410927, -0.0069505879655480385, 0.09449362754821777, 0.032624173909425735, -0.0038355463184416294, 0.04030449688434601, 0.045156870037317276, 0.017984502017498016, -0.02941700629889965, 0.022512055933475494, -0.07303440570831299, -0.02529275417327881, 0.018579963594675064, 0.0685206726193428, -0.10191226005554199, -0.02413002960383892, 0.03406170383095741, 0.017991894856095314, 0.03564056009054184, -0.0598939023911953, -0.09773315489292145, -0.034971971064805984, -0.033336181193590164, -0.01606447994709015, 0.12677006423473358, 0.05745014548301697, 0.01911797747015953, 0.029966777190566063, 0.050467003136873245, 0.013890041038393974, 0.011233468540012836, 0.0004883483634330332, -0.02242424339056015, 0.0816187858581543, -0.010309746488928795, 0.031137213110923767, -0.09715805947780609, -0.01526492927223444, -0.004777619615197182, -0.12491273880004883, 0.013106674887239933, 0.0285524670034647, -0.03833884745836258, 0.010938246734440327, 0.026101019233465195, -0.02863454632461071, 0.007001898251473904, -0.02461390197277069, 0.03526214882731438, 0.05416778102517128, -0.03433737903833389, -0.052458830177783966, 0.05595726519823074, 0.005246513523161411, -0.021720584481954575, -0.052679505199193954, -0.03862453252077103, -0.0523739755153656, 0.0031247606966644526, -0.0015264037065207958, 0.011799520812928677, -0.01507984846830368, 0.0007012285059317946, -0.03258265182375908, -0.0020692406687885523, 0.03592608496546745, -0.07408493012189865, 0.07283864915370941, -0.0028860748279839754, 0.0533171221613884, 0.04231163486838341, -0.0021939484868198633, 0.0107546616345644, 0.05098873749375343, 0.008813539519906044, -0.013520927168428898, 0.00518006831407547, -0.01872042566537857, -0.0552741102874279, 0.019376184791326523, -0.004067181143909693, 0.034512050449848175, -0.07474131882190704, -0.03418993577361107, -0.02213400788605213, 0.0031968820840120316, 0.07066445797681808, 0.08105839788913727, 0.10197235643863678, 0.023369379341602325, 0.01923779770731926, -0.0114351287484169, 0.04935416951775551, 0.010563505813479424, -0.002041657455265522, 0.003329027211293578, 0.0065612453036010265, -0.021517733111977577, 0.014158675447106361, -0.036029569804668427, 0.019664326682686806, 0.05793924629688263, -0.006179161835461855, -0.040580280125141144, -0.01727006770670414, 0.009023686870932579, 0.011379115283489227, 0.03682195395231247, -0.03274598345160484, 0.01017669215798378, -0.03649948537349701, 0.0018706537084653974, 0.07092069089412689, -0.01045936718583107, 0.07849378138780594, 0.060578975826501846, 0.008126224391162395, -0.0028285174630582333, -0.010568426921963692, -0.0005431327736005187, 0.01874852366745472, -0.028066197410225868, -0.07341983914375305, -0.019849034026265144, -0.02057306095957756, 0.027230121195316315]', 'uploads/faces/user_12_angle3_20260729_165741_392233.jpg'),
(24, NULL, 18, '', '', '2026-07-31 08:09:22', '[0.125174880027771, 0.013664181344211102, 0.02941037155687809, 0.01051365863531828, -0.021445484831929207, -0.02451101504266262, -0.014115272089838982, -0.04575040563941002, 0.008897818624973297, -0.014604845084249973, -0.0788472443819046, 0.04069904237985611, -0.005090830847620964, -0.026308370754122734, -0.01715720444917679, -0.04699636623263359, 0.02181834727525711, -0.022445157170295715, 0.020156363025307655, -0.031301502138376236, -0.10049598664045334, 0.026158630847930908, -0.040669362992048264, 0.020659374073147774, 0.02268158830702305, -0.03769703209400177, -0.02036658301949501, 0.03624336048960686, -0.03791006654500961, 0.029478151351213455, -0.08335626125335693, -0.00969184935092926, -0.00046146169188432395, 0.006875880062580109, -0.05098898336291313, -0.12103438377380371, -0.04672473296523094, -0.00020842321100644767, 0.013389183208346367, 0.04770566523075104, -0.04119766131043434, -0.05100001022219658, 0.016565192490816116, 0.06594905257225037, -0.021211005747318268, -0.060037512332201004, 0.05606508627533913, -0.005056601949036121, -0.012402373366057873, -0.010140725411474705, -0.006788318976759911, -0.005662356503307819, 0.0041523645631968975, -0.0290765892714262, -0.09993359446525574, 0.03634532168507576, -0.061693135648965836, 0.02557329088449478, 0.03671779856085777, -0.06475824862718582, -0.060614120215177536, 0.004102818667888641, -0.06167340278625488, -0.07558669149875641, 0.01105106994509697, -0.02250576950609684, 0.005058860406279564, 0.02735866978764534, -0.053090766072273254, 0.0628468319773674, -0.02559543028473854, 0.0486835278570652, 0.04087269306182861, -0.06675101816654205, -0.05355033650994301, 0.0668632909655571, 0.06617318838834763, 0.00429584039375186, 0.016971368342638016, 0.04578849673271179, 0.07239764928817749, -0.12914405763149261, 0.0848667174577713, -0.01763148605823517, -0.0066537712700665, -0.04339221864938736, 0.05600957199931145, 0.06839869916439056, -0.04623853415250778, 0.02882150188088417, -0.042190294712781906, 0.006051353644579649, -0.0363178588449955, 0.091094970703125, -0.028218623250722885, -0.04244140535593033, -0.06763161718845367, -0.015513195656239986, -0.006879840511828661, 0.02881932258605957, 0.006049240007996559, -0.06787585467100143, 0.007815075106918812, -0.0024779385421425104, 0.03667063266038895, -0.042929235845804214, 0.07271546125411987, 0.012439144775271416, 0.05889511480927467, -0.010946111753582954, 0.04092302918434143, 0.03094736859202385, -0.07676883041858673, -0.03103409707546234, 0.04312413930892944, -3.175190795445815e-05, -0.03317300230264664, -0.09845709800720215, -0.03398441523313522, -0.012316683307290077, 0.015713641420006752, 0.01835125871002674, -0.005703004542738199, 0.053735487163066864, 0.0410163588821888, -0.014796730130910873, 0.030801795423030853, 0.10547700524330139, -0.02512303926050663, -0.03972921893000603, -0.00887107290327549, 0.007810832932591438, 0.05982537195086479, 0.026643892750144005, -0.07005349546670914, 0.012252829037606716, -0.005727684590965509, 0.016167018562555313, 0.043750956654548645, 0.0187853891402483, 0.033274095505476, -0.02986414171755314, 0.004219374153763056, 0.03318256512284279, 0.05511904135346413, 0.06294993311166763, 0.012502159923315048, 0.014298060908913612, 0.04803519323468208, 0.05711774900555611, 0.01985519751906395, -0.04468847066164017, -0.058348219841718674, 0.003925849683582783, 0.004452021326869726, -0.037094708532094955, 0.08436419069766998, -0.03776058182120323, 0.0026519419625401497, 0.06438591331243515, -0.04831581935286522, 0.05343327671289444, -0.032934706658124924, -0.05003805086016655, 0.028330959379673004, 0.04998103156685829, 0.02230052277445793, 0.07008550316095352, -0.008299184963107109, -0.006284827366471291, -0.04298974573612213, 0.025318700820207596, -0.04631121829152107, 0.027454614639282227, -0.03133706748485565, -0.017703844234347343, -0.015293057076632977, -0.0013120794901624322, -0.023576682433485985, -0.04732709005475044, -0.031989891082048416, -0.02337714470922947, 0.021419228985905647, -0.03082454577088356, 0.028623146936297417, 0.0013336240081116557, -0.03983942046761513, 0.062206465750932693, -0.022361166775226593, -0.04945759475231171, -0.08746813982725143, -0.002295856364071369, 0.046392086893320084, 0.03640977665781975, 0.06949062645435333, 0.007834301330149174, 0.042098648846149445, -0.002016252838075161, 0.012164561077952385, -0.0207670871168375, -0.023328321054577827, -0.012613767758011818, 0.010573066771030426, 0.025112438946962357, -0.08111101388931274, -0.0602722130715847, -0.023983558639883995, -0.03082379885017872, -0.003114390652626753, 0.030843794345855713, -0.0073727997951209545, -0.032759156078100204, 0.006730722729116678, 0.03175712376832962, -0.10419417917728424, -0.0347231924533844, -0.0405367836356163, 0.04292036220431328, -0.067596934735775, -0.05838708207011223, 0.002721905242651701, 0.014541172422468662, 0.05199625343084335, 0.028006860986351967, 0.041950371116399765, 0.009007612243294716, 0.025333857163786888, -0.03043612837791443, 0.009644481353461742, -0.009399503469467163, 0.008331830613315105, -0.007088882382959127, -0.07611741870641708, 0.07550938427448273, -0.036149002611637115, -0.04755838215351105, -0.051542773842811584, 0.08246598392724991, 0.07179878652095795, -0.001772382645867765, -0.06983032077550888, 0.010317068547010422, -0.06276688724756241, 0.031483542174100876, -0.03339431434869766, -0.015761833637952805, -0.00241820327937603, -0.03237927332520485, 0.03476081043481827, 0.006486816331744194, -0.022527432069182396, 0.016943635419011116, 0.0382489450275898, -0.05809549242258072, -0.033984266221523285, -0.031669504940509796, 0.029672641307115555, 0.044278621673583984, -0.053403377532958984, 0.000188527672435157, -0.038849905133247375, 0.06987907737493515, 0.05602673068642616, -0.025808457285165787, 0.0312514565885067, -0.07438595592975616, 0.014006280340254307, 0.05428004637360573, -0.0837051048874855, 0.0007282413425855339, -0.0026376056484878063, -0.050558798015117645, -0.05383612588047981, 0.040335580706596375, -0.045516010373830795, 0.023167578503489494, 0.006977078504860401, -0.06088891252875328, 0.013357175514101982, -0.04984601214528084, 0.04057244211435318, -0.04468487203121185, 0.011256805621087551, 0.04295312613248825, 0.02004464715719223, -0.019534260034561157, 0.007169728167355061, 0.0002674576244316995, 0.0033612162806093693, -0.0510195828974247, 0.03454801067709923, -0.0781300887465477, 0.02481020614504814, -0.07974334806203842, 0.0820457860827446, -0.015061999671161175, -0.017465611919760704, 0.0018954912666231394, 0.015135361813008785, 0.021584156900644302, -0.014111974276602268, -0.04289357736706734, -0.09127707779407501, 0.05448460951447487, 0.025239141657948494, -0.023415887728333473, -0.010857651941478252, 0.006670261267572641, 0.015839185565710068, 0.02764599211513996, -0.03688213229179382, -0.041118279099464417, -0.00704282196238637, 0.019582225009799004, -0.031546059995889664, 0.025071531534194946, 0.0554051473736763, -0.061122164130210876, -0.045531079173088074, 0.021337104961276054, 0.06156489625573158, -0.08788763731718063, -0.01640818454325199, -0.05243195220828056, -0.049263373017311096, -0.05633634701371193, -0.027647806331515312, 0.04358629137277603, -0.045245930552482605, 0.05540287122130394, 0.07308898866176605, 0.05782157927751541, -0.11278997361660004, 0.006744739133864641, -0.0722818523645401, 0.038204435259103775, 0.043175339698791504, -0.004193145781755447, 0.02953650802373886, 0.061779290437698364, -0.013644203543663025, 0.01604607328772545, -0.06906437128782272, -0.02680795080959797, -0.05160193145275116, 0.011157400906085968, -0.06992894411087036, 0.00956783164292574, -0.041929762810468674, 0.0134506830945611, -0.0038325695786625147, 0.0314120352268219, 0.040680959820747375, -0.08144305646419525, -0.033228255808353424, -0.03316079452633858, -0.012723944149911404, -0.009155535139143467, -0.026164915412664413, -0.060250818729400635, 0.05352691560983658, 0.028750145807862282, -0.07535409182310104, 0.023591231554746628, -0.05649087578058243, 0.007252344861626625, 0.022756729274988174, 0.04045351594686508, -0.011318757198750973, 0.03862876072525978, -0.00032213758095167577, -0.02705792710185051, -0.05710526555776596, 0.06957035511732101, 0.01800978183746338, -0.06887160986661911, -0.0008786557009443641, -0.07185297459363937, 0.048134975135326385, 0.003769436851143837, -0.005688034929335117, 0.06484074890613556, 0.017842423170804977, 0.004608049523085356, -0.07747595012187958, -0.08817965537309647, 0.03531867638230324, -0.009699065238237381, -0.02079269289970398, 0.06632593274116516, 0.04810037463903427, -0.07106272131204605, 0.011118138208985329, -0.026271317154169083, -0.06313735991716385, -0.01467982493340969, -0.013807009905576706, -0.020194819197058678, -0.05394172668457031, 0.004065786954015493, -0.03933946043252945, 0.042170729488134384, -0.008834533393383026, 0.007784037385135889, -0.01889655366539955, 0.011475461535155773, -0.03744005784392357, -0.07238087803125381, 0.0191938579082489, 0.007967041805386543, -0.0171302929520607, -0.07734793424606323, 0.0348748117685318, -0.00742427259683609, 0.002448736224323511, 0.030509989708662033, -0.028312548995018005, -0.01983794942498207, -0.025776732712984085, 0.022055812180042267, 0.04083215817809105, -0.07418684661388397, -0.013538237661123276, 0.0435420423746109, -0.10124584287405014, 0.06331321597099304, 0.024724802002310753, -0.0347725972533226, -0.07798038423061371, 0.04631062224507332, 0.0595816932618618, -0.00376656511798501, 0.08475228399038315, 0.02122139185667038, 0.0707344189286232, -0.02707982249557972, 0.008319715037941933, 0.001690130797214806, -0.04173259809613228, -0.0305912084877491, -0.04983395338058472, -0.044405028223991394, -0.05888064578175545, -0.021639572456479073, -8.204797632060945e-05, -0.003777524456381798, -0.006856431718915701, 0.04220970347523689, 0.04813038557767868, 0.006128303706645966, 0.03324222192168236, 0.047266073524951935, 0.0007837516604922712, -0.01135554350912571, -0.02699694037437439, 0.043944887816905975, -0.08655784279108047, 0.05622761324048042, -0.0660286620259285, 0.03292366489768028, 0.029989542439579964, -0.009758392348885536, -0.015148504637181759, 0.1117669939994812, 0.012747402302920818, -0.025163834914565086, -0.003405250608921051, -0.003961179871112108, -0.10313960909843445, 0.02884446270763874, -0.08333589881658554, -0.03072960488498211, -0.08565589785575867, 0.06296845525503159, 0.03642928600311279, 0.002355195814743638, 0.011199712753295898, -0.009233909659087658, 0.009441052563488483, 0.014969240874052048, 0.05933977663516998, 0.012676839716732502, -0.07900479435920715, 0.0036119194701313972, -0.025226984173059464, 0.06000904366374016, 0.05692124366760254, 0.00017736907466314733, 0.002844718750566244, 0.1036413386464119, -0.007669846061617136, -0.06517458707094193, 0.04693889245390892, 0.05290543660521507, 0.01133926585316658, -0.02848926931619644, 0.02274257317185402, -0.0161504615098238, 0.01616552658379078, -0.0646231397986412, -0.03521738573908806, -0.007565113715827465, 0.1157713383436203, -0.016726674512028694, 0.03227626532316208, 0.04592760279774666, 0.0327751487493515, 0.02892596274614334, -0.04067084938287735, 0.055560387670993805, -0.008791756816208363, 0.01222692895680666]', 'uploads/faces/user_18_angle0_20260731_133912_993969.jpg'),
(25, NULL, 18, '', '', '2026-07-31 08:09:22', '[0.10150472819805145, -0.0002408821164863184, -0.0017446866258978844, -0.041583187878131866, 0.014119469560682774, -0.021785365417599678, -0.018523240461945534, -0.015910856425762177, 0.023518400266766548, 0.039146579802036285, -0.040983330458402634, 0.028459962457418442, -0.056231316179037094, 0.02300287038087845, -0.02882690168917179, -0.06535858660936356, -0.009196209721267223, -0.0021793011110275984, 0.018122419714927673, 0.006641181651502848, -0.1144397035241127, 0.06130243465304375, -0.03012790158390999, 0.020095668733119965, 0.06745610386133194, -0.07629220187664032, 0.0010277846595272422, 0.016963370144367218, -0.0003711020399350673, -0.0005605594487860799, -0.0661897361278534, -0.011584467254579067, 0.008030140772461891, -0.024112096056342125, -0.04655538126826286, -0.0757780522108078, -0.054008837789297104, 0.006071806885302067, 0.06495166569948196, 0.07530990242958069, -0.023349836468696594, -0.007008720654994249, 0.012144643813371658, 0.085028275847435, -0.026045778766274452, -0.009897512383759022, 0.01785031519830227, -0.04434255138039589, -0.011991128325462341, -0.03296396881341934, -8.149712812155485e-05, 0.06888625025749207, 0.022661205381155014, -0.023903196677565575, -0.025403006002306938, 0.043591003865003586, -0.052281878888607025, 0.018584640696644783, 0.03985508531332016, -0.05096633732318878, -0.04065600782632828, -0.06681258976459503, -0.07796638458967209, 0.016124660149216652, 0.02992084063589573, -0.05558226257562637, 0.034526750445365906, 0.023603243753314018, -0.11598870158195496, 0.05468674376606941, -0.06261122971773148, -0.004697071388363838, 0.03950660303235054, -0.07941962033510208, -0.03458116203546524, 0.05977872386574745, 0.10655415058135986, -0.0038272151723504066, 0.04281510040163994, 0.010092710144817829, 0.0424351841211319, -0.08113601058721542, 0.015618101693689823, -0.003082029288634658, 0.011867360211908817, -0.004108835011720657, 0.045519664883613586, -0.048086486756801605, -0.05823364108800888, 0.04158419370651245, -0.003477418562397361, 0.04247765243053436, -0.07148054242134094, 0.034715697169303894, 0.006594677455723286, -0.04508129507303238, -0.019307710230350494, 0.04106049984693527, 0.010232088156044483, 0.0874047726392746, 0.01667383313179016, -0.025130242109298706, 0.022587912157177925, 0.029239851981401443, 0.04207095503807068, -0.06117909401655197, 0.04472554847598076, -0.03210384398698807, -0.042853835970163345, 0.011640393175184727, -0.041469182819128036, 0.022506698966026306, -0.06269241869449615, 0.005320672411471605, 0.039546847343444824, -0.009412982501089573, 0.016520651057362556, -0.0729294940829277, -0.047571875154972076, 0.015174105763435364, 0.03252986818552017, 0.05584803223609924, -0.07522372156381607, 0.02319013886153698, 0.022067155689001083, -0.02377134934067726, 0.044185660779476166, 0.08914264291524887, -0.044491056352853775, 0.0344453863799572, 0.03649232164025307, -0.03671833872795105, 0.040730107575654984, -0.003542446531355381, -0.06976773589849472, -0.005090269260108471, 0.0049375309608876705, 0.010004916228353977, 0.049861472100019455, 0.012667828239500523, -0.0377422459423542, 0.02311878465116024, 0.043807029724121094, 0.02785843424499035, -0.006662702653557062, -0.0039752391166985035, -0.02801159769296646, -0.027687180787324905, 0.0023627919144928455, 0.016479743644595146, 0.024735895916819572, -0.057973772287368774, 0.016105318441987038, -0.041637178510427475, 0.03794541209936142, -0.023478738963603973, 0.09056923538446426, -0.05984887480735779, -0.03705524653196335, 0.031118212267756462, 0.0018264467362314463, 0.0419931523501873, -0.08702036738395691, -0.019183853641152382, 0.03280622139573097, 0.08414696156978607, 0.03917182609438896, 0.0013668783940374851, -0.08193953335285187, -0.043265681713819504, 0.010266333818435669, -0.025316165760159492, -0.03257781267166138, -0.009944098070263863, -0.04387906566262245, -0.0827624499797821, -0.013844519853591919, 0.006609312724322081, -0.019355546683073044, -0.053457360714673996, -0.0162966288626194, -0.02851548232138157, 0.03950602188706398, -0.06774047762155533, 0.0008513026987202466, -0.05558045580983162, -0.04738669842481613, 0.028483057394623756, -0.04316265136003494, 0.005980837158858776, -0.09170247614383698, 0.025660531595349312, 0.045947086066007614, 0.021897483617067337, 0.01080892514437437, 0.00044502250966615975, 0.016486911103129387, -0.0078959409147501, 0.027537520974874496, -0.04001274332404137, -0.08259492367506027, -0.014074327424168587, -0.04738739877939224, 0.0682310089468956, -0.03607775643467903, -0.06670542061328888, 0.02773626707494259, 0.030360402539372444, -0.02941702865064144, 0.011576250195503235, -0.019946694374084473, -0.0426781065762043, 0.05858265981078148, -0.011247625574469566, -0.05977865308523178, 0.032940082252025604, -0.009895950555801392, -0.016943536698818207, -0.007878417149186134, -0.045971669256687164, -0.049708664417266846, 0.0578659363090992, -0.04682410880923271, 0.04357972741127014, 0.010632184334099293, -0.017233600839972496, 0.005350031889975071, -0.011489016003906727, 0.016706790775060654, -0.04105227440595627, 0.06168070808053017, -0.05675644800066948, 0.026864834129810333, 0.08871788531541824, 0.02655959129333496, -0.05652312561869621, -0.03415737301111221, 0.030843116343021393, 0.06050163507461548, -0.013518808409571648, -0.06287730485200882, -0.044521939009428024, -0.10134104639291763, 0.006917974445968866, 0.001327294623479247, 0.03328139707446098, 0.001162078813649714, 0.0026782455388456583, 0.025116953998804092, -0.015782544389367104, 0.014873895794153214, 0.016789384186267853, 0.0819280743598938, -0.027043139562010765, -0.037562813609838486, 0.024577870965003967, -0.026594014838337898, 0.04086451977491379, -0.03153977915644646, 0.07500430196523666, -0.0225937832146883, 0.06143224239349365, 0.00564633309841156, -0.02246037684381008, 0.08400241285562515, -0.007262768689543009, 0.021793415769934654, 0.026759926229715347, -0.07585473358631134, -0.022400010377168655, -0.021061046048998833, -0.1080920547246933, -0.01803581416606903, 0.04275739938020706, -0.07723501324653625, 0.0710599422454834, -0.0008232297841459513, -0.06934364885091782, -0.006570211611688137, -0.028030261397361755, 0.06944350153207779, -0.02216716855764389, -0.0562700517475605, 0.034833550453186035, 0.019509511068463326, 0.011398674920201302, 0.07042743265628815, -0.01504245214164257, -0.028432320803403854, -0.08256547898054123, 0.01698525808751583, -0.09996751695871353, 0.06132305786013603, -0.023866329342126846, 0.05965042486786842, -0.0518382266163826, 0.028696734458208084, -0.02502419427037239, -0.010750088840723038, -0.03928493708372116, -0.018409447744488716, -0.02552139386534691, -0.027214573696255684, 0.06846214085817337, 0.0271604023873806, 0.012810162268579006, -0.01875138096511364, -0.05599414184689522, 0.030352100729942322, -0.004585502669215202, -0.05020952969789505, -0.07996372133493423, 0.01658221147954464, 0.034542687237262726, -0.06288154423236847, -0.008978947065770626, 0.002997261704877019, -0.05311821028590202, -0.0341363288462162, 0.040682103484869, -0.021249406039714813, -0.08455126732587814, -0.022249963134527206, -0.09571777284145355, 0.029116250574588776, -0.044191863387823105, -0.017702946439385414, 0.07972221076488495, -0.04595264792442322, -0.013197462074458599, 0.04149491339921951, -0.013225930742919445, -0.07584627717733383, 0.025049978867173195, -0.060129474848508835, 0.04050355777144432, -0.002856057370081544, 0.011456889100372791, 0.0028198512736707926, 0.0480407290160656, -0.03378695249557495, -0.016520293429493904, -0.03335156664252281, -0.0036717888433486223, -0.08227995038032532, 0.028785929083824158, -0.06005244702100754, -0.04416186735033989, -0.00330357369966805, -0.003206744324415922, -0.022587351500988007, 0.06073136255145073, -0.030680477619171143, -0.0615106001496315, -0.03315531462430954, 0.01485355943441391, 0.0006928432849235833, 0.055807214230298996, 0.00957917608320713, -0.06694275885820389, 0.025938117876648903, 0.029071249067783356, -0.07291701436042786, 0.05117866024374962, 0.02494497410953045, 0.010093845427036285, 0.016118217259645462, 0.0007737181149423122, 0.01426111999899149, -0.02189074084162712, 0.06576607376337051, 0.04918000102043152, -0.015257546678185463, -0.01765304245054722, 0.04509030655026436, -0.047063738107681274, -0.0022491400595754385, -0.012143686413764954, 0.042803991585969925, 0.017640333622694016, -0.039522696286439896, 0.08940698951482773, 0.036531250923871994, -0.04624512046575546, -0.031447723507881165, -0.04739925265312195, 0.03718239441514015, -0.014858686365187168, -0.0771460309624672, 0.03066241182386875, 0.015406345948576927, -0.02241177298128605, 0.009357779286801815, -0.03740362823009491, -0.05468267947435379, 0.023324891924858093, 0.005108058452606201, -0.0468461774289608, -0.02932506799697876, -0.02160944789648056, -0.030786314979195595, 0.05360397323966026, -0.0007356718415394425, -0.003420439315959811, -0.02232184447348118, 0.06754156947135925, 0.01545373722910881, -0.06260336190462112, 0.027675410732626915, -0.025249265134334564, -0.04888036102056503, -0.07413104921579361, -0.05655696615576744, -0.08535991609096527, -0.003881595330312848, 0.01490350067615509, -0.059714410454034805, -0.03133007511496544, -0.07937707751989365, -0.013002839870750904, 0.01825845055282116, -0.07326420396566391, -0.01931983232498169, 0.04721030965447426, -0.10297263413667679, 0.0507584884762764, 0.023780426010489464, 0.015247628092765808, 0.013939105905592442, 0.06336658447980881, 0.06938081979751587, 0.004856523592025042, 0.044304754585027695, 0.026431510224938393, 0.05631251260638237, -0.055089786648750305, 0.07165195047855377, -0.056597836315631866, -0.03597646579146385, 0.00854902807623148, -0.06228014826774597, -0.03603753820061684, -0.03498820587992668, 0.04051382094621658, -0.07590315490961075, 0.018601061776280403, -0.002818566747009754, 0.006443794816732407, 0.08501417189836502, 0.020971747115254402, 0.006356061436235905, -0.04192496836185455, -0.0025897652376443148, -0.060618385672569275, -0.08357781171798706, 0.015409274026751518, -0.0887763500213623, 0.05048933997750282, 0.008498314768075943, -0.05685342475771904, 0.03874288499355316, 0.02945142798125744, 0.00401800824329257, 0.030264565721154213, -0.037688206881284714, -0.01375679112970829, -0.029070306569337845, -0.008933257311582565, -0.05698368698358536, 0.044881924986839294, -0.07527925074100494, -0.022607434540987015, -0.012597846798598766, 0.0973225012421608, 0.0017856622580438852, -0.09432440251111984, 0.017218802124261856, -0.052883926779031754, 0.07728133350610733, 0.023766854777932167, 0.05028707534074783, 0.004584986716508865, -0.055557407438755035, 0.007656608708202839, 0.012833796441555023, 0.06368924677371979, 0.06194233521819115, 0.00689934054389596, -0.02918066270649433, 0.07031403481960297, 0.038095615804195404, -0.0762212872505188, 0.05722392722964287, 0.05090630054473877, 0.029855389147996902, -0.0035154481884092093, 0.034361738711595535, -0.045983586460351944, 0.0200339388102293, -0.07103204727172852, 0.014256038703024387, 0.024004900828003883, 0.07205083966255188, -0.005805580876767635, 0.0004461680946405977, 0.013425706885755062, -0.004065203480422497, 0.025017984211444855, -0.010895443148911, 0.02865762449800968, -0.06916915625333786, 0.041864700615406036]', 'uploads/faces/user_18_angle1_20260731_133915_825031.jpg'),
(26, NULL, 18, '', '', '2026-07-31 08:09:22', '[0.09592002630233765, 0.008961929008364677, 0.04155238717794418, -0.0472860261797905, -0.020606350153684616, -0.02237706258893013, -0.04628109559416771, 0.04455626755952835, 0.045881208032369614, -0.03511307016015053, -0.0044356416910886765, -0.005974854342639446, 0.05391114205121994, -0.03372739255428314, -0.06367970257997513, -0.04135764762759209, 0.014974121935665607, -0.040391743183135986, 0.0474424809217453, 0.03603668883442879, -0.0669042319059372, 0.04790182039141655, -0.03457246720790863, 0.003835243871435523, 0.03825986757874489, -0.01819377765059471, -0.05039559304714203, 0.06883716583251953, -0.04357884079217911, -0.03055790811777115, -0.08905694633722305, 0.05712152644991875, -0.03482754901051521, -0.0008593062520958483, -0.016862664371728897, -0.09008421748876572, -0.07009205222129822, -0.0158248171210289, 0.040756888687610626, 0.04886724427342415, -0.07009706646203995, 0.02484435960650444, 0.03839459642767906, 0.07773713022470474, -0.017928915098309517, -0.06016422063112259, -0.011308835819363594, -0.04548339545726776, 0.028858879581093788, 0.007159425411373377, 0.05931852012872696, 0.028455665335059166, 0.01289614848792553, -0.048430055379867554, -0.0181716438382864, 0.02293858677148819, -0.004417434800416231, -0.011825489811599255, 0.08899292349815369, -0.02249233052134514, 0.0006033166428096592, -0.00030207535019144416, -0.035942986607551575, -0.052999068051576614, 0.063511922955513, -0.04245544597506523, -0.002916920930147171, 0.01899767480790615, -0.02467905730009079, 0.05584045499563217, -0.030840646475553513, 0.05429062619805336, 0.03135402873158455, -0.0395234115421772, -0.09614017605781555, 0.024161959066987038, 0.05600219592452049, -0.006429732311517, 0.0875336155295372, 0.003286931198090315, 0.08203405141830444, -0.08795137703418732, 0.06718745082616806, -0.023499034345149994, 0.026814503595232964, -0.05178188160061836, 0.05426127091050148, -0.0025919561740010977, 0.018167071044445038, -0.0065298909321427345, -0.040632639080286026, 0.02331080101430416, -0.02632136456668377, 0.04453076794743538, -0.05041741207242012, -0.022528378292918205, -0.07286128401756287, -0.03111259452998638, -0.002603661036118865, 0.03078961744904518, 0.08203443139791489, -0.019015328958630562, 0.06461932510137558, -0.035874731838703156, -0.0009303039987571537, -0.04029655456542969, 0.055897656828165054, -0.029870249330997467, 0.02781827561557293, 0.02737840823829174, 0.011406267993152142, 0.025590024888515472, -0.08515480905771255, -0.049133166670799255, 0.029696915298700333, -0.03885609284043312, 0.0004384585190564394, -0.03106040321290493, -0.013601243495941162, 0.004096191376447678, 0.005548685789108276, 0.0694262832403183, -0.020336126908659935, 0.030110478401184082, 0.02943103387951851, -0.024827992543578148, 0.06559885293245316, 0.04925350472331047, 0.00017406881670467556, 0.03327755257487297, -0.0075096506625413895, 0.01852528005838394, 0.058749932795763016, 0.05166591703891754, -0.08518420904874802, -0.0657806470990181, 0.01082206517457962, 0.02833203785121441, 0.04377737268805504, -0.0832107663154602, -0.015061655081808567, 0.016606925055384636, -0.02787775546312332, 0.0179582666605711, 0.030587652698159218, 0.05099582299590111, 0.011191979981958866, 0.008686990477144718, 0.012638755142688751, 0.04115648940205574, 0.0024412258062511683, -0.02970059961080551, 0.003607839113101363, 0.00964244082570076, -0.031561870127916336, 0.04972083866596222, 0.04308200627565384, -0.036211855709552765, -0.02858998253941536, 0.028311045840382576, -0.07404688745737076, 0.008214549161493778, 0.0004939973005093634, -0.017784319818019867, 0.06972658634185791, 0.05663730949163437, -0.012066091410815716, -0.02843313291668892, -0.04879565164446831, -0.042563386261463165, 0.048134591430425644, -0.009533443488180637, 0.04509005695581436, -0.006148550659418106, -0.014001750387251377, -0.060036551207304, -0.059845395386219025, -0.05055145174264908, -0.05856434628367424, -0.041368093341588974, -0.019075734540820122, -0.02039041742682457, 0.03236577659845352, -0.06861050426959991, -0.03642713651061058, -0.014630649238824844, -0.061612967401742935, 0.0751492828130722, -0.04117807745933533, 0.010248257778584957, -0.04934829846024513, -0.017018349841237068, 0.08621838688850403, 0.10145314037799835, 0.03160794451832771, -0.012506375089287758, 0.015361819416284561, -0.01564229093492031, 0.009360386058688164, -0.0246184840798378, -0.012291290797293186, -0.0214603953063488, -0.05295062065124512, 0.056306689977645874, -0.05092154070734978, -0.01326908078044653, -0.006544824223965406, -0.008024358190596104, 0.059168342500925064, 0.0053354306146502495, -0.04448440670967102, -0.021746646612882614, -0.001666766474954784, -0.03717765957117081, -0.04879792779684067, 0.008528102189302444, 0.006422496400773525, 0.029514001682400703, -0.03826550394296646, -0.03362482413649559, 0.015360677614808083, 0.04051758721470833, -0.055252086371183395, 0.04832608997821808, 0.05238684266805649, -0.07351766526699066, 0.02252080850303173, -0.034865692257881165, -0.032955870032310486, 0.029460379853844643, 0.011335186660289764, 0.0169720109552145, -0.011249392293393612, 0.023302529007196426, 0.025676919147372246, -0.03987102955579758, -0.005872383248060942, 0.015304233878850937, 0.05410132184624672, 0.04442281648516655, -0.10105305165052414, -0.021005727350711823, -0.03925530984997749, 0.045842092484235764, -0.02413308061659336, 0.00975445844233036, -0.015542320907115936, -0.05505218729376793, 0.035275593400001526, -0.024780554696917534, 0.034875914454460144, -0.024657225236296654, 0.0619048997759819, -0.046781182289123535, -0.033679038286209106, 0.005663199350237846, -0.02889101207256317, 0.09358251094818115, -0.068340003490448, -0.024080222472548485, 0.002810482168570161, 0.05244099721312523, 0.05742739513516426, -0.03136291727423668, 0.10282643884420395, -0.026441631838679314, 0.058331068605184555, 0.024265937507152557, -0.10454278439283371, 0.015787940472364426, 0.017965078353881836, -0.033116601407527924, 0.022159934043884277, 0.04771348834037781, -0.09754333645105362, 0.06981724500656128, -0.02367333322763443, -0.07583200186491013, -0.0014368492411449552, -0.010503790341317654, 0.04088660702109337, -0.01808135211467743, 0.0118331927806139, 0.08333706855773926, 0.015595114789903164, 0.013710476458072662, 0.032319750636816025, 0.021823763847351074, -0.06373259425163269, -0.07452902942895889, 0.031478118151426315, -0.04988856986165047, 0.015341341495513916, -0.025493718683719635, 0.09170923382043839, -0.04444658011198044, -0.023090511560440063, 0.027554452419281006, -0.00914077926427126, -0.015256094746291637, 0.012368137016892433, -0.016942385584115982, -0.08675826340913773, 0.08054906874895096, 0.04254963994026184, -0.03731593117117882, 0.0010500772623345256, -0.057607315480709076, 0.02312982827425003, -0.020416881889104843, -0.05275791510939598, -0.06355375796556473, -0.01872503012418747, 0.0519479364156723, -0.085650734603405, 0.005162370856851339, 0.02614775113761425, -0.050783053040504456, -0.028345325961709023, 0.041330065578222275, 0.007960897870361805, -0.06980352103710175, -0.012008026242256165, -0.10727550834417343, -0.03868836164474487, -0.0069941566325724125, 0.013628334738314152, 0.06005823239684105, -0.013681328855454922, 0.047468554228544235, 0.08798167109489441, -0.006734822876751423, -0.061300523579120636, 0.05586548149585724, -0.07640765607357025, 0.03742288425564766, 0.027550023049116135, -0.007038883399218321, 0.030790479853749275, 0.056068237870931625, -0.034130074083805084, -0.0470314659178257, -0.018708394840359688, -0.05693310871720314, -0.11483371257781982, 0.04712478816509247, -0.057504698634147644, 0.008563089184463024, -0.11239901930093765, 0.02298840321600437, -0.018633447587490082, 0.017072541639208794, 0.0227738656103611, -0.12891855835914612, 0.0014264743076637387, -0.02612103894352913, -0.01946217194199562, 0.016230132430791855, 0.0494474321603775, -0.08260002732276917, 0.015981454402208328, -0.008780487813055515, -0.05094432830810547, -0.006479133851826191, 0.027222994714975357, 0.014176229946315289, 0.06793136894702911, -0.02324669063091278, -0.014055744744837284, -0.002666427055373788, -0.020126989111304283, -0.0035609027836471796, 0.033415988087654114, 0.004276752006262541, 0.05691863223910332, -0.02366763912141323, -0.005987134762108326, 0.020663103088736534, 0.04914586618542671, -0.0031152861192822456, 0.026939600706100464, 0.04335290193557739, 0.0053825839422643185, -0.06971703469753265, -0.083836130797863, -0.06288595497608185, 0.09662248194217682, 0.06506624072790146, -0.012998905964195728, -0.0034761796705424786, -0.027514973655343056, -0.03668823093175888, 0.0349310040473938, -0.03895624354481697, -0.028443854302167892, -0.013430498540401459, -0.06511499732732773, -0.047586508095264435, -0.042279504239559174, -0.0053200190886855125, -0.03780488297343254, 0.020938919857144356, -0.01806817762553692, -0.0025371392257511616, 0.01793619431555271, 0.08341306447982788, -0.010371392592787743, -0.005777610465884209, -0.004354857373982668, 0.0158979669213295, -0.04704732075333595, -0.08471424132585526, -0.03510143235325813, 0.00887297373265028, -0.033262841403484344, -0.017415493726730347, -0.042720623314380646, -0.044740572571754456, -0.05603873357176781, 0.010234937071800232, 0.07203032821416855, -0.04396882280707359, 0.009898052550852299, -0.0028270070906728506, -0.07400646805763245, 0.14135725796222687, 0.02044597826898098, -0.012651773169636726, -0.03252612426877022, 0.06000012531876564, 0.050764359533786774, 0.06718464195728302, 0.04161021485924721, -0.004159875214099884, 0.04751523956656456, 0.019559597596526146, 0.005497247911989689, 0.01120113953948021, -0.004765935707837343, -0.03936726599931717, -0.05450588837265968, 0.01017482578754425, -0.05671289563179016, 0.03669603168964386, -0.0451137013733387, 0.024743525311350822, 0.02968536503612995, 0.030500460416078568, 0.00023359358601737767, 0.004606832284480333, 0.06060691550374031, 0.013281955383718014, -0.03505418449640274, -0.013981678523123264, -0.07607561349868774, -0.01689055934548378, -0.053535301238298416, 0.02314033918082714, -0.04208153858780861, -0.033421680331230164, 0.04661085084080696, -0.014145915396511555, 0.024097789078950882, 0.057417891919612885, -0.021331844851374626, -0.03858962655067444, -0.03443941846489906, -0.043334588408470154, -0.029371311888098717, -0.026495937258005142, -0.07832881063222885, -0.05813831463456154, -0.002032962627708912, 0.12501393258571625, 0.042015064507722855, -0.05417514219880104, -0.027508417144417763, 0.015276700258255005, -0.0009492743411101401, -0.026180336251854897, 0.005592829082161188, -0.019838469102978706, -0.04388560354709625, -0.03720102459192276, -0.019690025597810745, 0.054755475372076035, 0.022002553567290306, -0.058588657528162, 0.03770405054092407, 0.024220246821641922, 0.026584889739751816, -0.05807321146130562, 0.009185640141367912, 0.04096362367272377, 0.022213798016309738, -0.0270072054117918, 0.09420141577720642, -0.057802487164735794, -0.009357874281704426, -0.00735437823459506, -0.05980738252401352, 0.025431770831346512, 0.09205152839422226, 0.0010848204838111997, -0.03765491023659706, 0.02412542514503002, -0.018812600523233414, 0.000776943750679493, 0.014391794800758362, 0.017173131927847862, 0.00875599030405283, -0.021915990859270096]', 'uploads/faces/user_18_angle2_20260731_133918_232458.jpg');
INSERT INTO `face_embeddings` (`id`, `employee_id`, `user_id`, `embedding_vector`, `capture_angle`, `created_at`, `embedding`, `image_path`) VALUES
(27, NULL, 18, '', '', '2026-07-31 08:09:22', '[0.1065312996506691, -0.023243319243192673, 0.03804292902350426, -0.005162181332707405, -4.835399522562511e-06, -0.017473211511969566, -0.07790469378232956, -0.008966158144176006, 0.02546365186572075, -0.0005616038688458502, -0.05121394991874695, -0.030743349343538284, -0.030073203146457672, 0.0011849135626107454, -0.0431184284389019, -0.02219627983868122, -0.008326235227286816, -0.04152561351656914, 0.033670973032712936, -0.04287571460008621, -0.09767372906208038, 0.0004819771274924278, -0.00012435181997716427, 0.02697308547794819, 0.03305336460471153, -0.06164411082863808, -0.05142012611031532, 0.06952214986085892, -0.06663373857736588, 0.005460008047521114, -0.12645210325717926, -0.05090980604290962, -0.01666654460132122, 0.0046454682014882565, -0.04462587833404541, -0.058855559676885605, -0.06583765149116516, 0.00472300685942173, 0.013812358491122723, -0.008770592510700226, -0.0224823746830225, -0.02317107655107975, 0.04559575766324997, 0.0828699842095375, -0.02660086564719677, -0.028910890221595764, 0.01901516690850258, -0.03726305067539215, 0.0028505942318588495, -0.019670521840453148, 0.012033398263156414, 0.017255710437893867, -0.05636294558644295, -0.04718373343348503, -0.07292851060628891, -0.010598336346447468, -0.0787028968334198, -0.0016060711350291967, 0.07941252738237381, -0.05918224900960922, -0.025608563795685768, -0.0002374959731241688, -0.07408282160758972, -0.0071447608061134815, 0.045697685331106186, -0.03041446954011917, 0.027809331193566322, -0.02281121350824833, -0.03422313183546066, 0.03558843210339546, -0.07767506688833237, 0.0651356503367424, 0.0313684307038784, -0.07165740430355072, -0.05097777396440506, 0.0682806670665741, 0.06589996814727783, -0.025897620245814323, 0.0023506605066359043, 0.019607678055763245, 0.056394532322883606, -0.09580197185277939, 0.05443744361400604, -0.04814990982413292, -0.006421550177037716, -0.04046470671892166, 0.02114003337919712, 0.022690072655677795, -0.015295329503715038, 0.005552009213715792, -0.06141372397542, 0.03091813623905182, -0.084647037088871, 0.004507077392190695, -0.016376221552491188, -0.07135237753391266, -0.029801687225699425, -0.04323549196124077, 0.018277447670698166, 0.0012095834827050567, 0.030829524621367455, -0.03660007193684578, 0.033768996596336365, 0.0614747516810894, 0.00686592748388648, -0.0727791041135788, 0.0575607605278492, -0.02452007681131363, 0.008773744106292725, -0.00630689412355423, -0.004601707216352224, 0.0687980130314827, -0.055666908621788025, 0.0004110800800845027, 0.04993949458003044, 0.021588023751974106, -0.04942765086889267, -0.05527994781732559, -0.04001705348491669, 0.021385682746767998, 0.05654901638627052, 0.024228433147072792, -0.019688209518790245, 0.06674319505691528, 0.014988214708864689, -0.007718270178884268, 0.028464142233133316, 0.07802443206310272, 0.020174488425254822, -0.05148085579276085, -0.02028735540807247, -0.05241796374320984, -0.004378603771328926, -0.049949005246162415, -0.010588649660348892, -0.010418466292321682, -0.007881986908614635, 0.021366165950894356, 0.02340852841734886, -0.007084598299115896, -0.018029697239398956, 0.001973796170204878, -0.026402339339256287, 0.06640512496232986, 0.033428799360990524, 0.03519812598824501, 0.03438638895750046, -0.004370565060526133, -0.009813152253627777, 0.008074140176177025, 0.011817595921456814, -0.07473151385784149, -0.038078103214502335, 0.02898242324590683, 0.02810170128941536, 0.034135375171899796, 0.02758900262415409, -0.028839299455285072, 0.013553098775446415, 0.066725954413414, -0.06538297235965729, 0.02706608735024929, -0.022478831931948662, -0.04947147145867348, -0.01613224297761917, 0.032177750021219254, -0.04129631817340851, 0.001317710499279201, -0.021796749904751778, -0.07396071404218674, 0.009802441112697124, 0.0004507901903707534, -0.043487634509801865, -0.004154120571911335, -0.06875228136777878, -0.0585884191095829, -0.04096740111708641, -0.03168487176299095, 0.0077235884964466095, -0.07955853641033173, -0.0558524988591671, -0.06297189742326736, -0.00701929209753871, -0.015964839607477188, 0.045986466109752655, 0.02294711209833622, -0.045779820531606674, 0.08785077184438705, -0.059427231550216675, 0.014847511425614357, -0.0370953269302845, 0.0411725714802742, 0.053854819387197495, 0.01502065360546112, 0.033510174602270126, -0.005435592494904995, -0.0026906351558864117, -0.009565403684973717, -0.03186742588877678, -0.07083046436309814, -0.06213276460766792, -0.010828964412212372, 0.01477748528122902, 0.08850779384374619, -0.06086834520101547, -0.011524690315127373, -0.01916005089879036, -0.007140993606299162, 0.014127126894891262, 0.03891020640730858, 0.04617302864789963, 0.01679270900785923, -0.012308002449572086, -0.058438148349523544, -0.04792749509215355, 0.032925236970186234, -0.026978496462106705, 0.07375422865152359, -0.01316825207322836, 0.006376755889505148, 0.0420190766453743, 0.01641138084232807, 0.015335125848650932, 0.024463366717100143, 0.08174362778663635, -0.0427672415971756, 0.03544086590409279, -0.012568839825689793, 0.00938973855227232, 0.00985017791390419, -0.02154579572379589, -0.01574903167784214, -0.04067066311836243, 0.09563259780406952, -0.04468663036823273, -0.04328402876853943, -0.036693375557661057, 0.04733459651470184, 0.018687406554818153, -0.008567112497985363, -0.0810631513595581, -0.03465953469276428, -0.04752509668469429, 0.05476175993680954, -0.06629065424203873, -0.075716033577919, -0.024035286158323288, -0.06405454128980637, 0.0794820636510849, -0.02022193744778633, 0.005630407016724348, 0.03034810908138752, 0.04812400043010712, -0.025638442486524582, -0.04232964664697647, 0.004082212690263987, 0.02043546736240387, 0.018626416102051735, -0.06686891615390778, 0.021231798455119133, -0.1017192155122757, 0.050249580293893814, 0.01444121915847063, 0.039702486246824265, 0.0707898661494255, -0.03753932937979698, -0.006840602494776249, 0.014993615448474884, -0.10629057139158249, 0.009910176508128643, 0.011940954253077507, -0.054996099323034286, -0.022812530398368835, 0.09053678065538406, -0.08693914115428925, 0.01989459991455078, 0.010824411176145077, -0.0986706018447876, 0.031675904989242554, -0.005193956196308136, 0.01502939872443676, -0.014954683370888233, 0.009944872930645943, 0.042607150971889496, 0.02801486663520336, 0.03177454322576523, 0.016642402857542038, 0.01864972524344921, -0.03522155433893204, -0.07445821166038513, 0.050996702164411545, -0.06655853241682053, 0.04508325457572937, -0.0405767522752285, 0.06811446696519852, -0.026263004168868065, -0.038371022790670395, -0.0007140709785744548, 0.03645782917737961, 0.03602372854948044, 0.023469148203730583, -0.12095142155885696, -0.0956496149301529, 0.1180470809340477, -0.011889360845088959, 0.015340331010520458, -0.0029056090861558914, 0.008307023905217648, 0.03681221976876259, -0.028270788490772247, -0.042201049625873566, -0.015503744594752789, 0.013127394951879978, 0.02524493634700775, -0.050949178636074066, 0.008157540112733841, 0.05602365359663963, -0.033031150698661804, -0.038518134504556656, 0.07248801738023758, 0.08888624608516693, -0.06538502871990204, 0.00429378030821681, -0.04046032950282097, -0.06203801557421684, -0.06754731386899948, -0.02788335271179676, 0.010967489331960678, -0.10584805905818939, 0.024275826290249825, 0.03947874531149864, 0.056563012301921844, -0.07883705943822861, 0.010247888043522835, -0.10380224138498306, 0.06733804941177368, -0.012859268113970757, 0.02713613770902157, -0.01850009337067604, 0.06002310663461685, -0.004639933351427317, -0.016507817432284355, -0.06384315341711044, -0.06714862585067749, -0.08768817037343979, 0.03961619362235069, -0.06171088665723801, 0.01275091152638197, 0.008464818820357323, 0.08640153706073761, -0.020100019872188568, -0.0160606000572443, -0.00946785882115364, -0.02661404013633728, 0.0007176687358878553, 0.007635154295712709, -0.02962777018547058, -0.013214053586125374, -0.017947321757674217, -0.025841575115919113, 0.02339271642267704, 0.004832685459405184, -0.07816234230995178, 0.04040759056806564, -0.03881083056330681, 0.01839718222618103, 0.05799765884876251, 0.02438719943165779, -0.012989717535674572, -0.0008077589445747435, -0.00980118103325367, -0.019387254491448402, -0.022506169974803925, 0.05373382568359375, 0.08052849024534225, -0.014102828688919544, 0.024535836651921272, -0.033668357878923416, 0.0013484865194186568, 0.014443052932620049, 0.04133090376853943, 0.051580704748630524, -0.030878208577632904, 0.02378780022263527, -0.036246515810489655, -0.10095153003931046, 0.011771256104111671, -0.04109178110957146, 0.03645884618163109, 0.005750507581979036, 0.008555985987186432, 0.006864324677735567, 0.018493035808205605, -0.008094594813883305, -0.07970204949378967, -0.03451055288314819, -0.05352803319692612, -0.027366403490304947, -0.04528177157044411, -0.04992692917585373, -0.03284607455134392, 0.039859261363744736, -0.037605296820402145, -0.006645746063441038, -0.0034805061295628548, 0.05402974784374237, 0.027978017926216125, 0.006520433351397514, -0.0008201102027669549, 0.017778629437088966, -0.03350308910012245, -0.058898188173770905, 0.009209606796503067, -0.01235232874751091, -0.027236472815275192, 0.027601715177297592, -0.009067647159099579, -0.008844845928251743, -0.01721380464732647, 0.004112185910344124, 0.0523916557431221, -0.06605994701385498, -0.02610285021364689, 0.009247826412320137, -0.09265969693660736, 0.08285526931285858, 0.0037475693970918655, -0.04140663146972656, -0.07519010454416275, 0.05898873135447502, 0.0005255370051600039, 0.02676963619887829, 0.04894251003861427, -0.02517036907374859, 0.04128517210483551, -0.006417567376047373, 0.016775034368038177, -0.03862873837351799, -0.042685095220804214, -0.015526960603892803, -0.00997056532651186, 0.03498917445540428, 0.003348373807966709, -0.023580987006425858, -0.03871018439531326, -0.06472382694482803, -0.03694252297282219, 0.0567840076982975, 0.08687329292297363, -0.012948567979037762, -0.04066405072808266, 0.028645314276218414, -0.022210385650396347, 0.021584713831543922, -0.02775050513446331, 0.05467236787080765, -0.04715268313884735, 0.03427387773990631, -0.0406658798456192, 0.03877091035246849, 0.032575614750385284, -0.052480533719062805, -0.023309597745537758, 0.07621783018112183, -0.06201484426856041, -0.0244486965239048, -0.055399976670742035, -0.018230527639389038, -0.0983768180012703, -0.01396830566227436, -0.053289808332920074, -0.030941560864448547, -0.0015395934460684657, 0.04521966725587845, 0.05993677303195, -0.025382328778505325, -0.03507126867771149, 0.005543060600757599, 0.04744776710867882, 0.01671767607331276, 0.06378329545259476, 0.015544300898909569, -0.04719996079802513, -0.029669128358364105, -0.035102639347314835, 0.0013009285321459174, 0.07967714220285416, -0.02638852782547474, 0.0380561426281929, 0.004609176889061928, -0.02879706397652626, -0.060468897223472595, 0.08131302893161774, 0.030751479789614677, 0.062130171805620193, -0.03985001891851425, -0.052075665444135666, -0.029090454801917076, -0.004749027546495199, -0.03151387348771095, -0.02562212012708187, 0.007756053935736418, 0.12412526458501816, -0.007961760275065899, 0.06350332498550415, 0.05543474853038788, -0.019709210842847824, 0.031525496393442154, 0.00900216493755579, 0.02307962253689766, -0.021297799423336983, 0.04626334458589554]', 'uploads/faces/user_18_angle3_20260731_133919_519730.jpg'),
(28, NULL, 18, '', '', '2026-07-31 08:09:22', '[0.10353118926286697, -0.009979219175875187, 0.010892150923609734, -0.007064029574394226, -0.04525284096598625, -0.040876470506191254, 0.027380166575312614, 0.021016260609030724, 0.028425700962543488, 0.009618640877306461, -0.03428404778242111, -0.01362755335867405, 0.05266057327389717, 0.06379298865795135, -0.015107247047126293, -0.015541219152510166, 0.009463964961469173, 0.008029695600271225, 0.0019451905973255634, -0.0006737944786436856, -0.10850127041339874, -0.06551185250282288, -0.023155156522989273, 0.038128893822431564, 0.05667468160390854, -0.06752261519432068, -0.004516016226261854, -0.018143435940146446, -0.024706091731786728, 0.06913315504789352, -0.008799022994935513, -0.0021374362986534834, 0.01097655389457941, 0.009551948867738247, -0.07035469263792038, -0.12516193091869354, 0.007172803394496441, -0.008440674282610416, 0.08851785212755203, 0.047096531838178635, -0.07367406785488129, 0.00338571285828948, -0.011439798399806023, 0.07169182598590851, -0.0007456286111846566, -0.0607653483748436, 0.023489000275731087, 0.01832512952387333, 0.028958264738321304, -0.02054385468363762, -0.015977561473846436, 0.03743729367852211, -0.03923402726650238, 0.037270426750183105, -0.038212236016988754, -0.012091060169041157, -0.03857411816716194, 0.07596299797296524, 0.0391504243016243, -0.023788537830114365, -0.059827715158462524, -0.002972603077068925, -0.014449270442128181, -0.05002037435770035, -0.024801695719361305, -0.020353224128484726, -0.09303074330091476, 0.048240844160318375, -0.09161734580993652, 0.041468847543001175, -0.01770365610718727, 0.009363263845443726, 0.0632815882563591, -0.051378581672906876, -0.12079522758722305, 0.020934389904141426, 0.0932939350605011, -0.021886054426431656, 0.03457121551036835, 0.024490447714924812, 0.006619411986321211, -0.026896322146058083, 0.039736658334732056, 0.03384390473365784, 0.05815006420016289, -0.024470394477248192, 0.005862654652446508, 0.04835114628076553, -0.04830191656947136, 0.04920945689082146, -0.038379959762096405, 0.048175688832998276, -0.0536600798368454, 0.012668875977396965, 0.03732847794890404, -0.07790135592222214, -0.061855874955654144, 0.02041218988597393, 0.011420909315347672, 0.04892751947045326, -0.00224299100227654, -0.018847167491912842, -0.0244038924574852, -0.01152760162949562, -0.014104197733104229, -0.08559460192918777, 0.07871169596910477, 0.0038827236276119947, 0.0026496753562241793, -0.005862706806510687, -0.008688297122716904, 0.021727096289396286, -0.10838980227708817, 0.01614268682897091, 0.020086897537112236, -0.07432708144187927, -0.04220092296600342, -0.025729207322001457, -0.08708459138870239, -0.0015003070002421737, -0.01402637455612421, 0.013569249771535397, 0.022186394780874252, -0.03229586407542229, -0.014284099452197552, -0.05209365859627724, 0.03508125618100166, 0.04686487466096878, -0.07398788630962372, -0.039280831813812256, 0.06433238089084625, 0.09227482229471207, 0.00989582110196352, -0.03134875372052193, -0.047228310257196426, -0.0271538645029068, -0.0003119453031104058, 0.0027966462075710297, 0.090662382543087, 0.04789822921156883, 0.014774654060602188, -0.008084084838628769, 0.001206958550028503, 0.04224749654531479, 0.01151266973465681, 0.06306098401546478, 0.042326487600803375, 0.030887244269251823, -0.009616067633032799, 0.06587960571050644, -0.01808208040893078, -0.024396197870373726, -0.05312864109873772, 0.03042667917907238, -0.015752028673887253, -0.03740980848670006, 0.09875662624835968, 0.04709898307919502, -0.006429596804082394, 0.004031376913189888, -0.034016720950603485, -0.004009310156106949, -0.04724786803126335, -0.026688283309340477, 0.025273729115724564, 0.020358221605420113, -0.011580352671444416, 0.022052930667996407, -0.09118077158927917, -0.05785408243536949, -0.017060106620192528, -0.054827023297548294, -0.03860950469970703, 0.004003678448498249, -0.08120813965797424, -0.025814108550548553, -0.01626702770590782, -0.023169543594121933, 0.026190755888819695, -0.021759234368801117, 0.013449308462440968, -0.0036684423685073853, 0.028614170849323273, -0.03485729545354843, 0.04832440987229347, 0.01891416124999523, -0.04310186952352524, 0.02590900845825672, 0.017214074730873108, 0.0141294589266181, -0.08488147705793381, -0.024630658328533173, 0.07611902803182602, 0.052759800106287, -0.009415913373231888, 0.0372588112950325, -0.018051782622933388, -0.013712402433156967, 0.040968261659145355, -0.029253628104925156, -0.08047165721654892, -0.00445521017536521, -0.042311638593673706, 0.021062808111310005, -0.06561368703842163, -0.04594733566045761, 0.017899420112371445, -0.04346148669719696, 0.0006826869212090969, 0.018766606226563454, -0.05924519896507263, -0.0444994755089283, 0.03285360336303711, 0.004224731586873531, -0.07089181989431381, -0.03331276401877403, 0.005487583111971617, 0.012571160681545734, -0.007476589176803827, -0.07844503968954086, 0.021925557404756546, 0.10707013309001923, 0.019370155408978462, 0.03476066142320633, 0.04209282249212265, -0.02840830199420452, 0.012212120927870274, -0.04007329046726227, -0.02301378920674324, -0.061692409217357635, 0.02189583145081997, 0.015138021670281887, -0.08072934299707413, 0.025393394753336906, -0.04682439565658569, -0.054714351892471313, -0.011594266630709171, 0.021381398662924767, 0.049296069890260696, 0.017071103677153587, -0.019485292956233025, -0.013735518790781498, -0.017556434497237206, 0.02208223193883896, -0.027673667296767235, 0.033097900450229645, 0.030882850289344788, -0.03960423544049263, 0.05295063927769661, 0.005730779375880957, -0.024808917194604874, -0.004482289310544729, 0.02194979041814804, -0.08362563699483871, -0.06549300998449326, -0.084517702460289, -0.08366744220256805, 0.02368239313364029, -0.0841718390583992, -0.0012065933551639318, -0.06295754015445709, 0.027216119691729546, 0.06748910993337631, 0.006908194161951542, 0.0515422597527504, -0.031442541629076004, 0.005568978376686573, 0.023987609893083572, -0.08313760161399841, 0.008342820219695568, 0.06167211756110191, -0.0882384181022644, -0.05070646107196808, 0.06825073808431625, -0.08991891145706177, 0.048114221543073654, 0.023606672883033752, -0.06201503798365593, 0.03393767774105072, -0.02090650238096714, 0.06029900908470154, 0.04577921703457832, -0.034618813544511795, 0.07822062075138092, 0.04481956735253334, 0.0414041243493557, 0.06608833372592926, -0.032335612922906876, 0.03202301636338234, -0.044490765780210495, 0.047813091427087784, 0.0187680721282959, -0.008775085210800171, -0.10919120162725449, 0.13384738564491272, 0.00348992133513093, 0.02645529806613922, 0.0977279469370842, 0.03153254836797714, -0.027354542165994644, -0.00443329056724906, -0.02524915151298046, -0.042087621986866, 0.0452156737446785, 0.008363340049982071, 0.047155916690826416, -0.024173200130462646, -0.001992465229704976, 0.013390831649303436, -0.014534089714288712, -0.010791478678584099, -0.0036892907228320837, -0.0048556034453213215, 0.026241913437843323, 0.023993344977498055, -0.008718201890587807, 0.04810449481010437, -0.06278225779533386, -0.04468023404479027, 0.012369133532047272, -0.017293717712163925, -0.033474989235401154, 0.0003530585381668061, -0.10091356933116913, 0.009027321822941303, -0.07750311493873596, -0.029563864693045616, -0.016123494133353233, -0.002468491205945611, 0.04960983991622925, -0.014776496216654778, 0.05639643594622612, -0.04307932406663895, 0.004364825319498777, -0.03005179576575756, 0.054885003715753555, -0.015752363950014114, -0.05594462901353836, -0.012213239446282387, 0.07273443043231964, -0.018778786063194275, 0.009368490427732468, -0.05794869735836983, -0.01264999434351921, -0.04809711501002312, 0.054571229964494705, -0.03916199505329132, -0.016140945255756378, 0.06207015737891197, -0.021847888827323914, -0.05408782511949539, 0.07776877284049988, 0.029111089184880257, -0.05076014623045921, -0.017147179692983627, 0.03296897932887077, 0.00960913673043251, -0.021082358434796333, 0.0032018881756812334, -0.010754982009530067, 0.0639835074543953, 0.08084392547607422, -0.07182474434375763, 0.04667781665921211, 0.05182024464011192, 0.009522617794573307, -0.02012225240468979, 0.006480853538960218, 0.008164974860846996, -0.012020129710435867, -0.04862101376056671, -0.0018677014159038663, -0.04777926951646805, 0.028929121792316437, 0.03628593310713768, -0.05281447619199753, 0.02127685956656933, -0.03644735738635063, 0.039136096835136414, 0.004680166486650705, -0.038061246275901794, 0.0689869374036789, 0.02839168906211853, -0.049832358956336975, -0.004805189091712236, -0.07916616648435593, 0.022666627541184425, 0.04607653617858887, -0.06016431003808975, 0.034657854586839676, 0.02712869644165039, -0.06265929341316223, 0.02609957754611969, -0.0016130777075886726, -0.07216638326644897, 0.00416816771030426, 0.012102164328098297, -0.06607521325349808, 0.0002893525524996221, 0.015134736895561218, 0.0022974146995693445, -0.01216427143663168, 0.0751914530992508, 0.014325710944831371, 0.07415174692869186, 0.011685483157634735, -0.031202612444758415, -0.021001143380999565, -0.009310394525527954, 0.031964391469955444, -0.04444773122668266, -0.014351585879921913, 0.015308981761336327, -0.061055988073349, -0.0014452234609052539, 0.004657750949263573, -0.02880316600203514, -0.012337110936641693, -0.02762599289417267, -0.02893347106873989, -0.02052406407892704, 0.0024048949126154184, -0.007141558453440666, 0.11306639015674591, -0.05918273329734802, 0.06655816733837128, 0.039180103689432144, -0.005393981002271175, -0.04277586191892624, 0.09238369017839432, 0.047749049961566925, -0.009533136151731014, 0.03194943815469742, -0.05855609476566315, 0.026948757469654083, 0.033702731132507324, 0.018501481041312218, 0.004351214971393347, -0.07805480062961578, 0.05204823613166809, -0.02506658062338829, -0.019535301253199577, -0.062300167977809906, -0.005053513217717409, -0.00970754586160183, 0.008585862815380096, -0.015495522879064083, 0.04915603622794151, 0.0626218393445015, 0.015166092664003372, 0.05951369181275368, 0.0286966972053051, -0.04109030216932297, 0.038428038358688354, -0.036299463361501694, 0.028995178639888763, -0.1351078599691391, 0.07922829687595367, -0.07682523131370544, -0.03650347515940666, 0.038561366498470306, -0.026542868465185165, -0.0027565855998545885, 0.015105945989489555, 0.03746165335178375, 0.016287561506032944, -0.030613034963607788, -0.012105338275432587, -0.009182772599160671, 0.04630948230624199, -0.010032165795564651, -0.026328423991799355, -0.023973505944013596, -0.023150380700826645, 0.023598939180374146, -0.022519951686263084, 0.0573263093829155, -0.016517091542482376, 0.008490059524774551, 0.0429006852209568, 0.04329549893736839, -0.020013220608234406, -0.04640546813607216, -0.012677331455051899, -0.0016962281661108136, 0.08662264794111252, 0.03499286249279976, -0.035369329154491425, -0.07535913586616516, 0.03722122311592102, 0.029855115339159966, -0.050151314586400986, 0.0063934545032680035, 0.0042807562276721, -0.02046845853328705, -0.07026781141757965, 0.04044782370328903, -0.044094931334257126, -0.010369351133704185, -0.06942924857139587, -0.02694569155573845, 0.02547101490199566, 0.10448349267244339, -0.015952348709106445, 0.01064200047403574, 0.016188714653253555, 0.00883775856345892, 0.014514115639030933, -0.0012703335378319025, -0.045373477041721344, 0.01634235866367817, -0.060439128428697586]', 'uploads/faces/user_18_angle4_20260731_133922_331818.jpg');

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

-- --------------------------------------------------------

--
-- Table structure for table `fee_categories`
--

CREATE TABLE `fee_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `tax` decimal(5,2) DEFAULT 0.00,
  `is_refundable` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_categories`
--

INSERT INTO `fee_categories` (`id`, `name`, `code`, `description`, `tax`, `is_refundable`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Tuition Fee', 'TUITION', 'Standard monthly/term tuition fees', 0.00, 0, 1, 1, '2026-08-02 08:07:29'),
(2, 'Admission Fee', 'ADMISSION', 'One-time admission charge', 0.00, 0, 1, 2, '2026-08-02 08:07:29'),
(3, 'Registration Fee', 'REGISTRATION', 'One-time registration/application fee', 0.00, 0, 1, 3, '2026-08-02 08:07:29'),
(4, 'Annual Fee', 'ANNUAL', 'Annual development and maintenance fee', 0.00, 0, 1, 4, '2026-08-02 08:07:29'),
(5, 'Transport Fee', 'TRANSPORT', 'Bus routing and transport fee', 0.00, 0, 1, 5, '2026-08-02 08:07:29'),
(6, 'Therapy Fee', 'THERAPY', 'Special therapy and assessment fee', 0.00, 0, 1, 6, '2026-08-02 08:07:29'),
(7, 'Activity Fee', 'ACTIVITY', 'Extracurricular activity fee', 0.00, 0, 1, 7, '2026-08-02 08:07:29'),
(8, 'Exam Fee', 'EXAM', 'Evaluation and examination fee', 0.00, 0, 1, 8, '2026-08-02 08:07:29'),
(9, 'Books & Stationery', 'BOOKS', 'Curriculum textbooks and material costs', 0.00, 0, 1, 9, '2026-08-02 08:07:29'),
(10, 'Uniform Fee', 'UNIFORM', 'School uniform sets', 0.00, 0, 1, 10, '2026-08-02 08:07:29'),
(11, 'Medical Fee', 'MEDICAL', 'On-campus first-aid and medical checkups', 0.00, 0, 1, 11, '2026-08-02 08:07:29'),
(12, 'Miscellaneous', 'MISC', 'Other miscellaneous charges', 0.00, 0, 1, 12, '2026-08-02 08:07:29');

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
  `discount_type` enum('NONE','PERCENTAGE','FIXED') DEFAULT 'NONE',
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `discount_reason` varchar(255) DEFAULT NULL,
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

INSERT INTO `fee_invoices` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `invoice_number`, `title`, `description`, `amount`, `discount_type`, `discount_value`, `discount_amount`, `discount_reason`, `due_date`, `status`, `paid_amount`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 3, 'INV-2026-7C3075', 'tution fee', '', 15000.00, 'NONE', 0.00, 0.00, NULL, '2026-08-03', 'paid', 15000.00, '2026-08-02 15:09:20', '2026-08-02 07:45:47', '2026-08-02 09:39:20'),
(2, 1, 1, 1, 3, '', 'teram 1 fees', '', 1500.00, 'NONE', 0.00, 0.00, NULL, '2026-08-03', 'unpaid', 0.00, NULL, '2026-08-02 09:01:52', '2026-08-02 09:36:19');

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
(1, 1, 1, 1, 1, 15000.00, 'Cash', 'Manual-815E47D6', '2026-08-02 09:39:20', '2026-08-02 09:39:20', 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `main_group_id` int(10) UNSIGNED NOT NULL,
  `installment_type` varchar(50) NOT NULL DEFAULT 'one_time',
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `late_fee_policy_id` int(10) UNSIGNED DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `version` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `academic_year_id`, `main_group_id`, `installment_type`, `class_id`, `name`, `late_fee_policy_id`, `effective_from`, `effective_to`, `is_active`, `version`, `created_at`) VALUES
(1, 3, 3, 'one_time', NULL, 'test', NULL, '0000-00-00', '0000-00-00', 1, 1, '2026-08-02 08:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structure_items`
--

CREATE TABLE `fee_structure_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `fee_structure_id` int(10) UNSIGNED NOT NULL,
  `fee_category_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 1,
  `due_date` date DEFAULT NULL,
  `late_fee_policy_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structure_items`
--

INSERT INTO `fee_structure_items` (`id`, `fee_structure_id`, `fee_category_id`, `amount`, `is_mandatory`, `due_date`, `late_fee_policy_id`) VALUES
(1, 1, 1, 1500.00, 1, '2026-08-02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `field_mappings`
--

CREATE TABLE `field_mappings` (
  `id` int(10) UNSIGNED NOT NULL,
  `certificate_type_id` int(10) UNSIGNED NOT NULL,
  `field_key` varchar(80) NOT NULL,
  `label` text NOT NULL,
  `options_json` text DEFAULT NULL,
  `x_pos` int(11) NOT NULL DEFAULT 0,
  `y_pos` int(11) NOT NULL DEFAULT 0,
  `font_size` int(11) NOT NULL DEFAULT 32,
  `align` enum('left','center') NOT NULL DEFAULT 'left',
  `color_hex` char(7) NOT NULL DEFAULT '#000000',
  `max_width` int(11) NOT NULL DEFAULT 900,
  `line_height` int(11) NOT NULL DEFAULT 42,
  `underline` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `folder_staff`
--

CREATE TABLE `folder_staff` (
  `folder_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `generated_certificates`
--

CREATE TABLE `generated_certificates` (
  `id` int(10) UNSIGNED NOT NULL,
  `participant_id` int(10) UNSIGNED NOT NULL,
  `jpg_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `download_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 15, 'test', 'Mother', NULL, 'test@gmail.com', '9427961426', NULL, NULL, '1234-5878-7984', NULL, NULL, NULL, NULL, NULL, 1, 7, 7, '2026-07-29 13:45:13', '2026-08-02 07:27:50', NULL),
(2, 1, 14, 'adaasd', 'Father', NULL, 'parentnew@gmail.com', '9427996142', NULL, NULL, '1654-6546-5464', NULL, NULL, NULL, NULL, NULL, 1, 7, 7, '2026-07-29 13:55:53', '2026-08-02 07:27:50', NULL),
(3, 1, 16, 'aksksas', 'Mother', NULL, 'test22@gmail.com', '9828504654', NULL, NULL, '1234-5878-7984', NULL, NULL, NULL, NULL, NULL, 1, 7, 7, '2026-07-30 08:14:53', '2026-08-02 07:25:46', NULL),
(4, 1, 17, 'test one', 'Mother', NULL, 'up@gmail.com', '9313457713', NULL, NULL, '5465-4652-1654', NULL, NULL, NULL, NULL, NULL, 1, 7, NULL, '2026-07-30 08:23:28', '2026-07-30 08:23:29', NULL),
(5, 1, NULL, 'test gurdian', 'Guardian', NULL, 'gurdian@gmail.com', '9425659787', NULL, NULL, '9646-9798-4654', NULL, NULL, NULL, NULL, NULL, 1, 7, 7, '2026-08-01 08:08:25', '2026-08-02 07:25:46', NULL),
(6, 1, 19, 'janhe', 'Father', NULL, 'jane@gmail.com', '9431346541', NULL, NULL, '6541-2135-4654', NULL, NULL, NULL, NULL, NULL, 1, 7, 7, '2026-08-01 10:34:15', '2026-08-01 10:51:56', NULL),
(7, 1, 20, 'asdasd', 'Father', NULL, 'asdasd@gmail.com', '8543216576', NULL, NULL, '7544-5516-5465', NULL, NULL, NULL, NULL, NULL, 1, 7, NULL, '2026-08-03 12:09:28', '2026-08-03 12:09:29', NULL);

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
(1, 4, 1, 1, '2026-08-02 07:27:50'),
(2, 4, 0, 1, '2026-08-02 07:27:50'),
(3, 3, 1, 1, '2026-08-02 07:25:46'),
(4, 2, 1, 1, '2026-07-30 08:23:29'),
(5, 3, 0, 1, '2026-08-02 07:25:46'),
(6, 5, 1, 1, '2026-08-01 10:51:56'),
(7, 6, 1, 1, '2026-08-03 12:09:29');

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

-- --------------------------------------------------------

--
-- Table structure for table `late_fee_policies`
--

CREATE TABLE `late_fee_policies` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `rule_type` enum('fixed_day','fixed_week','slab','percentage','one_time') NOT NULL,
  `value` decimal(10,2) DEFAULT 0.00,
  `grace_days` int(11) DEFAULT 0,
  `slab_config_json` longtext DEFAULT NULL,
  `max_cap` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `late_fee_policies`
--

INSERT INTO `late_fee_policies` (`id`, `name`, `rule_type`, `value`, `grace_days`, `slab_config_json`, `max_cap`, `created_at`) VALUES
(1, 'test', 'fixed_day', 100.00, 0, NULL, 0.00, '2026-08-02 08:37:57');

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `medical_certificate` varchar(500) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `tenant_id`, `school_id`, `branch_id`, `student_id`, `start_date`, `end_date`, `leave_type`, `reason`, `medical_certificate`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 4, '2026-08-03', '2026-08-05', 'Personal', 'family event', '4_1785579502_6a6dc7ee8888b.pdf', 'Approved', '2026-08-01 10:18:22', '2026-08-01 10:25:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` decimal(3,1) NOT NULL DEFAULT 1.0,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(50) NOT NULL,
  `code` varchar(20) NOT NULL,
  `annual_allowance` int(11) DEFAULT 12,
  `is_paid` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `main_groups`
--

CREATE TABLE `main_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `age_range` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `color` varchar(20) NOT NULL DEFAULT '#6366f1',
  `icon` varchar(50) NOT NULL DEFAULT '?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `main_groups`
--

INSERT INTO `main_groups` (`id`, `tenant_id`, `name`, `description`, `age_range`, `is_active`, `color`, `icon`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pre Primary', 'Pre-Primary core development and foundation', '3-6', 1, '#3b82f6', '🔵', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(2, 1, 'Primary', 'Primary schooling subjects and skills', '6-10', 1, '#8b5cf6', '🟣', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(3, 1, 'Functional', 'Functional academic and life-readiness program', '10-18', 1, '#10b981', '🟢', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(4, 1, 'NIOS OBE', 'National Institute of Open Schooling Open Basic Education program', '10-18', 1, '#f97316', '🟠', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(5, 1, 'Pearl Vocational', 'Pearl Vocational skills training programs', '18+', 1, '#78350f', '🟤', '2026-08-02 06:02:42', '2026-08-02 06:02:42'),
(6, 1, 'Workshop', 'Sheltered workshop production and employment training', '18+', 1, '#1e293b', '⚫', '2026-08-02 06:02:42', '2026-08-02 06:02:42');

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
(1, '000_create_rate_limits_table', 1, '2026-07-28 11:32:07'),
(2, '001_create_plans_table', 1, '2026-07-28 11:32:07'),
(3, '002_create_tenants_table', 1, '2026-07-28 11:32:07'),
(4, '003_create_schools_table', 1, '2026-07-28 11:32:07'),
(5, '004_create_branches_table', 1, '2026-07-28 11:32:07'),
(6, '005_create_users_table', 1, '2026-07-28 11:32:07'),
(7, '006_create_roles_table', 1, '2026-07-28 11:32:07'),
(8, '007_create_permissions_table', 1, '2026-07-28 11:32:07'),
(9, '008_create_role_permissions_table', 1, '2026-07-28 11:32:07'),
(10, '009_create_user_roles_table', 1, '2026-07-28 11:32:07'),
(11, '010_create_activity_logs_table', 1, '2026-07-28 11:32:07'),
(12, '011_create_sessions_table', 1, '2026-07-28 11:32:07'),
(13, '012_create_password_resets_table', 1, '2026-07-28 11:32:07'),
(14, '013_create_otp_codes_table', 1, '2026-07-28 11:32:07'),
(15, '014_create_subscriptions_table', 1, '2026-07-28 11:32:07'),
(16, '015_create_students_table', 1, '2026-07-28 11:32:07'),
(17, '016_create_student_medical_table', 1, '2026-07-28 11:32:07'),
(18, '017_create_emergency_contacts_table', 1, '2026-07-28 11:32:07'),
(19, '018_create_student_documents_table', 1, '2026-07-28 11:32:07'),
(20, '019_create_student_timeline_table', 1, '2026-07-28 11:32:07'),
(21, '020_create_guardians_table', 1, '2026-07-28 11:32:07'),
(22, '021_create_parent_portal_tables', 1, '2026-07-28 11:32:07'),
(23, '022_create_leave_applications_table', 1, '2026-07-28 11:32:07'),
(24, '20260802_AcademicRestructureSchema', 99, '2026-08-02 02:32:43'),
(25, '20260802_FeesModuleRedesignSchema', 100, '2026-08-02 04:37:30');

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

-- --------------------------------------------------------

--
-- Table structure for table `parent_attendance_notices`
--

CREATE TABLE `parent_attendance_notices` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `guardian_id` int(10) UNSIGNED NOT NULL,
  `notice_date` date NOT NULL,
  `notice_type` enum('absent','late','self_drop','other') DEFAULT 'absent',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_documents`
--

CREATE TABLE `parent_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(191) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
  `expiry_date` date DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parent_student`
--

CREATE TABLE `parent_student` (
  `parent_id` varchar(36) NOT NULL,
  `student_id` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(10) UNSIGNED NOT NULL,
  `conference_id` int(10) UNSIGNED NOT NULL,
  `certificate_type_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `institute` varchar(255) DEFAULT NULL,
  `title` text DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `extra_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_json`)),
  `verify_code` varchar(40) NOT NULL,
  `status` enum('pending','generated','emailed','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `payroll_items`
--

CREATE TABLE `payroll_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_run_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `working_days` int(11) DEFAULT 26,
  `present_days` int(11) DEFAULT 26,
  `late_days` int(11) DEFAULT 0,
  `half_days` int(11) DEFAULT 0,
  `absent_days` int(11) DEFAULT 0,
  `gross_salary` decimal(10,2) DEFAULT 0.00,
  `late_deduction` decimal(10,2) DEFAULT 0.00,
  `absent_deduction` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_items`
--

INSERT INTO `payroll_items` (`id`, `payroll_run_id`, `employee_id`, `working_days`, `present_days`, `late_days`, `half_days`, `absent_days`, `gross_salary`, `late_deduction`, `absent_deduction`, `net_salary`) VALUES
(5, 3, 1, 26, 0, 0, 0, 0, 35000.00, 0.00, 35000.00, 0.00),
(6, 3, 2, 26, 0, 0, 0, 0, 35000.00, 0.00, 35000.00, 0.00),
(7, 3, 5, 26, 2, 0, 0, 0, 10000.00, 0.00, 9677.42, 322.58),
(8, 3, 6, 26, 0, 0, 0, 0, 15000.00, 0.00, 15000.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_runs`
--

CREATE TABLE `payroll_runs` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `month_year` varchar(20) NOT NULL,
  `total_gross` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_net` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','approved','locked') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_runs`
--

INSERT INTO `payroll_runs` (`id`, `tenant_id`, `month_year`, `total_gross`, `total_deductions`, `total_net`, `status`, `created_at`) VALUES
(3, 1, 'July 2026', 95000.00, 94677.42, 322.58, 'approved', '2026-07-29 12:02:16');

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
(11, 'login:ef7b5ff194386ea6ed74d51ce440874ec3a40dce', 1, '2026-07-29 19:20:05', '2026-07-29 13:35:05', '2026-07-29 13:35:05'),
(14, 'login:317fbb92167c687ce33b74bc9c3d7fae4ad1acfb', 2, '2026-07-30 13:55:37', '2026-07-30 08:10:37', '2026-07-30 08:10:47'),
(20, 'route:0d12ca599dc0316beec6436bb3beb04e84fbe3e2', 1, '2026-07-30 16:37:48', '2026-07-30 11:06:48', '2026-07-30 11:06:48'),
(36, 'route:2da4ce2ae9ec891e4540f8ad844e8af7ee780059', 1, '2026-07-31 01:40:56', '2026-07-30 20:09:56', '2026-07-30 20:09:56'),
(38, 'route:2842adb58e5273960d8d49b229488b8cc07fda5c', 1, '2026-07-31 13:20:10', '2026-07-31 07:49:10', '2026-07-31 07:49:10'),
(57, 'route:0b7ad0d8d99d8f560446d6e98e02a60437403a1e', 1, '2026-08-01 13:03:23', '2026-08-01 07:32:23', '2026-08-01 07:32:23'),
(67, 'route:a4aecc7e1446d443b230f9b8064a8a1f78ea74c9', 1, '2026-08-01 19:58:00', '2026-08-01 14:27:00', '2026-08-01 14:27:00'),
(74, 'route:363baea9cba210afac6d7a556fca596e30c46333', 4, '2026-08-04 12:24:13', '2026-08-04 06:53:13', '2026-08-04 06:54:06');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_staff`
--

CREATE TABLE `resource_staff` (
  `resource_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, NULL, 'School Admin', 'school_admin', 'School level administrator', 1, 1, 2, NULL, NULL, '2026-06-16 03:13:12', '2026-07-28 06:52:43', NULL),
(3, NULL, 'Manager', 'manager', 'Branch manager', 1, 1, 3, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(4, NULL, 'Teacher', 'teacher', 'Classroom teacher', 1, 1, 4, NULL, NULL, '2026-06-16 03:13:12', '2026-07-29 08:28:14', NULL),
(5, NULL, 'Therapist', 'therapist', 'Therapy specialist', 1, 1, 5, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(6, NULL, 'Staff', 'staff', 'General staff member', 1, 1, 6, NULL, NULL, '2026-06-16 03:13:12', NULL, NULL),
(7, NULL, 'Driver', 'driver', 'Transport driver', 1, 1, 7, NULL, NULL, '2026-06-16 03:13:12', '2026-06-22 06:35:28', NULL),
(8, NULL, 'Parent', 'parent', 'Parent / guardian', 1, 1, 8, NULL, NULL, '2026-06-16 03:13:12', '2026-07-29 08:28:51', NULL),
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
(4, 1, '2026-07-29 08:28:14'),
(4, 2, '2026-07-29 08:28:14'),
(4, 8, '2026-07-29 08:28:14'),
(4, 27, '2026-07-29 08:28:14'),
(4, 35, '2026-07-29 08:28:14'),
(4, 41, '2026-07-29 08:28:14'),
(4, 44, '2026-07-29 08:28:14'),
(4, 45, '2026-07-29 08:28:14'),
(4, 46, '2026-07-29 08:28:14'),
(4, 47, '2026-07-29 08:28:14'),
(4, 48, '2026-07-29 08:28:14'),
(4, 49, '2026-07-29 08:28:14'),
(4, 50, '2026-07-29 08:28:14'),
(4, 51, '2026-07-29 08:28:14'),
(4, 52, '2026-07-29 08:28:14'),
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
(8, 1, '2026-07-29 08:28:51'),
(8, 2, '2026-07-29 08:28:51'),
(8, 42, '2026-07-29 08:28:51'),
(8, 47, '2026-07-29 08:28:51');

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

-- --------------------------------------------------------

--
-- Table structure for table `salary_structures`
--

CREATE TABLE `salary_structures` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `basic` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `medical_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `transport_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `special_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pf_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `esi_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 'Greenwood International School', 'GREENWOOD', '', '', '', '', '', '', NULL, NULL, 'special_needs', 1, NULL, NULL, NULL, '2026-06-16 03:13:12', '2026-08-03 07:13:51', NULL);

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
(1, 'campus_lat', '23.11871480381384'),
(2, 'campus_lng', '72.56136559295895');

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

-- --------------------------------------------------------

--
-- Table structure for table `shift_templates`
--

CREATE TABLE `shift_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `working_days_per_month` int(11) DEFAULT 0,
  `working_days_json` text DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `name` varchar(100) NOT NULL,
  `start_time` time NOT NULL DEFAULT '09:00:00',
  `end_time` time NOT NULL DEFAULT '17:00:00',
  `grace_minutes` int(11) NOT NULL DEFAULT 15,
  `late_after` time NOT NULL DEFAULT '09:16:00',
  `half_day_after` time NOT NULL DEFAULT '12:00:00',
  `absent_after` time NOT NULL DEFAULT '14:00:00',
  `min_hours` decimal(4,2) DEFAULT 8.00,
  `max_hours` decimal(4,2) DEFAULT 10.00,
  `is_overtime_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `late_limit_count` int(11) DEFAULT 3,
  `late_deduction_percent` decimal(5,2) DEFAULT 10.00,
  `half_day_deduction_percent` decimal(5,2) DEFAULT 50.00,
  `lec_grace_minutes` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shift_templates`
--

INSERT INTO `shift_templates` (`id`, `working_days_per_month`, `working_days_json`, `tenant_id`, `name`, `start_time`, `end_time`, `grace_minutes`, `late_after`, `half_day_after`, `absent_after`, `min_hours`, `max_hours`, `is_overtime_enabled`, `created_at`, `late_limit_count`, `late_deduction_percent`, `half_day_deduction_percent`, `lec_grace_minutes`) VALUES
(1, 0, '{\"01\":20,\"02\":12,\"03\":0,\"04\":0,\"05\":0,\"06\":0,\"07\":31,\"08\":0,\"09\":0,\"10\":0,\"11\":0,\"12\":0}', 1, 'Default Staff Shift Policy Template', '09:00:00', '17:00:00', 15, '09:16:00', '12:00:00', '14:00:00', 8.00, 10.00, 1, '2026-07-29 09:11:21', 3, 10.00, 50.00, 5);

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

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance_logs`
--

CREATE TABLE `staff_attendance_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `clock_in` time DEFAULT NULL,
  `clock_out` time DEFAULT NULL,
  `working_hours` decimal(4,2) DEFAULT 0.00,
  `late_minutes` int(11) DEFAULT 0,
  `status` enum('present','late','half_day','absent','on_leave','holiday','wfh') DEFAULT 'present',
  `device_type` varchar(50) DEFAULT 'web_kiosk',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_documents`
--

CREATE TABLE `staff_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(191) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('pending','verified','rejected','expired') NOT NULL DEFAULT 'pending',
  `expiry_date` date DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `roll_number` varchar(50) DEFAULT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `main_group_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `uuid`, `tenant_id`, `school_id`, `branch_id`, `admission_number`, `roll_number`, `gr_number`, `first_name`, `middle_name`, `last_name`, `gender`, `dob`, `photo`, `blood_group`, `nationality`, `religion`, `mother_tongue`, `aadhar_number`, `address`, `city`, `state`, `pincode`, `admission_status`, `admission_date`, `enrolled_date`, `withdrawal_date`, `withdrawal_reason`, `class`, `section`, `academic_year`, `is_active`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`, `class_id`, `main_group_id`) VALUES
(2, '982f43da-54e2-44cb-8329-6781c09daedc', 1, 1, 1, 'ADM-2026-1-2354', NULL, 'GR-1-51825', 'Het', '', 'Shah', 'male', '2006-02-11', NULL, 'Unknown', 'Indian', NULL, 'adasd', '1354-6464-6465', 'A 403 prakruti appt suvidha sanjivani road\r\nAhmedabad Gujarat', NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class B', '', NULL, 1, '', 7, 7, '2026-07-28 12:26:35', '2026-08-03 12:07:28', NULL, 2, NULL),
(3, '5cc33a67-6ebb-4803-b570-92bf806a95f6', 1, 1, 1, 'ADM-2026-1-0394', '01', 'GR-1-98450', 'akshat', '', 'Shah', 'male', '2005-02-25', NULL, 'Unknown', 'Indian', NULL, 'asdasd', '1354-6464-6465', 'A 403 prakruti appt suvidha sanjivani road\r\nAhmedabad Gujarat', NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', 7, 7, '2026-07-29 13:31:39', '2026-08-02 07:25:46', NULL, 1, 3),
(4, 'a5617e34-8ff5-46ae-bd8b-9a72faf880d1', 1, 1, 1, 'ADM-2026-1-6643', '02', 'GR-1-94934', 'Het', '', 'Shah', 'male', '2004-02-05', NULL, 'Unknown', 'Indian', NULL, 'adasd', '4564-6549-8787', 'A 403 prakruti appt suvidha sanjivani road\r\nAhmedabad Gujarat', NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'no', 7, 7, '2026-07-29 13:55:53', '2026-08-02 07:27:50', NULL, 1, 3),
(5, '8baca934-b258-40fd-a41c-001289292431', 1, 1, 1, 'ADM-2026-1-8533', NULL, 'GR-1-55334', 'john', '', 'ssa', 'male', '2006-02-11', NULL, 'Unknown', 'Indian', NULL, 'guj', '6546-5465-5465', 'prkaturi', NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'Class A', '', NULL, 1, '', 7, 7, '2026-08-01 10:34:15', '2026-08-02 06:02:43', NULL, 1, 3),
(6, '3c74cf32-d5d5-464c-a059-bd05d2910fae', 1, 1, 1, 'ADM-2026-1-5910', '05', 'GR-1-70229', 'taad', 'asda', 'asdad', 'male', '2019-02-05', NULL, 'Unknown', 'Indian', NULL, 'asd', '1354-6464-6465', 'asd', NULL, NULL, NULL, 'enrolled', NULL, NULL, NULL, NULL, 'functional a', '', NULL, 1, NULL, 7, NULL, '2026-08-03 12:09:28', '2026-08-03 12:12:38', NULL, 3, NULL);

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
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'verified',
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
(1, 3, '', 'Admission Form', 'media_1785478783276.webp', '3776d104a6600c8cdda2e1c8d564a4fd.webp', 'image/webp', 20236, 'verified', NULL, NULL, NULL, NULL, '2026-08-01 04:20:04', '2026-08-01 10:55:41', NULL),
(2, 4, 'aadhar', 'aadhar', 'Universal CRM & Customer Management System.pdf', '828485fe3be87458f2d089e0f3da2e4c.pdf', 'application/pdf', 176327, 'verified', NULL, NULL, NULL, 7, '2026-08-01 04:20:56', '2026-08-01 10:55:41', NULL),
(3, 5, 'birth_certificate', '', 'AI_Consultant_Engagement_Brief.pdf', 'a366ba8af39d17139b5814d7561bcc90.pdf', 'application/pdf', 261817, 'verified', NULL, NULL, NULL, 7, '2026-08-01 10:42:34', '2026-08-01 10:55:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_ledgers`
--

CREATE TABLE `student_ledgers` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `entry_type` enum('opening_balance','invoice','payment','late_fee','discount','adjustment','refund') NOT NULL,
  `debit` decimal(10,2) DEFAULT 0.00,
  `credit` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_ledgers`
--

INSERT INTO `student_ledgers` (`id`, `student_id`, `entry_type`, `debit`, `credit`, `balance`, `reference_id`, `description`, `created_at`) VALUES
(1, 3, 'invoice', 15000.00, 0.00, 15000.00, 1, 'Invoice: tution fee', '2026-08-02 07:45:47'),
(2, 3, 'invoice', 1500.00, 0.00, 16500.00, 2, 'Batch Generated Invoice: teram 1 fees', '2026-08-02 09:01:52'),
(3, 3, 'payment', 0.00, 15000.00, 1500.00, 1, 'Payment: Received 15000 via Cash for \'tution fee\'', '2026-08-02 09:39:20');

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

-- --------------------------------------------------------

--
-- Table structure for table `student_subject_enrollments`
--

CREATE TABLE `student_subject_enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','completed','dropped') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_subject_enrollments`
--

INSERT INTO `student_subject_enrollments` (`id`, `student_id`, `subject_id`, `academic_year_id`, `term_id`, `status`, `created_at`) VALUES
(1, 2, 12, 1, NULL, 'active', '2026-07-28 12:27:12'),
(2, 2, 7, 1, NULL, 'active', '2026-07-28 12:27:12'),
(3, 2, 11, 1, NULL, 'active', '2026-07-28 12:27:12'),
(4, 5, 12, 1, NULL, 'active', '2026-08-01 10:42:15'),
(5, 5, 7, 1, NULL, 'active', '2026-08-01 10:42:15'),
(6, 5, 11, 1, NULL, 'active', '2026-08-01 10:42:15'),
(7, 5, 8, 1, NULL, 'active', '2026-08-01 10:42:15');

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
(1, 2, 'admission', 'Application submitted', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 7, 'System Administrator', '2026-07-28 17:56:35', '2026-07-28 12:26:35'),
(2, 3, 'admission', 'Application submitted', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 7, 'System Administrator', '2026-07-29 19:01:39', '2026-07-29 13:31:39'),
(3, 4, 'admission', 'Application submitted', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 7, 'System Administrator', '2026-07-29 19:25:53', '2026-07-29 13:55:53'),
(4, 4, 'attendance_declaration', 'Parent declared attendance for 2026-07-30 as: Present', NULL, '{\"status\":\"present\",\"date\":\"2026-07-30\"}', 'calendar', 'green', 14, 'adaasd', '2026-07-29 19:27:30', '2026-07-29 13:57:30'),
(5, 4, 'attendance_declaration', 'Parent declared attendance for 2026-07-31 as: Present', NULL, '{\"status\":\"present\",\"date\":\"2026-07-31\"}', 'calendar', 'green', 15, 'test', '2026-07-30 14:43:03', '2026-07-30 09:13:03'),
(6, 4, 'attendance_declaration', 'Parent declared attendance for 2026-07-31 as: Present', NULL, '{\"status\":\"present\",\"date\":\"2026-07-31\"}', 'calendar', 'green', 15, 'test', '2026-07-30 15:19:14', '2026-07-30 09:49:14'),
(7, 4, 'transport', 'Transport Assigned', 'Assigned to driver \'test driver\'. Pickup: A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat at 08:00 AM.', NULL, 'truck', 'indigo', NULL, 'System Administrator', '2026-07-30 16:14:00', '2026-07-30 10:44:00'),
(8, 4, 'transport', 'Transport Assigned', 'Assigned to driver \'test driver\'. Pickup: A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat at 08:00 AM.', NULL, 'truck', 'indigo', NULL, 'System Administrator', '2026-07-30 17:16:31', '2026-07-30 11:46:31'),
(9, 3, 'transport', 'Transport Assigned', 'Assigned to driver \'test driver\'. Pickup: A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat at 08:00 AM.', NULL, 'truck', 'indigo', NULL, 'System Administrator', '2026-07-30 17:17:18', '2026-07-30 11:47:18'),
(10, 3, 'attendance_declaration', 'Parent declared attendance for 2026-08-01 as: Present', NULL, '{\"status\":\"present\",\"date\":\"2026-08-01\"}', 'calendar', 'green', 16, 'aksksas', '2026-07-31 00:18:05', '2026-07-30 18:48:05'),
(11, 4, 'attendance_declaration', 'Parent declared attendance for 2026-08-01 as: Present', NULL, '{\"status\":\"present\",\"date\":\"2026-08-01\"}', 'calendar', 'green', 15, 'test', '2026-07-31 14:57:28', '2026-07-31 09:27:28'),
(12, 2, 'attendance_declaration', 'Parent declared attendance for 2026-08-01 as: Present', NULL, '{\"status\":\"present\",\"date\":\"2026-08-01\"}', 'calendar', 'green', 17, 'test one', '2026-07-31 15:01:42', '2026-07-31 09:31:42'),
(13, 2, 'transport', 'Transport Assigned', 'Assigned to driver \'test driver\'. Pickup: A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat at 08:00 AM.', NULL, 'truck', 'indigo', NULL, 'System Administrator', '2026-07-31 15:04:25', '2026-07-31 09:34:25'),
(14, 4, 'document_upload', 'Document uploaded: aadhar', NULL, '[]', 'document', 'green', 7, 'System Administrator', '2026-08-01 09:50:56', '2026-08-01 04:20:56'),
(15, 5, 'admission', 'Application submitted', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 7, 'System Administrator', '2026-08-01 16:04:15', '2026-08-01 10:34:15'),
(16, 5, 'document_upload', 'Document uploaded: ', NULL, '[]', 'document', 'green', 7, 'System Administrator', '2026-08-01 16:12:34', '2026-08-01 10:42:34'),
(17, 6, 'admission', 'Application submitted', NULL, '{\"status\":\"applied\"}', 'user-plus', 'purple', 7, 'System Administrator', '2026-08-03 17:39:29', '2026-08-03 12:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `student_transport`
--

CREATE TABLE `student_transport` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED DEFAULT NULL,
  `route_id` int(10) UNSIGNED DEFAULT NULL,
  `pickup_point` varchar(255) DEFAULT NULL,
  `pickup_lat` decimal(10,8) DEFAULT NULL,
  `pickup_lng` decimal(10,8) DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_transport`
--

INSERT INTO `student_transport` (`id`, `student_id`, `driver_id`, `route_id`, `pickup_point`, `pickup_lat`, `pickup_lng`, `pickup_time`, `created_at`) VALUES
(2, 4, 7, NULL, 'A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat', 23.01559600, 72.55755700, '08:00:00', '2026-07-30 11:46:31'),
(3, 3, 7, NULL, 'A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat', 23.00830361, 72.54038732, '08:00:00', '2026-07-30 11:47:18'),
(4, 2, 7, NULL, 'A 403 prakruti appt suvidha sanjivani roadAhmedabad Gujarat', 23.08832243, 72.54926646, '08:00:00', '2026-07-31 09:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('academic','therapy','life_skills','cognitive') DEFAULT 'academic',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(50) NOT NULL DEFAULT 'Academic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `tenant_id`, `school_id`, `code`, `name`, `type`, `created_at`, `category`) VALUES
(7, 1, 0, 'SUB-102', 'Sensory Integration & Occupational Skills', 'therapy', '2026-07-28 11:33:07', 'Co-Curricular'),
(8, 1, 0, 'SUB-103', 'Visual Arts &amp; Creative Expression', 'academic', '2026-07-28 11:33:07', 'Academic'),
(11, 1, 0, '001', 'test', 'therapy', '2026-07-28 12:12:37', 'Co-Curricular'),
(12, 1, 0, 'AD', 'adsad', 'academic', '2026-07-28 12:12:46', 'Academic'),
(13, 1, 1, 'ENG01', 'English', 'academic', '2026-08-02 06:02:42', 'Academic'),
(14, 1, 1, 'MTH01', 'Maths', 'academic', '2026-08-02 06:02:42', 'Academic'),
(15, 1, 1, 'EVS01', 'EVS', 'academic', '2026-08-02 06:02:42', 'Academic'),
(16, 1, 1, 'COMP01', 'Computer', 'academic', '2026-08-02 06:02:42', 'Academic'),
(17, 1, 1, 'ADL01', 'ADL', 'academic', '2026-08-02 06:02:42', 'Life Skills'),
(18, 1, 1, 'MONEY01', 'Money Skills', 'academic', '2026-08-02 06:02:42', 'Life Skills'),
(19, 1, 1, 'MOT01', 'Motor Skills', 'academic', '2026-08-02 06:02:42', 'Life Skills'),
(20, 1, 1, 'PERF01', 'Performance Readiness', 'academic', '2026-08-02 06:02:42', 'Life Skills'),
(21, 1, 1, 'DANCE01', 'Dance', 'academic', '2026-08-02 06:02:42', 'Co-Curricular'),
(22, 1, 1, 'MUSIC01', 'Music', 'academic', '2026-08-02 06:02:42', 'Co-Curricular'),
(23, 1, 1, 'YOGA01', 'Yoga', 'academic', '2026-08-02 06:02:42', 'Co-Curricular'),
(24, 1, 1, 'VOCMONEY01', 'Money Transaction', 'academic', '2026-08-02 06:02:42', 'Vocational'),
(25, 1, 1, 'CUST01', 'Customer Interaction', 'academic', '2026-08-02 06:02:42', 'Vocational'),
(26, 1, 1, 'MOCKM01', 'Mock Market', 'academic', '2026-08-02 06:02:42', 'Vocational'),
(27, 1, 1, 'DATAE01', 'Data Entry', 'academic', '2026-08-02 06:02:42', 'Vocational'),
(28, 1, 1, 'COOK01', 'No Gas Cooking', 'academic', '2026-08-02 06:02:42', 'Vocational');

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
(1, 'whatsapp_api_key', 'wa_live_f16d82ecc5d4318798f0e8ef02503fa22dfd93d544119511'),
(2, 'whatsapp_enabled', '1'),
(3, 'payment_razorpay_key', ''),
(4, 'payment_stripe_key', ''),
(5, 'portal_hide_exams', '1'),
(6, 'portal_allow_payments', '0');

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
(2, 1, 1, 1, 8, '2026-07-29', '2026-07-29 14:52:01', NULL, NULL, NULL, NULL, 'on_time', '15:00:00', 5, '2026-07-29 09:22:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_lecture_attendance`
--

CREATE TABLE `teacher_lecture_attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `school_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `attendance_date` date NOT NULL,
  `opened_at` datetime NOT NULL,
  `status` enum('on_time','late') NOT NULL,
  `lecture_time` time NOT NULL,
  `grace_period` int(10) UNSIGNED NOT NULL,
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
(1, '349ed798-cb8b-4656-a850-969ac5258d66', 'Pearl Special Needs Foundation', 'psnf', NULL, NULL, NULL, 'admin@psnf.edu', '+91-9000000000', NULL, NULL, NULL, 'India', 'Asia/Kolkata', NULL, 1, NULL, '{\"receipt\":{\"header_title\":\"Official Fee Payment Receipt\",\"footer_notes\":\"This receipt is automatically generated and serves as official proof of payment.\",\"show_watermark\":1,\"accent_color\":\"#6366f1\"}}', NULL, NULL, '2026-06-16 03:13:12', '2026-08-03 07:13:51', NULL);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `class_id` int(10) UNSIGNED DEFAULT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `subject_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`id`, `tenant_id`, `school_id`, `branch_id`, `class`, `section`, `day_of_week`, `subject`, `teacher_name`, `room`, `start_time`, `end_time`, `created_at`, `updated_at`, `class_id`, `teacher_id`, `subject_id`) VALUES
(30, 1, 1, 1, 'Class A', '', 'Monday', 'asd', 'akshat shah', 'Class A', '11:02:00', '00:12:00', '2026-07-29 13:46:47', '2026-08-02 06:02:43', 1, 12, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transport_drivers`
--

CREATE TABLE `transport_drivers` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT 1,
  `user_id` int(11) DEFAULT NULL,
  `driver_code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_expiry` date DEFAULT NULL,
  `emergency_contact` varchar(30) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `current_speed` decimal(5,2) NOT NULL DEFAULT 0.00,
  `route_status` enum('inactive','en_route','completed') NOT NULL DEFAULT 'inactive',
  `last_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `transport_route_stops`
--

CREATE TABLE `transport_route_stops` (
  `id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_name` varchar(100) NOT NULL,
  `stop_sequence` int(11) DEFAULT 1,
  `pickup_time` time DEFAULT '07:30:00',
  `drop_time` time DEFAULT '16:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_vehicles`
--

CREATE TABLE `transport_vehicles` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT 1,
  `vehicle_number` varchar(50) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `capacity` int(11) DEFAULT 40,
  `vehicle_type` varchar(50) DEFAULT 'Bus',
  `driver_id` int(11) DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trip_students`
--

CREATE TABLE `trip_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `trip_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `status` enum('Waiting','Current Stop','Picked Up','Absent','Skipped') NOT NULL DEFAULT 'Waiting',
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_students`
--

INSERT INTO `trip_students` (`id`, `trip_id`, `student_id`, `status`, `is_current`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 'Waiting', 0, '2026-07-30 18:51:39', '2026-07-30 18:51:39'),
(2, 5, 4, 'Waiting', 1, '2026-07-30 18:58:52', '2026-07-30 19:04:09'),
(3, 5, 3, 'Waiting', 0, '2026-07-30 18:58:52', '2026-07-30 18:58:52'),
(4, 6, 4, 'Picked Up', 0, '2026-07-30 19:05:12', '2026-07-30 19:05:27'),
(5, 6, 3, 'Picked Up', 0, '2026-07-30 19:05:12', '2026-07-30 19:05:33');

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
(7, '', 1, 1, 1, 'System Administrator', 'admin@psnf.local', NULL, '$2y$10$9fYjjHqWgDkDblRAZKJ1zOCzii6h7hFzUMZ2Di9S3gZzrOFLJLy/G', NULL, NULL, NULL, NULL, 'EMP-101', NULL, NULL, 0, NULL, NULL, '2026-08-04 12:23:27', '::1', 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-07-27 11:00:38', '2026-08-04 06:53:27', NULL),
(8, '2a447e6e-134c-4781-9192-577d7eb13703', 1, 1, 1, 'Het Shah', 'hetshah6315@gmail.com', '+919427961426', '$2y$12$5i/l1Mxa7HCf1t9Ps1y1YuUV4bP/EtMF.1ao5ElKBC2Cra83AE4Pq', NULL, NULL, NULL, 'class teacher', 'EMP-102', NULL, NULL, 0, NULL, NULL, '2026-08-01 12:51:25', '192.168.29.61', 0, NULL, 1, NULL, '09:00:00', 5, 7, 7, '2026-07-28 11:39:57', '2026-08-01 07:21:25', NULL),
(9, '34c3acbc-1610-4556-9205-4aa9ef2dd62f', 1, 1, 1, 'Het b Shah', 'hetshah6312@gmail.com', NULL, '$2y$12$AFA9/JHQh1jkYREeWjpEquhlqIHbQdCpD6Rnib2iJDE4yL9JDWTD6', NULL, NULL, NULL, NULL, 'EMP-103', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-07-29 07:16:05', '2026-07-29 10:41:23', '2026-07-29 10:41:23'),
(10, '22cd1aa0-c8ed-4748-b424-498e26153c73', 1, 1, 1, 'Het asd Shah', 'hetshah6311@gmail.com', NULL, '$2y$12$qsayj6ZBF.3KCNMK9FKRWutiqqr16VbXTT0WQZ9esQ0YhNUVpnm/S', NULL, NULL, NULL, NULL, 'EMP-104', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-07-29 09:34:24', '2026-07-29 09:50:56', '2026-07-29 09:50:56'),
(11, '9807759f-a07a-49d5-8078-a28beb676b9b', 1, 1, 1, 'heeeet ssss', '105@psnf.edu', NULL, '$2y$10$5LSYpovGYYUB1BRxeOLGVeggdcgIwA9WKhx8lS/iHK6JLeU7lynT6', NULL, NULL, NULL, NULL, '105', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, NULL, NULL, '2026-07-29 09:38:24', '2026-07-29 12:02:07', NULL),
(12, 'c0924d6f-216a-445d-a349-135161851fcf', 1, 1, 1, 'akshat shah', 'akshat@gmail.com', '94279614262', '$2y$12$4i.xgKZf2SaZXx0xyLAXJuJxAm6rv9xhk9aQnhdvCCsvj2Knh3KyK', NULL, NULL, NULL, '', 'EMP-104', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, '09:00:00', 5, 7, NULL, '2026-07-29 11:25:56', '2026-07-29 12:01:49', NULL),
(13, '2e288d27-9e3e-4c79-8dd8-c0195bd72659', 1, 1, 1, 'test driver', 'driver@gmail.com', '54654651654694', '$2y$10$DeVKc3dn7vQ/5JFGNhi4aucEMha9qoiT99MlXot6zt6y59ONQt9pu', NULL, NULL, NULL, NULL, 'EMP-105', NULL, NULL, 0, NULL, NULL, '2026-07-30 20:08:36', '192.168.29.14', 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-07-29 12:35:30', '2026-07-30 14:38:36', NULL),
(14, '49fc166e-3662-4df0-9bf4-dcc3bc28533e', 1, 1, 1, 'adaasd', 'parentnew@gmail.com', '9427996142', '$2y$12$O8fihJi79Wq4S4U9SxJKH./7nEcV3uqL.nhHqyM3rX/emlfdjBQq2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-07-29 19:26:36', '::1', 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-07-29 13:55:53', '2026-07-29 13:56:36', NULL),
(15, 'cbd8cfb3-e9ea-4d8d-9a96-b6edd2ae6ce7', 1, 1, 1, 'test', 'test@gmail.com', '9427961426', '$2y$12$rb3t9WcM2XE4oKAb41pQ3OuYujeSqmImAPsswowPxkKvyACv.feP2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-07-30 08:13:44', '2026-07-30 08:36:55', NULL),
(16, 'ebf0a88d-677c-404f-8047-de8dac221309', 1, 1, 1, 'aksksas', 'test22@gmail.com', '9828504654', '$2y$12$EOOP/cUAAqCiPdvrZziqUuNgOO5duflRLAuPC/EuTcVdQa86Yghdi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-07-30 08:14:53', '2026-07-30 18:18:07', NULL),
(17, 'b5ad2795-859a-4c7c-9175-815503e444ba', 1, 1, 1, 'test one', 'up@gmail.com', '9313457713', '$2y$12$e5VAoALjWo1RonI3ilX7EuU/2TNA7de/tBl17VLya2Z4NcPB5p45m', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-07-30 08:23:29', '2026-07-31 09:31:33', NULL),
(18, '1054d935-b798-431a-acda-4eba58e326c4', 1, 1, 1, 'hiral shah', 'hiral@gmail.com', '9413548546312', '$2y$12$RPWTWKOfe03KWxuSOZ0a.Ot36NcLSQF1KK1qlJOsndkAQvzM60fK2', NULL, NULL, NULL, NULL, 'EMP-106', NULL, NULL, 0, NULL, NULL, '2026-08-01 19:57:01', '192.168.29.61', 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-07-30 19:38:31', '2026-08-01 14:27:01', NULL),
(19, '236a1d26-5fa1-41d4-be97-478df362feb9', 1, 1, 1, 'janhe', 'jane@gmail.com', '9431346541', '$2y$12$crcrBJxP3SxAsgt.86EItusBgzNmA/nHOPX0gQEQWzbnVUcBjHncG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-08-01 10:34:15', NULL, NULL),
(20, '9b518b14-5073-4b12-a3e9-4d95c1803983', 1, 1, 1, 'asdasd', 'asdasd@gmail.com', '8543216576', '$2y$12$qrCyFeMiTJNhtetluWgKsuotUU//xCnNarnrX1/aTQOczAobBICe.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, 5, 7, NULL, '2026-08-03 12:09:29', NULL, NULL);

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
(6, 'teacher_app'),
(8, 'staff_dashboard'),
(8, 'teacher_app'),
(11, 'teacher_app'),
(12, 'staff_dashboard'),
(12, 'teacher_app');

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
(7, 1, '2026-07-28 11:32:04'),
(8, 4, '2026-07-29 08:27:19'),
(9, 6, '2026-07-29 07:16:05'),
(10, 4, '2026-07-29 09:34:24'),
(11, 4, '2026-07-29 09:38:24'),
(12, 4, '2026-07-29 11:25:56'),
(13, 7, '2026-07-29 12:35:30'),
(14, 8, '2026-07-29 13:55:53'),
(15, 8, '2026-07-30 08:13:44'),
(16, 8, '2026-07-30 08:14:53'),
(17, 8, '2026-07-30 08:23:29'),
(18, 4, '2026-07-30 19:38:31'),
(19, 8, '2026-08-01 10:34:15'),
(20, 8, '2026-08-03 12:09:29');

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

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_message_logs`
--

CREATE TABLE `whatsapp_message_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` int(11) DEFAULT 1,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'sent',
  `response` text DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_message_logs`
--

INSERT INTO `whatsapp_message_logs` (`id`, `tenant_id`, `phone`, `message`, `status`, `response`, `sent_at`) VALUES
(1, 1, '919427961426', 'Test message from PSNF ERP Settings.', 'failed', '<html>\r\n<head><title>301 Moved Permanently</title></head>\r\n<body>\r\n<center><h1>301 Moved Permanently</h1></center>\r\n<hr><center>nginx</center>\r\n</body>\r\n</html>\r\n', '2026-08-03 12:44:00'),
(2, 1, '919427961426', 'Test message from PSNF ERP Settings.', 'failed', '<html>\r\n<head><title>301 Moved Permanently</title></head>\r\n<body>\r\n<center><h1>301 Moved Permanently</h1></center>\r\n<hr><center>nginx</center>\r\n</body>\r\n</html>\r\n', '2026-08-03 12:46:10'),
(3, 1, '9825079765', 'Test message from PSNF ERP Settings.', 'failed', '<html>\r\n<head><title>301 Moved Permanently</title></head>\r\n<body>\r\n<center><h1>301 Moved Permanently</h1></center>\r\n<hr><center>nginx</center>\r\n</body>\r\n</html>\r\n', '2026-08-03 12:46:16'),
(4, 1, '+919427961426', 'Test message from PSNF ERP Settings.', 'failed', '<html>\r\n<head><title>301 Moved Permanently</title></head>\r\n<body>\r\n<center><h1>301 Moved Permanently</h1></center>\r\n<hr><center>nginx</center>\r\n</body>\r\n</html>\r\n', '2026-08-03 12:46:24'),
(5, 1, '919427961426', 'Test message from PSNF ERP Settings.', 'sent', '{\"success\":false,\"error\":\"Attempted to use detached Frame \'DA9D8A78BCBB089BC298FDFE947C78E6\'.\"}', '2026-08-03 12:48:14'),
(6, 1, '919427961426', 'Test message from PSNF ERP Settings.', 'sent', '{\"success\":true,\"message_id\":\"24915\"}', '2026-08-03 13:41:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_semesters`
--
ALTER TABLE `academic_semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `academic_terms`
--
ALTER TABLE `academic_terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_year` (`tenant_id`,`school_id`,`year_name`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_event` (`event`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_date` (`created_at`);

--
-- Indexes for table `admins_merged_backup`
--
ALTER TABLE `admins_merged_backup`
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
-- Indexes for table `certificate_types`
--
ALTER TABLE `certificate_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_certificate_type_slug2` (`conference_id`,`slug`);

--
-- Indexes for table `cert_settings`
--
ALTER TABLE `cert_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `cert_users`
--
ALTER TABLE `cert_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
  ADD UNIQUE KEY `uq_class_section` (`tenant_id`,`name`,`section`),
  ADD KEY `fk_classes_curriculum_template_id` (`curriculum_template_id`);

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
-- Indexes for table `conferences`
--
ALTER TABLE `conferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conference_name_year` (`name`,`year`);

--
-- Indexes for table `conference_admins`
--
ALTER TABLE `conference_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conference_admin` (`conference_id`,`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_messages_created_at2` (`created_at`);

--
-- Indexes for table `curriculum_sections`
--
ALTER TABLE `curriculum_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_section_template` (`curriculum_template_id`);

--
-- Indexes for table `curriculum_subjects`
--
ALTER TABLE `curriculum_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curr_subject_sec` (`curriculum_section_id`),
  ADD KEY `idx_curr_subject_sub` (`subject_id`);

--
-- Indexes for table `curriculum_templates`
--
ALTER TABLE `curriculum_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cur_year` (`academic_year_id`),
  ADD KEY `idx_cur_group` (`main_group_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `driver_bus_manifest`
--
ALTER TABLE `driver_bus_manifest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_manifest_date` (`route_id`,`student_id`,`date`);

--
-- Indexes for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- Indexes for table `driver_trips`
--
ALTER TABLE `driver_trips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_logs_participant2` (`participant_id`),
  ADD KEY `idx_email_logs_status2` (`status`);

--
-- Indexes for table `email_schedules`
--
ALTER TABLE `email_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_email_sch_conf2` (`conference_id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_profiles`
--
ALTER TABLE `employee_profiles`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_fe_user_id` (`user_id`);

--
-- Indexes for table `fees_invoices`
--
ALTER TABLE `fees_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_code` (`code`);

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
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_acad_year` (`academic_year_id`),
  ADD KEY `idx_main_group` (`main_group_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `fee_structure_items`
--
ALTER TABLE `fee_structure_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_structure_id` (`fee_structure_id`),
  ADD KEY `fee_category_id` (`fee_category_id`),
  ADD KEY `fk_fsi_late_fee` (`late_fee_policy_id`);

--
-- Indexes for table `field_mappings`
--
ALTER TABLE `field_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_field_mappings_type_sort2` (`certificate_type_id`,`sort_order`);

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
-- Indexes for table `generated_certificates`
--
ALTER TABLE `generated_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_generated_participant2` (`participant_id`);

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
-- Indexes for table `late_fee_policies`
--
ALTER TABLE `late_fee_policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `main_groups`
--
ALTER TABLE `main_groups`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `parent_attendance_notices`
--
ALTER TABLE `parent_attendance_notices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_notice_date` (`student_id`,`notice_date`);

--
-- Indexes for table `parent_documents`
--
ALTER TABLE `parent_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parent_student`
--
ALTER TABLE `parent_student`
  ADD PRIMARY KEY (`parent_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verify_code` (`verify_code`),
  ADD KEY `idx_participants_lookup2` (`conference_id`,`certificate_type_id`,`status`),
  ADD KEY `fk_participants_type2` (`certificate_type_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `salary_structures`
--
ALTER TABLE `salary_structures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sal_emp` (`employee_id`);

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
-- Indexes for table `shift_templates`
--
ALTER TABLE `shift_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff_attendance_logs`
--
ALTER TABLE `staff_attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_emp_date` (`employee_id`,`date`);

--
-- Indexes for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_uuid` (`uuid`),
  ADD KEY `idx_students_roll_number` (`roll_number`);
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
-- Indexes for table `student_ledgers`
--
ALTER TABLE `student_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_entry_type` (`entry_type`);

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
-- Indexes for table `student_subject_enrollments`
--
ALTER TABLE `student_subject_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_stu_subj` (`student_id`,`subject_id`,`academic_year_id`);

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
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_subj` (`tenant_id`,`code`);

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
-- Indexes for table `teacher_lecture_attendance`
--
ALTER TABLE `teacher_lecture_attendance`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `transport_drivers`
--
ALTER TABLE `transport_drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `school_id` (`school_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `transport_route_stops`
--
ALTER TABLE `transport_route_stops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_students`
--
ALTER TABLE `trip_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`);

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
-- Indexes for table `whatsapp_message_logs`
--
ALTER TABLE `whatsapp_message_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_whatsapp_tenant` (`tenant_id`),
  ADD KEY `idx_whatsapp_phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_semesters`
--
ALTER TABLE `academic_semesters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_terms`
--
ALTER TABLE `academic_terms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=303;

--
-- AUTO_INCREMENT for table `admins_merged_backup`
--
ALTER TABLE `admins_merged_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_types`
--
ALTER TABLE `certificate_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cert_settings`
--
ALTER TABLE `cert_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cert_users`
--
ALTER TABLE `cert_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `communication_messages`
--
ALTER TABLE `communication_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conferences`
--
ALTER TABLE `conferences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conference_admins`
--
ALTER TABLE `conference_admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum_sections`
--
ALTER TABLE `curriculum_sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `curriculum_subjects`
--
ALTER TABLE `curriculum_subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `curriculum_templates`
--
ALTER TABLE `curriculum_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_bus_manifest`
--
ALTER TABLE `driver_bus_manifest`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_documents`
--
ALTER TABLE `driver_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `driver_trips`
--
ALTER TABLE `driver_trips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_schedules`
--
ALTER TABLE `email_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_profiles`
--
ALTER TABLE `employee_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `face_embeddings`
--
ALTER TABLE `face_embeddings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `fees_invoices`
--
ALTER TABLE `fees_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_categories`
--
ALTER TABLE `fee_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_structure_items`
--
ALTER TABLE `fee_structure_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `field_mappings`
--
ALTER TABLE `field_mappings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `generated_certificates`
--
ALTER TABLE `generated_certificates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gps_logs`
--
ALTER TABLE `gps_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `homeworks`
--
ALTER TABLE `homeworks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `iep_progress`
--
ALTER TABLE `iep_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `late_fee_policies`
--
ALTER TABLE `late_fee_policies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `main_groups`
--
ALTER TABLE `main_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medical_incidents`
--
ALTER TABLE `medical_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_administration`
--
ALTER TABLE `medication_administration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `parent_attendance_notices`
--
ALTER TABLE `parent_attendance_notices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parent_documents`
--
ALTER TABLE `parent_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_items`
--
ALTER TABLE `payroll_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_structures`
--
ALTER TABLE `salary_structures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift_templates`
--
ALTER TABLE `shift_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_attendance_logs`
--
ALTER TABLE `staff_attendance_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_favorites`
--
ALTER TABLE `staff_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_ledgers`
--
ALTER TABLE `student_ledgers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_report_cards`
--
ALTER TABLE `student_report_cards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_subject_enrollments`
--
ALTER TABLE `student_subject_enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_timeline`
--
ALTER TABLE `student_timeline`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `student_transport`
--
ALTER TABLE `student_transport`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teacher_lecture_attendance`
--
ALTER TABLE `teacher_lecture_attendance`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `transport_drivers`
--
ALTER TABLE `transport_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_routes`
--
ALTER TABLE `transport_routes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_route_stops`
--
ALTER TABLE `transport_route_stops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trip_students`
--
ALTER TABLE `trip_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_message_logs`
--
ALTER TABLE `whatsapp_message_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `certificate_types`
--
ALTER TABLE `certificate_types`
  ADD CONSTRAINT `fk_cert_types_conference2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_classes_curriculum_template_id` FOREIGN KEY (`curriculum_template_id`) REFERENCES `curriculum_templates` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `conference_admins`
--
ALTER TABLE `conference_admins`
  ADD CONSTRAINT `fk_conf_admins_conf2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `driver_trips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `fk_email_logs_participant2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `email_schedules`
--
ALTER TABLE `email_schedules`
  ADD CONSTRAINT `fk_email_sch_conf2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `fk_ec_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `fee_structure_items`
--
ALTER TABLE `fee_structure_items`
  ADD CONSTRAINT `fee_structure_items_ibfk_1` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_structure_items_ibfk_2` FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories` (`id`),
  ADD CONSTRAINT `fk_fsi_late_fee` FOREIGN KEY (`late_fee_policy_id`) REFERENCES `late_fee_policies` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `field_mappings`
--
ALTER TABLE `field_mappings`
  ADD CONSTRAINT `fk_field_mappings_type2` FOREIGN KEY (`certificate_type_id`) REFERENCES `certificate_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `generated_certificates`
--
ALTER TABLE `generated_certificates`
  ADD CONSTRAINT `fk_generated_participant2` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `leave_applications_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_4` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `fk_participants_conference2` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_participants_type2` FOREIGN KEY (`certificate_type_id`) REFERENCES `certificate_types` (`id`) ON UPDATE CASCADE;

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
-- Constraints for table `trip_students`
--
ALTER TABLE `trip_students`
  ADD CONSTRAINT `trip_students_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `driver_trips` (`id`) ON DELETE CASCADE;

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
