<?php
class CreatePermissionsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `permissions` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`        VARCHAR(191) NOT NULL UNIQUE,
                `slug`        VARCHAR(191) NOT NULL UNIQUE,
                `module`      VARCHAR(100) NOT NULL DEFAULT 'general',
                `description` TEXT NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_module` (`module`),
                INDEX `idx_slug`   (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `permissions`"); }
}
