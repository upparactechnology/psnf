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
        $departments = $db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = $db->select("SELECT * FROM designations WHERE is_active = 1");

        return $this->view('staff/employees', compact('employees', 'departments', 'designations', 'search', 'deptId'));
    }

    public function showEmployee(string|int $id): string
    {
        $id = (int) $id;
        $db = Application::$app->db;
        $employee = $db->selectOne("SELECT e.*, d.name as department_name, des.title as designation_title 
                                   FROM employees e 
                                   LEFT JOIN departments d ON e.department_id = d.id 
                                   LEFT JOIN designations des ON e.designation_id = des.id 
                                   WHERE e.id = ?", [$id]);

        if (!$employee) {
            $this->flash('error', 'Employee record not found.');
            return $this->redirect('/staff/employees');
        }

        $profile = $db->selectOne("SELECT * FROM employee_profiles WHERE employee_id = ?", [$id]);
        $salary  = $db->selectOne("SELECT * FROM salary_structures WHERE employee_id = ?", [$id]);
        $attendance = $db->select("SELECT * FROM staff_attendance_logs WHERE employee_id = ? ORDER BY date DESC LIMIT 15", [$id]);
        $departments = $db->select("SELECT * FROM departments WHERE is_active = 1");
        $designations = $db->select("SELECT * FROM designations WHERE is_active = 1");

        return $this->view('staff/employee_show', compact('employee', 'profile', 'salary', 'attendance', 'departments', 'designations'));
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

        $deptName = null;
        if ($deptId) {
            $d = $db->selectOne("SELECT name FROM departments WHERE id = ?", [$deptId]);
            if ($d) $deptName = $d['name'];
        }
        $desigTitle = null;
        if ($desigId) {
            $des = $db->selectOne("SELECT title FROM designations WHERE id = ?", [$desigId]);
            if ($des) $desigTitle = $des['title'];
        }

        $empId = (int) $db->insert('employees', [
            'tenant_id'      => 1,
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
            'employee_code'  => $empCode,
            'name'           => trim("$firstName $lastName"),
            'department'     => $deptName,
            'designation'    => $desigTitle,
            'created_at'     => now()
        ]);

        EmployeeSyncService::syncEmployeeToUser($empId);

        $this->flash('success', "Employee $firstName $lastName ($empCode) created successfully. Please register their face now.");
        return $this->redirect('/attendance/register.php?employee_code=' . $empCode);
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

        $deptName = null;
        if ($deptId) {
            $d = $db->selectOne("SELECT name FROM departments WHERE id = ?", [$deptId]);
            if ($d) $deptName = $d['name'];
        }
        $desigTitle = null;
        if ($desigId) {
            $des = $db->selectOne("SELECT title FROM designations WHERE id = ?", [$desigId]);
            if ($des) $desigTitle = $des['title'];
        }

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
            'employee_code'  => $db->selectOne("SELECT emp_code FROM employees WHERE id = ?", [$id])['emp_code'] ?? null,
            'name'           => trim("$firstName $lastName"),
            'department'     => $deptName,
            'designation'    => $desigTitle,
            'updated_at'     => now()
        ], 'id = ?', [$id]);

        EmployeeSyncService::syncEmployeeToUser($id);

        $this->flash('success', "Employee record updated successfully.");
        return $this->redirect('/staff/employees/' . $id);
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

        if ($name !== '') {
            $db->insert('departments', [
                'tenant_id'   => 1,
                'name'        => $name,
                'code'        => $code,
                'description' => $desc,
                'is_active'   => 1
            ]);
            $this->flash('success', "Department $name created successfully.");
        }
        return $this->redirect('/staff/departments');
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
        $title = trim($this->request->get('title', ''));
        $code  = strtoupper(trim($this->request->get('code', 'DES-' . rand(100, 999))));
        $desc  = trim($this->request->get('description', ''));

        if ($title !== '') {
            $db->insert('designations', [
                'tenant_id'   => 1,
                'title'       => $title,
                'code'        => $code,
                'description' => $desc,
                'is_active'   => 1
            ]);
            $this->flash('success', "Designation $title created successfully.");
        }
        return $this->redirect('/staff/designations');
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
            $selectedEmp = $db->selectOne("SELECT * FROM employees WHERE id = ?", [$employeeId]);
            
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)date('m', strtotime($startDate)), (int)date('Y', strtotime($startDate)));
            $firstDayOfWeek = date('w', strtotime($startDate)); // 0 (Sunday) to 6 (Saturday)
            
            $faceLogs = $db->select("
                SELECT DATE(check_in) as log_date, MIN(check_in) as min_ci, MAX(check_in) as max_ci, COUNT(id) as scans
                FROM attendance
                WHERE (employee_id = ? OR user_id = (SELECT user_id FROM employees WHERE id = ?))
                  AND DATE(check_in) BETWEEN ? AND ?
                GROUP BY DATE(check_in)
            ", [$employeeId, $employeeId, $startDate, $endDate]);
            
            $manualLogs = $db->select("
                SELECT date as log_date, clock_in as min_ci, clock_out as max_ci, status
                FROM staff_attendance_logs
                WHERE employee_id = ? AND date BETWEEN ? AND ?
            ", [$employeeId, $startDate, $endDate]);
            
            $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
            $empShiftStart = !empty($selectedEmp['min_clock_in']) ? $selectedEmp['min_clock_in'] : ($shift['start_time'] ?? '09:00:00');
            $grace = !empty($selectedEmp['min_clock_in']) ? 10 : (int)($shift['grace_minutes'] ?? 15);
            $latePenaltyTime = date('H:i:s', strtotime($empShiftStart) + ($grace * 60));
            $halfDayTime = !empty($selectedEmp['min_clock_in']) ? date('H:i:s', strtotime($empShiftStart) + (3 * 3600)) : ($shift['half_day_after'] ?? '12:00:00');

            $logMap = [];
            
            foreach ($faceLogs as $fl) {
                $timeOnly = date('H:i:s', strtotime($fl['min_ci']));
                $status = 'present';
                if ($timeOnly > $halfDayTime) {
                    $status = 'half_day';
                } elseif ($timeOnly > $latePenaltyTime) {
                    $status = 'late';
                }
                
                $logMap[$fl['log_date']] = [
                    'in' => date('h:i A', strtotime($fl['min_ci'])),
                    'out' => $fl['scans'] > 1 ? date('h:i A', strtotime($fl['max_ci'])) : '--:--',
                    'status' => $status
                ];
            }
            
            foreach ($manualLogs as $ml) {
                $logMap[$ml['log_date']] = [
                    'in' => $ml['min_ci'] ? date('h:i A', strtotime($ml['min_ci'])) : '--:--',
                    'out' => ($ml['max_ci'] && $ml['max_ci'] !== '--:--') ? date('h:i A', strtotime($ml['max_ci'])) : '--:--',
                    'status' => $ml['status']
                ];
            }
            
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dateStr = $month . '-' . sprintf('%02d', $i);
                $isSunday = (date('N', strtotime($dateStr)) == 7);
                
                if (isset($logMap[$dateStr])) {
                    $calData[$i] = $logMap[$dateStr];
                } else {
                    $calData[$i] = [
                        'in' => '--:--',
                        'out' => '--:--',
                        'status' => $isSunday ? 'sunday' : 'absent'
                    ];
                    
                    if ($dateStr > date('Y-m-d')) {
                        $calData[$i]['status'] = 'pending';
                    }
                }
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
        $date = $this->request->get('date', date('Y-m-d'));
        
        // 1. Fetch Face Recognition Kiosk Attendance logs for target date
        $faceLogsRaw = $db->select("
            SELECT 
                a.id,
                COALESCE(a.user_id, a.employee_id) as employee_id,
                a.check_in,
                a.attendance_date,
                a.confidence,
                a.image_path,
                COALESCE(u.name, e.first_name, e.emp_code, 'Staff Member') as first_name,
                COALESCE(e.last_name, '') as last_name,
                COALESCE(u.employee_id, e.emp_code, CONCAT('EMP-', COALESCE(a.user_id, a.employee_id))) as emp_code,
                COALESCE(u.designation, d.name, 'General Staff') as department_name
            FROM attendance a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE (DATE(a.check_in) = ? OR a.attendance_date = ?)
            ORDER BY a.check_in ASC
        ", [$date, $date]);

        // Group by employee_id to find first (check-in) and last (check-out)
        $grouped = [];
        foreach ($faceLogsRaw as $log) {
            $empId = $log['employee_id'];
            if (!isset($grouped[$empId])) {
                $grouped[$empId] = [
                    'id' => $log['id'],
                    'employee_id' => $empId,
                    'first_name' => $log['first_name'],
                    'last_name' => $log['last_name'],
                    'emp_code' => $log['emp_code'],
                    'department_name' => $log['department_name'],
                    'min_check_in' => $log['check_in'],
                    'max_check_in' => $log['check_in'],
                    'confidence' => $log['confidence'],
                    'image_path' => $log['image_path'],
                    'scan_count' => 1
                ];
            } else {
                $grouped[$empId]['max_check_in'] = $log['check_in'];
                $grouped[$empId]['scan_count']++;
            }
        }

        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        $empShiftStart = $shift['start_time'] ?? '09:00:00';
        $grace = (int)($shift['grace_minutes'] ?? 15);
        $latePenaltyTime = date('H:i:s', strtotime($empShiftStart) + ($grace * 60));
        $halfDayTime = $shift['half_day_after'] ?? '12:00:00';

        $faceLogs = [];
        foreach ($grouped as $empId => $data) {
            $clockIn = date('h:i A', strtotime($data['min_check_in']));
            $clockOut = '--:--';
            $workingHours = '0 hrs 0 mins';
            
            $timeOnly = date('H:i:s', strtotime($data['min_check_in']));
            $status = 'present';
            
            if ($timeOnly > $halfDayTime) {
                $status = 'half_day';
            } elseif ($timeOnly > $latePenaltyTime) {
                $status = 'late';
            }
            
            if ($data['scan_count'] > 1) {
                $clockOut = date('h:i A', strtotime($data['max_check_in']));
                $diff = strtotime($data['max_check_in']) - strtotime($data['min_check_in']);
                $hours = floor($diff / 3600);
                $mins = floor(($diff % 3600) / 60);
                $workingHours = "{$hours} hrs {$mins} mins";
            }

            $faceLogs[] = [
                'id' => $data['id'],
                'employee_id' => $empId,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'working_hours' => $workingHours,
                'status' => $status,
                'confidence' => $data['confidence'],
                'image_path' => $data['image_path'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'emp_code' => $data['emp_code'],
                'department_name' => $data['department_name'],
                'source' => 'Face Recognition Kiosk'
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
                l.status,
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

        // Format manual logs times to 12-hour format
        foreach ($manualLogs as &$mLog) {
            if (!empty($mLog['clock_in'])) {
                $mLog['clock_in'] = date('h:i A', strtotime($mLog['clock_in']));
            }
            if (!empty($mLog['clock_out']) && $mLog['clock_out'] !== '--:--') {
                $mLog['clock_out'] = date('h:i A', strtotime($mLog['clock_out']));
            }
        }

        $logs = array_merge($faceLogs, $manualLogs);
        $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");

        return $this->view('staff/attendance', compact('logs', 'date', 'employees'));
    }

    public function storeAttendance(): string
    {
        $db = Application::$app->db;
        $employeeId = (int) $this->request->get('employee_id');
        $date       = $this->request->get('date', date('Y-m-d'));
        $clockIn    = $this->request->get('clock_in', '09:00');
        $clockOut   = $this->request->get('clock_out', '17:00');
        $status     = $this->request->get('status', 'present');

        if ($employeeId > 0) {
            $db->query("INSERT INTO staff_attendance_logs (tenant_id, employee_id, date, clock_in, clock_out, working_hours, status)
                        VALUES (1, ?, ?, ?, ?, 8.0, ?)
                        ON DUPLICATE KEY UPDATE clock_in = VALUES(clock_in), clock_out = VALUES(clock_out), status = VALUES(status)",
                       [$employeeId, $date, $clockIn, $clockOut, $status]);
            $this->flash('success', "Attendance log recorded.");
        }
        return $this->redirect('/staff/attendance?date=' . $date);
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

        if ($empId > 0 && $start && $end) {
            $db->insert('leave_requests', [
                'tenant_id'     => 1,
                'employee_id'   => $empId,
                'leave_type_id' => $typeId,
                'start_date'    => $start,
                'end_date'      => $end,
                'days'          => 1.0,
                'reason'        => $reason,
                'status'        => 'approved'
            ]);
            $this->flash('success', "Leave request submitted & approved.");
        }
        return $this->redirect('/staff/leaves');
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
        $db = Application::$app->db;
        $targetMonth = $this->request->input('month', date('Y-m'));
        $monthYear = date('F Y', strtotime($targetMonth . '-01'));
        
        try {
            // Delete existing run for this month
            $existingRun = $db->selectOne("SELECT id FROM payroll_runs WHERE month_year = ?", [$monthYear]);
            if ($existingRun) {
                $db->delete('payroll_items', "payroll_run_id = ?", [$existingRun['id']]);
                $db->delete('payroll_runs', "id = ?", [$existingRun['id']]);
            }

            // Fetch policy
            $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
            $lateLimit = (int) ($shift['late_limit_count'] ?? 3);
            $lateDeductionPercent = (float) ($shift['late_deduction_percent'] ?? 10.00);
            $halfDayDeductionPercent = (float) ($shift['half_day_deduction_percent'] ?? 50.00);

            // Month range and dynamic working days (exclude Sundays)
            $startDate = date('Y-m-01', strtotime($targetMonth . '-01'));
            $endDate = date('Y-m-t', strtotime($targetMonth . '-01'));
            
            $month = date('m', strtotime($startDate));
            $year = date('Y', strtotime($startDate));
            
            $workingDaysMap = json_decode($shift['working_days_json'] ?? '{}', true) ?: [];
            $workingDays = (int) ($workingDaysMap[$month] ?? 0);
            
            if ($workingDays <= 0) {
                $month = date('m');
                $year = date('Y');
                $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                for ($i = 1; $i <= $totalDaysInMonth; $i++) {
                    $dateStr = "$year-$month-" . sprintf('%02d', $i);
                    if (date('N', strtotime($dateStr)) != 7) { // 7 is Sunday
                        $workingDays++;
                    }
                }
            }
            
            // Fallback just in case
            if ($workingDays == 0) $workingDays = 26;

            $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");
            
            $totalGross = 0;
            $totalNet = 0;
            $totalDeductions = 0;

            $items = [];
            foreach ($employees as $e) {
                $basic = (float) $e['salary_basic'];
                $perDaySalary = $basic / $workingDays;
                
                // Determine shift settings for employee (template vs customized)
                $empShiftStart = !empty($e['min_clock_in']) ? $e['min_clock_in'] : ($shift['start_time'] ?? '09:00:00');
                $grace = !empty($e['min_clock_in']) ? 10 : (int)($shift['grace_minutes'] ?? 15);
                
                $startTimeSecs = strtotime($empShiftStart);
                $latePenaltyTime = date('H:i:s', $startTimeSecs + ($grace * 60));
                $halfDayTime = !empty($e['min_clock_in']) ? date('H:i:s', $startTimeSecs + (3 * 3600)) : ($shift['half_day_after'] ?? '12:00:00');

                // Query month's logs (unique days)
                $logs = $db->select("
                    SELECT check_in FROM attendance 
                    WHERE user_id = (SELECT id FROM users WHERE employee_id = ? LIMIT 1) 
                    AND (DATE(check_in) BETWEEN ? AND ?)
                ", [$e['employee_id'], $startDate, $endDate]);

                // Also fetch fallback if attendance was marked by raw employee_id earlier
                if (empty($logs)) {
                     $logs = $db->select("
                        SELECT check_in FROM attendance 
                        WHERE employee_id = ? AND (DATE(check_in) BETWEEN ? AND ?)
                    ", [$e['id'], $startDate, $endDate]);
                }

                $lateCount = 0;
                $halfDayCount = 0;
                $presentDays = count($logs);

                foreach ($logs as $log) {
                    if (empty($log['check_in'])) continue;
                    $time = date('H:i:s', strtotime($log['check_in']));
                    if ($time > $halfDayTime) {
                        $halfDayCount++;
                    } elseif ($time > $latePenaltyTime) {
                        $lateCount++;
                    }
                }

                // 1. Absent Deduction (Full days missed)
                $absentDays = $workingDays - $presentDays;
                if ($absentDays < 0) $absentDays = 0; // Worked overtime/Sundays
                $absentDeduction = $absentDays * $perDaySalary;

                // 2. Late Deduction
                $lateDeduction = 0.00;
                if ($lateCount >= $lateLimit) {
                    $lateDeduction = round(($basic * ($lateDeductionPercent / 100)), 2);
                }

                // 3. Half-Day Deduction
                $halfDayDeduction = round(($perDaySalary * ($halfDayDeductionPercent / 100) * $halfDayCount), 2);
                
                $totalEmpDeduction = $absentDeduction + $lateDeduction + $halfDayDeduction;

                if ($totalEmpDeduction > $basic) {
                    $totalEmpDeduction = $basic;
                }

                $netSalary = $basic - $totalEmpDeduction;

                $totalGross += $basic;
                $totalDeductions += $totalEmpDeduction;
                $totalNet += $netSalary;

                $items[] = [
                    'employee_id' => $e['id'],
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'gross_salary' => $basic,
                    'late_deduction' => $lateDeduction,
                    'absent_deduction' => $absentDeduction + $halfDayDeduction, // Store total absent + halfday here
                    'net_salary' => $netSalary
                ];
            }

            $runId = $db->insert('payroll_runs', [
                'tenant_id'        => 1,
                'month_year'       => $monthYear,
                'total_gross'      => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net'        => $totalNet,
                'status'           => 'approved'
            ]);

            foreach ($items as $item) {
                $db->insert('payroll_items', [
                    'payroll_run_id'  => $runId,
                    'employee_id'     => $item['employee_id'],
                    'working_days'    => 26,
                    'present_days'    => $item['present_days'],
                    'gross_salary'    => $item['gross_salary'],
                    'late_deduction'  => $item['late_deduction'],
                    'absent_deduction'=> $item['absent_deduction'],
                    'net_salary'      => $item['net_salary']
                ]);
            }

            $this->flash('success', "Payroll generated and approved for $monthYear dynamically based on late attendance thresholds.");
        } catch (\Throwable $e) {
            $this->flash('error', "Failed to run payroll: " . $e->getMessage());
        }
        return $this->redirect('/staff/payroll');
    }

    public function payrollDetails(string $id): string
    {
        $db = Application::$app->db;
        $runId = (int) $id;
        
        $run = $db->selectOne("SELECT * FROM payroll_runs WHERE id = ?", [$runId]);
        if (!$run) {
            $this->flash('error', 'Payroll run not found.');
            return $this->redirect('/staff/payroll');
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
            return $this->redirect('/staff/payroll');
        }

        return $this->view('staff/payslip', compact('run', 'item'));
    }

    public function roles(): string
    {
        $roles = Role::allWithPermissionCount();
        return $this->view('staff/roles', compact('roles'));
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
        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        return $this->view('staff/settings', compact('shift'));
    }

    public function saveSettings(): string
    {
        $db = Application::$app->db;
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

        try {
            $db->update('shift_templates', [
                'working_days_json' => $workingDaysJsonEncoded,
                'start_time' => $startTime,
                'grace_minutes' => $graceMinutes,
                'late_after' => $lateAfter,
                'half_day_after' => $halfDayAfter,
                'late_limit_count' => $lateLimitCount,
                'late_deduction_percent' => $lateDeductionPercent,
                'half_day_deduction_percent' => $halfDayDeductionPercent,
                'lec_grace_minutes' => $lecGraceMinutes,
            ], 'id = 1');

            $this->flash('success', 'Staff shift policies and payroll rules saved successfully.');
        } catch (\Throwable $e) {
            $this->flash('error', 'Failed to save staff settings: ' . $e->getMessage());
        }
        return $this->redirect('/staff/settings');
    }
}
