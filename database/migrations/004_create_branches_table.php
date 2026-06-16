<?php
class CreateBranchesTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `branches` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`  INT UNSIGNED NOT NULL,
                `school_id`  INT UNSIGNED NOT NULL,
                `name`       VARCHAR(191) NOT NULL,
                `code`       VARCHAR(50) NULL,
                `email`      VARCHAR(191) NULL,
                `phone`      VARCHAR(30) NULL,
                `address`    TEXT NULL,
                `city`       VARCHAR(100) NULL,
                `is_main`    TINYINT(1) NOT NULL DEFAULT 0,
                `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
                `created_by` INT UNSIGNED NULL,
                `updated_by` INT UNSIGNED NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at` TIMESTAMP NULL,
                INDEX `idx_school` (`school_id`),
                INDEX `idx_tenant` (`tenant_id`),
                CONSTRAINT `fk_branches_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_branches_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `branches`"); }
}
