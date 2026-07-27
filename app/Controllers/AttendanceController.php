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
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $selectedClass   = trim((string)$this->request->get('class', ''));
        $selectedSection = trim((string)$this->request->get('section', ''));
        $selectedDate    = trim((string)$this->request->get('date', date('Y-m-d')));

        if (empty($selectedClass) && ($classSection = $this->request->get('class_section', ''))) {
            $parts = explode('|', (string)$classSection, 2);
            $selectedClass   = trim($parts[0] ?? '');
            $selectedSection = trim($parts[1] ?? '');
        }

        // Fetch all classes & sections to populate filter dropdowns
        $classes = $db->select(
            "SELECT class, section 
             FROM students 
             WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
             GROUP BY class, section 
             ORDER BY class ASC, section ASC",
            [$tenantId]
        );

        $students = [];
        $attendanceMap = [];

        if ($selectedClass) {
            // Fetch students in chosen class/section
            $students = $db->select(
                "SELECT * FROM students 
                 WHERE tenant_id = ? AND class = ? AND section = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );

            if ($students) {
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
        }

        return $this->view('attendance/index', compact(
            'classes', 'selectedClass', 'selectedSection', 'selectedDate', 'students', 'attendanceMap'
        ));
    }

    public function save(): string
    {
        $db = $this->db();
        $class   = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $date    = $this->request->input('date', date('Y-m-d'));
        $attData = $this->request->input('attendance', []);
        $remarks = $this->request->input('remarks', []);

        if (!$class || !$date) {
            $this->flash('error', 'Class and Date are required to save attendance.');
            return $this->redirect('/attendance');
        }

        $tenantId = \Core\Database::getTenantId();

        // Get students in this class/section to verify records are for enrolled students
        $students = $db->select(
            "SELECT id, tenant_id, school_id, branch_id FROM students 
             WHERE tenant_id = ? AND class = ? AND section = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
            [$tenantId, $class, $section]
        );

        if (empty($students)) {
            $this->flash('error', 'No enrolled students found in this class.');
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

        return $this->redirect("/attendance?class=" . urlencode($class) . "&section=" . urlencode($section) . "&date=" . urlencode($date));
    }
}
