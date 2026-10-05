-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Oct 05, 2026 at 12:36 PM
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
-- Database: `hrms_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `platform_user_id` int(11) UNSIGNED DEFAULT NULL,
  `company_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `record_type` varchar(100) DEFAULT NULL,
  `record_id` int(11) UNSIGNED DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `platform_user_id`, `company_id`, `action`, `module`, `record_type`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, NULL, 'create', 'company', 'company', 1, NULL, '{\"name\":\"Acme Technologies\",\"code\":\"acme\",\"plan_id\":2,\"status\":\"trial\",\"employee_limit\":50,\"timezone\":\"Asia\\/Kolkata\",\"currency\":\"INR\",\"contact_name\":null,\"contact_email\":\"admin@acme.example\",\"contact_phone\":null,\"country\":null,\"domain\":\"acme.example.com\"}', '::1', 'curl/8.16.0', '2026-08-19 13:04:46'),
(2, 1, 1, 'create', 'subscription', 'subscription', 1, NULL, '{\"company_id\":1,\"plan_id\":2,\"starts_at\":\"2026-08-19\",\"expires_at\":\"2027-08-19\",\"employee_limit_override\":null,\"status\":\"active\",\"remarks\":\"Initial subscription\",\"created_by\":1}', '::1', 'curl/8.16.0', '2026-08-19 13:08:17'),
(3, 1, 1, 'extend', 'subscription', 'subscription', 1, '{\"id\":\"1\",\"company_id\":\"1\",\"plan_id\":\"2\",\"starts_at\":\"2026-08-19\",\"expires_at\":\"2027-08-19\",\"employee_limit_override\":null,\"status\":\"active\",\"remarks\":\"Initial subscription\",\"created_by\":\"1\",\"created_at\":\"2026-08-19 13:08:17\",\"updated_at\":\"2026-08-19 13:08:17\"}', '{\"expires_at\":\"2028-01-01\"}', '::1', 'curl/8.16.0', '2026-08-19 13:08:36'),
(4, 1, 1, 'status_change', 'subscription', 'subscription', 1, '{\"id\":\"1\",\"company_id\":\"1\",\"plan_id\":\"2\",\"starts_at\":\"2026-08-19\",\"expires_at\":\"2028-01-01\",\"employee_limit_override\":null,\"status\":\"active\",\"remarks\":\"Initial subscription\",\"created_by\":\"1\",\"created_at\":\"2026-08-19 13:08:17\",\"updated_at\":\"2026-08-19 13:08:35\"}', '{\"status\":\"suspended\"}', '::1', 'curl/8.16.0', '2026-08-19 13:08:37'),
(5, 1, NULL, 'update', 'settings', 'platform_settings', NULL, '{\"platform_name\":\"HRMS Platform\",\"support_email\":\"support@example.com\",\"default_timezone\":\"Asia\\/Kolkata\",\"default_currency\":\"INR\"}', '{\"platform_name\":\"HRMS Cloud\",\"support_email\":\"support@hrms.local\",\"default_timezone\":\"Asia\\/Kolkata\",\"default_currency\":\"INR\"}', '::1', 'curl/8.16.0', '2026-08-19 13:13:04'),
(6, 1, NULL, 'create', 'role', 'role', 2, NULL, '{\"name\":\"Support\",\"permissions\":[1]}', '::1', 'curl/8.16.0', '2026-08-19 13:13:21'),
(7, 1, NULL, 'create', 'platform_user', 'platform_user', 2, NULL, '{\"name\":\"Support Rep\",\"email\":\"support@hrms-platform.local\",\"roles\":[2]}', '::1', 'curl/8.16.0', '2026-08-19 13:13:22'),
(8, 1, NULL, 'status_change', 'plan', 'plan', 1, '{\"is_active\":\"1\"}', '{\"is_active\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', '2026-08-19 13:27:54'),
(9, 1, NULL, 'status_change', 'plan', 'plan', 1, '{\"is_active\":\"0\"}', '{\"is_active\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', '2026-08-19 13:27:57'),
(10, 1, 2, 'create', 'subscription', 'subscription', 2, NULL, '{\"company_id\":2,\"plan_id\":\"2\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-20\",\"status\":\"trial\",\"created_by\":1}', '::1', 'curl/8.16.0', '2026-08-20 04:57:14'),
(11, 1, 2, 'create', 'company', 'company', 2, NULL, '{\"code\":\"abc\",\"name\":\"Alpha Beta Consulting\",\"domain\":\"abc.hrms.test\"}', '::1', 'curl/8.16.0', '2026-08-20 04:57:14'),
(12, 1, 2, 'provision_failed', 'company', 'company', 2, NULL, '{\"error\":\"CodeIgniter\\\\Config\\\\Services::curlrequest(): Argument #2 ($response) must be of type ?CodeIgniter\\\\HTTP\\\\ResponseInterface, bool given, called in F:\\\\xampp8.2\\\\htdocs\\\\superadmin\\\\vendor\\\\codeigniter4\\\\framework\\\\system\\\\Config\\\\BaseService.php on line 334\"}', '::1', 'curl/8.16.0', '2026-08-20 04:57:30'),
(13, 1, NULL, 'status_change', 'plan', 'plan', 3, '{\"is_active\":\"1\"}', '{\"is_active\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', '2026-08-20 04:57:38'),
(14, 1, NULL, 'status_change', 'plan', 'plan', 3, '{\"is_active\":\"0\"}', '{\"is_active\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', '2026-08-20 04:57:43'),
(15, 1, 2, 'provision_failed', 'company', 'company', 2, NULL, '{\"error\":\"Could not reach the HRMS application for step \\\"migrate\\\".\"}', '::1', 'curl/8.16.0', '2026-08-20 04:58:12'),
(16, 1, 2, 'provision_ready', 'company', 'company', 2, NULL, '{\"provisioning_status\":\"ready\"}', '127.0.0.1', 'curl/8.16.0', '2026-08-20 04:59:05'),
(17, 1, 3, 'create', 'subscription', 'subscription', 3, NULL, '{\"company_id\":3,\"plan_id\":\"1\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-20\",\"status\":\"active\",\"created_by\":1}', '127.0.0.1', 'curl/8.16.0', '2026-08-20 04:59:25'),
(18, 1, 3, 'create', 'company', 'company', 3, NULL, '{\"code\":\"abc2\",\"name\":\"Beta Gamma Industries\",\"domain\":\"abc2.hrms.test\"}', '127.0.0.1', 'curl/8.16.0', '2026-08-20 04:59:25'),
(19, 1, 3, 'provision_ready', 'company', 'company', 3, NULL, '{\"provisioning_status\":\"ready\"}', '127.0.0.1', 'curl/8.16.0', '2026-08-20 04:59:38'),
(20, 1, 2, 'status_change', 'company', 'company', 2, '{\"status\":\"trial\"}', '{\"status\":\"suspended\"}', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:07:01'),
(21, 1, 2, 'status_change', 'company', 'company', 2, '{\"status\":\"suspended\"}', '{\"status\":\"active\"}', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:07:13'),
(22, 1, 1, 'status_change', 'company', 'company', 1, '{\"status\":\"suspended\"}', '{\"status\":\"active\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.7339.186 Safari/537.36', '2026-08-20 05:37:26'),
(23, 1, 3, 'extend', 'subscription', 'subscription', 3, '{\"id\":\"3\",\"company_id\":\"3\",\"plan_id\":\"1\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-20\",\"employee_limit_override\":null,\"status\":\"active\",\"remarks\":null,\"created_by\":\"1\",\"created_at\":\"2026-08-20 04:59:25\",\"updated_at\":\"2026-08-20 04:59:25\"}', '{\"expires_at\":\"2026-09-30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-28 06:47:10'),
(24, 1, 3, 'plan_change', 'subscription', 'subscription', 3, '{\"id\":\"3\",\"company_id\":\"3\",\"plan_id\":\"1\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-30\",\"employee_limit_override\":null,\"status\":\"active\",\"remarks\":null,\"created_by\":\"1\",\"created_at\":\"2026-08-20 04:59:25\",\"updated_at\":\"2026-09-28 06:47:09\"}', '{\"plan_id\":2,\"employee_limit_override\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-28 06:48:21'),
(25, 1, 2, 'extend', 'subscription', 'subscription', 2, '{\"id\":\"2\",\"company_id\":\"2\",\"plan_id\":\"2\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-20\",\"employee_limit_override\":null,\"status\":\"trial\",\"remarks\":null,\"created_by\":\"1\",\"created_at\":\"2026-08-20 04:57:14\",\"updated_at\":\"2026-08-20 04:57:14\"}', '{\"expires_at\":\"2026-10-10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-28 06:49:00'),
(26, 1, 2, 'status_change', 'company', 'company', 2, '{\"status\":\"trial\"}', '{\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 04:56:22'),
(27, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#50833F\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":\"#4F5827\",\"sidebar_active_color\":\"#50833F\",\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:02:12'),
(28, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#50833F\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":\"#4F5827\",\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:04:55'),
(29, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#B7C484\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":\"#4F5827\",\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:05:28'),
(30, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#B7C484\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:05:46'),
(31, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#B7C484\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":20}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:06:38'),
(32, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#B7C484\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:06:49'),
(33, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#B7C484\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:06:58'),
(34, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#3454D1\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:07:42'),
(35, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#9D9E61\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:14:59'),
(36, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#848552\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:15:25'),
(37, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#000000\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:16:06'),
(38, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#FFFFFF\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:19:35'),
(39, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#808000\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:19:52'),
(40, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#39611F\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 05:21:12'),
(41, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#3454D1\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:23:08'),
(42, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#4B3FD1\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:24:26'),
(43, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#969878\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:25:01'),
(44, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#C10B0B\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:25:25'),
(45, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#367726\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:25:44'),
(46, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#0F766E\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:28:01'),
(47, 1, 2, 'branding_update', 'company', 'company', 2, NULL, '{\"primary_color\":\"#4B3FD1\",\"secondary_color\":null,\"success_color\":null,\"warning_color\":null,\"danger_color\":null,\"info_color\":null,\"sidebar_bg_color\":null,\"sidebar_active_color\":null,\"sidebar_hover_color\":null,\"header_bg_color\":null,\"card_accent_color\":null,\"button_radius\":null}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:28:22'),
(48, 1, 4, 'create', 'subscription', 'subscription', 4, NULL, '{\"company_id\":4,\"plan_id\":\"1\",\"starts_at\":\"2026-10-01\",\"expires_at\":\"2026-10-15\",\"status\":\"trial\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-01 10:19:41'),
(49, 1, 4, 'create', 'company', 'company', 4, NULL, '{\"code\":\"ram\",\"name\":\"ram\",\"domain\":\"ram.hrms.test\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-01 10:19:42'),
(56, 1, 2, 'update', 'company_settings', 'company_setting', 1, '{\"primary_color\":\"#4B3FD1\"}', '{\"primary_color\":\"#57AAB9\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 09:52:31'),
(57, 1, 2, 'update', 'company_settings', 'company_setting', 1, '{\"primary_color\":\"#57AAB9\"}', '{\"primary_color\":\"#32764E\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 09:53:14'),
(58, 1, 2, 'update', 'company_settings', 'company_setting', 1, '{\"default_language\":\"en\"}', '{\"default_language\":\"hi\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 12:29:02'),
(59, 1, 2, 'update', 'company_settings', 'company_setting', 1, '{\"font_family\":\"system\"}', '{\"font_family\":\"inter\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 12:33:57'),
(60, 1, 2, 'update', 'company_settings', 'company_setting', 1, '{\"font_family\":\"inter\"}', '{\"font_family\":\"roboto\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 12:38:20'),
(61, 1, 9, 'create', 'subscription', 'subscription', 5, NULL, '{\"company_id\":9,\"plan_id\":\"1\",\"starts_at\":\"2026-10-05\",\"expires_at\":\"2026-10-19\",\"status\":\"trial\",\"created_by\":1}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 04:32:39'),
(62, 1, 9, 'create', 'company', 'company', 9, NULL, '{\"code\":\"siva\",\"name\":\"siva\",\"domain\":\"siva.hrms.test\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 04:32:40'),
(63, 1, 9, 'db_connection_saved', 'company', 'company', 9, NULL, '{\"db_host\":\"127.0.0.1\",\"db_port\":3307,\"db_name\":\"hrms_siva\",\"db_username\":\"root\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 05:07:06'),
(64, 1, 9, 'provision_ready', 'company', 'company', 9, NULL, '{\"provisioning_status\":\"ready\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 05:07:06'),
(65, 1, 9, 'license_issue', 'license', 'license', 1, NULL, '{\"license_key\":\"HRMS-5EEC-F2F9-C95E-F7C8\",\"domain\":\"siva.hrms.test\",\"expires_at\":\"2026-10-19\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 05:07:06'),
(66, NULL, 9, 'license_issue', 'license', 'license', 2, NULL, '{\"license_key\":\"HRMS-8211-2B4B-419A-615F\",\"domain\":\"siva.hrms.test\",\"expires_at\":\"2026-10-19 00:00:00\"}', 'cli', 'cli', '2026-10-05 05:56:33'),
(67, NULL, 9, 'license_renew', 'license', 'license', 2, '{\"expires_at\":\"2026-10-19 00:00:00\"}', '{\"expires_at\":\"2026-10-19\"}', 'cli', 'cli', '2026-10-05 05:56:33'),
(68, 1, 9, 'update', 'company', 'company', 9, '{\"id\":\"9\",\"code\":\"siva\",\"name\":\"siva\",\"status\":\"trial\",\"provisioning_status\":\"ready\",\"provisioning_error\":null,\"plan_id\":\"1\",\"current_subscription_id\":\"5\",\"employee_limit\":\"25\",\"timezone\":\"Asia\\/Kolkata\",\"currency\":\"INR\",\"contact_name\":\"\",\"contact_email\":\"\",\"contact_phone\":\"\",\"country\":\"\",\"primary_admin_name\":\"siva\",\"primary_admin_email\":\"siva@gmail.com\",\"trial_ends_at\":null,\"created_at\":\"2026-10-05 04:32:39\",\"updated_at\":\"2026-10-05 05:07:06\",\"deleted_at\":null}', '{\"name\":\"siva\",\"plan_id\":1,\"employee_limit\":1,\"timezone\":\"Asia\\/Kolkata\",\"currency\":\"INR\",\"contact_name\":\"\",\"contact_email\":\"\",\"contact_phone\":\"\",\"country\":\"\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 06:16:32'),
(69, 1, 9, 'status_change', 'company', 'company', 9, '{\"status\":\"trial\"}', '{\"status\":\"active\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 06:20:50'),
(70, 1, 9, 'status_change', 'company', 'company', 9, '{\"status\":\"active\"}', '{\"status\":\"suspended\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 06:20:57'),
(71, 1, 9, 'status_change', 'company', 'company', 9, '{\"status\":\"suspended\"}', '{\"status\":\"active\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 06:21:08'),
(72, 1, 9, 'extend', 'subscription', 'subscription', 5, '{\"id\":\"5\",\"company_id\":\"9\",\"plan_id\":\"1\",\"starts_at\":\"2026-10-05\",\"expires_at\":\"2026-10-19\",\"employee_limit_override\":null,\"status\":\"trial\",\"remarks\":null,\"last_reminder_sent_at\":null,\"created_by\":\"1\",\"created_at\":\"2026-10-05 04:32:39\",\"updated_at\":\"2026-10-05 04:32:39\"}', '{\"expires_at\":\"2026-10-04\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 06:21:59');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `status` enum('trial','active','expiring','expired','suspended','cancelled') NOT NULL DEFAULT 'trial',
  `provisioning_status` enum('pending','provisioning','ready','failed') DEFAULT 'pending',
  `provisioning_error` varchar(255) DEFAULT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL,
  `current_subscription_id` int(11) UNSIGNED DEFAULT NULL,
  `employee_limit` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `timezone` varchar(64) NOT NULL DEFAULT 'Asia/Kolkata',
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `contact_name` varchar(150) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `contact_phone` varchar(30) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `primary_admin_name` varchar(150) DEFAULT NULL,
  `primary_admin_email` varchar(150) DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `code`, `name`, `status`, `provisioning_status`, `provisioning_error`, `plan_id`, `current_subscription_id`, `employee_limit`, `timezone`, `currency`, `contact_name`, `contact_email`, `contact_phone`, `country`, `primary_admin_name`, `primary_admin_email`, `trial_ends_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'acme', 'Acme Technologies', 'active', 'pending', NULL, 2, 1, 100, 'Asia/Kolkata', 'INR', NULL, 'admin@acme.example', NULL, NULL, NULL, NULL, NULL, '2026-08-19 13:04:46', '2026-08-20 05:37:26', NULL),
(2, 'abc', 'Alpha Beta Consulting', 'active', 'ready', NULL, 2, 2, 100, 'Asia/Kolkata', 'INR', 'Jane Admin', 'jane@abc.test', '1234567890', 'India', 'ABC Admin', 'admin@abc.test', NULL, '2026-08-20 04:57:13', '2026-09-29 04:56:22', NULL),
(3, 'abc2', 'Beta Gamma Industries', 'active', 'ready', NULL, 2, 3, 100, 'Asia/Kolkata', 'INR', 'Bob Admin', 'bob@abc2.test', '1234567890', 'India', 'ABC2 Admin', 'admin@abc2.test', NULL, '2026-08-20 04:59:25', '2026-09-28 06:48:21', NULL),
(4, 'ram', 'ram', 'trial', 'pending', NULL, 1, 4, 25, 'Asia/Kolkata', 'INR', '', '', '', '', 'ram', 'ram@gmail.com', NULL, '2026-10-01 10:19:41', '2026-10-01 10:19:41', NULL),
(9, 'siva', 'siva', 'trial', 'ready', NULL, 1, 5, 25, 'Asia/Kolkata', 'INR', '', '', '', '', 'siva', 'siva@gmail.com', NULL, '2026-10-05 04:32:39', '2026-10-05 06:21:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_database_connections`
--

CREATE TABLE `company_database_connections` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `db_host` varchar(150) NOT NULL,
  `db_port` int(5) UNSIGNED NOT NULL DEFAULT 3306,
  `db_name` varchar(100) NOT NULL,
  `db_username` varchar(100) NOT NULL,
  `db_password_enc` text NOT NULL,
  `status` enum('pending','provisioned','error') NOT NULL DEFAULT 'pending',
  `last_checked_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_database_connections`
--

INSERT INTO `company_database_connections` (`id`, `company_id`, `db_host`, `db_port`, `db_name`, `db_username`, `db_password_enc`, `status`, `last_checked_at`, `created_at`, `updated_at`) VALUES
(1, 1, '127.0.0.1', 3307, 'hrms_acme', 'pending', 'pgM8X3O0XdgGmF1Il9wo4yimFbbD6trS8Xmr1YFDoH9+khRbDCeBkWUQVxgkGdQvjplTC3beWGU2DfdW8m8K8bAxukLErDTO9qPgfOb4MIA=', 'pending', NULL, '2026-08-19 13:04:46', '2026-08-19 13:04:46'),
(2, 2, '127.0.0.1', 3307, 'hrms_abc', 'hrms_abc_usr', 'GHth9gs2A9vioOVky8pXA7speSVG244AHWLSTrYUpGvhzmKRqvDvfSjbv1yqtruS6+IK02893tdyqNJK10j9KVf81xlWOPapTJOoK/8MW4nJ2v2BSLXpAMYlzlMGlq0cOdqGLNFB/TuWcjHk/dQkBw==', 'provisioned', '2026-08-20 04:59:05', '2026-08-20 04:57:14', '2026-08-20 04:59:05'),
(3, 3, '127.0.0.1', 3307, 'hrms_abc2', 'hrms_abc2_usr', 'OaP821uF9daCPDIEC5BL7VTUxdrxeai00TArVbrdyjOO2yd01eYnavgzq7w2+rqJMEpv5BkrgU8ebXAYwWgM5S0aTR4rOJh/8gn2O1o1tgeMktzhE4zMNB2xaFpvMowJBmeaPb56WyJoabwgqJ5Omw==', 'provisioned', '2026-08-20 04:59:38', '2026-08-20 04:59:25', '2026-08-20 04:59:38'),
(6, 9, '127.0.0.1', 3307, 'hrms_siva', 'root', 'azx5OFreyCLDKjhGtPfY1QPZtsVZ0CiQpReK7B7sZybIAv/lvYdO05I4hXm2Qd7KYbXi6YpsTnJELovy6Dr/LJ2NnDJlmITyVLjbD/LMsaU=', 'provisioned', '2026-10-05 05:07:06', '2026-10-05 05:07:06', '2026-10-05 05:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `company_domains`
--

CREATE TABLE `company_domains` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `domain` varchar(191) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_domains`
--

INSERT INTO `company_domains` (`id`, `company_id`, `domain`, `is_primary`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'acme.example.com', 1, NULL, '2026-08-19 13:04:46', '2026-08-19 13:04:46'),
(2, 2, 'abc.hrms.test', 1, NULL, '2026-08-20 04:57:13', '2026-08-20 04:57:13'),
(3, 3, 'abc2.hrms.test', 1, NULL, '2026-08-20 04:59:25', '2026-08-20 04:59:25'),
(4, 4, 'ram.hrms.test', 1, NULL, '2026-10-01 10:19:41', '2026-10-01 10:19:41'),
(5, 9, 'siva.hrms.test', 1, NULL, '2026-10-05 04:32:39', '2026-10-05 04:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `company_module_overrides`
--

CREATE TABLE `company_module_overrides` (
  `company_id` int(11) UNSIGNED NOT NULL,
  `module_id` int(11) UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE `licenses` (
  `id` int(11) UNSIGNED NOT NULL,
  `license_uuid` char(36) NOT NULL,
  `license_key` varchar(40) NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL,
  `domain` varchar(191) NOT NULL,
  `issued_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `grace_days` int(11) UNSIGNED NOT NULL DEFAULT 7,
  `status` enum('active','revoked','expired') NOT NULL DEFAULT 'active',
  `signature` char(64) NOT NULL,
  `issued_by` int(11) UNSIGNED DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int(11) UNSIGNED DEFAULT NULL,
  `revoked_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`id`, `license_uuid`, `license_key`, `company_id`, `plan_id`, `domain`, `issued_at`, `expires_at`, `grace_days`, `status`, `signature`, `issued_by`, `revoked_at`, `revoked_by`, `revoked_reason`, `created_at`, `updated_at`) VALUES
(1, 'bf166b05-5158-44ee-bfaf-2378171cdd35', 'HRMS-5EEC-F2F9-C95E-F7C8', 9, 1, 'siva.hrms.test', '2026-10-05 05:07:06', '2026-10-19 00:00:00', 7, 'active', 'b1195ccafc63195f36a3afeb8c808d71d21e26249e653aac6eadfd4e5568f1c4', 1, NULL, NULL, NULL, '2026-10-05 05:07:06', '2026-10-05 05:07:06'),
(2, '1ffbf8e1-878f-44bf-aad0-1f1941403da6', 'HRMS-8211-2B4B-419A-615F', 9, 1, 'siva.hrms.test', '2026-10-05 05:56:33', '2026-10-19 00:00:00', 7, 'active', 'cca377e90dc9388b4d739878390c38a67bccf3f7640b1fc53c97608fb9f8bf87', 1, NULL, NULL, NULL, '2026-10-05 05:56:33', '2026-10-05 05:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `platform_user_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `platform_user_id`, `email`, `status`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'admin@hrms-platform.local', 'success', '::1', 'curl/8.16.0', '2026-08-19 13:01:42'),
(2, 1, 'admin@hrms-platform.local', 'success', '::1', 'curl/8.16.0', '2026-08-19 13:04:20'),
(3, 1, 'admin@hrms-platform.local', 'failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', '2026-08-19 13:09:04'),
(4, 2, 'support@hrms-platform.local', 'success', '::1', 'curl/8.16.0', '2026-08-19 13:13:35'),
(5, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.7339.186 Safari/537.36', '2026-08-19 13:20:35'),
(6, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.7339.186 Safari/537.36', '2026-08-19 13:22:27'),
(7, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/140.0.7339.186 Safari/537.36', '2026-08-19 13:23:33'),
(8, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', '2026-08-19 13:25:45'),
(9, 1, 'admin@hrms-platform.local', 'success', '::1', 'curl/8.16.0', '2026-08-20 04:56:51'),
(10, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', '2026-08-20 04:57:02'),
(11, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 04:59:00'),
(12, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:11:17'),
(13, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:15:31'),
(14, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:18:21'),
(15, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:34:07'),
(16, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 05:39:36'),
(17, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-20 06:30:55'),
(18, 2, 'support@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-24 12:35:58'),
(19, 2, 'support@hrms-platform.local', 'success', '127.0.0.1', 'curl/8.16.0', '2026-08-24 12:54:17'),
(20, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-05 07:02:51'),
(21, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-05 10:30:23'),
(22, NULL, 'admin@abc.test', 'failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-10 06:37:02'),
(23, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-10 06:37:05'),
(24, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-10 13:07:19'),
(25, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-10 13:08:34'),
(26, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:155.0) Gecko/20100101 Firefox/155.0', '2026-09-11 04:44:35'),
(27, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-28 06:43:26'),
(28, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-28 13:21:29'),
(29, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 04:50:43'),
(30, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', '2026-09-29 09:46:36'),
(31, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-09-30 09:21:17'),
(32, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-01 04:09:36'),
(33, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-01 07:20:06'),
(34, 1, 'admin@hrms-platform.local', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-01 09:58:13'),
(35, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-01 12:19:27'),
(36, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 07:15:01'),
(37, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 07:51:18'),
(38, NULL, 'admin@abc.test', 'failed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 12:28:41'),
(39, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-02 12:28:43'),
(40, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 04:16:46'),
(41, 1, 'admin@hrms-platform.local', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-05 06:16:04');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-08-19-090001', 'App\\Database\\Migrations\\CreatePlatformUsers', 'default', 'App', 1787142669, 1),
(2, '2026-08-19-090002', 'App\\Database\\Migrations\\CreatePlatformRoles', 'default', 'App', 1787142669, 1),
(3, '2026-08-19-090003', 'App\\Database\\Migrations\\CreatePlatformPermissions', 'default', 'App', 1787142669, 1),
(4, '2026-08-19-090004', 'App\\Database\\Migrations\\CreatePlatformUserRoles', 'default', 'App', 1787142670, 1),
(5, '2026-08-19-090005', 'App\\Database\\Migrations\\CreatePlatformRolePermissions', 'default', 'App', 1787142670, 1),
(6, '2026-08-19-090006', 'App\\Database\\Migrations\\CreatePlans', 'default', 'App', 1787142670, 1),
(7, '2026-08-19-090007', 'App\\Database\\Migrations\\CreateModules', 'default', 'App', 1787142670, 1),
(8, '2026-08-19-090008', 'App\\Database\\Migrations\\CreatePlanModules', 'default', 'App', 1787142670, 1),
(9, '2026-08-19-090009', 'App\\Database\\Migrations\\CreateCompanies', 'default', 'App', 1787142671, 1),
(10, '2026-08-19-090010', 'App\\Database\\Migrations\\CreateCompanyDomains', 'default', 'App', 1787142671, 1),
(11, '2026-08-19-090011', 'App\\Database\\Migrations\\CreateCompanyDatabaseConnections', 'default', 'App', 1787142671, 1),
(12, '2026-08-19-090012', 'App\\Database\\Migrations\\CreateCompanyModuleOverrides', 'default', 'App', 1787142672, 1),
(13, '2026-08-19-090013', 'App\\Database\\Migrations\\CreateSubscriptions', 'default', 'App', 1787142672, 1),
(14, '2026-08-19-090014', 'App\\Database\\Migrations\\CreateSubscriptionHistory', 'default', 'App', 1787142672, 1),
(15, '2026-08-19-090015', 'App\\Database\\Migrations\\CreatePlatformSettings', 'default', 'App', 1787142672, 1),
(16, '2026-08-19-090016', 'App\\Database\\Migrations\\CreateAuditLogs', 'default', 'App', 1787142672, 1),
(17, '2026-08-19-090017', 'App\\Database\\Migrations\\CreateLoginLogs', 'default', 'App', 1787142673, 1),
(18, '2026-08-20-090001', 'App\\Database\\Migrations\\AddProvisioningToCompanies', 'default', 'App', 1787201184, 2),
(19, '2026-08-20-090002', 'App\\Database\\Migrations\\CreateProvisioningLogs', 'default', 'App', 1787201184, 2),
(20, '2026-08-20-090003', 'App\\Database\\Migrations\\AddPrimaryAdminToCompanies', 'default', 'App', 1787201306, 3),
(21, '2026-09-10-090001', 'App\\Database\\Migrations\\AddReminderTrackingToSubscriptions', 'default', 'App', 1790657616, 4),
(22, '2026-09-10-090002', 'App\\Database\\Migrations\\AddGraceDaysToPlans', 'default', 'App', 1790657616, 4),
(23, '2026-09-10-090003', 'App\\Database\\Migrations\\CreateLicenses', 'default', 'App', 1790657616, 4);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) UNSIGNED NOT NULL,
  `module_key` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_core` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_key`, `name`, `description`, `is_core`, `created_at`, `updated_at`) VALUES
(1, 'employee', 'Employee Management', NULL, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(2, 'attendance', 'Attendance', NULL, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(3, 'leave', 'Leave', NULL, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(4, 'payroll', 'Payroll', NULL, 0, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(5, 'recruitment', 'Recruitment', NULL, 0, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(6, 'performance', 'Performance', NULL, 0, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(7, 'assets', 'Assets', NULL, 0, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(8, 'expenses', 'Expenses', NULL, 0, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(9, 'documents', 'Documents', NULL, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(10, 'reports', 'Reports', NULL, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(11, 'training', 'Training', NULL, 0, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(12, 'notifications', 'Notifications', NULL, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `employee_limit` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `branch_limit` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `storage_limit_mb` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `duration_days` int(11) UNSIGNED NOT NULL DEFAULT 365,
  `grace_days` int(11) UNSIGNED DEFAULT 7,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `code`, `name`, `employee_limit`, `branch_limit`, `storage_limit_mb`, `duration_days`, `grace_days`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'basic', 'Basic', 25, 1, 1024, 365, 7, 1, '2026-08-19 12:52:18', '2026-08-19 13:27:57'),
(2, 'professional', 'Professional', 100, 5, 5120, 365, 7, 1, '2026-08-19 12:52:18', '2026-08-19 12:52:18'),
(3, 'enterprise', 'Enterprise', 500, 25, 20480, 365, 7, 1, '2026-08-19 12:52:18', '2026-08-20 04:57:42');

-- --------------------------------------------------------

--
-- Table structure for table `plan_modules`
--

CREATE TABLE `plan_modules` (
  `plan_id` int(11) UNSIGNED NOT NULL,
  `module_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_modules`
--

INSERT INTO `plan_modules` (`plan_id`, `module_id`, `created_at`) VALUES
(1, 1, '2026-08-19 12:52:19'),
(1, 2, '2026-08-19 12:52:19'),
(1, 3, '2026-08-19 12:52:19'),
(1, 9, '2026-08-19 12:52:19'),
(1, 10, '2026-08-19 12:52:19'),
(1, 12, '2026-08-19 12:52:19'),
(2, 1, '2026-08-19 12:52:19'),
(2, 2, '2026-08-19 12:52:19'),
(2, 3, '2026-08-19 12:52:19'),
(2, 4, '2026-08-19 12:52:19'),
(2, 5, '2026-08-19 12:52:19'),
(2, 7, '2026-08-19 12:52:19'),
(2, 8, '2026-08-19 12:52:19'),
(2, 9, '2026-08-19 12:52:19'),
(2, 10, '2026-08-19 12:52:19'),
(2, 12, '2026-08-19 12:52:19'),
(3, 1, '2026-08-19 12:52:19'),
(3, 2, '2026-08-19 12:52:19'),
(3, 3, '2026-08-19 12:52:19'),
(3, 4, '2026-08-19 12:52:19'),
(3, 5, '2026-08-19 12:52:19'),
(3, 6, '2026-08-19 12:52:19'),
(3, 7, '2026-08-19 12:52:19'),
(3, 8, '2026-08-19 12:52:19'),
(3, 9, '2026-08-19 12:52:19'),
(3, 10, '2026-08-19 12:52:19'),
(3, 11, '2026-08-19 12:52:19'),
(3, 12, '2026-08-19 12:52:19');

-- --------------------------------------------------------

--
-- Table structure for table `platform_permissions`
--

CREATE TABLE `platform_permissions` (
  `id` int(11) UNSIGNED NOT NULL,
  `slug` varchar(150) NOT NULL,
  `module` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_permissions`
--

INSERT INTO `platform_permissions` (`id`, `slug`, `module`, `description`, `created_at`, `updated_at`) VALUES
(1, 'company.view', 'company', 'View companies', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(2, 'company.create', 'company', 'Create companies', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(3, 'company.edit', 'company', 'Edit company details', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(4, 'company.delete', 'company', 'Archive companies', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(5, 'company.activate', 'company', 'Activate companies', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(6, 'company.suspend', 'company', 'Suspend companies', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(7, 'plan.view', 'plan', 'View plans', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(8, 'plan.create', 'plan', 'Create plans', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(9, 'plan.edit', 'plan', 'Edit plans', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(10, 'plan.delete', 'plan', 'Deactivate plans', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(11, 'module.view', 'module', 'View modules', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(12, 'module.manage', 'module', 'Manage module catalog and plan/company assignment', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(13, 'subscription.view', 'subscription', 'View subscriptions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(14, 'subscription.create', 'subscription', 'Create subscriptions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(15, 'subscription.edit', 'subscription', 'Edit subscriptions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(16, 'subscription.extend', 'subscription', 'Extend or renew subscriptions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(17, 'subscription.suspend', 'subscription', 'Suspend or cancel subscriptions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(18, 'user.view', 'user', 'View platform users', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(19, 'user.manage', 'user', 'Create, edit, and deactivate platform users', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(20, 'role.view', 'role', 'View roles and permissions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(21, 'role.manage', 'role', 'Create and edit roles, assign permissions', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(22, 'audit.view', 'audit', 'View audit and login logs', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(23, 'settings.manage', 'settings', 'Manage platform settings', '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(24, 'company.settings', 'company', 'Manage a company\'s tenant settings (branding, localization, theme colors)', '2026-10-02 07:50:14', '2026-10-02 07:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `platform_roles`
--

CREATE TABLE `platform_roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_roles`
--

INSERT INTO `platform_roles` (`id`, `name`, `slug`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super-admin', 1, '2026-08-19 12:52:16', '2026-08-19 12:52:16'),
(2, 'Support', 'support', 0, '2026-08-19 13:13:21', '2026-08-19 13:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `platform_role_permissions`
--

CREATE TABLE `platform_role_permissions` (
  `role_id` int(11) UNSIGNED NOT NULL,
  `permission_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_role_permissions`
--

INSERT INTO `platform_role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES
(1, 1, '2026-10-02 07:50:14'),
(1, 2, '2026-10-02 07:50:14'),
(1, 3, '2026-10-02 07:50:14'),
(1, 4, '2026-10-02 07:50:14'),
(1, 5, '2026-10-02 07:50:14'),
(1, 6, '2026-10-02 07:50:14'),
(1, 7, '2026-10-02 07:50:14'),
(1, 8, '2026-10-02 07:50:14'),
(1, 9, '2026-10-02 07:50:14'),
(1, 10, '2026-10-02 07:50:14'),
(1, 11, '2026-10-02 07:50:14'),
(1, 12, '2026-10-02 07:50:14'),
(1, 13, '2026-10-02 07:50:14'),
(1, 14, '2026-10-02 07:50:14'),
(1, 15, '2026-10-02 07:50:14'),
(1, 16, '2026-10-02 07:50:14'),
(1, 17, '2026-10-02 07:50:14'),
(1, 18, '2026-10-02 07:50:14'),
(1, 19, '2026-10-02 07:50:14'),
(1, 20, '2026-10-02 07:50:14'),
(1, 21, '2026-10-02 07:50:14'),
(1, 22, '2026-10-02 07:50:14'),
(1, 23, '2026-10-02 07:50:14'),
(1, 24, '2026-10-02 07:50:14'),
(2, 1, '2026-08-19 13:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

CREATE TABLE `platform_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('default_currency', 'INR', '2026-08-19 13:13:04'),
('default_timezone', 'Asia/Kolkata', '2026-08-19 13:13:04'),
('platform_name', 'HRMS Cloud', '2026-08-19 13:13:04'),
('support_email', 'support@hrms.local', '2026-08-19 13:13:04');

-- --------------------------------------------------------

--
-- Table structure for table `platform_users`
--

CREATE TABLE `platform_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_users`
--

INSERT INTO `platform_users` (`id`, `name`, `email`, `password_hash`, `status`, `must_change_password`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Platform Super Admin', 'admin@hrms-platform.local', '$2y$10$dO/1TNVD.ZMhkCBMT8/grOZqJA6bNKMZX63jEl2Q/WffE6rrAvgqy', 'active', 1, '2026-10-05 06:16:04', '2026-08-19 12:52:16', '2026-10-05 06:16:04'),
(2, 'Support Rep', 'support@hrms-platform.local', '$2y$10$vcyD/bbikFV6m9aGfJhnzObT0Pe31TJHSzsDbhC9FB1yEBicoybRq', 'active', 1, '2026-08-24 12:54:17', '2026-08-19 13:13:22', '2026-08-24 12:54:17');

-- --------------------------------------------------------

--
-- Table structure for table `platform_user_roles`
--

CREATE TABLE `platform_user_roles` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_user_roles`
--

INSERT INTO `platform_user_roles` (`user_id`, `role_id`, `created_at`) VALUES
(1, 1, '2026-08-19 12:52:16'),
(2, 2, '2026-08-19 13:13:22');

-- --------------------------------------------------------

--
-- Table structure for table `provisioning_logs`
--

CREATE TABLE `provisioning_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `step` varchar(60) NOT NULL,
  `status` enum('started','completed','failed') NOT NULL,
  `message` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provisioning_logs`
--

INSERT INTO `provisioning_logs` (`id`, `company_id`, `step`, `status`, `message`, `created_at`) VALUES
(1, 2, 'create_database', 'started', NULL, '2026-08-20 04:57:30'),
(2, 2, 'create_database', 'completed', NULL, '2026-08-20 04:57:30'),
(3, 2, 'run_migrations', 'started', NULL, '2026-08-20 04:57:30'),
(4, 2, 'run_migrations', 'failed', 'CodeIgniter\\Config\\Services::curlrequest(): Argument #2 ($response) must be of type ?CodeIgniter\\HTTP\\ResponseInterface, bool given, called in F:\\xampp8.2\\htdocs\\superadmin\\vendor\\codeigniter4\\framework\\system\\Config\\BaseService.php on line', '2026-08-20 04:57:30'),
(5, 2, 'create_database', 'started', NULL, '2026-08-20 04:58:09'),
(6, 2, 'create_database', 'completed', NULL, '2026-08-20 04:58:09'),
(7, 2, 'run_migrations', 'started', NULL, '2026-08-20 04:58:09'),
(8, 2, 'run_migrations', 'failed', 'Could not reach the HRMS application for step \"migrate\".', '2026-08-20 04:58:12'),
(9, 2, 'create_database', 'started', NULL, '2026-08-20 04:59:01'),
(10, 2, 'create_database', 'completed', NULL, '2026-08-20 04:59:01'),
(11, 2, 'run_migrations', 'started', NULL, '2026-08-20 04:59:01'),
(12, 2, 'run_migrations', 'completed', NULL, '2026-08-20 04:59:03'),
(13, 2, 'seed_permissions_roles', 'started', NULL, '2026-08-20 04:59:03'),
(14, 2, 'seed_permissions_roles', 'completed', NULL, '2026-08-20 04:59:04'),
(15, 2, 'company_settings', 'started', NULL, '2026-08-20 04:59:04'),
(16, 2, 'company_settings', 'completed', NULL, '2026-08-20 04:59:04'),
(17, 2, 'create_admin', 'started', NULL, '2026-08-20 04:59:04'),
(18, 2, 'create_admin', 'completed', NULL, '2026-08-20 04:59:05'),
(19, 2, 'domain_mapping', 'started', NULL, '2026-08-20 04:59:05'),
(20, 2, 'domain_mapping', 'completed', NULL, '2026-08-20 04:59:05'),
(21, 2, 'verify', 'started', NULL, '2026-08-20 04:59:05'),
(22, 2, 'verify', 'completed', NULL, '2026-08-20 04:59:05'),
(23, 3, 'create_database', 'started', NULL, '2026-08-20 04:59:33'),
(24, 3, 'create_database', 'completed', NULL, '2026-08-20 04:59:34'),
(25, 3, 'run_migrations', 'started', NULL, '2026-08-20 04:59:34'),
(26, 3, 'run_migrations', 'completed', NULL, '2026-08-20 04:59:35'),
(27, 3, 'seed_permissions_roles', 'started', NULL, '2026-08-20 04:59:35'),
(28, 3, 'seed_permissions_roles', 'completed', NULL, '2026-08-20 04:59:36'),
(29, 3, 'company_settings', 'started', NULL, '2026-08-20 04:59:36'),
(30, 3, 'company_settings', 'completed', NULL, '2026-08-20 04:59:37'),
(31, 3, 'create_admin', 'started', NULL, '2026-08-20 04:59:37'),
(32, 3, 'create_admin', 'completed', NULL, '2026-08-20 04:59:37'),
(33, 3, 'domain_mapping', 'started', NULL, '2026-08-20 04:59:37'),
(34, 3, 'domain_mapping', 'completed', NULL, '2026-08-20 04:59:37'),
(35, 3, 'verify', 'started', NULL, '2026-08-20 04:59:37'),
(36, 3, 'verify', 'completed', NULL, '2026-08-20 04:59:37'),
(65, 9, 'verify_connection', 'started', NULL, '2026-10-05 05:07:05'),
(66, 9, 'verify_connection', 'completed', NULL, '2026-10-05 05:07:05'),
(67, 9, 'verify_schema', 'started', NULL, '2026-10-05 05:07:05'),
(68, 9, 'verify_schema', 'completed', NULL, '2026-10-05 05:07:05'),
(69, 9, 'verify_company_settings', 'started', NULL, '2026-10-05 05:07:05'),
(70, 9, 'verify_company_settings', 'completed', NULL, '2026-10-05 05:07:05'),
(71, 9, 'verify_rbac', 'started', NULL, '2026-10-05 05:07:05'),
(72, 9, 'verify_rbac', 'completed', NULL, '2026-10-05 05:07:05'),
(73, 9, 'issue_license', 'started', NULL, '2026-10-05 05:07:06'),
(74, 9, 'issue_license', 'completed', NULL, '2026-10-05 05:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL,
  `starts_at` date NOT NULL,
  `expires_at` date NOT NULL,
  `employee_limit_override` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('trial','active','expiring','expired','suspended','cancelled') NOT NULL DEFAULT 'trial',
  `remarks` varchar(255) DEFAULT NULL,
  `last_reminder_sent_at` datetime DEFAULT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `company_id`, `plan_id`, `starts_at`, `expires_at`, `employee_limit_override`, `status`, `remarks`, `last_reminder_sent_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-08-19', '2028-01-01', NULL, 'suspended', 'Initial subscription', NULL, 1, '2026-08-19 13:08:17', '2026-08-19 13:08:36'),
(2, 2, 2, '2026-08-20', '2026-10-10', NULL, 'trial', NULL, NULL, 1, '2026-08-20 04:57:14', '2026-09-28 06:49:00'),
(3, 3, 2, '2026-08-20', '2026-09-30', NULL, 'active', NULL, NULL, 1, '2026-08-20 04:59:25', '2026-09-28 06:48:21'),
(4, 4, 1, '2026-10-01', '2026-10-15', NULL, 'trial', NULL, NULL, 1, '2026-10-01 10:19:41', '2026-10-01 10:19:41'),
(5, 9, 1, '2026-10-05', '2026-10-04', NULL, 'trial', NULL, NULL, 1, '2026-10-05 04:32:39', '2026-10-05 06:21:59');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_history`
--

CREATE TABLE `subscription_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `subscription_id` int(11) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `performed_by` int(11) UNSIGNED DEFAULT NULL,
  `performed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_history`
--

INSERT INTO `subscription_history` (`id`, `subscription_id`, `action`, `old_values`, `new_values`, `performed_by`, `performed_at`) VALUES
(1, 1, 'created', NULL, '{\"company_id\":1,\"plan_id\":2,\"starts_at\":\"2026-08-19\",\"expires_at\":\"2027-08-19\",\"employee_limit_override\":null,\"status\":\"active\",\"remarks\":\"Initial subscription\",\"created_by\":1}', 1, '2026-08-19 13:08:17'),
(2, 1, 'extended', '{\"expires_at\":\"2027-08-19\"}', '{\"expires_at\":\"2028-01-01\"}', 1, '2026-08-19 13:08:35'),
(3, 1, 'status_change', '{\"status\":\"active\"}', '{\"status\":\"suspended\"}', 1, '2026-08-19 13:08:36'),
(4, 2, 'created', NULL, '{\"company_id\":2,\"plan_id\":\"2\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-20\",\"status\":\"trial\",\"created_by\":1}', 1, '2026-08-20 04:57:14'),
(5, 3, 'created', NULL, '{\"company_id\":3,\"plan_id\":\"1\",\"starts_at\":\"2026-08-20\",\"expires_at\":\"2026-09-20\",\"status\":\"active\",\"created_by\":1}', 1, '2026-08-20 04:59:25'),
(6, 3, 'extended', '{\"expires_at\":\"2026-09-20\"}', '{\"expires_at\":\"2026-09-30\"}', 1, '2026-09-28 06:47:09'),
(7, 3, 'plan_changed', '{\"plan_id\":\"1\"}', '{\"plan_id\":2,\"employee_limit_override\":null}', 1, '2026-09-28 06:48:21'),
(8, 2, 'extended', '{\"expires_at\":\"2026-09-20\"}', '{\"expires_at\":\"2026-10-10\"}', 1, '2026-09-28 06:49:00'),
(9, 4, 'created', NULL, '{\"company_id\":4,\"plan_id\":\"1\",\"starts_at\":\"2026-10-01\",\"expires_at\":\"2026-10-15\",\"status\":\"trial\",\"created_by\":1}', 1, '2026-10-01 10:19:41'),
(10, 5, 'created', NULL, '{\"company_id\":9,\"plan_id\":\"1\",\"starts_at\":\"2026-10-05\",\"expires_at\":\"2026-10-19\",\"status\":\"trial\",\"created_by\":1}', 1, '2026-10-05 04:32:39'),
(11, 5, 'extended', '{\"expires_at\":\"2026-10-19\"}', '{\"expires_at\":\"2026-10-04\"}', 1, '2026-10-05 06:21:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_user_id` (`platform_user_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `module` (`module`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `companies_plan_id_foreign` (`plan_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `company_database_connections`
--
ALTER TABLE `company_database_connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id` (`company_id`);

--
-- Indexes for table `company_domains`
--
ALTER TABLE `company_domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain` (`domain`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `company_module_overrides`
--
ALTER TABLE `company_module_overrides`
  ADD PRIMARY KEY (`company_id`,`module_id`),
  ADD KEY `company_module_overrides_module_id_foreign` (`module_id`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_uuid` (`license_uuid`),
  ADD UNIQUE KEY `license_key` (`license_key`),
  ADD KEY `licenses_plan_id_foreign` (`plan_id`),
  ADD KEY `company_id_status` (`company_id`,`status`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_user_id` (`platform_user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_key` (`module_key`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `plan_modules`
--
ALTER TABLE `plan_modules`
  ADD PRIMARY KEY (`plan_id`,`module_id`),
  ADD KEY `plan_modules_module_id_foreign` (`module_id`);

--
-- Indexes for table `platform_permissions`
--
ALTER TABLE `platform_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `module` (`module`);

--
-- Indexes for table `platform_roles`
--
ALTER TABLE `platform_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `platform_role_permissions`
--
ALTER TABLE `platform_role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `platform_role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `platform_users`
--
ALTER TABLE `platform_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `platform_user_roles`
--
ALTER TABLE `platform_user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `platform_user_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `provisioning_logs`
--
ALTER TABLE `provisioning_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_plan_id_foreign` (`plan_id`),
  ADD KEY `subscriptions_created_by_foreign` (`created_by`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `subscription_history`
--
ALTER TABLE `subscription_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_history_performed_by_foreign` (`performed_by`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `company_database_connections`
--
ALTER TABLE `company_database_connections`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `company_domains`
--
ALTER TABLE `company_domains`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `platform_permissions`
--
ALTER TABLE `platform_permissions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `platform_roles`
--
ALTER TABLE `platform_roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `platform_users`
--
ALTER TABLE `platform_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `provisioning_logs`
--
ALTER TABLE `provisioning_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subscription_history`
--
ALTER TABLE `subscription_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_logs_platform_user_id_foreign` FOREIGN KEY (`platform_user_id`) REFERENCES `platform_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

--
-- Constraints for table `company_database_connections`
--
ALTER TABLE `company_database_connections`
  ADD CONSTRAINT `company_database_connections_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_domains`
--
ALTER TABLE `company_domains`
  ADD CONSTRAINT `company_domains_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_module_overrides`
--
ALTER TABLE `company_module_overrides`
  ADD CONSTRAINT `company_module_overrides_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `company_module_overrides_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `licenses`
--
ALTER TABLE `licenses`
  ADD CONSTRAINT `licenses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `licenses_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_platform_user_id_foreign` FOREIGN KEY (`platform_user_id`) REFERENCES `platform_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `plan_modules`
--
ALTER TABLE `plan_modules`
  ADD CONSTRAINT `plan_modules_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plan_modules_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_role_permissions`
--
ALTER TABLE `platform_role_permissions`
  ADD CONSTRAINT `platform_role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `platform_permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `platform_role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `platform_roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_user_roles`
--
ALTER TABLE `platform_user_roles`
  ADD CONSTRAINT `platform_user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `platform_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `platform_user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `platform_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provisioning_logs`
--
ALTER TABLE `provisioning_logs`
  ADD CONSTRAINT `provisioning_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `platform_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

--
-- Constraints for table `subscription_history`
--
ALTER TABLE `subscription_history`
  ADD CONSTRAINT `subscription_history_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `platform_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `subscription_history_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
