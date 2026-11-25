<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Models\ActivityLog;
use App\Services\PayrollService;
use App\Services\ShiftSettings;

class PayrollController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    protected function authId(): int
    {
        return (int) (Session::get('user')['id'] ?? 1);
    }

    public function runs(): string
    {
        $db = $this->db();
        $runs = $db->select("SELECT * FROM payroll_runs ORDER BY id DESC");
        $employees = $db->select("
            SELECT e.*, d.name as department_name 
            FROM employees e 
            LEFT JOIN departments d ON e.department_id = d.id 
            WHERE e.status = 'active'
        ");

        // Check if any active runs are out of sync due to changes
        foreach ($runs as &$run) {
            if ($run['status'] === 'approved' || $run['status'] === 'draft') {
                if ($this->checkIfOutOfSync((int)$run['id'])) {
                    $db->update('payroll_runs', ['status' => 'out_of_sync'], 'id = ?', [$run['id']]);
                    $this->logAudit('marked_out_of_sync', 'payroll_runs', (int)$run['id'], $run['status'], 'out_of_sync', 'Automatic data drift detection');
                    $run['status'] = 'out_of_sync';
                }
            }
        }
        unset($run);

        return $this->view('payroll/runs', compact('runs', 'employees'));
    }

    public function holidays(): string
    {
        $db = $this->db();
        $holidays = $db->select("SELECT * FROM holidays ORDER BY holiday_date DESC");
        return $this->view('payroll/holidays', compact('holidays'));
    }

    public function storeHoliday(): string
    {
        $db = $this->db();
        $date = trim((string)$this->request->input('holiday_date'));
        $name = trim((string)$this->request->input('name'));
        $type = trim((string)$this->request->input('type', 'public'));
        $isPaid = (int)$this->request->input('is_paid', 1);

        if (!$date || !$name) {
            Session::flash('error', 'Date and Name are required.');
            return $this->redirect(url('payroll/holidays'));
        }

        try {
            $db->insert('holidays', [
                'holiday_date' => $date,
                'name'         => $name,
                'type'         => $type,
                'is_paid'      => $isPaid
            ]);
            $this->logAudit('created', 'holidays', (int)$db->getLastInsertId(), null, $name, 'Holiday added');
            $this->markAffectedRunsOutOfSync($date);
            Session::flash('success', "Holiday '$name' added successfully.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to add holiday: ' . $e->getMessage());
        }

        return $this->redirect(url('payroll/holidays'));
    }

    public function deleteHoliday(string $id): string
    {
        $db = $this->db();
        $holidayId = (int)$id;
        $holiday = $db->selectOne("SELECT * FROM holidays WHERE id = ?", [$holidayId]);

        if ($holiday) {
            $db->delete('holidays', 'id = ?', [$holidayId]);
            $this->logAudit('deleted', 'holidays', $holidayId, $holiday['name'], null, 'Holiday deleted');
            $this->markAffectedRunsOutOfSync($holiday['holiday_date']);
            Session::flash('success', 'Holiday deleted successfully.');
        }

        return $this->redirect(url('payroll/holidays'));
    }

    public function runDetails(string $id): string
    {
        $db = $this->db();
        $runId = (int)$id;
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [$runId]);

        if (!$run) {
            Session::flash('error', 'Payroll run not found.');
            return $this->redirect(url('payroll/runs'));
        }

        $items = $db->select("
            SELECT pi.*, e.first_name, e.last_name, e.emp_code, d.name as department_name, des.title as designation_title
            FROM payroll_items pi
            JOIN employees e ON pi.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            WHERE pi.payroll_run_id = ?
            ORDER BY e.first_name ASC
        ", [$runId]);

        // Get chronological details of late marks and penalties per employee
        $chronology = [];
        foreach ($items as $item) {
            $chronology[$item['employee_id']] = $this->getChronologicalBreakdown($run['month_year'], (int)$item['employee_id'], $item['status_engine_json'] ?? null);
        }

        return $this->view('payroll/payroll_details', compact('run', 'items', 'chronology'));
    }

    public function payslip(string $runId, string $empId): string
    {
        $db = $this->db();
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [(int)$runId]);
        $item = $db->selectOne("
            SELECT pi.*, e.first_name, e.last_name, e.emp_code, e.joining_date, d.name as department_name, des.title as designation_title
            FROM payroll_items pi
            JOIN employees e ON pi.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN designations des ON e.designation_id = des.id
            WHERE pi.payroll_run_id = ? AND pi.employee_id = ?
        ", [(int)$runId, (int)$empId]);

        if (!$run || !$item) {
            Session::flash('error', 'Payslip not found.');
            return $this->redirect(url('payroll/runs'));
        }

        $chronology = $this->getChronologicalBreakdown($run['month_year'], (int)$empId, $item['status_engine_json'] ?? null);

        return $this->view('payroll/payslip', compact('run', 'item', 'chronology'));
    }

    public function exemptLate(): string
    {
        $db = $this->db();
        $logId = (int)$this->request->input('log_id');
        $source = (string) $this->request->input('source', 'kiosk'); // kiosk or manual ('Manual Entry' from the list view)
        $reason = trim((string)$this->request->input('reason', 'Admin Exemption'));
        $exempt = (int)$this->request->input('exempt', 1);

        $table = (stripos($source, 'manual') !== false) ? 'staff_attendance_logs' : 'attendance';
        $log = $db->selectOne("SELECT * FROM `$table` WHERE id = ?", [$logId]);

        if (!$log) {
            return json_encode(['success' => false, 'message' => 'Log record not found.']);
        }

        $db->update($table, [
            'late_exempted'          => $exempt,
            'late_exemption_reason'  => $reason,
            'late_exempted_by'       => $this->authId(),
            'late_exempted_at'       => now()
        ], 'id = ?', [$logId]);

        $empId = (int)($log['employee_id'] ?? 0);
        if ($empId === 0 && !empty($log['user_id'])) {
            $emp = $db->selectOne("SELECT id FROM employees WHERE user_id = ? LIMIT 1", [$log['user_id']]);
            if ($emp) $empId = (int)$emp['id'];
        }

        $this->logAudit(
            $exempt ? 'late_exempted' : 'late_unexempted',
            $table,
            $logId,
            json_encode(['exempted' => 0]),
            json_encode(['exempted' => 1, 'reason' => $reason]),
            $reason
        );

        $date = $log['date'] ?? $log['attendance_date'] ?? (!empty($log['check_in']) ? substr((string)$log['check_in'], 0, 10) : null);
        if ($date) {
            $this->markAffectedRunsOutOfSync($date);
        }

        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Lateness exemption updated.']);
    }

    public function runPayroll(): string
    {
        $targetMonth = (string) $this->request->input('month', date('Y-m'));
        $monthYear = date('F Y', strtotime($targetMonth . '-01'));

        try {
            $runId = (new PayrollService($this->db()))->generateRun($targetMonth, $this->authId());
            $this->logAudit('run_payroll', 'payroll_runs', $runId, null, $monthYear, 'Monthly payroll executed');
            Session::flash('success', "Payroll generated for $monthYear as a draft. Review and approve it before payout.");
        } catch (\Throwable $e) {
            Session::flash('error', "Failed to run payroll: " . $e->getMessage());
        }

        return $this->redirect(url('payroll/runs'));
    }

    public function regenerate(string $id): string
    {
        $db = $this->db();
        $runId = (int)$id;
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [$runId]);

        if (!$run) {
            Session::flash('error', 'Payroll run not found.');
            return $this->redirect(url('payroll/runs'));
        }

        $monthYear = $run['month_year'];
        $targetMonth = date('Y-m', strtotime('1 ' . $monthYear));
        $newRunId = null;

        try {
            // Replaces the old run inside one transaction; on failure the old run stays.
            $newRunId = (new PayrollService($db))->generateRun($targetMonth, $this->authId());
            $this->logAudit('regenerated', 'payroll_runs', $newRunId, $monthYear, $monthYear, 'Payroll regenerated after sync update');
            Session::flash('success', "Payroll for $monthYear has been regenerated and synchronized.");
        } catch (\Throwable $e) {
            Session::flash('error', "Failed to regenerate payroll: " . $e->getMessage());
        }

        return $this->redirect(url('payroll/runs/' . ($newRunId ?? $runId)));
    }

    /**
     * Day-by-day breakdown for the run detail / payslip. Uses the snapshot stored on the
     * payroll item (same engine that produced the numbers); recomputes when absent.
     */
    private function getChronologicalBreakdown(string $monthYear, int $employeeId, ?string $statusEngineJson = null): array
    {
        return (new PayrollService($this->db()))->chronology($monthYear, $employeeId, $statusEngineJson);
    }

    private function checkIfOutOfSync(int $runId): bool
    {
        $db = $this->db();
        $run = $db->selectOne("SELECT month_year, created_at FROM payroll_runs WHERE id = ?", [$runId]);
        if (!$run) return false;

        $startDate = date('Y-m-01', strtotime('1 ' . $run['month_year']));
        $endDate = date('Y-m-t', strtotime($startDate));
        $runDate = $run['created_at'];

        $changes = $db->selectOne("
            SELECT COUNT(*) as cnt FROM payroll_audit_logs
            WHERE created_at > ? AND (
                (entity = 'attendance' AND entity_id IN (SELECT id FROM attendance WHERE check_in >= ? AND check_in < DATE_ADD(?, INTERVAL 1 DAY))) OR
                (entity = 'staff_attendance_logs' AND entity_id IN (SELECT id FROM staff_attendance_logs WHERE (date BETWEEN ? AND ?))) OR
                (entity = 'leave_requests' AND entity_id IN (SELECT id FROM leave_requests WHERE (start_date <= ? AND end_date >= ?))) OR
                (entity = 'holidays' AND entity_id IN (SELECT id FROM holidays WHERE (holiday_date BETWEEN ? AND ?)))
            )
        ", [$runDate, $startDate, $endDate, $startDate, $endDate, $endDate, $startDate, $startDate, $endDate]);

        return ((int)($changes['cnt'] ?? 0) > 0);
    }

    private function markAffectedRunsOutOfSync(string $date): void
    {
        PayrollService::markAffectedRunsOutOfSync($date);
    }

    private function logAudit(string $action, string $entity, int $entityId, ?string $oldVal, ?string $newVal, ?string $reason): void
    {
        PayrollService::logAudit($action, $entity, $entityId, $oldVal, $newVal, $reason, $this->authId());
    }

    public function attendance(): string
    {
        $db = $this->db();
        $date = (string) $this->request->get('date', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        // 1. Face Recognition Kiosk scans for the date, each resolved to exactly one employee
        $resolved = PayrollService::resolvedEmployeeSql('a');
        $faceLogsRaw = $db->select("
            SELECT x.*,
                   COALESCE(e.first_name, u.name, 'Staff Member') as first_name,
                   COALESCE(e.last_name, '') as last_name,
                   COALESCE(e.emp_code, u.employee_id, CONCAT('USR-', x.user_id)) as emp_code,
                   COALESCE(d.name, u.designation, 'General Staff') as department_name,
                   e.min_clock_in, e.max_clock_out,
                   COALESCE(u2.grace_period, u.grace_period) as user_grace_period
            FROM (
                SELECT a.id, a.user_id, a.employee_id AS raw_employee_id, a.check_in, a.confidence, a.image_path,
                       a.late_exempted, a.late_exemption_reason, $resolved AS resolved_employee_id
                FROM attendance a
                WHERE a.check_in >= ? AND a.check_in < DATE_ADD(?, INTERVAL 1 DAY)
            ) x
            LEFT JOIN employees e ON e.id = x.resolved_employee_id
            LEFT JOIN users u ON u.id = x.user_id
            LEFT JOIN users u2 ON u2.id = e.user_id
            LEFT JOIN departments d ON d.id = e.department_id
            ORDER BY x.check_in ASC, x.id ASC
        ", [$date . ' 00:00:00', $date]);

        $shift = ShiftSettings::current();

        $grouped = [];
        foreach ($faceLogsRaw as $log) {
            // Group by resolved employee; unmatched scans stay per login user
            $key = $log['resolved_employee_id'] ? 'E' . $log['resolved_employee_id'] : 'U' . ($log['user_id'] ?? 'x' . $log['id']);
            if (!isset($grouped[$key])) {
                $grouped[$key] = $log + ['scans' => [], 'exempted' => false, 'reason' => null];
            }
            $grouped[$key]['scans'][] = $log['check_in'];
            if (!empty($log['late_exempted'])) {
                $grouped[$key]['exempted'] = true;
                $grouped[$key]['reason'] = $log['late_exemption_reason'];
            }
        }

        $faceLogs = [];
        foreach ($grouped as $data) {
            $empId = $data['resolved_employee_id'] ? (int) $data['resolved_employee_id'] : null;
            $scans = $data['scans'];
            $first = $scans[0];
            $last  = $scans[count($scans) - 1];
            $rules = ShiftSettings::rulesFor($data, $shift);
            $status = ShiftSettings::classifyCheckIn($first, $rules);

            $clockOut = '--:--';
            $workingHours = '0 hrs 0 mins';
            if (count($scans) > 1) {
                $clockOut = date('h:i A', strtotime($last));
                $diff = strtotime($last) - strtotime($first);
                $workingHours = floor($diff / 3600) . ' hrs ' . floor(($diff % 3600) / 60) . ' mins';
            }

            $faceLogs[] = [
                'id' => $data['id'],
                'employee_id' => $empId,
                'clock_in' => date('h:i A', strtotime($first)),
                'clock_out' => $clockOut,
                'working_hours' => $workingHours,
                'status' => $status,
                'incomplete_log' => count($scans) === 1,
                'confidence' => $data['confidence'],
                'image_path' => $data['image_path'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'emp_code' => $data['emp_code'],
                'department_name' => $data['department_name'],
                'source' => 'Face Recognition Kiosk',
                'late_exempted' => $data['exempted'] ? 1 : 0,
                'late_exemption_reason' => $data['reason']
            ];
        }

        // 2. Manual staff attendance logs
        $manualLogs = $db->select("
            SELECT
                l.id,
                l.employee_id,
                l.clock_in,
                l.clock_out,
                l.working_hours,
                l.status,
                l.late_exempted,
                l.late_exemption_reason,
                l.incomplete_log,
                1.0 as confidence,
                '' as image_path,
                e.first_name,
                e.last_name,
                e.emp_code,
                d.name as department_name,
                'Manual Entry' as source
            FROM staff_attendance_logs l
            JOIN employees e ON l.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE l.date = ?
            ORDER BY l.id DESC
        ", [$date]);

        foreach ($manualLogs as &$mLog) {
            if (!empty($mLog['clock_in'])) {
                $mLog['clock_in'] = date('h:i A', strtotime($mLog['clock_in']));
            }
            if (!empty($mLog['clock_out']) && $mLog['clock_out'] !== '--:--') {
                $mLog['clock_out'] = date('h:i A', strtotime($mLog['clock_out']));
            }
        }
        unset($mLog);

        $logs = array_merge($faceLogs, $manualLogs);
        $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");

        return $this->view('payroll/attendance', compact('logs', 'date', 'employees'));
    }

    public function storeAttendance(): string
    {
        $employeeId = (int) $this->request->input('employee_id');
        $date       = (string) $this->request->input('date', date('Y-m-d'));
        $clockIn    = $this->request->input('clock_in', '09:00');
        $clockOut   = $this->request->input('clock_out', '17:00');
        $status     = (string) $this->request->input('status', 'present');

        if ($employeeId > 0) {
            try {
                PayrollService::saveManualAttendance($employeeId, $date, $clockIn, $clockOut, $status);
                Session::flash('success', "Attendance override log saved successfully.");
            } catch (\Throwable $e) {
                Session::flash('error', 'Failed to save attendance: ' . $e->getMessage());
            }
        }

        return $this->redirect(url('payroll/attendance?date=' . urlencode($date)));
    }

    public function auditLogs(): string
    {
        $db = $this->db();
        $auditLogs = $db->select("
            SELECT al.*, u.name as user_name 
            FROM payroll_audit_logs al
            JOIN users u ON al.user_id = u.id
            ORDER BY al.id DESC 
            LIMIT 200
        ");
        return $this->view('payroll/audit', compact('auditLogs'));
    }
}
