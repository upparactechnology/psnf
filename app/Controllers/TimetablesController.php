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
        $classes = $db->select("
            SELECT id, name as class, section 
            FROM classes 
            WHERE tenant_id = ?
            ORDER BY name ASC, section ASC
        ", [$tenantId]);

        $selectedClassId = (int)$this->request->get('class_id', $classes[0]['id'] ?? 0);

        $timetableByDay = [];
        $selectedClass = '';
        $selectedSection = '';

        if ($selectedClassId) {
            $classRow = $db->selectOne("SELECT * FROM classes WHERE id = ?", [$selectedClassId]);
            if ($classRow) {
                $selectedClass = $classRow['name'];
                $selectedSection = $classRow['section'];

                $rows = $db->select("
                    SELECT t.*, s.name as subject_name, s.code as subject_code, u.name as teacher_fullname
                    FROM timetables t
                    LEFT JOIN subjects s ON t.subject_id = s.id
                    LEFT JOIN users u ON t.teacher_id = u.id
                    WHERE t.tenant_id = ? AND t.class_id = ?
                    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time ASC
                ", [$tenantId, $selectedClassId]);

                foreach ($rows as $row) {
                    $timetableByDay[$row['day_of_week']][] = $row;
                }
            }
        }

        // Fetch all teachers
        $teachers = $db->select("
            SELECT u.id, u.name FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.tenant_id = ? AND r.slug = 'teacher'
            ORDER BY u.name ASC
        ", [$tenantId]);

        // Fetch all subjects
        $subjects = $db->select("SELECT id, name FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        return $this->view('timetables/index', compact(
            'classes', 'selectedClassId', 'selectedClass', 'selectedSection', 'timetableByDay', 'teachers', 'subjects'
        ));
    }

    public function store(): string
    {
        $db = $this->db();
        $classId     = (int)$this->request->input('class_id');
        $dayOfWeek   = $this->request->input('day_of_week', '');
        $subjectId   = (int)$this->request->input('subject_id');
        $teacherId   = (int)$this->request->input('teacher_id');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$classId || !$dayOfWeek || !$subjectId || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable');
        }

        $classRow = $db->selectOne("SELECT * FROM classes WHERE id = ?", [$classId]);
        $subjectRow = $db->selectOne("SELECT * FROM subjects WHERE id = ?", [$subjectId]);
        $teacherRow = $db->selectOne("SELECT * FROM users WHERE id = ?", [$teacherId]);

        if (!$classRow || !$subjectRow) {
            $this->flash('error', 'Invalid Class or Subject selected.');
            return $this->redirect('/academics/timetable');
        }

        $tenantId = \Core\Database::getTenantId();
        $schoolId = 1;
        $branchId = 1;

        try {
            $db->insert('timetables', [
                'tenant_id'    => $tenantId,
                'school_id'    => $schoolId,
                'branch_id'    => $branchId,
                'class_id'     => $classId,
                'subject_id'   => $subjectId,
                'teacher_id'   => $teacherId ?: null,
                // Legacy support values
                'class'        => $classRow['name'],
                'section'      => $classRow['section'],
                'day_of_week'  => $dayOfWeek,
                'subject'      => $subjectRow['name'],
                'teacher_name' => $teacherRow['name'] ?? '',
                'room'         => $classRow['name'],
                'start_time'   => $startTime . (strlen($startTime) == 5 ? ':00' : ''),
                'end_time'     => $endTime . (strlen($endTime) == 5 ? ':00' : ''),
            ]);

            ActivityLog::log('timetable_slot_added', $this->authId(), [
                'class_id'   => $classId,
                'subject_id' => $subjectId,
                'day'        => $dayOfWeek,
            ]);

            $this->flash('success', 'Timetable slot created successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to create slot: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?class_id=" . $classId);
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $classId     = (int)$this->request->input('class_id');
        $dayOfWeek   = $this->request->input('day_of_week', '');
        $subjectId   = (int)$this->request->input('subject_id');
        $teacherId   = (int)$this->request->input('teacher_id');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$classId || !$dayOfWeek || !$subjectId || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable');
        }

        $classRow = $db->selectOne("SELECT * FROM classes WHERE id = ?", [$classId]);
        $subjectRow = $db->selectOne("SELECT * FROM subjects WHERE id = ?", [$subjectId]);
        $teacherRow = $db->selectOne("SELECT * FROM users WHERE id = ?", [$teacherId]);

        if (!$classRow || !$subjectRow) {
            $this->flash('error', 'Invalid Class or Subject selected.');
            return $this->redirect('/academics/timetable');
        }

        try {
            $db->query("
                UPDATE timetables 
                SET class_id = ?, subject_id = ?, teacher_id = ?, day_of_week = ?, 
                    class = ?, section = ?, subject = ?, teacher_name = ?, room = ?, 
                    start_time = ?, end_time = ?
                WHERE id = ? AND tenant_id = ?
            ", [
                $classId, $subjectId, $teacherId ?: null, $dayOfWeek,
                $classRow['name'], $classRow['section'], $subjectRow['name'], $teacherRow['name'] ?? '', $classRow['name'],
                $startTime, $endTime, (int)$id, \Core\Database::getTenantId()
            ]);

            $this->flash('success', 'Timetable slot updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update slot: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?class_id=" . $classId);
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $classId = (int)$this->request->input('class_id');
        $db->query("DELETE FROM timetables WHERE id = ? AND tenant_id = ?", [(int)$id, \Core\Database::getTenantId()]);
        $this->flash('success', 'Timetable slot deleted successfully.');
        return $this->redirect("/academics/timetable?class_id=" . $classId);
    }
}
