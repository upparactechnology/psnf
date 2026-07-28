<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class AcademicWorkspaceController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Get active academic year for report card checks
        $activeYearRow = $db->selectOne("SELECT year_name FROM academic_years WHERE status = 'current' AND tenant_id = ? LIMIT 1", [$tenantId]);
        $currentYear = $activeYearRow['year_name'] ?? '2025-26';

        // Calculate pending report cards count dynamically
        $pendingRC = (int)($db->selectOne("
            SELECT COUNT(*) as cnt 
            FROM students 
            WHERE tenant_id = ? 
              AND admission_status = 'enrolled' 
              AND deleted_at IS NULL 
              AND id NOT IN (SELECT DISTINCT student_id FROM student_report_cards WHERE academic_year = ?)
        ", [$tenantId, $currentYear])['cnt'] ?? 0);

        $stats = [
            'students'    => $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['cnt'] ?? 0,
            'admissions'  => $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL", [$tenantId])['cnt'] ?? 0,
            'classes'     => $db->selectOne("SELECT COUNT(*) as cnt FROM classes WHERE tenant_id = ?", [$tenantId])['cnt'] ?? 0,
            'teachers'    => $db->selectOne("SELECT COUNT(*) as cnt FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE u.tenant_id = ? AND r.slug = 'teacher'", [$tenantId])['cnt'] ?? 0,
            'absent'      => $db->selectOne("SELECT COUNT(*) as cnt FROM attendance WHERE date = ? AND status = 'absent'", [date('Y-m-d')])['cnt'] ?? 0,
            'pending_rc'  => $pendingRC,
        ];

        // Pending admissions list
        $pendingAdmissions = $db->select(
            "SELECT id, first_name, last_name FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5",
            [$tenantId]
        );

        // Recent activity: get dynamic events from activity logs related to students, teachers, classes
        $recentLogs = $db->select("
            SELECT al.*, u.name as user_name 
            FROM activity_logs al 
            LEFT JOIN users u ON u.id = al.user_id 
            ORDER BY al.created_at DESC LIMIT 5
        ");

        return $this->view('academic/workspace', compact('stats', 'pendingAdmissions', 'recentLogs'));
    }
}
