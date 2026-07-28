<?php
$layout    = 'app';
$pageTitle = 'Portal Home';
$breadcrumbs = [];
ob_start();

$user = auth();
$userName = e($user['name'] ?? 'User');
?>

<div x-data="{ showLogs: false }" class="max-w-5xl mx-auto space-y-10 py-4">

    <!-- Greeting & Today's School Overview -->
    <div class="space-y-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Good Morning, <?= $userName ?> 👋</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Here is what's happening at school today.</p>
        </div>

        <!-- Today's Activity Strip (5 Clean Metrics, No Clutter) -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 shadow-sm">
            <div class="p-2">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Students Present</span>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['total_students'] ?? 23 ?> <span class="text-xs text-slate-400 font-normal">Enrolled</span></p>
            </div>
            <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Pending Admissions</span>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $statusCounts['applied'] ?? 4 ?></p>
            </div>
            <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Fee Invoices</span>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['total_invoices'] ?? 2 ?></p>
            </div>
            <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Medical Care</span>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['total_medical_logs'] ?? 1 ?> <span class="text-xs text-emerald-500 font-medium">Profiles</span></p>
            </div>
            <div class="p-2 border-l border-slate-100 dark:border-slate-800/60">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Transport Routes</span>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-1"><?= $stats['total_routes'] ?? 1 ?> <span class="text-xs text-slate-400 font-normal">Active</span></p>
            </div>
        </div>
    </div>

    <!-- WORKSPACES (6 Large Clean Workspaces) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Workspaces</h2>
            <span class="text-xs text-slate-400">Press <kbd class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 font-mono text-2xs">CTRL+K</kbd> to search</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- 🎓 1. Academics -->
            <a href="<?= url('academic') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-indigo-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        🎓
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Academics</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Manage students, admissions, IEP programs and class timetables</p>
                    </div>
                </div>
            </a>

            <!-- 👨‍🏫 2. Staff Management Workspace -->
            <a href="<?= url('staff') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-blue-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        👨‍🏫
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Staff Management</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Central enterprise HRMS for employees, attendance engine, leaves & payroll</p>
                    </div>
                </div>
            </a>

            <!-- 🚌 3. Transport -->
            <a href="<?= url('transport') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-yellow-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        🚌
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors">Transport</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Routes, live GPS bus radar, driver manifests, and safety alerts</p>
                    </div>
                </div>
            </a>

            <!-- ❤️ 4. Medical -->
            <a href="<?= url('medical') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-red-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        ❤️
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Medical</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Student care plans, medication logs, allergies, and clinical incidents</p>
                    </div>
                </div>
            </a>

            <!-- 💰 5. Finance -->
            <a href="<?= url('fees') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-amber-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        💰
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Finance</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Student fee invoices, receipts logging, and scholarship grants</p>
                    </div>
                </div>
            </a>

            <!-- 📄 6. Documents & Report Cards -->
            <a href="<?= url('report-cards') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-emerald-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        📄
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Documents</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Dynamic QR report cards, student IDs, and certificate generator</p>
                    </div>
                </div>
            </a>

            <!-- 📜 7. Certificate Generator -->
            <a href="/psnf/public/certificate_generator/" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-purple-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        📜
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Certificate Generator</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Design, issue, and print student achievement & participation certificates</p>
                    </div>
                </div>
            </a>

            <!-- 📂 8. File Manager -->
            <a href="/psnf/public/file_manager/public/admin/dashboard" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-cyan-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        📂
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">File Manager</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Browse, upload, organize and share school documents and media assets</p>
                    </div>
                </div>
            </a>

            <!-- 🎮 9. Games & Learning Activities -->
            <a href="<?= url('games') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-pink-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-pink-500/10 text-pink-600 dark:bg-pink-500/20 dark:text-pink-400 flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        🎮
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">Learning Games</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Gamified learning activities, quizzes and interactive educational modules</p>
                    </div>
                </div>
            </a>

        </div>
    </div>

    <!-- Recent Activity -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Recent Activity</h3>
            <button @click="showLogs = true" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">View Audit Logs →</button>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 divide-y divide-slate-100 dark:divide-slate-800/40">
            <?php foreach (array_slice($recentStudents ?? [], 0, 3) as $s): ?>
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-bold">
                        <?= strtoupper(substr($s['first_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-900 dark:text-white"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></p>
                        <p class="text-[11px] text-slate-400">Student Application registered</p>
                    </div>
                </div>
                <span class="text-[11px] font-mono text-slate-400"><?= e($s['admission_number'] ?? '—') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
