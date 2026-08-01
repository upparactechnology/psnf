<?php
$file = 'app/Controllers/StaffAppController.php';
$content = file_get_contents($file);

$classAttendanceMethod = <<<PHP
    public function getClassAttendance(): string
    {
        \$userId = \$this->getAuthenticatedUserId();
        if (!\$userId) {
            return \$this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        \$today = date('Y-m-d');
        
        // Fetch all active students, left join with today's attendance
        \$records = \$this->db()->select("
            SELECT s.id, s.first_name, s.last_name, s.photo, s.class, 
                   COALESCE(a.status, 'pending') as status
            FROM students s 
            LEFT JOIN attendance a ON a.student_id = s.id AND a.date = ?
            WHERE s.deleted_at IS NULL AND s.is_active = 1
            ORDER BY s.class ASC, s.first_name ASC
        ", [\$today]);

        \$students = [];
        \$classes = [];
        
        foreach (\$records as \$r) {
            \$className = \$r['class'] ?: 'Unassigned';
            if (!in_array(\$className, \$classes)) {
                \$classes[] = \$className;
            }
            
            \$students[] = [
                'id' => \$r['id'],
                'name' => \$r['first_name'] . ' ' . \$r['last_name'],
                'class' => \$className,
                'status' => \$r['status'], // 'present', 'absent', 'late', 'pending'
                'photo' => \$r['photo'] ? url('uploads/' . \$r['photo']) : null
            ];
        }

        return \$this->respondJson([
            'success' => true,
            'classes' => \$classes,
            'students' => \$students
        ]);
    }

    public function markStudentAttendance(): string
    {
        \$userId = \$this->getAuthenticatedUserId();
        if (!\$userId) {
            return \$this->respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        \$input = json_decode(file_get_contents('php://input'), true);
        \$studentId = \$input['student_id'] ?? null;
        \$status = \$input['status'] ?? null; // 'present' or 'absent'

        if (!\$studentId || !\$status) {
            return \$this->respondJson(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        \$today = date('Y-m-d');
        \$existing = \$this->db()->selectOne("SELECT id FROM attendance WHERE student_id = ? AND date = ?", [\$studentId, \$today]);

        if (\$existing) {
            \$this->db()->update('attendance', [
                'status' => \$status,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => \$userId
            ], "id = ?", [\$existing['id']]);
        } else {
            \$stu = \$this->db()->selectOne("SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?", [\$studentId]);
            if (\$stu) {
                \$this->db()->insert('attendance', [
                    'uuid' => \Core\Str::uuid(),
                    'tenant_id' => \$stu['tenant_id'],
                    'school_id' => \$stu['school_id'],
                    'branch_id' => \$stu['branch_id'],
                    'student_id' => \$studentId,
                    'date' => \$today,
                    'status' => \$status,
                    'remarks' => 'Marked by teacher',
                    'is_medical' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => \$userId
                ]);
            }
        }

        return \$this->respondJson(['success' => true]);
    }

PHP;

// Remove getEarlyStudents
$content = preg_replace('/public function getEarlyStudents\(\): string\s*\{.*?(?=public function getSummary)/s', $classAttendanceMethod, $content);

file_put_contents($file, $content);
echo "StaffAppController updated.\\n";

// Update api.php
$apiFile = 'routes/api.php';
$apiContent = file_get_contents($apiFile);

$apiContent = str_replace(
    "\$router->get('/api/v1/staff/early-students',    [App\Controllers\StaffAppController::class, 'getEarlyStudents'], ['auth']);",
    "\$router->get('/api/v1/staff/class-attendance',    [App\Controllers\StaffAppController::class, 'getClassAttendance'], ['auth']);\n\$router->post('/api/v1/staff/attendance/mark-student', [App\Controllers\StaffAppController::class, 'markStudentAttendance'], ['auth']);",
    $apiContent
);
file_put_contents($apiFile, $apiContent);
echo "api.php updated.";
