<?php
$path = 'c:\xampp\htdocs\psnf\resources\views\transport\tracking.php';
$c = file_get_contents($path);
// Check if it's UTF-16LE
if (strpos($c, "\x00") !== false) {
    $c = mb_convert_encoding($c, 'UTF-8', 'UTF-16LE');
    file_put_contents($path, $c);
    echo "Converted to UTF-8";
} else {
    echo "Already UTF-8";
}
