<?php

declare(strict_types=1);

use Core\Application;

class Migration_20260802_FeesModuleRedesignSchema
{
    public function up(): void
    {
        $db = Application::$app->db;

        $db->query("SET FOREIGN_KEY_CHECKS = 0;");

        // 1. Create fee_categories table
        $db->query("
            CREATE TABLE IF NOT EXISTS `fee_categories` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `code` VARCHAR(50) NOT NULL,
                `description` TEXT NULL,
                `tax` DECIMAL(5,2) DEFAULT 0.00,
                `is_refundable` TINYINT(1) DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `display_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed default fee categories if empty
        $db->query("
            INSERT IGNORE INTO `fee_categories` (`name`, `code`, `description`, `is_refundable`, `display_order`) VALUES
            ('Tuition Fee', 'TUITION', 'Standard monthly/term tuition fees', 0, 1),
            ('Admission Fee', 'ADMISSION', 'One-time admission charge', 0, 2),
            ('Registration Fee', 'REGISTRATION', 'One-time registration/application fee', 0, 3),
            ('Annual Fee', 'ANNUAL', 'Annual development and maintenance fee', 0, 4),
            ('Transport Fee', 'TRANSPORT', 'Bus routing and transport fee', 0, 5),
            ('Therapy Fee', 'THERAPY', 'Special therapy and assessment fee', 0, 6),
            ('Activity Fee', 'ACTIVITY', 'Extracurricular activity fee', 0, 7),
            ('Exam Fee', 'EXAM', 'Evaluation and examination fee', 0, 8),
            ('Books & Stationery', 'BOOKS', 'Curriculum textbooks and material costs', 0, 9),
            ('Uniform Fee', 'UNIFORM', 'School uniform sets', 0, 10),
            ('Medical Fee', 'MEDICAL', 'On-campus first-aid and medical checkups', 0, 11),
            ('Miscellaneous', 'MISC', 'Other miscellaneous charges', 0, 12);
        ");

        // 2. Create late_fee_policies table
        $db->query("
            CREATE TABLE IF NOT EXISTS `late_fee_policies` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `rule_type` ENUM('fixed_day', 'fixed_week', 'slab', 'percentage', 'one_time') NOT NULL,
                `value` DECIMAL(10,2) DEFAULT 0.00,
                `grace_days` INT DEFAULT 0,
                `slab_config_json` LONGTEXT NULL,
                `max_cap` DECIMAL(10,2) DEFAULT 0.00,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Create fee_structures table
        $db->query("
            CREATE TABLE IF NOT EXISTS `fee_structures` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `academic_year_id` INT UNSIGNED NOT NULL,
                `main_group_id` INT UNSIGNED NOT NULL,
                `class_id` INT UNSIGNED NULL,
                `name` VARCHAR(255) NOT NULL,
                `late_fee_policy_id` INT UNSIGNED NULL,
                `effective_from` DATE NOT NULL,
                `effective_to` DATE NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `version` INT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_acad_year` (`academic_year_id`),
                KEY `idx_main_group` (`main_group_id`),
                KEY `idx_class` (`class_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Create fee_structure_items table
        $db->query("
            CREATE TABLE IF NOT EXISTS `fee_structure_items` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `fee_structure_id` INT UNSIGNED NOT NULL,
                `fee_category_id` INT UNSIGNED NOT NULL,
                `amount` DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`fee_category_id`) REFERENCES `fee_categories`(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 5. Create student_ledgers table
        $db->query("
            CREATE TABLE IF NOT EXISTS `student_ledgers` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id` INT UNSIGNED NOT NULL,
                `entry_type` ENUM('opening_balance', 'invoice', 'payment', 'late_fee', 'discount', 'adjustment', 'refund') NOT NULL,
                `debit` DECIMAL(10,2) DEFAULT 0.00,
                `credit` DECIMAL(10,2) DEFAULT 0.00,
                `balance` DECIMAL(10,2) NOT NULL,
                `reference_id` INT UNSIGNED NULL,
                `description` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_student` (`student_id`),
                KEY `idx_entry_type` (`entry_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 6. Alter fee_invoices table to support discounts
        try {
            $db->query("
                ALTER TABLE `fee_invoices` 
                ADD COLUMN `discount_type` ENUM('NONE', 'PERCENTAGE', 'FIXED') DEFAULT 'NONE' AFTER `amount`,
                ADD COLUMN `discount_value` DECIMAL(10,2) DEFAULT 0.00 AFTER `discount_type`,
                ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `discount_value`,
                ADD COLUMN `discount_reason` VARCHAR(255) NULL AFTER `discount_amount`
            ");
        } catch (\Throwable $e) {
            // Columns might already exist
        }

        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
