<?php
require 'core/helpers.php';
require 'core/Application.php';
require 'core/Database.php';

$config = require 'config/database.php';
$db = new \Core\Database($config);

// 1. Update all students to enrolled
$db->execute("UPDATE students SET admission_status = 'enrolled'");
echo "All students set to enrolled.\n";

// 2. Drop the medical/support columns
$columnsToDrop = [
    'disability_type',
    'disability_detail',
    'care_instructions',
    'special_needs_summary',
    'allergies',
    'triggers',
    'medications'
];

foreach ($columnsToDrop as $col) {
    try {
        $db->execute("ALTER TABLE students DROP COLUMN {$col}");
        echo "Dropped column: {$col}\n";
    } catch (Exception $e) {
        echo "Could not drop column {$col} (might not exist): " . $e->getMessage() . "\n";
    }
}
echo "Database update complete.\n";
