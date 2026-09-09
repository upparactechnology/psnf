<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php_error.log');

// Enable CORS for Flutter Web local development and API access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ─── File Manager Interception ──────────────────────────────────────────────
// When accessed via .../file-manager/..., the .htaccess catch-all sends
// the request here. We detect the /file-manager prefix and route to the File
// Manager's own front controller instead of the ERP router.
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (($qPos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $qPos);
}

// Match /file-manager or /file-manager/... (with any prefix like /psnf/erp)
if (preg_match('#(.+)?/file-manager(?:/(.*))?$#', $requestUri, $fmMatch)) {
    $fmPrefix = ltrim($fmMatch[1] ?? '', '/');  // e.g. 'psnf/erp' or ''
    $fmPath   = $fmMatch[2] ?? '';               // e.g. 'dashboard' or ''

    // Rewrite REQUEST_URI so the File Manager router sees just the FM path
    $_SERVER['REQUEST_URI'] = '/' . $fmPath;

    // Set SCRIPT_NAME to full path so FM's resolveBaseUrl() generates
    // correct absolute URLs for redirects and assets
    $_SERVER['SCRIPT_NAME'] = '/' . $fmPrefix . '/file-manager/index.php';

    // Serve the File Manager
    require __DIR__ . '/file_manager/public/index.php';
    exit();
}

// Match legacy /file_manager/public/... URLs and route to the File Manager
if (preg_match('#(.+)?/file_manager/public(?:/(.*))?$#', $requestUri, $fmMatch)) {
    $fmPrefix = ltrim($fmMatch[1] ?? '', '/');
    $fmPath   = $fmMatch[2] ?? '';

    // Rewrite REQUEST_URI so the File Manager router sees just the FM path
    $_SERVER['REQUEST_URI'] = '/' . $fmPath;

    // Set SCRIPT_NAME to full path
    $_SERVER['SCRIPT_NAME'] = '/' . $fmPrefix . '/file-manager/index.php';

    // Serve the File Manager
    require __DIR__ . '/file_manager/public/index.php';
    exit();
}

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('START_TIME', microtime(true));

require ROOT_PATH . '/core/Application.php';

$app = new \Core\Application();

// Automatically run pending migrations to keep db schema up to date
try {
    require_once ROOT_PATH . '/core/Migration.php';
    $runner = new \Core\Migration();
    ob_start();
    $runner->run();
    ob_end_clean();
} catch (\Throwable $e) {
    // Ignore database errors if already migrated or during bootstrap
}

$app->run();
