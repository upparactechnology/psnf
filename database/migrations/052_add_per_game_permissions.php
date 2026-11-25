<?php

declare(strict_types=1);

class AddPerGamePermissions
{
    private \Core\Database $db;

    private const GAMES = [
        'money_counting'   => 'Money Counting',
        'safe_vs_unsafe'   => 'Safe vs Unsafe',
        'safety_signs'     => 'Safety Signs',
        'sentence_builder' => 'Sentence Builder',
        'shopping_store'   => 'Shopping Store',
    ];

    private const ACTIONS = ['view', 'create', 'edit', 'delete'];

    public function __construct(\Core\Database $db)
    {
        $this->db = $db;
    }

    /** @return array<string,string> slug => name */
    private function permissions(): array
    {
        $out = [];
        foreach (self::GAMES as $slug => $name) {
            foreach (self::ACTIONS as $action) {
                $out["{$action}_game_{$slug}"] = "$name – " . ucfirst($action);
            }
        }
        return $out;
    }

    public function up(): void
    {
        $perms = $this->permissions();

        foreach ($perms as $permSlug => $permName) {
            if (!$this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$permSlug])) {
                $this->db->query(
                    "INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, 'Games', NOW())",
                    [$permName, $permSlug]
                );
            }
        }

        // Assign exactly these slugs (no LIKE wildcards) to Super Admin (1) and School Admin (2)
        $slugs = array_keys($perms);
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $rows = $this->db->select("SELECT id FROM permissions WHERE slug IN ($placeholders)", $slugs);
        foreach ($rows as $row) {
            foreach ([1, 2] as $roleId) {
                $this->db->query(
                    "INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())",
                    [$roleId, (int) $row['id']]
                );
            }
        }
    }

    public function down(): void
    {
        $slugs = array_keys($this->permissions());
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $this->db->query("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug IN ($placeholders))", $slugs);
        $this->db->query("DELETE FROM `permissions` WHERE slug IN ($placeholders)", $slugs);
    }
}
