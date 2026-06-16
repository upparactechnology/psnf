<?php
class CreateOtpCodesTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `otp_codes` (
                `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id`    INT UNSIGNED NULL,
                `identifier` VARCHAR(191) NOT NULL,
                `code`       VARCHAR(10) NOT NULL,
                `type`       ENUM('login','email_verify','phone_verify','2fa','password_reset') NOT NULL DEFAULT 'login',
                `used`       TINYINT(1) NOT NULL DEFAULT 0,
                `attempts`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `expires_at` DATETIME NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_identifier` (`identifier`),
                INDEX `idx_code`       (`code`),
                INDEX `idx_expires`    (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `otp_codes`"); }
}
