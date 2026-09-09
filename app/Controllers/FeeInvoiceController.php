<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class FeeInvoiceController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $tenantId = \Core\Database::getTenantId();
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $query = "
            SELECT i.*, s.first_name, s.last_name, s.admission_number 
            FROM fee_invoices i
            JOIN students s ON i.student_id = s.id
            WHERE i.tenant_id = ? 
        ";
        $params = [$tenantId];

        if ($search) {
            $query .= " AND (i.invoice_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ? OR i.title LIKE ?)";
            $searchTerm = "%{$search}%";
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        if ($status) {
            $query .= " AND i.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY i.created_at DESC";

        $invoices = $this->db()->select($query, $params);

        return $this->view('fees/invoices/index', compact('invoices', 'search', 'status'));
    }

    public function create(): string
    {
        $tenantId = \Core\Database::getTenantId();
        $students = $this->db()->select("
            SELECT id, first_name, last_name, admission_number 
            FROM students 
            WHERE tenant_id = ? AND deleted_at IS NULL AND is_active = 1
            ORDER BY first_name ASC
        ", [$tenantId]);

        return $this->view('fees/invoices/create', compact('students'));
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
            return $this->redirect(url('fees/invoices/create'));
        }

        $tenantId = \Core\Database::getTenantId();
        
        // Generate invoice number
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        $invoiceNumber = "INV-{$year}-{$random}";
        
        $amount = (float)$data['amount'];

        $id = $this->db()->insert('fee_invoices', [
            'student_id'     => $data['student_id'],
            'title'          => $data['title'],
            'description'    => $data['description'] ?? null,
            'amount'         => $amount,
            'due_date'       => $data['due_date'],
            'invoice_number' => $invoiceNumber,
            'tenant_id'      => $tenantId,
            'school_id'      => \Core\Database::getSchoolId() ?: 1,
            'branch_id'      => \Core\Database::getBranchId() ?: 1,
            'paid_amount'    => 0.00,
            'status'         => 'unpaid',
            'created_at'     => date('Y-m-d H:i:s'),
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

        // Send Notification if service exists
        if (class_exists('\App\Services\NotificationService')) {
            $student = $this->db()->selectOne("SELECT s.*, p.whatsapp_number, p.first_name as parent_name FROM students s LEFT JOIN parent_student ps ON s.id = ps.student_id LEFT JOIN parents p ON ps.parent_id = p.id WHERE s.id = ?", [$data['student_id']]);
            if ($student && !empty($student['whatsapp_number'])) {
                $notifier = new \App\Services\NotificationService();
                $msg = "Hello {$student['parent_name']}, a new fee invoice ({$invoiceNumber}) of Rs.{$amount} has been generated for {$student['first_name']}. Due Date: " . date('d M Y', strtotime($data['due_date'])) . ". Please pay timely to avoid late fees.";
                $notifier->sendWhatsApp($student['whatsapp_number'], $msg);
            }
        }

        Session::flash('success', "Invoice {$invoiceNumber} created successfully.");
        return $this->redirect(url('fees/invoices'));
    }

    public function show(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        
        $invoice = $this->db()->selectOne("
            SELECT i.*, s.first_name, s.last_name, s.admission_number, s.roll_number,
                   c.name as class_name, c.section as section_name, mg.name as main_group_name
            FROM fee_invoices i
            JOIN students s ON i.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN main_groups mg ON s.main_group_id = mg.id
            WHERE i.id = ? AND i.tenant_id = ?
        ", [(int)$id, $tenantId]);

        if (!$invoice) {
            Session::flash('error', 'Invoice not found.');
            return $this->redirect(url('fees/invoices'));
        }

        // Fetch payments for this invoice
        $payments = $this->db()->select("
            SELECT * FROM fee_payments 
            WHERE invoice_id = ? AND tenant_id = ?
            ORDER BY paid_at DESC
        ", [(int)$id, $tenantId]);

        return $this->view('fees/invoices/show', compact('invoice', 'payments'));
    }

    public function edit(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        
        $invoice = $this->db()->selectOne("
            SELECT i.*, s.first_name, s.last_name, s.admission_number 
            FROM fee_invoices i
            JOIN students s ON i.student_id = s.id
            WHERE i.id = ? AND i.tenant_id = ?
        ", [(int)$id, $tenantId]);

        if (!$invoice) {
            Session::flash('error', 'Invoice not found.');
            return $this->redirect(url('fees/invoices'));
        }

        if ($invoice['status'] !== 'unpaid' || $invoice['paid_amount'] > 0) {
            Session::flash('error', 'Only unpaid invoices can be edited.');
            return $this->redirect(url('fees/invoices'));
        }

        return $this->view('fees/invoices/edit', compact('invoice'));
    }

    public function update(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        
        $invoice = $this->db()->selectOne("SELECT * FROM fee_invoices WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$invoice || $invoice['status'] !== 'unpaid' || $invoice['paid_amount'] > 0) {
            Session::flash('error', 'Invalid invoice or invoice cannot be edited.');
            return $this->redirect(url('fees/invoices'));
        }

        $data = $this->request->getBody();
        $rules = [
            'title'       => 'required|min:3',
            'amount'      => 'required|numeric',
            'due_date'    => 'required|date',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            return $this->redirect(url("fees/invoices/{$id}/edit"));
        }

        $newAmount = (float)$data['amount'];
        $oldAmount = (float)$invoice['amount'];
        $difference = $newAmount - $oldAmount;

        $this->db()->update('fee_invoices', [
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'amount'      => $newAmount,
            'due_date'    => $data['due_date'],
            'updated_at'  => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int)$id]);

        // Adjust ledger if amount changed
        if ($difference != 0) {
            $currentBalance = (float)($this->db()->selectOne("SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$invoice['student_id']])['balance'] ?? 0);
            $newBalance = $currentBalance + $difference;
            
            // If difference is positive, it's an extra debit. If negative, it acts like a credit.
            $debit = $difference > 0 ? $difference : 0;
            $credit = $difference < 0 ? abs($difference) : 0;
            
            $this->db()->insert('student_ledgers', [
                'student_id'   => $invoice['student_id'],
                'entry_type'   => 'adjustment',
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $newBalance,
                'reference_id' => $invoice['id'],
                'description'  => "Invoice Adjustment ({$invoice['invoice_number']})"
            ]);
        }

        Session::flash('success', "Invoice updated successfully.");
        return $this->redirect(url('fees/invoices'));
    }

    public function destroy(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        
        $invoice = $this->db()->selectOne("SELECT * FROM fee_invoices WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$invoice || $invoice['status'] !== 'unpaid' || $invoice['paid_amount'] > 0) {
            Session::flash('error', 'Only unpaid invoices can be deleted.');
            return $this->redirect(url('fees/invoices'));
        }

        // Delete the invoice
        $this->db()->query("DELETE FROM fee_invoices WHERE id = ?", [(int)$id]);
        
        // Adjust ledger
        $amount = (float)$invoice['amount'];
        $currentBalance = (float)($this->db()->selectOne("SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$invoice['student_id']])['balance'] ?? 0);
        $newBalance = $currentBalance - $amount; // Remove the debit
        
        $this->db()->insert('student_ledgers', [
            'student_id'   => $invoice['student_id'],
            'entry_type'   => 'adjustment',
            'debit'        => 0,
            'credit'       => $amount, // Credit back the deleted invoice amount
            'balance'      => $newBalance,
            'reference_id' => $invoice['id'],
            'description'  => "Invoice Deleted ({$invoice['invoice_number']})"
        ]);

        Session::flash('success', "Invoice deleted successfully.");
        return $this->redirect(url('fees/invoices'));
    }
}
