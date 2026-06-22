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
        
        $where = ["fi.tenant_id = ?"];
        $params = [\Core\Database::getTenantId()];
        
        if ($status) {
            $where[] = "fi.status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR fi.invoice_number LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $whereClause = implode(" AND ", $where);
        
        $invoices = $this->db()->select("
            SELECT fi.*, s.first_name, s.last_name, s.admission_number
            FROM fee_invoices fi
            JOIN students s ON s.id = fi.student_id
            WHERE $whereClause
            ORDER BY fi.due_date DESC
        ", $params);
 
        $stats = $this->db()->selectOne("
            SELECT 
                COALESCE(SUM(amount), 0) as total_invoiced,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(amount - paid_amount), 0) as total_unpaid,
                COUNT(CASE WHEN status != 'paid' AND due_date < CURRENT_DATE THEN 1 END) as overdue_count
            FROM fee_invoices
            WHERE tenant_id = ?
        ", [\Core\Database::getTenantId()]);
 
        return $this->view('fees/index', compact('invoices', 'stats', 'status', 'search', 'view'));
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

        $invoiceId = FeeInvoice::create($data);

        ActivityLog::log('fee_invoice_created', auth_id(), ['invoice_id' => $invoiceId]);
        Session::flash('success', "Invoice {$data['invoice_number']} created successfully.");
        return $this->redirect('/fees');
    }

    public function recordPayment(string $id): string
    {
        $invoice = FeeInvoice::find((int)$id);
        if (!$invoice || $invoice['status'] === 'paid') {
            Session::flash('error', 'Invoice cannot be paid.');
            return $this->redirect('/fees');
        }

        $amount = (float)$this->request->post('amount');
        $method = $this->request->post('payment_method', 'Cash');
        $ref    = $this->request->post('payment_ref', '');

        if ($amount <= 0 || $amount > ($invoice['amount'] - $invoice['paid_amount'])) {
            Session::flash('error', 'Invalid payment amount.');
            return $this->redirect('/fees');
        }

        $newPaidAmount = (float)$invoice['paid_amount'] + $amount;
        $status = $newPaidAmount >= (float)$invoice['amount'] ? 'paid' : 'partially_paid';

        $this->db()->update('fee_invoices', [
            'status'      => $status,
            'paid_amount' => $newPaidAmount,
            'paid_at'     => now(),
        ], 'id = ?', [$invoice['id']]);

        $this->db()->insert('fee_payments', [
            'tenant_id'      => $invoice['tenant_id'],
            'school_id'      => $invoice['school_id'],
            'branch_id'      => $invoice['branch_id'],
            'invoice_id'     => $invoice['id'],
            'amount'         => $amount,
            'payment_method' => $method,
            'payment_ref'    => $ref ?: 'Manual-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'paid_at'        => now(),
        ]);

        // Log to timeline
        $student = $this->db()->selectOne("SELECT first_name, last_name FROM students WHERE id = ?", [$invoice['student_id']]);
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
        return $this->redirect('/fees');
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
