<?php

declare(strict_types=1);

namespace App\Services;

use Core\Application;
use Core\Database;

/**
 * The one payroll / staff-attendance engine.
 *
 * Used by PayrollController (/payroll/*), StaffWorkspaceController (/staff/payroll/run,
 * attendance pages, employee page) so salary numbers and the attendance screens agree.
 *
 * Day statuses (status_engine_json):
 *   NOT_JOINED, UPCOMING            -> never counted (before joining_date / after today)
 *   SUNDAY, PAID_HOLIDAY            -> paid, not a working day
 *   UNPAID_HOLIDAY                  -> deducted like an unpaid leave day
 *   PRESENT (+ is_late flag)        -> paid (late days feed the late-penalty ticker)
 *   HALF_DAY                        -> half_day_deduction_percent of a day deducted
 *   PAID_LEAVE / UNPAID_LEAVE       -> paid / one day deducted
 *   ABSENT                          -> one day deducted
 *
 * Priority on a working day: manual log > kiosk scans > approved leave > absent.
 */
class PayrollService
{
    private Database $db;

    /** @var array<string, array> holiday rows keyed by date, per range */
    private array $holidayCache = [];
    private ?bool $leaveTypesConfigured = null;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Application::$app->db;
    }

    /* ───────────────────────── Employees & matching ───────────────────────── */

    /** Employee row with the fields the rules need (user_grace_period). */
    public function employee(int $employeeId): ?array
    {
        $row = $this->db->selectOne(
            "SELECT e.*, u.grace_period AS user_grace_period
             FROM employees e LEFT JOIN users u ON u.id = e.user_id
             WHERE e.id = ?",
            [$employeeId]
        );
        return $row ?: null;
    }

    /** Active employees for payroll (tenant-scoped when a tenant is known). */
    public function activeEmployees(?int $tenantId = null): array
    {
        $sql = "SELECT e.*, u.grace_period AS user_grace_period
                FROM employees e LEFT JOIN users u ON u.id = e.user_id
                WHERE e.status = 'active'";
        $params = [];
        if ($tenantId) {
            $sql .= " AND e.tenant_id = ?";
            $params[] = $tenantId;
        }
        return $this->db->select($sql . " ORDER BY e.id ASC", $params);
    }

    /**
     * Login user whose kiosk scans belong to this employee:
     * employees.user_id, else the user whose users.employee_id equals the emp_code.
     */
    public function scanUserId(array $employee): ?int
    {
        if (!empty($employee['user_id'])) {
            return (int) $employee['user_id'];
        }
        if (!empty($employee['emp_code'])) {
            $u = $this->db->selectOne("SELECT id FROM users WHERE employee_id = ? LIMIT 1", [$employee['emp_code']]);
            if ($u) {
                return (int) $u['id'];
            }
        }
        return null;
    }

    /**
     * Kiosk scans of one employee grouped per day (ascending).
     * A scan belongs to the employee when a.user_id is the employee's user, or —
     * only when the scan has no user_id — when a.employee_id is the employee id.
     *
     * @return array<string, array{scans: string[], first_id: int, exempted: bool, reason: ?string, incomplete_flag: bool}>
     */
    public function kioskDays(array $employee, string $from, string $to): array
    {
        $userId = $this->scanUserId($employee);
        $match  = "(a.user_id IS NULL AND a.employee_id = ?)";
        $params = [(int) $employee['id']];
        if ($userId) {
            $match = "(a.user_id = ? OR $match)";
            array_unshift($params, $userId);
        }
        $rows = $this->db->select(
            "SELECT a.id, a.check_in, a.late_exempted, a.late_exemption_reason, a.incomplete_log
             FROM attendance a
             WHERE a.check_in IS NOT NULL
               AND a.check_in >= ? AND a.check_in < ?
               AND $match
             ORDER BY a.check_in ASC, a.id ASC",
            array_merge([$from . ' 00:00:00', date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00'], $params)
        );

        $days = [];
        foreach ($rows as $r) {
            $d = substr((string) $r['check_in'], 0, 10);
            if (!isset($days[$d])) {
                $days[$d] = [
                    'scans'           => [],
                    'first_id'        => (int) $r['id'],
                    'exempted'        => false,
                    'reason'          => null,
                    'incomplete_flag' => (bool) $r['incomplete_log'],
                ];
            }
            $days[$d]['scans'][] = (string) $r['check_in'];
            if (!empty($r['late_exempted'])) {
                $days[$d]['exempted'] = true;
                $days[$d]['reason']   = $r['late_exemption_reason'];
            }
        }
        return $days;
    }

    /**
     * SQL fragment resolving each attendance scan to exactly one employee id
     * (use as a derived column: `<expr> AS resolved_employee_id`).
     * Prefers employees.user_id = a.user_id; falls back to a.employee_id when the scan
     * carries no user_id; finally users.employee_id = employees.emp_code.
     */
    public static function resolvedEmployeeSql(string $a = 'a'): string
    {
        return "COALESCE(
            (SELECT e1.id FROM employees e1 WHERE {$a}.user_id IS NOT NULL AND e1.user_id = {$a}.user_id ORDER BY e1.id LIMIT 1),
            (SELECT e2.id FROM employees e2 WHERE {$a}.user_id IS NULL AND e2.id = {$a}.employee_id LIMIT 1),
            (SELECT e3.id FROM employees e3 JOIN users u3 ON u3.employee_id = e3.emp_code COLLATE utf8mb4_unicode_ci
              WHERE {$a}.user_id IS NOT NULL AND u3.id = {$a}.user_id AND e3.user_id IS NULL ORDER BY e3.id LIMIT 1)
        )";
    }

    /* ───────────────────────── Calendar inputs ───────────────────────── */

    private function holidays(string $from, string $to): array
    {
        $key = $from . '|' . $to;
        if (!isset($this->holidayCache[$key])) {
            $map = [];
            foreach ($this->db->select(
                "SELECT holiday_date, name, is_paid FROM holidays WHERE status = 'active' AND holiday_date BETWEEN ? AND ?",
                [$from, $to]
            ) as $h) {
                $map[$h['holiday_date']] = $h;
            }
            $this->holidayCache[$key] = $map;
        }
        return $this->holidayCache[$key];
    }

    private function leaveTypesConfigured(): bool
    {
        if ($this->leaveTypesConfigured === null) {
            $this->leaveTypesConfigured = (int) ($this->db->selectOne("SELECT COUNT(*) AS c FROM leave_types")['c'] ?? 0) > 0;
        }
        return $this->leaveTypesConfigured;
    }

    /* ───────────────────────── Day resolution ───────────────────────── */

    /**
     * Resolve every day in [$from, $to] for one employee with the shared rules.
     * @return array<string, array> keyed by 'Y-m-d'
     */
    public function resolveDays(array $employee, string $from, string $to, ?array $shift = null, ?string $today = null): array
    {
        $shift = $shift ?? ShiftSettings::current();
        $rules = ShiftSettings::rulesFor($employee, $shift);
        $today = $today ?? date('Y-m-d');
        $empId = (int) $employee['id'];
        $joining = !empty($employee['joining_date']) && $employee['joining_date'] !== '0000-00-00' ? $employee['joining_date'] : null;

        $holidays = $this->holidays($from, $to);

        $manual = [];
        foreach ($this->db->select(
            "SELECT * FROM staff_attendance_logs WHERE employee_id = ? AND date BETWEEN ? AND ?",
            [$empId, $from, $to]
        ) as $m) {
            $manual[$m['date']] = $m;
        }

        $kiosk = $this->kioskDays($employee, $from, $to);

        $leaves = $this->db->select(
            "SELECT lr.*, lt.name AS leave_type_name, COALESCE(lt.is_paid, 1) AS is_paid
             FROM leave_requests lr
             LEFT JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.employee_id = ? AND lr.status = 'approved'
               AND lr.start_date <= ? AND lr.end_date >= ?
             ORDER BY lr.id DESC",
            [$empId, $to, $from]
        );
        $leaveOn = static function (string $d) use ($leaves): ?array {
            foreach ($leaves as $l) {
                if ($d >= $l['start_date'] && $d <= $l['end_date']) {
                    return $l;
                }
            }
            return null;
        };

        $out = [];
        for ($ts = strtotime($from); $ts <= strtotime($to); $ts = strtotime('+1 day', $ts)) {
            $d = date('Y-m-d', $ts);
            $info = [
                'date'           => $d,
                'status'         => 'ABSENT',
                'label'          => null,
                'is_late'        => false,
                'late_exempted'  => false,
                'late_minutes'   => 0,
                'incomplete_log' => false,
                'source'         => 'none',
                'check_in'       => null,
                'check_out'      => null,
                'scans'          => [],
                'log_id'         => null,
                'manual'         => null,
                'reason'         => '',
            ];
            $isSunday = (date('N', $ts) == 7);

            if ($joining && $d < $joining) {
                $info['status'] = 'NOT_JOINED';
                $info['source'] = 'calendar';
            } elseif ($isSunday) {
                $info['status'] = 'SUNDAY';
                $info['source'] = 'calendar';
            } elseif (isset($holidays[$d])) {
                $paid = (int) ($holidays[$d]['is_paid'] ?? 1) === 1;
                $info['status'] = $paid ? 'PAID_HOLIDAY' : 'UNPAID_HOLIDAY';
                $info['reason'] = (string) $holidays[$d]['name'];
                $info['source'] = 'calendar';
            } elseif (isset($manual[$d])) {
                $m = $manual[$d];
                $info['source']   = 'manual';
                $info['manual']   = $m;
                $info['log_id']   = (int) $m['id'];
                $info['check_in'] = $m['clock_in'] ?: null;
                $info['check_out'] = $m['clock_out'] ?: null;
                $info['label']    = $m['status'];
                $info['incomplete_log'] = !empty($m['incomplete_log']) || ($m['clock_in'] && !$m['clock_out']);
                $info['reason']   = (string) ($m['late_exemption_reason'] ?? '');
                switch ($m['status']) {
                    case 'late':
                        $info['status'] = 'PRESENT';
                        $info['is_late'] = true;
                        $info['late_exempted'] = !empty($m['late_exempted']);
                        $info['late_minutes'] = $m['clock_in'] ? ShiftSettings::lateMinutes((string) $m['clock_in'], $rules) : 0;
                        break;
                    case 'half_day':
                        $info['status'] = 'HALF_DAY';
                        break;
                    case 'absent':
                        $info['status'] = 'ABSENT';
                        break;
                    case 'holiday':
                        $info['status'] = 'PAID_HOLIDAY';
                        break;
                    case 'on_leave':
                        $lv = $leaveOn($d);
                        if ($lv) {
                            $info['status'] = (int) $lv['is_paid'] === 1 ? 'PAID_LEAVE' : 'UNPAID_LEAVE';
                            $info['reason'] = (string) ($lv['leave_type_name'] ?? $lv['reason'] ?? '');
                        } else {
                            // No approved leave behind the manual mark. When the school has no
                            // leave types configured at all there is nothing to check against,
                            // so the leave is treated as paid.
                            $info['status'] = $this->leaveTypesConfigured() ? 'UNPAID_LEAVE' : 'PAID_LEAVE';
                        }
                        break;
                    case 'wfh':
                    case 'present':
                    default:
                        // An explicit manual "present"/"wfh" overrides any kiosk lateness.
                        $info['status'] = 'PRESENT';
                        break;
                }
            } elseif (isset($kiosk[$d])) {
                $k = $kiosk[$d];
                $scans = $k['scans'];
                $info['source']   = 'kiosk';
                $info['scans']    = $scans;
                $info['log_id']   = $k['first_id'];
                $info['check_in'] = date('H:i:s', strtotime($scans[0]));
                $info['check_out'] = count($scans) > 1 ? date('H:i:s', strtotime($scans[count($scans) - 1])) : null;
                $info['incomplete_log'] = count($scans) === 1;
                $cls = ShiftSettings::classifyCheckIn($scans[0], $rules);
                if ($cls === 'half_day') {
                    $info['status'] = 'HALF_DAY';
                } else {
                    $info['status'] = 'PRESENT';
                    if ($cls === 'late') {
                        $info['is_late'] = true;
                        $info['late_exempted'] = $k['exempted'];
                        $info['reason'] = (string) ($k['reason'] ?? '');
                    }
                }
                $info['late_minutes'] = ShiftSettings::lateMinutes($scans[0], $rules);
            } elseif ($d > $today) {
                $info['status'] = 'UPCOMING';
                $info['source'] = 'calendar';
            } elseif ($lv = $leaveOn($d)) {
                $info['status'] = (int) $lv['is_paid'] === 1 ? 'PAID_LEAVE' : 'UNPAID_LEAVE';
                $info['reason'] = (string) ($lv['leave_type_name'] ?? $lv['reason'] ?? '');
                $info['source'] = 'leave';
            }

            $out[$d] = $info;
        }
        return $out;
    }

    /**
     * Map an engine day to the lowercase status the attendance screens use.
     * present | late | half_day | absent | on_leave | holiday | wfh | sunday | pending | not_joined
     */
    public static function screenStatus(array $info): string
    {
        switch ($info['status']) {
            case 'PRESENT':
                if ($info['is_late']) return 'late';
                return ($info['label'] === 'wfh') ? 'wfh' : 'present';
            case 'HALF_DAY':       return 'half_day';
            case 'PAID_LEAVE':
            case 'UNPAID_LEAVE':   return 'on_leave';
            case 'PAID_HOLIDAY':
            case 'UNPAID_HOLIDAY': return 'holiday';
            case 'SUNDAY':         return 'sunday';
            case 'UPCOMING':       return 'pending';
            case 'NOT_JOINED':     return 'not_joined';
            default:               return 'absent';
        }
    }

    /* ───────────────────────── Monthly calculation ───────────────────────── */

    /** Salary divisor: configured working days for the month, else all non-Sundays. */
    public static function salaryDays(string $targetMonth, ?array $shift = null): int
    {
        $ts = strtotime($targetMonth . '-01');
        $configured = ShiftSettings::configuredWorkingDays((int) date('n', $ts), $shift);
        if ($configured > 0) {
            return $configured;
        }
        $n = 0;
        $dim = (int) date('t', $ts);
        for ($d = 1; $d <= $dim; $d++) {
            if ((int) date('w', strtotime(sprintf('%s-%02d', $targetMonth, $d))) !== 0) {
                $n++;
            }
        }
        return $n;
    }

    /** Full payroll line for one employee and month (no writes). */
    public function calculateEmployee(array $e, string $targetMonth, ?array $shift = null, ?string $today = null): array
    {
        $shift = $shift ?? ShiftSettings::current();
        $from  = $targetMonth . '-01';
        $to    = date('Y-m-t', strtotime($from));

        $salaryDays = self::salaryDays($targetMonth, $shift);
        $basic = round((float) ($e['salary_basic'] ?? 0), 2);
        $daily = $salaryDays > 0 ? $basic / $salaryDays : 0.0;
        $halfPct = (float) ($shift['half_day_deduction_percent'] ?? 50) / 100;
        $lateLimit = max(1, (int) ($shift['late_limit_count'] ?? 3));

        $days = $this->resolveDays($e, $from, $to, $shift, $today);

        $c = [
            'PRESENT' => 0, 'HALF_DAY' => 0, 'ABSENT' => 0, 'PAID_LEAVE' => 0, 'UNPAID_LEAVE' => 0,
            'PAID_HOLIDAY' => 0, 'UNPAID_HOLIDAY' => 0, 'SUNDAY' => 0, 'NOT_JOINED' => 0, 'UPCOMING' => 0,
        ];
        $lateCount = 0;
        $lateExempted = 0;
        $incomplete = 0;

        $engine = [];
        foreach ($days as $d => $info) {
            $c[$info['status']] = ($c[$info['status']] ?? 0) + 1;
            if ($info['is_late']) {
                $info['late_exempted'] ? $lateExempted++ : $lateCount++;
            }
            if ($info['incomplete_log'] && in_array($info['status'], ['PRESENT', 'HALF_DAY'], true)) {
                $incomplete++;
            }
            $engine[(int) substr($d, 8, 2)] = [
                'date'           => $d,
                'status'         => $info['status'],
                'label'          => $info['label'],
                'source'         => $info['source'],
                'check_in'       => $info['check_in'],
                'check_out'      => $info['check_out'],
                'is_late'        => $info['is_late'],
                'late_exempted'  => $info['late_exempted'],
                'incomplete_log' => $info['incomplete_log'],
                'reason'         => $info['reason'],
                'ticker'         => null,
                'apply_penalty'  => false,
                'penalty_carried'=> false,
            ];
        }

        // Late penalty ticker (resets every month): every Nth non-exempt late creates one
        // penalty half-day, charged on the next day that still has pay to deduct
        // (PRESENT / HALF_DAY), never on the day that created it. A penalty still pending
        // at month end is charged on the day that created it instead of being dropped.
        $ticker = 0;
        $pending = [];          // creation day numbers
        $penaltyDays = 0;
        foreach ($engine as $dn => &$info) {
            $creation = false;
            if ($info['is_late'] && !$info['late_exempted']) {
                $ticker++;
                $info['ticker'] = $ticker;
                if ($ticker % $lateLimit === 0) {
                    $pending[] = $dn;
                    $creation = true;
                }
            }
            $eligible = in_array($info['status'], ['PRESENT', 'HALF_DAY'], true);
            if (!$creation && $eligible && !empty($pending)) {
                array_shift($pending);
                $info['apply_penalty'] = true;
                $penaltyDays++;
            }
        }
        unset($info);
        foreach ($pending as $dn) {
            $engine[$dn]['apply_penalty'] = true;
            $engine[$dn]['penalty_carried'] = true;
            $penaltyDays++;
        }
        $lateRemainder = $ticker % $lateLimit;

        // Money
        $halfRate = $daily * $halfPct;
        $penaltyAmount = 0.0;
        foreach ($engine as $info) {
            if (!$info['apply_penalty']) continue;
            $amt = $halfRate;
            if ($info['status'] === 'HALF_DAY') {
                $amt = min($amt, max(0.0, $daily - $halfRate)); // never more than one day in total
            }
            $penaltyAmount += $amt;
        }

        $absentDed       = round($c['ABSENT'] * $daily, 2);
        $unpaidLeaveDed  = round($c['UNPAID_LEAVE'] * $daily, 2);
        $unpaidHolDed    = round($c['UNPAID_HOLIDAY'] * $daily, 2);
        $halfDayDed      = round($c['HALF_DAY'] * $halfRate, 2);
        $latePenaltyDed  = round($penaltyAmount, 2);

        $total = round($absentDed + $unpaidLeaveDed + $unpaidHolDed + $halfDayDed + $latePenaltyDed, 2);
        $capped = false;
        if ($total > $basic) {          // net salary never below zero
            $total = $basic;
            $capped = true;
        }
        $net = round($basic - $total, 2);

        return [
            'employee_id'               => (int) $e['id'],
            'salary_basic'              => $basic,
            'salary_days'               => $salaryDays,
            'daily_salary'              => round($daily, 2),
            'present_days'              => $c['PRESENT'],
            'normal_half_days'          => $c['HALF_DAY'],
            'absent_days'               => $c['ABSENT'],
            'paid_leave_days'           => $c['PAID_LEAVE'],
            'unpaid_leave_days'         => $c['UNPAID_LEAVE'],
            'paid_holiday_days'         => $c['PAID_HOLIDAY'],
            'unpaid_holiday_days'       => $c['UNPAID_HOLIDAY'],
            'sunday_days'               => $c['SUNDAY'],
            'not_joined_days'           => $c['NOT_JOINED'],
            'upcoming_days'             => $c['UPCOMING'],
            'incomplete_days'           => $incomplete,
            'late_count'                => $lateCount,
            'late_exempted_count'       => $lateExempted,
            'late_penalty_half_days'    => $penaltyDays,
            'late_remainder'            => $lateRemainder,
            'absent_deduction'          => $absentDed,
            'unpaid_leave_deduction'    => $unpaidLeaveDed,
            'unpaid_holiday_deduction'  => $unpaidHolDed,
            'normal_half_day_deduction' => $halfDayDed,
            'late_penalty_deduction'    => $latePenaltyDed,
            'total_deductions'          => $total,
            'deductions_capped'         => $capped,
            'net_salary'                => $net,
            'status_engine'             => $engine,
        ];
    }

    /** Calculate all active employees for a month (no writes). */
    public function calculateMonth(string $targetMonth, ?int $tenantId = null, ?string $today = null): array
    {
        $shift = ShiftSettings::current($tenantId);
        $monthEnd = date('Y-m-t', strtotime($targetMonth . '-01'));
        $items = [];
        $tot = ['gross' => 0.0, 'deductions' => 0.0, 'net' => 0.0];
        foreach ($this->activeEmployees($tenantId) as $e) {
            // Employees who join after the month are not part of it.
            if (!empty($e['joining_date']) && $e['joining_date'] !== '0000-00-00' && $e['joining_date'] > $monthEnd) {
                continue;
            }
            $item = $this->calculateEmployee($e, $targetMonth, $shift, $today);
            $items[] = $item;
            $tot['gross']      += $item['salary_basic'];
            $tot['deductions'] += $item['total_deductions'];
            $tot['net']        += $item['net_salary'];
        }
        return ['items' => $items, 'totals' => array_map(fn($v) => round($v, 2), $tot)];
    }

    /**
     * Calculate and save a month's payroll as a new draft run, replacing any earlier
     * run of the same month in one transaction (a failure keeps the previous run).
     * Locked runs are never replaced.
     */
    public function generateRun(string $targetMonth, ?int $actorUserId = null): int
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $targetMonth)) {
            throw new \InvalidArgumentException("Invalid month '$targetMonth' (expected YYYY-MM).");
        }
        $tenantId  = Database::getTenantId();
        $monthYear = date('F Y', strtotime($targetMonth . '-01'));

        $existing = $this->db->select(
            "SELECT id, status FROM payroll_runs WHERE month_year = ?" . ($tenantId ? " AND tenant_id = ?" : ""),
            $tenantId ? [$monthYear, $tenantId] : [$monthYear]
        );
        foreach ($existing as $r) {
            if ($r['status'] === 'locked') {
                throw new \RuntimeException("Payroll for $monthYear is locked and cannot be regenerated.");
            }
        }

        // Compute everything before touching the stored run.
        $calc = $this->calculateMonth($targetMonth, $tenantId);

        $this->db->beginTransaction();
        try {
            foreach ($existing as $r) {
                $this->db->delete('payroll_items', 'payroll_run_id = ?', [(int) $r['id']]);
                $this->db->delete('payroll_runs', 'id = ?', [(int) $r['id']]);
            }

            $runId = (int) $this->db->insert('payroll_runs', [
                'tenant_id'        => $tenantId ?? 1,
                'month_year'       => $monthYear,
                'total_gross'      => $calc['totals']['gross'],
                'total_deductions' => $calc['totals']['deductions'],
                'total_net'        => $calc['totals']['net'],
                'status'           => 'draft',
            ]);

            foreach ($calc['items'] as $it) {
                $this->db->insert('payroll_items', [
                    'payroll_run_id'            => $runId,
                    'employee_id'               => $it['employee_id'],
                    'working_days'              => $it['salary_days'],
                    'present_days'              => $it['present_days'],
                    'late_days'                 => $it['late_count'],
                    'half_days'                 => $it['normal_half_days'],
                    'absent_days'               => $it['absent_days'],
                    'gross_salary'              => $it['salary_basic'],
                    'late_deduction'            => $it['late_penalty_deduction'],
                    'absent_deduction'          => $it['absent_deduction'],
                    'net_salary'                => $it['net_salary'],
                    'salary_basic'              => $it['salary_basic'],
                    'salary_days'               => $it['salary_days'],
                    'daily_salary'              => $it['daily_salary'],
                    'normal_half_days'          => $it['normal_half_days'],
                    'paid_leave_days'           => $it['paid_leave_days'],
                    'unpaid_leave_days'         => $it['unpaid_leave_days'],
                    'paid_holiday_days'         => $it['paid_holiday_days'],
                    'unpaid_holiday_days'       => $it['unpaid_holiday_days'],
                    'sunday_days'               => $it['sunday_days'],
                    'late_count'                => $it['late_count'],
                    'late_exempted_count'       => $it['late_exempted_count'],
                    'late_penalty_half_days'    => $it['late_penalty_half_days'],
                    'late_remainder'            => $it['late_remainder'],
                    'incomplete_days'           => $it['incomplete_days'],
                    'unpaid_leave_deduction'    => $it['unpaid_leave_deduction'],
                    'unpaid_holiday_deduction'  => $it['unpaid_holiday_deduction'],
                    'normal_half_day_deduction' => $it['normal_half_day_deduction'],
                    'late_penalty_deduction'    => $it['late_penalty_deduction'],
                    'total_deductions'          => $it['total_deductions'],
                    'status_engine_json'        => json_encode($it['status_engine']),
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $ex) {
            $this->db->rollback();
            throw $ex;
        }

        // Non-critical: flag single-scan past days as incomplete on the kiosk rows.
        try {
            $this->persistIncompleteFlags($targetMonth, $calc['items']);
        } catch (\Throwable $ignored) {
        }

        return $runId;
    }

    private function persistIncompleteFlags(string $targetMonth, array $items): void
    {
        $today = date('Y-m-d');
        foreach ($items as $it) {
            $e = ['id' => $it['employee_id']] + ($this->employee((int) $it['employee_id']) ?? []);
            $kiosk = $this->kioskDays($e, $targetMonth . '-01', date('Y-m-t', strtotime($targetMonth . '-01')));
            foreach ($kiosk as $d => $k) {
                if ($d < $today && count($k['scans']) === 1 && !$k['incomplete_flag']) {
                    $this->db->update('attendance', ['incomplete_log' => 1], 'id = ?', [$k['first_id']]);
                }
            }
        }
    }

    /**
     * Chronology for the run detail / payslip: the stored snapshot of the run when
     * available, else recomputed with the same engine.
     */
    public function chronology(string $monthYear, int $employeeId, ?string $statusEngineJson = null): array
    {
        $engine = $statusEngineJson ? json_decode($statusEngineJson, true) : null;
        if (!is_array($engine) || empty($engine)) {
            $e = $this->employee($employeeId);
            if (!$e) return [];
            $engine = $this->calculateEmployee($e, date('Y-m', strtotime('1 ' . $monthYear)))['status_engine'];
        }
        $out = [];
        foreach ($engine as $dn => $info) {
            $out[(int) $dn] = [
                'date'           => $info['date'],
                'status'         => $info['status'],
                'label'          => $info['label'] ?? null,
                'is_late'        => (bool) ($info['is_late'] ?? false),
                'late_exempted'  => (bool) ($info['late_exempted'] ?? false),
                'incomplete_log' => (bool) ($info['incomplete_log'] ?? false),
                'reason'         => $info['reason'] ?? '',
                'ticker'         => $info['ticker'] ?? null,
                'penalty'        => (bool) ($info['apply_penalty'] ?? false),
                'penalty_carried'=> (bool) ($info['penalty_carried'] ?? false),
            ];
        }
        ksort($out);
        return $out;
    }

    /* ───────────────────────── Audit & sync ───────────────────────── */

    /** Mark the draft run of the month containing $date as out of sync. */
    public static function markAffectedRunsOutOfSync(string $date): void
    {
        $ts = strtotime($date);
        if ($ts === false) return;
        Application::$app->db->query(
            "UPDATE payroll_runs SET status = 'out_of_sync' WHERE month_year = ? AND status = 'draft'",
            [date('F Y', $ts)]
        );
    }

    public static function logAudit(string $action, string $entity, int $entityId, ?string $oldVal, ?string $newVal, ?string $reason, ?int $userId = null): void
    {
        Application::$app->db->insert('payroll_audit_logs', [
            'user_id'   => $userId ?? (int) (auth_id() ?? 1),
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => max(0, $entityId),
            'old_value' => $oldVal,
            'new_value' => $newVal,
            'reason'    => $reason,
        ]);
    }

    /**
     * Upsert a manual staff attendance entry (shared by /payroll/attendance and
     * /staff/attendance). Working hours = clock_out - clock_in (breaks are paid).
     * Returns the staff_attendance_logs row id.
     */
    public static function saveManualAttendance(int $employeeId, string $date, ?string $clockIn, ?string $clockOut, string $status, $breakMinutes = false): int
    {
        $db = Application::$app->db;
        $allowed = ['present', 'late', 'half_day', 'absent', 'on_leave', 'holiday', 'wfh'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid status '$status'.");
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
            throw new \InvalidArgumentException("Invalid date '$date'.");
        }
        $clockIn  = ($clockIn === null || trim($clockIn) === '') ? null : ShiftSettings::hms($clockIn, '09:00:00');
        $clockOut = ($clockOut === null || trim($clockOut) === '' || $clockOut === '--:--') ? null : ShiftSettings::hms($clockOut, '17:00:00');

        $hours = 0.0;
        if ($clockIn && $clockOut) {
            $diff = strtotime('1970-01-01 ' . $clockOut) - strtotime('1970-01-01 ' . $clockIn);
            $hours = $diff > 0 ? round($diff / 3600, 2) : 0.0;
        }
        $hours = min($hours, 99.99);

        $existing = $db->selectOne("SELECT * FROM staff_attendance_logs WHERE employee_id = ? AND date = ?", [$employeeId, $date]);
        $tenantId = Database::getTenantId() ?? 1;

        $data = [
            'clock_in'       => $clockIn,
            'clock_out'      => $clockOut,
            'working_hours'  => $hours,
            'status'         => $status,
            'incomplete_log' => ($clockIn && !$clockOut) ? 1 : 0,
        ];
        if ($breakMinutes !== false) {
            $data['break_minutes'] = $breakMinutes;
        }

        if ($existing) {
            // Keep a late exemption unless the clock-in time itself changed.
            if ((string) $existing['clock_in'] !== (string) $clockIn) {
                $data['late_exempted'] = 0;
                $data['late_exemption_reason'] = null;
                $data['late_exempted_by'] = null;
                $data['late_exempted_at'] = null;
            }
            $db->update('staff_attendance_logs', $data, 'id = ?', [(int) $existing['id']]);
            $id = (int) $existing['id'];
        } else {
            $id = (int) $db->insert('staff_attendance_logs', $data + [
                'tenant_id'   => $tenantId,
                'employee_id' => $employeeId,
                'date'        => $date,
            ]);
        }

        self::logAudit(
            'manual_attendance_override',
            'staff_attendance_logs',
            $id,
            $existing ? json_encode(['status' => $existing['status'], 'clock_in' => $existing['clock_in'], 'clock_out' => $existing['clock_out']]) : null,
            json_encode(['status' => $status, 'clock_in' => $clockIn, 'clock_out' => $clockOut]),
            "Manual override logged for date $date"
        );
        self::markAffectedRunsOutOfSync($date);
        return $id;
    }
}
