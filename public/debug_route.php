<?php
// Live diagnostic script
header('Content-Type: text/plain');

require dirname(__DIR__) . '/core/Application.php';

echo "=== SERVER VARS ===\n";
echo "REQUEST_URI:     " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "SCRIPT_NAME:     " . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";
echo "DOCUMENT_ROOT:   " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";

$app = new \Core\Application();

echo "\n=== DETECTION RESULT ===\n";
echo "detectBasePath(): " . var_export($app->request->detectBasePath(), true) . "\n";
echo "getPath():        " . var_export($app->request->getPath(), true) . "\n";
echo "getMethod():      " . var_export($app->request->getMethod(), true) . "\n";
echo "get_dynamic_base_url(): " . var_export(get_dynamic_base_url(), true) . "\n";
echo "url('/login'):    " . var_export(url('/login'), true) . "\n";

echo "\n=== ALL REGISTERED ROUTES (first 10) ===\n";
$ref = new ReflectionClass($app->router);
$prop = $ref->getProperty('routes');
$prop->setAccessible(true);
$routes = $prop->getValue($app->router);
foreach (array_slice($routes, 0, 15) as $r) {
    echo $r['method'] . " " . $r['path'] . "\n";
}

echo "\n=== RESOLVE ATTEMPT ===\n";
// Temporarily mock request path to test different URLs
$testPaths = ['/', '/login', '/dashboard', '/students'];
foreach ($testPaths as $tp) {
    $matched = false;
    foreach ($routes as $route) {
        if ($route['method'] !== 'GET') continue;
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $tp)) {
            $matched = true;
            echo "Path '$tp' => MATCHED route: " . $route['path'] . "\n";
            break;
        }
    }
    if (!$matched) {
        echo "Path '$tp' => NOT MATCHED!\n";
    }
}
