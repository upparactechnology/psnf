<?php

declare(strict_types=1);

class SplitCertificateAndFileManagerModules
{
    private \Core\Database $db;

    private const FM_PERMISSIONS = [
        ['File Manager – View',   'view_file_manager',   'File Manager'],
        ['File Manager – Upload', 'upload_file_manager', 'File Manager'],
        ['File Manager – Delete', 'delete_file_manager', 'File Manager'],
        ['File Manager – Share',  'share_file_manager',  'File Manager'],
    ];

    public function __construct(\Core\Database $db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        // ── 1. Move Certificate permissions to their own module (no-op if already moved) ──
        $this->db->query(
            "UPDATE permissions SET module = 'Certificate Generator'
             WHERE slug IN ('view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates')
               AND module <> 'Certificate Generator'"
        );

        // ── 2. Add File Manager permissions (only when the slug is missing) ──
        foreach (self::FM_PERMISSIONS as [$name, $slug, $module]) {
            if (!$this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug])) {
                $this->db->query(
                    "INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, ?, NOW())",
                    [$name, $slug, $module]
                );
            }
        }

        // Assign File Manager permissions to super_admin (id=1) and school_admin (id=2)
        foreach (self::FM_PERMISSIONS as [, $slug]) {
            $perm = $this->db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug]);
            if ($perm) {
                foreach ([1, 2] as $roleId) {
                    $this->db->query(
                        "INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())",
                        [$roleId, (int) $perm['id']]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        $this->db->query("UPDATE permissions SET module = 'Documents' WHERE slug IN ('view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates')");

        $fmSlugs = array_column(self::FM_PERMISSIONS, 1);
        $placeholders = implode(',', array_fill(0, count($fmSlugs), '?'));
        $this->db->query("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug IN ($placeholders))", $fmSlugs);
        $this->db->query("DELETE FROM `permissions` WHERE slug IN ($placeholders)", $fmSlugs);
    }
}
