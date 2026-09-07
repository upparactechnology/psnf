<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{Student, User, ActivityLog};

class DashboardController extends Controller
{
    public function index(): string
    {
        if (!has_role('super_admin') && !has_role('school_admin') && !has_role('manager') && !has_role('teacher')) {
            if (has_role('parent')) {
                $this->redirect('/parent/dashboard');
            }
        }

        $sessionUser = $this->auth();
        $db = \Core\Application::$app->db;

        // Load fresh user data to get updated settings
        $user = User::find($sessionUser['id']);
        $tenantId = $user['tenant_id'];

        // Teacher attendance auto check-in
        if (has_role('teacher')) {
            $today = date('Y-m-d');
            $todayDayOfWeek = date('l');
            $teacherAttendance = $db->selectOne(
                "SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?",
                [$user['id'], $today]
            );
            $firstLecture = $db->selectOne(
                "SELECT start_time FROM timetables WHERE teacher_name = ? AND day_of_week = ? ORDER BY start_time ASC LIMIT 1",
                [$user['name'], $todayDayOfWeek]
            );
            $lectureTime = $firstLecture['start_time'] ?? ($user['lecture_time'] ?? null);
            if ($lectureTime && !$teacherAttendance) {
                $openedAt = date('Y-m-d H:i:s');
                $shiftPolicy = $db->selectOne("SELECT lec_grace_minutes FROM shift_templates WHERE id = 1");
                $grace = (int) ($shiftPolicy['lec_grace_minutes'] ?? ($user['grace_period'] ?? 5));
                $lectureTimestamp = strtotime($today . ' ' . $lectureTime);
                $cutoffTimestamp = $lectureTimestamp + ($grace * 60);
                $status = (time() <= $cutoffTimestamp) ? 'on_time' : 'late';
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
                \App\Models\ActivityLog::log('teacher_attendance_checkin', $user['id'], [
                    'status'    => $status,
                    'opened_at' => $openedAt
                ]);
            }
        }

        // Determine assigned apps - teachers and admins get full access
        $assignedApps = [
            'academic', 'academic_summary', 'hr', 'access_control', 'finance', 
            'transport', 'file_manager', 'games', 'config', 'report_cards'
        ];

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

        $queryCount = function($sql, $params = []) use ($db) {
            try {
                return (int)($db->selectOne($sql, $params)['c'] ?? 0);
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $stats = [
            'total_students'  => $queryCount("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId]),
            'enrolled'        => $queryCount("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'enrolled' AND deleted_at IS NULL", [$tenantId]),
            'applied'         => $queryCount("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL", [$tenantId]),
            'total_users'     => $queryCount("SELECT COUNT(DISTINCT u.id) as c FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE u.tenant_id = ? AND u.deleted_at IS NULL AND r.slug IN ('super_admin', 'teacher', 'staff', 'driver', 'parent')", [$tenantId]),
            'total_schools'   => $queryCount("SELECT COUNT(*) as c FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId]),
            'total_classes'   => $queryCount("SELECT COUNT(*) as c FROM classes WHERE tenant_id = ?", [$tenantId]),
            'total_roles'     => $queryCount("SELECT COUNT(*) as c FROM roles"),
            'total_invoices'  => $queryCount("SELECT COUNT(*) as c FROM fee_invoices WHERE tenant_id = ?", [$tenantId]),
            'total_routes'    => $queryCount("SELECT COUNT(*) as c FROM routes"),
            'total_files'     => $queryCount("SELECT COUNT(*) as c FROM resources"),
            'total_game_sessions' => $queryCount("SELECT COUNT(*) as c FROM game_sessions"),
            'total_certificates' => $queryCount("SELECT COUNT(*) as c FROM certificates"),
            'total_report_cards' => $queryCount("SELECT COUNT(*) as c FROM student_report_cards"),
            'total_guardians' => $queryCount("SELECT COUNT(*) as c FROM guardians WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId]),
        ];

        $statusCounts = Student::statusCounts();
        $recentLogs   = ActivityLog::recent(10);
        $recentStudents = $db->select(
            "SELECT s.*, b.name as branch_name FROM students s LEFT JOIN branches b ON b.id = s.branch_id
             WHERE s.tenant_id = ? AND s.deleted_at IS NULL ORDER BY s.created_at DESC LIMIT 5",
            [$tenantId]
        );

        // Upcoming birthdays (next 2 days) - students and staff
        $today = date('Y-m-d');
        $twoDaysLater = date('Y-m-d', strtotime('+2 days'));
        $todayMD = date('m-d');
        $twoDaysMD = date('m-d', strtotime('+2 days'));

        $birthdayStudents = $db->select(
            "SELECT id, first_name, last_name, dob, 'student' as type FROM students
             WHERE tenant_id = ? AND deleted_at IS NULL AND dob IS NOT NULL
             AND (
                (MONTH(dob) = ? AND DAY(dob) >= ?)
                OR (MONTH(dob) = ? AND DAY(dob) <= ?)
                OR (MONTH(dob) > ? AND MONTH(dob) < ?)
             )
             ORDER BY MONTH(dob), DAY(dob) LIMIT 10",
            [$tenantId, (int)date('m', strtotime($today)), (int)date('d', strtotime($today)),
             (int)date('m', strtotime($twoDaysLater)), (int)date('d', strtotime($twoDaysLater)),
             (int)date('m', strtotime($today)), (int)date('m', strtotime($twoDaysLater))]
        );

        $birthdayStaff = $db->select(
            "SELECT id, name, dob, 'staff' as type FROM users
             WHERE tenant_id = ? AND deleted_at IS NULL AND dob IS NOT NULL
             AND (
                (MONTH(dob) = ? AND DAY(dob) >= ?)
                OR (MONTH(dob) = ? AND DAY(dob) <= ?)
                OR (MONTH(dob) > ? AND MONTH(dob) < ?)
             )
             ORDER BY MONTH(dob), DAY(dob) LIMIT 10",
            [$tenantId, (int)date('m', strtotime($today)), (int)date('d', strtotime($today)),
             (int)date('m', strtotime($twoDaysLater)), (int)date('d', strtotime($twoDaysLater)),
             (int)date('m', strtotime($today)), (int)date('m', strtotime($twoDaysLater))]
        );

        $upcomingBirthdays = array_merge($birthdayStudents, $birthdayStaff);

        return $this->view('dashboard/index', compact(
            'stats', 'statusCounts', 'recentLogs', 'recentStudents', 'user', 'assignedApps', 'upcomingBirthdays'
        ));
    }

    public function games(): string
    {
        return $this->view('dashboard/games');
    }
}
