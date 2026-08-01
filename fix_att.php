<?php
$f = 'app/Controllers/AttendanceController.php';
$c = file_get_contents($f);
$c = preg_replace('/(\/\/ If no class is selected, auto-select the first available class\s*if \()empty\(\$selectedClass\)( && !empty\(\$classes\)\) \{)/', '$1!isset($_GET[\'class\']) && empty($selectedClass)$2', $c);
file_put_contents($f, $c);
echo "Fixed AttendanceController.php\n";
