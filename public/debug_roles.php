<?php
/**
 * Debug script: Check ashini Shah's actual roles and permissions
 * Access: http://localhost/psnf/public/debug_roles.php
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
require APP_PATH . '/Models/User.php';

$app = new \Core\Application();
header('Content-Type: text/plain');

$db = $app->db;

// Find ashini Shah
$user = $db->selectOne("SELECT * FROM users WHERE name LIKE '%ashini%' OR name LIKE '%Ashini%' LIMIT 1");
if (!$user) {
    echo "No user found matching 'ashini'\n";
    echo "Listing all users:\n";
    $all = $db->select("SELECT id, name, email FROM users WHERE deleted_at IS NULL ORDER BY id");
    foreach ($all as $u) {
        echo "  ID={$u['id']} | {$u['name']} | {$u['email']}\n";
    }
    exit;
}

echo "=== USER: {$user['name']} (ID: {$user['id']}) ===\n";
echo "Email: {$user['email']}\n";
echo "Active: {$user['is_active']}\n\n";

// Get roles
$roles = $db->select(
    "SELECT r.id, r.name, r.slug FROM roles r
     JOIN user_roles ur ON ur.role_id = r.id
     WHERE ur.user_id = ? AND r.deleted_at IS NULL",
    [$user['id']]
);
echo "=== ASSIGNED ROLES ===\n";
foreach ($roles as $r) {
    echo "  [{$r['id']}] {$r['name']} (slug: {$r['slug']})\n";
}
echo "\n";

// Get permissions
$permissions = $db->select(
    "SELECT DISTINCT p.slug, p.module FROM permissions p
     JOIN role_permissions rp ON rp.permission_id = p.id
     JOIN user_roles ur ON ur.role_id = rp.role_id
     WHERE ur.user_id = ?
     ORDER BY p.module, p.slug",
    [$user['id']]
);
echo "=== ASSIGNED PERMISSIONS (" . count($permissions) . " total) ===\n";
$grouped = [];
foreach ($permissions as $p) {
    $grouped[$p['module']][] = $p['slug'];
}
foreach ($grouped as $module => $slugs) {
    echo "\n  [{$module}]\n";
    foreach ($slugs as $s) {
        echo "    - {$s}\n";
    }
}

// Check specific permissions used in dashboard
echo "\n=== DASHBOARD PERMISSION CHECKS ===\n";
$dashPerms = [
    'Academics' => ['view_students_list', 'view_classes', 'view_teachers', 'view_acad_attendance'],
    'Staff Mgmt' => ['view_staff_overview', 'view_staff_directory'],
    'Finance' => ['view_fees_dashboard', 'view_all_invoices'],
    'Reports' => ['view_reports_overview', 'view_student_reports'],
    'Learning Games' => ['view_students_list'],
];
foreach ($dashPerms as $section => $perms) {
    $has = false;
    foreach ($perms as $p) {
        if (in_array($p, array_column($permissions, 'slug'))) {
            $has = true;
            break;
        }
    }
    echo "  {$section}: " . ($has ? 'YES (will show)' : 'NO (should hide)') . "\n";
}
