<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{Student, ActivityLog};

class AdmissionsController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Fetch students who are in the admissions pipeline (not enrolled or withdrawn)
        $students = $db->select(
            "SELECT s.*, b.name as branch_name FROM students s
             LEFT JOIN branches b ON b.id = s.branch_id
             WHERE s.tenant_id = ? AND s.deleted_at IS NULL AND s.admission_status NOT IN ('enrolled', 'withdrawn')
             ORDER BY s.created_at DESC",
            [$tenantId]
        );

        $statusCounts = Student::statusCounts();

        return $this->view('admissions/index', compact('students', 'statusCounts'));
    }

    public function updateStatus(string $id): string
    {
        $status = $this->request->input('admission_status');
        if (!$status) {
            $this->flash('error', 'Admission status is required.');
            return $this->redirect(url('admissions'));
        }

        try {
            Student::updateStatus((int) $id, $status, $this->authId());
            $this->flash('success', 'Admission status updated successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to update status: ' . $e->getMessage());
        }

        return $this->redirect(url('admissions'));
    }
}
