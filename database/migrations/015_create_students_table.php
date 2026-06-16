<?php
class CreateStudentsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `students` (
                `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `uuid`              CHAR(36) NOT NULL UNIQUE,
                `tenant_id`         INT UNSIGNED NOT NULL,
                `school_id`         INT UNSIGNED NOT NULL,
                `branch_id`         INT UNSIGNED NOT NULL,
                `admission_number`  VARCHAR(50) NULL UNIQUE,
                `gr_number`         VARCHAR(50) NULL UNIQUE,
                `first_name`        VARCHAR(100) NOT NULL,
                `middle_name`       VARCHAR(100) NULL,
                `last_name`         VARCHAR(100) NOT NULL,
                `gender`            ENUM('male','female','other') NOT NULL,
                `dob`               DATE NOT NULL,
                `photo`             VARCHAR(255) NULL,
                `blood_group`       ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown') NULL DEFAULT 'Unknown',
                `nationality`       VARCHAR(100) NULL DEFAULT 'Indian',
                `religion`          VARCHAR(100) NULL,
                `mother_tongue`     VARCHAR(100) NULL,
                `aadhar_number`     VARCHAR(20) NULL,
                `disability_type`   ENUM('ASD','ADHD','Down Syndrome','Cerebral Palsy','Dyslexia','Intellectual Disability','Hearing Impairment','Visual Impairment','Multiple Disabilities','Other') NOT NULL,
                `disability_detail` TEXT NULL,
                `disability_certificate` VARCHAR(255) NULL,
                `care_instructions` TEXT NULL,
                `special_needs_summary` TEXT NULL,
                `address`           TEXT NULL,
                `city`              VARCHAR(100) NULL,
                `state`             VARCHAR(100) NULL,
                `pincode`           VARCHAR(20) NULL,
                `admission_status`  ENUM('applied','review','assessment','approved','enrolled','withdrawn','graduated') NOT NULL DEFAULT 'applied',
                `admission_date`    DATE NULL,
                `enrolled_date`     DATE NULL,
                `withdrawal_date`   DATE NULL,
                `withdrawal_reason` TEXT NULL,
                `class`             VARCHAR(50) NULL,
                `section`           VARCHAR(20) NULL,
                `academic_year`     VARCHAR(20) NULL,
                `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
                `notes`             TEXT NULL,
                `created_by`        INT UNSIGNED NULL,
                `updated_by`        INT UNSIGNED NULL,
                `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`        TIMESTAMP NULL,
                INDEX `idx_tenant`  (`tenant_id`),
                INDEX `idx_school`  (`school_id`),
                INDEX `idx_branch`  (`branch_id`),
                INDEX `idx_status`  (`admission_status`),
                INDEX `idx_uuid`    (`uuid`),
                FULLTEXT INDEX `ft_name` (`first_name`, `last_name`),
                CONSTRAINT `fk_students_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`  (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_students_school` FOREIGN KEY (`school_id`) REFERENCES `schools`  (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_students_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `students`"); }
}
