<?php
/**
 * Run migration 050: Split Certificate & File Manager modules
 * Access: http://localhost/psnf/public/run_migration_050.php
 */
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require CORE_PATH . '/helpers.php';
$GLOBALS['config'] = [
    'app'      => require CONFIG_PATH . '/app.php',
    'database' => require CONFIG_PATH . '/database.php',
    'auth'     => require CONFIG_PATH . '/auth.php',
];
require CORE_PATH . '/Database.php';
require CORE_PATH . '/Model.php';
require CORE_PATH . '/Application.php';

$app = new \Core\Application();
header('Content-Type: text/plain');

$db = $app->db;

echo "=== Migration 050: Split Certificate & File Manager Modules ===\n\n";

// 1. Move Certificate permissions to their own module
echo "1. Moving Certificate permissions to 'Certificate Generator' module...\n";
$db->query("UPDATE permissions SET module = 'Certificate Generator' WHERE slug IN ('view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates')");
$affected = $db->selectOne("SELECT COUNT(*) as c FROM permissions WHERE module = 'Certificate Generator'");
echo "   -> {$affected['c']} certificate permissions in 'Certificate Generator' module\n\n";

// 2. Add File Manager permissions
echo "2. Adding File Manager permissions...\n";
$fmPermissions = [
    ['File Manager - View',       'view_file_manager',       'File Manager'],
    ['File Manager - Upload',     'upload_file_manager',     'File Manager'],
    ['File Manager - Delete',     'delete_file_manager',     'File Manager'],
    ['File Manager - Share',      'share_file_manager',      'File Manager'],
];

foreach ($fmPermissions as [$name, $slug, $module]) {
    $existing = $db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug]);
    if (!$existing) {
        $db->insert('permissions', ['name' => $name, 'slug' => $slug, 'module' => $module, 'created_at' => date('Y-m-d H:i:s')]);
        echo "   -> Added: {$slug}\n";
    } else {
        echo "   -> Already exists: {$slug}\n";
    }
}
echo "\n";

// 3. Assign File Manager to super_admin (id=1) and school_admin (id=2)
echo "3. Assigning File Manager permissions to super_admin & school_admin...\n";
foreach (['view_file_manager', 'upload_file_manager', 'delete_file_manager', 'share_file_manager'] as $slug) {
    $perm = $db->selectOne("SELECT id FROM permissions WHERE slug = ?", [$slug]);
    if ($perm) {
        $existing1 = $db->selectOne("SELECT 1 FROM role_permissions WHERE role_id = 1 AND permission_id = ?", [(int)$perm['id']]);
        if (!$existing1) {
            $db->insert('role_permissions', ['role_id' => 1, 'permission_id' => (int)$perm['id'], 'created_at' => date('Y-m-d H:i:s')]);
        }
        $existing2 = $db->selectOne("SELECT 1 FROM role_permissions WHERE role_id = 2 AND permission_id = ?", [(int)$perm['id']]);
        if (!$existing2) {
            $db->insert('role_permissions', ['role_id' => 2, 'permission_id' => (int)$perm['id'], 'created_at' => date('Y-m-d H:i:s')]);
        }
        echo "   -> Assigned {$slug} to super_admin & school_admin\n";
    }
}
echo "\n";

// Verify
echo "=== Verification ===\n";
$modules = $db->select("SELECT module, COUNT(*) as cnt FROM permissions GROUP BY module ORDER BY module");
foreach ($modules as $m) {
    echo "  {$m['module']}: {$m['cnt']} permissions\n";
}
echo "\nDone! Migration 050 complete.\n";
