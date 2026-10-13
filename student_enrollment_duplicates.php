<?php
/**
 * Student Enrollment Duplicate Checker
 * Drop this page into the existing ERP and adjust ONLY the DB include
 * if your ERP already has a database connection/config file.
 */

declare(strict_types=1);

// ============================================================
// 1. USE YOUR EXISTING ERP DATABASE CONNECTION
// ============================================================
// Option A: If your ERP has a PDO connection file, uncomment and
// change the path below.
//
// require_once __DIR__ . '/config/database.php';
// $pdo = $pdo;
//
// Option B: If the ERP exposes $conn as mysqli, this page can be
// adapted. The code below expects PDO.
//
// Example:
// require_once __DIR__ . '/../config/db.php';
// $pdo = $db;

// ---- TEMPORARY / FALLBACK CONNECTION ----
// Replace these values if you are not including your ERP DB config.
$dbHost = '127.0.0.1';
$dbName = 'user_psnf_drmsv2';
$dbUser = 'YOUR_DB_USER';
$dbPass = 'YOUR_DB_PASSWORD';

if (!isset($pdo)) {
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Database connection failed. Configure the ERP database connection at the top of this page.');
    }
}

// ============================================================
// 2. LOAD ENROLLMENTS
// ============================================================
$stmt = $pdo->query("
    SELECT
        id,
        application_code,
        student_full_name,
        dob,
        gender,
        student_aadhar,
        father_name,
        father_phone,
        mother_name,
        mother_phone,
        status,
        created_at,
        updated_at
    FROM online_enrollments
    ORDER BY id ASC
");

$students = $stmt->fetchAll();

// ============================================================
// 3. SAME DEDUPLICATION LOGIC AS THE PDF
//    Aadhaar first; otherwise normalized name + DOB.
// ============================================================
function normalizeValue(?string $value): string
{
    $value = strtolower(trim((string)$value));
    return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
}

function identityKey(array $student): string
{
    $aadhaar = normalizeValue($student['student_aadhar'] ?? '');

    if ($aadhaar !== '') {
        return 'aadhaar:' . $aadhaar;
    }

    return 'name_dob:' .
        normalizeValue($student['student_full_name'] ?? '') .
        '|' .
        ($student['dob'] ?? '');
}

$groups = [];

foreach ($students as $student) {
    $key = identityKey($student);
    $groups[$key][] = $student;
}

// First record = Unique.
// Later records in the same group = Duplicate.
$duplicateIds = [];
$duplicateGroupKeys = [];

foreach ($groups as $key => $group) {
    if (count($group) > 1) {
        $duplicateGroupKeys[$key] = true;

        foreach (array_slice($group, 1) as $duplicate) {
            $duplicateIds[(int)$duplicate['id']] = true;
        }
    }
}

// ============================================================
// 4. FILTER / SEARCH
// ============================================================
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';

$filteredStudents = [];

foreach ($students as $student) {
    $id = (int)$student['id'];
    $isDuplicate = isset($duplicateIds[$id]);

    if ($filter === 'duplicate' && !$isDuplicate) {
        continue;
    }

    if ($filter === 'unique' && $isDuplicate) {
        continue;
    }

    if ($search !== '') {
        $haystack = strtolower(
            ($student['student_full_name'] ?? '') . ' ' .
            ($student['application_code'] ?? '') . ' ' .
            ($student['student_aadhar'] ?? '') . ' ' .
            ($student['father_name'] ?? '') . ' ' .
            ($student['mother_name'] ?? '')
        );

        if (strpos($haystack, strtolower($search)) === false) {
            continue;
        }
    }

    $student['_is_duplicate'] = $isDuplicate;
    $student['_identity_key'] = identityKey($student);
    $filteredStudents[] = $student;
}

$totalRecords = count($students);
$uniqueStudents = count($groups);
$duplicateRecords = count($duplicateIds);
$duplicateGroups = count($duplicateGroupKeys);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Enrollment — Duplicate Checker</title>

<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --bg: #f5f7fb;
        --card: #ffffff;
        --text: #172033;
        --muted: #667085;
        --border: #e5e7eb;
        --danger: #dc2626;
        --danger-bg: #fff1f2;
        --success: #15803d;
        --success-bg: #f0fdf4;
        --warning: #b45309;
        --warning-bg: #fffbeb;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system,
                     BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .page {
        max-width: 1500px;
        margin: 0 auto;
        padding: 28px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-start;
        margin-bottom: 22px;
    }

    h1 {
        margin: 0 0 6px;
        font-size: 28px;
        letter-spacing: -0.5px;
    }

    .subtitle {
        color: var(--muted);
        font-size: 14px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef4ff;
        color: var(--primary-dark);
        font-size: 13px;
        font-weight: 700;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .stat {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(16,24,40,.04);
    }

    .stat-label {
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .stat-value {
        margin-top: 7px;
        font-size: 28px;
        font-weight: 800;
    }

    .stat.duplicate .stat-value { color: var(--danger); }
    .stat.unique .stat-value { color: var(--success); }

    .toolbar {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    input, select, button {
        height: 42px;
        border: 1px solid var(--border);
        border-radius: 9px;
        background: #fff;
        padding: 0 13px;
        font-size: 14px;
    }

    input {
        flex: 1;
        min-width: 260px;
    }

    button, .button {
        cursor: pointer;
        border: 0;
        background: var(--primary);
        color: white;
        font-weight: 700;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
    }

    button:hover, .button:hover {
        background: var(--primary-dark);
    }

    .button.secondary {
        background: #eef2f6;
        color: #344054;
    }

    .table-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(16,24,40,.04);
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1050px;
    }

    th {
        background: #f8fafc;
        color: #475467;
        text-align: left;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .35px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    td {
        padding: 13px 14px;
        border-bottom: 1px solid #eef0f3;
        font-size: 13px;
        vertical-align: middle;
    }

    tr.duplicate-row {
        background: var(--danger-bg);
    }

    tr.duplicate-row td {
        border-top: 1px solid #fecaca;
        border-bottom: 1px solid #fecaca;
    }

    .name {
        font-weight: 750;
    }

    .app-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
    }

    .status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .status.unique {
        color: var(--success);
        background: var(--success-bg);
    }

    .status.duplicate {
        color: var(--danger);
        background: #fee2e2;
    }

    .group-tag {
        font-size: 10px;
        color: var(--danger);
        margin-left: 6px;
        font-weight: 800;
    }

    .empty {
        padding: 45px;
        text-align: center;
        color: var(--muted);
    }

    .legend {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 12px 14px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 12px;
    }

    .dot {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 50%;
        margin-right: 5px;
    }

    .dot.red { background: #dc2626; }
    .dot.green { background: #16a34a; }

    @media (max-width: 800px) {
        .page { padding: 16px; }
        .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .header { flex-direction: column; }
    }
</style>
</head>

<body>
<div class="page">

    <div class="header">
        <div>
            <h1>Student Enrollment</h1>
            <div class="subtitle">
                Duplicate detection is integrated directly with <code>online_enrollments</code>.
            </div>
        </div>

        <div class="badge">
            ● Live ERP Data
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Total Records</div>
            <div class="stat-value"><?= number_format($totalRecords) ?></div>
        </div>

        <div class="stat unique">
            <div class="stat-label">Unique Students</div>
            <div class="stat-value"><?= number_format($uniqueStudents) ?></div>
        </div>

        <div class="stat duplicate">
            <div class="stat-label">Duplicate Records</div>
            <div class="stat-value"><?= number_format($duplicateRecords) ?></div>
        </div>

        <div class="stat duplicate">
            <div class="stat-label">Duplicate Groups</div>
            <div class="stat-value"><?= number_format($duplicateGroups) ?></div>
        </div>
    </div>

    <form class="toolbar" method="get">
        <input
            type="search"
            name="search"
            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Search student, application code, Aadhaar, parent name..."
        >

        <select name="filter">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Students</option>
            <option value="unique" <?= $filter === 'unique' ? 'selected' : '' ?>>Unique Only</option>
            <option value="duplicate" <?= $filter === 'duplicate' ? 'selected' : '' ?>>Duplicates Only</option>
        </select>

        <button type="submit">Search</button>
        <a class="button secondary" href="?">Reset</a>
    </form>

    <div class="table-card">
        <div class="table-wrap">
            <?php if (!$filteredStudents): ?>
                <div class="empty">No students found.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Application</th>
                        <th>Aadhaar</th>
                        <th>Father</th>
                        <th>Mother</th>
                        <th>Status</th>
                        <th>Enrollment ID</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filteredStudents as $index => $student): ?>
                    <?php $isDuplicate = $student['_is_duplicate']; ?>
                    <tr class="<?= $isDuplicate ? 'duplicate-row' : '' ?>">
                        <td><?= $index + 1 ?></td>

                        <td>
                            <div class="name">
                                <?= htmlspecialchars($student['student_full_name'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                            <?php if ($isDuplicate): ?>
                                <span class="group-tag">REPEAT ENROLLMENT</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['dob'], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(ucfirst($student['gender']), ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td class="app-code">
                            <?= htmlspecialchars($student['application_code'], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= $student['student_aadhar']
                                ? htmlspecialchars($student['student_aadhar'], ENT_QUOTES, 'UTF-8')
                                : '—' ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['father_name'] ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student['mother_name'] ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?php if ($isDuplicate): ?>
                                <span class="status duplicate">DUPLICATE</span>
                            <?php else: ?>
                                <span class="status unique">UNIQUE</span>
                            <?php endif; ?>
                        </td>

                        <td><?= (int)$student['id'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="legend">
            <span><span class="dot green"></span> First enrollment / retained record</span>
            <span><span class="dot red"></span> Later repeated enrollment</span>
        </div>
    </div>

</div>
</body>
</html>
