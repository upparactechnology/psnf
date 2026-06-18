<?php

declare(strict_types=1);

class CreateParentPortalTables
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        // Disable foreign key checks while defining schema
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Attendance
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `attendance` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`   INT UNSIGNED NOT NULL,
                `school_id`   INT UNSIGNED NOT NULL,
                `branch_id`   INT UNSIGNED NOT NULL,
                `student_id`  INT UNSIGNED NOT NULL,
                `date`        DATE NOT NULL,
                `status`      ENUM('present', 'absent', 'late', 'half_day') NOT NULL DEFAULT 'present',
                `remarks`     TEXT NULL,
                `created_by`  INT UNSIGNED NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_student_date` (`student_id`, `date`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 2. Timetables (Class Schedule)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `timetables` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`    INT UNSIGNED NOT NULL,
                `school_id`    INT UNSIGNED NOT NULL,
                `branch_id`    INT UNSIGNED NOT NULL,
                `class`        VARCHAR(50) NOT NULL,
                `section`      VARCHAR(20) NULL,
                `day_of_week`  ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
                `subject`      VARCHAR(100) NOT NULL,
                `teacher_name` VARCHAR(100) NULL,
                `room`         VARCHAR(50) NULL,
                `start_time`   TIME NOT NULL,
                `end_time`     TIME NOT NULL,
                `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 3. Homeworks
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `homeworks` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`   INT UNSIGNED NOT NULL,
                `school_id`   INT UNSIGNED NOT NULL,
                `branch_id`   INT UNSIGNED NOT NULL,
                `class`       VARCHAR(50) NOT NULL,
                `section`     VARCHAR(20) NULL,
                `subject`     VARCHAR(100) NOT NULL,
                `title`       VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `file_path`   VARCHAR(500) NULL,
                `due_date`    DATE NOT NULL,
                `assigned_at` DATE NOT NULL,
                `created_by`  INT UNSIGNED NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 4. Exam Results
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `exam_results` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`      INT UNSIGNED NOT NULL,
                `school_id`      INT UNSIGNED NOT NULL,
                `branch_id`      INT UNSIGNED NOT NULL,
                `student_id`     INT UNSIGNED NOT NULL,
                `exam_name`      VARCHAR(100) NOT NULL,
                `subject`        VARCHAR(100) NOT NULL,
                `marks_obtained` DECIMAL(5,2) NOT NULL,
                `max_marks`      DECIMAL(5,2) NOT NULL,
                `grade`          VARCHAR(10) NULL,
                `remarks`        TEXT NULL,
                `date_published` DATE NULL,
                `created_by`     INT UNSIGNED NULL,
                `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 5. Announcements
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `announcements` (
                `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`       INT UNSIGNED NOT NULL,
                `school_id`       INT UNSIGNED NOT NULL,
                `branch_id`       INT UNSIGNED NOT NULL,
                `title`           VARCHAR(255) NOT NULL,
                `content`         TEXT NOT NULL,
                `target_audience` ENUM('all', 'parents', 'teachers', 'staff') NOT NULL DEFAULT 'all',
                `published_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_by`      INT UNSIGNED NULL,
                `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 6. Certificates
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `certificates` (
                `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`        INT UNSIGNED NOT NULL,
                `school_id`        INT UNSIGNED NOT NULL,
                `branch_id`        INT UNSIGNED NOT NULL,
                `student_id`       INT UNSIGNED NOT NULL,
                `title`            VARCHAR(255) NOT NULL,
                `certificate_type` VARCHAR(100) NOT NULL,
                `file_path`        VARCHAR(500) NOT NULL,
                `issued_at`        DATE NOT NULL,
                `created_by`       INT UNSIGNED NULL,
                `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 7. Fee Invoices
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `fee_invoices` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`      INT UNSIGNED NOT NULL,
                `school_id`      INT UNSIGNED NOT NULL,
                `branch_id`      INT UNSIGNED NOT NULL,
                `student_id`     INT UNSIGNED NOT NULL,
                `invoice_number` VARCHAR(50) NOT NULL,
                `title`          VARCHAR(255) NOT NULL,
                `description`    TEXT NULL,
                `amount`         DECIMAL(10,2) NOT NULL,
                `due_date`       DATE NOT NULL,
                `status`         ENUM('unpaid', 'paid', 'partially_paid', 'void') NOT NULL DEFAULT 'unpaid',
                `paid_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid_at`        DATETIME NULL,
                `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_invoice_number` (`invoice_number`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 8. Fee Payments
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `fee_payments` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`      INT UNSIGNED NOT NULL,
                `school_id`      INT UNSIGNED NOT NULL,
                `branch_id`      INT UNSIGNED NOT NULL,
                `invoice_id`     INT UNSIGNED NOT NULL,
                `amount`         DECIMAL(10,2) NOT NULL,
                `payment_method` VARCHAR(50) NOT NULL,
                `payment_ref`    VARCHAR(255) NULL,
                `paid_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 9. Transport Routes
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `transport_routes` (
                `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`         INT UNSIGNED NOT NULL,
                `school_id`         INT UNSIGNED NOT NULL,
                `branch_id`         INT UNSIGNED NOT NULL,
                `route_name`        VARCHAR(255) NOT NULL,
                `bus_number`        VARCHAR(50) NOT NULL,
                `driver_name`       VARCHAR(255) NOT NULL,
                `driver_phone`      VARCHAR(30) NOT NULL,
                `current_latitude`  DECIMAL(10,8) NULL,
                `current_longitude` DECIMAL(11,8) NULL,
                `current_speed`     DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `status`            ENUM('inactive', 'en_route', 'completed') NOT NULL DEFAULT 'inactive',
                `last_updated_at`   TIMESTAMP NULL,
                `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 10. Student Transport (Mapping)
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `student_transport` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id`   INT UNSIGNED NOT NULL,
                `route_id`     INT UNSIGNED NOT NULL,
                `pickup_point` VARCHAR(255) NULL,
                `pickup_time`  TIME NULL,
                `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_student_route` (`student_id`),
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`route_id`) REFERENCES `transport_routes`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 11. Communication Messages
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `communication_messages` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`   INT UNSIGNED NOT NULL,
                `school_id`   INT UNSIGNED NOT NULL,
                `branch_id`   INT UNSIGNED NOT NULL,
                `sender_id`   INT UNSIGNED NOT NULL,
                `receiver_id` INT UNSIGNED NOT NULL,
                `subject`     VARCHAR(255) NULL,
                `message`     TEXT NOT NULL,
                `is_read`     TINYINT(1) NOT NULL DEFAULT 0,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Enable foreign key checks back
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    public function down(): void
    {
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->db->query("DROP TABLE IF EXISTS `communication_messages`");
        $this->db->query("DROP TABLE IF EXISTS `student_transport`");
        $this->db->query("DROP TABLE IF EXISTS `transport_routes`");
        $this->db->query("DROP TABLE IF EXISTS `fee_payments`");
        $this->db->query("DROP TABLE IF EXISTS `fee_invoices`");
        $this->db->query("DROP TABLE IF EXISTS `certificates`");
        $this->db->query("DROP TABLE IF EXISTS `announcements`");
        $this->db->query("DROP TABLE IF EXISTS `exam_results`");
        $this->db->query("DROP TABLE IF EXISTS `homeworks`");
        $this->db->query("DROP TABLE IF EXISTS `timetables`");
        $this->db->query("DROP TABLE IF EXISTS `attendance`");
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}
