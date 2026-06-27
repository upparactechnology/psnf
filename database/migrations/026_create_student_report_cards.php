<?php

declare(strict_types=1);

class CreateStudentReportCards
{
    public function __construct(private \Core\Database $db) {}

    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `student_report_cards` (
                `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`            INT UNSIGNED NOT NULL,
                `school_id`            INT UNSIGNED NOT NULL,
                `branch_id`            INT UNSIGNED NOT NULL,
                `student_id`           INT UNSIGNED NOT NULL,
                `academic_year`        VARCHAR(20) NOT NULL, -- e.g., '2023-24'
                `semester`             VARCHAR(20) NOT NULL, -- e.g., 'Semester 1' or 'Semester 2'
                `routine_profile`      JSON NOT NULL,
                `learning_skills`      JSON NOT NULL,
                `academic_profile`     JSON NOT NULL,
                `cocurriculum_profile` JSON NOT NULL,
                `attendance_profile`   JSON NOT NULL,
                `feedback_text`        TEXT NULL,
                `authorized_by`        JSON NULL,
                `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_student_semester` (`student_id`, `academic_year`, `semester`),
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `student_report_cards`");
    }
}
