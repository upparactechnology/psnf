<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\ActivityLog;

class ExamsController extends Controller
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

        $examResults = [];
        $students = [];

        if ($selectedClass) {
            // Fetch enrolled students in this class/section for the dropdown selector
            $students = $db->select(
                "SELECT id, first_name, last_name FROM students 
                 WHERE tenant_id = ? AND class = ? AND section = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );

            // Fetch exam results
            $examResults = $db->select(
                "SELECT er.*, s.first_name, s.last_name, s.admission_number 
                 FROM exam_results er
                 JOIN students s ON er.student_id = s.id
                 WHERE s.tenant_id = ? AND s.class = ? AND s.section = ? AND s.deleted_at IS NULL AND s.admission_status = 'enrolled'
                 ORDER BY er.date_published DESC, er.created_at DESC, s.first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );
        }

        return $this->view('exams/index', compact(
            'classes', 'selectedClass', 'selectedSection', 'students', 'examResults'
        ));
    }

    public function store(): string
    {
        $db = $this->db();
        $studentId     = (int) $this->request->input('student_id', 0);
        $examName      = $this->request->input('exam_name', '');
        $subject       = $this->request->input('subject', '');
        $marksObtained = $this->request->input('marks_obtained', '');
        $maxMarks      = $this->request->input('max_marks', '');
        $grade         = $this->request->input('grade', '');
        $remarks       = $this->request->input('remarks', '');

        if (!$studentId || !$examName || !$subject || $marksObtained === '' || !$maxMarks || !$grade) {
            $this->flash('error', 'All required fields must be completed.');
            return $this->redirect('/exams');
        }

        // Get student context
        $student = $db->selectOne(
            "SELECT tenant_id, school_id, branch_id, class, section FROM students WHERE id = ?",
            [$studentId]
        );

        if (!$student) {
            $this->flash('error', 'Student not found.');
            return $this->redirect('/exams');
        }

        try {
            $db->insert('exam_results', [
                'tenant_id'      => $student['tenant_id'],
                'school_id'      => $student['school_id'],
                'branch_id'      => $student['branch_id'],
                'student_id'     => $studentId,
                'exam_name'      => $examName,
                'subject'        => $subject,
                'marks_obtained' => (float) $marksObtained,
                'max_marks'      => (float) $maxMarks,
                'grade'          => $grade,
                'remarks'        => $remarks,
                'date_published' => date('Y-m-d'),
            ]);

            ActivityLog::log('exam_result_added', $this->authId(), [
                'student_id' => $studentId,
                'subject'    => $subject,
                'grade'      => $grade,
            ]);

            $this->flash('success', 'Student grade recorded successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to save grade: ' . $e->getMessage());
        }

        return $this->redirect("/exams?class=" . urlencode($student['class']) . "&section=" . urlencode($student['section']));
    }

    public function bulkEntry(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $classes = $db->select(
            "SELECT name as class, section FROM classes WHERE tenant_id = ? ORDER BY name ASC, section ASC",
            [$tenantId]
        );
        if (empty($classes)) {
            $classes = $db->select(
                "SELECT class, COALESCE(section, '') as section FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled' GROUP BY class, section ORDER BY class ASC, section ASC",
                [$tenantId]
            );
        }

        $selectedClass   = $this->request->get('class', $classes[0]['class'] ?? '');
        $selectedSection = $this->request->get('section', $classes[0]['section'] ?? '');
        $selectedTerm    = $this->request->get('term', 'First Term Evaluation');

        $students = [];
        if ($selectedClass) {
            $students = $db->select(
                "SELECT id, first_name, last_name, admission_number FROM students 
                 WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
                 ORDER BY first_name ASC",
                [$tenantId, $selectedClass, $selectedSection]
            );
        }

        $subjects = [
            ['name' => 'Speech Therapy', 'code' => 'ST'],
            ['name' => 'Sensory Integration', 'code' => 'SI'],
            ['name' => 'Visual Arts', 'code' => 'VA'],
            ['name' => 'Math Foundations', 'code' => 'MF'],
            ['name' => 'Life Skills', 'code' => 'LS'],
        ];

        $existingMarks = [];
        if (!empty($students)) {
            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $rows = $db->select(
                "SELECT student_id, subject, marks_obtained, max_marks, grade, remarks 
                 FROM exam_results 
                 WHERE exam_name = ? AND student_id IN ($placeholders)",
                array_merge([$selectedTerm], $studentIds)
            );
            foreach ($rows as $r) {
                $existingMarks[$r['student_id']][$r['subject']] = $r;
            }
        }

        return $this->view('exams/bulk_entry', compact(
            'classes', 'selectedClass', 'selectedSection', 'selectedTerm', 'students', 'subjects', 'existingMarks'
        ));
    }

    public function bulkSave(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $class   = $this->request->input('class', '');
        $section = $this->request->input('section', '');
        $term    = $this->request->input('term', 'First Term Evaluation');
        $marks   = $this->request->input('marks', []);
        $remarks = $this->request->input('remarks', []);

        $savedCount = 0;
        foreach ($marks as $studentId => $subjMarks) {
            $student = $db->selectOne(
                "SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?",
                [(int)$studentId]
            );
            if (!$student) continue;

            $stuRemark = $remarks[$studentId] ?? '';

            foreach ($subjMarks as $subjName => $val) {
                if ($val === '' || $val === null) continue;
                $obt = (float) $val;
                $max = 100.0;
                $pct = ($obt / $max) * 100;
                $grade = $pct >= 90 ? 'A+' : ($pct >= 80 ? 'A' : ($pct >= 70 ? 'B' : ($pct >= 60 ? 'C' : 'F')));

                $exists = $db->selectOne(
                    "SELECT id FROM exam_results WHERE student_id = ? AND exam_name = ? AND subject = ?",
                    [(int)$studentId, $term, $subjName]
                );

                if ($exists) {
                    $db->update('exam_results', [
                        'marks_obtained' => $obt,
                        'max_marks'      => $max,
                        'grade'          => $grade,
                        'remarks'        => $stuRemark,
                        'date_published' => date('Y-m-d'),
                    ], 'id = ?', [$exists['id']]);
                } else {
                    $db->insert('exam_results', [
                        'tenant_id'      => $student['tenant_id'],
                        'school_id'      => $student['school_id'],
                        'branch_id'      => $student['branch_id'],
                        'student_id'     => (int)$studentId,
                        'exam_name'      => $term,
                        'subject'        => $subjName,
                        'marks_obtained' => $obt,
                        'max_marks'      => $max,
                        'grade'          => $grade,
                        'remarks'        => $stuRemark,
                        'date_published' => date('Y-m-d'),
                    ]);
                }
                $savedCount++;
            }
        }

        ActivityLog::log('bulk_exam_marks_saved', $this->authId(), [
            'class' => $class,
            'term'  => $term,
            'count' => $savedCount
        ]);

        $this->flash('success', "Successfully saved bulk evaluation marks.");
        return $this->redirect("/exams/bulk-entry?class=" . urlencode($class) . "&section=" . urlencode($section) . "&term=" . urlencode($term));
    }
}
