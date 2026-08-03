<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Database;
use App\Models\ActivityLog;

class SettingsController extends Controller
{
    private function getCommonData(): array
    {
        $db = Application::$app->db;
        $tenantId = Database::getTenantId();
        $schoolId = Database::getSchoolId();

        $tenant = $db->selectOne("SELECT * FROM tenants WHERE id = ? LIMIT 1", [$tenantId]);
        $school = $db->selectOne("SELECT * FROM schools WHERE id = ? LIMIT 1", [$schoolId]);

        $sysSettingsRaw = $db->select("SELECT * FROM system_settings");
        $systemSettings = [];
        foreach ($sysSettingsRaw as $row) {
            $systemSettings[$row['key']] = $row['value'];
        }

        return compact('tenant', 'school', 'systemSettings');
    }

    public function index(): string
    {
        return $this->view('settings/general', $this->getCommonData());
    }

    public function school(): string
    {
        return $this->view('settings/school', $this->getCommonData());
    }

    public function integrations(): string
    {
        return $this->view('settings/integrations', $this->getCommonData());
    }

    public function system(): string
    {
        return $this->view('settings/system', $this->getCommonData());
    }

    public function update(): string
    {
        $db = Application::$app->db;
        $tenantId = Database::getTenantId();
        $schoolId = Database::getSchoolId();

        $data = $this->request->getBody();

        // Get existing tenant to retrieve current settings & logo
        $tenant = $db->selectOne("SELECT settings, logo FROM tenants WHERE id = ? LIMIT 1", [$tenantId]);
        $settings = json_decode($tenant['settings'] ?? '{}', true) ?: [];

        // Handle logo upload
        $logoName = $tenant['logo'] ?? null;
        $logoFile = $this->request->file('receipt_logo');
        if ($logoFile && $logoFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = STORAGE_PATH . '/uploads/logo';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $logoName = bin2hex(random_bytes(16)) . '.' . pathinfo($logoFile['name'], PATHINFO_EXTENSION);
            move_uploaded_file($logoFile['tmp_name'], $uploadDir . '/' . $logoName);
        }

        // Update Receipt Settings
        $settings['receipt'] = [
            'header_title' => $data['receipt_header_title'] ?? 'Official Fee Payment Receipt',
            'footer_notes' => $data['receipt_footer_notes'] ?? 'This receipt is automatically generated and serves as official proof of payment.',
            'show_watermark' => isset($data['receipt_show_watermark']) ? (int)$data['receipt_show_watermark'] : 1,
            'accent_color' => $data['receipt_accent_color'] ?? '#6366f1',
        ];

        // Update Tenant Info
        if (isset($data['tenant_name'])) {
            $tenantData = [
                'name'     => $data['tenant_name'] ?? '',
                'email'    => $data['tenant_email'] ?? '',
                'phone'    => $data['tenant_phone'] ?? '',
                'country'  => $data['tenant_country'] ?? 'India',
                'timezone' => $data['tenant_timezone'] ?? 'Asia/Kolkata',
                'logo'     => $logoName,
                'settings' => json_encode($settings),
            ];
            $db->update('tenants', $tenantData, 'id = ?', [$tenantId]);
        }

        // Update School Info
        if (isset($data['school_name'])) {
            $schoolData = [
                'name'             => $data['school_name'] ?? '',
                'email'            => $data['school_email'] ?? '',
                'phone'            => $data['school_phone'] ?? '',
                'address'          => $data['school_address'] ?? '',
                'city'             => $data['school_city'] ?? '',
                'state'            => $data['school_state'] ?? '',
                'pincode'          => $data['school_pincode'] ?? '',
                'established_year' => !empty($data['school_established_year']) ? (int)$data['school_established_year'] : null,
                'type'             => $data['school_type'] ?? 'special_needs',
            ];
            $db->update('schools', $schoolData, 'id = ?', [$schoolId]);
        }

        // Process System Settings
        $sysKeys = [
            'whatsapp_api_key', 'whatsapp_enabled',
            'email_host', 'email_port', 'email_user', 'email_pass',
            'payment_razorpay_key', 'payment_stripe_key',
            'academic_grading_scale', 'academic_report_template',
            'portal_hide_exams', 'portal_allow_payments',
            'hr_leave_quota', 'hr_payroll_date',
            'transport_gps_interval', 'transport_delay_threshold',
            'security_password_policy', 'security_timeout'
        ];

        foreach ($sysKeys as $key) {
            if (isset($data[$key])) {
                $exists = $db->selectOne("SELECT id FROM system_settings WHERE `key` = ?", [$key]);
                if ($exists) {
                    $db->update('system_settings', ['value' => $data[$key]], 'id = ?', [$exists['id']]);
                } else {
                    $db->insert('system_settings', ['key' => $key, 'value' => $data[$key]]);
                }
            }
        }

        ActivityLog::log('settings_updated', auth_id(), ['tenant_id' => $tenantId, 'school_id' => $schoolId]);
        
        $this->flash('success', 'Settings updated successfully.');
        
        $redirectUrl = $data['redirect_tab'] ?? '/settings';
        return $this->redirect($redirectUrl);
    }

    public function testWhatsApp(): string
    {
        $phone = $this->request->post('phone');
        $message = "Test message from PSNF ERP Settings.";
        $redirectUrl = $this->request->post('redirect_tab') ?: '/settings/integrations';
        
        if (empty($phone)) {
            $this->flash('error', 'Phone number is required.');
            return $this->redirect($redirectUrl);
        }

        try {
            $result = \App\Services\NotificationService::sendWhatsApp($phone, $message);
            if ($result) {
                $this->flash('success', 'Test message sent successfully! Check your phone.');
            } else {
                $this->flash('error', 'Failed to send test message.');
            }
        } catch (\Throwable $e) {
            $this->flash('error', 'WhatsApp Error: ' . $e->getMessage());
        }

        return $this->redirect($redirectUrl);
    }
}
