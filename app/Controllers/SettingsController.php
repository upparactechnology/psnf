<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Database;
use App\Models\ActivityLog;

class SettingsController extends Controller
{
    public function index(): string
    {
        $db = Application::$app->db;
        $tenantId = Database::getTenantId();
        $schoolId = Database::getSchoolId();

        $tenant = $db->selectOne("SELECT * FROM tenants WHERE id = ? LIMIT 1", [$tenantId]);
        $school = $db->selectOne("SELECT * FROM schools WHERE id = ? LIMIT 1", [$schoolId]);

        return $this->view('settings/index', compact('tenant', 'school'));
    }

    public function update(): string
    {
        $db = Application::$app->db;
        $tenantId = Database::getTenantId();
        $schoolId = Database::getSchoolId();

        $data = $this->request->getBody();

        // Update Tenant Info
        $tenantData = [
            'name'     => $data['tenant_name'] ?? '',
            'email'    => $data['tenant_email'] ?? '',
            'phone'    => $data['tenant_phone'] ?? '',
            'country'  => $data['tenant_country'] ?? 'India',
            'timezone' => $data['tenant_timezone'] ?? 'Asia/Kolkata',
        ];
        $db->update('tenants', $tenantData, 'id = ?', [$tenantId]);

        // Update School Info
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

        ActivityLog::log('settings_updated', auth_id(), ['tenant_id' => $tenantId, 'school_id' => $schoolId]);
        
        $this->flash('success', 'Settings updated successfully.');
        return $this->redirect('/settings');
    }
}
