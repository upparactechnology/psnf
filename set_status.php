<?php
$f = 'app/Controllers/StudentController.php';
$c = file_get_contents($f);

// We want to add $validated['admission_status'] = 'enrolled'; right before $this->service->create
$c = preg_replace('/(\$studentId\s*=\s*\$this->service->create\(\$validated,\s*\$filesData\);)/', "\$validated['admission_status'] = 'enrolled';\n            $1", $c);

file_put_contents($f, $c);
echo "Added enrolled status in controller.\n";
