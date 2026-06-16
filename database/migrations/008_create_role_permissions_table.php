<?php
class CreateRolePermissionsTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `role_permissions` (
                `role_id`       INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`role_id`, `permission_id`),
                CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `role_permissions`"); }
}
