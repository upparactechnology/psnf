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

    public function index(): string
    {
        $policies = $this->db()->select("SELECT * FROM late_fee_policies ORDER BY id DESC");
        return $this->view('fees/late_fee_policies', compact('policies'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'amount' => 'required'
        ];
        
        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('error', 'Name, Type, and Amount are required.');
            return $this->redirect('/fees/late-fee-policies');
        }

        // Map form types to database ENUM values
        $typeMapping = [
            'fixed' => 'one_time',
            'percentage' => 'percentage',
            'daily' => 'fixed_day'
        ];
        
        $ruleType = $typeMapping[$data['type']] ?? 'one_time';

        $this->db()->insert('late_fee_policies', [
            'name' => $data['name'],
            'rule_type' => $ruleType,
            'value' => (float)$data['amount'],
            'grace_days' => (int)($data['grace_days'] ?? 0),
            'max_cap' => (float)($data['max_amount'] ?? 0.00),
        ]);
        
        Session::flash('success', 'Late fee policy created successfully.');
        return $this->redirect('/fees/late-fee-policies');
    }

    public function update(string $id): string
    {
        $data = $this->request->getBody();
        
        $typeMapping = [
            'fixed' => 'one_time',
            'percentage' => 'percentage',
            'daily' => 'fixed_day'
        ];
        
        $ruleType = $typeMapping[$data['type']] ?? 'one_time';
        
        $this->db()->update('late_fee_policies', [
            'name' => $data['name'],
            'rule_type' => $ruleType,
            'value' => (float)$data['amount'],
            'grace_days' => (int)($data['grace_days'] ?? 0),
            'max_cap' => (float)($data['max_amount'] ?? 0.00)
        ], 'id = ?', [(int)$id]);
        
        Session::flash('success', 'Late fee policy updated successfully.');
        return $this->redirect('/fees/late-fee-policies');
    }

    public function destroy(string $id): string
    {
        // Simple delete for now. In real-world, we'd check if it's attached to structures.
        $this->db()->query("DELETE FROM late_fee_policies WHERE id = ?", [(int)$id]);
        Session::flash('success', 'Late fee policy deleted.');
        
        return $this->redirect('/fees/late-fee-policies');
    }
}
