<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;

class ReceiptsController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Fetch all fee payment records along with invoice and student names
        $payments = $db->select(
            "SELECT fp.*, fi.invoice_number, fi.title as invoice_title, s.first_name, s.last_name, s.admission_number 
             FROM fee_payments fp
             JOIN fee_invoices fi ON fp.invoice_id = fi.id
             JOIN students s ON fi.student_id = s.id
             WHERE fp.tenant_id = ?
             ORDER BY fp.paid_at DESC, fp.created_at DESC",
            [$tenantId]
        );

        return $this->view('receipts/index', compact('payments'));
    }

    public function show(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Fetch single payment detail for printable voucher view
        $payment = $db->selectOne(
            "SELECT fp.*, fi.invoice_number, fi.title as invoice_title, fi.description as invoice_desc, 
                    s.first_name, s.last_name, s.admission_number, s.class, s.section, t.name as tenant_name 
             FROM fee_payments fp
             JOIN fee_invoices fi ON fp.invoice_id = fi.id
             JOIN students s ON fi.student_id = s.id
             JOIN tenants t ON fp.tenant_id = t.id
             WHERE fp.id = ? AND fp.tenant_id = ?",
            [(int) $id, $tenantId]
        );

        if (!$payment) {
            $this->response->abort(404);
            exit();
        }

        return $this->view('receipts/show', compact('payment'));
    }
}
