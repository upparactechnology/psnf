<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{Student, User, ActivityLog};

class DashboardController extends Controller
{
    public function index(): string
    {
        if (has_role('parent')) {
            $this->redirect('/parent/dashboard');
        }
        if (has_role('teacher')) {
            $this->redirect('/teacher/dashboard');
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
                        'transport', 'file_manager', 'games', 'config'
                    ]);
                } elseif ($rawApp === 'driver_app') {
                    $assignedApps[] = 'transport';
                } elseif ($rawApp === 'teacher_app') {
                    $assignedApps = array_merge($assignedApps, [
                        'academic', 'academic_summary', 'medical', 'games'
                    ]);
                } elseif ($rawApp === 'parents_dashboard') {
                    // Parents dashboard doesn't need admin launcher items
                } else {
                    $assignedApps[] = $rawApp;
                }
            }
            $assignedApps = array_unique($assignedApps);
        } else {
            // Default: if no assignment exists and user is admin/manager, grant all apps.
            // Teachers with no assignments get none.
            if (has_role('super_admin') || has_role('school_admin') || has_role('manager')) {
                $assignedApps = [
                    'academic', 'academic_summary', 'hr', 'access_control', 'finance', 'medical', 
                    'transport', 'file_manager', 'games', 'config'
                ];
            }
        }

        $stats = [
            'total_students'  => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'enrolled'        => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'enrolled' AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'applied'         => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'total_users'     => (int) ($db->selectOne("SELECT COUNT(DISTINCT u.id) as c FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE u.tenant_id = ? AND u.deleted_at IS NULL AND r.slug IN ('super_admin', 'teacher', 'staff', 'driver', 'parent')", [$tenantId])['c'] ?? 0),
            'total_schools'   => (int) ($db->selectOne("SELECT COUNT(*) as c FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
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
