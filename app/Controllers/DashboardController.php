<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{Student, User, ActivityLog};

class DashboardController extends Controller
{
    public function index(): string
    {
        if (!has_role('super_admin') && !has_role('school_admin') && !has_role('manager')) {
            if (has_role('parent')) {
                $this->redirect('/parent/dashboard');
            }
            if (has_role('teacher')) {
                $this->redirect('/teacher/dashboard');
            }
        }

        $sessionUser = $this->auth();
        $db = \Core\Application::$app->db;

        // Load fresh user data to get updated settings
        $user = User::find($sessionUser['id']);
        $tenantId = $user['tenant_id'];

        // Determine assigned apps
        $assignedApps = [];
        $hasAppAccessRecords = $db->selectOne("SELECT 1 FROM user_apps WHERE user_id = ?", [$user['id']]);
        
        if ($hasAppAccessRecords) {
            $userApps = $db->select("SELECT app_name FROM user_apps WHERE user_id = ?", [$user['id']]);
            $rawApps = array_column($userApps, 'app_name');
            foreach ($rawApps as $rawApp) {
                if ($rawApp === 'staff_dashboard') {
                    $assignedApps = array_merge($assignedApps, [
                        'academic', 'academic_summary', 'hr', 'access_control', 'finance', 'medical', 
                        'transport', 'file_manager', 'games', 'config', 'report_cards'
                    ]);
                } elseif ($rawApp === 'driver_app') {
                    $assignedApps[] = 'transport';
                } elseif ($rawApp === 'teacher_app') {
                    $assignedApps = array_merge($assignedApps, [
                        'academic', 'academic_summary', 'medical', 'games', 'report_cards'
                    ]);
                } elseif ($rawApp === 'parents_dashboard') {
                    // Parents dashboard doesn't need admin launcher items
                } else {
                    $assignedApps[] = $rawApp;
                }
            }
            $assignedApps = array_unique($assignedApps);
        } else {
            // Default: if no assignment exists and user is admin/manager/teacher, grant all apps.
            if (has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('teacher')) {
                $assignedApps = [
                    'academic', 'academic_summary', 'hr', 'access_control', 'finance', 'medical', 
                    'transport', 'file_manager', 'games', 'config', 'report_cards'
                ];
            }
        }

        // Ensure classes table exists
        $db->query("
            CREATE TABLE IF NOT EXISTS `classes` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `tenant_id` INT UNSIGNED NOT NULL,
                `school_id` INT UNSIGNED NOT NULL,
                `branch_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `section` VARCHAR(50) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_class_section` (`tenant_id`, `name`, `section`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $stats = [
            'total_students'  => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'enrolled'        => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'enrolled' AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'applied'         => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'total_users'     => (int) ($db->selectOne("SELECT COUNT(DISTINCT u.id) as c FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE u.tenant_id = ? AND u.deleted_at IS NULL AND r.slug IN ('super_admin', 'teacher', 'staff', 'driver', 'parent')", [$tenantId])['c'] ?? 0),
            'total_schools'   => (int) ($db->selectOne("SELECT COUNT(*) as c FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'total_classes'   => (int) ($db->selectOne("SELECT COUNT(*) as c FROM classes WHERE tenant_id = ?", [$tenantId])['c'] ?? 0),
            'total_roles'     => (int) (($db->selectOne("SELECT COUNT(*) as c FROM roles")['c'] ?? 0)),
            'total_invoices'  => (int) (($db->selectOne("SELECT COUNT(*) as c FROM fee_invoices WHERE tenant_id = ?", [$tenantId])['c'] ?? 0)),
            'total_medical_logs' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM student_medical")['c'] ?? 0)),
            'total_routes'    => (int) (($db->selectOne("SELECT COUNT(*) as c FROM routes")['c'] ?? 0)),
            'total_files'     => (int) (($db->selectOne("SELECT COUNT(*) as c FROM resources")['c'] ?? 0)),
            'total_game_sessions' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM game_sessions")['c'] ?? 0)),
            'total_certificates' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM certificates")['c'] ?? 0)),
            'total_report_cards' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM student_report_cards")['c'] ?? 0)),
            'total_guardians' => (int) ($db->selectOne("SELECT COUNT(*) as c FROM guardians WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
        ];

        $statusCounts = Student::statusCounts();
        $recentLogs   = ActivityLog::recent(10);
        $recentStudents = $db->select(
            "SELECT s.*, b.name as branch_name FROM students s LEFT JOIN branches b ON b.id = s.branch_id
             WHERE s.tenant_id = ? AND s.deleted_at IS NULL ORDER BY s.created_at DESC LIMIT 5",
            [$tenantId]
        );

        return $this->view('dashboard/index', compact(
            'stats', 'statusCounts', 'recentLogs', 'recentStudents', 'user', 'assignedApps'
        ));
    }
}
