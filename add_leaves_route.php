<?php
$f = 'routes/web.php';
$c = file_get_contents($f);
$c = preg_replace('/(\$router->get\(\'\/academics\/attendance\',.*?\]\);)/', "$1\n\$router->get('/academics/attendance/leaves', [AttendanceController::class, 'leaves'], ['auth', 'tenant']);", $c);
file_put_contents($f, $c);
echo "Added leaves route\n";

$f2 = 'app/Controllers/AttendanceController.php';
$c2 = file_get_contents($f2);

// Let's also restore $pendingLeaves to index() just in case they want pending ones on index
$c2 = preg_replace('/(\$students = \[\];\s*\$attendanceMap = \[\];)/', "$1\n        \$pendingLeaves = \$db->select(\"SELECT l.*, s.first_name, s.last_name, s.class, s.section FROM leave_applications l JOIN students s ON l.student_id = s.id WHERE l.tenant_id = ? AND l.status = 'Pending' ORDER BY l.created_at DESC\", [\$tenantId]);", $c2);

// Add leaves() method
$leavesMethod = "
    public function leaves(): string
    {
        \$db = \$this->db();
        \$tenantId = \Core\Database::getTenantId();
        \$allLeaves = \$db->select(\"
            SELECT l.*, s.first_name, s.last_name, s.class, s.section 
            FROM leave_applications l
            JOIN students s ON l.student_id = s.id
            WHERE l.tenant_id = ?
            ORDER BY CASE WHEN l.status = 'Pending' THEN 1 ELSE 2 END, l.created_at DESC
        \", [\$tenantId]);
        
        return \$this->view('attendance/leaves', compact('allLeaves'));
    }
";
$c2 = preg_replace('/(public function save\(\): string)/', $leavesMethod . "\n    $1", $c2);

// Fix pendingLeaves passed to compact in index()
$c2 = preg_replace('/(compact\(\s*\'classes\', \'selectedClass\', \'selectedSection\', \'selectedDate\', \'students\', \'attendanceMap\')(\s*\))/', "$1, 'pendingLeaves'$2", $c2);

file_put_contents($f2, $c2);
echo "Updated AttendanceController\n";
