<?php
/**
 * PSNF ERP — Full Schema Import Script
 * Run once: http://localhost/psnf/install.php
 * Delete this file after successful import!
 */

// ── Basic security: only allow from localhost ─────────────────────────────────
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$remoteIp   = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteIp, $allowedIPs, true)) {
    http_response_code(403);
    die('Forbidden: This script can only be run from localhost.');
}

$dbHost = '127.0.0.1';
$dbPort = 3306;
$dbName = 'psnf_db';
$dbUser = 'root';
$dbPass = '';

$status  = [];
$success = true;

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4",
        $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    $status[] = ['ok', "Database `$dbName` selected."];

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = [

        'migrations' => "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL DEFAULT 1,
            `ran_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'rate_limits' => "CREATE TABLE IF NOT EXISTS `rate_limits` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `key` VARCHAR(255) NOT NULL,
            `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
            `reset_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `key_unique` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'plans' => "CREATE TABLE IF NOT EXISTS `plans` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `max_schools` INT UNSIGNED NOT NULL DEFAULT 1,
            `max_branches` INT UNSIGNED NOT NULL DEFAULT 5,
            `max_students` INT UNSIGNED NOT NULL DEFAULT 200,
            `max_users` INT UNSIGNED NOT NULL DEFAULT 50,
            `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `features` JSON NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'tenants' => "CREATE TABLE IF NOT EXISTS `tenants` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `uuid` VARCHAR(36) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `domain` VARCHAR(255) NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(30) NULL,
            `address` TEXT NULL,
            `logo` VARCHAR(255) NULL,
            `plan_id` INT UNSIGNED NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `settings` JSON NULL,
            `metadata` JSON NULL,
            `trial_ends_at` TIMESTAMP NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'schools' => "CREATE TABLE IF NOT EXISTS `schools` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `uuid` VARCHAR(36) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(50) NULL,
            `address` TEXT NULL,
            `phone` VARCHAR(30) NULL,
            `email` VARCHAR(255) NULL,
            `logo` VARCHAR(255) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `settings` JSON NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'branches' => "CREATE TABLE IF NOT EXISTS `branches` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `uuid` VARCHAR(36) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(50) NULL,
            `address` TEXT NULL,
            `phone` VARCHAR(30) NULL,
            `email` VARCHAR(255) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'users' => "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `branch_id` INT UNSIGNED NOT NULL,
            `uuid` VARCHAR(36) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(30) NULL,
            `avatar` VARCHAR(255) NULL,
            `designation` VARCHAR(100) NULL,
            `employee_id` VARCHAR(50) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `is_email_verified` TINYINT(1) NOT NULL DEFAULT 0,
            `email_verified_at` TIMESTAMP NULL,
            `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0,
            `two_factor_secret` VARCHAR(255) NULL,
            `remember_token` VARCHAR(100) NULL,
            `last_login_at` TIMESTAMP NULL,
            `last_login_ip` VARCHAR(64) NULL,
            `failed_login_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `locked_until` TIMESTAMP NULL,
            `password_changed_at` TIMESTAMP NULL,
            `meta` JSON NULL,
            `created_by` INT UNSIGNED NULL,
            `updated_by` INT UNSIGNED NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `users_email_tenant` (`email`, `tenant_id`),
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'roles' => "CREATE TABLE IF NOT EXISTS `roles` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NULL,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
            `created_by` INT UNSIGNED NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `roles_slug_tenant` (`slug`, `tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'permissions' => "CREATE TABLE IF NOT EXISTS `permissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `slug` VARCHAR(150) NOT NULL UNIQUE,
            `module` VARCHAR(100) NOT NULL DEFAULT 'general',
            `description` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'role_permissions' => "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `role_id` INT UNSIGNED NOT NULL,
            `permission_id` INT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `role_perm_unique` (`role_id`, `permission_id`),
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'user_roles' => "CREATE TABLE IF NOT EXISTS `user_roles` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `role_id` INT UNSIGNED NOT NULL,
            `assigned_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `user_role_unique` (`user_id`, `role_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'activity_logs' => "CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NULL,
            `school_id` INT UNSIGNED NULL,
            `branch_id` INT UNSIGNED NULL,
            `user_id` INT UNSIGNED NULL,
            `user_name` VARCHAR(255) NULL,
            `event` VARCHAR(190) NOT NULL,
            `model_type` VARCHAR(100) NULL,
            `model_id` INT UNSIGNED NULL,
            `old_values` JSON NULL,
            `new_values` JSON NULL,
            `meta` JSON NULL,
            `ip` VARCHAR(64) NULL,
            `user_agent` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'sessions' => "CREATE TABLE IF NOT EXISTS `sessions` (
            `id` VARCHAR(128) NOT NULL PRIMARY KEY,
            `user_id` INT UNSIGNED NULL,
            `tenant_id` INT UNSIGNED NULL,
            `ip_address` VARCHAR(64) NULL,
            `user_agent` TEXT NULL,
            `payload` TEXT NULL,
            `last_activity` INT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'password_resets' => "CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `used` TINYINT(1) NOT NULL DEFAULT 0,
            `expires_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `email_index` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'otp_codes' => "CREATE TABLE IF NOT EXISTS `otp_codes` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `type` ENUM('login','email_verify','2fa','phone_verify') NOT NULL DEFAULT 'login',
            `code` VARCHAR(10) NOT NULL,
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `used` TINYINT(1) NOT NULL DEFAULT 0,
            `expires_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'subscriptions' => "CREATE TABLE IF NOT EXISTS `subscriptions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `plan_id` INT UNSIGNED NOT NULL,
            `status` ENUM('active','trial','past_due','cancelled','expired') NOT NULL DEFAULT 'trial',
            `starts_at` DATETIME NOT NULL,
            `ends_at` DATETIME NULL,
            `trial_ends_at` DATETIME NULL,
            `auto_renew` TINYINT(1) NOT NULL DEFAULT 1,
            `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `currency` VARCHAR(5) NOT NULL DEFAULT 'INR',
            `payment_ref` VARCHAR(255) NULL,
            `cancelled_at` DATETIME NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'students' => "CREATE TABLE IF NOT EXISTS `students` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `branch_id` INT UNSIGNED NOT NULL,
            `uuid` VARCHAR(36) NOT NULL UNIQUE,
            `admission_number` VARCHAR(50) NULL,
            `gr_number` VARCHAR(50) NULL,
            `first_name` VARCHAR(100) NOT NULL,
            `middle_name` VARCHAR(100) NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `gender` ENUM('male','female','other') NOT NULL,
            `dob` DATE NOT NULL,
            `blood_group` VARCHAR(10) NULL DEFAULT 'Unknown',
            `nationality` VARCHAR(100) NULL DEFAULT 'Indian',
            `religion` VARCHAR(100) NULL,
            `mother_tongue` VARCHAR(100) NULL,
            `aadhar_number` VARCHAR(20) NULL,
            `photo` VARCHAR(255) NULL,
            `address` TEXT NULL,
            `disability_type` VARCHAR(100) NOT NULL,
            `disability_detail` TEXT NULL,
            `care_instructions` TEXT NULL,
            `special_needs_summary` TEXT NULL,
            `admission_status` ENUM('applied','review','assessment','approved','enrolled','withdrawn','graduated') NOT NULL DEFAULT 'applied',
            `admitted_date` DATE NULL,
            `enrolled_date` DATE NULL,
            `academic_year` VARCHAR(20) NULL,
            `class` VARCHAR(50) NULL,
            `section` VARCHAR(20) NULL,
            `notes` TEXT NULL,
            `created_by` INT UNSIGNED NULL,
            `updated_by` INT UNSIGNED NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'student_medical' => "CREATE TABLE IF NOT EXISTS `student_medical` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT UNSIGNED NOT NULL,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `branch_id` INT UNSIGNED NOT NULL,
            `allergies` TEXT NULL,
            `triggers` TEXT NULL,
            `current_medications` TEXT NULL,
            `past_medications` TEXT NULL,
            `medical_conditions` TEXT NULL,
            `immunization_status` TEXT NULL,
            `doctor_name` VARCHAR(255) NULL,
            `doctor_phone` VARCHAR(30) NULL,
            `hospital` VARCHAR(255) NULL,
            `insurance_info` TEXT NULL,
            `care_instructions` TEXT NULL,
            `emergency_protocols` TEXT NULL,
            `notes` TEXT NULL,
            `updated_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `student_medical_unique` (`student_id`),
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'emergency_contacts' => "CREATE TABLE IF NOT EXISTS `emergency_contacts` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT UNSIGNED NOT NULL,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `branch_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `relationship` VARCHAR(100) NOT NULL,
            `phone` VARCHAR(30) NOT NULL,
            `phone_alt` VARCHAR(30) NULL,
            `email` VARCHAR(255) NULL,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `notes` TEXT NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'student_documents' => "CREATE TABLE IF NOT EXISTS `student_documents` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT UNSIGNED NOT NULL,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `branch_id` INT UNSIGNED NOT NULL,
            `type` VARCHAR(100) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(500) NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
            `mime_type` VARCHAR(100) NULL,
            `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
            `verified_by` INT UNSIGNED NULL,
            `verified_at` TIMESTAMP NULL,
            `notes` TEXT NULL,
            `uploaded_by` INT UNSIGNED NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'student_timeline' => "CREATE TABLE IF NOT EXISTS `student_timeline` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT UNSIGNED NOT NULL,
            `tenant_id` INT UNSIGNED NOT NULL,
            `school_id` INT UNSIGNED NOT NULL,
            `branch_id` INT UNSIGNED NOT NULL,
            `event_type` VARCHAR(100) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `color` VARCHAR(30) NULL DEFAULT 'blue',
            `icon` VARCHAR(50) NULL,
            `actor_id` INT UNSIGNED NULL,
            `actor_name` VARCHAR(255) NULL,
            `meta` JSON NULL,
            `occurred_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'guardians' => "CREATE TABLE IF NOT EXISTS `guardians` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL,
            `uuid` VARCHAR(36) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `relationship` VARCHAR(100) NOT NULL,
            `gender` ENUM('male','female','other') NULL,
            `phone` VARCHAR(30) NOT NULL,
            `phone_alt` VARCHAR(30) NULL,
            `email` VARCHAR(255) NULL,
            `occupation` VARCHAR(100) NULL,
            `address` TEXT NULL,
            `id_type` VARCHAR(50) NULL,
            `id_number` VARCHAR(100) NULL,
            `photo` VARCHAR(255) NULL,
            `notes` TEXT NULL,
            `user_id` INT UNSIGNED NULL,
            `created_by` INT UNSIGNED NULL,
            `deleted_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'guardian_student' => "CREATE TABLE IF NOT EXISTS `guardian_student` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `guardian_id` INT UNSIGNED NOT NULL,
            `student_id` INT UNSIGNED NOT NULL,
            `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
            `can_pickup` TINYINT(1) NOT NULL DEFAULT 0,
            `is_emergency` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `guardian_student_unique` (`guardian_id`, `student_id`),
            FOREIGN KEY (`guardian_id`) REFERENCES `guardians`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($tables as $name => $sql) {
        try {
            $pdo->exec($sql);
            $status[] = ['ok', "Table `$name` created."];
        } catch (PDOException $e) {
            $status[] = ['error', "Table `$name`: " . $e->getMessage()];
            $success = false;
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // ── Seed ──────────────────────────────────────────────────────────────────
    $seeds = [
        "INSERT IGNORE INTO `roles` (`name`,`slug`,`description`,`is_system`,`sort_order`) VALUES
            ('Super Admin','super_admin','Full system access',1,1),
            ('School Admin','school_admin','School-level administrator',1,2),
            ('Manager','manager','Operations manager',1,3),
            ('Teacher','teacher','Class teacher',1,4),
            ('Therapist','therapist','Therapy specialist',1,5),
            ('Staff','staff','General staff member',1,6),
            ('Driver','driver','Transport driver',1,7),
            ('Parent','parent','Student parent/guardian',1,8),
            ('Student','student','Student account',1,9)",

        "INSERT IGNORE INTO `permissions` (`name`,`slug`,`module`) VALUES
            ('View Users','view_users','users'),
            ('Create Users','create_users','users'),
            ('Edit Users','edit_users','users'),
            ('Delete Users','delete_users','users'),
            ('View Roles','view_roles','roles'),
            ('Create Roles','create_roles','roles'),
            ('Edit Roles','edit_roles','roles'),
            ('Delete Roles','delete_roles','roles'),
            ('View Students','view_students','students'),
            ('Create Students','create_students','students'),
            ('Edit Students','edit_students','students'),
            ('Delete Students','delete_students','students'),
            ('Approve Admissions','approve_admissions','students'),
            ('Upload Documents','upload_documents','students'),
            ('View Reports','view_reports','reports'),
            ('Export Reports','export_reports','reports'),
            ('Manage Settings','manage_settings','settings'),
            ('View Activity Logs','view_activity_logs','admin')",

        "INSERT IGNORE INTO `role_permissions` (`role_id`,`permission_id`)
            SELECT r.id, p.id FROM roles r, permissions p WHERE r.slug = 'super_admin'",

        "INSERT IGNORE INTO `role_permissions` (`role_id`,`permission_id`)
            SELECT r.id, p.id FROM roles r, permissions p WHERE r.slug='school_admin' AND p.slug NOT IN ('delete_users','delete_roles','manage_settings')",

        "INSERT IGNORE INTO `role_permissions` (`role_id`,`permission_id`)
            SELECT r.id, p.id FROM roles r, permissions p WHERE r.slug='teacher' AND p.slug IN ('view_students','edit_students','upload_documents','view_reports')",

        "INSERT IGNORE INTO `plans` (`name`,`slug`,`max_schools`,`max_branches`,`max_students`,`max_users`,`price_monthly`,`is_active`)
            VALUES ('Standard','standard',5,20,1000,200,0.00,1)",

        "INSERT IGNORE INTO `tenants` (`uuid`,`name`,`slug`,`email`,`plan_id`,`is_active`)
            SELECT UUID(),'Pearl Special Needs Foundation','psnf','admin@psnf.edu',id,1 FROM `plans` WHERE slug='standard' LIMIT 1",

        "INSERT IGNORE INTO `schools` (`tenant_id`,`uuid`,`name`,`code`,`is_active`)
            SELECT t.id,UUID(),'PSNF Main School','PSNF-MAIN',1 FROM `tenants` t WHERE t.slug='psnf' LIMIT 1",

        "INSERT IGNORE INTO `branches` (`tenant_id`,`school_id`,`uuid`,`name`,`code`,`is_active`)
            SELECT t.id,s.id,UUID(),'Main Branch','MAIN',1 FROM `tenants` t JOIN `schools` s ON s.tenant_id=t.id WHERE t.slug='psnf' LIMIT 1",

        // Admin@1234 bcrypt hash
        "INSERT IGNORE INTO `users` (`tenant_id`,`school_id`,`branch_id`,`uuid`,`name`,`email`,`password`,`is_active`,`is_email_verified`)
            SELECT t.id,s.id,b.id,UUID(),'Super Admin','admin@psnf.edu',
            '\$2y\$12\$LCy3GUCqhbL6E.Ek4U2GWuJjFnm7XAo6v8yXvf30oVl6bH4gaqQ5O',
            1,1
            FROM `tenants` t JOIN `schools` s ON s.tenant_id=t.id JOIN `branches` b ON b.school_id=s.id WHERE t.slug='psnf' LIMIT 1",

        "INSERT IGNORE INTO `user_roles` (`user_id`,`role_id`)
            SELECT u.id,r.id FROM `users` u, `roles` r WHERE u.email='admin@psnf.edu' AND r.slug='super_admin' LIMIT 1",
    ];

    foreach ($seeds as $i => $sql) {
        try {
            $affected = $pdo->exec($sql);
            $status[] = ['ok', "Seed #" . ($i+1) . " executed ($affected rows)."];
        } catch (PDOException $e) {
            $status[] = ['warn', "Seed #" . ($i+1) . ": " . $e->getMessage()];
        }
    }

} catch (PDOException $e) {
    $success = false;
    $status[] = ['error', 'Connection failed: ' . $e->getMessage()];
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PSNF ERP — Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body style="background:#080d1a;min-height:100vh;padding:40px 20px;">
<div style="max-width:700px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:32px;">
        <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="32" height="32" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <h1 style="color:white;font-size:24px;font-weight:700;margin-bottom:4px;">PSNF ERP Installer</h1>
        <p style="color:#64748b;font-size:14px;">Database Schema Setup</p>
    </div>

    <?php if ($success): ?>
    <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:16px;padding:20px;margin-bottom:24px;text-align:center;">
        <p style="color:#34d399;font-weight:600;font-size:18px;margin-bottom:4px;">✓ Installation Successful!</p>
        <p style="color:#64748b;font-size:14px;">Login at <a href="http://localhost/psnf/public/" style="color:#818cf8;">http://localhost/psnf/public/</a><br>
        Email: <strong style="color:white;">admin@psnf.edu</strong> &nbsp;|&nbsp; Password: <strong style="color:white;">Admin@1234</strong></p>
        <p style="color:#ef4444;font-size:12px;margin-top:12px;">⚠️ DELETE this install.php file for security!</p>
    </div>
    <?php else: ?>
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:16px;padding:20px;margin-bottom:24px;text-align:center;">
        <p style="color:#f87171;font-weight:600;font-size:18px;">✗ Installation had errors</p>
        <p style="color:#64748b;font-size:14px;">Check the log below.</p>
    </div>
    <?php endif; ?>

    <div style="background:rgba(30,41,59,0.6);border:1px solid rgba(255,255,255,0.06);border-radius:16px;padding:20px;max-height:400px;overflow-y:auto;">
        <?php foreach ($status as [$type, $msg]): ?>
        <div style="display:flex;gap:8px;margin-bottom:6px;align-items:baseline;">
            <span style="color:<?= $type==='ok'?'#34d399':($type==='warn'?'#fbbf24':'#f87171') ?>;font-size:12px;flex-shrink:0;"><?= $type==='ok'?'✓':($type==='warn'?'⚠':'✗') ?></span>
            <span style="color:#94a3b8;font-size:13px;font-family:monospace;"><?= htmlspecialchars($msg) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
