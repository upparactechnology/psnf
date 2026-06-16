<?php
class CreateUserRolesTable
{
    public function __construct(private \Core\Database $db) {}
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `user_roles` (
                `user_id`    INT UNSIGNED NOT NULL,
                `role_id`    INT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`user_id`, `role_id`),
                CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    public function down(): void { $this->db->query("DROP TABLE IF EXISTS `user_roles`"); }
}
