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

        $stats = [
            'students'    => $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['cnt'] ?? 0,
            'admissions'  => $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL", [$tenantId])['cnt'] ?? 0,
            'classes'     => $db->selectOne("SELECT COUNT(*) as cnt FROM classes WHERE tenant_id = ?", [$tenantId])['cnt'] ?? 0,
            'teachers'    => $db->selectOne("SELECT COUNT(*) as cnt FROM users WHERE tenant_id = ? AND designation = 'Teacher'", [$tenantId])['cnt'] ?? 8,
            'absent'      => $db->selectOne("SELECT COUNT(*) as cnt FROM attendance WHERE date = ? AND status = 'absent'", [date('Y-m-d')])['cnt'] ?? 2,
            'iep_reviews' => 4,
            'pending_rc'  => 8,
        ];

        $recentStudents = $db->select(
            "SELECT * FROM students WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 4",
            [$tenantId]
        );

        return $this->view('academic/workspace', compact('stats', 'recentStudents'));
    }
}
