<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require CORE_PATH . '/Application.php';

$app = new \Core\Application();

header('Content-Type: text/plain');

$db = $app->db;
$user = $db->selectOne("SELECT * FROM users WHERE email = 'admin@psnf.edu'");
if ($user) {
    echo "User found:\n";
    print_r($user);
    $roles = $db->select(
        "SELECT r.* FROM roles r
         JOIN user_roles ur ON ur.role_id = r.id
         WHERE ur.user_id = ?",
        [$user['id']]
    );
    echo "Roles:\n";
    print_r($roles);
} else {
    echo "Admin user not found.\n";
}

$allRoles = $db->select("SELECT * FROM roles");
echo "All Roles:\n";
print_r($allRoles);

$allUserRoles = $db->select("SELECT * FROM user_roles");
echo "All User Roles mappings:\n";
print_r($allUserRoles);
