<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class ClassesController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Retrieve all unique class and section combinations with student counts
        $classes = $db->select(
            "SELECT class, section, COUNT(*) as student_count 
             FROM students 
             WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
             GROUP BY class, section 
             ORDER BY class ASC, section ASC",
            [$tenantId]
        );

        // Fetch all active students to assign in the modal
        $students = $db->select(
            "SELECT id, first_name, last_name, admission_number, class, section, admission_status
             FROM students
             WHERE tenant_id = ? AND deleted_at IS NULL
             ORDER BY first_name ASC, last_name ASC",
            [$tenantId]
        );

        return $this->view('classes/index', compact('classes', 'students'));
    }

    public function store(): string
    {
        $db = $this->db();
        $data = $this->request->getBody();
        $className = trim($data['class'] ?? '');
        $sectionName = trim($data['section'] ?? '');
        $studentIds = $data['student_ids'] ?? [];
        $redirectTo = $data['redirect_to'] ?? '/classes';

        if (empty($className)) {
            \Core\Session::flash('error', 'Class name is required.');
            return $this->redirect($redirectTo);
        }

        if (!empty($studentIds)) {
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $params = array_merge([$className, $sectionName], array_map('intval', $studentIds));
            
            $db->query(
                "UPDATE students 
                 SET class = ?, section = ?, admission_status = 'enrolled' 
                 WHERE id IN ($placeholders)",
                $params
            );
            
            \App\Models\ActivityLog::log('class_created', auth_id(), [
                'class' => $className,
                'section' => $sectionName,
                'assigned_count' => count($studentIds)
            ]);
            
            \Core\Session::flash('success', "Student(s) assigned to '{$className}' successfully.");
        } else {
            \Core\Session::flash('error', "You must select and assign at least one student.");
        }

        return $this->redirect($redirectTo);
    }

    public function show(string $class): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $className = urldecode($class);

        $classCondition = "s.class = ?";
        $params = [$tenantId, $className];

        if ($className === 'Unassigned') {
            $classCondition = "(s.class IS NULL OR s.class = '')";
            $params = [$tenantId];
        }

        // Fetch students in this class
        $students = $db->select(
            "SELECT s.*, b.name as branch_name 
             FROM students s
             LEFT JOIN branches b ON b.id = s.branch_id
             WHERE s.tenant_id = ? AND $classCondition AND s.deleted_at IS NULL AND s.admission_status = 'enrolled'
             ORDER BY s.first_name ASC",
            $params
        );

        // Fetch all active students NOT in this class
        if ($className === 'Unassigned') {
            $assignableStudents = $db->select(
                "SELECT id, first_name, last_name, admission_number, class, section, admission_status
                 FROM students
                 WHERE tenant_id = ? AND deleted_at IS NULL AND (class IS NOT NULL AND class != '')
                 ORDER BY first_name ASC, last_name ASC",
                [$tenantId]
            );
        } else {
            $assignableStudents = $db->select(
                "SELECT id, first_name, last_name, admission_number, class, section, admission_status
                 FROM students
                 WHERE tenant_id = ? AND deleted_at IS NULL AND (class != ? OR class IS NULL OR class = '')
                 ORDER BY first_name ASC, last_name ASC",
                [$tenantId, $className]
            );
        }

        // Get distinct sections for this class
        $sections = [];
        if ($className !== 'Unassigned') {
            $sections = $db->select(
                "SELECT DISTINCT section 
                 FROM students 
                 WHERE tenant_id = ? AND class = ? AND deleted_at IS NULL AND admission_status = 'enrolled'",
                [$tenantId, $className]
            );
        }

        return $this->view('classes/show', compact('students', 'className', 'assignableStudents', 'sections'));
    }
}
