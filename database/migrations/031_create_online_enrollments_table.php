<?php

class CreateOnlineEnrollmentsTable
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `online_enrollments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `application_code` VARCHAR(50) NOT NULL UNIQUE,
            `student_full_name` VARCHAR(255) NOT NULL,
            `dob` DATE NOT NULL,
            `gender` ENUM('male', 'female', 'other') NOT NULL,
            `student_aadhar` VARCHAR(20) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `student_photo` VARCHAR(255) DEFAULT NULL,
            `student_aadhar_doc` VARCHAR(255) DEFAULT NULL,

            `father_name` VARCHAR(255) DEFAULT NULL,
            `father_phone` VARCHAR(30) DEFAULT NULL,
            `father_aadhar` VARCHAR(20) DEFAULT NULL,
            `father_photo` VARCHAR(255) DEFAULT NULL,
            `father_aadhar_doc` VARCHAR(255) DEFAULT NULL,

            `mother_name` VARCHAR(255) DEFAULT NULL,
            `mother_phone` VARCHAR(30) DEFAULT NULL,
            `mother_aadhar` VARCHAR(20) DEFAULT NULL,
            `mother_photo` VARCHAR(255) DEFAULT NULL,
            `mother_aadhar_doc` VARCHAR(255) DEFAULT NULL,

            `pickup_persons_json` LONGTEXT DEFAULT NULL,

            `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            `admin_notes` TEXT DEFAULT NULL,
            `processed_by` INT DEFAULT NULL,
            `processed_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`application_code`),
            INDEX (`status`),
            INDEX (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `online_enrollments`;");
    }
}
