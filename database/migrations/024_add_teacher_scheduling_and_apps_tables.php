<?php

declare(strict_types=1);

class AddTeacherSchedulingAndAppsTables
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        // 1. Add teacher scheduling columns to users table
        // We catch if they already exist, but running direct query is fine.
        $this->db->query("
            ALTER TABLE `users`
            ADD COLUMN `lecture_time` TIME NULL AFTER `settings`,
            ADD COLUMN `grace_period` INT UNSIGNED NULL DEFAULT 5 AFTER `lecture_time`
        ");

        // 2. Create user_apps table for admin-assigned apps
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `user_apps` (
                `user_id` INT UNSIGNED NOT NULL,
                `app_name` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`user_id`, `app_name`),
                CONSTRAINT `fk_user_apps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 3. Create teacher_attendance table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `teacher_attendance` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `school_id` INT UNSIGNED NOT NULL,
                `branch_id` INT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                `attendance_date` DATE NOT NULL,
                `opened_at` DATETIME NOT NULL,
                `status` ENUM('on_time', 'late') NOT NULL,
                `lecture_time` TIME NOT NULL,
                `grace_period` INT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_teacher_date` (`user_id`, `attendance_date`),
                CONSTRAINT `fk_teacher_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `teacher_attendance`");
        $this->db->query("DROP TABLE IF EXISTS `user_apps`");
        $this->db->query("
            ALTER TABLE `users`
            DROP COLUMN `lecture_time`,
            DROP COLUMN `grace_period`
        ");
    }
}
