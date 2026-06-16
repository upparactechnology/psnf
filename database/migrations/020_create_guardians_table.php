<?php
class CreateGuardiansTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `guardians` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`    INT UNSIGNED NOT NULL,
                `user_id`      INT UNSIGNED NULL,
                `name`         VARCHAR(191) NOT NULL,
                `relationship` VARCHAR(100) NOT NULL DEFAULT 'parent',
                `gender`       ENUM('male','female','other') NULL,
                `email`        VARCHAR(191) NULL,
                `phone`        VARCHAR(30) NOT NULL,
                `alt_phone`    VARCHAR(30) NULL,
                `occupation`   VARCHAR(191) NULL,
                `aadhar`       VARCHAR(20) NULL,
                `address`      TEXT NULL,
                `city`         VARCHAR(100) NULL,
                `state`        VARCHAR(100) NULL,
                `pincode`      VARCHAR(20) NULL,
                `photo`        VARCHAR(255) NULL,
                `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
                `created_by`   INT UNSIGNED NULL,
                `updated_by`   INT UNSIGNED NULL,
                `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`   TIMESTAMP NULL,
                INDEX `idx_tenant` (`tenant_id`),
                INDEX `idx_user`   (`user_id`),
                CONSTRAINT `fk_guardian_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `guardian_student` (
                `guardian_id` INT UNSIGNED NOT NULL,
                `student_id`  INT UNSIGNED NOT NULL,
                `is_primary`  TINYINT(1) NOT NULL DEFAULT 0,
                `can_pickup`  TINYINT(1) NOT NULL DEFAULT 1,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`guardian_id`, `student_id`),
                CONSTRAINT `fk_gs_guardian` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_gs_student`  FOREIGN KEY (`student_id`)  REFERENCES `students`  (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `guardian_student`");
        $this->db->query("DROP TABLE IF EXISTS `guardians`");
    }
}
