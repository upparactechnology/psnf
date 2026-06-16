<?php
class CreateStudentTimelineTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `student_timeline` (
                `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id`  INT UNSIGNED NOT NULL,
                `event_type`  VARCHAR(100) NOT NULL,
                `title`       VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `meta`        JSON NULL,
                `icon`        VARCHAR(50) NULL DEFAULT 'circle',
                `color`       VARCHAR(20) NULL DEFAULT 'blue',
                `actor_id`    INT UNSIGNED NULL,
                `actor_name`  VARCHAR(191) NULL,
                `occurred_at` DATETIME NOT NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_student` (`student_id`),
                INDEX `idx_event`   (`event_type`),
                INDEX `idx_date`    (`occurred_at`),
                CONSTRAINT `fk_tl_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `student_timeline`"); }
}
