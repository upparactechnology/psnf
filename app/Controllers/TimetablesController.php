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

        return $this->view('timetables/index', compact(
            'classes', 'selectedClass', 'selectedSection', 'timetableByDay'
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
        $room        = $this->request->input('room', '');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$class || !$dayOfWeek || !$subject || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/timetables');
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
                'room'         => $room,
                'start_time'   => $startTime . ':00',
                'end_time'     => $endTime . ':00',
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

        return $this->redirect("/timetables?class=" . urlencode($class) . "&section=" . urlencode($section));
    }
}
