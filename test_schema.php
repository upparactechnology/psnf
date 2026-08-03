<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=psnf_drm', 'root', '');
foreach(['activity_logs', 'tenants', 'schools'] as $t) {
    $stmt = $pdo->query("SHOW COLUMNS FROM $t");
    echo "$t: " . implode(', ', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field')) . "\n";
}
