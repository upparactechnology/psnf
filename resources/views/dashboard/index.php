<?php
$layout    = 'app';
$pageTitle = 'Dashboard';
$breadcrumbs = [];
ob_start();
?>

<div class="space-y-6">

    <!-- Header Panel -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Good Morning, <?= e($user['name'] ?? 'User') ?></h1>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed">Welcome back to the school administration control panel.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-mono"><?= date('l, d M Y') ?></span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        <!-- Academics -->
        <a href="<?= url('academic') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-indigo-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Academics</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Manage students, admissions, and class timetables</p>
                </div>
            </div>
        </a>

        <!-- Staff Management -->
        <a href="<?= url('staff') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-blue-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Staff Management</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Central enterprise HRMS for employees, attendance engine, leaves & payroll</p>
                </div>
            </div>
        </a>

        <!-- Transport -->
        <a href="<?= url('transport') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-yellow-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors">Transport</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Routes, live GPS bus radar, driver manifests, and safety alerts</p>
                </div>
            </div>
        </a>

        <!-- Finance -->
        <a href="<?= url('fees') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-amber-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Finance</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Student fee invoices, receipts logging, and scholarship grants</p>
                </div>
            </div>
        </a>

        <!-- Documents Workspace -->
        <a href="<?= url('documents') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-emerald-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Documents Workspace</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Centralized document repository, student & staff documents, verifications, and templates</p>
                </div>
            </div>
        </a>

        <!-- Certificate Generator -->
        <a href="/psnf/public/certificate_generator/" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-purple-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">Certificate Generator</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Design, issue, and print student achievement & participation certificates</p>
                </div>
            </div>
        </a>

        <!-- File Manager -->
        <a href="/psnf/public/file_manager/public/admin/dashboard" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-cyan-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">File Manager</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Browse, upload, organize and share school documents and media assets</p>
                </div>
            </div>
        </a>

        <!-- Learning Games -->
        <a href="<?= url('games') ?>" class="group p-6 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900/40 hover:border-pink-500/50 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all shadow-sm hover:shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-pink-500/10 text-pink-600 dark:bg-pink-500/20 dark:text-pink-400 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">Learning Games</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Gamified learning activities, quizzes and interactive educational modules</p>
                </div>
            </div>
        </a>

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
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-bold border border-slate-700/10">
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
