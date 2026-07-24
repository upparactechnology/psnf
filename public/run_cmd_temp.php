<?php
header('Content-Type: text/plain');
$cmd = 'cd C:\\xampp\\htdocs\\psnf\\staff_app && flutter devices 2>&1';
echo "Running: $cmd\n\n";
echo shell_exec($cmd);
