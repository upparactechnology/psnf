<?php
define('ROOT_DIR', dirname(__DIR__));
define('ROOT_PATH', ROOT_DIR);
define('CORE_PATH', ROOT_DIR . '/core');
define('APP_PATH', ROOT_DIR . '/app');
define('CONFIG_PATH', ROOT_DIR . '/config');
define('VIEWS_PATH', ROOT_DIR . '/resources/views');
define('STORAGE_PATH', ROOT_DIR . '/storage');

spl_autoload_register(function ($class) {
    $prefix = 'Core\\';
    $base_dir = CORE_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    $prefixApp = 'App\\';
    $base_dirApp = APP_PATH . '/';
    $lenApp = strlen($prefixApp);
    if (strncmp($prefixApp, $class, $lenApp) === 0) {
        $relative_class = substr($class, $lenApp);
        $file = $base_dirApp . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once CORE_PATH . '/helpers.php';
$app = new \Core\Application(ROOT_DIR);

try {
    require_once ROOT_DIR . '/database/migrations/20260728_TransportWorkspaceSchema.php';
    $m = new Migration_20260728_TransportWorkspaceSchema();
    $m->up();
    echo "TRANSPORT_MIGRATION_SUCCESSFUL";
} catch (\Throwable $e) {
    echo "ERR: " . $e->getMessage();
}
