<?php
$layout    = 'app';
$pageTitle = 'Portal Home';
$breadcrumbs = [['label' => 'Portal Home']];
ob_start();
?>

<div x-data="{ showLogs: false }" class="max-w-6xl mx-auto space-y-8">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-200 dark:border-slate-800/50 bg-white dark:bg-slate-900/30 backdrop-blur">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Welcome, <?= e(auth()['name'] ?? 'Administrator') ?>!</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Select an application from the launcher below to get started.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showLogs = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all border border-slate-200 dark:border-slate-700/50 shadow-md">
                <svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Audit Trail Logs
            </button>
        </div>
    </div>


    <!-- Odoo style App Launcher Grid -->
    <div>
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4 px-2">Applications</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">

            <!-- 1. Academic (Students) -->
            <?php if (in_array('academic', $assignedApps)): ?>
            <a href="<?= url('students') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-indigo-500/30 dark:hover:border-indigo-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Academic Registry</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_students'] ?> Students</span>
            </a>
            <?php endif; ?>

            <!-- 1b. Academic Summary -->
            <?php if (in_array('academic_summary', $assignedApps)): ?>
            <a href="<?= url('classes') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-indigo-500/30 dark:hover:border-indigo-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Academic Summary</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_classes'] ?? 0 ?> Active Classes</span>
            </a>
            <?php endif; ?>

            <!-- 2. Staff Attendance (Face Recognition Logs) -->
            <?php if (in_array('hr', $assignedApps)): ?>
            <a href="<?= url('staff/attendance') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-blue-500/30 dark:hover:border-blue-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Staff Attendance</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30">Kiosk Sync Active</span>
            </a>
            <?php endif; ?>

            <!-- 3. Access Control (Roles) -->
            <?php if (in_array('access_control', $assignedApps)): ?>
            <a href="<?= url('roles') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-emerald-500/30 dark:hover:border-emerald-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Roles & Permissions</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_roles'] ?? 0 ?> Roles</span>
            </a>
            <?php endif; ?>
 
            <!-- 5. Finance & Fees -->
            <?php if (in_array('finance', $assignedApps)): ?>
            <a href="<?= url('fees') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-amber-500/30 dark:hover:border-amber-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Finance & Fees</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_invoices'] ?? 0 ?> Invoices</span>
            </a>
            <?php endif; ?>
 
            <!-- 6. Medical Log -->
            <?php if (in_array('medical', $assignedApps)): ?>
            <a href="<?= url('medical') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-red-500/30 dark:hover:border-red-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-red-500 to-pink-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Medical Logs</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_medical_logs'] ?? 0 ?> Care Profiles</span>
            </a>
            <?php endif; ?>
 
            <!-- 7. Transport & Bus -->
            <?php if (in_array('transport', $assignedApps)): ?>
            <a href="<?= url('transport') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-yellow-500/30 dark:hover:border-yellow-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-yellow-500 to-amber-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors">Transport & Bus</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_routes'] ?? 0 ?> Transit Routes</span>
            </a>
            <?php endif; ?>
 
 
 
            <!-- 8b. File Manager -->
            <?php if (in_array('file_manager', $assignedApps)): ?>
            <a href="/psnf/file%20manager/public/" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-teal-500/30 dark:hover:border-teal-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">File Manager</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_files'] ?? 0 ?> DRM Files</span>
            </a>
            <?php endif; ?>
 
            <!-- 8c. Learning Games -->
            <?php if (in_array('games', $assignedApps)): ?>
            <a href="/psnf/game/index.html" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-fuchsia-500/30 dark:hover:border-fuchsia-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-fuchsia-500 to-pink-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-fuchsia-600 dark:group-hover:text-fuchsia-400 transition-colors">Learning Games</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_game_sessions'] ?? 0 ?> Play Sessions</span>
            </a>
            <?php endif; ?>
 
            <!-- 8d. Certificate Generator -->
            <?php if (has_role('super_admin') || has_role('school_admin') || has_role('manager')): ?>
            <a href="/psnf/certificate_generator/" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-pink-500/30 dark:hover:border-pink-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">Certificates</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_certificates'] ?? 0 ?> Credentials</span>
            </a>
            <?php endif; ?>
 
            <!-- Report Cards -->
            <?php if (in_array('report_cards', $assignedApps)): ?>
            <a href="<?= url('report-cards') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-emerald-500/30 dark:hover:border-emerald-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Report Cards</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_report_cards'] ?? 0 ?> Generated</span>
            </a>
            <?php endif; ?>

            <!-- Parents Portal -->
            <?php if (in_array('parents_dashboard', $assignedApps) || has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('parent')): ?>
            <a href="<?= url('parent/dashboard') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-violet-500/30 dark:hover:border-violet-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-violet-500 to-indigo-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Parents Portal</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_guardians'] ?? 0 ?> Registered Parents</span>
            </a>
            <?php endif; ?>

            <!-- 9. System Config -->
            <?php if (in_array('config', $assignedApps)): ?>
            <a href="<?= url('roles') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-slate-500/30 dark:hover:border-slate-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-slate-600 to-slate-800 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-slate-600 dark:group-hover:text-slate-400 transition-colors">System Config</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30">Settings</span>
            </a>
            <?php endif; ?>

            <!-- Fallback if empty -->
            <?php if (empty($assignedApps)): ?>
            <div class="col-span-full rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/20 p-12 text-center shadow-sm">
                <svg class="w-12 h-12 text-slate-400 dark:text-slate-650 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <h3 class="text-sm font-semibold text-slate-800 dark:text-white">No applications assigned</h3>
                <p class="text-xs text-slate-500 mt-1">Please contact your administrator to assign applications to your account.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Overview Stats Pane -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

        <!-- Admission Pipeline Widget -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-800 dark:text-white mb-5">Admissions Pipeline</h3>
            <?php
            $pipeline = [
                ['key' => 'applied',    'label' => 'Applied',    'color' => 'bg-slate-500'],
                ['key' => 'review',     'label' => 'Under Review','color' => 'bg-yellow-500'],
                ['key' => 'assessment', 'label' => 'Assessment', 'color' => 'bg-blue-500'],
                ['key' => 'approved',   'label' => 'Approved',   'color' => 'bg-emerald-500'],
                ['key' => 'enrolled',   'label' => 'Enrolled',   'color' => 'bg-brand-500'],
            ];
            $total = array_sum($statusCounts) ?: 1;
            foreach ($pipeline as $stage):
                $count = $statusCounts[$stage['key']] ?? 0;
                $pct   = round(($count / $total) * 100);
            ?>
            <div class="mb-4 last:mb-0">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm text-slate-600 dark:text-slate-300"><?= $stage['label'] ?></span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-white"><?= $count ?></span>
                </div>
                <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-2 <?= $stage['color'] ?> rounded-full transition-all duration-700" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Recent Registries Widget -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white">Recent Student Applications</h3>
                <a href="<?= url('students') ?>" class="text-xs text-brand-500 dark:text-brand-400 hover:text-brand-600 dark:hover:text-brand-300 font-medium transition-colors">View All →</a>
            </div>
            <?php if (empty($recentStudents)): ?>
            <div class="text-center py-10">
                <p class="text-sm text-slate-500">No applications recently.</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($recentStudents, 0, 4) as $s): ?>
                <?php
                $statusClasses = [
                    'applied'    => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/30',
                    'review'     => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-700/30',
                    'assessment' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-700/30',
                    'approved'   => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-700/30',
                    'enrolled'   => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-700/30',
                    'withdrawn'  => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-700/30',
                ];
                $sc = $statusClasses[$s['admission_status']] ?? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/50 dark:text-slate-300 dark:border-slate-600/30';
                ?>
                <a href="<?= url('students/' . $s['id']) ?>" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/30 transition-colors group">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0 text-white"
                         style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                        <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 dark:text-white truncate group-hover:text-brand-600 dark:group-hover:text-brand-300 transition-colors">
                            <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                        </p>
                        <p class="text-xs text-slate-500 font-mono"><?= e($s['admission_number'] ?? 'No ADM#') ?></p>
                    </div>
                    <span class="text-2xs px-2 py-0.5 rounded-full font-medium border <?= $sc ?>">
                        <?= ucfirst($s['admission_status']) ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Slide-over Drawer for Audit Logs -->
    <div x-show="showLogs" class="fixed inset-0 overflow-hidden z-[9999]" x-cloak>
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div x-show="showLogs" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showLogs = false"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="showLogs" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="w-screen max-w-md">
                    <div class="h-full flex flex-col bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl overflow-y-scroll">
                        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Audit Trail Logs</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Recent system activity records</p>
                            </div>
                            <button @click="showLogs = false" class="p-1 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="flex-1 p-6 space-y-4">
                            <?php if (empty($recentLogs)): ?>
                            <p class="text-sm text-slate-500 py-6 text-center">No recent activity logs.</p>
                            <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentLogs as $log): ?>
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-700/20">
                                    <div class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0 mt-1.5"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white truncate"><?= e(str_replace('_', ' ', $log['event'])) ?></p>
                                        <?php if ($log['description']): ?>
                                        <p class="text-xs text-slate-650 dark:text-slate-400 mt-0.5 leading-relaxed"><?= e($log['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2 mt-2 text-2xs text-slate-500">
                                            <span class="font-medium text-slate-700 dark:text-slate-500"><?= e($log['user_name'] ?? 'System') ?></span>
                                            <span>·</span>
                                            <span><?= format_date($log['created_at'], 'd M Y, H:i') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
