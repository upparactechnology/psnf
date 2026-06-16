<?php
class CreateEmergencyContactsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `emergency_contacts` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id`   INT UNSIGNED NOT NULL,
                `name`         VARCHAR(191) NOT NULL,
                `relationship` VARCHAR(100) NOT NULL,
                `phone`        VARCHAR(30) NOT NULL,
                `alt_phone`    VARCHAR(30) NULL,
                `email`        VARCHAR(191) NULL,
                `address`      TEXT NULL,
                `is_primary`   TINYINT(1) NOT NULL DEFAULT 0,
                `priority`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
                `notes`        TEXT NULL,
                `created_by`   INT UNSIGNED NULL,
                `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`   TIMESTAMP NULL,
                INDEX `idx_student` (`student_id`),
                CONSTRAINT `fk_ec_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `emergency_contacts`"); }
}
