<?php
$file = 'resources/views/parent/dashboard.php';
$content = file_get_contents($file);

// Remove absentModal from x-data
$content = str_replace('x-data="{ emergencyModal: false, absentModal: false }"', 'x-data="{ emergencyModal: false }"', $content);

// Remove Mark Absent button
$buttonPattern = '/<button @click="absentModal = true".*?❌ Mark Absent\s*<\/button>/s';
$content = preg_replace($buttonPattern, '', $content);

// Remove Mark Absent Modal
$modalPattern = '/<!-- Mark Absent Modal -->.*?<\/div>\s*<\/div>\s*<\/div>/s';
$content = preg_replace($modalPattern, '', $content);

file_put_contents($file, $content);
echo "Dashboard cleaned up.";
