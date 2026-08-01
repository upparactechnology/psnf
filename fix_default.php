<?php
$f = 'resources/views/attendance/index.php';
$c = file_get_contents($f);

// Change the default status logic inside the index() method in the view
$c = preg_replace(
    '/\$isToday = \$selectedDate === date\(\'Y-m-d\'\);\s*\$defaultStatus = \$isToday \? \'present\' : null;/',
    '$defaultStatus = null;',
    $c
);

file_put_contents($f, $c);
echo "Fixed default status\n";
