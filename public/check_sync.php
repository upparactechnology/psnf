<?php
declare(strict_types=1);

header('Content-Type: text/plain');

$root = dirname(__DIR__);

echo "=== SERVER FOLDER & PERMISSIONS ===\n";
echo "Root Path:       $root\n";
echo "Root writable:   " . (is_writable($root) ? 'YES' : 'NO') . "\n";
echo "core/ writable:  " . (is_writable($root . '/core') ? 'YES' : 'NO') . "\n";
echo "core/Request.php writable: " . (is_writable($root . '/core/Request.php') ? 'YES' : 'NO') . "\n";

echo "\n=== GIT STATUS ON SERVER ===\n";
if (function_exists('shell_exec')) {
    echo "git status:\n";
    echo shell_exec("cd $root && git status 2>&1") ?? "shell_exec returned null\n";
    echo "\ngit log -n 3 --oneline:\n";
    echo shell_exec("cd $root && git log -n 3 --oneline 2>&1") ?? "shell_exec returned null\n";
} else {
    echo "shell_exec is disabled in php.ini on this server.\n";
}

echo "\n=== CHECK CORE FILES ===\n";
$reqFile = $root . '/core/Request.php';
if (file_exists($reqFile)) {
    $content = file_get_contents($reqFile);
    echo "core/Request.php size: " . strlen($content) . " bytes\n";
    echo "core/Request.php last modified: " . date('Y-m-d H:i:s', filemtime($reqFile)) . "\n";
    echo "Contains detectBasePath: " . (str_contains($content, 'detectBasePath') ? 'YES' : 'NO') . "\n";
} else {
    echo "core/Request.php NOT FOUND!\n";
}

$helpFile = $root . '/core/helpers.php';
if (file_exists($helpFile)) {
    $content = file_get_contents($helpFile);
    echo "core/helpers.php size: " . strlen($content) . " bytes\n";
    echo "Contains get_dynamic_base_url: " . (str_contains($content, 'get_dynamic_base_url') ? 'YES' : 'NO') . "\n";
}
