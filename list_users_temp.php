<?php
define('ROOT_PATH', __DIR__);
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
require CORE_PATH . '/helpers.php';
require CORE_PATH . '/Database.php';

$db = new \Core\Database();
$users = $db->select("SELECT id, name, email, designation FROM users");
print_r($users);

$roles = $db->select("SELECT ur.user_id, r.name as role_name FROM user_roles ur JOIN roles r ON ur.role_id = r.id");
print_r($roles);
