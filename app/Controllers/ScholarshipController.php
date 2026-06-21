<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\ActivityLog;

class ScholarshipController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Fetch all student scholarships
        $scholarships = $db->select(
            "SELECT s.*, st.first_name, st.last_name, st.admission_number, st.class, st.section 
             FROM scholarships s
             JOIN students st ON s.student_id = st.id
             WHERE s.tenant_id = ?
             ORDER BY s.created_at DESC",
            [$tenantId]
        );

        // Fetch all enrolled students to select from in the add award form dropdown
        $students = $db->select(
            "SELECT id, first_name, last_name, class, section FROM students 
             WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'
             ORDER BY first_name ASC",
            [$tenantId]
        );

        return $this->view('scholarships/index', compact('scholarships', 'students'));
    }

    public function store(): string
    {
        $db = $this->db();
        $studentId = (int) $this->request->input('student_id', 0);
        $name      = $this->request->input('name', '');
        $amount    = $this->request->input('amount', '');
        $type      = $this->request->input('type', 'fixed');
        $status    = $this->request->input('status', 'active');
        $remarks   = $this->request->input('remarks', '');

        if (!$studentId || !$name || $amount === '') {
            $this->flash('error', 'Student, Scholarship Name, and Amount are required.');
            return $this->redirect('/scholarships');
        }

        // Get student context details
        $student = $db->selectOne(
            "SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?",
            [$studentId]
        );

        if (!$student) {
            $this->flash('error', 'Student record not found.');
            return $this->redirect('/scholarships');
        }

        try {
            $db->insert('scholarships', [
                'tenant_id'  => $student['tenant_id'],
                'school_id'  => $student['school_id'],
                'branch_id'  => $student['branch_id'],
                'student_id' => $studentId,
                'name'       => $name,
                'amount'     => (float) $amount,
                'type'       => $type,
                'status'     => $status,
                'remarks'    => $remarks,
            ]);

            ActivityLog::log('scholarship_awarded', $this->authId(), [
                'student_id' => $studentId,
                'name'       => $name,
                'amount'     => $amount,
            ]);

            $this->flash('success', 'Scholarship awarded successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to award scholarship: ' . $e->getMessage());
        }

        return $this->redirect('/scholarships');
    }
}
