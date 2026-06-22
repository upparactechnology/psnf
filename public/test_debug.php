<?php
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__ . '/../core/Application.php';
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['config'] = $config;
$db = new \Core\Database($config['db']);
$invoices = $db->select("SELECT * FROM fee_invoices");
header('Content-Type: application/json');
echo json_encode($invoices, JSON_PRETTY_PRINT);
unlink(__FILE__); // auto-delete
