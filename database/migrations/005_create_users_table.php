<?php
class CreateUsersTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `users` (
                `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `uuid`                CHAR(36) NOT NULL UNIQUE,
                `tenant_id`           INT UNSIGNED NOT NULL,
                `school_id`           INT UNSIGNED NOT NULL,
                `branch_id`           INT UNSIGNED NOT NULL,
                `name`                VARCHAR(191) NOT NULL,
                `email`               VARCHAR(191) NOT NULL,
                `phone`               VARCHAR(30) NULL,
                `password`            VARCHAR(255) NOT NULL,
                `avatar`              VARCHAR(255) NULL,
                `gender`              ENUM('male','female','other') NULL,
                `dob`                 DATE NULL,
                `designation`         VARCHAR(100) NULL,
                `employee_id`         VARCHAR(50) NULL,
                `email_verified_at`   DATETIME NULL,
                `phone_verified_at`   DATETIME NULL,
                `two_factor_enabled`  TINYINT(1) NOT NULL DEFAULT 0,
                `two_factor_secret`   VARCHAR(255) NULL,
                `remember_token`      VARCHAR(100) NULL,
                `last_login_at`       DATETIME NULL,
                `last_login_ip`       VARCHAR(64) NULL,
                `login_attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `locked_until`        DATETIME NULL,
                `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
                `settings`            JSON NULL,
                `created_by`          INT UNSIGNED NULL,
                `updated_by`          INT UNSIGNED NULL,
                `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`          TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`          TIMESTAMP NULL,
                UNIQUE KEY `uq_email_tenant` (`email`, `tenant_id`),
                INDEX `idx_tenant`  (`tenant_id`),
                INDEX `idx_school`  (`school_id`),
                INDEX `idx_branch`  (`branch_id`),
                INDEX `idx_uuid`    (`uuid`),
                INDEX `idx_active`  (`is_active`),
                CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_users_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `users`"); }
}
