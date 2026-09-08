<?php
// Quick debug - visit on live server to diagnose base path issues
header('Content-Type: text/plain');

echo "=== SERVER VARIABLES ===\n";
echo "REQUEST_URI     = " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME     = " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME = " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "PHP_SELF        = " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT   = " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "HTTP_HOST       = " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";

echo "\n=== BASE PATH DETECTION ===\n";

// Replicate detectBasePath logic inline
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (($qPos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $qPos);
}
$requestUri = rtrim($requestUri, '/');

$scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
$dir = rtrim(dirname($scriptName), '/\\');

echo "1. dirname(SCRIPT_NAME) = '$dir'\n";
echo "   is prefix of REQUEST_URI? " . (str_starts_with($requestUri, $dir) ? 'YES' : 'NO') . "\n";

if ($dir !== '' && $dir !== '/' && str_starts_with($requestUri, $dir)) {
    echo "   -> Using SCRIPT_NAME dir: '$dir'\n";
    $basePath = $dir;
} elseif (($pos = strpos($requestUri, '/public/')) !== false) {
    $basePath = substr($requestUri, 0, $pos + 8);
    echo "   -> Found /public/ in REQUEST_URI: '$basePath'\n";
} else {
    $basePath = $dir !== '/' ? $dir : '';
    echo "   -> Fallback: '$basePath'\n";
}

echo "\nBase path = '$basePath'\n";

// Simulate getPath()
$path = $_SERVER['REQUEST_URI'] ?? '/';
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
}
if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';

echo "getPath() = '$path'\n";
echo "Route '/' would match? " . ($path === '/' ? 'YES' : 'NO - THIS IS THE PROBLEM!') . "\n";
