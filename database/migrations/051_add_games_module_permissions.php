<?php

declare(strict_types=1);

class AddGamesModulePermissions
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        // Games module permissions
        $permissions = [
            ['Games Overview – View',        'view_games_overview',        'Games'],
            ['Games – View',                 'view_games',                 'Games'],
            ['Games – Create',               'create_games',               'Games'],
            ['Games – Edit',                 'edit_games',                 'Games'],
            ['Games – Delete',               'delete_games',               'Games'],
        ];

        // Insert permissions (skip duplicates)
        $ins = $this->db->prepare("INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, ?, NOW())");
        foreach ($permissions as [$name, $slug, $module]) {
            $ins->execute([$name, $slug, $module]);
        }

        // Assign to Super Admin (id=1) and School Admin (id=2)
        $allNew = $this->db->query("SELECT id FROM permissions WHERE module = 'Games'")->fetchAll(PDO::FETCH_ASSOC);
        $insRp = $this->db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())");
        foreach ($allNew as $perm) {
            $insRp->execute([1, (int)$perm['id']]);
            $insRp->execute([2, (int)$perm['id']]);
        }
    }

    public function down(): void
    {
        $slugs = ['view_games_overview', 'view_games', 'create_games', 'edit_games', 'delete_games'];
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $this->db->prepare("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug IN ($placeholders))")->execute($slugs);
        $this->db->prepare("DELETE FROM `permissions` WHERE slug IN ($placeholders)")->execute($slugs);
    }
}
