<?php
$file = 'app/Controllers/ParentPortalController.php';
$content = file_get_contents($file);

$leaveHistoryQuery = <<<PHP
        // Leave Applications History
        \$leaveHistory = \$this->db()->select(
            "SELECT * FROM leave_applications WHERE student_id = ? ORDER BY created_at DESC",
            [\$student['id']]
        );

        return View::render('parent/attendance', array_merge(\$context, [
            'title'        => 'Child Attendance',
            'logs'         => \$logs,
            'summary'      => \$summary,
            'month'        => \$month,
            'year'         => \$year,
            'leaveHistory' => \$leaveHistory,
PHP;

$content = preg_replace('/return View::render\(\'parent\/attendance\', array_merge\(\$context, \[\s*\'title\'\s*=>\s*\'Child Attendance\',\s*\'logs\'\s*=>\s*\$logs,\s*\'summary\'\s*=>\s*\$summary,\s*\'month\'\s*=>\s*\$month,\s*\'year\'\s*=>\s*\$year,/s', $leaveHistoryQuery, $content);

file_put_contents($file, $content);
echo "Parent controller updated.";
