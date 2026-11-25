<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{User, Role};
use App\Services\EmployeeSyncService;

class StaffWorkspaceController extends Controller
{
    public function overview(): string
    {
        $db = Application::$app->db;
        $totalEmployees = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE status = 'active'")['cnt'] ?? 3);
        $totalDepartments = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM departments WHERE is_active = 1")['cnt'] ?? 5);
        $totalDesignations = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM designations WHERE is_active = 1")['cnt'] ?? 6);
        $totalUsers = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM users")['cnt'] ?? 7);

        return $this->view('staff/overview', compact('totalEmployees', 'totalDepartments', 'totalDesignations', 'totalUsers'));
    }

    public function employees(): string
    {
        $db = Application::$app->db;
        $search = $this->request->get('search', '');
        $deptId = $this->request->get('department_id', '');

        $sql = "SELECT e.*, d.name as department_name, des.title as designation_title 
                FROM employees e 
                LEFT JOIN departments d ON e.department_id = d.id 
                LEFT JOIN designations des ON e.designation_id = des.id 
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.emp_code LIKE ? OR e.email LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
        }

        if ($deptId !== '') {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $deptId;
        }

        $sql .= " ORDER BY e.id DESC";

        $employees = $db->select($sql, $params);

        // Also fetch teacher/staff users who don't have an employee record yet
        $userConditions = [
            "u.id IN (SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.slug IN ('teacher', 'staff', 'therapist', 'driver'))",
            "u.id NOT IN (SELECT e2.user_id FROM employees e2 WHERE e2.user_id IS NOT NULL)",
            "u.deleted_at IS NULL"
        ];
        $userParams = [];

        if ($search !== '') {
            $userConditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $userParams[] = "%$search%";
            $userParams[] = "%$search%";
        }

        $unlinkedUsers = $db->select(
            "SELECT u.* FROM users u WHERE " . implode(' AND ', $userConditions),
            $userParams
        );

        // Get the Academics department and Teacher designation for defaults
        $acadDept = $db->selectOne("SELECT id FROM departments WHERE code = 'ACAD' LIMIT 1");
        $teacherDesig = $db->selectOne("SELECT id FROM designations WHERE code = 'TCH' LIMIT 1");
        $defaultDeptId = $acadDept['id'] ?? null;
        $defaultDesigId = $teacherDesig['id'] ?? null;

        foreach ($unlinkedUsers as $u) {
            $parts = explode(' ', trim($u['name']), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';

            // Resolve role-based department/designation
            $userRoles = $db->select(
                "SELECT r.slug FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?",
                [$u['id']]
            );
            $roleSlugs = array_column($userRoles, 'slug');

            $empDeptId = $defaultDeptId;
            $empDesigId = $defaultDesigId;
            $empDeptName = 'Academics';
            $empDesigTitle = 'Teacher';

            if (in_array('driver', $roleSlugs)) {
                $empDeptName = 'Support';
                $empDesigTitle = 'Driver';
                $d = $db->selectOne("SELECT id FROM departments WHERE code = 'SUPP' LIMIT 1");
                $empDeptId = $d['id'] ?? $defaultDeptId;
                $des = $db->selectOne("SELECT id FROM designations WHERE code = 'DRV' LIMIT 1");
                $empDesigId = $des['id'] ?? $defaultDesigId;
            } elseif (in_array('therapist', $roleSlugs)) {
                $empDesigTitle = 'Therapist';
                $des = $db->selectOne("SELECT id FROM designations WHERE code = 'THER' LIMIT 1");
                $empDesigId = $des['id'] ?? $defaultDesigId;
            } elseif (in_array('staff', $roleSlugs) && !in_array('teacher', $roleSlugs)) {
                $empDeptName = 'Administration';
                $empDesigTitle = 'Staff Member';
                $d = $db->selectOne("SELECT id FROM departments WHERE code = 'ADMIN' LIMIT 1");
                $empDeptId = $d['id'] ?? $defaultDeptId;
                $des = $db->selectOne("SELECT id FROM designations WHERE code = 'STAFF' LIMIT 1");
                $empDesigId = $des['id'] ?? $defaultDesigId;
            }

            if ($deptId !== '' && (int)$empDeptId !== (int)$deptId) {
                continue;
            }

            $employees[] = [
                'id' => null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $u['email'],
                'emp_code' => $u['employee_id'] ?: 'Not Linked',
                'department_id' => $empDeptId,
                'designation_id' => $empDesigId,
                'department_name' => $empDeptName,
                'designation_title' => $empDesigTitle,
                'joining_date' => $u['created_at'] ? date('Y-m-d', strtotime($u['created_at'])) : '',
                'min_clock_in' => null,
                'max_clock_out' => null,
                'salary_basic' => 0,
                'status' => $u['is_active'] ? 'active' : 'inactive',
                'user_id' => $u['id'],
            ];
        }

        $departments = $db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = $db->select("SELECT * FROM designations WHERE is_active = 1");

        return $this->view('staff/employees', compact('employees', 'departments', 'designations', 'search', 'deptId'));
    }

    public function showEmployee(string|int $id): string
    {
        $id = (int) $id;
        $db = Application::$app->db;
        $employee = $db->selectOne("SELECT e.*, d.name as department_name, des.title as designation_title, u.grace_period as user_grace_period
                                   FROM employees e 
                                   LEFT JOIN departments d ON e.department_id = d.id 
                                   LEFT JOIN designations des ON e.designation_id = des.id 
                                   LEFT JOIN users u ON e.user_id = u.id
                                   WHERE e.id = ?", [$id]);

        if (!$employee) {
            $this->flash('error', 'Employee record not found.');
            return $this->redirect(url('staff/employees'));
        }

        $profile = $db->selectOne("SELECT * FROM employee_profiles WHERE employee_id = ?", [$id]);
        $salary  = $db->selectOne("SELECT * FROM salary_structures WHERE employee_id = ?", [$id]);
        $departments = $db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = $db->select("SELECT * FROM designations WHERE is_active = 1");

        // Attendance month filter
        $attMonth = max(1, min(12, (int)($this->request->get('att_month') ?? date('m'))));
        $attYear  = (int)($this->request->get('att_year') ?? date('Y'));
        $startDate = sprintf('%04d-%02d-01', $attYear, $attMonth);
        $endDate = date('Y-m-t', strtotime($startDate));

        // Same engine and shift rules as payroll (manual > kiosk > leave > absent,
        // holidays / Sundays / pre-joining / future days are never absent).
        $shift = \App\Services\ShiftSettings::current();
        $rules = \App\Services\ShiftSettings::rulesFor($employee, $shift);
        $shiftEnd = $rules['end'];
        $configuredDays = \App\Services\ShiftSettings::configuredWorkingDays($attMonth, $shift);
        $resolvedDays = (new \App\Services\PayrollService($db))->resolveDays($employee, $startDate, $endDate, $shift);

        $allAttendance = [];
        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $halfDayCount = 0;
        $leaveCount = 0;
        $holidayCount = 0;
        $wfhCount = 0;
        $incompleteCount = 0;
        $totalHours = 0;
        $totalLateMins = 0;
        $totalBreakMins = 0;
        $overBreakDays = 0;
        $allowedBreak = \App\Services\StaffAttendanceCalculator::allowedMinutes($employee['break_allowed_minutes'] ?? null);

        foreach ($resolvedDays as $dateStr => $day) {
            $status = \App\Services\PayrollService::screenStatus($day);
            // Sundays, future days and days before joining are not attendance rows
            if (in_array($status, ['sunday', 'pending', 'not_joined'], true) && $day['source'] !== 'manual' && $day['source'] !== 'kiosk') {
                continue;
            }

            $entry = [
                'date' => $dateStr,
                'day' => date('D', strtotime($dateStr)),
                'clock_in' => $day['check_in'],
                'clock_out' => $day['check_out'],
                'working_hours' => 0,
                'late_minutes' => ($day['is_late'] || $day['status'] === 'HALF_DAY') ? (int)$day['late_minutes'] : 0,
                'status' => $status,
                'source' => $day['source'] === 'kiosk' ? 'face' : ($day['source'] === 'manual' ? 'manual' : 'none'),
                'incomplete' => $day['incomplete_log'] && in_array($day['status'], ['PRESENT', 'HALF_DAY'], true),
                'late_exempted' => $day['late_exempted'],
                'note' => $day['reason'],
                'break' => null,
            ];

            if ($day['source'] === 'manual') {
                $m = $day['manual'];
                $entry['working_hours'] = (float)($m['working_hours'] ?? 0);
                if ($entry['working_hours'] == 0 && $m['clock_in'] && $m['clock_out']) {
                    $entry['working_hours'] = max(0, round((strtotime($m['clock_out']) - strtotime($m['clock_in'])) / 3600, 2));
                }
                if ((int)($m['late_minutes'] ?? 0) > 0) {
                    $entry['late_minutes'] = (int)$m['late_minutes'];
                }
                $entry['break'] = \App\Services\StaffAttendanceCalculator::summarizeManual($m['break_minutes'] ?? null, $allowedBreak);
            } elseif ($day['source'] === 'kiosk') {
                $scans = $day['scans'];
                $entry['clock_in'] = $scans[0];
                $entry['clock_out'] = count($scans) > 1 ? $scans[count($scans) - 1] : null;
                if (count($scans) > 1) {
                    $entry['working_hours'] = round((strtotime($scans[count($scans) - 1]) - strtotime($scans[0])) / 3600, 2);
                }
                $entry['break'] = \App\Services\StaffAttendanceCalculator::summarizeBreaks($scans, $allowedBreak, $shiftEnd, $dateStr);
            }

            switch ($entry['status']) {
                case 'present': $presentCount++; break;
                case 'late': $lateCount++; break;
                case 'half_day': $halfDayCount++; break;
                case 'absent': $absentCount++; break;
                case 'on_leave': $leaveCount++; break;
                case 'holiday': $holidayCount++; break;
                case 'wfh': $wfhCount++; break;
            }
            if ($entry['incomplete']) $incompleteCount++;
            $totalHours += $entry['working_hours'];
            $totalLateMins += $entry['late_minutes'];
            if ($entry['break']) {
                $totalBreakMins += $entry['break']['total_break_minutes'];
                if ($entry['break']['exceeded']) $overBreakDays++;
            }

            $allAttendance[] = $entry;
        }

        // Reverse to show newest first
        $allAttendance = array_reverse($allAttendance);
        $totalDays = $configuredDays > 0 ? $configuredDays : count($allAttendance);

        $summary = [
            'total' => $totalDays,
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'absent_count' => $absentCount,
            'half_day_count' => $halfDayCount,
            'on_leave_count' => $leaveCount,
            'holiday_count' => $holidayCount,
            'wfh_count' => $wfhCount,
            'incomplete_count' => $incompleteCount,
            'total_hours' => $totalHours,
            'avg_hours' => $totalDays > 0 ? round($totalHours / max(1, $presentCount + $lateCount + $halfDayCount + $wfhCount), 1) : 0,
            'total_late_mins' => $totalLateMins,
            'total_break_mins' => $totalBreakMins,
            'over_break_days' => $overBreakDays,
            'allowed_break_mins' => $allowedBreak,
        ];

        // ──── Payroll data ────
        $payrollHistory = $db->select("
            SELECT pr.*, pi.working_days, pi.present_days, pi.late_days, pi.half_days, 
                   pi.absent_days, pi.gross_salary, pi.late_deduction, pi.absent_deduction, pi.net_salary
            FROM payroll_items pi
            JOIN payroll_runs pr ON pr.id = pi.payroll_run_id
            WHERE pi.employee_id = ?
            ORDER BY pr.created_at DESC
        ", [$id]);

        // Calculate salary breakdown
        $basicSalary = (float)$employee['salary_basic'];
        $hra = $basicSalary * 0.40;
        $medicalAllowance = $salary['medical_allowance'] ?? 0;
        $transportAllowance = $salary['transport_allowance'] ?? 0;
        $specialAllowance = $salary['special_allowance'] ?? 0;
        $pfDeduction = $salary['pf_deduction'] ?? ($basicSalary * 0.12);
        $esiDeduction = $salary['esi_deduction'] ?? 0;
        $taxDeduction = $salary['tax_deduction'] ?? 0;
        $totalAllowances = $hra + $medicalAllowance + $transportAllowance + $specialAllowance;
        $totalDeductions = $pfDeduction + $esiDeduction + $taxDeduction;
        $grossMonthly = $basicSalary + $totalAllowances;
        $netMonthly = $grossMonthly - $totalDeductions;

        return $this->view('staff/employee_show', compact(
            'employee', 'profile', 'salary', 'allAttendance',
            'departments', 'designations', 'attMonth', 'attYear', 'summary',
            'payrollHistory', 'basicSalary', 'hra', 'medicalAllowance',
            'transportAllowance', 'specialAllowance', 'pfDeduction',
            'esiDeduction', 'taxDeduction', 'totalAllowances',
            'totalDeductions', 'grossMonthly', 'netMonthly'
        ));
    }

    public function storeEmployee(): string
    {
        $db = Application::$app->db;
        $firstName = trim($this->request->input('first_name', ''));
        $lastName  = trim($this->request->input('last_name', ''));
        $email     = trim($this->request->input('email', ''));
        $deptId    = $this->request->input('department_id') ?: null;
        $desigId   = $this->request->input('designation_id') ?: null;
        $basicSal  = (float) $this->request->input('salary_basic', 35000);
        $minClockIn  = $this->request->input('min_clock_in', '09:00');
        $maxClockOut = $this->request->input('max_clock_out', '17:00');

        $codeCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM employees")['cnt'] ?? 0) + 101;
        $empCode   = 'EMP-' . $codeCount;

        $empId = (int) $db->insert('employees', [
            'tenant_id'      => \Core\Database::getTenantId() ?? 1,
            'emp_code'       => $empCode,
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'email'          => $email,
            'department_id'  => $deptId,
            'designation_id' => $desigId,
            'min_clock_in'   => $minClockIn,
            'max_clock_out'  => $maxClockOut,
            'joining_date'   => date('Y-m-d'),
            'salary_basic'   => $basicSal,
            'status'         => 'active',
            'created_at'     => now()
        ]);

        EmployeeSyncService::syncEmployeeToUser($empId);

        $this->flash('success', "Employee $firstName $lastName ($empCode) created successfully. Please register their face now.");
        return $this->redirect(url('attendance/register.php?employee_code=' . $empCode));
    }

    public function updateEmployee(string|int $id): string
    {
        $id = (int) $id;
        $db = Application::$app->db;
        $firstName  = trim($this->request->input('first_name', ''));
        $lastName   = trim($this->request->input('last_name', ''));
        $email      = trim($this->request->input('email', ''));
        $deptId     = $this->request->input('department_id') ?: null;
        $desigId    = $this->request->input('designation_id') ?: null;
        $basicSal   = (float) $this->request->input('salary_basic', 35000);
        $minClockIn  = $this->request->input('min_clock_in', '09:00');
        $maxClockOut = $this->request->input('max_clock_out', '17:00');
        $status     = $this->request->input('status', 'active');

        $db->update('employees', [
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'email'          => $email,
            'department_id'  => $deptId,
            'designation_id' => $desigId,
            'min_clock_in'   => $minClockIn,
            'max_clock_out'  => $maxClockOut,
            'salary_basic'   => $basicSal,
            'status'         => $status,
            'updated_at'     => now()
        ], 'id = ?', [$id]);

        EmployeeSyncService::syncEmployeeToUser($id);

        $this->flash('success', "Employee record updated successfully.");
        return $this->redirect(url('staff/employees/' . $id));
    }

    public function departments(): string
    {
        $db = Application::$app->db;
        $departments = $db->select("SELECT d.*, COUNT(e.id) as total_staff 
                                    FROM departments d 
                                    LEFT JOIN employees e ON e.department_id = d.id 
                                    GROUP BY d.id ORDER BY d.id ASC");
        return $this->view('staff/departments', compact('departments'));
    }

    public function storeDepartment(): string
    {
        $db = Application::$app->db;
        $name = trim($this->request->get('name', ''));
        $code = strtoupper(trim($this->request->get('code', 'DEP-' . rand(100, 999))));
        $desc = trim($this->request->get('description', ''));
        $addNext = !empty($_POST['_add_next']);

        if ($name !== '') {
            $db->insert('departments', [
                'tenant_id'   => \Core\Database::getTenantId(),
                'name'        => $name,
                'code'        => $code,
                'description' => $desc,
                'is_active'   => 1
            ]);
            $this->flash('success', "Department '{$name}' created successfully.");
        }
        if ($addNext) {
            return $this->redirect(url('staff/departments?add_next=1'));
        }
        return $this->redirect(url('staff/departments'));
    }

    public function updateDepartment(string $id): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();
        $name = trim($this->request->get('name', ''));
        $code = strtoupper(trim($this->request->get('code', '')));
        $desc = trim($this->request->get('description', ''));

        if ($name !== '') {
            $db->update('departments', [
                'name'        => $name,
                'code'        => $code,
                'description' => $desc,
            ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);
            $this->flash('success', "Department updated successfully.");
        }
        return $this->redirect(url('staff/departments'));
    }

    public function deleteDepartment(string $id): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();

        $dept = $db->selectOne("SELECT id, name FROM departments WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$dept) {
            $this->flash('error', 'Department not found.');
            return $this->redirect(url('staff/departments'));
        }

        // Check if any employees are assigned to this department
        $staffCount = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE department_id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (($staffCount['cnt'] ?? 0) > 0) {
            $this->flash('error', "Cannot delete '{$dept['name']}': {$staffCount['cnt']} staff member(s) are assigned to it. Reassign them first.");
            return $this->redirect(url('staff/departments'));
        }

        $db->query("DELETE FROM departments WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        $this->flash('success', "Department '{$dept['name']}' deleted successfully.");
        return $this->redirect(url('staff/departments'));
    }

    public function designations(): string
    {
        $db = Application::$app->db;
        $designations = $db->select("SELECT des.*, COUNT(e.id) as total_staff 
                                     FROM designations des 
                                     LEFT JOIN employees e ON e.designation_id = des.id 
                                     GROUP BY des.id ORDER BY des.id ASC");
        return $this->view('staff/designations', compact('designations'));
    }

    public function storeDesignation(): string
    {
        $db = Application::$app->db;
        $title = trim($this->request->post('title', ''));
        $code  = strtoupper(trim($this->request->post('code', 'DES-' . rand(100, 999))));
        $desc  = trim($this->request->post('description', ''));
        $addNext = !empty($_POST['_add_next']);

        if ($title !== '') {
            $db->insert('designations', [
                'tenant_id'   => \Core\Database::getTenantId(),
                'title'       => $title,
                'code'        => $code,
                'description' => $desc,
                'is_active'   => 1
            ]);
            $this->flash('success', "Designation '{$title}' created successfully.");
        }
        if ($addNext) {
            return $this->redirect(url('staff/designations?add_next=1'));
        }
        return $this->redirect(url('staff/designations'));
    }

    public function updateDesignation(string $id): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();
        $title = trim($this->request->post('title', ''));
        $code  = strtoupper(trim($this->request->post('code', '')));
        $desc  = trim($this->request->post('description', ''));

        if ($title !== '') {
            $db->update('designations', [
                'title'       => $title,
                'code'        => $code,
                'description' => $desc,
            ], 'id = ? AND tenant_id = ?', [(int)$id, $tenantId]);
            $this->flash('success', "Designation updated successfully.");
        }
        return $this->redirect(url('staff/designations'));
    }

    public function deleteDesignation(string $id): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();

        $desig = $db->selectOne("SELECT id, title FROM designations WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (!$desig) {
            $this->flash('error', 'Designation not found.');
            return $this->redirect(url('staff/designations'));
        }

        $staffCount = $db->selectOne("SELECT COUNT(*) as cnt FROM employees WHERE designation_id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        if (($staffCount['cnt'] ?? 0) > 0) {
            $this->flash('error', "Cannot delete '{$desig['title']}': {$staffCount['cnt']} staff member(s) are assigned to it. Reassign them first.");
            return $this->redirect(url('staff/designations'));
        }

        $db->query("DELETE FROM designations WHERE id = ? AND tenant_id = ?", [(int)$id, $tenantId]);
        $this->flash('success', "Designation '{$desig['title']}' deleted successfully.");
        return $this->redirect(url('staff/designations'));
    }

    public function attendanceCalendar(): string
    {
        $db = \Core\Application::$app->db;
        $month = $this->request->get('month', date('Y-m'));
        $employeeId = $this->request->get('employee_id');
        
        $employees = $db->select("SELECT * FROM employees WHERE status = 'active' ORDER BY first_name ASC");
        
        if (!$employeeId && !empty($employees)) {
            $employeeId = $employees[0]['id'];
        }

        $calData = [];
        $selectedEmp = null;
        $daysInMonth = 31;
        $firstDayOfWeek = 1;

        if ($employeeId) {
            $selectedEmp = (new \App\Services\PayrollService($db))->employee((int)$employeeId);
        }

        if ($selectedEmp) {
            if (!preg_match('/^\d{4}-\d{2}$/', (string)$month)) {
                $month = date('Y-m');
            }
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $daysInMonth = (int)date('t', strtotime($startDate));
            $firstDayOfWeek = date('w', strtotime($startDate)); // 0 (Sunday) to 6 (Saturday)

            // Same day engine and shift rules as payroll
            $shift = \App\Services\ShiftSettings::current();
            $rules = \App\Services\ShiftSettings::rulesFor($selectedEmp, $shift);
            $allowedBreak = \App\Services\StaffAttendanceCalculator::allowedMinutes($selectedEmp['break_allowed_minutes'] ?? null);
            $days = (new \App\Services\PayrollService($db))->resolveDays($selectedEmp, $startDate, $endDate, $shift);

            foreach ($days as $dateStr => $day) {
                $i = (int)substr($dateStr, 8, 2);
                $cell = [
                    'in' => '--:--',
                    'out' => '--:--',
                    'status' => \App\Services\PayrollService::screenStatus($day),
                    'incomplete' => $day['incomplete_log'] && in_array($day['status'], ['PRESENT', 'HALF_DAY'], true),
                    'note' => $day['reason'],
                ];
                if ($day['source'] === 'kiosk') {
                    $scans = $day['scans'];
                    $cell['in'] = date('h:i A', strtotime($scans[0]));
                    $cell['out'] = count($scans) > 1 ? date('h:i A', strtotime($scans[count($scans) - 1])) : '--:--';
                    $cell['break'] = \App\Services\StaffAttendanceCalculator::summarizeBreaks($scans, $allowedBreak, $rules['end'], $dateStr);
                } elseif ($day['source'] === 'manual') {
                    $m = $day['manual'];
                    $cell['in'] = $m['clock_in'] ? date('h:i A', strtotime($m['clock_in'])) : '--:--';
                    $cell['out'] = $m['clock_out'] ? date('h:i A', strtotime($m['clock_out'])) : '--:--';
                    $cell['break'] = \App\Services\StaffAttendanceCalculator::summarizeManual($m['break_minutes'] ?? null, $allowedBreak);
                }
                $calData[$i] = $cell;
            }
        }
        
        return $this->view('staff/attendance_calendar', compact('month', 'employeeId', 'employees', 'selectedEmp', 'calData', 'daysInMonth', 'firstDayOfWeek'));
    }

    public function attendance(): string
    {
        if ($this->request->get('view') === 'calendar') {
            return $this->attendanceCalendar();
        }

        $db = Application::$app->db;
        $date = (string) $this->request->get('date', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        
        // 1. Face Recognition Kiosk scans for the date, each resolved to exactly one employee
        $resolved = \App\Services\PayrollService::resolvedEmployeeSql('a');
        $faceLogsRaw = $db->select("
            SELECT x.*,
                   COALESCE(e.first_name, u.name, 'Staff Member') as first_name,
                   COALESCE(e.last_name, '') as last_name,
                   COALESCE(e.emp_code, u.employee_id, CONCAT('USR-', x.user_id)) as emp_code,
                   COALESCE(d.name, u.designation, 'General Staff') as department_name,
                   e.min_clock_in,
                   e.max_clock_out,
                   e.break_allowed_minutes,
                   COALESCE(u2.grace_period, u.grace_period) as user_grace_period
            FROM (
                SELECT a.id, a.user_id, a.check_in, a.confidence, a.image_path,
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

        // Group per resolved employee (unmatched scans stay per login user)
        $grouped = [];
        foreach ($faceLogsRaw as $log) {
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

        $shift = \App\Services\ShiftSettings::current();

        $faceLogs = [];
        foreach ($grouped as $data) {
            $scans = $data['scans'];
            $first = $scans[0];
            $last = $scans[count($scans) - 1];
            $clockOut = '--:--';
            $workingHours = '0 hrs 0 mins';

            // Same shift rules as payroll
            $rules = \App\Services\ShiftSettings::rulesFor($data, $shift);
            $status = \App\Services\ShiftSettings::classifyCheckIn($first, $rules);
            
            // Break time (paid — tracked and flagged, not deducted)
            $breakInfo = \App\Services\StaffAttendanceCalculator::summarizeBreaks(
                $scans,
                \App\Services\StaffAttendanceCalculator::allowedMinutes($data['break_allowed_minutes']),
                $rules['end'],
                $date
            );

            if (count($scans) > 1 && $breakInfo['on_break_since'] === null) {
                $clockOut = date('h:i A', strtotime($last));
                $diff = strtotime($last) - strtotime($first);
                $hours = floor($diff / 3600);
                $mins = floor(($diff % 3600) / 60);
                $workingHours = "{$hours} hrs {$mins} mins";
            }

            $faceLogs[] = [
                'id' => $data['id'],
                'employee_id' => $data['resolved_employee_id'] ? (int)$data['resolved_employee_id'] : null,
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
                'break' => $breakInfo,
                'late_exempted' => $data['exempted'] ? 1 : 0,
                'late_exemption_reason' => $data['reason']
            ];
        }

        // 2. Fetch manual staff attendance logs
        $manualLogs = $db->select("
            SELECT 
                l.id,
                l.employee_id,
                l.clock_in,
                l.clock_out,
                l.working_hours,
                l.break_minutes,
                l.status,
                l.late_exempted,
                l.late_exemption_reason,
                l.incomplete_log,
                1.0 as confidence,
                e.break_allowed_minutes,
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

        // Format manual logs times to 12-hour format
        foreach ($manualLogs as &$mLog) {
            if (!empty($mLog['clock_in'])) {
                $mLog['clock_in'] = date('h:i A', strtotime($mLog['clock_in']));
            }
            if (!empty($mLog['clock_out']) && $mLog['clock_out'] !== '--:--') {
                $mLog['clock_out'] = date('h:i A', strtotime($mLog['clock_out']));
            }
            $mLog['break'] = \App\Services\StaffAttendanceCalculator::summarizeManual(
                $mLog['break_minutes'],
                \App\Services\StaffAttendanceCalculator::allowedMinutes($mLog['break_allowed_minutes'])
            );
        }
        unset($mLog);

        $logs = array_merge($faceLogs, $manualLogs);
        $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");

        return $this->view('staff/attendance', compact('logs', 'date', 'employees'));
    }

    public function storeAttendance(): string
    {
        $employeeId = (int) $this->request->get('employee_id');
        $date       = (string) $this->request->get('date', date('Y-m-d'));
        $clockIn    = $this->request->get('clock_in', '09:00');
        $clockOut   = $this->request->get('clock_out', '17:00');
        $status     = (string) $this->request->get('status', 'present');
        $breakRaw   = $this->request->get('break_minutes', '');
        $breakMins  = ($breakRaw === '' || $breakRaw === null) ? null : max(0, (int) $breakRaw);

        if ($employeeId > 0) {
            try {
                // Shared with /payroll/attendance: real working hours, audit trail,
                // tenant scope and payroll runs of that month marked out of sync.
                \App\Services\PayrollService::saveManualAttendance($employeeId, $date, $clockIn, $clockOut, $status, $breakMins);
                $this->flash('success', "Attendance log recorded.");
            } catch (\Throwable $e) {
                $this->flash('error', 'Failed to save attendance: ' . $e->getMessage());
            }
        }
        return $this->redirect(url('staff/attendance?date=' . urlencode($date)));
    }

    public function lectureAttendance(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();

        $filterTeacher  = (int) $this->request->get('teacher_id', 0);
        $filterGroup    = (int) $this->request->get('main_group_id', 0);
        $filterStatus   = $this->request->get('status', '');
        $filterMonth    = $this->request->get('month', '');
        $filterWeek     = $this->request->get('week', '');
        $academicYearId = (int) $this->request->get('academic_year_id', 0);
        $dateFrom       = $this->request->get('date_from', '');
        $dateTo         = $this->request->get('date_to', '');
        $date           = $this->request->get('date', date('Y-m-d'));
        $viewMode       = $this->request->get('view', 'table');

        $academicYears = $db->select(
            "SELECT id, year_name, start_date, end_date, status 
             FROM academic_years WHERE tenant_id = ? ORDER BY id DESC",
            [$tenantId]
        );

        $currentYear = null;
        foreach ($academicYears as $ay) {
            if ($ay['status'] === 'current') {
                $currentYear = $ay;
                break;
            }
        }

        if (!$academicYearId && $currentYear) {
            $academicYearId = (int) $currentYear['id'];
        }

        $selectedAcademicYear = null;
        foreach ($academicYears as $ay) {
            if ((int)$ay['id'] === $academicYearId) {
                $selectedAcademicYear = $ay;
                break;
            }
        }

        if (!$dateFrom && !$dateTo && !$filterMonth && !$filterWeek) {
            if ($selectedAcademicYear && $selectedAcademicYear['start_date'] !== '0000-00-00') {
                $dateFrom = $selectedAcademicYear['start_date'];
                $dateTo   = $selectedAcademicYear['end_date'];
            } else {
                $dateFrom = $date;
                $dateTo   = $date;
            }
        }

        if ($filterMonth) {
            $dateFrom = $filterMonth . '-01';
            $dateTo   = date('Y-m-t', strtotime($dateFrom));
        }

        if ($filterWeek) {
            $weekStart = date('Y-m-d', strtotime($filterWeek . ' monday'));
            $weekEnd   = date('Y-m-d', strtotime($filterWeek . ' sunday'));
            if (!$dateFrom || $dateFrom === $date) {
                $dateFrom = $weekStart;
                $dateTo   = $weekEnd;
            }
        }

        $where   = ["ta.tenant_id = ?"];
        $params  = [$tenantId];

        if ($dateFrom && $dateTo) {
            $where[]  = "ta.attendance_date BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        } elseif ($date) {
            $where[]  = "ta.attendance_date = ?";
            $params[] = $date;
        }

        if ($filterTeacher) {
            $where[]  = "ta.user_id = ?";
            $params[] = $filterTeacher;
        }

        if ($filterStatus) {
            $where[]  = "ta.status = ?";
            $params[] = $filterStatus;
        }

        $whereSql = implode(' AND ', $where);

        $logs = $db->select("
            SELECT 
                ta.*,
                u.name as teacher_name,
                u.email as teacher_email,
                u.phone as teacher_phone,
                t.subject as timetable_subject,
                t.class as timetable_class,
                t.room as timetable_room,
                t.day_of_week as timetable_day,
                t.start_time as timetable_start,
                t.end_time as timetable_end,
                mg.name as main_group_name,
                mg.color as main_group_color
            FROM teacher_attendance ta
            JOIN users u ON ta.user_id = u.id
            LEFT JOIN timetables t ON t.teacher_name = u.name 
                AND t.day_of_week = DAYNAME(ta.attendance_date)
                AND TIME(t.start_time) = ta.lecture_time
                AND t.tenant_id = ta.tenant_id
            LEFT JOIN main_groups mg ON t.main_group_id = mg.id
            WHERE {$whereSql}
            ORDER BY ta.attendance_date DESC, ta.opened_at ASC
        ", $params);

        if ($filterGroup) {
            $logs = array_filter($logs, fn($l) => (int)($l['main_group_id'] ?? 0) === $filterGroup);
            $logs = array_values($logs);
        }

        $teachers = $db->select("
            SELECT u.id, u.name 
            FROM users u 
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.slug = 'teacher' AND u.tenant_id = ? AND u.deleted_at IS NULL
            ORDER BY u.name ASC
        ", [$tenantId]);

        $mainGroups = $db->select("
            SELECT id, name, color FROM main_groups WHERE tenant_id = ? AND is_active = 1 ORDER BY name ASC
        ", [$tenantId]);

        $totalLogs   = count($logs);
        $onTimeLogs  = count(array_filter($logs, fn($l) => $l['status'] === 'on_time'));
        $lateLogs    = count(array_filter($logs, fn($l) => $l['status'] === 'late'));

        $teacherStats = [];
        foreach ($logs as $l) {
            $tid = (int) $l['user_id'];
            if (!isset($teacherStats[$tid])) {
                $teacherStats[$tid] = [
                    'name'     => $l['teacher_name'],
                    'total'    => 0,
                    'on_time'  => 0,
                    'late'     => 0,
                    'subjects' => [],
                    'classes'  => [],
                ];
            }
            $teacherStats[$tid]['total']++;
            if ($l['status'] === 'on_time') {
                $teacherStats[$tid]['on_time']++;
            } else {
                $teacherStats[$tid]['late']++;
            }
            $subj = $l['timetable_subject'] ?? 'N/A';
            if (!in_array($subj, $teacherStats[$tid]['subjects'])) {
                $teacherStats[$tid]['subjects'][] = $subj;
            }
            $cls = $l['timetable_class'] ?? $l['main_group_name'] ?? 'N/A';
            if (!in_array($cls, $teacherStats[$tid]['classes'])) {
                $teacherStats[$tid]['classes'][] = $cls;
            }
        }
        uasort($teacherStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $groupStats = [];
        foreach ($logs as $l) {
            $gname = $l['main_group_name'] ?? 'Ungrouped';
            if (!isset($groupStats[$gname])) {
                $groupStats[$gname] = [
                    'name'     => $gname,
                    'color'    => $l['main_group_color'] ?? '#6366f1',
                    'total'    => 0,
                    'on_time'  => 0,
                    'late'     => 0,
                ];
            }
            $groupStats[$gname]['total']++;
            if ($l['status'] === 'on_time') {
                $groupStats[$gname]['on_time']++;
            } else {
                $groupStats[$gname]['late']++;
            }
        }
        uasort($groupStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $dailyStats = [];
        foreach ($logs as $l) {
            $d = $l['attendance_date'];
            if (!isset($dailyStats[$d])) {
                $dailyStats[$d] = ['date' => $d, 'total' => 0, 'on_time' => 0, 'late' => 0];
            }
            $dailyStats[$d]['total']++;
            if ($l['status'] === 'on_time') {
                $dailyStats[$d]['on_time']++;
            } else {
                $dailyStats[$d]['late']++;
            }
        }
        krsort($dailyStats);

        $subjectsList = [];
        foreach ($logs as $l) {
            $s = $l['timetable_subject'] ?? null;
            if ($s && !isset($subjectsList[$s])) {
                $subjectsList[$s] = ['name' => $s, 'total' => 0];
            }
            if ($s) {
                $subjectsList[$s]['total']++;
            }
        }
        uasort($subjectsList, fn($a, $b) => $b['total'] <=> $a['total']);

        $onTimePercent = $totalLogs > 0 ? round(($onTimeLogs / $totalLogs) * 100, 1) : 0;
        $latePercent   = $totalLogs > 0 ? round(($lateLogs / $totalLogs) * 100, 1) : 0;

        $dateRangeLabel = '';
        if ($filterMonth) {
            $dateRangeLabel = date('F Y', strtotime($filterMonth . '-01'));
        } elseif ($filterWeek) {
            $dateRangeLabel = 'Week of ' . date('M d', strtotime($filterWeek . ' monday')) . ' - ' . date('M d, Y', strtotime($filterWeek . ' sunday'));
        } elseif ($dateFrom && $dateTo && $dateFrom === $dateTo) {
            $dateRangeLabel = date('M d, Y', strtotime($dateFrom));
        } elseif ($dateFrom && $dateTo) {
            $dateRangeLabel = date('M d', strtotime($dateFrom)) . ' - ' . date('M d, Y', strtotime($dateTo));
        } elseif ($filterYear) {
            $dateRangeLabel = 'Year ' . $filterYear;
        } else {
            $dateRangeLabel = date('M d, Y', strtotime($date));
        }

        return $this->view('staff/lecture_attendance', compact(
            'logs', 'date', 'teachers', 'mainGroups', 'academicYears', 'academicYearId',
            'totalLogs', 'onTimeLogs', 'lateLogs', 'onTimePercent', 'latePercent',
            'teacherStats', 'groupStats', 'dailyStats', 'subjectsList',
            'filterTeacher', 'filterGroup', 'filterStatus', 'filterMonth', 'filterWeek',
            'dateFrom', 'dateTo', 'viewMode', 'dateRangeLabel'
        ));
    }

    public function leaves(): string
    {
        $db = Application::$app->db;
        $requests = $db->select("SELECT r.*, e.first_name, e.last_name, t.name as leave_type_name 
                                 FROM leave_requests r 
                                 JOIN employees e ON r.employee_id = e.id 
                                 LEFT JOIN leave_types t ON r.leave_type_id = t.id 
                                 ORDER BY r.id DESC");
        $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");
        $leaveTypes = $db->select("SELECT * FROM leave_types");

        return $this->view('staff/leaves', compact('requests', 'employees', 'leaveTypes'));
    }

    public function storeLeave(): string
    {
        $db = Application::$app->db;
        $empId   = (int) $this->request->get('employee_id');
        $typeId  = (int) $this->request->get('leave_type_id', 1);
        $start   = $this->request->get('start_date');
        $end     = $this->request->get('end_date');
        $reason  = trim($this->request->get('reason', ''));

        if ($empId > 0 && $start && $end && strtotime($start) !== false && strtotime($end) !== false) {
            if ($end < $start) {
                [$start, $end] = [$end, $start];
            }
            // Leave days = non-Sunday days in the range
            $days = 0;
            for ($t = strtotime($start); $t <= strtotime($end); $t = strtotime('+1 day', $t)) {
                if ((int)date('w', $t) !== 0) $days++;
            }
            $leaveId = (int) $db->insert('leave_requests', [
                'tenant_id'     => \Core\Database::getTenantId() ?? 1,
                'employee_id'   => $empId,
                'leave_type_id' => $typeId,
                'start_date'    => $start,
                'end_date'      => $end,
                'days'          => min(99.9, max(0.5, (float)$days)),
                'reason'        => $reason,
                'status'        => 'approved'
            ]);
            \App\Services\PayrollService::logAudit('leave_approved', 'leave_requests', $leaveId, null, "$start..$end", $reason ?: 'Leave recorded');
            for ($t = strtotime(date('Y-m-01', strtotime($start))); $t <= strtotime($end); $t = strtotime('+1 month', $t)) {
                \App\Services\PayrollService::markAffectedRunsOutOfSync(date('Y-m-d', $t));
            }
            $this->flash('success', "Leave request submitted & approved.");
        }
        return $this->redirect(url('staff/leaves'));
    }

    public function payroll(): string
    {
        $db = Application::$app->db;
        $runs = $db->select("SELECT * FROM payroll_runs ORDER BY id DESC");
        $employees = $db->select("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.status = 'active'");

        return $this->view('staff/payroll', compact('runs', 'employees'));
    }

    public function runPayroll(): string
    {
        $targetMonth = (string) $this->request->input('month', date('Y-m'));
        $monthYear = date('F Y', strtotime($targetMonth . '-01'));

        try {
            // Same engine as /payroll/runs (holidays, leaves, manual overrides, joining
            // date, configured working days). Saved as a draft for review, never auto-approved.
            $runId = (new \App\Services\PayrollService(Application::$app->db))->generateRun($targetMonth, auth_id());
            \App\Services\PayrollService::logAudit('run_payroll', 'payroll_runs', $runId, null, $monthYear, 'Monthly payroll executed (staff workspace)');
            $this->flash('success', "Payroll generated for $monthYear as a draft. Review and approve it before payout.");
        } catch (\Throwable $e) {
            $this->flash('error', "Failed to run payroll: " . $e->getMessage());
        }
        return $this->redirect(url('staff/payroll'));
    }

    public function payrollDetails(string $id): string
    {
        $db = Application::$app->db;
        $runId = (int) $id;
        
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [$runId]);
        if (!$run) {
            $this->flash('error', 'Payroll run not found.');
            return $this->redirect(url('staff/payroll'));
        }

        $items = $db->select("
            SELECT pi.*, e.first_name, e.last_name, e.emp_code, d.name as department_name
            FROM payroll_items pi
            JOIN employees e ON pi.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE pi.payroll_run_id = ?
        ", [$runId]);

        return $this->view('staff/payroll_details', compact('run', 'items'));
    }

    public function payslip(string $runId, string $empId): string
    {
        $db = Application::$app->db;
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
            $this->flash('error', 'Payslip not found.');
            return $this->redirect(url('staff/payroll'));
        }

        return $this->view('staff/payslip', compact('run', 'item'));
    }

    public function roles(): string
    {
        $roles = Role::allWithPermissionCount();
        
        $games = [
            [
                'title'       => 'Money Counting',
                'description' => 'Practice counting coins and bills in real-world shopping scenarios',
                'emoji'       => '🪙',
                'color'       => 'amber',
                'url'         => url('game/money-counting.html'),
                'tag'         => 'Math / Life Skills',
                'target_roles' => ['Teacher', 'Therapist', 'Parent'],
                'permission'  => 'view_game_money_counting',
            ],
            [
                'title'       => 'Safe vs Unsafe',
                'description' => 'Identify safe and unsafe situations to build safety awareness',
                'emoji'       => '🛡️',
                'color'       => 'red',
                'url'         => url('game/safe-vs-unsafe.html'),
                'tag'         => 'Safety Skills',
                'target_roles' => ['Teacher', 'Therapist', 'Parent', 'Staff'],
                'permission'  => 'view_game_safe_vs_unsafe',
            ],
            [
                'title'       => 'Safety Signs',
                'description' => 'Learn to recognize and understand important safety signs and symbols',
                'emoji'       => '⚠️',
                'color'       => 'orange',
                'url'         => url('game/safety-signs.html'),
                'tag'         => 'Safety Awareness',
                'target_roles' => ['Teacher', 'Therapist', 'Parent'],
                'permission'  => 'view_game_safety_signs',
            ],
            [
                'title'       => 'Sentence Builder',
                'description' => 'Drag and drop words to build grammatically correct sentences',
                'emoji'       => '📝',
                'color'       => 'blue',
                'url'         => url('game/sentence-builder.html'),
                'tag'         => 'Language Arts',
                'target_roles' => ['Teacher', 'Therapist'],
                'permission'  => 'view_game_sentence_builder',
            ],
            [
                'title'       => 'Shopping Store',
                'description' => 'Interactive grocery shopping game to practice math and life skills',
                'emoji'       => '🛒',
                'color'       => 'emerald',
                'url'         => url('game/shopping-store.html'),
                'tag'         => 'Life Skills / Math',
                'target_roles' => ['Teacher', 'Therapist', 'Parent'],
                'permission'  => 'view_game_shopping_store',
            ],
        ];
        
        return $this->view('staff/roles', compact('roles', 'games'));
    }

    public function storeRole(): string
    {
        $data = $this->request->getBody();
        $data['slug'] = strtolower(str_replace(' ', '_', $data['name']));
        $data['created_by'] = auth_id();

        unset($data['_csrf']);
        \App\Models\Role::create($data);
        \App\Models\ActivityLog::log('role_created', auth_id(), ['role_name' => $data['name']]);

        $this->flash('success', 'Role created.');
        return $this->redirect(url('staff/roles'));
    }

    public function updateRole(string $id): string
    {
        $data = $this->request->getBody();
        unset($data['_csrf'], $data['_method']);

        if (!empty($data)) {
            \App\Models\Role::update((int) $id, $data);
        }

        \App\Models\ActivityLog::log('role_updated', auth_id(), ['role_id' => $id]);

        $this->flash('success', 'Role updated.');
        return $this->redirect(url('staff/roles'));
    }

    public function deleteRole(string $id): string
    {
        \App\Models\Role::delete((int) $id);
        \App\Models\ActivityLog::log('role_deleted', auth_id(), ['role_id' => $id]);

        $this->flash('success', 'Role deleted.');
        return $this->redirect(url('staff/roles'));
    }

    public function users(): string
    {
        $db = Application::$app->db;
        $users = $db->select("SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') as role_names 
                              FROM users u 
                              LEFT JOIN user_roles ur ON u.id = ur.user_id 
                              LEFT JOIN roles r ON ur.role_id = r.id 
                              WHERE u.deleted_at IS NULL
                              GROUP BY u.id ORDER BY u.id DESC");
        return $this->view('staff/users', compact('users'));
    }

    public function settings(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();

        // Ensure academic_year_id column exists on shift_templates
        try { $db->query("ALTER TABLE `shift_templates` ADD COLUMN `academic_year_id` INT UNSIGNED NULL AFTER `tenant_id`"); } catch (\Throwable $e) {}

        $years = $db->select("SELECT * FROM academic_years WHERE tenant_id = ? ORDER BY id DESC", [$tenantId]);

        $selectedYearId = (int) $this->request->get('year_id', 0);
        if (!$selectedYearId && !empty($years)) {
            foreach ($years as $y) { if ($y['status'] === 'current') { $selectedYearId = (int) $y['id']; break; } }
            if (!$selectedYearId) $selectedYearId = (int) $years[0]['id'];
        }

        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE tenant_id = ? AND academic_year_id = ? ORDER BY id DESC LIMIT 1", [$tenantId, $selectedYearId]);
        if (!$shift) {
            // Fall back to global template (id=1) or create one for this year
            $src = \App\Services\ShiftSettings::current($tenantId);
            $shift = !empty($src['id']) ? $db->selectOne("SELECT * FROM shift_templates WHERE id = ?", [(int)$src['id']]) : false;
            if ($shift && $selectedYearId) {
                $shiftData = $shift;
                unset($shiftData['id']);
                $shiftData['academic_year_id'] = $selectedYearId;
                $shiftData['tenant_id'] = $tenantId;
                $db->insert('shift_templates', $shiftData);
                $shift = $db->selectOne("SELECT * FROM shift_templates WHERE tenant_id = ? AND academic_year_id = ? ORDER BY id DESC LIMIT 1", [$tenantId, $selectedYearId]);
            }
        }

        return $this->view('staff/settings', compact('shift', 'years', 'selectedYearId'));
    }

    public function saveSettings(): string
    {
        $db = Application::$app->db;
        $tenantId = \Core\Database::getTenantId();

        // Ensure academic_year_id column exists
        try { $db->query("ALTER TABLE `shift_templates` ADD COLUMN `academic_year_id` INT UNSIGNED NULL AFTER `tenant_id`"); } catch (\Throwable $e) {}

        $selectedYearId = (int) $this->request->input('academic_year_id', 0);

        $workingDaysJson = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStr = sprintf('%02d', $m);
            $workingDaysJson[$monthStr] = (int) $this->request->input('working_days_'.$monthStr, 0);
        }
        $workingDaysJsonEncoded = json_encode($workingDaysJson);

        $startTime = $this->request->input('start_time', '09:00:00');
        $graceMinutes = (int) $this->request->input('grace_minutes', 15);
        $lateAfter = $this->request->input('late_after', '09:16:00');
        $halfDayAfter = $this->request->input('half_day_after', '12:00:00');
        $lateLimitCount = (int) $this->request->input('late_limit_count', 3);
        $lateDeductionPercent = (float) $this->request->input('late_deduction_percent', 10.00);
        $halfDayDeductionPercent = (float) $this->request->input('half_day_deduction_percent', 50.00);
        $lecGraceMinutes = (int) $this->request->input('lec_grace_minutes', 5);

        $updateData = [
            'working_days_json' => $workingDaysJsonEncoded,
            'start_time' => $startTime,
            'grace_minutes' => $graceMinutes,
            'late_after' => $lateAfter,
            'half_day_after' => $halfDayAfter,
            'late_limit_count' => $lateLimitCount,
            'late_deduction_percent' => $lateDeductionPercent,
            'half_day_deduction_percent' => $halfDayDeductionPercent,
            'lec_grace_minutes' => $lecGraceMinutes,
            'break_allowed_minutes' => max(0, (int) $this->request->input('break_allowed_minutes', 30)),
        ];

        try {
            // Find existing shift for this year
            $existing = $db->selectOne("SELECT id FROM shift_templates WHERE tenant_id = ? AND academic_year_id = ? ORDER BY id DESC LIMIT 1", [$tenantId, $selectedYearId]);
            if ($existing) {
                $db->update('shift_templates', $updateData, 'id = ?', [(int)$existing['id']]);
            } else {
                $updateData['tenant_id'] = $tenantId;
                $updateData['academic_year_id'] = $selectedYearId;
                $updateData['name'] = 'Default Shift';
                $db->insert('shift_templates', $updateData);
            }

            \App\Services\ShiftSettings::flush();

            // Mark all draft/out_of_sync payroll runs so they regenerate
            $db->query("UPDATE payroll_runs SET status = 'out_of_sync' WHERE status IN ('draft', 'out_of_sync')");

            $this->flash('success', 'Staff settings saved. Existing draft payroll runs marked out of sync.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to save staff settings: ' . $e->getMessage());
        }
        return $this->redirect(url('staff/settings?year_id=' . $selectedYearId));
    }
}
