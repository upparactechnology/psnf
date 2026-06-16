<?php
class CreatePasswordResetsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `password_resets` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `email`      VARCHAR(191) NOT NULL,
                `token`      VARCHAR(255) NOT NULL UNIQUE,
                `used`       TINYINT(1) NOT NULL DEFAULT 0,
                `expires_at` DATETIME NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_email` (`email`),
                INDEX `idx_token` (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `password_resets`"); }
}
