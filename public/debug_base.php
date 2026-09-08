<?php
// Minimal debug - no framework needed
header('Content-Type: text/plain');

echo "=== SERVER VARIABLES ===\n";
echo "REQUEST_URI     = " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME     = " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME = " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT   = " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "HTTP_HOST       = " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";

echo "\n=== TEST: Does .htaccess in public/ rewrite to index.php? ===\n";
echo "If you see this, debug_base.php was served directly (NOT through index.php).\n";
echo "This means the .htaccess is NOT catching real files - only non-existent paths.\n";
echo "That is correct behavior.\n";

echo "\n=== WHAT TO CHECK ===\n";
echo "1. Visit psnf.upparac.com/erpv2/public/debug_base.php -> should show this output\n";
echo "2. Visit psnf.upparac.com/erpv2/public/ -> should redirect to /login\n";
echo "3. Visit psnf.upparac.com/erpv2/public/login -> should show login page\n";
echo "\nIf #2 or #3 shows 404, the root .htaccess revert has NOT been deployed yet.\n";
echo "Make sure .htaccess in the PROJECT ROOT (erpv2/.htaccess) only contains:\n";
echo "  Options -Indexes\n";
echo "  RewriteEngine On\n";
echo "  RewriteRule (^\\.|/\\.) - [F]\n";
