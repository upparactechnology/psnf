<?php
class CreateRolesTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `roles` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`   INT UNSIGNED NULL,
                `name`        VARCHAR(100) NOT NULL,
                `slug`        VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `is_system`   TINYINT(1) NOT NULL DEFAULT 0,
                `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order`  INT NOT NULL DEFAULT 0,
                `created_by`  INT UNSIGNED NULL,
                `updated_by`  INT UNSIGNED NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`  TIMESTAMP NULL,
                UNIQUE KEY `uq_role_tenant` (`slug`, `tenant_id`),
                INDEX `idx_tenant` (`tenant_id`),
                INDEX `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `roles`"); }
}
