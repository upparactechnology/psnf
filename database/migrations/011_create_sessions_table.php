<?php
class CreateSessionsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `sessions` (
                `id`            VARCHAR(100) NOT NULL PRIMARY KEY,
                `user_id`       INT UNSIGNED NULL,
                `tenant_id`     INT UNSIGNED NULL,
                `ip_address`    VARCHAR(64) NULL,
                `user_agent`    TEXT NULL,
                `payload`       LONGTEXT NOT NULL,
                `last_activity` INT NOT NULL,
                `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user`     (`user_id`),
                INDEX `idx_activity` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `sessions`"); }
}
