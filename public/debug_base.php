<?php
// Quick debug - visit psnf.upparac.com/erpv2/public/debug_base.php
echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:20px;font-family:monospace;border-radius:8px;">';
echo "<b style='color:#f38ba8'>SERVER VARIABLES:</b>\n\n";
echo "REQUEST_URI    = " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME    = " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME= " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "PHP_SELF       = " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT  = " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "SERVER_NAME    = " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
echo "HTTP_HOST      = " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "HTTPS          = " . ($_SERVER['HTTPS'] ?? 'N/A') . "\n";
echo "SERVER_PORT    = " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n";
echo "\n<b style='color:#a6e3a1'>DETECTED BASE PATH:</b>\n\n";

require dirname(__DIR__) . '/core/helpers.php';
require dirname(__DIR__) . '/core/Application.php';

CoreApplication::$app = new class {
    public $request;
    public $db;
    public $router;
    public $response;
    public $session;
};

CoreApplication::$app->request = new Core\Request();
CoreApplication::$app->response = new Core\Response();

$detected = CoreApplication::$app->request->detectBasePath();
$getPath  = CoreApplication::$app->request->getPath();
$baseUrl  = get_dynamic_base_url();

echo "detectBasePath() = " . var_export($detected, true) . "\n";
echo "getPath()        = " . var_export($getPath, true) . "\n";
echo "get_dynamic_base_url() = " . var_export($baseUrl, true) . "\n";
echo "\n<b style='color:#89b4fa'>ROUTE / should match? getPath() === '/' ? " . ($getPath === '/' ? 'YES' : 'NO - this is the problem!') . "</b>\n";
echo '</pre>';
