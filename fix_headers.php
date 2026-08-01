<?php
$f = 'resources/views/students/index.php';
$c = file_get_contents($f);
$c = preg_replace('/<th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Disability<\/th>/', '', $c);
file_put_contents($f, $c);
echo "Fixed header in students/index.php\n";

$f = 'app/Models/Student.php';
$c = file_get_contents($f);
$c = preg_replace('/if \(!empty\(\$filters\[\'disability\'\]\)\) \{.*?\}\n/s', '', $c);
file_put_contents($f, $c);
echo "Removed disability filter from Student.php\n";
