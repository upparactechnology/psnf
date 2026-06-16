<?php
class CreateStudentDocumentsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `student_documents` (
                `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id`    INT UNSIGNED NOT NULL,
                `type`          ENUM('birth_certificate','aadhar','medical_report','disability_certificate','transfer_certificate','photo','other') NOT NULL DEFAULT 'other',
                `title`         VARCHAR(191) NOT NULL,
                `file_name`     VARCHAR(255) NOT NULL,
                `stored_name`   VARCHAR(255) NOT NULL,
                `mime_type`     VARCHAR(100) NOT NULL,
                `file_size`     BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `status`        ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
                `verified_by`   INT UNSIGNED NULL,
                `verified_at`   DATETIME NULL,
                `notes`         TEXT NULL,
                `created_by`    INT UNSIGNED NULL,
                `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`    TIMESTAMP NULL,
                INDEX `idx_student` (`student_id`),
                INDEX `idx_type`    (`type`),
                CONSTRAINT `fk_doc_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `student_documents`"); }
}
