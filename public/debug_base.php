<?php
declare(strict_types=1);

header('Content-Type: text/plain');

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require ROOT_PATH . '/core/Application.php';

echo "=== SERVER VARIABLES ===\n";
echo "REQUEST_URI:     " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME:     " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT:   " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "HTTP_HOST:       " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";

try {
    $app = new \Core\Application();

    echo "\n=== PATH DETECTION ===\n";
    echo "detectBasePath(): " . var_export($app->request->detectBasePath(), true) . "\n";
    echo "getPath():        " . var_export($app->request->getPath(), true) . "\n";
    echo "getMethod():      " . var_export($app->request->getMethod(), true) . "\n";
    echo "get_dynamic_base_url(): " . var_export(get_dynamic_base_url(), true) . "\n";
    echo "url('/login'):    " . var_export(url('/login'), true) . "\n";

    echo "\n=== SIMULATE ROOT ROUTE ===\n";
    $_SERVER['REQUEST_URI'] = '/erpv2/public/';
    $req = new \Core\Request();
    echo "When REQUEST_URI is '/erpv2/public/':\n";
    echo "  detectBasePath() = " . var_export($req->detectBasePath(), true) . "\n";
    echo "  getPath()        = " . var_export($req->getPath(), true) . "\n";

    $_SERVER['REQUEST_URI'] = '/erpv2/public/login';
    $req2 = new \Core\Request();
    echo "When REQUEST_URI is '/erpv2/public/login':\n";
    echo "  detectBasePath() = " . var_export($req2->detectBasePath(), true) . "\n";
    echo "  getPath()        = " . var_export($req2->getPath(), true) . "\n";

} catch (\Throwable $e) {
    echo "\nFATAL EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
