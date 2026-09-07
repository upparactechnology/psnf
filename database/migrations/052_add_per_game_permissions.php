<?php

declare(strict_types=1);

class AddPerGamePermissions
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        $games = [
            'money_counting'  => 'Money Counting',
            'safe_vs_unsafe'  => 'Safe vs Unsafe',
            'safety_signs'    => 'Safety Signs',
            'sentence_builder' => 'Sentence Builder',
            'shopping_store'  => 'Shopping Store',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        $ins = $this->db->prepare("INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, 'Games', NOW())");

        foreach ($games as $slug => $name) {
            foreach ($actions as $action) {
                $permName = "$name – " . ucfirst($action);
                $permSlug = "${action}_game_${slug}";
                $ins->execute([$permName, $permSlug]);
            }
        }

        $allNew = $this->db->query("SELECT id FROM permissions WHERE slug LIKE '%_game_%'")->fetchAll(PDO::FETCH_ASSOC);
        $insRp = $this->db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())");
        foreach ($allNew as $perm) {
            $insRp->execute([1, (int)$perm['id']]);
            $insRp->execute([2, (int)$perm['id']]);
        }
    }

    public function down(): void
    {
        $this->db->prepare("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug LIKE '%\_game\_%' ESCAPE '\\\\')")->execute();
        $this->db->prepare("DELETE FROM `permissions` WHERE slug LIKE '%\_game\_%' ESCAPE '\\\\'")->execute();
    }
}
