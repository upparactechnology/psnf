<?php
/**
 * Migration: staff table -> users + user_roles + employees
 */

$config = require __DIR__ . '/../config/database.php';
$dryRun = isset($_GET['action']) ? $_GET['action'] === 'dry-run' : true;

// Output as HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff -> Users Migration</title>
    <style>
        * { font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #1a1a2e; color: #eee; padding: 40px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #e94560; margin-bottom: 20px; font-size: 24px; }
        .buttons { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn { padding: 12px 28px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-dry { background: #0f3460; color: #fff; }
        .btn-dry:hover { background: #16508a; }
        .btn-live { background: #e94560; color: #fff; }
        .btn-live:hover { background: #c73650; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .output { background: #16213e; border: 1px solid #0f3460; border-radius: 8px; padding: 20px; font-family: 'Courier New', monospace; font-size: 14px; line-height: 1.8; white-space: pre-wrap; overflow-x: auto; }
        .success { color: #4ecca3; }
        .error { color: #e94560; }
        .skip { color: #f0c040; }
        .info { color: #7ec8e3; }
        .mode-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 20px; }
        .mode-dry { background: #0f3460; color: #7ec8e3; }
        .mode-live { background: #e94560; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Staff -> Users Migration</h1>
        <div class="buttons">
            <a href="?action=dry-run" class="btn btn-dry <?= $dryRun ? 'disabled' : '' ?>">Preview (Dry Run)</a>
            <a href="?action=live" class="btn btn-live" onclick="return confirm('This will modify the database. Are you sure?')">Run Migration</a>
        </div>
        <div class="mode-badge <?= $dryRun ? 'mode-dry' : 'mode-live' ?>">
            MODE: <?= $dryRun ? 'DRY RUN (no changes)' : 'LIVE (writing to database)' ?>
        </div>
        <div class="output"><?php

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("<span class='error'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</span>");
}

echo "<span class='info'>Connecting to: {$config['dbname']}@{$config['host']}...</span>\n";

// Check tables
$staffExists = $pdo->query("SHOW TABLES LIKE 'staff'")->fetch();
if (!$staffExists) {
    die("<span class='error'>ERROR: 'staff' table does not exist. Migration already completed or table not found.</span>");
}

$usersExists = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
if (!$usersExists) {
    die("<span class='error'>ERROR: 'users' table does not exist. Run application migrations first.</span>");
}

$staffRecords = $pdo->query("SELECT * FROM staff WHERE deleted_at IS NULL ORDER BY id")->fetchAll();

if (empty($staffRecords)) {
    echo "<span class='success'>No staff records to migrate.</span>";
    exit(0);
}

echo "<span class='info'>Found " . count($staffRecords) . " staff records to migrate.</span>\n\n";

$roleMap = [
    'Teacher'    => 'teacher',
    'Admin'      => 'super_admin',
    'Staff'      => 'staff',
    'Driver'     => 'driver',
    'Therapist'  => 'therapist',
    'Principal'  => 'super_admin',
    'Accountant' => 'staff',
];

$defaultTenant = $pdo->query("SELECT id FROM tenants ORDER BY id LIMIT 1")->fetch();
$tenantId = $defaultTenant ? (int) $defaultTenant['id'] : 1;
$defaultSchool = $pdo->query("SELECT id FROM schools WHERE tenant_id = $tenantId ORDER BY id LIMIT 1")->fetch();
$schoolId = $defaultSchool ? (int) $defaultSchool['id'] : 1;
$defaultBranch = $pdo->query("SELECT id FROM branches WHERE tenant_id = $tenantId ORDER BY id LIMIT 1")->fetch();
$branchId = $defaultBranch ? (int) $defaultBranch['id'] : 1;

$roleIds = [];
foreach (array_unique(array_values($roleMap)) as $slug) {
    $row = $pdo->prepare("SELECT id FROM roles WHERE slug = ? LIMIT 1");
    $row->execute([$slug]);
    $f = $row->fetch();
    if ($f) $roleIds[$slug] = (int) $f['id'];
}

$deptAcad  = $pdo->query("SELECT id FROM departments WHERE code = 'ACAD' LIMIT 1")->fetch();
$deptAdmin = $pdo->query("SELECT id FROM departments WHERE code = 'ADMIN' LIMIT 1")->fetch();
$deptSupp  = $pdo->query("SELECT id FROM departments WHERE code = 'SUPP' LIMIT 1")->fetch();
$desigTch    = $pdo->query("SELECT id FROM designations WHERE code = 'TCH' LIMIT 1")->fetch();
$desigStaff  = $pdo->query("SELECT id FROM designations WHERE code = 'STAFF' LIMIT 1")->fetch();
$desigDrv    = $pdo->query("SELECT id FROM designations WHERE code = 'DRV' LIMIT 1")->fetch();
$desigTher   = $pdo->query("SELECT id FROM designations WHERE code = 'THER' LIMIT 1")->fetch();
$desigAdmin  = $pdo->query("SELECT id FROM designations WHERE code = 'ADMIN' LIMIT 1")->fetch();

$migrated = 0; $skipped = 0; $errors = 0;

foreach ($staffRecords as $staff) {
    $staffId = (int) $staff['id'];
    $name    = trim($staff['name'] ?? '');
    $email   = trim($staff['email'] ?? '');
    $isActive = (int) ($staff['is_active'] ?? 1);
    $roleStr  = $staff['role'] ?? 'Staff';

    echo "Staff #$staffId: " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ") [$roleStr] - ";

    if ($email === '') {
        echo "<span class='skip'>SKIPPED (no email)</span>\n";
        $skipped++;
        continue;
    }

    $existing = $pdo->prepare("SELECT id FROM users WHERE email = ? AND tenant_id = ? LIMIT 1");
    $existing->execute([$email, $tenantId]);
    if ($existing->fetch()) {
        echo "<span class='skip'>SKIPPED (user already exists)</span>\n";
        $skipped++;
        continue;
    }

    $mappedRole = $roleMap[$roleStr] ?? 'staff';

    if ($mappedRole === 'teacher')        { $deptId = $deptAcad ? (int)$deptAcad['id'] : null; $desigId = $desigTch ? (int)$desigTch['id'] : null; }
    elseif ($mappedRole === 'therapist')  { $deptId = $deptAcad ? (int)$deptAcad['id'] : null; $desigId = $desigTher ? (int)$desigTher['id'] : null; }
    elseif ($mappedRole === 'driver')     { $deptId = $deptSupp ? (int)$deptSupp['id'] : null; $desigId = $desigDrv ? (int)$desigDrv['id'] : null; }
    elseif ($mappedRole === 'super_admin'){ $deptId = $deptAdmin ? (int)$deptAdmin['id'] : null; $desigId = $desigAdmin ? (int)$desigAdmin['id'] : null; }
    else                                 { $deptId = $deptAdmin ? (int)$deptAdmin['id'] : null; $desigId = $desigStaff ? (int)$desigStaff['id'] : null; }

    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));

    $password = $staff['password'] ?? password_hash('Password123!', PASSWORD_BCRYPT, ['cost' => 12]);

    if ($dryRun) {
        echo "<span class='info'>WOULD CREATE user (role: $mappedRole)</span>\n";
        $migrated++;
        continue;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (uuid, tenant_id, school_id, branch_id, name, email, password, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$uuid, $tenantId, $schoolId, $branchId, $name, $email, $password, $isActive]);
        $userId = (int) $pdo->lastInsertId();

        if (isset($roleIds[$mappedRole])) {
            $r = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $r->execute([$userId, $roleIds[$mappedRole]]);
        }

        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] !== '' ? $parts[0] : 'Staff';
        $lastName = $parts[1] ?? '';

        $empCount = $pdo->query("SELECT COUNT(*) as cnt FROM employees")->fetch()['cnt'] ?? 0;
        $empCode = 'EMP-' . ((int) $empCount + 101);

        $e = $pdo->prepare("INSERT INTO employees (tenant_id, user_id, emp_code, first_name, last_name, email, department_id, designation_id, branch_id, joining_date, salary_basic, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $e->execute([$tenantId, $userId, $empCode, $firstName, $lastName, $email, $deptId, $desigId, $branchId, date('Y-m-d'), 0.00, $isActive ? 'active' : 'inactive']);

        $pdo->prepare("UPDATE users SET employee_id = ? WHERE id = ?")->execute([$empCode, $userId]);

        $pdo->commit();
        echo "<span class='success'>CREATED user #$userId + employee $empCode (role: $mappedRole)</span>\n";
        $migrated++;
    } catch (Exception $ex) {
        $pdo->rollBack();
        echo "<span class='error'>ERROR: " . htmlspecialchars($ex->getMessage()) . "</span>\n";
        $errors++;
    }
}

echo "\n<span class='info'>=== Migration Complete ===</span>\n";
echo "<span class='success'>Migrated: $migrated</span> | <span class='skip'>Skipped: $skipped</span> | <span class='error'>Errors: $errors</span>\n";

if (!$dryRun && $migrated > 0) {
    echo "\n<span class='info'>Next steps:</span>\n";
    echo "<span class='info'>1. Verify users in admin panel</span>\n";
    echo "<span class='info'>2. Test login with migrated accounts</span>\n";
    echo "<span class='info'>3. Once confirmed, drop old tables:</span>\n";
    echo "<span class='info'>   DROP TABLE IF EXISTS staff, staff_documents, staff_favorites;</span>\n";
}

?></div>
    </div>
</body>
</html>
