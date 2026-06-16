<?php
class CreateRateLimitsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `rate_limits` (
                `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `key`        VARCHAR(255) NOT NULL UNIQUE,
                `attempts`   INT UNSIGNED NOT NULL DEFAULT 1,
                `expires_at` DATETIME NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_key` (`key`),
                INDEX `idx_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `rate_limits`"); }
}
