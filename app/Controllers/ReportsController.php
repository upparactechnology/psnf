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

        // ── Core KPIs ──
        $totalRevenue = (float)($db->selectOne("SELECT COALESCE(SUM(amount),0) as total FROM fee_payments WHERE tenant_id = ?", [$tenantId])['total'] ?? 0);
        $totalExpected = (float)($db->selectOne("SELECT COALESCE(SUM(amount),0) as total FROM fee_invoices WHERE tenant_id = ?", [$tenantId])['total'] ?? 0);
        $outstandingFees = (float)($db->selectOne("SELECT COALESCE(SUM(amount - paid_amount),0) as outstanding FROM fee_invoices WHERE tenant_id = ? AND status != 'paid'", [$tenantId])['outstanding'] ?? 0);
        $overdueCount = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM fee_invoices WHERE tenant_id = ? AND status != 'paid' AND due_date < CURRENT_DATE()", [$tenantId])['cnt'] ?? 0);
        $overdueAmount = (float)($db->selectOne("SELECT COALESCE(SUM(amount - paid_amount),0) as amt FROM fee_invoices WHERE tenant_id = ? AND status != 'paid' AND due_date < CURRENT_DATE()", [$tenantId])['amt'] ?? 0);
        $paidCount = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM fee_invoices WHERE tenant_id = ? AND status = 'paid'", [$tenantId])['cnt'] ?? 0);
        $unpaidCount = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM fee_invoices WHERE tenant_id = ? AND status = 'unpaid'", [$tenantId])['cnt'] ?? 0);
        $partialCount = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM fee_invoices WHERE tenant_id = ? AND status = 'partially_paid'", [$tenantId])['cnt'] ?? 0);
        $totalInvoiceCount = $paidCount + $unpaidCount + $partialCount;
        $collectionRate = $totalExpected > 0 ? round($totalRevenue / $totalExpected * 100) : 0;

        // ── Invoice Status Breakdown (pie) ──
        $invoiceStatusCounts = $db->select("
            SELECT status, COUNT(*) as cnt, COALESCE(SUM(amount),0) as total_amount
            FROM fee_invoices WHERE tenant_id = ? GROUP BY status ORDER BY cnt DESC
        ", [$tenantId]);

        // ── Collections by Payment Mode (pie + bar) ──
        $collectionsByMode = $db->select("
            SELECT COALESCE(payment_method, 'Other') as payment_mode, SUM(amount) as total, COUNT(*) as cnt 
            FROM fee_payments WHERE tenant_id = ? GROUP BY payment_method ORDER BY total DESC
        ", [$tenantId]);

        // ── Monthly Revenue vs Outstanding Trend (last 12 months) ──
        $monthlyTrend = $db->select("
            SELECT DATE_FORMAT(fp.paid_at, '%Y-%m') as month,
                   COALESCE(SUM(fp.amount),0) as collected,
                   COUNT(*) as txn_count
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id AND fi.tenant_id = ?
            WHERE fp.paid_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // ── Outstanding by Month (invoices not yet paid, generated month) ──
        $outstandingByMonth = $db->select("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   COALESCE(SUM(amount - paid_amount),0) as outstanding,
                   COUNT(*) as invoice_count
            FROM fee_invoices WHERE tenant_id = ? AND status != 'paid'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // ── Category-wise Collection ──
        $categoryCollection = $db->select("
            SELECT fc.name as category_name, 
                   COALESCE(SUM(fp.amount),0) as collected,
                   COUNT(DISTINCT fp.id) as txn_count
            FROM fee_categories fc
            LEFT JOIN fee_structures fs ON fs.main_group_id = fc.id
            LEFT JOIN fee_invoices fi ON fi.title = fs.name AND fi.tenant_id = ?
            LEFT JOIN fee_payments fp ON fp.invoice_id = fi.id
            WHERE fc.is_active = 1
            GROUP BY fc.id, fc.name ORDER BY collected DESC
        ", [$tenantId]);

        // ── Class-wise Fee Collection ──
        $classWiseCollection = $db->select("
            SELECT c.name as class_name, c.section,
                   COALESCE(SUM(fp.amount),0) as collected,
                   COALESCE(SUM(fi.amount),0) as billed,
                   COUNT(DISTINCT fi.id) as invoice_count
            FROM classes c
            LEFT JOIN students s ON s.class_id = c.id AND s.deleted_at IS NULL
            LEFT JOIN fee_invoices fi ON fi.student_id = s.id AND fi.tenant_id = ?
            LEFT JOIN fee_payments fp ON fp.invoice_id = fi.id
            WHERE c.tenant_id = ?
            GROUP BY c.id, c.name, c.section
            HAVING collected > 0 OR billed > 0
            ORDER BY collected DESC
        ", [$tenantId, $tenantId]);

        // ── Daily Collection This Month ──
        $dailyCollection = $db->select("
            SELECT DATE(fp.paid_at) as pay_date, SUM(fp.amount) as daily_total, COUNT(*) as txn_count
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id AND fi.tenant_id = ?
            WHERE MONTH(fp.paid_at) = MONTH(CURRENT_DATE()) AND YEAR(fp.paid_at) = YEAR(CURRENT_DATE())
            GROUP BY pay_date ORDER BY pay_date ASC
        ", [$tenantId]);

        // ── Discount Summary ──
        $discountSummary = $db->selectOne("
            SELECT COALESCE(SUM(discount_amount),0) as total_discount,
                   COUNT(CASE WHEN discount_amount > 0 THEN 1 END) as discounted_invoices
            FROM fee_invoices WHERE tenant_id = ?
        ", [$tenantId]);

        // ── Top Paying Students ──
        $topPayingStudents = $db->select("
            SELECT s.first_name, s.last_name, s.class,
                   COALESCE(SUM(fp.amount),0) as total_paid,
                   COUNT(fp.id) as payment_count
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id AND fi.tenant_id = ?
            JOIN students s ON fi.student_id = s.id AND s.deleted_at IS NULL
            GROUP BY s.id, s.first_name, s.last_name, s.class
            ORDER BY total_paid DESC LIMIT 10
        ", [$tenantId]);

        // ── Recent Payments ──
        $recentPayments = $db->select("
            SELECT fp.*, fi.invoice_number, fi.title as invoice_title, s.first_name, s.last_name 
            FROM fee_payments fp 
            JOIN fee_invoices fi ON fp.invoice_id = fi.id 
            JOIN students s ON fi.student_id = s.id 
            WHERE fp.tenant_id = ? 
            ORDER BY fp.paid_at DESC, fp.id DESC LIMIT 15
        ", [$tenantId]);

        return $this->view('reports/finance', compact(
            'totalRevenue', 'totalExpected', 'outstandingFees', 'overdueCount', 'overdueAmount',
            'paidCount', 'unpaidCount', 'partialCount', 'totalInvoiceCount', 'collectionRate',
            'invoiceStatusCounts', 'collectionsByMode', 'monthlyTrend', 'outstandingByMonth',
            'categoryCollection', 'classWiseCollection', 'dailyCollection',
            'discountSummary', 'topPayingStudents', 'recentPayments'
        ));
    }

    public function students(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        // ── Student Counts ──
        $totalStudents = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL", [$tenantId])['cnt'] ?? 0);
        $enrolledCount = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND admission_status = 'enrolled'", [$tenantId])['cnt'] ?? 0);

        // ── Status Distribution ──
        $statusCounts = $db->select("
            SELECT admission_status, COUNT(*) as cnt 
            FROM students WHERE tenant_id = ? AND deleted_at IS NULL GROUP BY admission_status
        ", [$tenantId]);

        // ── Class Distribution ──
        $classCounts = $db->select("
            SELECT c.name as class_name, c.section, COUNT(s.id) as cnt 
            FROM classes c 
            LEFT JOIN students s ON s.class_id = c.id AND s.deleted_at IS NULL 
            WHERE c.tenant_id = ? GROUP BY c.id ORDER BY c.name ASC, c.section ASC
        ", [$tenantId]);

        // ── Gender Distribution ──
        $genderCounts = $db->select("
            SELECT gender, COUNT(*) as cnt 
            FROM students WHERE tenant_id = ? AND deleted_at IS NULL GROUP BY gender
        ", [$tenantId]);

        // ── Blood Group Distribution ──
        $bloodGroupCounts = $db->select("
            SELECT COALESCE(blood_group, 'Unknown') as blood_group, COUNT(*) as cnt 
            FROM students WHERE tenant_id = ? AND deleted_at IS NULL GROUP BY blood_group ORDER BY cnt DESC
        ", [$tenantId]);

        // ── Age Distribution ──
        $ageDistribution = $db->select("
            SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 5 THEN 'Under 5'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 5 AND 8 THEN '5-8'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 9 AND 12 THEN '9-12'
                    WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 13 AND 16 THEN '13-16'
                    ELSE '16+'
                END as age_group,
                COUNT(*) as cnt
            FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND dob IS NOT NULL
            GROUP BY age_group ORDER BY FIELD(age_group, 'Under 5', '5-8', '9-12', '13-16', '16+')
        ", [$tenantId]);

        // ── Monthly Enrollment Trend (last 12 months) ──
        $enrollmentTrend = $db->select("
            SELECT DATE_FORMAT(enrolled_date, '%Y-%m') as month, COUNT(*) as cnt
            FROM students WHERE tenant_id = ? AND deleted_at IS NULL AND enrolled_date IS NOT NULL
              AND enrolled_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // ── Attendance Overview (last 6 months) ──
        $attendanceOverview = $db->select("
            SELECT DATE_FORMAT(date, '%Y-%m') as month,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_cnt,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_cnt,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_cnt,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_cnt,
                COUNT(*) as total
            FROM attendance WHERE tenant_id = ? 
              AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // Overall attendance rate
        $attStats = $db->selectOne("
            SELECT COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_cnt
            FROM attendance WHERE tenant_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ", [$tenantId]);
        $attendanceRate = ($attStats['total'] ?? 0) > 0 ? round(($attStats['present_cnt'] ?? 0) / $attStats['total'] * 100) : 0;

        // ── Exam Performance (last 5 exams by avg marks) ──
        $examPerformance = $db->select("
            SELECT exam_name, 
                   ROUND(AVG(marks_obtained), 1) as avg_marks,
                   ROUND(AVG(max_marks), 1) as avg_max,
                   COUNT(DISTINCT student_id) as student_count
            FROM exam_results WHERE tenant_id = ? 
            GROUP BY exam_name ORDER BY date_published DESC LIMIT 8
        ", [$tenantId]);

        // Average marks overall
        $avgMarks = $db->selectOne("
            SELECT ROUND(AVG(marks_obtained), 1) as avg_marks, ROUND(AVG(max_marks), 1) as avg_max
            FROM exam_results WHERE tenant_id = ?
        ", [$tenantId]);
        $avgPercentage = ($avgMarks['avg_max'] ?? 0) > 0 ? round(($avgMarks['avg_marks'] ?? 0) / $avgMarks['avg_max'] * 100) : 0;

        // ── Fee Collection Summary ──
        $feeStats = $db->selectOne("
            SELECT 
                COALESCE(SUM(amount), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as collected,
                COALESCE(SUM(amount - paid_amount), 0) as outstanding,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status != 'paid' THEN 1 END) as unpaid_count
            FROM fee_invoices WHERE tenant_id = ?
        ", [$tenantId]);

        // Monthly fee collection trend (last 6 months)
        $feeTrend = $db->select("
            SELECT DATE_FORMAT(fp.paid_at, '%Y-%m') as month, 
                   COALESCE(SUM(fp.amount), 0) as collected
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id
            WHERE fi.tenant_id = ? 
              AND fp.paid_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // ── Religion Distribution ──
        $religionCounts = $db->select("
            SELECT COALESCE(religion, 'Not Specified') as religion, COUNT(*) as cnt
            FROM students WHERE tenant_id = ? AND deleted_at IS NULL GROUP BY religion ORDER BY cnt DESC
        ", [$tenantId]);

        return $this->view('reports/students', compact(
            'totalStudents', 'enrolledCount', 'statusCounts', 'classCounts',
            'genderCounts', 'bloodGroupCounts', 'ageDistribution',
            'enrollmentTrend', 'attendanceOverview', 'attendanceRate',
            'examPerformance', 'avgMarks', 'avgPercentage',
            'feeStats', 'feeTrend', 'religionCounts'
        ));
    }

    public function staff(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        // ── Core KPIs ──
        $totalStaff = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE tenant_id = ? AND status = 'active'", [$tenantId])['cnt'] ?? 0);
        $inactiveStaff = (int)($db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE tenant_id = ? AND status != 'active'", [$tenantId])['cnt'] ?? 0);
        $totalEmployees = $totalStaff + $inactiveStaff;

        // ── Employment Type Breakdown ──
        $employmentTypeCounts = $db->select("
            SELECT COALESCE(employment_type, 'full_time') as emp_type, COUNT(*) as cnt
            FROM employees WHERE tenant_id = ? GROUP BY emp_type ORDER BY cnt DESC
        ", [$tenantId]);

        // ── Department Breakdown (pie + bar) ──
        $deptCounts = $db->select("
            SELECT d.name as department_name, COUNT(e.id) as cnt 
            FROM departments d 
            LEFT JOIN employees e ON e.department_id = d.id AND e.status = 'active' AND e.tenant_id = ?
            WHERE d.is_active = 1 AND d.tenant_id = ?
            GROUP BY d.id ORDER BY d.name ASC
        ", [$tenantId, $tenantId]);

        // ── Designation Breakdown (bar) ──
        $desigCounts = $db->select("
            SELECT des.title as designation_title, COUNT(e.id) as cnt 
            FROM designations des 
            LEFT JOIN employees e ON e.designation_id = des.id AND e.status = 'active' AND e.tenant_id = ?
            WHERE des.is_active = 1 AND des.tenant_id = ?
            GROUP BY des.id ORDER BY des.title ASC
        ", [$tenantId, $tenantId]);

        // ── Leave Requests Stats ──
        $leaveStats = $db->selectOne("
            SELECT 
                COUNT(*) as total, 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending, 
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved, 
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COALESCE(SUM(days),0) as total_days
            FROM leave_requests WHERE tenant_id = ?
        ", [$tenantId]);

        // ── Monthly Leave Trend (last 6 months) ──
        $leaveTrend = $db->select("
            SELECT DATE_FORMAT(start_date, '%Y-%m') as month,
                   COUNT(*) as total_requests,
                   SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_cnt,
                   SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_cnt,
                   COALESCE(SUM(CASE WHEN status = 'approved' THEN days ELSE 0 END),0) as approved_days
            FROM leave_requests WHERE tenant_id = ?
              AND start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // ── Payroll Summary ──
        $payrollSummary = $db->select("
            SELECT pr.*, 
                   (SELECT COALESCE(SUM(net_salary),0) FROM payroll_items WHERE payroll_run_id = pr.id) as total_payout, 
                   (SELECT COALESCE(SUM(gross_salary),0) FROM payroll_items WHERE payroll_run_id = pr.id) as total_gross,
                   (SELECT COALESCE(SUM(total_deductions),0) FROM payroll_items WHERE payroll_run_id = pr.id) as total_deductions,
                   (SELECT COUNT(*) FROM payroll_items WHERE payroll_run_id = pr.id) as employee_count 
            FROM payroll_runs pr WHERE pr.tenant_id = ?
            ORDER BY pr.created_at DESC LIMIT 6
        ", [$tenantId]);

        // ── Monthly Payroll Trend ──
        $payrollTrend = $db->select("
            SELECT pr.month_year,
                   pr.total_gross, pr.total_net, pr.total_deductions
            FROM payroll_runs pr WHERE pr.tenant_id = ?
            ORDER BY pr.month_year ASC LIMIT 12
        ", [$tenantId]);

        // ── Salary Structure Overview (avg salary ranges) ──
        $salaryRanges = $db->select("
            SELECT 
                CASE 
                    WHEN basic < 15000 THEN 'Under 15K'
                    WHEN basic BETWEEN 15000 AND 25000 THEN '15K-25K'
                    WHEN basic BETWEEN 25001 AND 35000 THEN '25K-35K'
                    WHEN basic BETWEEN 35001 AND 50000 THEN '35K-50K'
                    ELSE '50K+'
                END as salary_range,
                COUNT(*) as cnt
            FROM salary_structures WHERE tenant_id = ?
            GROUP BY salary_range ORDER BY FIELD(salary_range, 'Under 15K', '15K-25K', '25K-35K', '35K-50K', '50K+')
        ", [$tenantId]);

        // ── Average Salary by Department ──
        $avgSalaryByDept = $db->select("
            SELECT d.name as department_name, 
                   ROUND(AVG(ss.basic),0) as avg_basic,
                   COUNT(DISTINCT e.id) as staff_count
            FROM departments d
            JOIN employees e ON e.department_id = d.id AND e.status = 'active' AND e.tenant_id = ?
            LEFT JOIN salary_structures ss ON ss.employee_id = e.id
            WHERE d.is_active = 1 AND d.tenant_id = ?
            GROUP BY d.id, d.name HAVING staff_count > 0 ORDER BY avg_basic DESC
        ", [$tenantId, $tenantId]);

        // ── Staff Attendance Overview (last 30 days) ──
        $staffAttStats = $db->selectOne("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_cnt,
                   SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_cnt,
                   SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_cnt,
                   SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_cnt,
                   SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as on_leave_cnt,
                   ROUND(AVG(working_hours),1) as avg_hours
            FROM staff_attendance_logs WHERE tenant_id = ?
              AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ", [$tenantId]);

        // ── Monthly Attendance Trend ──
        $staffAttTrend = $db->select("
            SELECT DATE_FORMAT(date, '%Y-%m') as month,
                   SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_cnt,
                   SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_cnt,
                   SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_cnt,
                   SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_cnt
            FROM staff_attendance_logs WHERE tenant_id = ?
              AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month ORDER BY month ASC
        ", [$tenantId]);

        // ── Top 10 Most Punctual Staff ──
        $topPunctualStaff = $db->select("
            SELECT e.first_name, e.last_name, e.emp_code,
                   COUNT(sal.id) as total_days,
                   SUM(CASE WHEN sal.status = 'present' THEN 1 ELSE 0 END) as present_days,
                   ROUND(SUM(CASE WHEN sal.status = 'present' THEN 1 ELSE 0 END) / COUNT(sal.id) * 100,1) as attendance_pct
            FROM employees e
            JOIN staff_attendance_logs sal ON sal.employee_id = e.id AND sal.tenant_id = ?
            WHERE e.status = 'active' AND e.tenant_id = ?
              AND sal.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY e.id, e.first_name, e.last_name, e.emp_code
            HAVING total_days >= 5
            ORDER BY attendance_pct DESC, total_days DESC LIMIT 10
        ", [$tenantId, $tenantId]);

        // ── Recent Payroll Items (latest run detail) ──
        $latestPayrollItems = [];
        if (!empty($payrollSummary)) {
            $latestRunId = $payrollSummary[0]['id'];
            $latestPayrollItems = $db->select("
                SELECT pi.*, e.first_name, e.last_name, e.emp_code
                FROM payroll_items pi
                JOIN employees e ON e.id = pi.employee_id
                WHERE pi.payroll_run_id = ? ORDER BY pi.net_salary DESC
            ", [$latestRunId]);
        }

        return $this->view('reports/staff', compact(
            'totalStaff', 'inactiveStaff', 'totalEmployees', 'employmentTypeCounts',
            'deptCounts', 'desigCounts',
            'leaveStats', 'leaveTrend',
            'payrollSummary', 'payrollTrend', 'salaryRanges', 'avgSalaryByDept',
            'staffAttStats', 'staffAttTrend', 'topPunctualStaff', 'latestPayrollItems'
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
