<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\{Student, User, ActivityLog};

class DashboardController extends Controller
{
    public function index(): string
    {
        $user     = $this->auth();
        $tenantId = $user['tenant_id'];

        $db = \Core\Application::$app->db;

        $stats = [
            'total_students'  => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'enrolled'        => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'enrolled' AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'applied'         => (int) ($db->selectOne("SELECT COUNT(*) as c FROM students WHERE tenant_id = ? AND admission_status = 'applied' AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'total_users'     => (int) ($db->selectOne("SELECT COUNT(*) as c FROM users WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
            'total_schools'   => (int) ($db->selectOne("SELECT COUNT(*) as c FROM schools WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['c'] ?? 0),
        ];

        $statusCounts = Student::statusCounts();
        $recentLogs   = ActivityLog::recent(10);
        $recentStudents = $db->select(
            "SELECT s.*, b.name as branch_name FROM students s LEFT JOIN branches b ON b.id = s.branch_id
             WHERE s.tenant_id = ? AND s.deleted_at IS NULL ORDER BY s.created_at DESC LIMIT 5",
            [$tenantId]
        );

        return $this->view('dashboard/index', compact('stats', 'statusCounts', 'recentLogs', 'recentStudents', 'user'));
    }
}
