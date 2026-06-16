<?php
class CreatePlansTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `plans` (
                `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name`        VARCHAR(100) NOT NULL,
                `slug`        VARCHAR(100) NOT NULL UNIQUE,
                `description` TEXT NULL,
                `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `billing_cycle` ENUM('monthly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
                `max_users`   INT UNSIGNED NOT NULL DEFAULT 50,
                `max_students`INT UNSIGNED NOT NULL DEFAULT 500,
                `max_branches`INT UNSIGNED NOT NULL DEFAULT 5,
                `features`    JSON NULL,
                `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order`  INT NOT NULL DEFAULT 0,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at`  TIMESTAMP NULL,
                INDEX `idx_slug` (`slug`),
                INDEX `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `plans`"); }
}
