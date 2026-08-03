<?php

declare(strict_types=1);

use Core\Application;

class Migration_20260802_AcademicRestructureSchema
{
    public function up(): void
    {
        $db = Application::$app->db;

        $db->query("SET FOREIGN_KEY_CHECKS = 0;");

        // 1. Create main_groups table
        $db->query("
            CREATE TABLE IF NOT EXISTS `main_groups` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `age_range` VARCHAR(50) NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `color` VARCHAR(20) NOT NULL DEFAULT '#6366f1',
                `icon` VARCHAR(50) NOT NULL DEFAULT '🎓',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed default Main Groups
        $db->query("
            INSERT IGNORE INTO `main_groups` (`id`, `tenant_id`, `name`, `description`, `age_range`, `is_active`, `color`, `icon`) VALUES
            (1, 1, 'Pre Primary', 'Pre-Primary core development and foundation', '3-6', 1, '#3b82f6', '🔵'),
            (2, 1, 'Primary', 'Primary schooling subjects and skills', '6-10', 1, '#8b5cf6', '🟣'),
            (3, 1, 'Functional', 'Functional academic and life-readiness program', '10-18', 1, '#10b981', '🟢'),
            (4, 1, 'NIOS OBE', 'National Institute of Open Schooling Open Basic Education program', '10-18', 1, '#f97316', '🟠'),
            (5, 1, 'Pearl Vocational', 'Pearl Vocational skills training programs', '18+', 1, '#78350f', '🟤'),
            (6, 1, 'Workshop', 'Sheltered workshop production and employment training', '18+', 1, '#1e293b', '⚫');
        ");

        // 2. Modify subjects table: add category
        try {
            $db->query("ALTER TABLE `subjects` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'Academic'");
        } catch (\Throwable $e) {
            // Already exists
        }

        // Migrate any old subjects 'type' values to new 'category'
        $db->query("UPDATE `subjects` SET `category` = 'Academic' WHERE `type` = 'academic'");
        $db->query("UPDATE `subjects` SET `category` = 'Life Skills' WHERE `type` = 'life_skills'");
        $db->query("UPDATE `subjects` SET `category` = 'Co-Curricular' WHERE `type` = 'therapy' OR `type` = 'cognitive'");

        // Seed basic Subjects
        $db->query("
            INSERT IGNORE INTO `subjects` (`tenant_id`, `school_id`, `code`, `name`, `category`) VALUES
            (1, 1, 'ENG01', 'English', 'Academic'),
            (1, 1, 'MTH01', 'Maths', 'Academic'),
            (1, 1, 'EVS01', 'EVS', 'Academic'),
            (1, 1, 'COMP01', 'Computer', 'Academic'),
            (1, 1, 'ADL01', 'ADL', 'Life Skills'),
            (1, 1, 'MONEY01', 'Money Skills', 'Life Skills'),
            (1, 1, 'MOT01', 'Motor Skills', 'Life Skills'),
            (1, 1, 'PERF01', 'Performance Readiness', 'Life Skills'),
            (1, 1, 'DANCE01', 'Dance', 'Co-Curricular'),
            (1, 1, 'MUSIC01', 'Music', 'Co-Curricular'),
            (1, 1, 'YOGA01', 'Yoga', 'Co-Curricular'),
            (1, 1, 'VOCMONEY01', 'Money Transaction', 'Vocational'),
            (1, 1, 'CUST01', 'Customer Interaction', 'Vocational'),
            (1, 1, 'MOCKM01', 'Mock Market', 'Vocational'),
            (1, 1, 'DATAE01', 'Data Entry', 'Vocational'),
            (1, 1, 'COOK01', 'No Gas Cooking', 'Vocational');
        ");

        // 3. Create curriculum_templates table
        $db->query("
            CREATE TABLE IF NOT EXISTS `curriculum_templates` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `academic_year_id` INT UNSIGNED NOT NULL,
                `main_group_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(150) NOT NULL,
                `version` INT UNSIGNED NOT NULL DEFAULT 1,
                `description` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_cur_year` (`academic_year_id`),
                KEY `idx_cur_group` (`main_group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Create curriculum_sections table
        $db->query("
            CREATE TABLE IF NOT EXISTS `curriculum_sections` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `curriculum_template_id` INT UNSIGNED NOT NULL,
                `section_name` VARCHAR(100) NOT NULL,
                `sort_order` INT UNSIGNED NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_section_template` (`curriculum_template_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 5. Create curriculum_subjects table
        $db->query("
            CREATE TABLE IF NOT EXISTS `curriculum_subjects` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `curriculum_section_id` INT UNSIGNED NOT NULL,
                `subject_id` INT UNSIGNED NOT NULL,
                `is_required` TINYINT(1) NOT NULL DEFAULT 1,
                `default_grade` VARCHAR(10) NULL,
                `visible` TINYINT(1) NOT NULL DEFAULT 1,
                `sequence` INT UNSIGNED NOT NULL DEFAULT 1,
                `assessment_type` VARCHAR(50) NOT NULL DEFAULT 'Marks',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_curr_subject_sec` (`curriculum_section_id`),
                KEY `idx_curr_subject_sub` (`subject_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed a sample Functional Curriculum for year 2026-27 (academic_years.id = 3 usually based on settings)
        $db->query("
            INSERT IGNORE INTO `curriculum_templates` (`id`, `tenant_id`, `academic_year_id`, `main_group_id`, `name`, `version`, `description`, `is_active`) VALUES
            (1, 1, 3, 3, 'Functional Curriculum 2026', 1, 'Core academic and life skills framework for Functional classes', 1);
        ");

        $db->query("
            INSERT IGNORE INTO `curriculum_sections` (`id`, `curriculum_template_id`, `section_name`, `sort_order`) VALUES
            (1, 1, 'Academic', 1),
            (2, 1, 'Life Skills', 2),
            (3, 1, 'Co-Curricular', 3);
        ");

        // Fetch subject IDs for seeding
        $subjectsMap = [];
        $res = $db->select("SELECT id, name FROM `subjects` WHERE tenant_id = 1");
        foreach ($res as $row) {
            $subjectsMap[$row['name']] = (int)$row['id'];
        }

        if (!empty($subjectsMap)) {
            $seedCurrSubjects = [
                // Academic Section
                ['sec_id' => 1, 'sub' => 'English', 'req' => 1, 'seq' => 1, 'type' => 'Marks'],
                ['sec_id' => 1, 'sub' => 'Maths', 'req' => 1, 'seq' => 2, 'type' => 'Marks'],
                ['sec_id' => 1, 'sub' => 'EVS', 'req' => 1, 'seq' => 3, 'type' => 'Marks'],
                ['sec_id' => 1, 'sub' => 'Computer', 'req' => 1, 'seq' => 4, 'type' => 'Marks'],
                // Life Skills Section
                ['sec_id' => 2, 'sub' => 'ADL', 'req' => 1, 'seq' => 1, 'type' => 'Rating'],
                ['sec_id' => 2, 'sub' => 'Money Skills', 'req' => 1, 'seq' => 2, 'type' => 'Rating'],
                ['sec_id' => 2, 'sub' => 'Motor Skills', 'req' => 1, 'seq' => 3, 'type' => 'Rating'],
                ['sec_id' => 2, 'sub' => 'Performance Readiness', 'req' => 1, 'seq' => 4, 'type' => 'Rating'],
                // Co-Curricular Section
                ['sec_id' => 3, 'sub' => 'Dance', 'req' => 1, 'seq' => 1, 'type' => 'Grade'],
                ['sec_id' => 3, 'sub' => 'Music', 'req' => 1, 'seq' => 2, 'type' => 'Grade'],
                ['sec_id' => 3, 'sub' => 'Yoga', 'req' => 1, 'seq' => 3, 'type' => 'Grade'],
            ];

            foreach ($seedCurrSubjects as $cs) {
                if (isset($subjectsMap[$cs['sub']])) {
                    $db->query("
                        INSERT IGNORE INTO `curriculum_subjects` (`curriculum_section_id`, `subject_id`, `is_required`, `sequence`, `assessment_type`) VALUES
                        (?, ?, ?, ?, ?)
                    ", [$cs['sec_id'], $subjectsMap[$cs['sub']], $cs['req'], $cs['seq'], $cs['type']]);
                }
            }
        }

        // 6. Add relation columns to classes table
        try {
            $db->query("ALTER TABLE `classes` ADD COLUMN `academic_year_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}
        try {
            $db->query("ALTER TABLE `classes` ADD COLUMN `main_group_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}
        try {
            $db->query("ALTER TABLE `classes` ADD COLUMN `class_teacher_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}

        // Populate default values for existing classes if needed
        $db->query("UPDATE `classes` SET `academic_year_id` = 3 WHERE `academic_year_id` IS NULL");
        $db->query("UPDATE `classes` SET `main_group_id` = 3 WHERE `main_group_id` IS NULL");

        // 7. Add relational columns to students table
        try {
            $db->query("ALTER TABLE `students` ADD COLUMN `class_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}
        try {
            $db->query("ALTER TABLE `students` ADD COLUMN `main_group_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}

        // Populate default class_id on students
        $classes = $db->select("SELECT id, name FROM `classes`");
        foreach ($classes as $cls) {
            $db->query("UPDATE `students` SET `class_id` = ?, `main_group_id` = 3 WHERE `class` = ?", [$cls['id'], $cls['name']]);
        }

        // 8. Add relational columns to timetables table
        try {
            $db->query("ALTER TABLE `timetables` ADD COLUMN `class_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}
        try {
            $db->query("ALTER TABLE `timetables` ADD COLUMN `teacher_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}
        try {
            $db->query("ALTER TABLE `timetables` ADD COLUMN `subject_id` INT UNSIGNED NULL");
        } catch (\Throwable $e) {}

        // Match timetables entries to class_id, subject_id, and teacher_id
        $db->query("UPDATE `timetables` t JOIN `classes` c ON t.class = c.name SET t.class_id = c.id");
        $db->query("UPDATE `timetables` t JOIN `subjects` s ON t.subject = s.name SET t.subject_id = s.id");
        $db->query("UPDATE `timetables` t JOIN `users` u ON t.teacher_name = u.name SET t.teacher_id = u.id");

        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function down(): void
    {
        $db = Application::$app->db;
        $db->query("SET FOREIGN_KEY_CHECKS = 0;");
        $db->query("DROP TABLE IF EXISTS `curriculum_subjects`, `curriculum_sections`, `curriculum_templates`, `main_groups`;");
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
