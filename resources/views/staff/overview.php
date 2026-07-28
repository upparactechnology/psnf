<?php
$layout    = 'app';
$pageTitle = 'Staff Management Workspace Overview';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Staff Workspace']];
ob_start();
?>

<div class="space-y-8 max-w-6xl mx-auto">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Staff Management Workspace</h1>
            <p class="text-xs text-slate-500 mt-0.5">Centralized Enterprise HRMS for Employees, Attendance Engine, Leave & Payroll</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('staff/employees') ?>" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Add Employee</a>
            <form action="<?= url('staff/payroll/run') ?>" method="POST" class="inline">
                <?= \Core\View::csrf() ?>
                <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition-all">💰 Generate Payroll</button>
            </form>
        </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Total Active Staff</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalEmployees ?></p>
            <span class="text-2xs text-emerald-500 font-medium">Full-time Faculty & Staff</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Departments</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalDepartments ?></p>
            <span class="text-2xs text-indigo-500 font-medium">Configured Units</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Designations</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalDesignations ?></p>
            <span class="text-2xs text-purple-500 font-medium">Configurable Roles</span>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm space-y-1">
            <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider">User Accounts</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= $totalUsers ?></p>
            <span class="text-2xs text-amber-500 font-medium">Linked Credentials</span>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <a href="<?= url('staff/employees') ?>" class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 hover:border-indigo-500/50 transition-all space-y-2 group">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-all">👨</div>
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Staff Directory</h3>
            <p class="text-xs text-slate-500">Manage all employee records, profiles, assigned departments & designations.</p>
        </a>

        <a href="<?= url('staff/attendance') ?>" class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 hover:border-indigo-500/50 transition-all space-y-2 group">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-all">🕒</div>
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Attendance Engine</h3>
            <p class="text-xs text-slate-500">Policy-driven shift tracking, grace periods, clock in/out logs & overtime rules.</p>
        </a>

        <a href="<?= url('staff/payroll') ?>" class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 hover:border-indigo-500/50 transition-all space-y-2 group">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-all">💰</div>
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Payroll Engine</h3>
            <p class="text-xs text-slate-500">Attendance-driven automated salary structures, deductions, and payslip generation.</p>
        </a>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
