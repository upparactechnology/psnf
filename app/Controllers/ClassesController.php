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

        return $this->view('classes/index', compact('classes'));
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

        return $this->view('classes/show', compact('students', 'className'));
    }
}
