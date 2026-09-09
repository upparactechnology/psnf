<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class LateFeePolicyController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    private function ensureColumns(): void
    {
        try { $this->db()->query("ALTER TABLE `late_fee_policies` ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1"); } catch (\Throwable $e) {}
        try { $this->db()->query("ALTER TABLE `late_fee_policies` ADD COLUMN `academic_year_id` INT UNSIGNED NULL"); } catch (\Throwable $e) {}
    }

    public function index(): string
    {
        $this->ensureColumns();
        $tenantId = \Core\Database::getTenantId();

        $years = $this->db()->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);
        $selectedYearId = (int) $this->request->get('year_id', 0);
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) { if ($y['status'] === 'current') { $selectedYearId = (int) $y['id']; break; } }
            if (!$selectedYearId) $selectedYearId = (int) $years[0]['id'];
        }

        $policies = $this->db()->select("SELECT * FROM late_fee_policies WHERE tenant_id = ? AND academic_year_id = ? ORDER BY id DESC", [$tenantId, $selectedYearId]);

        return $this->view('fees/late_fee_policies', compact('policies', 'years', 'selectedYearId'));
    }

    public function store(): string
    {
        $this->ensureColumns();
        $data = $this->request->getBody();
        $tenantId = \Core\Database::getTenantId();
        
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'amount' => 'required'
        ];
        
        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('error', 'Name, Type, and Amount are required.');
            return $this->redirect(url('fees/late-fee-policies'));
        }

        $typeMapping = [
            'fixed' => 'one_time',
            'percentage' => 'percentage',
            'daily' => 'fixed_day'
        ];
        
        $ruleType = $typeMapping[$data['type']] ?? 'one_time';
        $yearId = (int) ($data['academic_year_id'] ?? 0);

        $this->db()->insert('late_fee_policies', [
            'name' => $data['name'],
            'rule_type' => $ruleType,
            'value' => (float)$data['amount'],
            'grace_days' => (int)($data['grace_days'] ?? 0),
            'max_cap' => (float)($data['max_amount'] ?? 0.00),
            'tenant_id' => $tenantId,
            'academic_year_id' => $yearId ?: null,
        ]);
        
        Session::flash('success', 'Late fee policy created successfully.');
        return $this->redirect(url('fees/late-fee-policies?year_id=' . $yearId));
    }

    public function update(string $id): string
    {
        $this->ensureColumns();
        $data = $this->request->getBody();
        $tenantId = \Core\Database::getTenantId();
        
        $typeMapping = [
            'fixed' => 'one_time',
            'percentage' => 'percentage',
            'daily' => 'fixed_day'
        ];
        
        $ruleType = $typeMapping[$data['type']] ?? 'one_time';
        $yearId = (int) ($data['academic_year_id'] ?? 0);
        
        $this->db()->update('late_fee_policies', [
            'name' => $data['name'],
            'rule_type' => $ruleType,
            'value' => (float)$data['amount'],
            'grace_days' => (int)($data['grace_days'] ?? 0),
            'max_cap' => (float)($data['max_amount'] ?? 0.00)
        ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);
        
        Session::flash('success', 'Late fee policy updated successfully.');
        return $this->redirect(url('fees/late-fee-policies?year_id=' . $yearId));
    }

    public function destroy(string $id): string
    {
        $this->ensureColumns();
        $tenantId = \Core\Database::getTenantId();
        $yearId = (int) $this->request->get('year_id', 0);

        $this->db()->query("DELETE FROM late_fee_policies WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        Session::flash('success', 'Late fee policy deleted.');
        
        return $this->redirect(url('fees/late-fee-policies?year_id=' . $yearId));
    }
}
