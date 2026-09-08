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
        $today = date('Y-m-d');

        // Read-only: attendance is now recorded on login in AuthController
        $teacherAttendance = $db->selectOne(
            "SELECT * FROM teacher_attendance WHERE user_id = ? AND attendance_date = ?",
            [$user['id'], $today]
        );

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
            // Default: if no assignment exists and user is teacher, grant teacher apps
            if (has_role('teacher')) {
                $assignedApps = [
                    'academic', 'academic_summary', 'medical', 'games'
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

        $queryCount = function($sql, $params = []) use ($db) {
            try {
                return (int)($db->selectOne($sql, $params)['c'] ?? 0);
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $stats = [
            'total_students'  => $queryCount("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId]),
            'total_users'     => $queryCount("SELECT COUNT(DISTINCT u.id) as c FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE u.tenant_id = ? AND u.deleted_at IS NULL AND r.slug IN ('super_admin', 'teacher', 'staff', 'driver', 'parent')", [$tenantId]),
            'total_classes'   => $queryCount("SELECT COUNT(*) as c FROM classes WHERE tenant_id = ?", [$tenantId]),
            'total_roles'     => $queryCount("SELECT COUNT(*) as c FROM roles"),
            'total_invoices'  => $queryCount("SELECT COUNT(*) as c FROM fee_invoices"),
            'total_routes'    => $queryCount("SELECT COUNT(*) as c FROM routes"),
            'total_files'     => $queryCount("SELECT COUNT(*) as c FROM resources"),
            'total_game_sessions' => $queryCount("SELECT COUNT(*) as c FROM game_sessions WHERE tenant_id = ?", [$tenantId]),
            'total_certificates' => $queryCount("SELECT COUNT(*) as c FROM certificates WHERE tenant_id = ?", [$tenantId]),
            'total_report_cards' => $queryCount("SELECT COUNT(*) as c FROM student_report_cards WHERE tenant_id = ?", [$tenantId]),
            'total_guardians' => $queryCount("SELECT COUNT(*) as c FROM guardians WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId]),
        ];

        // Fetch teacher's timetable schedule for today
        $todayDay = date('l'); // e.g. Monday
        $teacherSchedule = $db->select(
            "SELECT * FROM timetables WHERE teacher_name = ? AND day_of_week = ? ORDER BY start_time ASC",
            [$user['name'], $todayDay]
        );

        // Fetch recently shared timetables for this teacher (last 7 days)
        $sharedTimetables = $db->select(
            "SELECT t.*, u.name as shared_by_name, mg.name as group_name
             FROM timetables t
             LEFT JOIN users u ON t.shared_by = u.id
             LEFT JOIN main_groups mg ON t.main_group_id = mg.id
             WHERE t.teacher_name = ? AND t.shared_at IS NOT NULL
             AND t.shared_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY t.shared_at DESC
             LIMIT 5",
            [$user['name']]
        );

        // Fetch announcements
        $announcements = $db->select(
            "SELECT * FROM announcements WHERE tenant_id = ? AND target_audience IN ('all', 'teachers', 'staff') ORDER BY published_at DESC LIMIT 5",
            [$tenantId]
        );

        // Upcoming birthdays (next 2 days) - staff and students
        $todayDate = date('Y-m-d');
        $twoDaysLater = date('Y-m-d', strtotime('+2 days'));

        $birthdayStaff = $db->select(
            "SELECT id, name, dob, 'staff' as type FROM users
             WHERE tenant_id = ? AND deleted_at IS NULL AND dob IS NOT NULL
             AND (
                (MONTH(dob) = ? AND DAY(dob) >= ?)
                OR (MONTH(dob) = ? AND DAY(dob) <= ?)
                OR (MONTH(dob) > ? AND MONTH(dob) < ?)
             )
             ORDER BY MONTH(dob), DAY(dob) LIMIT 10",
            [$tenantId, (int)date('m', strtotime($todayDate)), (int)date('d', strtotime($todayDate)),
             (int)date('m', strtotime($twoDaysLater)), (int)date('d', strtotime($twoDaysLater)),
             (int)date('m', strtotime($todayDate)), (int)date('m', strtotime($twoDaysLater))]
        );

        $birthdayStudents = $db->select(
            "SELECT id, first_name, last_name, dob, 'student' as type FROM students
             WHERE tenant_id = ? AND deleted_at IS NULL AND dob IS NOT NULL
             AND (
                (MONTH(dob) = ? AND DAY(dob) >= ?)
                OR (MONTH(dob) = ? AND DAY(dob) <= ?)
                OR (MONTH(dob) > ? AND MONTH(dob) < ?)
             )
             ORDER BY MONTH(dob), DAY(dob) LIMIT 10",
            [$tenantId, (int)date('m', strtotime($todayDate)), (int)date('d', strtotime($todayDate)),
             (int)date('m', strtotime($twoDaysLater)), (int)date('d', strtotime($twoDaysLater)),
             (int)date('m', strtotime($todayDate)), (int)date('m', strtotime($twoDaysLater))]
        );

        $upcomingBirthdays = array_merge($birthdayStaff, $birthdayStudents);

        return $this->view('teacher/dashboard', compact(
            'user', 'teacherAttendance', 'assignedApps', 'stats', 'teacherSchedule', 'announcements', 'upcomingBirthdays', 'sharedTimetables'
        ));
    }

    public function markAttendance(): string
    {
        $sessionUser = $this->auth();
        if (!$sessionUser) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $db = $this->db();
        $user = User::find($sessionUser['id']);
        if (!$user) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $selectedClass   = trim((string)$this->request->get('class', ''));
        $selectedSection = trim((string)$this->request->get('section', ''));
        $selectedDate    = trim((string)$this->request->get('date', date('Y-m-d')));

        // Get classes assigned to this teacher (as class teacher)
        $assignedClasses = $db->select(
            "SELECT name as class, COALESCE(section, '') as section FROM classes WHERE class_teacher_id = ?",
            [$user['id']]
        );

        // Auto-select first class if none selected
        if (empty($selectedClass) && !empty($assignedClasses)) {
            $selectedClass = $assignedClasses[0]['class'];
            $selectedSection = $assignedClasses[0]['section'];
        }

        $students = [];
        $attendanceMap = [];

        if ($selectedClass) {
            // Fetch enrolled students for selected class
            $students = $db->select(
                "SELECT * FROM students 
                 WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$user['tenant_id'], $selectedClass, $selectedSection]
            );

            if (empty($students)) {
                $students = $db->select(
                    "SELECT * FROM students 
                     WHERE tenant_id = ? AND class = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                     ORDER BY first_name ASC",
                    [$user['tenant_id'], $selectedClass]
                );
            }

            if (!empty($students)) {
                $studentIds = array_column($students, 'id');
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

                $records = $db->select(
                    "SELECT student_id, status, remarks FROM attendance 
                     WHERE date = ? AND student_id IN ($placeholders)",
                    array_merge([$selectedDate], $studentIds)
                );

                foreach ($records as $r) {
                    $attendanceMap[$r['student_id']] = [
                        'status'  => $r['status'],
                        'remarks' => $r['remarks'],
                    ];
                }
            }
        }

        return $this->view('teacher/mark_attendance', compact(
            'user', 'assignedClasses', 'selectedClass', 'selectedSection', 'selectedDate', 'students', 'attendanceMap'
        ));
    }

    public function saveTeacherAttendance(): string
    {
        $sessionUser = $this->auth();
        if (!$sessionUser) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $db = $this->db();
        $user = User::find($sessionUser['id']);
        if (!$user) {
            Application::$app->response->redirect('/login');
            exit();
        }

        $class   = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $date    = $this->request->input('date', date('Y-m-d'));
        $attData = $this->request->input('attendance', []);
        $remarks = $this->request->input('remarks', []);

        if (!$date) {
            $this->flash('error', 'Date is required to save attendance.');
            return $this->redirect('/teacher/attendance');
        }

        // Verify teacher is assigned to this class
        $isAssigned = $db->selectOne(
            "SELECT 1 FROM classes WHERE class_teacher_id = ? AND name = ? AND COALESCE(section, '') = ?",
            [$user['id'], $class, $section]
        );

        if (!$isAssigned) {
            $this->flash('error', 'You are not assigned as the class teacher for this class.');
            return $this->redirect('/teacher/attendance');
        }

        // Get students in this class/section
        $students = $db->select(
            "SELECT id, tenant_id, school_id, branch_id FROM students 
             WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
            [$user['tenant_id'], $class, $section]
        );

        if (empty($students)) {
            $students = $db->select(
                "SELECT id, tenant_id, school_id, branch_id FROM students 
                 WHERE tenant_id = ? AND class = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
                [$user['tenant_id'], $class]
            );
        }

        if (empty($students)) {
            $this->flash('error', 'No enrolled students found.');
            return $this->redirect('/teacher/attendance?class=' . urlencode($class) . '&section=' . urlencode($section));
        }

        try {
            foreach ($students as $student) {
                $studentId = (int) $student['id'];
                $status    = $attData[$studentId] ?? 'present';
                $remark    = $remarks[$studentId] ?? '';

                $exists = $db->selectOne(
                    "SELECT id FROM attendance WHERE student_id = ? AND date = ?",
                    [$studentId, $date]
                );

                if ($exists) {
                    $db->update('attendance', [
                        'status'     => $status,
                        'remarks'    => $remark,
                        'updated_at' => now(),
                    ], 'id = ?', [$exists['id']]);
                } else {
                    $db->insert('attendance', [
                        'tenant_id'  => $student['tenant_id'],
                        'school_id'  => $student['school_id'],
                        'branch_id'  => $student['branch_id'],
                        'student_id' => $studentId,
                        'date'       => $date,
                        'status'     => $status,
                        'remarks'    => $remark,
                        'created_by' => $this->authId(),
                        'created_at' => now(),
                    ]);
                }
            }

            ActivityLog::log('teacher_marked_attendance', $this->authId(), [
                'class'   => $class,
                'section' => $section,
                'date'    => $date,
            ]);

            $this->flash('success', 'Attendance marked successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to save attendance: ' . $e->getMessage());
        }

        return $this->redirect('/teacher/attendance?class=' . urlencode($class) . '&section=' . urlencode($section) . '&date=' . urlencode($date));
    }
}
