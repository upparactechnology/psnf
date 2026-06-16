<?php
class CreateStudentMedicalTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `student_medical` (
                `id`                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `student_id`            INT UNSIGNED NOT NULL UNIQUE,
                `allergies`             TEXT NULL,
                `allergy_severity`      ENUM('mild','moderate','severe') NULL,
                `triggers`              TEXT NULL,
                `current_medications`   TEXT NULL,
                `medical_conditions`    TEXT NULL,
                `care_instructions`     TEXT NULL,
                `emergency_protocols`   TEXT NULL,
                `doctor_name`           VARCHAR(191) NULL,
                `doctor_phone`          VARCHAR(30) NULL,
                `hospital_name`         VARCHAR(191) NULL,
                `insurance_provider`    VARCHAR(191) NULL,
                `insurance_number`      VARCHAR(100) NULL,
                `blood_pressure`        VARCHAR(20) NULL,
                `weight_kg`             DECIMAL(5,2) NULL,
                `height_cm`             DECIMAL(5,2) NULL,
                `vision`                VARCHAR(100) NULL,
                `hearing`               VARCHAR(100) NULL,
                `last_checkup_date`     DATE NULL,
                `dietary_restrictions`  TEXT NULL,
                `notes`                 TEXT NULL,
                `created_by`            INT UNSIGNED NULL,
                `updated_by`            INT UNSIGNED NULL,
                `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`            TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`            TIMESTAMP NULL,
                CONSTRAINT `fk_med_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `student_medical`"); }
}
