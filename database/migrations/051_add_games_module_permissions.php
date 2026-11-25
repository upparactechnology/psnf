<?php

declare(strict_types=1);

class AddGamesModulePermissions
{
    private \Core\Database $db;

    private const PERMISSIONS = [
        ['Games Overview – View', 'view_games_overview', 'Games'],
        ['Games – View',          'view_games',          'Games'],
        ['Games – Create',        'create_games',        'Games'],
        ['Games – Edit',          'edit_games',          'Games'],
        ['Games – Delete',        'delete_games',        'Games'],
    ];

    public function __construct(\Core\Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        foreach (self::PERMISSIONS as [$name, $slug, $module]) {
            if (!$this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug])) {
                $this->db->query(
                    "INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, ?, NOW())",
                    [$name, $slug, $module]
                );
            }
        }

        // Assign exactly these permissions to Super Admin (id=1) and School Admin (id=2)
        $slugs = array_column(self::PERMISSIONS, 1);
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $perms = $this->db->select("SELECT id FROM permissions WHERE slug IN ($placeholders)", $slugs);
        foreach ($perms as $perm) {
            foreach ([1, 2] as $roleId) {
                $this->db->query(
                    "INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())",
                    [$roleId, (int) $perm['id']]
                );
            }
        }
    }

    public function down(): void
    {
        $slugs = array_column(self::PERMISSIONS, 1);
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $this->db->query("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug IN ($placeholders))", $slugs);
        $this->db->query("DELETE FROM `permissions` WHERE slug IN ($placeholders)", $slugs);
    }
}
