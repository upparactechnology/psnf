<?php

declare(strict_types=1);

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
