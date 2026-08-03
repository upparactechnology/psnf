<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;

class BatchFeeGeneratorController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function index(): string
    {
        $structures = $this->db()->select("
            SELECT fs.*, ay.year_name, mg.name as main_group_name
            FROM fee_structures fs
            LEFT JOIN academic_years ay ON fs.academic_year_id = ay.id
            LEFT JOIN main_groups mg ON fs.main_group_id = mg.id
            WHERE fs.is_active = 1
            ORDER BY fs.id DESC
        ");
        
        $mainGroups = $this->db()->select("SELECT * FROM main_groups ORDER BY name ASC");
        
        return $this->view('fees/batch_generator', compact('structures', 'mainGroups'));
    }

    public function generate(): string
    {
        $data = $this->request->getBody();
        
        $structureId = (int)($data['fee_structure_id'] ?? 0);
        $mainGroupId = (int)($data['main_group_id'] ?? 0);
        
        if (!$structureId || !$mainGroupId) {
            Session::flash('error', 'Structure and Main Group are required.');
            return $this->redirect('/fees/batch-generator');
        }

        $structure = $this->db()->selectOne("SELECT * FROM fee_structures WHERE id = ?", [$structureId]);
        if (!$structure) {
            Session::flash('error', 'Invalid structure.');
            return $this->redirect('/fees/batch-generator');
        }
        
        $items = $this->db()->select("
            SELECT fsi.*, fc.name as category_name
            FROM fee_structure_items fsi
            JOIN fee_categories fc ON fsi.fee_category_id = fc.id
            WHERE fsi.fee_structure_id = ?
        ", [$structureId]);
        
        if (empty($items)) {
            Session::flash('error', 'Structure has no fee items.');
            return $this->redirect('/fees/batch-generator');
        }

        // Get students in the main group
        $students = $this->db()->select("
            SELECT id, tenant_id, school_id, branch_id
            FROM students
            WHERE main_group_id = ? AND is_active = 1 AND deleted_at IS NULL
        ", [$mainGroupId]);
        
        if (empty($students)) {
            Session::flash('warning', 'No active students found in this Main Group.');
            return $this->redirect('/fees/batch-generator');
        }
        
        $totalAmount = array_sum(array_column($items, 'amount'));
        $title = $data['invoice_title'] ?: ($structure['name'] . ' Invoice');
        $dueDate = $data['due_date'] ?: date('Y-m-d', strtotime('+15 days'));
        
        $generatedCount = 0;
        
        foreach ($students as $student) {
            // Create Invoice
            $invoiceId = $this->db()->insert('fee_invoices', [
                'tenant_id' => $student['tenant_id'],
                'school_id' => $student['school_id'],
                'branch_id' => $student['branch_id'],
                'student_id' => $student['id'],
                'title' => $title,
                'amount' => $totalAmount,
                'due_date' => $dueDate,
                'status' => 'unpaid',
                'created_at' => now(),
            ]);
            
            // Note: the individual line items could be saved in a 'fee_invoice_items' table if it existed, 
            // but currently PSNF lumps it into 'amount'. For future expansion we would link items.
            
            // Trigger WhatsApp Notification
            \App\Services\NotificationService::notifyInvoiceCreated($student, ['title' => $title, 'amount' => $totalAmount, 'due_date' => $dueDate]);

            // Dual-Write: Ledger Debit
            $currentBalance = (float)($this->db()->selectOne("SELECT balance FROM student_ledgers WHERE student_id = ? ORDER BY id DESC LIMIT 1", [$student['id']])['balance'] ?? 0);
            $newBalance = $currentBalance + $totalAmount;
            
            $this->db()->insert('student_ledgers', [
                'student_id'   => $student['id'],
                'entry_type'   => 'invoice',
                'debit'        => $totalAmount,
                'credit'       => 0.00,
                'balance'      => $newBalance,
                'reference_id' => $invoiceId,
                'description'  => "Batch Generated Invoice: {$title}"
            ]);
            
            $generatedCount++;
        }
        
        Session::flash('success', "Successfully generated {$generatedCount} invoices.");
        return $this->redirect('/fees/batch-generator');
    }
}
