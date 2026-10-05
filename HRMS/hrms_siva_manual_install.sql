-- ============================================================================
-- HRMS tenant installation: company "siva" (platform company ID 9)
-- Target database: hrms_siva   (MariaDB 10.4 / MySQL, utf8mb4)
-- Import into an EMPTY database that you have already created and selected.
-- Schema source: current HRMS schema (all 83 migrations, 68 tables).
-- Contains: schema, system RBAC, master/default data, company settings,
--           one initial company administrator (no usable password yet).
-- Contains NO other tenant's transactional/business data.
-- ============================================================================
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1. SCHEMA
-- ----------------------------------------------------------------------------
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `shift_id` int(11) unsigned DEFAULT NULL,
  `first_punch_in_at` datetime DEFAULT NULL,
  `last_punch_out_at` datetime DEFAULT NULL,
  `working_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `late_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `early_exit_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `status` enum('present','absent','half_day','holiday','weekly_off','leave','on_duty','work_from_home','late','missed_punch','half_day_leave','lop') DEFAULT 'present',
  `source` enum('gps','manual','biometric','regularized','leave') DEFAULT 'manual',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id_attendance_date` (`employee_id`,`attendance_date`),
  KEY `attendance_shift_id_foreign` (`shift_id`),
  KEY `attendance_date` (`attendance_date`),
  KEY `status` (`status`),
  CONSTRAINT `attendance_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `attendance_shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_biometric_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `device_serial` varchar(100) NOT NULL,
  `employee_code` varchar(30) NOT NULL,
  `punch_time` datetime NOT NULL,
  `punch_type` enum('in','out') DEFAULT NULL,
  `raw_payload` text DEFAULT NULL,
  `sync_status` enum('pending','synced','failed','ignored') NOT NULL DEFAULT 'pending',
  `synced_employee_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_biometric_logs_synced_employee_id_foreign` (`synced_employee_id`),
  KEY `employee_code` (`employee_code`),
  KEY `sync_status` (`sync_status`),
  CONSTRAINT `attendance_biometric_logs_synced_employee_id_foreign` FOREIGN KEY (`synced_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_devices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `device_uid` varchar(100) NOT NULL,
  `device_name` varchar(150) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `first_login_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected','blocked') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id_device_uid` (`employee_id`,`device_uid`),
  CONSTRAINT `attendance_devices_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_holidays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `date` date NOT NULL,
  `holiday_type` enum('public','restricted','company') NOT NULL DEFAULT 'public',
  `branch_id` int(11) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_optional` tinyint(1) NOT NULL DEFAULT 0,
  `is_annual` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `attendance_holidays_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `branch_id` int(11) unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `radius_meters` int(6) unsigned NOT NULL DEFAULT 200,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `attendance_locations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `attendance_id` int(11) unsigned DEFAULT NULL,
  `punch_type` enum('in','out') NOT NULL,
  `punch_time` datetime NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `accuracy_meters` decimal(8,2) DEFAULT NULL,
  `device_id` int(11) unsigned DEFAULT NULL,
  `location_id` int(11) unsigned DEFAULT NULL,
  `distance_meters` decimal(10,2) DEFAULT NULL,
  `geofence_status` enum('inside','outside','gps_disabled','low_accuracy','not_applicable') NOT NULL DEFAULT 'not_applicable',
  `ip_address` varchar(45) DEFAULT NULL,
  `source` enum('gps','manual','biometric') NOT NULL DEFAULT 'gps',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_logs_attendance_id_foreign` (`attendance_id`),
  KEY `attendance_logs_device_id_foreign` (`device_id`),
  KEY `attendance_logs_location_id_foreign` (`location_id`),
  KEY `employee_id_punch_time` (`employee_id`,`punch_time`),
  CONSTRAINT `attendance_logs_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_logs_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `attendance_devices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_logs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `attendance_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_overtime` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `shift_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(6) unsigned NOT NULL DEFAULT 0,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id_attendance_date` (`employee_id`,`attendance_date`),
  CONSTRAINT `attendance_overtime_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_regularizations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `requested_punch_in` time DEFAULT NULL,
  `requested_punch_out` time DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id_attendance_date` (`employee_id`,`attendance_date`),
  CONSTRAINT `attendance_regularizations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `default_shift_id` int(11) unsigned DEFAULT NULL,
  `grace_minutes` int(5) unsigned NOT NULL DEFAULT 10,
  `late_mark_minutes` int(5) unsigned NOT NULL DEFAULT 15,
  `half_day_minutes` int(6) unsigned NOT NULL DEFAULT 240,
  `full_day_minutes` int(6) unsigned NOT NULL DEFAULT 480,
  `gps_required` tinyint(1) NOT NULL DEFAULT 1,
  `device_approval_required` tinyint(1) NOT NULL DEFAULT 0,
  `self_attendance_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `overtime_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `weekend_policy` varchar(100) NOT NULL DEFAULT 'Sunday Off',
  `holiday_policy` varchar(100) NOT NULL DEFAULT 'Paid',
  `timezone` varchar(64) NOT NULL DEFAULT 'Asia/Kolkata',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_settings_default_shift_id_foreign` (`default_shift_id`),
  CONSTRAINT `attendance_settings_default_shift_id_foreign` FOREIGN KEY (`default_shift_id`) REFERENCES `attendance_shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_shift_assignments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `shift_id` int(11) unsigned NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_shift_assignments_shift_id_foreign` (`shift_id`),
  KEY `employee_id_effective_from` (`employee_id`,`effective_from`),
  CONSTRAINT `attendance_shift_assignments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_shift_assignments_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `attendance_shifts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_shifts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `grace_minutes` int(5) unsigned NOT NULL DEFAULT 0,
  `late_minutes` int(5) unsigned NOT NULL DEFAULT 0,
  `half_day_minutes` int(6) unsigned NOT NULL DEFAULT 240,
  `full_day_minutes` int(6) unsigned NOT NULL DEFAULT 480,
  `is_night_shift` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `attendance_weekly_offs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `day_of_week` enum('sunday','monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `week_pattern` enum('every','first','second','third','fourth','fifth','alternate') NOT NULL DEFAULT 'every',
  `branch_id` int(11) unsigned DEFAULT NULL,
  `shift_id` int(11) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_weekly_offs_shift_id_foreign` (`shift_id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `attendance_weekly_offs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_weekly_offs_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `attendance_shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `employee_id` int(11) unsigned DEFAULT NULL,
  `action` varchar(40) NOT NULL,
  `module` varchar(60) NOT NULL,
  `record_type` varchar(60) DEFAULT NULL,
  `record_id` int(11) unsigned DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `module` (`module`),
  KEY `idx_audit_logs_module_record_id` (`module`,`record_id`),
  KEY `idx_audit_logs_employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `branches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(30) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `manager_user_id` int(11) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `company_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `favicon_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `theme` varchar(10) DEFAULT 'light',
  `accent_color` varchar(7) DEFAULT '#1f6f5c',
  `font_family` varchar(40) DEFAULT 'system',
  `default_language` varchar(10) DEFAULT 'en',
  `week_start_day` tinyint(1) DEFAULT 1,
  `timezone` varchar(64) NOT NULL DEFAULT 'Asia/Kolkata',
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `date_format` varchar(20) NOT NULL DEFAULT 'd-m-Y',
  `time_format` varchar(10) NOT NULL DEFAULT '24h',
  `require_email_verification` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `permissions_version` int(11) unsigned DEFAULT 1,
  `employee_code_prefix` varchar(10) NOT NULL DEFAULT 'EMP',
  `employee_code_next_seq` int(11) unsigned NOT NULL DEFAULT 1,
  `primary_color` varchar(7) DEFAULT NULL,
  `secondary_color` varchar(7) DEFAULT NULL,
  `success_color` varchar(7) DEFAULT NULL,
  `warning_color` varchar(7) DEFAULT NULL,
  `danger_color` varchar(7) DEFAULT NULL,
  `info_color` varchar(7) DEFAULT NULL,
  `sidebar_bg_color` varchar(7) DEFAULT NULL,
  `sidebar_active_color` varchar(7) DEFAULT NULL,
  `sidebar_hover_color` varchar(7) DEFAULT NULL,
  `header_bg_color` varchar(7) DEFAULT NULL,
  `card_accent_color` varchar(7) DEFAULT NULL,
  `button_radius` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(30) NOT NULL,
  `branch_id` int(11) unsigned NOT NULL,
  `head_user_id` int(11) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `departments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `designations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `department_id` int(11) unsigned NOT NULL,
  `level` int(5) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `designations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_addresses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `address_type` enum('permanent','current') NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(12) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id_address_type` (`employee_id`,`address_type`),
  CONSTRAINT `employee_addresses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_bank_accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `account_holder_name` varchar(150) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `branch_name` varchar(150) DEFAULT NULL,
  `account_number` varchar(30) NOT NULL,
  `ifsc_code` varchar(15) NOT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_bank_accounts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_documents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `document_type` enum('aadhaar','pan','passport','driving_license','resume','appointment_letter','offer_letter','education_certificate','experience_certificate','other') NOT NULL,
  `document_number` varchar(60) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) unsigned NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `remarks` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `document_type` (`document_type`),
  CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_education` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `qualification` varchar(150) NOT NULL,
  `institution` varchar(200) NOT NULL,
  `board_university` varchar(200) DEFAULT NULL,
  `percentage_cgpa` varchar(20) DEFAULT NULL,
  `year_of_passing` smallint(6) unsigned DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_education_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_emergency_contacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `relationship` varchar(60) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `priority` int(3) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_emergency_contacts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_experience` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `from_date` date NOT NULL,
  `to_date` date DEFAULT NULL,
  `years_experience` decimal(4,1) DEFAULT NULL,
  `reason_for_leaving` text DEFAULT NULL,
  `experience_letter_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_experience_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_family_members` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `relationship` varchar(60) NOT NULL,
  `name` varchar(150) NOT NULL,
  `dob` date DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `is_dependent` tinyint(1) NOT NULL DEFAULT 0,
  `is_nominee` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_family_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_leave_balances` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `leave_type_id` int(11) unsigned NOT NULL,
  `leave_policy_id` int(11) unsigned DEFAULT NULL,
  `financial_year` int(4) NOT NULL,
  `opening_balance` decimal(6,2) NOT NULL DEFAULT 0.00,
  `earned` decimal(6,2) NOT NULL DEFAULT 0.00,
  `availed` decimal(6,2) NOT NULL DEFAULT 0.00,
  `adjusted` decimal(6,2) NOT NULL DEFAULT 0.00,
  `carry_forward_in` decimal(6,2) NOT NULL DEFAULT 0.00,
  `carry_forward_out` decimal(6,2) NOT NULL DEFAULT 0.00,
  `encashed` decimal(6,2) NOT NULL DEFAULT 0.00,
  `closing_balance` decimal(6,2) NOT NULL DEFAULT 0.00,
  `last_transaction_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id_leave_type_id_financial_year` (`employee_id`,`leave_type_id`,`financial_year`),
  KEY `employee_leave_balances_leave_policy_id_foreign` (`leave_policy_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `financial_year` (`financial_year`),
  CONSTRAINT `employee_leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_leave_balances_leave_policy_id_foreign` FOREIGN KEY (`leave_policy_id`) REFERENCES `leave_policies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employee_status_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `event_type` enum('joined','confirmed','promoted','transferred','department_changed','manager_changed','on_notice','relieved','terminated','rejoined','suspended','activated') NOT NULL,
  `from_value` varchar(255) DEFAULT NULL,
  `to_value` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `changed_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_status_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `aadhaar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `passport_number` varchar(30) DEFAULT NULL,
  `driving_license_number` varchar(30) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `alternate_mobile` varchar(20) DEFAULT NULL,
  `personal_email` varchar(150) DEFAULT NULL,
  `company_email` varchar(150) DEFAULT NULL,
  `branch_id` int(11) unsigned NOT NULL,
  `department_id` int(11) unsigned NOT NULL,
  `designation_id` int(11) unsigned NOT NULL,
  `reporting_manager_id` int(11) unsigned DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contract','intern','consultant') NOT NULL DEFAULT 'full_time',
  `employment_category` varchar(100) DEFAULT NULL,
  `shift` varchar(100) DEFAULT NULL,
  `work_location` varchar(150) DEFAULT NULL,
  `date_of_joining` date NOT NULL,
  `date_of_confirmation` date DEFAULT NULL,
  `probation_period_months` int(3) unsigned DEFAULT NULL,
  `status` enum('active','probation','notice_period','suspended','resigned','terminated','retired','absconded','relieved') NOT NULL DEFAULT 'probation',
  `photo_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  UNIQUE KEY `company_email` (`company_email`),
  UNIQUE KEY `personal_email` (`personal_email`),
  KEY `branch_id` (`branch_id`),
  KEY `department_id` (`department_id`),
  KEY `designation_id` (`designation_id`),
  KEY `reporting_manager_id` (`reporting_manager_id`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`),
  CONSTRAINT `employees_reporting_manager_id_foreign` FOREIGN KEY (`reporting_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_application_days` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `leave_application_id` int(11) unsigned NOT NULL,
  `leave_date` date NOT NULL,
  `day_type` enum('full','half_first','half_second') NOT NULL DEFAULT 'full',
  `day_category` enum('working','weekend','holiday') NOT NULL DEFAULT 'working',
  `is_sandwiched` tinyint(1) NOT NULL DEFAULT 0,
  `counts_as_leave` tinyint(1) NOT NULL DEFAULT 1,
  `day_value` decimal(3,1) NOT NULL DEFAULT 1.0,
  `holiday_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_application_id_leave_date` (`leave_application_id`,`leave_date`),
  KEY `leave_application_days_holiday_id_foreign` (`holiday_id`),
  KEY `leave_date` (`leave_date`),
  CONSTRAINT `leave_application_days_holiday_id_foreign` FOREIGN KEY (`holiday_id`) REFERENCES `attendance_holidays` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_application_days_leave_application_id_foreign` FOREIGN KEY (`leave_application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_applications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `leave_type_id` int(11) unsigned NOT NULL,
  `leave_policy_id` int(11) unsigned DEFAULT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `is_half_day` tinyint(1) NOT NULL DEFAULT 0,
  `half_day_session` enum('first_half','second_half') DEFAULT NULL,
  `total_days` decimal(5,1) NOT NULL DEFAULT 0.0,
  `reason` varchar(500) NOT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `is_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `current_level` enum('level1','level2','completed') NOT NULL DEFAULT 'level1',
  `level1_approver_id` int(11) unsigned DEFAULT NULL,
  `level1_status` enum('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending',
  `level1_acted_by` int(11) unsigned DEFAULT NULL,
  `level1_acted_at` datetime DEFAULT NULL,
  `level1_remarks` varchar(500) DEFAULT NULL,
  `level2_status` enum('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending',
  `level2_acted_by` int(11) unsigned DEFAULT NULL,
  `level2_acted_at` datetime DEFAULT NULL,
  `level2_remarks` varchar(500) DEFAULT NULL,
  `balance_deducted` tinyint(1) NOT NULL DEFAULT 0,
  `attendance_applied` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled_by` int(11) unsigned DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` varchar(500) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_applications_leave_policy_id_foreign` (`leave_policy_id`),
  KEY `leave_applications_level1_approver_id_foreign` (`level1_approver_id`),
  KEY `leave_applications_level1_acted_by_foreign` (`level1_acted_by`),
  KEY `leave_applications_level2_acted_by_foreign` (`level2_acted_by`),
  KEY `leave_applications_cancelled_by_foreign` (`cancelled_by`),
  KEY `leave_applications_created_by_foreign` (`created_by`),
  KEY `leave_applications_updated_by_foreign` (`updated_by`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `status` (`status`),
  KEY `from_date` (`from_date`),
  KEY `to_date` (`to_date`),
  KEY `employee_id_status` (`employee_id`,`status`),
  CONSTRAINT `leave_applications_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_applications_leave_policy_id_foreign` FOREIGN KEY (`leave_policy_id`) REFERENCES `leave_policies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  CONSTRAINT `leave_applications_level1_acted_by_foreign` FOREIGN KEY (`level1_acted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_level1_approver_id_foreign` FOREIGN KEY (`level1_approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_level2_acted_by_foreign` FOREIGN KEY (`level2_acted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_applications_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_approval_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `leave_application_id` int(11) unsigned NOT NULL,
  `action` enum('submitted','level1_approved','level1_rejected','level2_approved','level2_rejected','admin_override_approved','admin_override_rejected','cancelled','delegation_assigned') NOT NULL,
  `level` enum('level1','level2','admin','system') DEFAULT NULL,
  `actor_id` int(11) unsigned DEFAULT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `override_reason` varchar(500) DEFAULT NULL,
  `snapshot_status` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_approval_history_actor_id_foreign` (`actor_id`),
  KEY `leave_application_id` (`leave_application_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `leave_approval_history_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_approval_history_leave_application_id_foreign` FOREIGN KEY (`leave_application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_carry_forward_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `leave_type_id` int(11) unsigned NOT NULL,
  `from_financial_year` int(4) NOT NULL,
  `to_financial_year` int(4) NOT NULL,
  `eligible_balance` decimal(6,2) NOT NULL,
  `carried_forward_days` decimal(6,2) NOT NULL,
  `expired_days` decimal(6,2) NOT NULL DEFAULT 0.00,
  `carry_forward_rule_limit` decimal(5,1) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `processed_by` int(11) unsigned DEFAULT NULL,
  `run_batch_id` varchar(40) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_carry_forward_history_processed_by_foreign` (`processed_by`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `from_financial_year_to_financial_year` (`from_financial_year`,`to_financial_year`),
  KEY `run_batch_id` (`run_batch_id`),
  CONSTRAINT `leave_carry_forward_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_carry_forward_history_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_carry_forward_history_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_delegations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `leave_application_id` int(11) unsigned NOT NULL,
  `delegate_employee_id` int(11) unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_application_id` (`leave_application_id`),
  KEY `leave_delegations_created_by_foreign` (`created_by`),
  KEY `delegate_employee_id` (`delegate_employee_id`),
  CONSTRAINT `leave_delegations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_delegations_delegate_employee_id_foreign` FOREIGN KEY (`delegate_employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `leave_delegations_leave_application_id_foreign` FOREIGN KEY (`leave_application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_encashments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `leave_type_id` int(11) unsigned NOT NULL,
  `financial_year` int(4) NOT NULL,
  `days_encashed` decimal(5,1) NOT NULL,
  `amount_placeholder` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_by` int(11) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_encashments_approved_by_foreign` (`approved_by`),
  KEY `leave_encashments_created_by_foreign` (`created_by`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `financial_year` (`financial_year`),
  KEY `status` (`status`),
  CONSTRAINT `leave_encashments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_encashments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_encashments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_encashments_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_policies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `branch_id` int(11) unsigned DEFAULT NULL,
  `department_id` int(11) unsigned DEFAULT NULL,
  `designation_id` int(11) unsigned DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contract','intern','consultant') DEFAULT NULL,
  `employee_id` int(11) unsigned DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `priority` int(5) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `department_id` (`department_id`),
  KEY `designation_id` (`designation_id`),
  KEY `employee_id` (`employee_id`),
  KEY `status` (`status`),
  CONSTRAINT `leave_policies_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_policies_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_policies_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_policies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_policy_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `leave_policy_id` int(11) unsigned NOT NULL,
  `leave_type_id` int(11) unsigned NOT NULL,
  `annual_allocation` decimal(5,1) NOT NULL DEFAULT 0.0,
  `accrual_method` enum('annual','monthly') NOT NULL DEFAULT 'annual',
  `monthly_accrual_days` decimal(4,2) NOT NULL DEFAULT 0.00,
  `carry_forward_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `carry_forward_limit` decimal(5,1) DEFAULT NULL,
  `carry_forward_unlimited` tinyint(1) NOT NULL DEFAULT 0,
  `encashment_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `max_consecutive_days` int(4) unsigned DEFAULT NULL,
  `min_days_per_application` decimal(3,1) NOT NULL DEFAULT 0.5,
  `max_applications_per_year` int(4) unsigned DEFAULT NULL,
  `sandwich_rule_applicable` tinyint(1) NOT NULL DEFAULT 1,
  `notice_period_days` int(3) unsigned DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_policy_id_leave_type_id` (`leave_policy_id`,`leave_type_id`),
  KEY `leave_policy_rules_leave_type_id_foreign` (`leave_type_id`),
  CONSTRAINT `leave_policy_rules_leave_policy_id_foreign` FOREIGN KEY (`leave_policy_id`) REFERENCES `leave_policies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_policy_rules_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `financial_year_start_month` int(2) NOT NULL DEFAULT 1,
  `leave_year_start_month` int(2) NOT NULL DEFAULT 1,
  `half_day_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sandwich_leave_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `holiday_between_leave_policy` enum('count','not_count') NOT NULL DEFAULT 'not_count',
  `weekly_off_between_leave_policy` enum('count','not_count') NOT NULL DEFAULT 'not_count',
  `carry_forward_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `carry_forward_limit` decimal(5,1) NOT NULL DEFAULT 0.0,
  `carry_forward_expiry_month` int(2) DEFAULT NULL,
  `leave_encashment_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `max_consecutive_leave` int(4) DEFAULT NULL,
  `min_notice_days` int(3) NOT NULL DEFAULT 0,
  `max_future_apply_days` int(4) NOT NULL DEFAULT 90,
  `allow_negative_balance` tinyint(1) NOT NULL DEFAULT 0,
  `self_approval_allowed_for_admin` tinyint(1) NOT NULL DEFAULT 1,
  `half_day_hours` decimal(3,1) NOT NULL DEFAULT 4.0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#4B3FD1',
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `annual_allocation` decimal(5,1) NOT NULL DEFAULT 0.0,
  `half_day_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `attachment_required` tinyint(1) NOT NULL DEFAULT 0,
  `medical_certificate_required` tinyint(1) NOT NULL DEFAULT 0,
  `carry_forward_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `encashment_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `attendance_status_map` enum('leave','lop','work_from_home','on_duty') NOT NULL DEFAULT 'leave',
  `sort_order` int(5) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `idx_login_logs_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` varchar(500) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_read_at` (`user_id`,`read_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `password_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_created_at` (`user_id`,`created_at`),
  CONSTRAINT `password_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_adjustments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_run_item_id` int(11) unsigned NOT NULL,
  `type` enum('earning','deduction') NOT NULL,
  `label` varchar(150) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_adjustments_created_by_foreign` (`created_by`),
  KEY `payroll_run_item_id` (`payroll_run_item_id`),
  CONSTRAINT `payroll_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_adjustments_payroll_run_item_id_foreign` FOREIGN KEY (`payroll_run_item_id`) REFERENCES `payroll_run_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_advances` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `advance_date` date NOT NULL,
  `recovery_type` enum('lump_sum','installments') NOT NULL DEFAULT 'installments',
  `installments_count` smallint(3) unsigned NOT NULL DEFAULT 1,
  `installment_amount` decimal(10,2) NOT NULL,
  `recovered_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remaining_balance` decimal(12,2) NOT NULL,
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  `reason` varchar(500) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_advances_created_by_foreign` (`created_by`),
  KEY `payroll_advances_updated_by_foreign` (`updated_by`),
  KEY `employee_id_status` (`employee_id`,`status`),
  CONSTRAINT `payroll_advances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_advances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_advances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_arrears` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `from_month` tinyint(2) unsigned NOT NULL,
  `from_year` smallint(4) unsigned NOT NULL,
  `to_month` tinyint(2) unsigned NOT NULL,
  `to_year` smallint(4) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `payroll_month_id` int(11) unsigned DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_arrears_payroll_month_id_foreign` (`payroll_month_id`),
  KEY `payroll_arrears_created_by_foreign` (`created_by`),
  KEY `payroll_arrears_updated_by_foreign` (`updated_by`),
  KEY `employee_id_payroll_month_id` (`employee_id`,`payroll_month_id`),
  CONSTRAINT `payroll_arrears_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_arrears_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_arrears_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_arrears_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_bonus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_batch_id` varchar(40) DEFAULT NULL,
  `employee_id` int(11) unsigned NOT NULL,
  `bonus_type` enum('festival','annual','performance','other') NOT NULL DEFAULT 'other',
  `amount` decimal(10,2) NOT NULL,
  `payroll_month_id` int(11) unsigned DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_bonus_payroll_month_id_foreign` (`payroll_month_id`),
  KEY `payroll_bonus_created_by_foreign` (`created_by`),
  KEY `payroll_bonus_updated_by_foreign` (`updated_by`),
  KEY `bonus_batch_id` (`bonus_batch_id`),
  KEY `employee_id_payroll_month_id` (`employee_id`,`payroll_month_id`),
  CONSTRAINT `payroll_bonus_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_bonus_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_bonus_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_bonus_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_employee_salary` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `salary_structure_id` int(11) unsigned NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `gross_salary` decimal(12,2) NOT NULL,
  `ctc` decimal(12,2) NOT NULL,
  `status` enum('scheduled','active','superseded') NOT NULL DEFAULT 'active',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_employee_salary_created_by_foreign` (`created_by`),
  KEY `payroll_employee_salary_updated_by_foreign` (`updated_by`),
  KEY `employee_id_status` (`employee_id`,`status`),
  KEY `effective_from` (`effective_from`),
  KEY `salary_structure_id` (`salary_structure_id`),
  CONSTRAINT `payroll_employee_salary_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_employee_salary_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_employee_salary_salary_structure_id_foreign` FOREIGN KEY (`salary_structure_id`) REFERENCES `payroll_salary_structures` (`id`),
  CONSTRAINT `payroll_employee_salary_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_esi_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_percentage` decimal(5,2) NOT NULL DEFAULT 0.75,
  `employer_percentage` decimal(5,2) NOT NULL DEFAULT 3.25,
  `wage_ceiling` decimal(10,2) NOT NULL DEFAULT 21000.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_incentives` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `incentive_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payroll_month_id` int(11) unsigned DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_incentives_payroll_month_id_foreign` (`payroll_month_id`),
  KEY `payroll_incentives_created_by_foreign` (`created_by`),
  KEY `payroll_incentives_updated_by_foreign` (`updated_by`),
  KEY `employee_id_payroll_month_id` (`employee_id`,`payroll_month_id`),
  CONSTRAINT `payroll_incentives_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_incentives_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_incentives_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_incentives_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_loan_installments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) unsigned NOT NULL,
  `installment_no` smallint(3) unsigned NOT NULL,
  `due_year` smallint(4) unsigned NOT NULL,
  `due_month` tinyint(2) unsigned NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL,
  `principal_component` decimal(10,2) NOT NULL DEFAULT 0.00,
  `interest_component` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','deducted','skipped') NOT NULL DEFAULT 'pending',
  `payroll_run_item_id` int(11) unsigned DEFAULT NULL,
  `deducted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_id_installment_no` (`loan_id`,`installment_no`),
  KEY `payroll_loan_installments_payroll_run_item_id_foreign` (`payroll_run_item_id`),
  KEY `due_year_due_month_status` (`due_year`,`due_month`,`status`),
  CONSTRAINT `payroll_loan_installments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `payroll_loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_loan_installments_payroll_run_item_id_foreign` FOREIGN KEY (`payroll_run_item_id`) REFERENCES `payroll_run_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_loans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `loan_number` varchar(30) NOT NULL,
  `loan_type` varchar(100) NOT NULL,
  `principal_amount` decimal(12,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tenure_months` smallint(3) unsigned NOT NULL,
  `emi_amount` decimal(10,2) NOT NULL,
  `start_month` tinyint(2) unsigned NOT NULL,
  `start_year` smallint(4) unsigned NOT NULL,
  `outstanding_balance` decimal(12,2) NOT NULL,
  `status` enum('active','closed','foreclosed') NOT NULL DEFAULT 'active',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `loan_number` (`loan_number`),
  KEY `payroll_loans_created_by_foreign` (`created_by`),
  KEY `payroll_loans_updated_by_foreign` (`updated_by`),
  KEY `employee_id_status` (`employee_id`,`status`),
  CONSTRAINT `payroll_loans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_loans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_months` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `month` tinyint(2) unsigned NOT NULL,
  `year` smallint(4) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','generated','approved','locked','paid') NOT NULL DEFAULT 'draft',
  `approved_by` int(11) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `locked_by` int(11) unsigned DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `month_year` (`month`,`year`),
  KEY `payroll_months_approved_by_foreign` (`approved_by`),
  KEY `payroll_months_locked_by_foreign` (`locked_by`),
  KEY `status` (`status`),
  CONSTRAINT `payroll_months_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_months_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_payslips` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_run_item_id` int(11) unsigned NOT NULL,
  `employee_id` int(11) unsigned NOT NULL,
  `payroll_month_id` int(11) unsigned NOT NULL,
  `payslip_number` varchar(50) NOT NULL,
  `generated_at` datetime DEFAULT NULL,
  `downloaded_at` datetime DEFAULT NULL,
  `downloaded_count` int(6) unsigned NOT NULL DEFAULT 0,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_run_item_id` (`payroll_run_item_id`),
  UNIQUE KEY `payslip_number` (`payslip_number`),
  KEY `payroll_payslips_created_by_foreign` (`created_by`),
  KEY `employee_id` (`employee_id`),
  KEY `payroll_month_id` (`payroll_month_id`),
  CONSTRAINT `payroll_payslips_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_payslips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_payslips_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_payslips_payroll_run_item_id_foreign` FOREIGN KEY (`payroll_run_item_id`) REFERENCES `payroll_run_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_pf_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_percentage` decimal(5,2) NOT NULL DEFAULT 12.00,
  `employer_percentage` decimal(5,2) NOT NULL DEFAULT 12.00,
  `wage_ceiling` decimal(10,2) NOT NULL DEFAULT 15000.00,
  `pf_wage_basis` enum('basic','basic_da','gross') NOT NULL DEFAULT 'basic',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_professional_tax_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `state` varchar(50) NOT NULL,
  `min_gross` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_gross` decimal(10,2) DEFAULT NULL,
  `tax_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_reimbursements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) unsigned NOT NULL,
  `expense_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `payroll_month_id` int(11) unsigned DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_reimbursements_approved_by_foreign` (`approved_by`),
  KEY `payroll_reimbursements_created_by_foreign` (`created_by`),
  KEY `payroll_reimbursements_updated_by_foreign` (`updated_by`),
  KEY `employee_id_status` (`employee_id`,`status`),
  KEY `payroll_month_id` (`payroll_month_id`),
  CONSTRAINT `payroll_reimbursements_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_reimbursements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_reimbursements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_reimbursements_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_reimbursements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_run_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_run_id` int(11) unsigned NOT NULL,
  `payroll_month_id` int(11) unsigned NOT NULL,
  `employee_id` int(11) unsigned NOT NULL,
  `salary_structure_id` int(11) unsigned DEFAULT NULL,
  `settlement_type` enum('regular','final') NOT NULL DEFAULT 'regular',
  `working_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `present_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `paid_leave_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `lop_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `half_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `overtime_hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `overtime_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gross_earnings` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gross_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pf_employee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pf_employer` decimal(10,2) NOT NULL DEFAULT 0.00,
  `esi_employee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `esi_employer` decimal(10,2) NOT NULL DEFAULT 0.00,
  `professional_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tds` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loan_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonus_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `incentive_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reimbursement_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `arrears_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `earnings_breakdown` text DEFAULT NULL,
  `deductions_breakdown` text DEFAULT NULL,
  `status` enum('draft','approved','locked','paid','hold') NOT NULL DEFAULT 'draft',
  `remarks` varchar(500) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_run_id_employee_id` (`payroll_run_id`,`employee_id`),
  KEY `payroll_run_items_salary_structure_id_foreign` (`salary_structure_id`),
  KEY `payroll_run_items_created_by_foreign` (`created_by`),
  KEY `payroll_run_items_updated_by_foreign` (`updated_by`),
  KEY `employee_id_status` (`employee_id`,`status`),
  KEY `payroll_month_id` (`payroll_month_id`),
  CONSTRAINT `payroll_run_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_run_items_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_run_items_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_run_items_payroll_run_id_foreign` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_run_items_salary_structure_id_foreign` FOREIGN KEY (`salary_structure_id`) REFERENCES `payroll_salary_structures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_run_items_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_runs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_month_id` int(11) unsigned NOT NULL,
  `run_type` enum('regular','off_cycle') NOT NULL DEFAULT 'regular',
  `status` enum('draft','generated','approved','locked','paid','cancelled') NOT NULL DEFAULT 'draft',
  `total_employees` int(6) unsigned NOT NULL DEFAULT 0,
  `total_gross` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_net` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_pf` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_esi` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_pt` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_tds` decimal(14,2) NOT NULL DEFAULT 0.00,
  `generated_by` int(11) unsigned DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL,
  `approved_by` int(11) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `locked_by` int(11) unsigned DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `paid_by` int(11) unsigned DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_runs_generated_by_foreign` (`generated_by`),
  KEY `payroll_runs_approved_by_foreign` (`approved_by`),
  KEY `payroll_runs_locked_by_foreign` (`locked_by`),
  KEY `payroll_runs_paid_by_foreign` (`paid_by`),
  KEY `payroll_month_id_status` (`payroll_month_id`,`status`),
  CONSTRAINT `payroll_runs_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_runs_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_runs_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_runs_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_runs_payroll_month_id_foreign` FOREIGN KEY (`payroll_month_id`) REFERENCES `payroll_months` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_salary_components` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(30) NOT NULL,
  `type` enum('earning','deduction') NOT NULL,
  `calculation_type` enum('fixed','percentage','formula') NOT NULL DEFAULT 'fixed',
  `percentage_of` varchar(30) DEFAULT NULL,
  `formula` varchar(255) DEFAULT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
  `pf_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `esi_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(5) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `payroll_salary_components_created_by_foreign` (`created_by`),
  KEY `payroll_salary_components_updated_by_foreign` (`updated_by`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  CONSTRAINT `payroll_salary_components_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_salary_components_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_salary_structure_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `salary_structure_id` int(11) unsigned NOT NULL,
  `salary_component_id` int(11) unsigned NOT NULL,
  `calculation_type` enum('fixed','percentage','formula') NOT NULL DEFAULT 'fixed',
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `formula` varchar(255) DEFAULT NULL,
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(5) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salary_structure_id_salary_component_id` (`salary_structure_id`,`salary_component_id`),
  KEY `salary_component_id` (`salary_component_id`),
  CONSTRAINT `payroll_salary_structure_items_salary_component_id_foreign` FOREIGN KEY (`salary_component_id`) REFERENCES `payroll_salary_components` (`id`),
  CONSTRAINT `payroll_salary_structure_items_salary_structure_id_foreign` FOREIGN KEY (`salary_structure_id`) REFERENCES `payroll_salary_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_salary_structures` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `effective_from` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_salary_structures_created_by_foreign` (`created_by`),
  KEY `payroll_salary_structures_updated_by_foreign` (`updated_by`),
  KEY `name` (`name`),
  KEY `status` (`status`),
  CONSTRAINT `payroll_salary_structures_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_salary_structures_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_start_day` tinyint(2) unsigned NOT NULL DEFAULT 1,
  `payroll_end_day` tinyint(2) unsigned NOT NULL DEFAULT 31,
  `salary_payment_day` tinyint(2) unsigned NOT NULL DEFAULT 7,
  `financial_year_start_month` tinyint(2) unsigned NOT NULL DEFAULT 4,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `working_days_basis` enum('calendar','fixed') NOT NULL DEFAULT 'calendar',
  `fixed_working_days` tinyint(2) unsigned NOT NULL DEFAULT 30,
  `overtime_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `overtime_multiplier` decimal(3,2) NOT NULL DEFAULT 1.50,
  `overtime_rate_basis` enum('basic','gross') NOT NULL DEFAULT 'basic',
  `lop_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `lop_deduction_basis` enum('basic','gross') NOT NULL DEFAULT 'gross',
  `pf_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `esi_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `pt_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `tds_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `payslip_prefix` varchar(20) NOT NULL DEFAULT 'PAY',
  `lock_after_approval` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `payroll_tds_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `default_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `applicable_above_gross` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `module` varchar(60) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `selector` varchar(40) NOT NULL,
  `validator_hash` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) unsigned NOT NULL,
  `permission_id` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `slug` varchar(100) NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `email_verification_token_hash` varchar(64) DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `employee_id` varchar(30) DEFAULT NULL,
  `branch_id` int(11) unsigned DEFAULT NULL,
  `department_id` int(11) unsigned DEFAULT NULL,
  `designation_id` int(11) unsigned DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `failed_login_attempts` tinyint(3) unsigned DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `locked_reason` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `login_count` int(11) unsigned DEFAULT 0,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `last_active_at` datetime DEFAULT NULL,
  `session_version` int(11) unsigned DEFAULT 1,
  `allow_remember_me` tinyint(1) DEFAULT 1,
  `allow_mobile_login` tinyint(1) DEFAULT 1,
  `allow_web_login` tinyint(1) DEFAULT 1,
  `require_2fa` tinyint(1) DEFAULT 0,
  `password_changed_at` datetime DEFAULT NULL,
  `reset_token_hash` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_branch_id_foreign` (`branch_id`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_designation_id_foreign` (`designation_id`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

-- ----------------------------------------------------------------------------
-- 2. MIGRATION HISTORY (marks the schema as fully migrated; tenants:migrate will see nothing pending)
-- ----------------------------------------------------------------------------
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (1,'2026-08-19-120001','App\\Database\\Migrations\\CreateUsers','default','App',1787201942,1);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (2,'2026-08-19-120002','App\\Database\\Migrations\\CreateRoles','default','App',1787201942,1);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (3,'2026-08-19-120003','App\\Database\\Migrations\\CreatePermissions','default','App',1787201942,1);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (4,'2026-08-19-120004','App\\Database\\Migrations\\CreateRolePermissions','default','App',1787201943,1);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (5,'2026-08-19-120005','App\\Database\\Migrations\\CreateUserRoles','default','App',1787201943,1);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (6,'2026-08-19-120006','App\\Database\\Migrations\\CreateCompanySettings','default','App',1787201943,1);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (7,'2026-08-20-140001','App\\Database\\Migrations\\CreateBranches','default','App',1787205277,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (8,'2026-08-20-140002','App\\Database\\Migrations\\CreateDepartments','default','App',1787205277,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (9,'2026-08-20-140003','App\\Database\\Migrations\\CreateDesignations','default','App',1787205277,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (10,'2026-08-20-140004','App\\Database\\Migrations\\AlterUsersAddPhase3Fields','default','App',1787205280,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (11,'2026-08-20-140005','App\\Database\\Migrations\\AlterRolesAddStatus','default','App',1787205280,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (12,'2026-08-20-140006','App\\Database\\Migrations\\AlterUserRolesAddPrimary','default','App',1787205280,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (13,'2026-08-20-140007','App\\Database\\Migrations\\CreateLoginLogs','default','App',1787205280,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (14,'2026-08-20-140008','App\\Database\\Migrations\\CreateAuditLogs','default','App',1787205280,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (15,'2026-08-20-140009','App\\Database\\Migrations\\AlterCompanySettingsAddPermissionsVersion','default','App',1787205280,2);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (16,'2026-08-20-140010','App\\Database\\Migrations\\CreateRememberTokens','default','App',1787207055,3);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (17,'2026-08-20-150001','App\\Database\\Migrations\\CreateEmployees','default','App',1787213524,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (18,'2026-08-20-150002','App\\Database\\Migrations\\CreateEmployeeAddresses','default','App',1787213525,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (19,'2026-08-20-150003','App\\Database\\Migrations\\CreateEmployeeEmergencyContacts','default','App',1787213525,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (20,'2026-08-20-150004','App\\Database\\Migrations\\CreateEmployeeFamilyMembers','default','App',1787213525,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (21,'2026-08-20-150005','App\\Database\\Migrations\\CreateEmployeeEducation','default','App',1787213525,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (22,'2026-08-20-150006','App\\Database\\Migrations\\CreateEmployeeExperience','default','App',1787213525,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (23,'2026-08-20-150007','App\\Database\\Migrations\\CreateEmployeeBankAccounts','default','App',1787213525,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (24,'2026-08-20-150008','App\\Database\\Migrations\\CreateEmployeeDocuments','default','App',1787213526,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (25,'2026-08-20-150009','App\\Database\\Migrations\\CreateEmployeeStatusHistory','default','App',1787213526,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (26,'2026-08-20-150010','App\\Database\\Migrations\\AlterCompanySettingsAddEmployeeSequence','default','App',1787213526,4);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (27,'2026-08-20-160001','App\\Database\\Migrations\\CreateAttendanceShifts','default','App',1787221681,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (28,'2026-08-20-160002','App\\Database\\Migrations\\CreateAttendanceShiftAssignments','default','App',1787221682,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (29,'2026-08-20-160003','App\\Database\\Migrations\\CreateAttendanceWeeklyOffs','default','App',1787221682,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (30,'2026-08-20-160004','App\\Database\\Migrations\\CreateAttendanceHolidays','default','App',1787221682,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (31,'2026-08-20-160005','App\\Database\\Migrations\\CreateAttendanceLocations','default','App',1787221683,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (32,'2026-08-20-160006','App\\Database\\Migrations\\CreateAttendanceDevices','default','App',1787221683,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (33,'2026-08-20-160007','App\\Database\\Migrations\\CreateAttendance','default','App',1787221683,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (34,'2026-08-20-160008','App\\Database\\Migrations\\CreateAttendanceLogs','default','App',1787221684,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (35,'2026-08-20-160009','App\\Database\\Migrations\\CreateAttendanceRegularizations','default','App',1787221684,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (36,'2026-08-20-160010','App\\Database\\Migrations\\CreateAttendanceOvertime','default','App',1787221685,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (37,'2026-08-20-160011','App\\Database\\Migrations\\CreateAttendanceBiometricLogs','default','App',1787221685,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (38,'2026-08-20-160012','App\\Database\\Migrations\\CreateAttendanceSettings','default','App',1787221685,5);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (39,'2026-08-20-170001','App\\Database\\Migrations\\CreateLeaveTypes','default','App',1787231158,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (40,'2026-08-20-170002','App\\Database\\Migrations\\CreateLeaveSettings','default','App',1787231158,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (41,'2026-08-20-170003','App\\Database\\Migrations\\CreateLeavePolicies','default','App',1787231158,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (42,'2026-08-20-170004','App\\Database\\Migrations\\CreateLeavePolicyRules','default','App',1787231158,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (43,'2026-08-20-170005','App\\Database\\Migrations\\CreateEmployeeLeaveBalances','default','App',1787231159,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (44,'2026-08-20-170006','App\\Database\\Migrations\\CreateLeaveApplications','default','App',1787231159,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (45,'2026-08-20-170007','App\\Database\\Migrations\\CreateLeaveApplicationDays','default','App',1787231159,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (46,'2026-08-20-170008','App\\Database\\Migrations\\CreateLeaveApprovalHistory','default','App',1787231160,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (47,'2026-08-20-170009','App\\Database\\Migrations\\CreateLeaveDelegations','default','App',1787231160,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (48,'2026-08-20-170010','App\\Database\\Migrations\\CreateLeaveCarryForwardHistory','default','App',1787231160,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (49,'2026-08-20-170011','App\\Database\\Migrations\\CreateLeaveEncashments','default','App',1787231161,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (50,'2026-08-20-170012','App\\Database\\Migrations\\AlterAttendanceAddLeaveStatuses','default','App',1787231162,6);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (51,'2026-08-21-180001','App\\Database\\Migrations\\CreatePayrollSettings','default','App',1787309276,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (52,'2026-08-21-180002','App\\Database\\Migrations\\CreatePayrollPfSettings','default','App',1787309277,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (53,'2026-08-21-180003','App\\Database\\Migrations\\CreatePayrollEsiSettings','default','App',1787309277,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (54,'2026-08-21-180004','App\\Database\\Migrations\\CreatePayrollTdsSettings','default','App',1787309278,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (55,'2026-08-21-180005','App\\Database\\Migrations\\CreatePayrollProfessionalTaxSettings','default','App',1787309278,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (56,'2026-08-21-180006','App\\Database\\Migrations\\CreatePayrollSalaryComponents','default','App',1787309279,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (57,'2026-08-21-180007','App\\Database\\Migrations\\CreatePayrollSalaryStructures','default','App',1787309280,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (58,'2026-08-21-180008','App\\Database\\Migrations\\CreatePayrollSalaryStructureItems','default','App',1787309281,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (59,'2026-08-21-180009','App\\Database\\Migrations\\CreatePayrollEmployeeSalary','default','App',1787309281,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (60,'2026-08-21-180010','App\\Database\\Migrations\\CreatePayrollMonths','default','App',1787309282,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (61,'2026-08-21-180011','App\\Database\\Migrations\\CreatePayrollRuns','default','App',1787309283,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (62,'2026-08-21-180012','App\\Database\\Migrations\\CreatePayrollRunItems','default','App',1787309284,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (63,'2026-08-21-180013','App\\Database\\Migrations\\CreatePayrollAdjustments','default','App',1787309284,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (64,'2026-08-21-180014','App\\Database\\Migrations\\CreatePayrollLoans','default','App',1787309285,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (65,'2026-08-21-180015','App\\Database\\Migrations\\CreatePayrollLoanInstallments','default','App',1787309286,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (66,'2026-08-21-180016','App\\Database\\Migrations\\CreatePayrollAdvances','default','App',1787309287,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (67,'2026-08-21-180017','App\\Database\\Migrations\\CreatePayrollBonus','default','App',1787309287,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (68,'2026-08-21-180018','App\\Database\\Migrations\\CreatePayrollIncentives','default','App',1787309288,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (69,'2026-08-21-180019','App\\Database\\Migrations\\CreatePayrollReimbursements','default','App',1787309289,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (70,'2026-08-21-180020','App\\Database\\Migrations\\CreatePayrollArrears','default','App',1787309289,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (71,'2026-08-21-180021','App\\Database\\Migrations\\CreatePayrollPayslips','default','App',1787309290,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (72,'2026-08-21-180022','App\\Database\\Migrations\\AlterBranchesAddState','default','App',1787309292,7);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (73,'2026-08-25-111321','App\\Database\\Migrations\\AlterUsersAddIamFields','default','App',1787641434,8);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (74,'2026-08-25-112007','App\\Database\\Migrations\\AlterRememberTokensAddDeviceInfo','default','App',1787641434,8);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (75,'2026-09-10-090001','App\\Database\\Migrations\\CreatePasswordHistory','default','App',1789044773,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (76,'2026-09-10-090002','App\\Database\\Migrations\\AlterUsersAddEmailVerification','default','App',1789044773,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (77,'2026-09-10-090003','App\\Database\\Migrations\\AlterCompanySettingsAddEmailVerificationToggle','default','App',1789044774,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (78,'2026-09-10-090004','App\\Database\\Migrations\\AddBrandingToCompanySettings','default','App',1789044774,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (79,'2026-09-10-090005','App\\Database\\Migrations\\CreateNotifications','default','App',1789044774,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (80,'2026-09-10-090006','App\\Database\\Migrations\\AddMissingIndexes','default','App',1789044775,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (81,'2026-09-10-090007','App\\Database\\Migrations\\AddEmployeeIdToAuditLogs','default','App',1789044776,9);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (82,'2026-09-28-100000','App\\Database\\Migrations\\AddBrandingColorsToCompanySettings','default','App',1790657888,10);
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES (83,'2026-09-29-160100','App\\Database\\Migrations\\AlterAttendanceHolidaysAddIsAnnual','default','App',1790680181,11);

-- ----------------------------------------------------------------------------
-- 3. RBAC: permission catalog, 4 system roles, role grants (fixed system IDs preserved)
-- ----------------------------------------------------------------------------
INSERT INTO `permissions` (`id`, `slug`, `module`, `description`, `created_at`, `updated_at`) VALUES (1,'dashboard.view','dashboard','View dashboard','2026-08-20 04:59:03','2026-08-20 04:59:03'),(2,'users.view','user','View users','2026-08-20 04:59:03','2026-08-20 05:56:02'),(3,'users.create','user','Create users','2026-08-20 04:59:03','2026-08-20 05:56:02'),(4,'users.edit','user','Edit users','2026-08-20 04:59:03','2026-08-20 05:56:02'),(5,'roles.view','role','View roles','2026-08-20 04:59:03','2026-08-20 05:56:02'),(6,'roles.create','role','Create roles','2026-08-20 04:59:03','2026-08-20 05:56:02'),(7,'roles.edit','role','Edit roles','2026-08-20 04:59:03','2026-08-20 05:56:02'),(8,'roles.delete','role','Delete roles','2026-08-20 04:59:03','2026-08-20 05:56:02'),(9,'users.delete','users','Delete users','2026-08-20 05:56:02','2026-08-20 05:56:02'),(10,'branches.view','branches','View branches','2026-08-20 05:56:02','2026-08-20 05:56:02'),(11,'branches.create','branches','Create branches','2026-08-20 05:56:02','2026-08-20 05:56:02'),(12,'branches.edit','branches','Edit branches','2026-08-20 05:56:02','2026-08-20 05:56:02'),(13,'branches.delete','branches','Delete branches','2026-08-20 05:56:02','2026-08-20 05:56:02'),(14,'departments.view','departments','View departments','2026-08-20 05:56:02','2026-08-20 05:56:02'),(15,'departments.create','departments','Create departments','2026-08-20 05:56:02','2026-08-20 05:56:02'),(16,'departments.edit','departments','Edit departments','2026-08-20 05:56:02','2026-08-20 05:56:02'),(17,'departments.delete','departments','Delete departments','2026-08-20 05:56:02','2026-08-20 05:56:02'),(18,'designations.view','designations','View designations','2026-08-20 05:56:02','2026-08-20 05:56:02'),(19,'designations.create','designations','Create designations','2026-08-20 05:56:02','2026-08-20 05:56:02'),(20,'designations.edit','designations','Edit designations','2026-08-20 05:56:02','2026-08-20 05:56:02'),(21,'designations.delete','designations','Delete designations','2026-08-20 05:56:02','2026-08-20 05:56:02'),(22,'settings.view','settings','View company settings','2026-08-20 05:56:02','2026-08-20 05:56:02'),(23,'settings.manage','settings','Manage company settings','2026-08-20 05:56:02','2026-08-20 05:56:02'),(24,'audit.view','audit','View audit logs','2026-08-20 05:56:02','2026-08-20 05:56:02'),(25,'employee.view','employee','View employees','2026-08-20 05:56:02','2026-08-20 05:56:02'),(26,'employee.create','employee','Create employees','2026-08-20 05:56:02','2026-08-20 05:56:02'),(27,'employee.edit','employee','Edit employees','2026-08-20 05:56:02','2026-08-20 05:56:02'),(28,'employee.delete','employee','Delete employees','2026-08-20 05:56:02','2026-08-20 05:56:02'),(29,'employee.export','employee','Export employees','2026-08-20 05:56:02','2026-08-20 05:56:02'),(30,'employee.import','employee','Import employees','2026-08-20 05:56:02','2026-08-20 05:56:02'),(31,'attendance.view','attendance','View attendance','2026-08-20 05:56:02','2026-08-20 05:56:02'),(32,'attendance.create','attendance','Record attendance','2026-08-20 05:56:02','2026-08-20 05:56:02'),(33,'attendance.edit','attendance','Edit attendance','2026-08-20 05:56:02','2026-08-20 05:56:02'),(34,'attendance.delete','attendance','Delete attendance','2026-08-20 05:56:02','2026-08-20 05:56:02'),(35,'attendance.approve','attendance','Approve attendance','2026-08-20 05:56:02','2026-08-20 05:56:02'),(36,'leave.view','leave','View leave','2026-08-20 05:56:02','2026-08-20 05:56:02'),(37,'leave.create','leave','Apply for leave','2026-08-20 05:56:02','2026-08-20 05:56:02'),(38,'leave.edit','leave','Edit leave','2026-08-20 05:56:02','2026-08-20 05:56:02'),(39,'leave.approve','leave','Approve leave','2026-08-20 05:56:02','2026-08-20 05:56:02'),(40,'leave.reject','leave','Reject leave','2026-08-20 05:56:02','2026-08-20 05:56:02'),(41,'payroll.view','payroll','View payroll','2026-08-20 05:56:02','2026-08-20 05:56:02'),(42,'payroll.process','payroll','Process payroll','2026-08-20 05:56:02','2026-08-20 05:56:02'),(43,'payroll.approve','payroll','Approve payroll','2026-08-20 05:56:02','2026-08-20 05:56:02'),(44,'assets.view','assets','View assets','2026-08-20 05:56:02','2026-08-20 05:56:02'),(45,'assets.create','assets','Create assets','2026-08-20 05:56:02','2026-08-20 05:56:02'),(46,'reports.view','reports','View reports','2026-08-20 05:56:02','2026-08-20 05:56:02'),(47,'reports.export','reports','Export reports','2026-08-20 05:56:02','2026-08-20 05:56:02'),(48,'employee.documents','employee','Manage employee documents','2026-08-20 08:12:06','2026-08-20 08:12:06'),(49,'employee.bank.view','employee','View employee bank details','2026-08-20 08:12:06','2026-08-20 08:12:06'),(50,'employee.bank.edit','employee','Edit employee bank details','2026-08-20 08:12:06','2026-08-20 08:12:06'),(51,'employee.status.change','employee','Change employee status','2026-08-20 08:12:06','2026-08-20 08:12:06'),(52,'employee.profile.view','employee','View employee 360 profile','2026-08-20 08:12:06','2026-08-20 08:12:06'),(53,'attendance.export','attendance','Export attendance','2026-08-20 10:28:05','2026-08-20 10:28:05'),(54,'attendance.import','attendance','Import attendance','2026-08-20 10:28:05','2026-08-20 10:28:05'),(55,'attendance.settings.manage','attendance','Manage attendance settings','2026-08-20 10:28:05','2026-08-20 10:28:05'),(56,'attendance.shift.manage','attendance','Manage shifts','2026-08-20 10:28:05','2026-08-20 10:28:05'),(57,'attendance.location.manage','attendance','Manage office locations','2026-08-20 10:28:05','2026-08-20 10:28:05'),(58,'attendance.device.manage','attendance','Manage attendance devices','2026-08-20 10:28:05','2026-08-20 10:28:05'),(59,'attendance.regularization.approve','attendance','Approve attendance regularizations','2026-08-20 10:28:05','2026-08-20 10:28:05'),(60,'leave.cancel','leave','Cancel leave applications','2026-08-20 13:06:02','2026-08-20 13:06:02'),(61,'leave.balance.view','leave','View leave balances','2026-08-20 13:06:02','2026-08-20 13:06:02'),(62,'leave.balance.adjust','leave','Adjust leave balances','2026-08-20 13:06:02','2026-08-20 13:06:02'),(63,'leave.policy.manage','leave','Manage leave policies','2026-08-20 13:06:02','2026-08-20 13:06:02'),(64,'leave.type.manage','leave','Manage leave types','2026-08-20 13:06:02','2026-08-20 13:06:02'),(65,'leave.settings.manage','leave','Manage leave settings','2026-08-20 13:06:02','2026-08-20 13:06:02'),(66,'leave.report.export','leave','Export leave reports','2026-08-20 13:06:02','2026-08-20 13:06:02'),(67,'payroll.generate','payroll','Generate payroll','2026-08-21 10:48:12','2026-08-21 10:48:12'),(68,'payroll.edit','payroll','Edit payroll','2026-08-21 10:48:12','2026-08-21 10:48:12'),(69,'payroll.lock','payroll','Lock/unlock payroll','2026-08-21 10:48:12','2026-08-21 10:48:12'),(70,'payroll.pay','payroll','Mark payroll as paid','2026-08-21 10:48:12','2026-08-21 10:48:12'),(71,'payroll.export','payroll','Export payroll reports','2026-08-21 10:48:12','2026-08-21 10:48:12'),(72,'payroll.settings.manage','payroll','Manage payroll settings','2026-08-21 10:48:12','2026-08-21 10:48:12'),(73,'salarystructure.manage','salarystructure','Manage salary structures','2026-08-21 10:48:12','2026-08-21 10:48:12'),(74,'salarycomponent.manage','salarycomponent','Manage salary components','2026-08-21 10:48:12','2026-08-21 10:48:12'),(75,'loan.manage','loan','Manage employee loans','2026-08-21 10:48:12','2026-08-21 10:48:12'),(76,'advance.manage','advance','Manage salary advances','2026-08-21 10:48:12','2026-08-21 10:48:12'),(77,'bonus.manage','bonus','Manage bonus and incentives','2026-08-21 10:48:12','2026-08-21 10:48:12'),(78,'reimbursement.manage','reimbursement','Manage reimbursements','2026-08-21 10:48:12','2026-08-21 10:48:12'),(79,'payslip.download','payslip','Download payslips','2026-08-21 10:48:12','2026-08-21 10:48:12'),(80,'employee.directory.search','employee','Search the employee directory (name lookup only)','2026-09-29 06:15:59','2026-09-29 06:15:59'),(81,'attendance.view.own','attendance','View own attendance (self-service)','2026-09-29 06:15:59','2026-09-29 06:15:59'),(82,'leave.view.own','leave','View own leave applications (self-service)','2026-09-29 06:15:59','2026-09-29 06:15:59'),(83,'leave.cancel.any','leave','Cancel any employee\'s leave applications','2026-09-29 06:15:59','2026-09-29 06:15:59'),(84,'leave.balance.view.own','leave','View own leave balance (self-service)','2026-09-29 06:15:59','2026-09-29 06:15:59'),(85,'payslip.download.all','payslip','Download any employee\'s payslip (administrative)','2026-09-29 06:15:59','2026-09-29 06:15:59');
INSERT INTO `roles` (`id`, `name`, `description`, `slug`, `is_system`, `status`, `created_at`, `updated_at`) VALUES (1,'Company Admin',NULL,'company-admin',1,'active','2026-08-20 04:59:03','2026-08-20 04:59:03'),(2,'HR Manager','test','hr-manager',1,'active','2026-08-20 04:59:03','2026-08-20 06:30:16'),(3,'Manager',NULL,'manager',1,'active','2026-08-20 04:59:03','2026-08-20 04:59:03'),(4,'Employee',NULL,'employee',1,'active','2026-08-20 04:59:03','2026-08-20 04:59:03');
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (1,1,'2026-09-29 06:15:59'),(1,2,'2026-09-29 06:15:59'),(1,3,'2026-09-29 06:15:59'),(1,4,'2026-09-29 06:15:59'),(1,5,'2026-09-29 06:15:59'),(1,6,'2026-09-29 06:15:59'),(1,7,'2026-09-29 06:15:59'),(1,8,'2026-09-29 06:15:59'),(1,9,'2026-09-29 06:15:59'),(1,10,'2026-09-29 06:15:59'),(1,11,'2026-09-29 06:15:59'),(1,12,'2026-09-29 06:15:59'),(1,13,'2026-09-29 06:15:59'),(1,14,'2026-09-29 06:15:59'),(1,15,'2026-09-29 06:15:59'),(1,16,'2026-09-29 06:15:59'),(1,17,'2026-09-29 06:15:59'),(1,18,'2026-09-29 06:15:59'),(1,19,'2026-09-29 06:15:59'),(1,20,'2026-09-29 06:15:59'),(1,21,'2026-09-29 06:15:59'),(1,22,'2026-09-29 06:15:59'),(1,23,'2026-09-29 06:15:59'),(1,24,'2026-09-29 06:15:59'),(1,25,'2026-09-29 06:15:59'),(1,26,'2026-09-29 06:15:59'),(1,27,'2026-09-29 06:15:59'),(1,28,'2026-09-29 06:15:59'),(1,29,'2026-09-29 06:15:59'),(1,30,'2026-09-29 06:15:59'),(1,31,'2026-09-29 06:15:59'),(1,32,'2026-09-29 06:15:59'),(1,33,'2026-09-29 06:15:59'),(1,34,'2026-09-29 06:15:59'),(1,35,'2026-09-29 06:15:59'),(1,36,'2026-09-29 06:15:59'),(1,37,'2026-09-29 06:15:59'),(1,38,'2026-09-29 06:15:59'),(1,39,'2026-09-29 06:15:59'),(1,40,'2026-09-29 06:15:59'),(1,41,'2026-09-29 06:15:59'),(1,42,'2026-09-29 06:15:59'),(1,43,'2026-09-29 06:15:59'),(1,44,'2026-09-29 06:15:59'),(1,45,'2026-09-29 06:15:59'),(1,46,'2026-09-29 06:15:59'),(1,47,'2026-09-29 06:15:59'),(1,48,'2026-09-29 06:15:59'),(1,49,'2026-09-29 06:15:59'),(1,50,'2026-09-29 06:15:59'),(1,51,'2026-09-29 06:15:59'),(1,52,'2026-09-29 06:15:59'),(1,53,'2026-09-29 06:15:59'),(1,54,'2026-09-29 06:15:59'),(1,55,'2026-09-29 06:15:59'),(1,56,'2026-09-29 06:15:59'),(1,57,'2026-09-29 06:15:59'),(1,58,'2026-09-29 06:15:59'),(1,59,'2026-09-29 06:15:59'),(1,60,'2026-09-29 06:15:59'),(1,61,'2026-09-29 06:15:59'),(1,62,'2026-09-29 06:15:59'),(1,63,'2026-09-29 06:15:59'),(1,64,'2026-09-29 06:15:59'),(1,65,'2026-09-29 06:15:59'),(1,66,'2026-09-29 06:15:59'),(1,67,'2026-09-29 06:15:59'),(1,68,'2026-09-29 06:15:59'),(1,69,'2026-09-29 06:15:59'),(1,70,'2026-09-29 06:15:59'),(1,71,'2026-09-29 06:15:59'),(1,72,'2026-09-29 06:15:59'),(1,73,'2026-09-29 06:15:59'),(1,74,'2026-09-29 06:15:59'),(1,75,'2026-09-29 06:15:59'),(1,76,'2026-09-29 06:15:59'),(1,77,'2026-09-29 06:15:59'),(1,78,'2026-09-29 06:15:59'),(1,79,'2026-09-29 06:15:59'),(1,80,'2026-09-29 06:15:59'),(1,81,'2026-09-29 06:15:59'),(1,82,'2026-09-29 06:15:59'),(1,83,'2026-09-29 06:15:59'),(1,84,'2026-09-29 06:15:59'),(1,85,'2026-09-29 06:15:59'),(2,1,'2026-09-29 06:15:59'),(2,2,'2026-09-29 06:15:59'),(2,3,'2026-09-29 06:15:59'),(2,4,'2026-09-29 06:15:59'),(2,10,'2026-09-29 06:15:59'),(2,14,'2026-09-29 06:15:59'),(2,18,'2026-09-29 06:15:59'),(2,25,'2026-09-29 06:15:59'),(2,26,'2026-09-29 06:15:59'),(2,27,'2026-09-29 06:15:59'),(2,29,'2026-09-29 06:15:59'),(2,30,'2026-09-29 06:15:59'),(2,31,'2026-09-29 06:15:59'),(2,32,'2026-09-29 06:15:59'),(2,33,'2026-09-29 06:15:59'),(2,35,'2026-09-29 06:15:59'),(2,36,'2026-09-29 06:15:59'),(2,37,'2026-09-29 06:15:59'),(2,38,'2026-09-29 06:15:59'),(2,39,'2026-09-29 06:15:59'),(2,40,'2026-09-29 06:15:59'),(2,41,'2026-09-29 06:15:59'),(2,43,'2026-09-29 06:15:59'),(2,46,'2026-09-29 06:15:59'),(2,47,'2026-09-29 06:15:59'),(2,48,'2026-09-29 06:15:59'),(2,49,'2026-09-29 06:15:59'),(2,50,'2026-09-29 06:15:59'),(2,51,'2026-09-29 06:15:59'),(2,52,'2026-09-29 06:15:59'),(2,53,'2026-09-29 06:15:59'),(2,54,'2026-09-29 06:15:59'),(2,56,'2026-09-29 06:15:59'),(2,57,'2026-09-29 06:15:59'),(2,58,'2026-09-29 06:15:59'),(2,59,'2026-09-29 06:15:59'),(2,60,'2026-09-29 06:15:59'),(2,61,'2026-09-29 06:15:59'),(2,62,'2026-09-29 06:15:59'),(2,63,'2026-09-29 06:15:59'),(2,64,'2026-09-29 06:15:59'),(2,65,'2026-09-29 06:15:59'),(2,66,'2026-09-29 06:15:59'),(2,67,'2026-09-29 06:15:59'),(2,68,'2026-09-29 06:15:59'),(2,71,'2026-09-29 06:15:59'),(2,73,'2026-09-29 06:15:59'),(2,74,'2026-09-29 06:15:59'),(2,75,'2026-09-29 06:15:59'),(2,76,'2026-09-29 06:15:59'),(2,77,'2026-09-29 06:15:59'),(2,78,'2026-09-29 06:15:59'),(2,79,'2026-09-29 06:15:59'),(2,81,'2026-09-29 06:15:59'),(2,82,'2026-09-29 06:15:59'),(2,83,'2026-09-29 06:15:59'),(2,84,'2026-09-29 06:15:59'),(2,85,'2026-09-29 06:15:59'),(3,1,'2026-09-29 06:15:59'),(3,25,'2026-09-29 06:15:59'),(3,31,'2026-09-29 06:15:59'),(3,35,'2026-09-29 06:15:59'),(3,36,'2026-09-29 06:15:59'),(3,37,'2026-09-29 06:15:59'),(3,39,'2026-09-29 06:15:59'),(3,40,'2026-09-29 06:15:59'),(3,46,'2026-09-29 06:15:59'),(3,52,'2026-09-29 06:15:59'),(3,59,'2026-09-29 06:15:59'),(3,60,'2026-09-29 06:15:59'),(3,61,'2026-09-29 06:15:59'),(3,81,'2026-09-29 06:15:59'),(3,82,'2026-09-29 06:15:59'),(3,83,'2026-09-29 06:15:59'),(3,84,'2026-09-29 06:15:59'),(4,1,'2026-09-29 15:49:22'),(4,37,'2026-09-29 15:49:22'),(4,60,'2026-09-29 15:49:22'),(4,79,'2026-09-29 15:49:22'),(4,80,'2026-09-29 15:49:22'),(4,81,'2026-09-29 15:49:22'),(4,82,'2026-09-29 15:49:22'),(4,84,'2026-09-29 15:49:22');
UPDATE `roles` SET `description`=NULL, `updated_at`=`created_at` WHERE `id`<=4;

-- ----------------------------------------------------------------------------
-- 4. LEAVE master data (default policy; EL restored to active)
-- ----------------------------------------------------------------------------
INSERT INTO `leave_types` (`id`, `name`, `code`, `description`, `color`, `is_paid`, `annual_allocation`, `half_day_allowed`, `attachment_required`, `medical_certificate_required`, `carry_forward_allowed`, `encashment_allowed`, `attendance_status_map`, `sort_order`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Casual Leave','CL',NULL,'#3B82F6',1,12.0,1,0,0,1,0,'leave',0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL),(2,'Sick Leave','SL',NULL,'#F59E0B',1,10.0,1,0,1,0,0,'leave',0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL),(3,'Earned Leave','EL',NULL,'#10b981',1,18.0,1,0,0,1,1,'leave',0,'inactive',NULL,2,'2026-08-20 13:06:03','2026-08-24 05:06:22',NULL),(4,'Loss of Pay','LOP',NULL,'#EF4444',0,0.0,0,0,0,0,0,'lop',0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL),(5,'Compensatory Off','COMP',NULL,'#8B5CF6',1,0.0,1,0,0,0,0,'leave',0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL),(6,'Work From Home','WFH',NULL,'#06B6D4',1,0.0,1,0,0,0,0,'work_from_home',0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL),(7,'On Duty','ONDUTY',NULL,'#6366F1',1,0.0,0,0,0,0,0,'on_duty',0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL);
INSERT INTO `leave_settings` (`id`, `financial_year_start_month`, `leave_year_start_month`, `half_day_enabled`, `sandwich_leave_enabled`, `holiday_between_leave_policy`, `weekly_off_between_leave_policy`, `carry_forward_enabled`, `carry_forward_limit`, `carry_forward_expiry_month`, `leave_encashment_enabled`, `max_consecutive_leave`, `min_notice_days`, `max_future_apply_days`, `allow_negative_balance`, `self_approval_allowed_for_admin`, `half_day_hours`, `created_at`, `updated_at`) VALUES (1,1,1,1,1,'count','count',1,6.0,NULL,1,NULL,3,180,0,1,4.0,'2026-08-20 13:06:03','2026-08-26 13:54:01');
INSERT INTO `leave_policies` (`id`, `name`, `description`, `branch_id`, `department_id`, `designation_id`, `employment_type`, `employee_id`, `is_default`, `effective_from`, `effective_to`, `priority`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Standard Company Policy','Company-wide default leave policy.',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,'active',NULL,NULL,'2026-08-20 13:06:03','2026-08-20 13:06:03',NULL);
INSERT INTO `leave_policy_rules` (`id`, `leave_policy_id`, `leave_type_id`, `annual_allocation`, `accrual_method`, `monthly_accrual_days`, `carry_forward_allowed`, `carry_forward_limit`, `carry_forward_unlimited`, `encashment_allowed`, `max_consecutive_days`, `min_days_per_application`, `max_applications_per_year`, `sandwich_rule_applicable`, `notice_period_days`, `status`, `created_at`, `updated_at`) VALUES (1,1,1,12.0,'annual',0.00,1,6.0,0,0,3,0.5,NULL,1,NULL,'active','2026-08-20 13:06:03','2026-08-24 05:17:53'),(2,1,2,10.0,'annual',0.00,0,NULL,0,0,NULL,0.5,NULL,1,NULL,'active','2026-08-20 13:06:03','2026-08-24 05:17:53'),(3,1,3,18.0,'annual',0.00,1,NULL,1,1,NULL,0.5,NULL,1,NULL,'active','2026-08-20 13:06:03','2026-08-20 13:06:03'),(4,1,4,0.0,'annual',0.00,0,NULL,0,0,NULL,0.5,NULL,1,NULL,'active','2026-08-20 13:06:04','2026-08-24 05:17:53'),(5,1,5,0.0,'annual',0.00,0,NULL,0,0,NULL,0.5,NULL,1,NULL,'active','2026-08-20 13:06:04','2026-08-24 05:17:54'),(6,1,6,0.0,'annual',0.00,0,NULL,0,0,NULL,0.5,NULL,1,NULL,'active','2026-08-20 13:06:04','2026-08-24 05:17:54'),(7,1,7,0.0,'annual',0.00,0,NULL,0,0,NULL,0.5,NULL,1,NULL,'active','2026-08-20 13:06:04','2026-08-24 05:17:54');
UPDATE `leave_types` SET `status`='active', `updated_by`=NULL, `updated_at`=`created_at` WHERE `id`=3;

-- ----------------------------------------------------------------------------
-- 5. PAYROLL master data (statutory defaults, salary components, PT slabs)
-- ----------------------------------------------------------------------------
INSERT INTO `payroll_settings` (`id`, `payroll_start_day`, `payroll_end_day`, `salary_payment_day`, `financial_year_start_month`, `currency`, `working_days_basis`, `fixed_working_days`, `overtime_enabled`, `overtime_multiplier`, `overtime_rate_basis`, `lop_enabled`, `lop_deduction_basis`, `pf_enabled`, `esi_enabled`, `pt_enabled`, `tds_enabled`, `payslip_prefix`, `lock_after_approval`, `created_at`, `updated_at`) VALUES (1,1,31,7,4,'INR','calendar',30,1,2.00,'basic',1,'gross',1,1,1,1,'PAY',1,'2026-08-21 10:52:56','2026-08-21 11:06:16');
INSERT INTO `payroll_pf_settings` (`id`, `employee_percentage`, `employer_percentage`, `wage_ceiling`, `pf_wage_basis`, `created_at`, `updated_at`) VALUES (1,12.00,12.00,15000.00,'basic','2026-08-21 10:52:56','2026-08-21 10:52:56');
INSERT INTO `payroll_esi_settings` (`id`, `employee_percentage`, `employer_percentage`, `wage_ceiling`, `created_at`, `updated_at`) VALUES (1,0.75,3.25,21000.00,'2026-08-21 10:52:56','2026-08-21 10:52:56');
INSERT INTO `payroll_tds_settings` (`id`, `default_percentage`, `applicable_above_gross`, `created_at`, `updated_at`) VALUES (1,0.00,0.00,'2026-08-21 10:52:56','2026-08-21 10:52:56');
INSERT INTO `payroll_salary_components` (`id`, `name`, `code`, `type`, `calculation_type`, `percentage_of`, `formula`, `is_taxable`, `pf_applicable`, `esi_applicable`, `display_order`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Basic','BASIC','earning','percentage',NULL,NULL,1,1,1,1,'active',NULL,NULL,'2026-08-21 10:48:17','2026-08-21 10:48:17',NULL),(2,'House Rent Allowance','HRA','earning','percentage','BASIC',NULL,1,0,1,2,'inactive',NULL,1,'2026-08-21 10:48:17','2026-09-29 17:02:53',NULL),(3,'Dearness Allowance','DA','earning','percentage','BASIC',NULL,1,1,1,3,'active',NULL,NULL,'2026-08-21 10:48:17','2026-08-21 10:48:17',NULL),(4,'Conveyance Allowance','CONVEYANCE','earning','fixed',NULL,NULL,0,0,1,4,'active',NULL,NULL,'2026-08-21 10:48:17','2026-08-21 10:48:17',NULL),(5,'Medical Allowance','MEDICAL','earning','fixed',NULL,NULL,0,0,1,5,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(6,'Special Allowance','SPECIAL_ALLOWANCE','earning','formula',NULL,'GROSS-BASIC-HRA-DA-CONVEYANCE-MEDICAL',1,0,1,6,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(7,'Food Allowance','FOOD_ALLOWANCE','earning','fixed',NULL,NULL,1,0,1,7,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(8,'Travel Allowance','TRAVEL_ALLOWANCE','earning','fixed',NULL,NULL,1,0,1,8,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(9,'Internet Allowance','INTERNET_ALLOWANCE','earning','fixed',NULL,NULL,1,0,1,9,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(10,'Bonus','BONUS','earning','fixed',NULL,NULL,1,0,0,10,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(11,'Incentive','INCENTIVE','earning','fixed',NULL,NULL,1,0,0,11,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(12,'Overtime','OVERTIME','earning','fixed',NULL,NULL,1,0,0,12,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(13,'Arrears','ARREARS','earning','fixed',NULL,NULL,1,0,0,13,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(14,'Reimbursement','REIMBURSEMENT','earning','fixed',NULL,NULL,0,0,0,14,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(15,'Provident Fund','PF','deduction','fixed',NULL,NULL,0,0,0,15,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(16,'ESI','ESI','deduction','fixed',NULL,NULL,0,0,0,16,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(17,'Professional Tax','PT','deduction','fixed',NULL,NULL,0,0,0,17,'active',NULL,NULL,'2026-08-21 10:48:18','2026-08-21 10:48:18',NULL),(18,'TDS','TDS','deduction','fixed',NULL,NULL,0,0,0,18,'active',NULL,NULL,'2026-08-21 10:48:19','2026-08-21 10:48:19',NULL),(19,'Loan EMI','LOAN_EMI','deduction','fixed',NULL,NULL,0,0,0,19,'active',NULL,NULL,'2026-08-21 10:48:19','2026-08-21 10:48:19',NULL),(20,'Salary Advance','SALARY_ADVANCE','deduction','fixed',NULL,NULL,0,0,0,20,'active',NULL,NULL,'2026-08-21 10:48:19','2026-08-21 10:48:19',NULL),(21,'Loss of Pay','LOP','deduction','fixed',NULL,NULL,0,0,0,21,'active',NULL,NULL,'2026-08-21 10:48:19','2026-08-21 10:48:19',NULL),(22,'Other Deduction','OTHER_DEDUCTION','deduction','fixed',NULL,NULL,0,0,0,22,'active',NULL,NULL,'2026-08-21 10:48:19','2026-08-21 10:48:19',NULL),(23,'Shift Allowance','SHIFT_ALLOWANCE','earning','fixed',NULL,NULL,1,0,0,23,'active',1,NULL,'2026-08-21 11:06:58','2026-08-21 11:06:58',NULL);
INSERT INTO `payroll_professional_tax_settings` (`id`, `state`, `min_gross`, `max_gross`, `tax_amount`, `effective_from`, `status`, `created_at`, `updated_at`) VALUES (1,'Tamil Nadu',0.00,21000.00,0.00,'2026-08-21','active','2026-08-21 10:48:16','2026-08-21 10:48:16'),(2,'Tamil Nadu',21001.00,NULL,208.33,'2026-08-21','active','2026-08-21 10:48:16','2026-08-21 10:48:16'),(3,'Karnataka',0.00,15000.00,0.00,'2026-08-21','active','2026-08-21 10:48:16','2026-08-21 10:48:16'),(4,'Karnataka',15001.00,NULL,200.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17'),(5,'Telangana',0.00,15000.00,0.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17'),(6,'Telangana',15001.00,20000.00,150.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17'),(7,'Telangana',20001.00,NULL,200.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17'),(8,'Andhra Pradesh',0.00,15000.00,0.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17'),(9,'Andhra Pradesh',15001.00,20000.00,150.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17'),(10,'Andhra Pradesh',20001.00,NULL,200.00,'2026-08-21','active','2026-08-21 10:48:17','2026-08-21 10:48:17');

-- ----------------------------------------------------------------------------
-- 6. ATTENDANCE master data (default shifts, weekly offs, settings)
--    (same defaults the HRMS attendance seeder uses; no locations/holidays copied)
-- ----------------------------------------------------------------------------
INSERT INTO `attendance_shifts` (`id`,`name`,`code`,`start_time`,`end_time`,`grace_minutes`,`late_minutes`,`half_day_minutes`,`full_day_minutes`,`is_night_shift`,`status`,`created_at`,`updated_at`) VALUES
(1,'General Shift','GEN','09:00:00','18:00:00',10,15,240,480,0,'active',NOW(),NOW()),
(2,'Morning Shift','MOR','06:00:00','14:00:00',10,15,210,420,0,'active',NOW(),NOW()),
(3,'Night Shift','NGT','22:00:00','06:00:00',15,20,240,480,1,'active',NOW(),NOW());

INSERT INTO `attendance_weekly_offs` (`id`,`name`,`day_of_week`,`week_pattern`,`branch_id`,`shift_id`,`status`,`created_at`,`updated_at`) VALUES
(1,'Sunday Off','sunday','every',NULL,NULL,'active',NOW(),NOW()),
(2,'2nd & 4th Saturday Off','saturday','alternate',NULL,NULL,'active',NOW(),NOW());

INSERT INTO `attendance_settings` (`id`,`default_shift_id`,`grace_minutes`,`late_mark_minutes`,`half_day_minutes`,`full_day_minutes`,`gps_required`,`device_approval_required`,`self_attendance_enabled`,`overtime_enabled`,`weekend_policy`,`holiday_policy`,`timezone`,`created_at`,`updated_at`) VALUES
(1,1,10,15,240,480,1,0,1,1,'Sunday Off','Paid','Asia/Kolkata',NOW(),NOW());

-- ----------------------------------------------------------------------------
-- 7. COMPANY DATA: tenant settings row for "siva" (platform company ID 9)
--    Branding/theme left at column defaults; logo/favicon/banner paths empty.
-- ----------------------------------------------------------------------------
INSERT INTO `company_settings` (`id`,`company_name`,`timezone`,`currency`,`date_format`,`time_format`,`permissions_version`,`employee_code_prefix`,`employee_code_next_seq`,`created_at`,`updated_at`) VALUES
(1,'siva','Asia/Kolkata','INR','d-m-Y','24h',1,'EMP',1,NOW(),NOW());

-- ----------------------------------------------------------------------------
-- 8. INITIAL ADMINISTRATOR (role: Company Admin, id 1)
--    Name/email taken from the platform record of company 9.
--    password_hash is an UNUSABLE placeholder (never matches any password).
--    Set the real password AFTER import, either:
--      a) use "Forgot password" on the tenant login page for siva@gmail.com, or
--      b) generate a bcrypt hash yourself (PHP: password_hash('<your password>', PASSWORD_DEFAULT))
--         and run:  UPDATE users SET password_hash='<hash>' WHERE id=1;
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`,`name`,`email`,`password_hash`,`status`,`must_change_password`,`created_at`,`updated_at`) VALUES
(1,'siva','siva@gmail.com','!UNUSABLE-SET-PASSWORD-AFTER-IMPORT','active',1,NOW(),NOW());

INSERT INTO `user_roles` (`user_id`,`role_id`,`is_primary`,`created_at`) VALUES (1,1,1,NOW());

-- ----------------------------------------------------------------------------
-- Licensing is NOT stored in this database (it lives in the platform DB
-- `licenses` table, issued by Super Admin) - nothing to insert here.
-- ----------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
