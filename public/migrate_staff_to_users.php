<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

$config = require __DIR__ . '/../config/database.php';

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$staffRole = 6;
$tenantId  = 1;
$schoolId  = 1;
$branchId  = 1;

$staffRows = $pdo->query("SELECT id, name, email, password, is_active, created_at FROM staff ORDER BY id ASC")->fetchAll();

$inserted = 0;
$skipped  = 0;
$errors   = 0;

$stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmtInsert = $pdo->prepare("
    INSERT INTO users (uuid, tenant_id, school_id, branch_id, name, email, password, is_active, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmtRole = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (?, ?, NOW())");

foreach ($staffRows as $staff) {
    $stmtCheck->execute([$staff['email']]);
    if ($stmtCheck->fetch()) {
        $skipped++;
        echo "SKIP  [{$staff['id']}] {$staff['name']} ({$staff['email']}) — already in users\n";
        continue;
    }

    try {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $stmtInsert->execute([
            $uuid,
            $tenantId,
            $schoolId,
            $branchId,
            $staff['name'],
            $staff['email'],
            $staff['password'],
            $staff['is_active'],
            $staff['created_at'],
        ]);

        $newUserId = (int) $pdo->lastInsertId();
        $stmtRole->execute([$newUserId, $staffRole]);

        $inserted++;
        echo "OK    [{$staff['id']}] {$staff['name']} ({$staff['email']}) → user_id={$newUserId}\n";
    } catch (Throwable $e) {
        $errors++;
        echo "ERROR [{$staff['id']}] {$staff['name']}: {$e->getMessage()}\n";
    }
}

echo "\n--- Done ---\n";
echo "Inserted: {$inserted}\n";
echo "Skipped:  {$skipped}\n";
echo "Errors:   {$errors}\n";
echo "Total staff: " . count($staffRows) . "\n";
