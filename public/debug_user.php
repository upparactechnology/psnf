<?php
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

// Let's find or create a teacher user
$email = 'teacher@psnf.edu';
$teacher = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
if (!$teacher) {
    echo "Creating teacher user...\n";
    $userId = $db->insert('users', [
        'uuid' => str_uuid(),
        'tenant_id' => 1,
        'school_id' => 1,
        'branch_id' => 1,
        'name' => 'Sarah Jenkins',
        'email' => $email,
        'password' => password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]),
        'designation' => 'Senior Teacher',
        'lecture_time' => '08:00:00',
        'grace_period' => 5,
        'is_active' => 1
    ]);
    // Assign role 'teacher' (id 4)
    $db->insert('user_roles', ['user_id' => $userId, 'role_id' => 4]);
    
    // Assign apps: academic, games, medical
    $db->insert('user_apps', ['user_id' => $userId, 'app_name' => 'academic']);
    $db->insert('user_apps', ['user_id' => $userId, 'app_name' => 'games']);
    $db->insert('user_apps', ['user_id' => $userId, 'app_name' => 'medical']);
    
    echo "Teacher user created with ID: $userId\n";
    $targetId = $userId;
} else {
    echo "Teacher user already exists:\n";
    print_r($teacher);
    $targetId = (int)$teacher['id'];
    
    // Reset/update scheduling & apps to test
    $db->update('users', [
        'lecture_time' => '08:00:00',
        'grace_period' => 5,
    ], 'id = ?', [$targetId]);
    
    $db->query("DELETE FROM user_apps WHERE user_id = ?", [$targetId]);
    $db->insert('user_apps', ['user_id' => $targetId, 'app_name' => 'academic']);
    $db->insert('user_apps', ['user_id' => $targetId, 'app_name' => 'games']);
    $db->insert('user_apps', ['user_id' => $targetId, 'app_name' => 'medical']);
    
    // Check if role is assigned
    $hasRole = $db->selectOne("SELECT 1 FROM user_roles WHERE user_id = ? AND role_id = 4", [$targetId]);
    if (!$hasRole) {
        $db->insert('user_roles', ['user_id' => $targetId, 'role_id' => 4]);
    }
    
    echo "Teacher settings updated.\n";
}

// Clear today's teacher attendance to allow testing fresh check-in
$db->query("DELETE FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?", [
    $targetId,
    date('Y-m-d')
]);
echo "Today's teacher attendance cleared so check-in triggers on login.\n";
