<?php

declare(strict_types=1);

class SplitCertificateAndFileManagerModules
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up(): void
    {
        // ── 1. Move Certificate permissions to their own module ──
        $this->db->prepare("UPDATE permissions SET module = 'Certificate Generator' WHERE slug IN ('view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates')")->execute();

        // ── 2. Add File Manager permissions ──
        $fmPermissions = [
            ['File Manager – View',       'view_file_manager',       'File Manager'],
            ['File Manager – Upload',     'upload_file_manager',     'File Manager'],
            ['File Manager – Delete',     'delete_file_manager',     'File Manager'],
            ['File Manager – Share',      'share_file_manager',      'File Manager'],
        ];

        $ins = $this->db->prepare("INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`, `created_at`) VALUES (?, ?, ?, NOW())");
        foreach ($fmPermissions as [$name, $slug, $module]) {
            $ins->execute([$name, $slug, $module]);
        }

        // Assign File Manager view to super_admin (id=1) and school_admin (id=2)
        $allFmSlugs = ['view_file_manager', 'upload_file_manager', 'delete_file_manager', 'share_file_manager'];
        $findPerm = $this->db->prepare("SELECT id FROM permissions WHERE slug = ?");
        $insRp = $this->db->prepare("INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES (?, ?, NOW())");
        foreach ($allFmSlugs as $slug) {
            $findPerm->execute([$slug]);
            $perm = $findPerm->fetch(PDO::FETCH_ASSOC);
            if ($perm) {
                $insRp->execute([1, (int)$perm['id']]); // super_admin
                $insRp->execute([2, (int)$perm['id']]); // school_admin
            }
        }
    }

    public function down(): void
    {
        // Revert certificate module back to Documents
        $this->db->prepare("UPDATE permissions SET module = 'Documents' WHERE slug IN ('view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates')")->execute();

        // Remove File Manager permissions
        $fmSlugs = ['view_file_manager', 'upload_file_manager', 'delete_file_manager', 'share_file_manager'];
        $placeholders = implode(',', array_fill(0, count($fmSlugs), '?'));
        $this->db->prepare("DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT id FROM permissions WHERE slug IN ($placeholders))")->execute($fmSlugs);
        $this->db->prepare("DELETE FROM `permissions` WHERE slug IN ($placeholders)")->execute($fmSlugs);
    }
}
