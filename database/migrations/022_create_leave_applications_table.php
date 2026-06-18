<?php

declare(strict_types=1);

class CreateLeaveApplicationsTable
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `leave_applications` (
                `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`           INT UNSIGNED NOT NULL,
                `school_id`           INT UNSIGNED NOT NULL,
                `branch_id`           INT UNSIGNED NOT NULL,
                `student_id`          INT UNSIGNED NOT NULL,
                `start_date`          DATE NOT NULL,
                `end_date`            DATE NOT NULL,
                `leave_type`          VARCHAR(50) NOT NULL,
                `reason`              TEXT NOT NULL,
                `medical_certificate` VARCHAR(500) NULL,
                `status`              ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
                `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`          TIMESTAMP NULL,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `leave_applications`");
    }
}
