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
                -- Outstanding = what is still due on every open invoice (amount - discount - paid).
                -- (Summing student_ledgers.balance added up running balances, not the current due.)
                COALESCE(SUM(CASE WHEN status NOT IN ('paid', 'void')
                                  THEN GREATEST(amount - COALESCE(discount_amount, 0) - paid_amount, 0) END), 0) AS total_outstanding,
                COUNT(CASE WHEN status NOT IN ('paid', 'void') AND due_date < CURRENT_DATE THEN 1 END) AS overdue_count
            FROM fee_invoices
            WHERE tenant_id = ?
        ", [$tenantId, $tenantId, $tenantId]);

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
            return $this->redirect(url('fees/create'));
        }

        if (!is_numeric($data['amount']) || (float)$data['amount'] <= 0) {
            Session::flash('errors', ['amount' => ['Amount must be greater than zero.']]);
            Session::flash('old', $data);
            return $this->redirect(url('fees/create'));
        }

        // Student must belong to the current tenant
        $studentRow = $this->db()->selectOne(
            "SELECT id, school_id, branch_id FROM students WHERE id = ? AND tenant_id = ? AND deleted_at IS NULL",
            [(int)$data['student_id'], \Core\Database::getTenantId()]
        );
        if (!$studentRow) {
            Session::flash('errors', ['student_id' => ['Please select a valid student.']]);
            Session::flash('old', $data);
            return $this->redirect(url('fees/create'));
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
        return $this->redirect(url('fees'));
    }

    /** Only same-site return URLs are honoured (prevents open redirects through return_url). */
    private function safeReturnUrl(?string $url): string
    {
        $url = trim((string)$url);
        if ($url === '' || str_starts_with($url, '//')) {
            return '/fees';
        }
        $parts = parse_url($url);
        if ($parts === false) {
            return '/fees';
        }
        if (isset($parts['host'])) {
            $host = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            $current = (string)($_SERVER['HTTP_HOST'] ?? '');
            if (strcasecmp($host, $current) !== 0 && strcasecmp($parts['host'], $current) !== 0) {
                return '/fees';
            }
            return $url;
        }
        return str_starts_with($url, '/') ? $url : '/fees';
    }

    private function lastLedgerBalance(int $studentId): float
    {
        return (float)($this->db()->selectOne(
            "SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1 FOR UPDATE",
            [$studentId]
        )['balance'] ?? 0);
    }

    public function recordPayment(string $id): string
    {
        $data      = $this->request->getBody();
        $returnUrl = $this->safeReturnUrl($data['return_url'] ?? '/fees');
        $tenantId  = \Core\Database::getTenantId();

        $rawAmount = $data['amount'] ?? '';
        $amount    = is_numeric($rawAmount) ? round((float)$rawAmount, 2) : 0.0;
        $method    = trim((string)($data['payment_method'] ?? 'Cash')) ?: 'Cash';
        $ref       = trim((string)($data['payment_ref'] ?? ''));

        if ($amount <= 0) {
            Session::flash('error', 'Payment amount must be greater than zero.');
            return $this->redirect($returnUrl);
        }

        $db = $this->db();
        try {
            $invoice = $db->transaction(function ($db) use ($id, $tenantId, $amount, $method, $ref) {
                // Lock the invoice row so concurrent payments cannot over-collect
                $invoice = $db->selectOne(
                    "SELECT * FROM fee_invoices WHERE id = ? AND tenant_id = ? FOR UPDATE",
                    [(int)$id, $tenantId]
                );
                if (!$invoice) {
                    throw new \DomainException('Invoice not found.');
                }
                if (in_array($invoice['status'], ['paid', 'void', 'cancelled'], true)) {
                    throw new \DomainException('This invoice is ' . $invoice['status'] . ' and cannot accept payments.');
                }

                $net = round((float)$invoice['amount'] - (float)($invoice['discount_amount'] ?? 0), 2);
                $due = round($net - (float)$invoice['paid_amount'], 2);
                if ($due <= 0) {
                    throw new \DomainException('Nothing is due on this invoice.');
                }
                if ($amount > $due) {
                    throw new \DomainException('Amount exceeds the balance due (' . number_format($due, 2) . ').');
                }

                $newPaidAmount = round((float)$invoice['paid_amount'] + $amount, 2);
                $status = $newPaidAmount >= $net ? 'paid' : 'partially_paid';

                $db->update('fee_invoices', [
                    'status'      => $status,
                    'paid_amount' => $newPaidAmount,
                    'paid_at'     => now(),
                ], 'id = ?', [$invoice['id']]);

                $paymentId = $db->insert('fee_payments', [
                    'tenant_id'      => $invoice['tenant_id'],
                    'school_id'      => $invoice['school_id'],
                    'branch_id'      => $invoice['branch_id'],
                    'invoice_id'     => $invoice['id'],
                    'amount'         => $amount,
                    'payment_method' => $method,
                    'payment_ref'    => $ref ?: 'Manual-' . strtoupper(substr(md5(uniqid('', true)), 0, 8)),
                    'paid_at'        => now(),
                    'payment_source' => 'staff',
                ]);

                // Record to Student Ledger
                $newBalance = $this->lastLedgerBalance((int)$invoice['student_id']) - $amount;
                $db->insert('student_ledgers', [
                    'student_id'   => $invoice['student_id'],
                    'entry_type'   => 'payment',
                    'debit'        => 0.00,
                    'credit'       => $amount,
                    'balance'      => $newBalance,
                    'reference_id' => $paymentId,
                    'description'  => mb_substr("Payment: Received {$amount} via {$method} for '{$invoice['title']}'", 0, 255),
                ]);

                $db->insert('student_timeline', [
                    'student_id'  => $invoice['student_id'],
                    'event_type'  => 'fee_payment',
                    'title'       => 'Fee Payment Logged',
                    'description' => "Logged manual payment of {$amount} INR for '{$invoice['title']}' via {$method}.",
                    'color'       => 'green',
                    'icon'        => 'cash',
                    'actor_name'  => auth()['name'] ?? 'Staff',
                    'occurred_at' => now(),
                ]);

                return $invoice;
            });
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            return $this->redirect($returnUrl);
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to record payment: ' . $e->getMessage());
            return $this->redirect($returnUrl);
        }

        // WhatsApp Notification (outside the transaction; a failure must not undo the payment)
        try {
            $student = $db->selectOne("
                SELECT s.*, p.whatsapp_number as phone, p.first_name as parent_name
                FROM students s
                LEFT JOIN parent_student ps ON s.id = ps.student_id
                LEFT JOIN parents p ON ps.parent_id = p.id
                WHERE s.id = ?
            ", [$invoice['student_id']]);
            \App\Services\NotificationService::notifyPaymentReceived($student, $invoice, $amount);
        } catch (\Throwable $e) {
            error_log('Payment notification failed: ' . $e->getMessage());
        }

        ActivityLog::log('fee_payment_recorded', auth_id(), ['invoice_id' => $invoice['id'], 'amount' => $amount]);
        Session::flash('success', 'Payment recorded successfully.');
        return $this->redirect($returnUrl);
    }

    /**
     * Delete an invoice. Its payments are deleted too, and the student ledger gets reversing
     * entries (refund debit for every posted payment credit, adjustment credit for the invoice
     * debit) so the running balance stays correct. Everything happens in one transaction.
     */
    public function destroy(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        try {
            $deleted = $this->db()->transaction(function ($db) use ($id, $tenantId) {
                $invoice = $db->selectOne("SELECT * FROM fee_invoices WHERE id = ? AND tenant_id = ? FOR UPDATE", [(int)$id, $tenantId]);
                if (!$invoice) {
                    return null;
                }
                $studentId = (int)$invoice['student_id'];
                $payments  = $db->select("SELECT id, amount FROM fee_payments WHERE invoice_id = ?", [$invoice['id']]);

                // Only reverse what was actually posted to the ledger for this invoice
                $invoiceDebit = (float)($db->selectOne(
                    "SELECT COALESCE(SUM(debit), 0) AS d FROM student_ledgers WHERE student_id = ? AND entry_type = 'invoice' AND reference_id = ?",
                    [$studentId, $invoice['id']]
                )['d'] ?? 0);

                $balance = $this->lastLedgerBalance($studentId);

                foreach ($payments as $p) {
                    $posted = (float)($db->selectOne(
                        "SELECT COALESCE(SUM(credit), 0) AS c FROM student_ledgers WHERE student_id = ? AND entry_type = 'payment' AND reference_id = ?",
                        [$studentId, $p['id']]
                    )['c'] ?? 0);
                    if ($posted > 0) {
                        $balance += $posted;
                        $db->insert('student_ledgers', [
                            'student_id'   => $studentId,
                            'entry_type'   => 'refund',
                            'debit'        => $posted,
                            'credit'       => 0.00,
                            'balance'      => $balance,
                            'reference_id' => $p['id'],
                            'description'  => mb_substr("Reversal of payment #{$p['id']} (invoice {$invoice['invoice_number']} deleted)", 0, 255),
                        ]);
                    }
                }

                if ($invoiceDebit > 0) {
                    $balance -= $invoiceDebit;
                    $db->insert('student_ledgers', [
                        'student_id'   => $studentId,
                        'entry_type'   => 'adjustment',
                        'debit'        => 0.00,
                        'credit'       => $invoiceDebit,
                        'balance'      => $balance,
                        'reference_id' => $invoice['id'],
                        'description'  => mb_substr("Reversal of invoice {$invoice['invoice_number']} (deleted)", 0, 255),
                    ]);
                }

                $db->query("DELETE FROM fee_payments WHERE invoice_id = ?", [$invoice['id']]);
                $db->query("DELETE FROM fee_invoices WHERE id = ?", [$invoice['id']]);
                return $invoice;
            });
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to delete invoice: ' . $e->getMessage());
            return $this->redirect(url('fees'));
        }

        if ($deleted) {
            ActivityLog::log('fee_invoice_deleted', auth_id(), ['invoice_id' => (int)$id, 'invoice_number' => $deleted['invoice_number']]);
            Session::flash('success', 'Invoice deleted and ledger reversed.');
        } else {
            Session::flash('error', 'Invoice not found.');
        }

        return $this->redirect(url('fees'));
    }
}
