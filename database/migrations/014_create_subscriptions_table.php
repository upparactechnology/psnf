<?php
class CreateSubscriptionsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `subscriptions` (
                `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id`      INT UNSIGNED NOT NULL,
                `plan_id`        INT UNSIGNED NOT NULL,
                `status`         ENUM('active','expired','cancelled','trialing','past_due') NOT NULL DEFAULT 'active',
                `starts_at`      DATETIME NOT NULL,
                `ends_at`        DATETIME NULL,
                `trial_ends_at`  DATETIME NULL,
                `cancelled_at`   DATETIME NULL,
                `amount_paid`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `currency`       VARCHAR(10) NOT NULL DEFAULT 'INR',
                `payment_method` VARCHAR(50) NULL,
                `payment_ref`    VARCHAR(255) NULL,
                `notes`          TEXT NULL,
                `created_by`     INT UNSIGNED NULL,
                `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`     TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_tenant` (`tenant_id`),
                INDEX `idx_status` (`status`),
                CONSTRAINT `fk_sub_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_sub_plan`   FOREIGN KEY (`plan_id`)   REFERENCES `plans`   (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `subscriptions`"); }
}
