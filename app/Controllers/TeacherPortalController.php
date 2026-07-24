<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{User, ActivityLog};

class TeacherPortalController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function dashboard(): string
    {
        $sessionUser = $this->auth();
        if (!$sessionUser) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $db = $this->db();

        // Load fresh user data to get updated settings
        $user = User::find($sessionUser['id']);
        if (!$user) {
            Application::$app->response->redirect('/login');
            exit();
        }
        $tenantId = $user['tenant_id'];

        $teacherAttendance = null;
        $lectureTime = $user['lecture_time'] ?? null;
        if ($lectureTime) {
            $today = date('Y-m-d');
            $teacherAttendance = $db->selectOne(
                "SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?",
                [$user['id'], $today]
            );

            if (!$teacherAttendance) {
                $openedAt = date('Y-m-d H:i:s');
                $grace = (int) ($user['grace_period'] ?? 5);

                // Compute cutoff time
                $lectureTimestamp = strtotime($today . ' ' . $lectureTime);
                $cutoffTimestamp = $lectureTimestamp + ($grace * 60);
                $currentTimestamp = time();

                $status = ($currentTimestamp <= $cutoffTimestamp) ? 'on_time' : 'late';

                $db->insert('teacher_attendance', [
                    'tenant_id'       => $user['tenant_id'],
                    'school_id'       => $user['school_id'],
                    'branch_id'       => $user['branch_id'],
                    'user_id'         => $user['id'],
                    'attendance_date' => $today,
                    'opened_at'       => $openedAt,
                    'status'          => $status,
                    'lecture_time'    => $lectureTime,
                    'grace_period'    => $grace
                ]);

                $teacherAttendance = $db->selectOne(
                    "SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?",
                    [$user['id'], $today]
                );

                ActivityLog::log('teacher_attendance_checkin', $user['id'], [
                    'status'    => $status,
                    'opened_at' => $openedAt
                ]);
            }
        }

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
            'total_users'     => (int) ($db->selectOne("SELECT COUNT(DISTINCT u.id) as c FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE u.tenant_id = ? AND u.deleted_at IS NULL AND r.slug IN ('super_admin', 'teacher', 'staff', 'driver', 'parent')", [$tenantId])['c'] ?? 0),
            'total_classes'   => (int) ($db->selectOne("SELECT COUNT(*) as c FROM classes WHERE tenant_id = ?", [$tenantId])['c'] ?? 0),
            'total_roles'     => (int) (($db->selectOne("SELECT COUNT(*) as c FROM roles")['c'] ?? 0)),
            'total_invoices'  => (int) (($db->selectOne("SELECT COUNT(*) as c FROM fee_invoices")['c'] ?? 0)),
            'total_medical_logs' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM student_medical")['c'] ?? 0)),
            'total_routes'    => (int) (($db->selectOne("SELECT COUNT(*) as c FROM routes")['c'] ?? 0)),
            'total_files'     => (int) (($db->selectOne("SELECT COUNT(*) as c FROM resources")['c'] ?? 0)),
            'total_game_sessions' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM game_sessions WHERE tenant_id = ?", [$tenantId])['c'] ?? 0)),
            'total_certificates' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM certificates WHERE tenant_id = ?", [$tenantId])['c'] ?? 0)),
            'total_report_cards' => (int) (($db->selectOne("SELECT COUNT(*) as c FROM student_report_cards WHERE tenant_id = ?", [$tenantId])['c'] ?? 0)),
            'total_guardians' => (int) ($db->selectOne("SELECT COUNT(*) as c FROM guardians WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
        ];

        // Fetch teacher's timetable schedule for today
        $todayDay = date('l'); // e.g. Monday
        $teacherSchedule = $db->select(
            "SELECT * FROM timetables WHERE teacher_name = ? AND day_of_week = ? ORDER BY start_time ASC",
            [$user['name'], $todayDay]
        );

        // Fetch announcements
        $announcements = $db->select(
            "SELECT * FROM announcements WHERE tenant_id = ? AND target_audience IN ('all', 'teachers', 'staff') ORDER BY published_at DESC LIMIT 5",
            [$tenantId]
        );

        return $this->view('teacher/dashboard', compact(
            'user', 'teacherAttendance', 'assignedApps', 'stats', 'teacherSchedule', 'announcements'
        ));
    }
}
