<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{Student, ActivityLog};

class AttendanceController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        // Route to calendar view if requested
        if ($this->request->get('view') === 'calendar') {
            return $this->calendar();
        }

        
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $userId = $this->authId();
        $isTeacher = has_role('teacher');
        $canEdit = has_role('super_admin') || has_role('school_admin') || $isTeacher;

        $selectedClass   = trim((string)$this->request->get('class', ''));
        $selectedSection = trim((string)$this->request->get('section', ''));
        $selectedDate    = trim((string)$this->request->get('date', date('Y-m-d')));

        if (empty($selectedClass) && ($classSection = $this->request->get('class_section', ''))) {
            $parts = explode('|', (string)$classSection, 2);
            $selectedClass   = trim($parts[0] ?? '');
            $selectedSection = trim($parts[1] ?? '');
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

        // Fetch classes - for teachers, only show assigned classes
        if ($isTeacher) {
            $classes = $db->select(
                "SELECT name as class, COALESCE(section, '') as section 
                 FROM classes 
                 WHERE tenant_id = ? AND class_teacher_id = ?
                 ORDER BY name ASC, section ASC",
                [$tenantId, $userId]
            );
        } else {
            $classes = $db->select(
                "SELECT name as class, COALESCE(section, '') as section 
                 FROM classes 
                 WHERE tenant_id = ? 
                 ORDER BY name ASC, section ASC",
                [$tenantId]
            );
        }

        if (empty($classes)) {
            $classes = $db->select(
                "SELECT class, COALESCE(section, '') as section 
                 FROM students 
                 WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled' AND class IS NOT NULL AND class != ''
                 GROUP BY class, section 
                 ORDER BY class ASC, section ASC",
                [$tenantId]
            );
        }

        // If no class is selected, auto-select the first available class
        if (!isset($_GET['class']) && empty($selectedClass) && !empty($classes)) {
            $selectedClass = $classes[0]['class'];
            $selectedSection = $classes[0]['section'];
        }

        $students = [];
        $attendanceMap = [];
        $pendingLeaves = $db->select("SELECT l.*, s.first_name, s.last_name, s.class, s.section FROM leave_applications l JOIN students s ON l.student_id = s.id WHERE l.tenant_id = ? AND l.status = 'Pending' ORDER BY l.created_at DESC", [$tenantId]);

        if ($selectedClass) {
            // Fetch enrolled students for selected class and section
            $students = $db->select(
                "SELECT * FROM students 
                 WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );

            // If empty with section filter, fallback to class matching
            if (empty($students)) {
                $students = $db->select(
                    "SELECT * FROM students 
                     WHERE tenant_id = ? AND class = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                     ORDER BY first_name ASC",
                    [$tenantId, $selectedClass]
                );
            }
        } else {
            // Fallback: Fetch all enrolled students across all classes
            $students = $db->select(
                "SELECT * FROM students 
                 WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY class ASC, first_name ASC",
                [$tenantId]
            );
        }

        if (!empty($students)) {
            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            
            // Fetch attendance records for these students on the selected date
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

        return $this->view('attendance/index', compact(
            'classes', 'selectedClass', 'selectedSection', 'selectedDate', 'students', 'attendanceMap', 'pendingLeaves', 'canEdit'
        ));
    }

    public function calendar(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $selectedClass   = trim((string)$this->request->get('class', ''));
        $selectedSection = trim((string)$this->request->get('section', ''));
        $month           = trim((string)$this->request->get('month', date('Y-m')));
        $studentId       = (int)$this->request->get('student_id', 0);

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

        // Fetch classes
        $classes = $db->select(
            "SELECT name as class, COALESCE(section, '') as section 
             FROM classes 
             WHERE tenant_id = ? 
             ORDER BY name ASC, section ASC",
            [$tenantId]
        );

        if (empty($classes)) {
            $classes = $db->select(
                "SELECT class, COALESCE(section, '') as section 
                 FROM students 
                 WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled' AND class IS NOT NULL AND class != ''
                 GROUP BY class, section 
                 ORDER BY class ASC, section ASC",
                [$tenantId]
            );
        }

        // Auto-select first class if none selected
        if (empty($selectedClass) && !empty($classes)) {
            $selectedClass = $classes[0]['class'];
            $selectedSection = $classes[0]['section'];
        }

        // Fetch students for selected class
        $students = [];
        if ($selectedClass) {
            $students = $db->select(
                "SELECT id, first_name, last_name, class, section FROM students 
                 WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );
        }

        // If specific student selected, filter to just that student
        $selectedStudent = null;
        if ($studentId && !empty($students)) {
            foreach ($students as $s) {
                if ((int)$s['id'] === $studentId) {
                    $selectedStudent = $s;
                    break;
                }
            }
        }

        // Build calendar data for the month
        $monthStart = $month . '-01';
        $daysInMonth = (int)date('t', strtotime($monthStart));
        $firstDayOfWeek = (int)date('w', strtotime($monthStart));
        $monthName = date('F Y', strtotime($monthStart));

        $calData = [];
        $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // Initialize all days
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $month . '-' . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
            $dayOfWeek = date('w', strtotime($dateStr));
            $dayName = $weekDays[$dayOfWeek];

            if ($dayOfWeek == 0) { // Sunday
                $calData[$day] = ['status' => 'sunday', 'in' => '', 'out' => '', 'date' => $dateStr];
            } elseif (strtotime($dateStr) > strtotime('today')) {
                $calData[$day] = ['status' => 'pending', 'in' => '', 'out' => '', 'date' => $dateStr];
            } else {
                $calData[$day] = ['status' => 'absent', 'in' => '', 'out' => '', 'date' => $dateStr];
            }
        }

        // If we have students, fetch attendance data
        if (!empty($students)) {
            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

            // Fetch attendance for the month
            $records = $db->select(
                "SELECT student_id, status, date, remarks FROM attendance 
                 WHERE student_id IN ($placeholders) 
                 AND date >= ? AND date <= ?",
                array_merge($studentIds, [$monthStart, date('Y-m-t', strtotime($monthStart))])
            );

            // If specific student selected, filter records
            if ($studentId) {
                $records = array_filter($records, fn($r) => (int)$r['student_id'] === $studentId);
            }

            // Map attendance to calendar days
            foreach ($records as $r) {
                $day = (int)date('d', strtotime($r['date']));
                if (isset($calData[$day]) && $calData[$day]['status'] !== 'sunday') {
                    $calData[$day]['status'] = $r['status'] ?? 'absent';
                }
            }
        }

        return $this->view('attendance/student_calendar', compact(
            'classes', 'selectedClass', 'selectedSection', 'month', 'monthName',
            'daysInMonth', 'firstDayOfWeek', 'calData', 'students', 'studentId', 'selectedStudent', 'weekDays'
        ));
    }

    
    public function leaves(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $allLeaves = $db->select("
            SELECT l.*, s.first_name, s.last_name, s.class, s.section 
            FROM leave_applications l
            JOIN students s ON l.student_id = s.id
            WHERE l.tenant_id = ?
            ORDER BY CASE WHEN l.status = 'Pending' THEN 1 ELSE 2 END, l.created_at DESC
        ", [$tenantId]);
        
        return $this->view('attendance/leaves', compact('allLeaves'));
    }

    public function save(): string
    {
        $db = $this->db();
        $class   = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $date    = $this->request->input('date', date('Y-m-d'));
        $attData = $this->request->input('attendance', []);
        $remarks = $this->request->input('remarks', []);
        $userId = $this->authId();
        $isTeacher = has_role('teacher');

        if (!$date) {
            $this->flash('error', 'Date is required to save attendance.');
            return $this->redirect('/attendance');
        }

        // Verify teacher is assigned to this class
        if ($isTeacher) {
            $isAssigned = $db->selectOne(
                "SELECT 1 FROM classes WHERE class_teacher_id = ? AND name = ? AND COALESCE(section, '') = ?",
                [$userId, $class, $section]
            );

            if (!$isAssigned) {
                $this->flash('error', 'You are not assigned as the class teacher for this class.');
                return $this->redirect('/academics/attendance');
            }
        }

        $tenantId = \Core\Database::getTenantId();

        // Get students in this class/section (or all enrolled students if no class specified)
        if ($class) {
            $students = $db->select(
                "SELECT id, tenant_id, school_id, branch_id FROM students 
                 WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
                [$tenantId, $class, $section]
            );
            if (empty($students)) {
                $students = $db->select(
                    "SELECT id, tenant_id, school_id, branch_id FROM students 
                     WHERE tenant_id = ? AND class = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
                    [$tenantId, $class]
                );
            }
        } else {
            $students = $db->select(
                "SELECT id, tenant_id, school_id, branch_id FROM students 
                 WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
                [$tenantId]
            );
        }

        if (empty($students)) {
            $this->flash('error', 'No enrolled students found.');
            return $this->redirect('/attendance');
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

            ActivityLog::log('attendance_marked', $this->authId(), [
                'class'   => $class,
                'section' => $section,
                'date'    => $date,
            ]);

            $this->flash('success', 'Attendance marked successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to save attendance: ' . $e->getMessage());
        }

        $redirectTo = $this->request->input('redirect_to', '/student-attendance');
        $baseRedirect = str_contains($redirectTo, 'academics/attendance') ? '/academics/attendance' : '/student-attendance';

        return $this->redirect($baseRedirect . "?class=" . urlencode($class) . "&section=" . urlencode($section) . "&date=" . urlencode($date));
    }
}
