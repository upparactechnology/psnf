<?php
$layout    = 'app';
$pageTitle = 'Academic Workspace';
$breadcrumbs = [];
ob_start();

$db = \Core\Application::$app->db;
$recentStudents = $db->select(
    "SELECT * FROM students WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 4",
    [\Core\Database::getTenantId()]
);
?>

<div class="max-w-6xl mx-auto space-y-8 py-2">

    <!-- Title & Description -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                    🎓
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Academic Workspace</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Manage students, admissions, classes, timetables, and academic records</p>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= url('academics/students/create') ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">
                + New Student
            </a>
            <a href="<?= url('academics/admissions') ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50">
                + Admission
            </a>
            <a href="<?= url('academics/classes') ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50">
                Create Class
            </a>
            <a href="<?= url('academics/report-cards') ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50">
                Generate Report Card
            </a>
        </div>
    </div>

    <!-- Top Row: Actionable Overview KPIs -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm">
        <div class="p-2">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Students</span>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['students'] ?? 0 ?></p>
        </div>
        <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Admissions</span>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['admissions'] ?? 0 ?></p>
        </div>
        <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Absent Today</span>
            <p class="text-xl font-extrabold text-amber-500 mt-1"><?= $stats['absent'] ?? 0 ?></p>
        </div>
        <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Pending Reports</span>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['pending_rc'] ?? 0 ?></p>
        </div>
    </div>

    <!-- Workspace Operations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Pending Tasks & Priorities -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Pending Academic Tasks</h3>
                <span class="text-2xs font-semibold px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-500"><?= count($pendingAdmissions) + ($stats['pending_rc'] > 0 ? 1 : 0) ?> Items Due</span>
            </div>
            <div class="space-y-3 text-xs">
                <?php if (empty($pendingAdmissions) && $stats['pending_rc'] == 0): ?>
                    <p class="text-slate-500 text-center py-4">All caught up! No pending tasks.</p>
                <?php else: ?>
                    <?php foreach ($pendingAdmissions as $adm): ?>
                        <a href="<?= url('academics/admissions') ?>" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">New Student Admission for <strong><?= e($adm['first_name'] . ' ' . $adm['last_name']) ?></strong> requires review</span>
                            <span class="text-2xs text-indigo-500 font-bold">Review →</span>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($stats['pending_rc'] > 0): ?>
                        <a href="<?= url('academics/report-cards') ?>" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Publish term evaluation report cards (<strong><?= $stats['pending_rc'] ?></strong> pending students)</span>
                            <span class="text-2xs text-indigo-500 font-bold">Publish →</span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Academic Activity Log -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Recent Activity</h3>
                <span class="text-2xs text-slate-400 font-mono">Real-time</span>
            </div>
            <div class="space-y-3 text-xs">
                <?php if (empty($recentLogs)): ?>
                    <p class="text-slate-500 text-center py-4">No recent activity logged.</p>
                <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></span>
                            <div>
                                <p class="text-slate-700 dark:text-slate-300">
                                    <strong><?= e($log['user_name'] ?: 'System') ?></strong>: 
                                    <span class="text-slate-500 font-mono text-[11px]"><?= e($log['event']) ?> <?= !empty($log['description']) ? '(' . e($log['description']) . ')' : '' ?></span>
                                </p>
                                <span class="text-[10px] text-slate-400"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Recently Accessed Students -->
    <div class="space-y-4">
        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Recently Accessed Students</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach (array_slice($recentStudents ?? [], 0, 4) as $s): ?>
            <a href="<?= url('academics/students/' . $s['id']) ?>" class="group p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 hover:border-indigo-500/40 transition-all shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-slate-700/10">
                        <?= strtoupper(substr($s['first_name'], 0, 1) . (isset($s['last_name'][0]) ? substr($s['last_name'], 0, 1) : 'S')) ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-500 transition-colors"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></p>
                        <p class="text-2xs font-mono text-slate-400"><?= e($s['admission_number'] ?? '—') ?></p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
