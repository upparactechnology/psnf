<?php
$file = 'app/Controllers/ParentPortalController.php';
$content = file_get_contents($file);

$requestLeaveMethod = <<<PHP
    public function requestLeave(string \$id): void
    {
        \$context = \$this->getContext((int)\$id);
        \$student = \$context['active_student'];

        \$startDate = \Core\Application::\$app->request->input('start_date');
        \$endDate = \Core\Application::\$app->request->input('end_date');
        \$leaveType = \Core\Application::\$app->request->input('leave_type', 'Other');
        \$reason = \Core\Application::\$app->request->input('reason', '');

        \$startTs = strtotime(\$startDate);
        \$endTs = strtotime(\$endDate);

        if (!\$startTs || !\$endTs || \$startTs > \$endTs) {
            \Core\Session::flash('error', 'Invalid date range.');
            \Core\Application::\$app->response->redirect("/parent/students/{\$id}/attendance");
            return;
        }

        \$diffDays = round((\$endTs - \$startTs) / 86400);
        \$medicalCertPath = null;

        if (\$diffDays >= 1) {
            if (!isset(\$_FILES['medical_certificate']) || \$_FILES['medical_certificate']['error'] !== UPLOAD_ERR_OK) {
                \Core\Session::flash('error', 'A supporting document is required for multi-day leaves.');
                \Core\Application::\$app->response->redirect("/parent/students/{\$id}/attendance");
                return;
            }

            \$uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/absences/';
            if (!is_dir(\$uploadDir)) {
                mkdir(\$uploadDir, 0755, true);
            }

            \$fileInfo = pathinfo(\$_FILES['medical_certificate']['name']);
            \$ext = strtolower(\$fileInfo['extension']);
            \$allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];

            if (!in_array(\$ext, \$allowedExts)) {
                \Core\Session::flash('error', 'Invalid file type. Only PDF and images are allowed.');
                \Core\Application::\$app->response->redirect("/parent/students/{\$id}/attendance");
                return;
            }

            \$fileName = \$student['id'] . '_' . time() . '_' . uniqid() . '.' . \$ext;
            if (move_uploaded_file(\$_FILES['medical_certificate']['tmp_name'], \$uploadDir . \$fileName)) {
                \$medicalCertPath = \$fileName;
            } else {
                \Core\Session::flash('error', 'Failed to upload document.');
                \Core\Application::\$app->response->redirect("/parent/students/{\$id}/attendance");
                return;
            }
        }

        \$this->db()->insert('leave_applications', [
            'tenant_id' => \$student['tenant_id'],
            'school_id' => \$student['school_id'],
            'branch_id' => \$student['branch_id'],
            'student_id' => \$student['id'],
            'start_date' => date('Y-m-d', \$startTs),
            'end_date' => date('Y-m-d', \$endTs),
            'leave_type' => \$leaveType,
            'reason' => \$reason,
            'medical_certificate' => \$medicalCertPath,
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        \Core\Session::flash('success', 'Leave request submitted successfully.');
        \Core\Application::\$app->response->redirect("/parent/students/{\$id}/attendance");
    }

PHP;

// Find submitTomorrowAttendance and inject before it
$content = preg_replace('/public function submitTomorrowAttendance\(string \$id\): void/m', $requestLeaveMethod . "\n    public function submitTomorrowAttendance(string \$id): void", $content);

file_put_contents($file, $content);
echo "Method requestLeave added to ParentPortalController.";
