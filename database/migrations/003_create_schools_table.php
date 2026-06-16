<?php
class CreateSchoolsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `schools` (
                `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`     INT UNSIGNED NOT NULL,
                `name`          VARCHAR(191) NOT NULL,
                `code`          VARCHAR(50) NULL UNIQUE,
                `email`         VARCHAR(191) NULL,
                `phone`         VARCHAR(30) NULL,
                `address`       TEXT NULL,
                `city`          VARCHAR(100) NULL,
                `state`         VARCHAR(100) NULL,
                `pincode`       VARCHAR(20) NULL,
                `logo`          VARCHAR(255) NULL,
                `established_year` YEAR NULL,
                `type`          ENUM('special_needs','regular','both') NOT NULL DEFAULT 'special_needs',
                `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
                `settings`      JSON NULL,
                `created_by`    INT UNSIGNED NULL,
                `updated_by`    INT UNSIGNED NULL,
                `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`    TIMESTAMP NULL,
                INDEX `idx_tenant` (`tenant_id`),
                INDEX `idx_active` (`is_active`),
                CONSTRAINT `fk_schools_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `schools`"); }
}
