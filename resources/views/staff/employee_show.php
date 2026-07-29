<?php
$layout    = 'app';
$pageTitle = 'Employee Profile – ' . e($employee['first_name'] . ' ' . $employee['last_name']);
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace', 'url' => '/staff/overview'], ['label' => 'Staff Directory', 'url' => '/staff/employees'], ['label' => $employee['emp_code']]];
ob_start();

$linkedUser = null;
if (!empty($employee['user_id'])) {
    $linkedUser = \Core\Application::$app->db->selectOne("SELECT id, name, email, is_active FROM users WHERE id = ?", [$employee['user_id']]);
}
?>

<div x-data="{ tab: 'overview' }" class="space-y-6 max-w-5xl mx-auto">

    <!-- Profile Header Card -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 flex flex-col sm:flex-row items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-xl flex items-center justify-center shadow-lg">
                <?= strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white"><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>
                <p class="text-xs text-slate-500 mt-0.5"><span class="font-mono text-indigo-500 font-bold"><?= e($employee['emp_code']) ?></span> • <?= e($employee['designation_title'] ?? 'Staff') ?> (<?= e($employee['department_name'] ?? 'General') ?>)</p>
                <div class="flex items-center gap-3 text-2xs text-slate-400 mt-2 font-mono">
                    <span>Joined: <?= e($employee['joining_date']) ?></span>
                    <span>•</span>
                    <span><?= e($employee['email']) ?></span>
                </div>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($employee['status']) ?></span>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-1 text-xs">
        <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Overview</button>
        <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Attendance History</button>
        <button @click="tab = 'payroll'" :class="tab === 'payroll' ? 'border-b-2 border-indigo-600 text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 font-medium transition-all">Salary & Payroll</button>
    </div>

    <!-- Tab 1: Overview & Edit -->
    <div x-show="tab === 'overview'" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Employment Details & Shift Limits</h3>
                <div class="space-y-2 text-slate-600 dark:text-slate-300">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Department:</span> <span class="font-bold"><?= e($employee['department_name'] ?? 'N/A') ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Designation:</span> <span class="font-bold"><?= e($employee['designation_title'] ?? 'N/A') ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Employment Type:</span> <span class="font-bold uppercase"><?= e($employee['employment_type']) ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Min Clock In Time:</span> <span class="font-bold font-mono text-emerald-500"><?= date('h:i A', strtotime($employee['min_clock_in'] ?? '09:00:00')) ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Max Clock Out Time:</span> <span class="font-bold font-mono text-indigo-500"><?= date('h:i A', strtotime($employee['max_clock_out'] ?? '17:00:00')) ?></span></div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400">Linked User Account:</span> 
                        <span class="font-bold">
                            <?php if ($linkedUser): ?>
                                <a href="<?= url('users/' . $linkedUser['id'] . '/edit?redirect_to=' . urlencode('/staff/employees/' . $employee['id'])) ?>" class="text-indigo-500 hover:underline">
                                    <?= e($linkedUser['name']) ?> (<?= e($linkedUser['email']) ?>)
                                </a>
                            <?php else: ?>
                                <span class="text-slate-500">Not Linked</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Edit Employee Form Card -->
            <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Edit Employee Shift & Profile</h3>
                <form action="<?= url('staff/employees/' . $employee['id']) ?>" method="POST" class="space-y-3">
                    <?= \Core\View::csrf() ?>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">First Name</label>
                            <input type="text" name="first_name" value="<?= e($employee['first_name']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="<?= e($employee['last_name']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="<?= e($employee['email']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Min Clock In</label>
                            <input type="time" name="min_clock_in" value="<?= e(substr($employee['min_clock_in'] ?? '09:00:00', 0, 5)) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-1">Max Clock Out</label>
                            <input type="time" name="max_clock_out" value="<?= e(substr($employee['max_clock_out'] ?? '17:00:00', 0, 5)) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-400 mb-1">Basic Monthly Salary (₹)</label>
                        <input type="number" step="0.01" name="salary_basic" value="<?= e($employee['salary_basic']) ?>" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-slate-900 dark:text-white font-mono">
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition-all">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab 2: Attendance -->
    <div x-show="tab === 'attendance'" class="space-y-4 text-xs" x-cloak>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 uppercase tracking-wider text-2xs p-3">
                    <tr>
                        <th class="p-3">Date</th>
                        <th class="p-3">Clock In</th>
                        <th class="p-3">Clock Out</th>
                        <th class="p-3">Hours</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php foreach ($attendance as $att): ?>
                    <tr>
                        <td class="p-3 font-mono font-bold"><?= e($att['date']) ?></td>
                        <td class="p-3 font-mono"><?= e($att['clock_in']) ?></td>
                        <td class="p-3 font-mono"><?= e($att['clock_out']) ?></td>
                        <td class="p-3 font-mono"><?= e($att['working_hours']) ?> hrs</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-2xs font-bold bg-emerald-500/10 text-emerald-500 uppercase"><?= e($att['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Payroll -->
    <div x-show="tab === 'payroll'" class="space-y-4 text-xs" x-cloak>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-3">
            <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Configured Salary Structure</h3>
            <div class="space-y-2">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Basic Monthly Salary:</span> <span class="font-mono font-bold text-slate-900 dark:text-white">₹<?= number_format((float)$employee['salary_basic'], 2) ?></span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">HRA Allowance (40%):</span> <span class="font-mono font-bold text-emerald-500">₹<?= number_format((float)$employee['salary_basic'] * 0.4, 2) ?></span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Estimated Gross Monthly Pay:</span> <span class="font-mono font-bold text-indigo-500">₹<?= number_format((float)$employee['salary_basic'] * 1.4, 2) ?></span></div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
