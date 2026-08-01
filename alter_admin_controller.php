<?php
$file = 'app/Controllers/AttendanceController.php';
$content = file_get_contents($file);

// 1. Fetch pending leave requests
$fetchRequestsLogic = <<<PHP
        \$pendingLeaves = \$db->select(
            "SELECT l.*, s.first_name, s.last_name, s.class, s.section 
             FROM leave_applications l
             JOIN students s ON l.student_id = s.id
             WHERE l.tenant_id = ? AND l.status = 'Pending'
             ORDER BY l.created_at DESC",
            [\$tenantId]
        );

        return \$this->view('attendance/index', compact(
            'classes', 'selectedClass', 'selectedSection', 'selectedDate', 'students', 'attendanceMap', 'pendingLeaves'
        ));
PHP;

$content = preg_replace('/return \$this->view\(\'attendance\/index\', compact\([\s\S]*?\)\);/s', $fetchRequestsLogic, $content);

// 2. Add methods to approve and reject
$leaveMethods = <<<PHP
    public function approveLeave(string \$id): void
    {
        \$db = \$this->db();
        \$tenantId = \Core\Database::getTenantId();
        
        \$leave = \$db->selectOne("SELECT * FROM leave_applications WHERE id = ? AND tenant_id = ?", [\$id, \$tenantId]);
        if (!\$leave) {
            \$this->flash('error', 'Leave request not found.');
            \$this->redirect('/academics/attendance');
            return;
        }

        \$db->update('leave_applications', ['status' => 'Approved', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [\$id]);

        // Insert into attendance table
        \$startTs = strtotime(\$leave['start_date']);
        \$endTs = strtotime(\$leave['end_date']);
        \$studentId = \$leave['student_id'];
        
        for (\$current = \$startTs; \$current <= \$endTs; \$current += 86400) {
            \$dateStr = date('Y-m-d', \$current);
            // Check if exists
            \$existingAtt = \$db->selectOne("SELECT id FROM attendance WHERE student_id = ? AND date = ?", [\$studentId, \$dateStr]);
            
            if (\$existingAtt) {
                \$db->update('attendance', [
                    'status' => 'absent',
                    'remarks' => 'Leave Approved: ' . \$leave['reason'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => auth_id()
                ], "id = ?", [\$existingAtt['id']]);
            } else {
                \$stu = \$db->selectOne("SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?", [\$studentId]);
                if (\$stu) {
                    \$db->insert('attendance', [
                        'uuid' => \Core\Str::uuid(),
                        'tenant_id' => \$stu['tenant_id'],
                        'school_id' => \$stu['school_id'],
                        'branch_id' => \$stu['branch_id'],
                        'student_id' => \$studentId,
                        'date' => \$dateStr,
                        'status' => 'absent',
                        'remarks' => 'Leave Approved: ' . \$leave['reason'],
                        'is_medical' => (\$leave['leave_type'] === 'Medical' ? 1 : 0),
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => auth_id()
                    ]);
                }
            }
        }
        
        \$this->flash('success', 'Leave request approved and attendance updated.');
        \$this->redirect('/academics/attendance');
    }

    public function rejectLeave(string \$id): void
    {
        \$db = \$this->db();
        \$tenantId = \Core\Database::getTenantId();
        
        \$leave = \$db->selectOne("SELECT * FROM leave_applications WHERE id = ? AND tenant_id = ?", [\$id, \$tenantId]);
        if (!\$leave) {
            \$this->flash('error', 'Leave request not found.');
            \$this->redirect('/academics/attendance');
            return;
        }

        \$db->update('leave_applications', ['status' => 'Rejected', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [\$id]);
        
        \$this->flash('success', 'Leave request rejected.');
        \$this->redirect('/academics/attendance');
    }

PHP;

// Inject methods right before class closing brace (rough replacement)
$content = preg_replace('/}\s*$/', $leaveMethods . "\n}", $content);

file_put_contents($file, $content);
echo "AttendanceController updated.";
