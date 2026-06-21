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

        // Fetch all classes & sections to populate filter dropdowns
        $classes = $db->select(
            "SELECT class, section 
             FROM students 
             WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
             GROUP BY class, section 
             ORDER BY class ASC, section ASC",
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
