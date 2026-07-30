<?php

namespace Database\Migrations;

use Core\Database;
use PDO;

class CreateDriverTripTables
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `driver_trips` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `driver_id` INT UNSIGNED NOT NULL,
                `status` ENUM('active', 'completed') NOT NULL DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `trip_students` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `trip_id` INT UNSIGNED NOT NULL,
                `student_id` INT UNSIGNED NOT NULL,
                `status` ENUM('Waiting', 'Current Stop', 'Picked Up', 'Absent', 'Skipped') NOT NULL DEFAULT 'Waiting',
                `is_current` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`trip_id`) REFERENCES `driver_trips`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `driver_locations` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `trip_id` INT UNSIGNED NOT NULL,
                `lat` DECIMAL(10, 8) NOT NULL,
                `lng` DECIMAL(10, 8) NOT NULL,
                `speed` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`trip_id`) REFERENCES `driver_trips`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `driver_locations`");
        $pdo->exec("DROP TABLE IF EXISTS `trip_students`");
        $pdo->exec("DROP TABLE IF EXISTS `driver_trips`");
    }
}
