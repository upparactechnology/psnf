<?php
require 'core/Database.php';

$dbConfig = require 'config/database.php';
$db = new \Core\Database($dbConfig);

$users = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "USERS COLUMNS:\n";
foreach($users as $u) echo $u['Field'] . "\n";

echo "\nEMPLOYEES COLUMNS:\n";
try {
    $employees = $db->query("SHOW COLUMNS FROM employees")->fetchAll(PDO::FETCH_ASSOC);
    foreach($employees as $u) echo $u['Field'] . "\n";
} catch (Exception $e) {
    echo "No employees table.\n";
}
