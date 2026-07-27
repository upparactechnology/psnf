<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$config = require __DIR__ . '/config.php';

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}


$adminPass = $config['admin_password'] ?? 'admin123';
$error = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['enrollment_admin_auth']);
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $identity  = trim($_POST['admin_identity'] ?? '');
    $inputPass = $_POST['admin_password'] ?? '';

    if (!empty($identity)) {
        $authenticated = false;

        // 1. Try querying 'admins' table first
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE (email = :id OR username = :id OR name = :id OR phone = :id) LIMIT 1");
            $stmt->execute([':id' => $identity]);
            $adminUser = $stmt->fetch();

            if ($adminUser && (password_verify($inputPass, $adminUser['password']) || (isset($adminUser['password']) && $adminUser['password'] === md5($inputPass)) || $inputPass === $adminPass)) {
                $_SESSION['enrollment_admin_auth'] = true;
                $_SESSION['enrollment_admin_user'] = $adminUser['name'] ?? $adminUser['username'] ?? $identity;
                $authenticated = true;
            }
        } catch (PDOException $e) {
            // admins table might not exist or columns differ
        }

        // 2. Fallback to 'users' table if not authenticated
        if (!$authenticated) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = :id OR name = :id OR phone = :id) LIMIT 1");
                $stmt->execute([':id' => $identity]);
                $user = $stmt->fetch();

                if ($user && (password_verify($inputPass, $user['password']) || $inputPass === $adminPass)) {
                    $_SESSION['enrollment_admin_auth'] = true;
                    $_SESSION['enrollment_admin_user'] = $user['name'] ?? $identity;
                    $authenticated = true;
                }
            } catch (PDOException $e) {
                // Ignore fallback exception
            }
        }

        // 3. Fallback to config password
        if (!$authenticated && $inputPass === $adminPass) {
            $_SESSION['enrollment_admin_auth'] = true;
            $_SESSION['enrollment_admin_user'] = 'Admin';
            $authenticated = true;
        }

        if (!$authenticated) {
            $error = 'Invalid Email/Username or Password!';
        }
    } else {
        $error = 'Please enter Email/Username and Password!';
    }
}

$isAuth = !empty($_SESSION['enrollment_admin_auth']);

if ($isAuth && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = $_POST['action_type'] ?? '';

    // DELETE ENROLLMENT
    if ($actionType === 'delete_enrollment') {
        $id = (int)($_POST['enrollment_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM online_enrollments WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $_SESSION['admin_msg'] = "Application #{$id} has been deleted successfully.";
        }
        $currentStatus = $_GET['status'] ?? 'pending';
        header("Location: admin.php?status=" . urlencode($currentStatus));
        exit;
    }

    // UPDATE ENROLLMENT
    if ($actionType === 'update_enrollment') {
        $id = (int)($_POST['enrollment_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE online_enrollments SET 
                student_full_name = :sname,
                dob = :dob,
                gender = :gender,
                student_aadhar = :saadhar,
                address = :address,
                father_name = :fname,
                father_phone = :fphone,
                father_aadhar = :faadhar,
                mother_name = :mname,
                mother_phone = :mphone,
                mother_aadhar = :maadhar,
                status = :status,
                admin_notes = :notes,
                processed_at = NOW()
                WHERE id = :id");

            $stmt->execute([
                ':sname'   => trim($_POST['student_full_name'] ?? ''),
                ':dob'     => trim($_POST['dob'] ?? ''),
                ':gender'  => trim($_POST['gender'] ?? 'male'),
                ':saadhar' => trim($_POST['student_aadhar'] ?? ''),
                ':address' => trim($_POST['address'] ?? ''),
                ':fname'   => trim($_POST['father_name'] ?? ''),
                ':fphone'  => trim($_POST['father_phone'] ?? ''),
                ':faadhar' => trim($_POST['father_aadhar'] ?? ''),
                ':mname'   => trim($_POST['mother_name'] ?? ''),
                ':mphone'  => trim($_POST['mother_phone'] ?? ''),
                ':maadhar' => trim($_POST['mother_aadhar'] ?? ''),
                ':status'  => trim($_POST['status'] ?? 'pending'),
                ':notes'   => trim($_POST['admin_notes'] ?? ''),
                ':id'      => $id
            ]);
            $_SESSION['admin_msg'] = "Application #{$id} updated successfully.";
        }
        $currentStatus = $_POST['redirect_status'] ?? $_GET['status'] ?? 'pending';
        header("Location: admin.php?status=" . urlencode($currentStatus));
        exit;
    }
}

$adminMsg = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['admin_msg']);

if ($isAuth) {
    $status = $_GET['status'] ?? 'pending';
    $stmt = $pdo->prepare("SELECT * FROM online_enrollments WHERE status = :status ORDER BY created_at DESC");
    $stmt->execute([':status' => $status]);
    $enrollments = $stmt->fetchAll();
}
function e($val) { return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment Admin Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .white-card { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
        .print-area { display: block; }
        @media print {
            body { background: #fff !important; color: #000 !important; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .white-card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="min-h-screen p-4 sm:p-8" x-data="{ viewRecord: null, editRecord: null, formatDate12h(dStr) { if (!dStr) return ''; const dt = new Date(dStr.replace(/-/g, '/')); return isNaN(dt.getTime()) ? dStr : dt.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }); } }">

    <?php if (!$isAuth): ?>
    <!-- Light Theme Login Box -->
    <div class="max-w-md mx-auto mt-16">
        <div class="white-card rounded-3xl p-8 space-y-6 text-center shadow-xl">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Admin Portal Login</h1>
                <p class="text-xs text-slate-500 mt-1">Sign in using system Admin User credentials.</p>
            </div>

            <?php if ($error): ?>
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs font-semibold">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email / Username</label>
                    <input type="text" name="admin_identity" required placeholder="admin@example.com" class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:bg-white outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                    <input type="password" name="admin_password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 text-slate-900 rounded-xl py-2.5 px-3.5 text-sm focus:border-indigo-500 focus:bg-white outline-none transition-all">
                </div>
                <button type="submit" name="login_submit" value="1" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-indigo-600/20">
                    Sign In
                </button>
            </form>
            <div class="pt-2">
                <a href="index.php" class="text-xs text-slate-400 hover:text-indigo-600">← Back to Student Enrollment Form</a>
            </div>
        </div>
    </div>
    <?php else: ?>

    <!-- Admin Directory View -->
    <div class="max-w-7xl mx-auto space-y-6 no-print">
        
        <!-- Navbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Online Student Applications</h1>
                    <p class="text-xs text-slate-500">Structured Data Directory & Detailed Printable Reports</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="admin.php?status=pending" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($status === 'pending') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">Pending</a>
                <a href="admin.php?status=approved" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($status === 'approved') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">Approved</a>
                <a href="admin.php?status=rejected" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= ($status === 'rejected') ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">Rejected</a>
                <a href="admin.php?action=logout" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">Logout</a>
            </div>
        </div>

        <?php if (!empty($adminMsg)): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span><?= e($adminMsg) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($enrollments)): ?>
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 text-slate-400 text-sm">
                No <?= e($status) ?> applications recorded in the database.
            </div>
        <?php else: ?>
            <!-- Rows and Columns Data Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4">App Code</th>
                                <th class="py-3.5 px-4">Student Details</th>
                                <th class="py-3.5 px-4">DOB & Gender</th>
                                <th class="py-3.5 px-4">Father Name & Phone</th>
                                <th class="py-3.5 px-4">Mother Name & Phone</th>
                                <th class="py-3.5 px-4 text-center">Pickups</th>
                                <th class="py-3.5 px-4 text-center">Submitted At</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($enrollments as $row): ?>
                                <?php $pickups = json_decode($row['pickup_persons_json'] ?? '[]', true) ?: []; ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <!-- App Code -->
                                    <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 whitespace-nowrap">
                                        <?= e($row['application_code']) ?>
                                    </td>

                                    <!-- Student Name & Photo -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($row['student_photo'])): ?>
                                                <img src="<?= e($row['student_photo']) ?>" class="w-9 h-10 rounded-lg object-cover border border-slate-200 shadow-sm">
                                            <?php else: ?>
                                                <div class="w-9 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-[9px]">No Pic</div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-bold text-slate-900"><?= e($row['student_full_name']) ?></div>
                                                <div class="text-[11px] text-slate-400">Aadhar: <?= e($row['student_aadhar'] ?: 'N/A') ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- DOB & Gender -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-medium text-slate-700"><?= e($row['dob']) ?></div>
                                        <div class="text-[11px] text-slate-400 capitalize"><?= e($row['gender']) ?></div>
                                    </td>

                                    <!-- Father -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-medium text-slate-800"><?= e($row['father_name']) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($row['father_phone']) ?></div>
                                    </td>

                                    <!-- Mother -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-medium text-slate-800"><?= e($row['mother_name']) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($row['mother_phone']) ?></div>
                                    </td>

                                    <!-- Pickups Count -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <?= count($pickups) ?> Person(s)
                                        </span>
                                    </td>

                                    <!-- Submitted At -->
                                    <td class="py-3.5 px-4 text-center text-slate-500 whitespace-nowrap text-[11px]">
                                        <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                                    </td>

                                    <!-- Action Column (View, Edit, Delete) -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1">
                                        <button type="button" @click="viewRecord = <?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-xs transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View
                                        </button>
                                        <button type="button" @click="editRecord = <?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold text-xs transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </button>
                                        <form method="POST" action="admin.php?status=<?= e($status) ?>" onsubmit="return confirm('Are you sure you want to delete this student application permanently?');" class="inline">
                                            <input type="hidden" name="action_type" value="delete_enrollment">
                                            <input type="hidden" name="enrollment_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl font-semibold text-xs transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- DETAILED REPORT FORM MODAL (WHITE THEME & PRINTABLE) -->
    <div x-show="viewRecord" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 flex items-start justify-center" x-cloak>
        <div class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 relative my-6" @click.outside="viewRecord = null">
            
            <!-- Modal Controls -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-4 no-print">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-mono text-xs font-bold rounded-lg border border-indigo-200" x-text="viewRecord?.application_code"></span>
                    <h2 class="text-xl font-bold text-slate-900">Student Application Report</h2>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Print Report
                    </button>
                    <button type="button" @click="viewRecord = null" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Clean Report Document Form Body -->
            <div class="space-y-6 text-slate-800">
                
                <!-- Report Header Title -->
                <div class="text-center border-b border-slate-200 pb-4">
                    <h2 class="text-2xl font-extrabold text-slate-900">STUDENT ENROLLMENT FORM REPORT</h2>
                    <p class="text-xs text-slate-500 mt-1">Application Code: <span class="font-mono font-bold text-indigo-600" x-text="viewRecord?.application_code"></span> | Submitted Date: <span class="font-medium" x-text="formatDate12h(viewRecord?.created_at)"></span></p>
                </div>

                <!-- 1. Student Personal Details -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                    <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider border-b border-slate-200 pb-2">1. Student Details</h3>
                    <div class="flex flex-col sm:flex-row gap-6 items-start">
                        <!-- Photo preview -->
                        <template x-if="viewRecord?.student_photo">
                            <div class="w-24 h-28 rounded-xl border border-slate-300 overflow-hidden bg-slate-100 shrink-0 shadow-sm">
                                <img :src="viewRecord?.student_photo" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs w-full">
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Full Name</strong> <span class="text-sm font-bold text-slate-900" x-text="viewRecord?.student_full_name"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Date of Birth</strong> <span class="font-medium" x-text="viewRecord?.dob"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Gender</strong> <span class="capitalize font-medium" x-text="viewRecord?.gender"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Student Aadhar Card No.</strong> <span class="font-medium" x-text="viewRecord?.student_aadhar || 'N/A'"></span></div>
                            <div class="sm:col-span-2"><strong class="text-slate-500 block text-[10px] uppercase">Residential Address</strong> <span class="font-medium" x-text="viewRecord?.address || 'N/A'"></span></div>
                            <template x-if="viewRecord?.student_aadhar_doc">
                                <div class="sm:col-span-2 pt-1 no-print">
                                    <a :href="viewRecord?.student_aadhar_doc" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-indigo-600 font-semibold text-xs shadow-sm hover:bg-slate-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg> View Student Aadhar Document
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 2. Parents Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Father Card -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider border-b border-slate-200 pb-2 flex items-center justify-between">
                            <span>Father Details</span>
                            <template x-if="viewRecord?.father_photo">
                                <img :src="viewRecord?.father_photo" class="w-8 h-10 rounded object-cover border border-slate-300">
                            </template>
                        </h4>
                        <div class="space-y-2 text-xs">
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Father Name</strong> <span class="font-bold text-slate-900" x-text="viewRecord?.father_name"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Mobile Phone</strong> <span class="font-medium" x-text="viewRecord?.father_phone"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Aadhar Card No.</strong> <span class="font-medium" x-text="viewRecord?.father_aadhar"></span></div>
                            <template x-if="viewRecord?.father_aadhar_doc">
                                <div class="pt-2 no-print">
                                    <a :href="viewRecord?.father_aadhar_doc" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-300 rounded-lg text-indigo-600 font-semibold text-xs shadow-sm hover:bg-slate-50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg> Father Aadhar Document
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Mother Card -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
                        <h4 class="text-xs font-bold text-pink-600 uppercase tracking-wider border-b border-slate-200 pb-2 flex items-center justify-between">
                            <span>Mother Details</span>
                            <template x-if="viewRecord?.mother_photo">
                                <img :src="viewRecord?.mother_photo" class="w-8 h-10 rounded object-cover border border-slate-300">
                            </template>
                        </h4>
                        <div class="space-y-2 text-xs">
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Mother Name</strong> <span class="font-bold text-slate-900" x-text="viewRecord?.mother_name"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Mobile Phone</strong> <span class="font-medium" x-text="viewRecord?.mother_phone"></span></div>
                            <div><strong class="text-slate-500 block text-[10px] uppercase">Aadhar Card No.</strong> <span class="font-medium" x-text="viewRecord?.mother_aadhar"></span></div>
                            <template x-if="viewRecord?.mother_aadhar_doc">
                                <div class="pt-2 no-print">
                                    <a :href="viewRecord?.mother_aadhar_doc" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-300 rounded-lg text-indigo-600 font-semibold text-xs shadow-sm hover:bg-slate-50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 1H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg> Mother Aadhar Document
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 3. Pickup Persons -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                    <h3 class="text-sm font-bold text-emerald-700 uppercase tracking-wider border-b border-slate-200 pb-2">3. Authorized Pickup Persons</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="(pk, pIdx) in JSON.parse(viewRecord?.pickup_persons_json || '[]')" :key="pIdx">
                            <div class="bg-white p-4 rounded-xl border border-slate-200 flex gap-4 items-start shadow-sm">
                                <template x-if="pk.photo">
                                    <img :src="pk.photo" class="w-14 h-16 rounded-lg object-cover border border-slate-300 shrink-0">
                                </template>
                                <div class="space-y-1 text-xs">
                                    <div class="font-bold text-slate-900" x-text="pk.name"></div>
                                    <div class="text-slate-500">Relation: <span class="text-slate-800 font-medium" x-text="pk.relationship || 'N/A'"></span></div>
                                    <div class="text-slate-500">Phone: <span class="text-slate-800 font-medium" x-text="pk.phone || 'N/A'"></span></div>
                                    <template x-if="pk.is_emergency">
                                        <span class="inline-block px-2 py-0.5 bg-red-50 text-red-600 font-bold text-[9px] rounded">Emergency Contact</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- EDIT APPLICATION RECORD MODAL -->
    <div x-show="editRecord" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6 flex items-start justify-center no-print" x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 relative my-6" @click.outside="editRecord = null">
            
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Edit Application Details</h2>
                        <p class="text-xs text-slate-500 font-mono" x-text="editRecord?.application_code"></p>
                    </div>
                </div>
                <button type="button" @click="editRecord = null" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="admin.php?status=<?= e($status) ?>" class="space-y-5">
                <input type="hidden" name="action_type" value="update_enrollment">
                <input type="hidden" name="enrollment_id" :value="editRecord?.id">
                <input type="hidden" name="redirect_status" value="<?= e($status) ?>">

                <!-- Status & Notes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Application Status</label>
                        <select name="status" :value="editRecord?.status" class="w-full bg-white border border-slate-300 rounded-xl py-2 px-3 text-sm font-semibold text-slate-800 outline-none focus:border-indigo-600">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Admin Notes</label>
                        <input type="text" name="admin_notes" :value="editRecord?.admin_notes" placeholder="Optional admin comments..." class="w-full bg-white border border-slate-300 rounded-xl py-2 px-3 text-sm text-slate-800 outline-none focus:border-indigo-600">
                    </div>
                </div>

                <!-- Student Info -->
                <div class="space-y-3 border-t border-slate-200 pt-4">
                    <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Student Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Student Full Name</label>
                            <input type="text" name="student_full_name" required :value="editRecord?.student_full_name" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date of Birth</label>
                            <input type="date" name="dob" required :value="editRecord?.dob" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Gender</label>
                            <select name="gender" :value="editRecord?.gender" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Student Aadhar Card No.</label>
                            <input type="text" name="student_aadhar" :value="editRecord?.student_aadhar" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Residential Address</label>
                            <input type="text" name="address" :value="editRecord?.address" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                    </div>
                </div>

                <!-- Parents Details -->
                <div class="space-y-3 border-t border-slate-200 pt-4">
                    <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Parents Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Father Name</label>
                            <input type="text" name="father_name" :value="editRecord?.father_name" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Father Phone</label>
                            <input type="text" name="father_phone" :value="editRecord?.father_phone" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Father Aadhar No.</label>
                            <input type="text" name="father_aadhar" :value="editRecord?.father_aadhar" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Mother Name</label>
                            <input type="text" name="mother_name" :value="editRecord?.mother_name" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Mother Phone</label>
                            <input type="text" name="mother_phone" :value="editRecord?.mother_phone" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Mother Aadhar No.</label>
                            <input type="text" name="mother_aadhar" :value="editRecord?.mother_aadhar" class="w-full bg-slate-50 border border-slate-300 rounded-xl py-2 px-3 text-sm outline-none focus:bg-white focus:border-indigo-600">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                    <button type="button" @click="editRecord = null" class="px-5 py-2.5 border border-slate-300 text-slate-700 hover:bg-slate-100 rounded-xl text-xs font-semibold">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>

</body>
</html>

