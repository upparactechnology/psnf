<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
try {
    Application::$app->db->pdo->exec('ALTER TABLE shift_templates ADD COLUMN working_days_per_month INT DEFAULT 0 AFTER id;');
    echo "Column added successfully.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
