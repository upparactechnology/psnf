<?php
declare(strict_types=1);

header('Content-Type: text/plain');

ini_set('display_errors', '1');
error_reporting(E_ALL);

// Reset opcache in case PHP has cached old versions of core files
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "opcache_reset(): SUCCESS\n\n";
}

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

echo "=== FILE STATUS ON SERVER ===\n";
$reqFile = CORE_PATH . '/Request.php';
echo "core/Request.php exists? " . (file_exists($reqFile) ? 'YES' : 'NO') . "\n";
echo "core/Request.php size: " . filesize($reqFile) . " bytes\n";
echo "core/Request.php modified: " . date('Y-m-d H:i:s', filemtime($reqFile)) . "\n";
echo "core/Request.php has detectBasePath? " . (str_contains(file_get_contents($reqFile), 'detectBasePath') ? 'YES' : 'NO') . "\n";

require ROOT_PATH . '/core/Application.php';

try {
    $app = new \Core\Application();

    echo "\n=== ROUTING TEST ===\n";
    echo "detectBasePath(): " . var_export($app->request->detectBasePath(), true) . "\n";
    echo "getPath():        " . var_export($app->request->getPath(), true) . "\n";
    echo "Dynamic Base URL: " . var_export(get_dynamic_base_url(), true) . "\n";
    echo "url('/login'):    " . var_export(url('/login'), true) . "\n";

    echo "\n=== RESOLVE TEST FOR '/' ===\n";
    $_SERVER['REQUEST_URI'] = '/erpv2/public/';
    $req = new \Core\Request();
    echo "Path for '/erpv2/public/': " . var_export($req->getPath(), true) . "\n";

} catch (\Throwable $e) {
    echo "\nEXCEPTION: " . $e->getMessage() . "\n";
    echo "In " . $e->getFile() . ":" . $e->getLine() . "\n";
}
