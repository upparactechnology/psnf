<?php
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/resources/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');

require ROOT_PATH . '/core/Application.php';

$app = new \Core\Application();
$cols = $app->db->select("DESCRIBE report_card_settings");
print_r($cols);
?>
