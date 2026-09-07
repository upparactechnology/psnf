<?php
/**
 * Remove portal_access permissions permanently
 * Access: http://localhost/psnf/public/remove_portal_access.php
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

echo "=== Removing portal_access permissions ===\n\n";

// Get permission IDs for portal_access module
$portalPerms = $db->select("SELECT id, slug FROM permissions WHERE module = 'portal_access'");
echo "Found " . count($portalPerms) . " portal_access permissions:\n";
foreach ($portalPerms as $p) {
    echo "  - {$p['slug']} (id: {$p['id']})\n";
}

if (!empty($portalPerms)) {
    $ids = array_column($portalPerms, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Remove from role_permissions first
    $db->query("DELETE FROM role_permissions WHERE permission_id IN ($placeholders)", $ids);
    echo "\n→ Removed from role_permissions\n";

    // Remove from permissions
    $db->query("DELETE FROM permissions WHERE module = 'portal_access'");
    echo "→ Removed from permissions\n";
} else {
    echo "\n→ No portal_access permissions found\n";
}

echo "\nDone! Portal access permissions permanently removed.\n";
echo "\nIMPORTANT: All users must log out and log back in for changes to take effect.\n";
