<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class ReportsController extends Controller
{
    public function index(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        // Financial Analytics
        $totalRevenue = $db->selectOne("SELECT SUM(amount) as total FROM fee_payments WHERE tenant_id = ?", [$tenantId])['total'] ?? 0;
        $outstandingFees = $db->selectOne("SELECT SUM(amount - paid_amount) as outstanding FROM fee_invoices WHERE tenant_id = ? AND status != 'paid'", [$tenantId])['outstanding'] ?? 0;

        // Academic Analytics
        $totalStudents = $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'", [$tenantId])['cnt'] ?? 0;
        if ($totalStudents == 0) {
            $totalStudents = $db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND is_active = 1", [$tenantId])['cnt'] ?? 0;
        }
        $totalClasses = $db->selectOne("SELECT COUNT(*) as cnt FROM classes WHERE tenant_id = ?", [$tenantId])['cnt'] ?? 0;

        // WhatsApp / Communication Analytics
        $whatsappTotal = $db->selectOne("SELECT COUNT(*) as cnt FROM whatsapp_message_logs WHERE tenant_id = ?", [$tenantId])['cnt'] ?? 0;
        $whatsappSentThisMonth = $db->selectOne("SELECT COUNT(*) as cnt FROM whatsapp_message_logs WHERE tenant_id = ? AND MONTH(sent_at) = MONTH(CURRENT_DATE()) AND YEAR(sent_at) = YEAR(CURRENT_DATE())", [$tenantId])['cnt'] ?? 0;
        $whatsappFailed = $db->selectOne("SELECT COUNT(*) as cnt FROM whatsapp_message_logs WHERE tenant_id = ? AND status = 'failed'", [$tenantId])['cnt'] ?? 0;

        // Staff Analytics
        $totalStaff = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE status = 'active'")['cnt'] ?? 0;
        if ($totalStaff == 0) {
            $totalStaff = $db->selectOne("SELECT COUNT(*) as cnt FROM staff WHERE deleted_at IS NULL")['cnt'] ?? 0;
        }

        return $this->view('reports/index', compact(
            'totalRevenue', 'outstandingFees',
            'totalStudents', 'totalClasses',
            'whatsappTotal', 'whatsappSentThisMonth', 'whatsappFailed',
            'totalStaff'
        ));
    }

    public function finance(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        $totalRevenue = $db->selectOne("SELECT SUM(amount) as total FROM fee_payments WHERE tenant_id = ?", [$tenantId])['total'] ?? 0;
        $totalExpected = $db->selectOne("SELECT SUM(amount) as total FROM fee_invoices WHERE tenant_id = ?", [$tenantId])['total'] ?? 0;
        $outstandingFees = $db->selectOne("SELECT SUM(amount - paid_amount) as outstanding FROM fee_invoices WHERE tenant_id = ? AND status != 'paid'", [$tenantId])['outstanding'] ?? 0;
        $overdueCount = $db->selectOne("SELECT COUNT(*) as cnt FROM fee_invoices WHERE tenant_id = ? AND status != 'paid' AND due_date < CURRENT_DATE()", [$tenantId])['cnt'] ?? 0;

        $collectionsByMode = $db->select("
            SELECT COALESCE(payment_method, 'Online') as payment_mode, SUM(amount) as total, COUNT(*) as cnt 
            FROM fee_payments 
            WHERE tenant_id = ? 
            GROUP BY payment_method
        ", [$tenantId]);

        $recentPayments = $db->select("
            SELECT fp.*, fi.invoice_number, fi.title as invoice_title, s.first_name, s.last_name 
            FROM fee_payments fp 
            JOIN fee_invoices fi ON fp.invoice_id = fi.id 
            JOIN students s ON fi.student_id = s.id 
            WHERE fp.tenant_id = ? 
            ORDER BY fp.paid_at DESC, fp.id DESC 
            LIMIT 10
        ", [$tenantId]);

        return $this->view('reports/finance', compact(
            'totalRevenue', 'totalExpected', 'outstandingFees', 'overdueCount',
            'collectionsByMode', 'recentPayments'
        ));
    }

    public function students(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        $statusCounts = $db->select("
            SELECT admission_status, COUNT(*) as cnt 
            FROM students 
            WHERE tenant_id = ? AND deleted_at IS NULL 
            GROUP BY admission_status
        ", [$tenantId]);

        $classCounts = $db->select("
            SELECT c.name as class_name, c.section, COUNT(s.id) as cnt 
            FROM classes c 
            LEFT JOIN students s ON s.class_id = c.id AND s.deleted_at IS NULL 
            WHERE c.tenant_id = ? 
            GROUP BY c.id 
            ORDER BY c.name ASC, c.section ASC
        ", [$tenantId]);

        $disabilityCounts = $db->select("
            SELECT COALESCE(blood_group, 'Unknown') as disability_type, COUNT(*) as cnt 
            FROM students 
            WHERE tenant_id = ? AND deleted_at IS NULL 
            GROUP BY blood_group
        ", [$tenantId]);

        $genderCounts = $db->select("
            SELECT gender, COUNT(*) as cnt 
            FROM students 
            WHERE tenant_id = ? AND deleted_at IS NULL 
            GROUP BY gender
        ", [$tenantId]);

        return $this->view('reports/students', compact(
            'statusCounts', 'classCounts', 'disabilityCounts', 'genderCounts'
        ));
    }

    public function staff(): string
    {
        $db = Application::$app->db;

        $deptCounts = $db->select("
            SELECT d.name as department_name, COUNT(e.id) as cnt 
            FROM departments d 
            LEFT JOIN employees e ON e.department_id = d.id AND e.status = 'active' 
            WHERE d.is_active = 1 
            GROUP BY d.id 
            ORDER BY d.name ASC
        ");

        $desigCounts = $db->select("
            SELECT des.title as designation_title, COUNT(e.id) as cnt 
            FROM designations des 
            LEFT JOIN employees e ON e.designation_id = des.id AND e.status = 'active' 
            WHERE des.is_active = 1 
            GROUP BY des.id 
            ORDER BY des.title ASC
        ");

        // Leave Requests
        $leaveStats = $db->selectOne("
            SELECT 
                COUNT(*) as total, 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending, 
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved, 
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected 
            FROM leave_requests
        ");

        // Payroll Runs
        $payrollSummary = $db->select("
            SELECT pr.*, 
                   (SELECT SUM(net_salary) FROM payroll_items WHERE payroll_run_id = pr.id) as total_payout, 
                   (SELECT COUNT(*) FROM payroll_items WHERE payroll_run_id = pr.id) as employee_count 
            FROM payroll_runs pr 
            ORDER BY pr.created_at DESC 
            LIMIT 5
        ");

        return $this->view('reports/staff', compact(
            'deptCounts', 'desigCounts', 'leaveStats', 'payrollSummary'
        ));
    }

    public function communication(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        $whatsappTotal = $db->selectOne("SELECT COUNT(*) as cnt FROM whatsapp_message_logs WHERE tenant_id = ?", [$tenantId])['cnt'] ?? 0;
        
        $statusSummary = $db->select("
            SELECT status, COUNT(*) as cnt 
            FROM whatsapp_message_logs 
            WHERE tenant_id = ? 
            GROUP BY status
        ", [$tenantId]);

        $whatsappLogs = $db->select("
            SELECT * 
            FROM whatsapp_message_logs 
            WHERE tenant_id = ? 
            ORDER BY sent_at DESC, id DESC 
            LIMIT 50
        ", [$tenantId]);

        return $this->view('reports/communication', compact(
            'whatsappTotal', 'statusSummary', 'whatsappLogs'
        ));
    }
}
