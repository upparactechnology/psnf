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

        $mainGroups = $db->select("
            SELECT id, name, color, icon 
            FROM main_groups 
            WHERE tenant_id = ? AND is_active = 1
            ORDER BY name ASC
        ", [$tenantId]);

        $selectedGroupId = (int)$this->request->get('main_group_id', $mainGroups[0]['id'] ?? 0);

        $timetableByDay = [];
        $selectedGroup = '';

        if ($selectedGroupId) {
            $groupRow = $db->selectOne("SELECT * FROM main_groups WHERE id = ?", [$selectedGroupId]);
            if ($groupRow) {
                $selectedGroup = $groupRow['name'];

                $rows = $db->select("
                    SELECT t.*, s.name as subject_name, s.code as subject_code, u.name as teacher_fullname
                    FROM timetables t
                    LEFT JOIN subjects s ON t.subject_id = s.id
                    LEFT JOIN users u ON t.teacher_id = u.id
                    WHERE t.tenant_id = ? AND t.main_group_id = ?
                    ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time ASC
                ", [$tenantId, $selectedGroupId]);

                foreach ($rows as $row) {
                    $timetableByDay[$row['day_of_week']][] = $row;
                }
            }
        }

        $teachers = $db->select("
            SELECT u.id, u.name FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.tenant_id = ? AND r.slug = 'teacher'
            ORDER BY u.name ASC
        ", [$tenantId]);

        $subjects = $db->select("SELECT id, name FROM subjects WHERE tenant_id = ? ORDER BY name ASC", [$tenantId]);

        return $this->view('timetables/index', compact(
            'mainGroups', 'selectedGroupId', 'selectedGroup', 'timetableByDay', 'teachers', 'subjects'
        ));
    }

    public function store(): string
    {
        $db = $this->db();
        $groupId     = (int)$this->request->input('main_group_id');
        $dayOfWeek   = $this->request->input('day_of_week', '');
        $subjectId   = (int)$this->request->input('subject_id');
        $teacherId   = (int)$this->request->input('teacher_id');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$groupId || !$dayOfWeek || !$subjectId || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable');
        }

        $groupRow = $db->selectOne("SELECT * FROM main_groups WHERE id = ?", [$groupId]);
        $subjectRow = $db->selectOne("SELECT * FROM subjects WHERE id = ?", [$subjectId]);
        $teacherRow = $db->selectOne("SELECT * FROM users WHERE id = ?", [$teacherId]);

        if (!$groupRow || !$subjectRow) {
            $this->flash('error', 'Invalid Group or Subject selected.');
            return $this->redirect('/academics/timetable');
        }

        $tenantId = \Core\Database::getTenantId();
        $schoolId = 1;
        $branchId = 1;

        try {
            $db->insert('timetables', [
                'tenant_id'      => $tenantId,
                'school_id'      => $schoolId,
                'branch_id'      => $branchId,
                'main_group_id'  => $groupId,
                'subject_id'     => $subjectId,
                'teacher_id'     => $teacherId ?: null,
                'class'          => $groupRow['name'],
                'day_of_week'    => $dayOfWeek,
                'subject'        => $subjectRow['name'],
                'teacher_name'   => $teacherRow['name'] ?? '',
                'room'           => $groupRow['name'],
                'start_time'     => $startTime . (strlen($startTime) == 5 ? ':00' : ''),
                'end_time'       => $endTime . (strlen($endTime) == 5 ? ':00' : ''),
            ]);

            ActivityLog::log('timetable_slot_added', $this->authId(), [
                'main_group_id' => $groupId,
                'subject_id'    => $subjectId,
                'day'           => $dayOfWeek,
            ]);

            $this->flash('success', 'Timetable slot created successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to create slot: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?main_group_id=" . $groupId);
    }

    public function update(string $id): string
    {
        $db = $this->db();
        $groupId     = (int)$this->request->input('main_group_id');
        $dayOfWeek   = $this->request->input('day_of_week', '');
        $subjectId   = (int)$this->request->input('subject_id');
        $teacherId   = (int)$this->request->input('teacher_id');
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$groupId || !$dayOfWeek || !$subjectId || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable');
        }

        $groupRow = $db->selectOne("SELECT * FROM main_groups WHERE id = ?", [$groupId]);
        $subjectRow = $db->selectOne("SELECT * FROM subjects WHERE id = ?", [$subjectId]);
        $teacherRow = $db->selectOne("SELECT * FROM users WHERE id = ?", [$teacherId]);

        if (!$groupRow || !$subjectRow) {
            $this->flash('error', 'Invalid Group or Subject selected.');
            return $this->redirect('/academics/timetable');
        }

        try {
            $db->query("
                UPDATE timetables 
                SET main_group_id = ?, subject_id = ?, teacher_id = ?, day_of_week = ?, 
                    class = ?, subject = ?, teacher_name = ?, room = ?, 
                    start_time = ?, end_time = ?
                WHERE id = ? AND tenant_id = ?
            ", [
                $groupId, $subjectId, $teacherId ?: null, $dayOfWeek,
                $groupRow['name'], $subjectRow['name'], $teacherRow['name'] ?? '', $groupRow['name'],
                $startTime, $endTime, (int)$id, \Core\Database::getTenantId()
            ]);

            $this->flash('success', 'Timetable slot updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update slot: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?main_group_id=" . $groupId);
    }

    public function destroy(string $id): string
    {
        $db = $this->db();
        $groupId = (int)$this->request->input('main_group_id');
        $db->query("DELETE FROM timetables WHERE id = ? AND tenant_id = ?", [(int)$id, \Core\Database::getTenantId()]);
        $this->flash('success', 'Timetable slot deleted successfully.');
        return $this->redirect("/academics/timetable?main_group_id=" . $groupId);
    }

    public function bulkGenerate(): string
    {
        $db = $this->db();
        $groupId     = (int)$this->request->input('main_group_id');
        $days        = $this->request->input('days', []);
        $subjectName = trim($this->request->input('subject', ''));
        $teacherName = trim($this->request->input('teacher_name', ''));
        $startTime   = $this->request->input('start_time', '');
        $endTime     = $this->request->input('end_time', '');

        if (!$groupId || empty($days) || !$subjectName || !$startTime || !$endTime) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/academics/timetable?main_group_id=' . $groupId);
        }

        $groupRow = $db->selectOne("SELECT * FROM main_groups WHERE id = ?", [$groupId]);
        $subjectRow = $db->selectOne("SELECT * FROM subjects WHERE name = ?", [$subjectName]);
        $teacherRow = $teacherName ? $db->selectOne("SELECT * FROM users WHERE name = ?", [$teacherName]) : null;

        $tenantId = \Core\Database::getTenantId();
        $schoolId = 1;
        $branchId = 1;
        $startFormatted = $startTime . (strlen($startTime) == 5 ? ':00' : '');
        $endFormatted = $endTime . (strlen($endTime) == 5 ? ':00' : '');

        $count = 0;
        foreach ($days as $day) {
            try {
                $db->insert('timetables', [
                    'tenant_id'      => $tenantId,
                    'school_id'      => $schoolId,
                    'branch_id'      => $branchId,
                    'main_group_id'  => $groupId,
                    'subject_id'     => $subjectRow['id'] ?? null,
                    'teacher_id'     => $teacherRow['id'] ?? null,
                    'class'          => $groupRow['name'],
                    'day_of_week'    => $day,
                    'subject'        => $subjectName,
                    'teacher_name'   => $teacherName,
                    'room'           => $groupRow['name'],
                    'start_time'     => $startFormatted,
                    'end_time'       => $endFormatted,
                ]);
                $count++;
            } catch (\Throwable $e) {
                // skip duplicate or error
            }
        }

        $this->flash('success', "Generated {$count} timetable slot(s) successfully.");
        return $this->redirect("/academics/timetable?main_group_id=" . $groupId);
    }

    public function clearTimetable(): string
    {
        $db = $this->db();
        $groupId = (int)$this->request->input('main_group_id');
        $tenantId = \Core\Database::getTenantId();

        $db->query("DELETE FROM timetables WHERE main_group_id = ? AND tenant_id = ?", [$groupId, $tenantId]);

        $this->flash('success', 'Timetable cleared successfully.');
        return $this->redirect("/academics/timetable?main_group_id=" . $groupId);
    }

    public function share(): string
    {
        $db = $this->db();
        $groupId = (int)$this->request->input('main_group_id');
        $tenantId = \Core\Database::getTenantId();
        $userId = $this->authId();

        if (!$groupId) {
            $this->flash('error', 'Invalid group selected.');
            return $this->redirect('/academics/timetable');
        }

        try {
            $db->query(
                "UPDATE timetables SET shared_at = NOW(), shared_by = ? WHERE main_group_id = ? AND tenant_id = ?",
                [$userId, $groupId, $tenantId]
            );

            ActivityLog::log('timetable_shared', $userId, [
                'main_group_id' => $groupId,
            ]);

            $this->flash('success', 'Timetable shared with teachers successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to share timetable: ' . $e->getMessage());
        }

        return $this->redirect("/academics/timetable?main_group_id=" . $groupId);
    }
}
