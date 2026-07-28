<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use App\Models\{User, Role};

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

        return $this->view('staff/employee_show', compact('employee', 'profile', 'salary', 'attendance'));
    }

    public function storeEmployee(): string
    {
        $db = Application::$app->db;
        $firstName = trim($this->request->get('first_name', ''));
        $lastName  = trim($this->request->get('last_name', ''));
        $email     = trim($this->request->get('email', ''));
        $deptId    = $this->request->get('department_id') ?: null;
        $desigId   = $this->request->get('designation_id') ?: null;
        $basicSal  = (float) $this->request->get('salary_basic', 35000);
        $minClockIn  = $this->request->get('min_clock_in', '09:00');
        $maxClockOut = $this->request->get('max_clock_out', '17:00');

        $codeCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM employees")['cnt'] ?? 0) + 101;
        $empCode   = 'EMP-' . $codeCount;

        $db->insert('employees', [
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
            'created_at'     => now()
        ]);

        $this->flash('success', "Employee $firstName $lastName ($empCode) created successfully.");
        return $this->redirect('/staff/employees');
    }

    public function updateEmployee(string|int $id): string
    {
        $id = (int) $id;
        $db = Application::$app->db;
        $firstName  = trim($this->request->get('first_name', ''));
        $lastName   = trim($this->request->get('last_name', ''));
        $email      = trim($this->request->get('email', ''));
        $deptId     = $this->request->get('department_id') ?: null;
        $desigId    = $this->request->get('designation_id') ?: null;
        $basicSal   = (float) $this->request->get('salary_basic', 35000);
        $minClockIn  = $this->request->get('min_clock_in', '09:00');
        $maxClockOut = $this->request->get('max_clock_out', '17:00');
        $status     = $this->request->get('status', 'active');

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
        ], ['id' => $id]);

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

    public function attendance(): string
    {
        $db = Application::$app->db;
        $date = $this->request->get('date', date('Y-m-d'));
        
        // 1. Fetch Face Recognition Kiosk Attendance logs for target date
        $faceLogs = $db->select("
            SELECT 
                a.id,
                a.employee_id,
                DATE_FORMAT(a.check_in, '%H:%i') as clock_in,
                '--:--' as clock_out,
                '8.00' as working_hours,
                COALESCE(a.status, 'present') as status,
                a.confidence,
                a.image_path,
                COALESCE(e.first_name, e.emp_code, 'Staff Member') as first_name,
                COALESCE(e.last_name, '') as last_name,
                COALESCE(e.emp_code, CONCAT('EMP-', a.employee_id)) as emp_code,
                d.name as department_name,
                'Face Recognition Kiosk' as source
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE (DATE(a.check_in) = ? OR a.attendance_date = ?)
            ORDER BY a.check_in DESC
        ", [$date, $date]);

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
        $monthYear = date('F Y');
        $employees = $db->select("SELECT * FROM employees WHERE status = 'active'");
        $totalGross = 0;
        $totalNet = 0;

        foreach ($employees as $e) {
            $totalGross += (float) $e['salary_basic'];
            $totalNet += (float) $e['salary_basic'];
        }

        $runId = $db->insert('payroll_runs', [
            'tenant_id'        => 1,
            'month_year'       => $monthYear,
            'total_gross'      => $totalGross,
            'total_deductions' => 0.00,
            'total_net'        => $totalNet,
            'status'           => 'approved'
        ]);

        foreach ($employees as $e) {
            $db->insert('payroll_items', [
                'payroll_run_id'  => $runId,
                'employee_id'     => $e['id'],
                'working_days'    => 26,
                'present_days'    => 26,
                'gross_salary'    => $e['salary_basic'],
                'late_deduction'  => 0.00,
                'absent_deduction'=> 0.00,
                'net_salary'      => $e['salary_basic']
            ]);
        }

        $this->flash('success', "Payroll generated and approved for $monthYear.");
        return $this->redirect('/staff/payroll');
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
                              GROUP BY u.id ORDER BY u.id DESC");
        return $this->view('staff/users', compact('users'));
    }

    public function settings(): string
    {
        $db = Application::$app->db;
        $shift = $db->selectOne("SELECT * FROM shift_templates WHERE id = 1");
        return $this->view('staff/settings', compact('shift'));
    }
}
