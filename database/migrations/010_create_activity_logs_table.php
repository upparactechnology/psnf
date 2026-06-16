<?php
class CreateActivityLogsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `activity_logs` (
                `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`   INT UNSIGNED NULL,
                `school_id`   INT UNSIGNED NULL,
                `branch_id`   INT UNSIGNED NULL,
                `user_id`     INT UNSIGNED NULL,
                `event`       VARCHAR(191) NOT NULL,
                `model`       VARCHAR(100) NULL,
                `model_id`    VARCHAR(50) NULL,
                `description` TEXT NULL,
                `properties`  JSON NULL,
                `ip_address`  VARCHAR(64) NULL,
                `user_agent`  TEXT NULL,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user`   (`user_id`),
                INDEX `idx_event`  (`event`),
                INDEX `idx_tenant` (`tenant_id`),
                INDEX `idx_date`   (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `activity_logs`"); }
}
