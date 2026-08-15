<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('START_TIME', microtime(true));

require_once ROOT_PATH . '/core/Application.php';
$app = new \Core\Application();

\Core\Database::setTenantScope(1);

require_once ROOT_PATH . '/core/Migration.php';
$runner = new \Core\Migration();
$runner->run();
echo "Migrations executed successfully.\n";
