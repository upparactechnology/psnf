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
        $search = $this->request->get('search', '');

        $where = ["fp.tenant_id = ?"];
        $params = [$tenantId];

        if ($search) {
            $cleanedSearch = trim($search);
            if (preg_match('/^rec-0*(\d+)$/i', $cleanedSearch, $matches)) {
                $receiptId = (int)$matches[1];
                $where[] = "(fp.id = ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR fi.invoice_number LIKE ? OR fp.payment_ref LIKE ?)";
                array_push($params, $receiptId, "%$search%", "%$search%", "%$search%", "%$search%");
            } else {
                $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR fi.invoice_number LIKE ? OR fp.payment_ref LIKE ? OR fp.id LIKE ?)";
                array_push($params, "%$search%", "%$search%", "%$search%", "%$search%", "%$search%");
            }
        }

        $whereClause = implode(" AND ", $where);

        // Fetch fee payment records along with invoice and student names matching search
        $payments = $db->select(
            "SELECT fp.*, fi.invoice_number, fi.title as invoice_title, s.first_name, s.last_name, s.admission_number 
             FROM fee_payments fp
             JOIN fee_invoices fi ON fp.invoice_id = fi.id
             JOIN students s ON fi.student_id = s.id
             WHERE $whereClause
             ORDER BY fp.paid_at DESC, fp.created_at DESC",
            $params
        );

        return $this->view('receipts/index', compact('payments', 'search'));
    }

    public function show(string $id): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        // Fetch single payment detail for printable voucher view along with tenant custom logo and settings
        $payment = $db->selectOne(
            "SELECT fp.*, fi.invoice_number, fi.title as invoice_title, fi.description as invoice_desc, 
                    s.first_name, s.last_name, s.admission_number, s.class, s.section, 
                    t.name as tenant_name, t.logo as tenant_logo, t.settings as tenant_settings
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

    public function settings(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();

        $tenant = $db->selectOne("SELECT name, logo, settings FROM tenants WHERE id = ? LIMIT 1", [$tenantId]);

        return $this->view('receipts/settings', compact('tenant'));
    }

    public function saveSettings(): string
    {
        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $data = $this->request->getBody();

        // Get existing tenant
        $tenant = $db->selectOne("SELECT settings FROM tenants WHERE id = ? LIMIT 1", [$tenantId]);
        $settings = json_decode($tenant['settings'] ?? '{}', true) ?: [];

        // Handle background image upload
        $bgName = $settings['receipt']['bg_image'] ?? null;
        $bgFile = $this->request->file('receipt_bg');
        if ($bgFile && $bgFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = STORAGE_PATH . '/uploads/receipt_templates';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $bgName = bin2hex(random_bytes(16)) . '.' . pathinfo($bgFile['name'], PATHINFO_EXTENSION);
            move_uploaded_file($bgFile['tmp_name'], $uploadDir . '/' . $bgName);
        }

        $mappings = json_decode($data['mappings_json'] ?? '[]', true) ?: [];

        // Update Receipt Settings
        $settings['receipt'] = [
            'header_title'   => $data['receipt_header_title'] ?? 'Official Fee Payment Receipt',
            'footer_notes'   => $data['receipt_footer_notes'] ?? 'This receipt is automatically generated and serves as official proof of payment.',
            'show_watermark' => isset($data['receipt_show_watermark']) ? (int)$data['receipt_show_watermark'] : 1,
            'accent_color'   => $data['receipt_accent_color'] ?? '#6366f1',
            'font_family'    => $data['receipt_font_family'] ?? 'Inter',
            'bg_image'       => $bgName,
            'mappings'       => $mappings,
        ];

        // Update Tenant Table
        $db->update('tenants', [
            'settings' => json_encode($settings),
        ], 'id = ?', [$tenantId]);

        \App\Models\ActivityLog::log('receipt_settings_updated', auth_id(), ['tenant_id' => $tenantId]);

        \Core\Session::flash('success', 'Receipt designer template saved successfully.');
        return $this->redirect(url('receipts/settings'));
    }
}
