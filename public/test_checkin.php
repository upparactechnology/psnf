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
require CORE_PATH . '/Session.php';
require CORE_PATH . '/Controller.php';
require CORE_PATH . '/View.php';
require APP_PATH . '/Models/User.php';
require APP_PATH . '/Models/Student.php';
require APP_PATH . '/Models/ActivityLog.php';
require APP_PATH . '/Controllers/DashboardController.php';
require APP_PATH . '/Controllers/TeacherPortalController.php';
require APP_PATH . '/Controllers/UserController.php';

// Start session
\Core\Session::start();

$app = new \Core\Application();
$db = $app->db;

header('Content-Type: text/plain');

echo "=== STARTING INTEGRATION TESTS ===\n\n";

// 1. Simulating Teacher Login Session
$teacher = $db->selectOne("SELECT * FROM users WHERE email = 'teacher@psnf.edu'");
if (!$teacher) {
    die("Teacher user not found! Please run debug_user.php first.\n");
}

$fullTeacher = \App\Models\User::withRoles((int)$teacher['id']);

echo "1. Simulating Session for Teacher: " . $fullTeacher['name'] . "\n";
\Core\Session::set('user', $fullTeacher);
\Core\Session::set('tenant_id', $fullTeacher['tenant_id']);
\Core\Session::set('school_id', $fullTeacher['school_id']);
\Core\Session::set('branch_id', $fullTeacher['branch_id']);

// Force-clear today's attendance to trigger a fresh check-in
$db->query("DELETE FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?", [
    $fullTeacher['id'],
    date('Y-m-d')
]);
echo "Today's check-in cleared to test fresh check-in.\n";

// Run TeacherPortalController dashboard
echo "2. Invoking TeacherPortalController::dashboard()...\n";
$teacherPortalController = new \App\Controllers\TeacherPortalController();

try {
    $html = $teacherPortalController->dashboard();
    echo "Dashboard index invoked successfully!\n";
    
    // Dump assigned apps from DB
    $userAppsList = $db->select("SELECT app_name FROM user_apps WHERE user_id = ?", [$fullTeacher['id']]);
    $assignedApps = array_column($userAppsList, 'app_name');
    echo "Assigned Apps (expected): " . json_encode($assignedApps) . "\n";
    
    // Check if attendance is recorded in DB
    $att = $db->selectOne("SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?", [
        $fullTeacher['id'],
        date('Y-m-d')
    ]);
    if ($att) {
        echo "✓ Success: Teacher attendance recorded automatically! Status: " . $att['status'] . ", Opened At: " . $att['opened_at'] . "\n";
    } else {
        echo "✗ Failure: Attendance was not recorded.\n";
    }

    // Check if the filtered apps are present in the HTML (ignoring comments)
    $cleanHtml = preg_replace('/<!--.*?-->/s', '', $html);
    $hasAcademic = strpos($cleanHtml, 'Academic Registry') !== false;
    $hasGames = strpos($cleanHtml, 'Learning Games') !== false;
    $hasMedical = strpos($cleanHtml, 'Medical Logs') !== false;
    $hasHR = strpos($cleanHtml, 'HR Directory') !== false;
    $hasAccessControl = strpos($cleanHtml, 'Access Control') !== false;

    echo "App Assignment Filter checks:\n";
    echo "- Academic Registry visible? " . ($hasAcademic ? "YES (Correct)" : "NO (Incorrect)") . "\n";
    echo "- Learning Games visible? " . ($hasGames ? "YES (Correct)" : "NO (Incorrect)") . "\n";
    echo "- Medical Logs visible? " . ($hasMedical ? "YES (Correct)" : "NO (Incorrect)") . "\n";
    echo "- HR Directory visible? " . ($hasHR ? "YES (Incorrect - Should be hidden)" : "NO (Correct - Hidden)") . "\n";
    echo "- Access Control visible? " . ($hasAccessControl ? "YES (Incorrect - Should be hidden)" : "NO (Correct - Hidden)") . "\n";

} catch (\Exception $e) {
    echo "Error running dashboard index: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

// 3. Simulating Admin Login Session
$admin = $db->selectOne("SELECT * FROM users WHERE email = 'admin@psnf.edu'");
if ($admin) {
    $fullAdmin = \App\Models\User::withRoles((int)$admin['id']);
    echo "\n3. Simulating Session for Admin: " . $fullAdmin['name'] . "\n";
    \Core\Session::set('user', $fullAdmin);
    \Core\Session::set('tenant_id', $fullAdmin['tenant_id']);
    \Core\Session::set('school_id', $fullAdmin['school_id']);
    \Core\Session::set('branch_id', $fullAdmin['branch_id']);
    
    echo "4. Invoking UserController::attendanceLog()...\n";
    $userController = new \App\Controllers\UserController();
    try {
        $logHtml = $userController->attendanceLog();
        echo "Attendance log view invoked successfully!\n";
        
        // Verify teacher's name and status appear in the logs
        $hasTeacherName = strpos($logHtml, 'Sarah Jenkins') !== false;
        $hasLateStatus = strpos($logHtml, 'Late') !== false;
        
        echo "Admin Log checks:\n";
        echo "- Teacher Name 'Sarah Jenkins' in logs? " . ($hasTeacherName ? "YES (Correct)" : "NO (Incorrect)") . "\n";
        echo "- Status 'Late' visible in logs? " . ($hasLateStatus ? "YES (Correct)" : "NO (Incorrect)") . "\n";
    } catch (\Exception $e) {
        echo "Error running attendanceLog: " . $e->getMessage() . "\n";
    }
} else {
    echo "Admin user admin@psnf.edu not found to verify log views.\n";
}

echo "\n=== TESTS COMPLETED ===\n";
        
        echo "Admin Log checks:\n";
        echo "- Teacher Name 'Sarah Jenkins' in logs? " . ($hasTeacherName ? "YES (Correct)" : "NO (Incorrect)") . "\n";
        echo "- Status 'Late' visible in logs? " . ($hasLateStatus ? "YES (Correct)" : "NO (Incorrect)") . "\n";
    } catch (\Exception $e) {
        echo "Error running attendanceLog: " . $e->getMessage() . "\n";
    }

    echo "\n5. Invoking DashboardController::index() as Admin...\n";
    $dashboardController = new \App\Controllers\DashboardController();
    try {
        $adminHtml = $dashboardController->index();
        $cleanAdminHtml = preg_replace('/<!--.*?-->/s', '', $adminHtml);
        
        $hasAdminSchedule = strpos($cleanAdminHtml, "Today's Class Schedule") !== false || strpos($cleanAdminHtml, "No lectures scheduled for today") !== false;
        $hasAdminAnnouncements = strpos($cleanAdminHtml, "Recent Announcements") !== false || strpos($cleanAdminHtml, "No announcements posted yet") !== false;
        $hasAdminPipeline = strpos($cleanAdminHtml, "Admissions Pipeline") !== false;
        $hasAdminRecentStudents = strpos($cleanAdminHtml, "Recent Student Applications") !== false;
        $hasAdminAuditLogsButton = strpos($cleanAdminHtml, "Audit Trail Logs") !== false;
        
        echo "Admin Widget visibility checks:\n";
        echo "- Class Schedule visible? " . ($hasAdminSchedule ? "YES (Incorrect - Should be hidden)" : "NO (Correct - Hidden)") . "\n";
        echo "- Recent Announcements visible? " . ($hasAdminAnnouncements ? "YES (Incorrect - Should be hidden)" : "NO (Correct - Hidden)") . "\n";
        echo "- Admissions Pipeline visible? " . ($hasAdminPipeline ? "YES (Correct)" : "NO (Incorrect)") . "\n";
        echo "- Recent Student Applications visible? " . ($hasAdminRecentStudents ? "YES (Correct)" : "NO (Incorrect)") . "\n";
        echo "- Audit Trail Logs button visible? " . ($hasAdminAuditLogsButton ? "YES (Correct)" : "NO (Incorrect)") . "\n";
    } catch (\Exception $e) {
        echo "Error running admin dashboard index: " . $e->getMessage() . "\n";
    }
} else {
    echo "Admin user admin@psnf.edu not found to verify log views.\n";
}

echo "\n=== TESTS COMPLETED ===\n";
