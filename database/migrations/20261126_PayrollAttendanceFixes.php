<?php

/**
 * Payroll & staff attendance fixes.
 *  - payroll_items: columns the payroll engine writes (breakdown, deductions, day snapshot)
 *  - payroll_runs.status: add 'out_of_sync' (code wrote it into an enum that lacked it,
 *    which MySQL stored as ''); repair those rows to 'out_of_sync'
 *  - indexes for kiosk matching and payroll lookups
 * Idempotent: every change checks the live schema first.
 */
class PayrollAttendanceFixes
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return (bool) $this->db->selectOne(
            "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?",
            [$table, $column]
        );
    }

    private function hasIndex(string $table, string $index): bool
    {
        return (bool) $this->db->selectOne(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
            [$table, $index]
        );
    }

    /** True when some index on $table starts with exactly these columns. */
    private function hasIndexOn(string $table, array $columns): bool
    {
        $rows = $this->db->select(
            "SELECT index_name, seq_in_index, column_name FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? ORDER BY index_name, seq_in_index",
            [$table]
        );
        $byIndex = [];
        foreach ($rows as $r) {
            $byIndex[$r['index_name'] ?? $r['INDEX_NAME']][] = strtolower($r['column_name'] ?? $r['COLUMN_NAME']);
        }
        foreach ($byIndex as $cols) {
            if (array_slice($cols, 0, count($columns)) === array_map('strtolower', $columns)) {
                return true;
            }
        }
        return false;
    }

    private function addIndex(string $table, string $name, array $columns, bool $unique = false): void
    {
        if ($this->hasIndex($table, $name) || $this->hasIndexOn($table, $columns)) {
            return;
        }
        $cols = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $this->db->query("ALTER TABLE `$table` ADD " . ($unique ? 'UNIQUE ' : '') . "INDEX `$name` ($cols)");
    }

    private function itemColumns(): array
    {
        return [
            'salary_basic'              => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'salary_days'               => "INT NOT NULL DEFAULT 0",
            'daily_salary'              => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'normal_half_days'          => "INT NOT NULL DEFAULT 0",
            'paid_leave_days'           => "INT NOT NULL DEFAULT 0",
            'unpaid_leave_days'         => "INT NOT NULL DEFAULT 0",
            'paid_holiday_days'         => "INT NOT NULL DEFAULT 0",
            'unpaid_holiday_days'       => "INT NOT NULL DEFAULT 0",
            'sunday_days'               => "INT NOT NULL DEFAULT 0",
            'late_count'                => "INT NOT NULL DEFAULT 0",
            'late_exempted_count'       => "INT NOT NULL DEFAULT 0",
            'late_penalty_half_days'    => "INT NOT NULL DEFAULT 0",
            'late_remainder'            => "INT NOT NULL DEFAULT 0",
            'incomplete_days'           => "INT NOT NULL DEFAULT 0",
            'unpaid_leave_deduction'    => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'unpaid_holiday_deduction'  => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'normal_half_day_deduction' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'late_penalty_deduction'    => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'total_deductions'          => "DECIMAL(10,2) NOT NULL DEFAULT 0.00",
            'status_engine_json'        => "LONGTEXT NULL",
        ];
    }

    public function up(): void
    {
        // 1. payroll_items columns
        foreach ($this->itemColumns() as $col => $def) {
            if (!$this->hasColumn('payroll_items', $col)) {
                $this->db->query("ALTER TABLE `payroll_items` ADD COLUMN `$col` $def");
            }
        }

        // 2. payroll_runs.status gets 'out_of_sync'; repair rows silently stored as ''
        $col = $this->db->selectOne("SHOW COLUMNS FROM `payroll_runs` LIKE 'status'");
        if ($col && stripos((string) $col['Type'], "'out_of_sync'") === false) {
            $this->db->query("ALTER TABLE `payroll_runs` MODIFY `status` ENUM('draft','approved','locked','out_of_sync') NULL DEFAULT 'draft'");
        }
        $this->db->query("UPDATE `payroll_runs` SET `status` = 'out_of_sync' WHERE `status` = ''");

        // 3. indexes
        $this->addIndex('attendance', 'idx_att_user_date', ['user_id', 'attendance_date']);
        $this->addIndex('attendance', 'idx_att_emp_date', ['employee_id', 'attendance_date']);
        $this->addIndex('attendance', 'idx_att_check_in', ['check_in']);
        $this->addIndex('employees', 'idx_emp_user', ['user_id']);
        $this->addIndex('payroll_items', 'idx_pi_employee', ['employee_id']);

        $dups = $this->db->selectOne(
            "SELECT COUNT(*) AS c FROM (SELECT payroll_run_id, employee_id FROM payroll_items
             GROUP BY payroll_run_id, employee_id HAVING COUNT(*) > 1) d"
        );
        if ((int) ($dups['c'] ?? 0) === 0) {
            // The unique (run, employee) index also serves lookups by payroll_run_id.
            if (!$this->hasIndex('payroll_items', 'uq_pi_run_employee')) {
                $this->db->query("ALTER TABLE `payroll_items` ADD UNIQUE INDEX `uq_pi_run_employee` (`payroll_run_id`, `employee_id`)");
            }
        } else {
            $this->addIndex('payroll_items', 'idx_pi_run', ['payroll_run_id']);
        }
    }

    public function down(): void
    {
        foreach (['idx_att_user_date', 'idx_att_emp_date', 'idx_att_check_in'] as $ix) {
            if ($this->hasIndex('attendance', $ix)) $this->db->query("ALTER TABLE `attendance` DROP INDEX `$ix`");
        }
        if ($this->hasIndex('employees', 'idx_emp_user')) $this->db->query("ALTER TABLE `employees` DROP INDEX `idx_emp_user`");
        foreach (['uq_pi_run_employee', 'idx_pi_run', 'idx_pi_employee'] as $ix) {
            if ($this->hasIndex('payroll_items', $ix)) $this->db->query("ALTER TABLE `payroll_items` DROP INDEX `$ix`");
        }

        $this->db->query("UPDATE `payroll_runs` SET `status` = 'draft' WHERE `status` = 'out_of_sync'");
        $this->db->query("ALTER TABLE `payroll_runs` MODIFY `status` ENUM('draft','approved','locked') NULL DEFAULT 'draft'");

        foreach (array_keys($this->itemColumns()) as $col) {
            if ($this->hasColumn('payroll_items', $col)) {
                $this->db->query("ALTER TABLE `payroll_items` DROP COLUMN `$col`");
            }
        }
    }
}
