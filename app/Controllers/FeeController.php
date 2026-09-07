<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Models\{FeeInvoice, FeePayment, Student, ActivityLog};

class FeeController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $status = $this->request->get('status', '');
        $search = $this->request->get('search', '');
        $view   = $this->request->get('view', 'pending');
        $tenantId = \Core\Database::getTenantId();

        // Ensure payment_source column exists
        try {
            $this->db()->query("ALTER TABLE fee_payments ADD COLUMN IF NOT EXISTS payment_source VARCHAR(20) NOT NULL DEFAULT 'staff' COMMENT 'staff or parent_online'");
        } catch (\Throwable $e) { /* column may already exist */ }

        $where  = ['fi.tenant_id = ?'];
        $params = [$tenantId];

        if ($status) {
            $where[]  = 'fi.status = ?';
            $params[] = $status;
        }

        if ($search) {
            $where[]  = '(s.first_name LIKE ? OR s.last_name LIKE ? OR fi.invoice_number LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(' AND ', $where);

        $invoices = $this->db()->select("
            SELECT fi.*,
                   s.first_name, s.last_name, s.admission_number,
                   (
                       SELECT fp2.payment_source
                       FROM fee_payments fp2
                       WHERE fp2.invoice_id = fi.id
                       ORDER BY fp2.paid_at DESC LIMIT 1
                   ) AS last_payment_source
            FROM fee_invoices fi
            JOIN students s ON s.id = fi.student_id
            WHERE $whereClause
            ORDER BY fi.updated_at DESC, fi.due_date DESC
        ", $params);

        $stats = $this->db()->selectOne("
            SELECT
                (SELECT COALESCE(SUM(debit), 0) FROM student_ledgers sl JOIN students s ON sl.student_id = s.id WHERE s.tenant_id = ?) AS total_revenue_expected,
                (SELECT COALESCE(SUM(credit), 0) FROM student_ledgers sl JOIN students s ON sl.student_id = s.id WHERE s.tenant_id = ?) AS total_collected,
                (SELECT COALESCE(SUM(balance), 0) FROM student_ledgers sl JOIN students s ON sl.student_id = s.id WHERE s.tenant_id = ?) AS total_outstanding,
                COUNT(CASE WHEN status != 'paid' AND due_date < CURRENT_DATE THEN 1 END) AS overdue_count
            FROM fee_invoices
            WHERE tenant_id = ?
        ", [$tenantId, $tenantId, $tenantId, $tenantId]);

        // Recent payments from Parent Portal (online)
        $recentParentPayments = $this->db()->select("
            SELECT fp.*, fi.title AS invoice_title, fi.invoice_number,
                   s.first_name, s.last_name, s.admission_number
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id
            JOIN students s ON s.id = fi.student_id
            WHERE fi.tenant_id = ?
              AND (fp.payment_source = 'parent_online' OR fp.payment_ref LIKE 'SIM-%')
            ORDER BY fp.paid_at DESC
            LIMIT 10
        ", [$tenantId]);

        // Monthly revenue data for chart (current year)
        $monthlyRevenue = $this->db()->select("
            SELECT MONTH(fp.paid_at) as month, COALESCE(SUM(fp.amount), 0) as total
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id
            WHERE fi.tenant_id = ? AND YEAR(fp.paid_at) = YEAR(CURDATE())
            GROUP BY MONTH(fp.paid_at)
            ORDER BY month ASC
        ", [$tenantId]);

        // Build 12-month array
        $chartData = array_fill(1, 12, 0);
        foreach ($monthlyRevenue as $row) {
            $chartData[(int)$row['month']] = (float)$row['total'];
        }

        return $this->view('fees/index', compact('invoices', 'stats', 'status', 'search', 'view', 'recentParentPayments', 'chartData'));
    }

    public function exportCsv(): void
    {
        $tenantId = \Core\Database::getTenantId();
        $ledgers = $this->db()->select("
            SELECT sl.id, s.admission_number, s.first_name, s.last_name, sl.entry_type, sl.debit, sl.credit, sl.balance, sl.description, sl.created_at
            FROM student_ledgers sl
            JOIN students s ON sl.student_id = s.id
            WHERE s.tenant_id = ?
            ORDER BY sl.created_at DESC
        ", [$tenantId]);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fees_ledger_report.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Admission No', 'First Name', 'Last Name', 'Entry Type', 'Debit', 'Credit', 'Balance', 'Description', 'Date']);
        
        foreach ($ledgers as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    public function create(): string
    {
        $students = $this->db()->select("
            SELECT id, first_name, last_name, admission_number 
            FROM students 
            WHERE tenant_id = ? AND deleted_at IS NULL 
            ORDER BY first_name ASC
        ", [\Core\Database::getTenantId()]);

        return $this->view('fees/create', compact('students'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        unset($data['_csrf']);

        $rules = [
            'student_id'  => 'required',
            'title'       => 'required|min:3',
            'amount'      => 'required|numeric',
            'due_date'    => 'required|date',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            return $this->redirect('/fees/create');
        }

        // Generate invoice number
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        $data['invoice_number'] = "INV-{$year}-{$random}";
        $data['status'] = 'unpaid';
        $data['paid_amount'] = 0.00;
        $data['tenant_id'] = \Core\Database::getTenantId();
        $data['school_id'] = \Core\Database::getSchoolId() ?: 1;
        $data['branch_id'] = \Core\Database::getBranchId() ?: 1;
        $amount = (float)$data['amount'];

        $id = $this->db()->insert('fee_invoices', [
            'student_id'     => $data['student_id'],
            'title'          => $data['title'],
            'amount'         => $amount,
            'due_date'       => $data['due_date'],
            'invoice_number' => $data['invoice_number'],
            'tenant_id'      => $data['tenant_id'],
            'school_id'      => $data['school_id'],
            'branch_id'      => $data['branch_id'],
            'paid_amount'    => 0.00,
            'status'         => 'unpaid',
            'created_at'     => now(),
        ]);

        // Dual-Write: Ledger Debit
        $currentBalance = (float)($this->db()->selectOne("SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$data['student_id']])['balance'] ?? 0);
        $newBalance = $currentBalance + $amount;
        $this->db()->insert('student_ledgers', [
            'student_id'   => $data['student_id'],
            'entry_type'   => 'invoice',
            'debit'        => $amount,
            'credit'       => 0.00,
            'balance'      => $newBalance,
            'reference_id' => $id,
            'description'  => "Manual Invoice: {$data['title']}"
        ]);

        // Audit Trail (Student Timeline)
        $student = $this->db()->selectOne("SELECT first_name, last_name FROM students WHERE id = ?", [$data['student_id']]);
        $this->db()->insert('student_timeline', [
            'student_id'  => $data['student_id'],
            'event_type'  => 'fee_invoice',
            'title'       => 'Fee Invoice Created',
            'description' => "Invoice '{$data['title']}' for Rs. {$amount} created manually.",
            'color'       => 'blue',
            'icon'        => 'document',
            'actor_name'  => Session::get('user')['name'] ?? 'System',
            'occurred_at' => now(),
        ]);

        ActivityLog::log('fee_invoice_created', auth_id(), ['invoice_id' => $id]);
        Session::flash('success', "Invoice {$data['invoice_number']} created successfully.");
        return $this->redirect('/fees');
    }

    public function recordPayment(string $id): string
    {
        $data = $this->request->getBody();
        $returnUrl = $data['return_url'] ?? '/fees';
        
        $invoice = FeeInvoice::find((int)$id);
        if (!$invoice || $invoice['status'] === 'paid') {
            Session::flash('error', 'Invoice cannot be paid.');
            return $this->redirect($returnUrl);
        }

        $amount = (float)($data['amount'] ?? 0);
        $method = $data['payment_method'] ?? 'Cash';
        $ref    = $data['payment_ref'] ?? '';

        if ($amount <= 0 || $amount > ($invoice['amount'] - $invoice['paid_amount'])) {
            Session::flash('error', 'Invalid payment amount.');
            return $this->redirect($returnUrl);
        }

        $newPaidAmount = (float)$invoice['paid_amount'] + $amount;
        $status = $newPaidAmount >= (float)$invoice['amount'] ? 'paid' : 'partially_paid';

        $this->db()->update('fee_invoices', [
            'status'      => $status,
            'paid_amount' => $newPaidAmount,
            'paid_at'     => now(),
        ], 'id = ?', [$invoice['id']]);

        $paymentId = $this->db()->insert('fee_payments', [
            'tenant_id'      => $invoice['tenant_id'],
            'school_id'      => $invoice['school_id'],
            'branch_id'      => $invoice['branch_id'],
            'invoice_id'     => $invoice['id'],
            'amount'         => $amount,
            'payment_method' => $method,
            'payment_ref'    => $ref ?: 'Manual-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'paid_at'        => now(),
            'payment_source' => 'staff',
        ]);

        // Record to Student Ledger
        $currentBalance = (float)($this->db()->selectOne("SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$invoice['student_id']])['balance'] ?? 0);
        $newBalance = $currentBalance - $amount;
        $this->db()->insert('student_ledgers', [
            'student_id'   => $invoice['student_id'],
            'entry_type'   => 'payment',
            'debit'        => 0.00,
            'credit'       => $amount,
            'balance'      => $newBalance,
            'reference_id' => $paymentId,
            'description'  => "Payment: Received {$amount} via {$method} for '{$invoice['title']}'"
        ]);

        // Fetch student details with parent phone for WhatsApp Notification
        $student = $this->db()->selectOne("
            SELECT s.*, p.whatsapp_number as phone, p.first_name as parent_name 
            FROM students s 
            LEFT JOIN parent_student ps ON s.id = ps.student_id 
            LEFT JOIN parents p ON ps.parent_id = p.id 
            WHERE s.id = ?
        ", [$invoice['student_id']]);

        // WhatsApp Notification
        \App\Services\NotificationService::notifyPaymentReceived($student, $invoice, $amount);

        // Log to timeline
        $this->db()->insert('student_timeline', [
            'student_id'  => $invoice['student_id'],
            'event_type'  => 'fee_payment',
            'title'       => 'Fee Payment Logged',
            'description' => "Logged manual payment of {$amount} INR for '{$invoice['title']}' via {$method}.",
            'color'       => 'green',
            'icon'        => 'cash',
            'actor_name'  => auth()['name'] ?? 'Staff',
            'occurred_at' => now(),
        ]);

        ActivityLog::log('fee_payment_recorded', auth_id(), ['invoice_id' => $invoice['id'], 'amount' => $amount]);
        Session::flash('success', 'Payment recorded successfully.');
        
        $data = $this->request->getBody();
        $returnUrl = $data['return_url'] ?? '/fees';
        return $this->redirect($returnUrl);
    }

    public function destroy(string $id): string
    {
        // Delete manual payments and invoice
        $invoice = FeeInvoice::find((int)$id);
        if ($invoice) {
            $this->db()->query("DELETE FROM fee_payments WHERE invoice_id = ?", [$invoice['id']]);
            $this->db()->query("DELETE FROM fee_invoices WHERE id = ?", [$invoice['id']]);
            ActivityLog::log('fee_invoice_deleted', auth_id(), ['invoice_id' => $id]);
            Session::flash('success', 'Invoice deleted.');
        }

        return $this->redirect('/fees');
    }
}
