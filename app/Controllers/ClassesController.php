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

    private function checkAndInitializeClassesTable(): void
    {
        $db = $this->db();
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

        // Sync existing classes from students table to classes table if empty
        $count = $db->selectOne("SELECT COUNT(*) as c FROM classes")['c'] ?? 0;
        if ($count == 0) {
            $existing = $db->select("
                SELECT tenant_id, school_id, branch_id, class as name, section 
                FROM students 
                WHERE class IS NOT NULL AND class != ''
                GROUP BY tenant_id, school_id, branch_id, class, section
            ");
            foreach ($existing as $row) {
                try {
                    $db->insert('classes', [
                        'tenant_id' => $row['tenant_id'],
                        'school_id' => $row['school_id'] ?: 1,
                        'branch_id' => $row['branch_id'] ?: 1,
                        'name' => $row['name'],
                        'section' => $row['section'] ?: '',
                    ]);
                } catch (\Throwable $e) {
                    // Ignore duplicate key errors or constraint errors
                }
            }
        }
    }

    public function index(): string
    {
        $this->checkAndInitializeClassesTable();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Retrieve all unique class and section combinations with student counts from the classes table
        $classes = $db->select(
            "SELECT c.name as class, c.section, COUNT(s.id) as student_count 
             FROM classes c
             LEFT JOIN students s ON s.class = c.name AND COALESCE(s.section, '') = COALESCE(c.section, '') AND s.tenant_id = c.tenant_id AND s.deleted_at IS NULL AND s.admission_status = 'enrolled'
             WHERE c.tenant_id = ?
             GROUP BY c.id, c.name, c.section
             ORDER BY c.name ASC, c.section ASC",
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
        $this->checkAndInitializeClassesTable();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $data = $this->request->getBody();
        $className = trim($data['class'] ?? '');
        $sectionName = trim($data['section'] ?? '');
        $studentIds = $data['student_ids'] ?? [];
        $redirectTo = $data['redirect_to'] ?? '/classes';

        if (empty($className)) {
            \Core\Session::flash('error', 'Class name is required.');
            return $this->redirect($redirectTo);
        }

        // Insert class into classes table if not exists
        $exists = $db->selectOne("SELECT id FROM classes WHERE tenant_id = ? AND name = ? AND COALESCE(section, '') = ?", [$tenantId, $className, $sectionName]);
        if (!$exists) {
            $db->insert('classes', [
                'tenant_id' => $tenantId,
                'school_id' => \Core\Database::getSchoolId() ?: 1,
                'branch_id' => \Core\Database::getBranchId() ?: 1,
                'name' => $className,
                'section' => $sectionName,
            ]);
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
            
            \Core\Session::flash('success', "Class '{$className}' created and student(s) assigned successfully.");
        } else {
            \App\Models\ActivityLog::log('class_created', auth_id(), [
                'class' => $className,
                'section' => $sectionName,
                'assigned_count' => 0
            ]);

            \Core\Session::flash('success', "Class '{$className}' created successfully.");
        }

        return $this->redirect($redirectTo);
    }

    public function destroy(): string
    {
        $this->checkAndInitializeClassesTable();
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $className = trim($this->request->input('class', ''));
        $sectionName = trim($this->request->input('section', ''));

        if (empty($className)) {
            \Core\Session::flash('error', 'Class name is required.');
            return $this->redirect('/classes');
        }

        // Delete from classes table
        $db->query("DELETE FROM classes WHERE tenant_id = ? AND name = ? AND COALESCE(section, '') = ?", [$tenantId, $className, $sectionName]);

        // Unassign any students belonging to this class and section
        $db->query(
            "UPDATE students 
             SET class = NULL, section = NULL 
             WHERE tenant_id = ? AND class = ? AND COALESCE(section, '') = ?",
            [$tenantId, $className, $sectionName]
        );

        \App\Models\ActivityLog::log('class_deleted', auth_id(), [
            'class' => $className,
            'section' => $sectionName
        ]);

        \Core\Session::flash('success', "Class '{$className}' deleted successfully.");
        return $this->redirect('/classes');
    }

    public function show(string $class): string
    {
        $this->checkAndInitializeClassesTable();
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
