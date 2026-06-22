<?php
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/core/Application.php';
$app = new \Core\Application();
$db = \Core\Application::$app->db;
$invoices = $db->select("SELECT id, title FROM fee_invoices");
header('Content-Type: application/json');
echo json_encode($invoices, JSON_PRETTY_PRINT);
unlink(__FILE__);
