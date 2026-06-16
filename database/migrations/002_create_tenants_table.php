<?php
class CreateTenantsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `tenants` (
                `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `uuid`         CHAR(36) NOT NULL UNIQUE,
                `name`         VARCHAR(191) NOT NULL,
                `slug`         VARCHAR(100) NOT NULL UNIQUE,
                `domain`       VARCHAR(191) NULL,
                `subdomain`    VARCHAR(100) NULL UNIQUE,
                `logo`         VARCHAR(255) NULL,
                `email`        VARCHAR(191) NULL,
                `phone`        VARCHAR(30) NULL,
                `address`      TEXT NULL,
                `city`         VARCHAR(100) NULL,
                `state`        VARCHAR(100) NULL,
                `country`      VARCHAR(100) NULL DEFAULT 'India',
                `timezone`     VARCHAR(50) NOT NULL DEFAULT 'Asia/Kolkata',
                `plan_id`      INT UNSIGNED NULL,
                `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
                `trial_ends_at`DATETIME NULL,
                `settings`     JSON NULL,
                `created_by`   INT UNSIGNED NULL,
                `updated_by`   INT UNSIGNED NULL,
                `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`   TIMESTAMP NULL,
                INDEX `idx_slug`   (`slug`),
                INDEX `idx_active` (`is_active`),
                INDEX `idx_uuid`   (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `tenants`"); }
}
