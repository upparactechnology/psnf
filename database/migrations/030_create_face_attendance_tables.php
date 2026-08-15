<?php

declare(strict_types=1);

/**
 * Migration 030: Create Face Attendance System Tables
 */
class CreateFaceAttendanceTables
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        // 1. Face Embeddings Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `face_embeddings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED DEFAULT NULL,
            `employee_id` INT DEFAULT NULL,
            `embedding` LONGTEXT NOT NULL COMMENT 'JSON array of 512-dim float vectors',
            `image_path` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX (`user_id`),
            INDEX (`employee_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Attendance Table
        $this->db->query("CREATE TABLE IF NOT EXISTS `attendance` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED DEFAULT NULL,
            `employee_id` INT DEFAULT NULL,
            `attendance_date` DATE DEFAULT NULL,
            `check_in` DATETIME DEFAULT NULL,
            `confidence` FLOAT DEFAULT 0.0,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX (`user_id`),
            INDEX (`employee_id`),
            INDEX (`attendance_date`),
            INDEX (`check_in`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `attendance`;");
        $this->db->query("DROP TABLE IF EXISTS `face_embeddings`;");
    }
}
