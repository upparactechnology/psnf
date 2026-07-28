<?php

declare(strict_types=1);

use Core\Application;

class Migration_20260728_TransportWorkspaceSchema
{
    public function up(): void
    {
        $db = Application::$app->db;

        $db->query("SET FOREIGN_KEY_CHECKS = 0;");

        // 1. Vehicles Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `transport_vehicles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT DEFAULT 1,
                `vehicle_number` VARCHAR(50) NOT NULL,
                `registration_number` VARCHAR(50) NOT NULL,
                `capacity` INT DEFAULT 40,
                `vehicle_type` VARCHAR(50) DEFAULT 'Bus',
                `driver_id` INT NULL,
                `route_id` INT NULL,
                `status` VARCHAR(30) DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Drivers Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `transport_drivers` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT DEFAULT 1,
                `user_id` INT NULL,
                `driver_code` VARCHAR(30) NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `phone` VARCHAR(30) NOT NULL,
                `license_number` VARCHAR(50) NOT NULL,
                `license_expiry` DATE NULL,
                `emergency_contact` VARCHAR(30) NULL,
                `photo` VARCHAR(255) NULL,
                `status` VARCHAR(30) DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Route Stops Table
        $db->query("
            CREATE TABLE IF NOT EXISTS `transport_route_stops` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `route_id` INT NOT NULL,
                `stop_name` VARCHAR(100) NOT NULL,
                `stop_sequence` INT DEFAULT 1,
                `pickup_time` TIME DEFAULT '07:30:00',
                `drop_time` TIME DEFAULT '16:00:00'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Seed Sample Vehicles
        $db->query("
            INSERT IGNORE INTO `transport_vehicles` (`id`, `tenant_id`, `vehicle_number`, `registration_number`, `capacity`, `vehicle_type`, `status`) VALUES
            (1, 1, 'BUS-01', 'MH-12-AB-5678', 42, 'School Bus', 'active'),
            (2, 1, 'BUS-02', 'GJ-01-XP-2354', 35, 'Mini Bus', 'active');
        ");

        // Seed Sample Drivers
        $db->query("
            INSERT IGNORE INTO `transport_drivers` (`id`, `tenant_id`, `driver_code`, `name`, `phone`, `license_number`, `license_expiry`, `status`) VALUES
            (1, 1, 'DRV-101', 'Rajesh Patel', '9876543212', 'DL-MH2024009', '2028-12-31', 'active'),
            (2, 1, 'DRV-102', 'Rajendra Singh', '+91-9123456789', 'DL-GJ2023991', '2027-10-15', 'active');
        ");

        $db->query("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function down(): void
    {
        $db = Application::$app->db;
        $db->query("DROP TABLE IF EXISTS `transport_route_stops`, `transport_drivers`, `transport_vehicles`;");
    }
}
