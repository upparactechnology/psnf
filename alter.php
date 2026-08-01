<?php
$file = 'app/Controllers/TransportController.php';
$content = file_get_contents($file);

$attendanceLogic = <<<PHP
        \$tripId = \$activeTrip['id'];
        \$this->db()->query("UPDATE trip_students SET status = ?, is_current = 0 WHERE trip_id = ? AND student_id = ?", [\$status, \$tripId, \$studentId]);
        
        // If status is Picked Up, mark present in attendance table for today
        if (\$status === 'Picked Up') {
            \$dateStr = date('Y-m-d');
            \$existingAtt = \$this->db()->selectOne("SELECT id FROM attendance WHERE student_id = ? AND date = ?", [\$studentId, \$dateStr]);
            
            if (\$existingAtt) {
                \$this->db()->update('attendance', [
                    'status' => 'present',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => \$userId
                ], "id = ?", [\$existingAtt['id']]);
            } else {
                // Get student info for required fields
                \$stu = \$this->db()->selectOne("SELECT tenant_id, school_id, branch_id FROM students WHERE id = ?", [\$studentId]);
                if (\$stu) {
                    \$this->db()->insert('attendance', [
                        'uuid' => \\Core\\Str::uuid(),
                        'tenant_id' => \$stu['tenant_id'],
                        'school_id' => \$stu['school_id'],
                        'branch_id' => \$stu['branch_id'],
                        'student_id' => \$studentId,
                        'date' => \$dateStr,
                        'status' => 'present',
                        'remarks' => 'Picked up by transport',
                        'is_medical' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => \$userId
                    ]);
                }
            }
        }

PHP;

$content = str_replace(
    "\$tripId = \$activeTrip['id'];\n        \$this->db()->query(\"UPDATE trip_students SET status = ?, is_current = 0 WHERE trip_id = ? AND student_id = ?\", [\$status, \$tripId, \$studentId]);", 
    $attendanceLogic, 
    $content
);

file_put_contents($file, $content);
echo "Driver app logic updated.";
