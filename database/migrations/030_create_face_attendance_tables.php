<?php

declare(strict_types=1);

/**
 * Migration 030: Create Face Attendance System Tables
 */
return new class {
    public function up(PDO $pdo): void
    {
        // 1. Employees Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `employees` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_code` VARCHAR(50) NOT NULL UNIQUE,
            `name` VARCHAR(150) NOT NULL,
            `department` VARCHAR(100) DEFAULT NULL,
            `designation` VARCHAR(100) DEFAULT NULL,
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`employee_code`),
            INDEX (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Face Embeddings Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `face_embeddings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_id` INT NOT NULL,
            `embedding` LONGTEXT NOT NULL COMMENT 'JSON array of 512-dim float vectors',
            `image_path` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
            INDEX (`employee_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Attendance Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `employee_id` INT NOT NULL,
            `attendance_date` DATE NOT NULL,
            `check_in` DATETIME NOT NULL,
            `confidence` FLOAT NOT NULL,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `ip_address` VARCHAR(45) DEFAULT NULL,
            `user_agent` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
            INDEX (`employee_id`),
            INDEX (`attendance_date`),
            INDEX (`check_in`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `attendance`;");
        $pdo->exec("DROP TABLE IF EXISTS `face_embeddings`;");
        $pdo->exec("DROP TABLE IF EXISTS `employees`;");
    }
};
