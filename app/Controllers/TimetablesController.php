<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\ActivityLog;

class TimetablesController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

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

        // Fetch all classes & sections to populate filter dropdowns
        $classes = $db->select(
            "SELECT name as class, section 
             FROM classes 
             WHERE tenant_id = ?
             ORDER BY name ASC, section ASC",
            [$tenantId]
        );

        $selectedClass   = $this->request->get('class', $classes[0]['class'] ?? '');
        $selectedSection = $this->request->get('section', $classes[0]['section'] ?? '');

        $timetableByDay = [];

        if ($selectedClass) {
            $rows = $db->select(
                "SELECT * FROM timetables
                 WHERE tenant_id = ? AND class = ? AND section = ?
                 ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );

            foreach ($rows as $row) {
                $timetableByDay[$row['day_of_week']][] = $row;
            }
        }

        // Fetch all teachers from the database
        $teachers = $db->select(
            "SELECT id, name FROM users 
             WHERE id IN (SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.slug = 'teacher')
             ORDER BY name ASC"
        );

        // Fetch lecture attendance logs
        $lectureAttendance = $db->select("
            SELECT tla.*, u.name as teacher_name
            FROM teacher_attendance tla
            JOIN users u ON tla.user_id = u.id
            WHERE tla.tenant_id = ?
        ", [$tenantId]);

        // Fetch all subjects from database
        $subjects = $db->select("SELECT id, name FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        return $this->view('timetables/index', compact(
            'classes', 'selectedClass', 'selectedSection', 'timetableByDay', 'teachers', 'lectureAttendance', 'subjects'
        ));
    }

    public function store(): string
    {
        $db = $this->db();
        $class       = $this->request->input('class', '');
        $section     = $this->request->input('section', '');
        $dayOfWeek   = $this->request->input('day_of_week', '');
        $subject     = $this->request->input('subject', '');
        $teacherName = $this->request->input('teacher_name', '');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$class || !$dayOfWeek || !$subject || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable');
        }

        $tenantId = \Core\Database::getTenantId();
        $schoolId = auth()['school_id'] ?? 1;
        $branchId = auth()['branch_id'] ?? 1;

        try {
            $db->insert('timetables', [
                'tenant_id'    => $tenantId,
                'school_id'    => $schoolId,
                'branch_id'    => $branchId,
                'class'        => $class,
                'section'      => $section,
                'day_of_week'  => $dayOfWeek,
                'subject'      => $subject,
                'teacher_name' => $teacherName,
                'room'         => $class, // Use class as room/space directly
                'start_time'   => $startTime . (strlen($startTime) == 5 ? ':00' : ''),
                'end_time'     => $endTime . (strlen($endTime) == 5 ? ':00' : ''),
            ]);

            ActivityLog::log('timetable_slot_added', $this->authId(), [
                'class'   => $class,
                'subject' => $subject,
                'day'     => $dayOfWeek,
            ]);

            $this->flash('success', 'Timetable slot created successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to create slot: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $class       = $this->request->input('class', '');
        $section     = $this->request->input('section', '');
        $dayOfWeek   = $this->request->input('day_of_week', '');
        $subject     = $this->request->input('subject', '');
        $teacherName = $this->request->input('teacher_name', '');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$class || !$dayOfWeek || !$subject || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable');
        }

        try {
            $db->query("
                UPDATE timetables 
                SET day_of_week = ?, subject = ?, teacher_name = ?, room = ?, start_time = ?, end_time = ?
                WHERE id = ? AND tenant_id = ?
            ", [$dayOfWeek, $subject, $teacherName, $class, $startTime, $endTime, (int)$id, \Core\Database::getTenantId()]);

            $this->flash('success', 'Timetable slot updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update slot: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $class   = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $db->query("DELETE FROM timetables WHERE id = ? AND tenant_id = ?", [(int)$id, \Core\Database::getTenantId()]);
        $this->flash('success', 'Timetable slot deleted successfully.');
        return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
    }

    public function bulkGenerate(): string
    {
        $db = $this->db();
        $class = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $selectedDays = $this->request->input('days', []);
        $startTime = $this->request->input('start_time', '');
        $endTime = $this->request->input('end_time', '');
        $subject = $this->request->input('subject', '');
        $teacherName = $this->request->input('teacher_name', 'Teacher');
        $room = $this->request->input('room', $class);

        if (!$class) {
            $this->flash('error', 'Class is required.');
            return $this->redirect('/academics/timetable');
        }

        if (empty($selectedDays)) {
            $this->flash('error', 'Please select at least one day.');
            return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
        }

        if (!$startTime || !$endTime || !$subject) {
            $this->flash('error', 'Start time, End time, and Subject are required.');
            return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
        }

        $tenantId = \Core\Database::getTenantId();
        $schoolId = auth()['school_id'] ?? 1;
        $branchId = auth()['branch_id'] ?? 1;

        try {
            foreach ($selectedDays as $day) {
                // Clear overlapping slot on this weekday to avoid duplication
                $db->query("
                    DELETE FROM timetables 
                    WHERE tenant_id = ? AND class = ? AND section = ? AND day_of_week = ? AND start_time = ?
                ", [$tenantId, $class, $section, $day, $startTime]);

                $db->insert('timetables', [
                    'tenant_id' => $tenantId,
                    'school_id' => $schoolId,
                    'branch_id' => $branchId,
                    'class' => $class,
                    'section' => $section,
                    'day_of_week' => $day,
                    'subject' => $subject,
                    'teacher_name' => $teacherName,
                    'room' => $room,
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]);
            }
            $this->flash('success', "Bulk timetable slots generated successfully for " . implode(', ', $selectedDays) . ".");
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to generate bulk slots: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
    }

    public function clearTimetable(): string
    {
        $db = $this->db();
        $class = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $tenantId = \Core\Database::getTenantId();

        if ($class) {
            $db->query("DELETE FROM timetables WHERE tenant_id = ? AND class = ? AND section = ?", [$tenantId, $class, $section]);
            $this->flash('success', "Timetable for $class - $section has been completely cleared.");
        }
        return $this->redirect("/academics/timetable?class=" . urlencode($class) . "&section=" . urlencode($section));
    }
}
