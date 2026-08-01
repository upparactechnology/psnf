<?php
// 1. Fix show.php
$f = 'resources/views/students/show.php';
$c = file_get_contents($f);
$c = preg_replace('/<span class="text-brand-400"><\?= e\(\$s\[\'disability_type\'\]\)\s*\?><\/span>\s*/s', '', $c);
file_put_contents($f, $c);
echo "Fixed show.php\n";

// 2. Fix StudentService.php
$f2 = 'app/Services/StudentService.php';
$c2 = file_get_contents($f2);
$c2 = preg_replace('/(\'created_by\'\s*=>\s*auth_id\(\),)/', "$1\n            'status'      => 'verified',", $c2);
file_put_contents($f2, $c2);
echo "Fixed StudentService.php\n";

// 3. Update DB
require 'core/helpers.php';
require 'core/Application.php';
require 'core/Database.php';

$config = require 'config/database.php';
$db = new \Core\Database($config);
$db->execute("UPDATE student_documents SET status = 'verified' WHERE status = 'pending'");
echo "Updated DB documents to verified.\n";
