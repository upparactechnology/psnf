<?php
$file = 'resources/views/attendance/index.php';
$content = file_get_contents($file);

$replacement = <<<PHP
                            <?php 
                                \$isToday = \$selectedDate === date('Y-m-d');
                                \$defaultStatus = \$isToday ? 'present' : null;
                                \$attRecord = \$attendanceMap[\$s['id']] ?? ['status' => \$defaultStatus, 'remarks' => ''];
                                \$status = \$attRecord['status'];
                            ?>
PHP;

$content = preg_replace('/<\?php \s*\$attRecord = \$attendanceMap\[\$s\[\'id\'\]\] \?\? \[\'status\' => \'present\', \'remarks\' => \'\'\];\s*\$status = \$attRecord\[\'status\'\];\s*\?>/s', $replacement, $content);

file_put_contents($file, $content);
echo "Updated default status in index.php";
