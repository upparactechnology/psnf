<?php

declare(strict_types=1);

use Core\Application;

class Migration_20260728_EnhancedWorkspacesSchema
{
    public function up(): void
    {
        $db = Application::$app->db;

        // 1. Ensure permissions matrix support
        $db->query("
            CREATE TABLE IF NOT EXISTS `academic_years` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `school_id` INT UNSIGNED NOT NULL,
                `year_name` VARCHAR(50) NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `is_active` TINYINT(1) DEFAULT 0,
                `is_locked` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_year` (`tenant_id`, `school_id`, `year_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `academic_terms` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `academic_year_id` INT UNSIGNED NOT NULL,
                `term_name` VARCHAR(50) NOT NULL,
                `start_date` DATE NULL,
                `end_date` DATE NULL,
                `is_active` TINYINT(1) DEFAULT 0,
                `is_locked` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `parent_attendance_notices` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `student_id` INT UNSIGNED NOT NULL,
                `guardian_id` INT UNSIGNED NOT NULL,
                `notice_date` DATE NOT NULL,
                `notice_type` ENUM('absent', 'late', 'self_drop', 'other') DEFAULT 'absent',
                `remarks` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_notice_date` (`student_id`, `notice_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `driver_bus_manifest` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `route_id` INT UNSIGNED NOT NULL,
                `student_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `status` ENUM('scheduled', 'boarded', 'dropped', 'absent_notified', 'skipped') DEFAULT 'scheduled',
                `boarded_at` TIMESTAMP NULL,
                `remarks` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_manifest_date` (`route_id`, `student_id`, `date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `subjects` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `school_id` INT UNSIGNED NOT NULL,
                `code` VARCHAR(50) NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `type` ENUM('academic', 'therapy', 'life_skills', 'cognitive') DEFAULT 'academic',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_subj` (`tenant_id`, `code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `student_subject_enrollments` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id` INT UNSIGNED NOT NULL,
                `subject_id` INT UNSIGNED NOT NULL,
                `academic_year_id` INT UNSIGNED NOT NULL,
                `term_id` INT UNSIGNED NULL,
                `status` ENUM('active', 'completed', 'dropped') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_stu_subj` (`student_id`, `subject_id`, `academic_year_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Add role entries if missing
        $rolesToEnsure = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Global unconstrained system control', 'is_system' => 1],
            ['name' => 'School Admin', 'slug' => 'school_admin', 'description' => 'Full control over branch operations', 'is_system' => 1],
            ['name' => 'School Manager', 'slug' => 'manager', 'description' => 'Operations & Leave approval manager', 'is_system' => 1],
            ['name' => 'Teacher', 'slug' => 'teacher', 'description' => 'Class Educator & IEP Evaluator', 'is_system' => 1],
            ['name' => 'Therapist', 'slug' => 'therapist', 'description' => 'Specialist Clinical & Therapy Staff', 'is_system' => 1],
            ['name' => 'Support Staff', 'slug' => 'staff', 'description' => 'Support Staff & School Nurse', 'is_system' => 1],
            ['name' => 'Transport Driver', 'slug' => 'driver', 'description' => 'Bus Driver & Route Operator', 'is_system' => 1],
            ['name' => 'Parent', 'slug' => 'parent', 'description' => 'Child Guardian & Parent Portal User', 'is_system' => 1],
            ['name' => 'Student', 'slug' => 'student', 'description' => 'Student Learner Profile', 'is_system' => 1],
        ];

        foreach ($rolesToEnsure as $r) {
            $exists = $db->selectOne("SELECT id FROM roles WHERE slug = ?", [$r['slug']]);
            if (!$exists) {
                $db->insert('roles', [
                    'name'        => $r['name'],
                    'slug'        => $r['slug'],
                    'description' => $r['description'],
                    'is_system'   => $r['is_system'],
                    'created_at'  => now()
                ]);
            }
        }
    }
}
