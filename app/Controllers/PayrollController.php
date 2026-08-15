<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Models\ActivityLog;

class PayrollController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    private function authId(): int
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
                    $run['status'] = 'out_of_sync';
                    $this->logAudit('marked_out_of_sync', 'payroll_runs', (int)$run['id'], $run['status'], 'out_of_sync', 'Automatic data drift detection');
                }
            }
        }

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
            return $this->redirect('/payroll/holidays');
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

        return $this->redirect('/payroll/holidays');
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

        return $this->redirect('/payroll/holidays');
    }

    public function settings(): string
    {
        $db = $this->db();
        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        return $this->view('payroll/settings', compact('shift'));
    }

    public function saveSettings(): string
    {
        $db = $this->db();
        $startTime = $this->request->input('start_time', '09:00:00');
        $graceMinutes = (int) $this->request->input('grace_minutes', 15);
        $lateAfter = $this->request->input('late_after', '09:16:00');
        $halfDayAfter = $this->request->input('half_day_after', '12:00:00');
        $lateLimitCount = (int) $this->request->input('late_limit_count', 3);
        $lateDeductionPercent = (float) $this->request->input('late_deduction_percent', 10.00);
        $halfDayDeductionPercent = (float) $this->request->input('half_day_deduction_percent', 50.00);
        $lecGraceMinutes = (int) $this->request->input('lec_grace_minutes', 5);

        try {
            $db->update('shift_templates', [
                'start_time' => $startTime,
                'grace_minutes' => $graceMinutes,
                'late_after' => $lateAfter,
                'half_day_after' => $halfDayAfter,
                'late_limit_count' => $lateLimitCount,
                'late_deduction_percent' => $lateDeductionPercent,
                'half_day_deduction_percent' => $halfDayDeductionPercent,
                'lec_grace_minutes' => $lecGraceMinutes,
            ], 'id = 1');

            // Force all draft runs to become out of sync when shift policies change
            $db->query("UPDATE payroll_runs SET status = 'out_of_sync' WHERE status = 'draft'");

            Session::flash('success', 'Shift policies and payroll rules saved.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to save settings: ' . $e->getMessage());
        }

        return $this->redirect('/payroll/settings');
    }

    public function runDetails(string $id): string
    {
        $db = $this->db();
        $runId = (int)$id;
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [$runId]);

        if (!$run) {
            Session::flash('error', 'Payroll run not found.');
            return $this->redirect('/payroll/runs');
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
            $chronology[$item['employee_id']] = $this->getChronologicalBreakdown($run['month_year'], (int)$item['employee_id']);
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
            return $this->redirect('/payroll/runs');
        }

        $chronology = $this->getChronologicalBreakdown($run['month_year'], (int)$empId);

        return $this->view('payroll/payslip', compact('run', 'item', 'chronology'));
    }

    public function exemptLate(): string
    {
        $db = $this->db();
        $logId = (int)$this->request->input('log_id');
        $source = $this->request->input('source', 'kiosk'); // kiosk or manual
        $reason = trim((string)$this->request->input('reason', 'Admin Exemption'));
        $exempt = (int)$this->request->input('exempt', 1);

        $table = ($source === 'manual') ? 'staff_attendance_logs' : 'attendance';
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

        $date = $log['date'] ?? $log['attendance_date'] ?? null;
        if ($date) {
            $this->markAffectedRunsOutOfSync($date);
        }

        header('Content-Type: application/json');
        return json_encode(['success' => true, 'message' => 'Lateness exemption updated.']);
    }

    public function runPayroll(): string
    {
        $db = $this->db();
        $targetMonth = $this->request->input('month', date('Y-m'));
        $monthYear = date('F Y', strtotime($targetMonth . '-01'));

        try {
            // Delete existing run for this month to ensure clean regeneration
            $existingRun = $db->selectOne("SELECT id FROM payroll_runs WHERE month_year = ?", [$monthYear]);
            if ($existingRun) {
                $db->delete('payroll_items', "payroll_run_id = ?", [$existingRun['id']]);
                $db->delete('payroll_runs', "id = ?", [$existingRun['id']]);
            }

            // Calculate payroll run
            $runId = $this->calculateAndSavePayroll($targetMonth);

            $this->logAudit('run_payroll', 'payroll_runs', $runId, null, $monthYear, 'Monthly payroll executed');
            Session::flash('success', "Payroll generated and approved for $monthYear successfully.");
        } catch (\Throwable $e) {
            Session::flash('error', "Failed to run payroll: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        }

        return $this->redirect('/payroll/runs');
    }

    public function regenerate(string $id): string
    {
        $db = $this->db();
        $runId = (int)$id;
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [$runId]);

        if (!$run) {
            Session::flash('error', 'Payroll run not found.');
            return $this->redirect('/payroll/runs');
        }

        $monthYear = $run['month_year'];
        $targetMonth = date('Y-m', strtotime($monthYear));

        try {
            $db->delete('payroll_items', "payroll_run_id = ?", [$runId]);
            $db->delete('payroll_runs', "id = ?", [$runId]);

            $newRunId = $this->calculateAndSavePayroll($targetMonth);

            $this->logAudit('regenerated', 'payroll_runs', $newRunId, $monthYear, $monthYear, 'Payroll regenerated after sync update');
            Session::flash('success', "Payroll for $monthYear has been regenerated and synchronized.");
        } catch (\Throwable $e) {
            Session::flash('error', "Failed to regenerate payroll: " . $e->getMessage());
        }

        return $this->redirect('/payroll/runs/' . ($newRunId ?? $runId));
    }

    private function calculateAndSavePayroll(string $targetMonth): int
    {
        $db = $this->db();
        $monthYear = date('F Y', strtotime($targetMonth . '-01'));

        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        $globalShiftStart = $shift['start_time'] ?? '09:00:00';
        $globalGrace = (int)($shift['grace_minutes'] ?? 15);
        $halfDayDeductionPercent = (float)($shift['half_day_deduction_percent'] ?? 50.00);

        // Salary Days = Calendar Days
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)date('m', strtotime($targetMonth)), (int)date('Y', strtotime($targetMonth)));
        $salaryDays = $daysInMonth; 

        $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");
        
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        $items = [];

        foreach ($employees as $e) {
            $empId = (int)$e['id'];
            $basic = (float)$e['salary_basic'];
            $dailySalary = $basic / $salaryDays;

            // Resolve daily statuses chronologically
            $presentCount = 0;
            $normalHalfDayCount = 0;
            $absentCount = 0;
            $paidLeaveCount = 0;
            $unpaidLeaveCount = 0;
            $paidHolidayCount = 0;
            $sundayCount = 0;
            $lateCount = 0;
            $lateExemptedCount = 0;
            $latePenaltyHalfDays = 0;

            $statusEngine = [];
            $accumulatedLates = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = $targetMonth . '-' . sprintf('%02d', $day);
                $isSunday = (date('N', strtotime($dateStr)) == 7);

                // Initialize default
                $dayStatus = 'ABSENT';
                $checkIn = null;
                $checkOut = null;
                $isLate = false;
                $lateExempted = false;
                $incompleteLog = false;
                $source = 'none';

                if ($isSunday) {
                    $dayStatus = 'SUNDAY';
                    $sundayCount++;
                    $source = 'calendar';
                } else {
                    // Check paid holiday
                    $holiday = $db->selectOne("SELECT * FROM holidays WHERE holiday_date = ? AND status = 'active'", [$dateStr]);
                    if ($holiday) {
                        $dayStatus = 'PAID_HOLIDAY';
                        $paidHolidayCount++;
                        $source = 'calendar';
                    } else {
                        // Check Manual logs
                        $manual = $db->selectOne("SELECT * FROM staff_attendance_logs WHERE employee_id = ? AND date = ?", [$empId, $dateStr]);
                        // Check Kiosk logs
                        $kioskLogs = $db->select("
                            SELECT * FROM attendance 
                            WHERE (employee_id = ? OR user_id = (SELECT id FROM users WHERE employee_id = ? LIMIT 1) OR user_id = ?)
                              AND (DATE(check_in) = ? OR attendance_date = ?)
                            ORDER BY check_in ASC
                        ", [$empId, $e['employee_code'], $e['user_id'], $dateStr, $dateStr]);

                        // Resolve Priority
                        if ($manual) {
                            $dayStatus = strtoupper($manual['status']); // PRESENT, LATE, HALF_DAY, etc.
                            $checkIn = $manual['clock_in'];
                            $checkOut = $manual['clock_out'];
                            $isLate = ($manual['status'] === 'late');
                            $lateExempted = (bool)$manual['late_exempted'];
                            $incompleteLog = (bool)$manual['incomplete_log'];
                            $source = 'manual';

                            // Recalculate late status based on custom shift start if set
                            $empShiftStart = !empty($e['min_clock_in']) ? $e['min_clock_in'] : $globalShiftStart;
                            $grace = !empty($e['min_clock_in']) ? 10 : $globalGrace;
                            $latePenaltyTime = date('H:i:s', strtotime($empShiftStart) + ($grace * 60));
                            if ($checkIn && $checkIn > $latePenaltyTime) {
                                $isLate = true;
                            }
                        } elseif (!empty($kioskLogs)) {
                            $first = $kioskLogs[0];
                            $last = count($kioskLogs) > 1 ? $kioskLogs[count($kioskLogs)-1] : null;
                            $checkIn = date('H:i:s', strtotime($first['check_in']));
                            $checkOut = $last ? date('H:i:s', strtotime($last['check_in'])) : null;
                            $source = 'kiosk';

                            if (!$last || $first['id'] === $last['id']) {
                                $incompleteLog = true;
                                // Save incomplete flag to DB if not already set
                                $db->update('attendance', ['incomplete_log' => 1], 'id = ?', [$first['id']]);
                            }

                            // Check shift start rules
                            $empShiftStart = !empty($e['min_clock_in']) ? $e['min_clock_in'] : $globalShiftStart;
                            $grace = !empty($e['min_clock_in']) ? 10 : $globalGrace;
                            $latePenaltyTime = date('H:i:s', strtotime($empShiftStart) + ($grace * 60));
                            $halfDayTime = !empty($e['min_clock_in']) ? date('H:i:s', strtotime($empShiftStart) + (3 * 3600)) : ($shift['half_day_after'] ?? '12:00:00');

                            if ($checkIn > $halfDayTime) {
                                $dayStatus = 'HALF_DAY';
                            } elseif ($checkIn > $latePenaltyTime) {
                                $dayStatus = 'PRESENT'; // Lateness calculated below
                                $isLate = true;
                                $lateExempted = (bool)$first['late_exempted'];
                            } else {
                                $dayStatus = 'PRESENT';
                            }
                        } else {
                            // Check Leave request
                            $leave = $db->selectOne("
                                SELECT lr.*, lt.is_paid 
                                FROM leave_requests lr
                                JOIN leave_types lt ON lr.leave_type_id = lt.id
                                WHERE lr.employee_id = ? AND lr.status = 'approved'
                                  AND ? BETWEEN lr.start_date AND lr.end_date
                                LIMIT 1
                            ", [$empId, $dateStr]);

                            if ($leave) {
                                $dayStatus = $leave['is_paid'] ? 'PAID_LEAVE' : 'UNPAID_LEAVE';
                                $source = 'leave';
                            } else {
                                $dayStatus = 'ABSENT';
                                $source = 'none';
                            }
                        }
                    }
                }

                // If approved leave exists but they scanned, marked status wins
                if (($dayStatus === 'PAID_LEAVE' || $dayStatus === 'UNPAID_LEAVE') && ($source === 'manual' || $source === 'kiosk')) {
                    $activeLeave = $db->selectOne("
                        SELECT id FROM leave_requests 
                        WHERE employee_id = ? AND status = 'approved' AND ? BETWEEN start_date AND end_date LIMIT 1
                    ", [$empId, $dateStr]);
                    if ($activeLeave) {
                        $db->update('leave_requests', [
                            'status'          => 'rejected', // Recalled/Rejected
                            'recalled_at'     => now(),
                            'recalled_by'     => $this->authId(),
                            'recalled_reason' => 'Physical attendance logged at kiosk'
                        ], 'id = ?', [$activeLeave['id']]);
                        $this->logAudit('leave_recalled', 'leave_requests', (int)$activeLeave['id'], 'approved', 'recalled', 'Auto recalled due to scan');
                    }
                }

                // Tally days counts
                if ($dayStatus === 'PRESENT') $presentCount++;
                elseif ($dayStatus === 'HALF_DAY') $normalHalfDayCount++;
                elseif ($dayStatus === 'ABSENT') $absentCount++;
                elseif ($dayStatus === 'PAID_LEAVE') $paidLeaveCount++;
                elseif ($dayStatus === 'UNPAID_LEAVE') $unpaidLeaveCount++;

                // Track late counts
                if ($isLate) {
                    if ($lateExempted) {
                        $lateExemptedCount++;
                    } else {
                        $lateCount++;
                        $accumulatedLates++;
                    }
                }

                $statusEngine[$day] = [
                    'date'              => $dateStr,
                    'status'            => $dayStatus,
                    'is_late'           => $isLate,
                    'late_exempted'     => $lateExempted,
                    'incomplete_log'    => $incompleteLog,
                    'is_sunday_holiday' => ($isSunday || $dayStatus === 'PAID_HOLIDAY'),
                    'apply_penalty'     => false
                ];
            }

            // Calculate chronological late penalty days (Every 3 lates → next eligible day receives 50% salary deduction)
            $pendingPenalties = 0;
            $lateTicker = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $info = &$statusEngine[$d];
                
                // Track accumulated lates
                if ($info['is_late'] && !$info['late_exempted']) {
                    $lateTicker++;
                    if ($lateTicker % 3 === 0) {
                        $pendingPenalties++;
                    }
                }

                // Check if this day is eligible to absorb a pending penalty
                // Eligible day: Not Sunday, Not Paid Holiday, and must be a day of active employment
                $isEligible = (!$info['is_sunday_holiday'] && ($info['status'] === 'PRESENT' || $info['status'] === 'HALF_DAY' || $info['status'] === 'ABSENT'));
                
                // Also: Do NOT penalize the exact same day that creates the 3rd late
                $isCreationDay = ($info['is_late'] && !$info['late_exempted'] && $lateTicker % 3 === 0);

                if ($pendingPenalties > 0 && $isEligible && !$isCreationDay) {
                    $info['apply_penalty'] = true;
                    $latePenaltyHalfDays++;
                    $pendingPenalties--;
                }
            }

            // Deductions Math
            $absentDeduction = $absentCount * $dailySalary;
            $unpaidLeaveDeduction = $unpaidLeaveCount * $dailySalary;
            
            // Normal Half-Day Deductions
            $normalHalfDayDeduction = $normalHalfDayCount * $dailySalary * ($halfDayDeductionPercent / 100);

            // Late Penalty Deductions
            $latePenaltyDeduction = 0.00;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $info = $statusEngine[$d];
                if ($info['apply_penalty']) {
                    // Penalty deduction is 50% of daily salary.
                    $penaltyAmount = $dailySalary * 0.5;

                    // If already a normal half-day (50% deduction), cap the combined deduction at 100% (Daily Salary)
                    if ($info['status'] === 'HALF_DAY') {
                        $normalHalfDayRate = $dailySalary * ($halfDayDeductionPercent / 100);
                        $remainingEligibleDeduction = max(0.00, $dailySalary - $normalHalfDayRate);
                        $penaltyAmount = min($penaltyAmount, $remainingEligibleDeduction);
                    }

                    $latePenaltyDeduction += $penaltyAmount;
                }
            }

            $totalEmpDeductions = $absentDeduction + $unpaidLeaveDeduction + $normalHalfDayDeduction + $latePenaltyDeduction;

            // Enforce basic salary cap
            if ($totalEmpDeductions > $basic) {
                $totalEmpDeductions = $basic;
            }

            $netSalary = $basic - $totalEmpDeductions;

            $totalGross += $basic;
            $totalDeductions += $totalEmpDeductions;
            $totalNet += $netSalary;

            $items[] = [
                'employee_id'            => $empId,
                'salary_basic'           => $basic,
                'salary_days'            => $salaryDays,
                'daily_salary'           => $dailySalary,
                'present_days'           => $presentCount,
                'normal_half_days'       => $normalHalfDayCount,
                'absent_days'            => $absentCount,
                'paid_leave_days'        => $paidLeaveCount,
                'unpaid_leave_days'      => $unpaidLeaveCount,
                'paid_holiday_days'      => $paidHolidayCount,
                'sunday_days'            => $sundayCount,
                'late_count'             => $lateCount,
                'late_exempted_count'    => $lateExemptedCount,
                'late_penalty_half_days' => $latePenaltyHalfDays,
                'absent_deduction'       => $absentDeduction,
                'unpaid_leave_deduction' => $unpaidLeaveDeduction,
                'normal_half_day_deduction' => $normalHalfDayDeduction,
                'late_penalty_deduction' => $latePenaltyDeduction,
                'total_deductions'       => $totalEmpDeductions,
                'net_salary'             => $netSalary,
                'status_engine_json'     => json_encode($statusEngine)
            ];
        }

        $runId = (int)$db->insert('payroll_runs', [
            'tenant_id'        => 1,
            'month_year'       => $monthYear,
            'total_gross'      => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net'        => $totalNet,
            'status'           => 'draft'
        ]);

        foreach ($items as $item) {
            $db->insert('payroll_items', [
                'payroll_run_id'            => $runId,
                'employee_id'               => $item['employee_id'],
                'working_days'              => $item['salary_days'],
                'present_days'              => $item['present_days'],
                'gross_salary'              => $item['salary_basic'],
                'absent_deduction'          => $item['absent_deduction'],
                'net_salary'                => $item['net_salary'],
                'salary_basic'              => $item['salary_basic'],
                'salary_days'               => $item['salary_days'],
                'daily_salary'              => $item['daily_salary'],
                'normal_half_days'          => $item['normal_half_days'],
                'paid_leave_days'           => $item['paid_leave_days'],
                'unpaid_leave_days'         => $item['unpaid_leave_days'],
                'paid_holiday_days'         => $item['paid_holiday_days'],
                'sunday_days'               => $item['sunday_days'],
                'late_count'                => $item['late_count'],
                'late_exempted_count'       => $item['late_exempted_count'],
                'late_penalty_half_days'    => $item['late_penalty_half_days'],
                'unpaid_leave_deduction'    => $item['unpaid_leave_deduction'],
                'normal_half_day_deduction' => $item['normal_half_day_deduction'],
                'late_penalty_deduction'    => $item['late_penalty_deduction'],
                'total_deductions'          => $item['total_deductions'],
                'late_days'                 => $item['late_count'],
                'half_days'                 => $item['normal_half_days'],
                'absent_days'               => $item['absent_days']
            ]);

            // Save status engine payload to table for audit chronology
            $db->query("UPDATE payroll_items SET absent_deduction = ? WHERE payroll_run_id = ? AND employee_id = ?", [$item['absent_deduction'], $runId, $item['employee_id']]);
        }

        return $runId;
    }

    private function getChronologicalBreakdown(string $monthYear, int $employeeId): array
    {
        $db = $this->db();
        $targetMonth = date('Y-m', strtotime($monthYear));
        
        $run = $db->selectOne("SELECT id FROM payroll_runs WHERE month_year = ? LIMIT 1", [$monthYear]);
        if (!$run) return [];

        // Regenerate chronological list dynamically for admin panel breakdown
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)date('m', strtotime($targetMonth)), (int)date('Y', strtotime($targetMonth)));
        
        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        $globalShiftStart = $shift['start_time'] ?? '09:00:00';
        $globalGrace = (int)($shift['grace_minutes'] ?? 15);

        $e = $db->selectOne("SELECT * FROM employees WHERE id = ?", [$employeeId]);
        if (!$e) return [];

        $chronology = [];
        $lateTicker = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $targetMonth . '-' . sprintf('%02d', $day);
            $isSunday = (date('N', strtotime($dateStr)) == 7);
            
            $status = 'PRESENT';
            $isLate = false;
            $lateExempted = false;
            $reason = '';
            
            if ($isSunday) {
                $status = 'SUNDAY';
            } else {
                $holiday = $db->selectOne("SELECT * FROM holidays WHERE holiday_date = ? AND status = 'active'", [$dateStr]);
                if ($holiday) {
                    $status = 'PAID_HOLIDAY';
                    $reason = $holiday['name'];
                } else {
                    $manual = $db->selectOne("SELECT * FROM staff_attendance_logs WHERE employee_id = ? AND date = ?", [$employeeId, $dateStr]);
                    $kiosk = $db->selectOne("
                        SELECT * FROM attendance 
                        WHERE (employee_id = ? OR user_id = ?) AND (DATE(check_in) = ? OR attendance_date = ?) 
                        LIMIT 1
                    ", [$employeeId, $e['user_id'], $dateStr, $dateStr]);

                    if ($manual) {
                        $status = strtoupper($manual['status']);
                        $isLate = ($manual['status'] === 'late');
                        $lateExempted = (bool)$manual['late_exempted'];
                        $reason = $manual['late_exemption_reason'] ?? '';

                        $empShiftStart = !empty($e['min_clock_in']) ? $e['min_clock_in'] : $globalShiftStart;
                        $grace = !empty($e['min_clock_in']) ? 10 : $globalGrace;
                        $latePenaltyTime = date('H:i:s', strtotime($empShiftStart) + ($grace * 60));
                        if ($manual['clock_in'] && $manual['clock_in'] > $latePenaltyTime) {
                            $isLate = true;
                        }
                    } elseif ($kiosk) {
                        $status = 'PRESENT';
                        $checkIn = date('H:i:s', strtotime($kiosk['check_in']));
                        
                        $empShiftStart = !empty($e['min_clock_in']) ? $e['min_clock_in'] : $globalShiftStart;
                        $grace = !empty($e['min_clock_in']) ? 10 : $globalGrace;
                        $latePenaltyTime = date('H:i:s', strtotime($empShiftStart) + ($grace * 60));
                        $halfDayTime = !empty($e['min_clock_in']) ? date('H:i:s', strtotime($empShiftStart) + (3 * 3600)) : ($shift['half_day_after'] ?? '12:00:00');

                        if ($checkIn > $halfDayTime) {
                            $status = 'HALF_DAY';
                        } elseif ($checkIn > $latePenaltyTime) {
                            $isLate = true;
                            $lateExempted = (bool)$kiosk['late_exempted'];
                            $reason = $kiosk['late_exemption_reason'] ?? '';
                        }
                    } else {
                        $leave = $db->selectOne("
                            SELECT lr.*, lt.is_paid 
                            FROM leave_requests lr
                            JOIN leave_types lt ON lr.leave_type_id = lt.id
                            WHERE lr.employee_id = ? AND lr.status = 'approved' AND ? BETWEEN lr.start_date AND lr.end_date LIMIT 1
                        ", [$employeeId, $dateStr]);
                        if ($leave) {
                            $status = $leave['is_paid'] ? 'PAID_LEAVE' : 'UNPAID_LEAVE';
                            $reason = $leave['reason'] ?? '';
                        } else {
                            $status = 'ABSENT';
                        }
                    }
                }
            }

            if ($isLate && !$lateExempted) {
                $lateTicker++;
            }

            $chronology[$day] = [
                'date'          => $dateStr,
                'status'        => $status,
                'is_late'       => $isLate,
                'late_exempted' => $lateExempted,
                'reason'        => $reason,
                'ticker'        => ($isLate && !$lateExempted) ? $lateTicker : null,
                'penalty'       => false
            ];
        }

        // Apply penalty ticks chronologically
        $pendingPenalties = 0;
        $ticker = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $info = &$chronology[$d];
            if ($info['is_late'] && !$info['late_exempted']) {
                $ticker++;
                if ($ticker % 3 === 0) {
                    $pendingPenalties++;
                }
            }
            $isEligible = ($info['status'] !== 'SUNDAY' && $info['status'] !== 'PAID_HOLIDAY' && ($info['status'] === 'PRESENT' || $info['status'] === 'HALF_DAY' || $info['status'] === 'ABSENT'));
            $isCreationDay = ($info['is_late'] && !$info['late_exempted'] && $ticker % 3 === 0);

            if ($pendingPenalties > 0 && $isEligible && !$isCreationDay) {
                $info['penalty'] = true;
                $pendingPenalties--;
            }
        }

        return $chronology;
    }

    private function checkIfOutOfSync(int $runId): bool
    {
        $db = $this->db();
        $run = $db->selectOne("SELECT month_year FROM payroll_runs WHERE id = ?", [$runId]);
        if (!$run) return false;

        $targetMonth = date('Y-m', strtotime($run['month_year']));
        $startDate = $targetMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Check if audit logs exist after payroll generation date
        $runDate = $db->selectOne("SELECT created_at FROM payroll_runs WHERE id = ?", [$runId])['created_at'];
        
        $changes = $db->selectOne("
            SELECT COUNT(*) as cnt FROM payroll_audit_logs 
            WHERE created_at > ? AND (
                (entity = 'attendance' AND entity_id IN (SELECT id FROM attendance WHERE (DATE(check_in) BETWEEN ? AND ?))) OR
                (entity = 'staff_attendance_logs' AND entity_id IN (SELECT id FROM staff_attendance_logs WHERE (date BETWEEN ? AND ?))) OR
                (entity = 'leave_requests' AND entity_id IN (SELECT id FROM leave_requests WHERE (start_date BETWEEN ? AND ? OR end_date BETWEEN ? AND ?))) OR
                (entity = 'holidays' AND entity_id IN (SELECT id FROM holidays WHERE (holiday_date BETWEEN ? AND ?)))
            )
        ", [$runDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);

        return ((int)($changes['cnt'] ?? 0) > 0);
    }

    private function markAffectedRunsOutOfSync(string $date): void
    {
        $db = $this->db();
        $monthYear = date('F Y', strtotime($date));
        $db->query("UPDATE payroll_runs SET status = 'out_of_sync' WHERE month_year = ? AND status = 'draft'", [$monthYear]);
    }

    private function logAudit(string $action, string $entity, int $entityId, ?string $oldVal, ?string $newVal, ?string $reason): void
    {
        $db = $this->db();
        $db->insert('payroll_audit_logs', [
            'user_id'   => $this->authId(),
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'old_value' => $oldVal,
            'new_value' => $newVal,
            'reason'    => $reason
        ]);
    }
}
