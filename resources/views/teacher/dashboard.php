<?php
$layout    = 'app';
$pageTitle = 'Teacher Dashboard';
$breadcrumbs = [['label' => 'Teacher Dashboard']];
ob_start();
?>

<div class="max-w-6xl mx-auto space-y-8">

    <!-- Header section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-200 dark:border-slate-800/50 bg-white dark:bg-slate-900/30 backdrop-blur">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Welcome, <?= e(auth()['name'] ?? 'Teacher') ?>!</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Select an application from the launcher below to get started.</p>
        </div>
    </div>

    <!-- Teacher Attendance Info Box -->
    <?php if (isset($teacherAttendance) && $teacherAttendance): ?>
        <?php if ($teacherAttendance['status'] === 'late'): ?>
        <div class="flex items-center gap-3 p-5 rounded-2xl border border-red-750/30 bg-red-950/20 text-red-400 backdrop-blur shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-red-900/30 flex items-center justify-center flex-shrink-0 border border-red-700/30">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold leading-none">Today's Attendance: <span class="text-red-500 font-bold uppercase">Late Check-in</span></p>
                <p class="text-xs text-red-400/80 mt-1">Checked in at <?= date('h:i A', strtotime($teacherAttendance['opened_at'])) ?> (Lecture Time: <?= date('h:i A', strtotime($teacherAttendance['lecture_time'])) ?> + <?= $teacherAttendance['grace_period'] ?>m grace period)</p>
            </div>
        </div>
        <?php else: ?>
        <div class="flex items-center gap-3 p-5 rounded-2xl border border-emerald-700/30 bg-emerald-950/20 text-emerald-400 backdrop-blur shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-900/30 flex items-center justify-center flex-shrink-0 border border-emerald-700/30">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold leading-none">Today's Attendance: <span class="text-emerald-500 font-bold uppercase">On Time</span></p>
                <p class="text-xs text-emerald-400/80 mt-1">Checked in at <?= date('h:i A', strtotime($teacherAttendance['opened_at'])) ?> (Lecture Time: <?= date('h:i A', strtotime($teacherAttendance['lecture_time'])) ?>)</p>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

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

            <!-- 2. HR Directory (Users) -->
            <?php if (in_array('hr', $assignedApps)): ?>
            <a href="<?= url('users') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-blue-500/30 dark:hover:border-blue-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg group-hover:scale-105 transition-transform duration-300 mb-3.5">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">HR Directory</span>
                <span class="text-2xs text-slate-500 mt-1 font-medium bg-slate-100 dark:bg-slate-800/50 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700/30"><?= $stats['total_users'] ?> Accounts</span>
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
            <a href="<?= url('file_manager/public/') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-teal-500/30 dark:hover:border-teal-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
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
            <a href="<?= url('game/index.html') ?>" class="flex flex-col items-center p-5 rounded-2xl border border-slate-200 dark:border-slate-800/40 bg-white dark:bg-slate-900/20 hover:border-fuchsia-500/30 dark:hover:border-fuchsia-500/30 hover:bg-slate-50 dark:hover:bg-slate-900/60 hover:-translate-y-1 transition-all duration-300 group text-center shadow-sm hover:shadow-md">
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

        <!-- Today's Class Schedule Widget -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm flex flex-col">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-9 h-9 rounded-xl bg-brand-500/10 dark:bg-brand-500/20 flex items-center justify-center text-brand-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Today's Class Schedule</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= date('l, d M Y') ?></p>
                </div>
            </div>
            <?php if (empty($teacherSchedule)): ?>
            <div class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-555" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-850 dark:text-slate-200">No lectures scheduled for today.</p>
                <p class="text-2xs text-slate-500 mt-0.5">Enjoy your free day or prepare your materials.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4 flex-1">
                <?php foreach ($teacherSchedule as $item): ?>
                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-150 dark:border-slate-800/40 bg-slate-50/50 dark:bg-slate-900/20 hover:border-brand-500/20 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-all duration-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/10 dark:bg-brand-500/20 flex flex-col items-center justify-center text-brand-600 dark:text-brand-400 font-bold shrink-0">
                            <span class="text-xs"><?= date('g:i', strtotime($item['start_time'])) ?></span>
                            <span class="text-3xs uppercase tracking-wider"><?= date('A', strtotime($item['start_time'])) ?></span>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-white"><?= e($item['subject']) ?></h4>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-550 dark:text-slate-400">
                                <span><?= e($item['class']) ?><?= $item['section'] ? ' - ' . e($item['section']) : '' ?></span>
                                <?php if ($item['room']): ?>
                                <span>•</span>
                                <span class="flex items-center gap-0.5">
                                    <svg class="w-3.5 h-3.5 inline text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Room <?= e($item['room']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
                            <?= date('h:i A', strtotime($item['start_time'])) ?> - <?= date('h:i A', strtotime($item['end_time'])) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Announcements Widget -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/40 p-6 shadow-sm flex flex-col">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Recent Announcements</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Important notices & updates</p>
                </div>
            </div>
            <?php if (empty($announcements)): ?>
            <div class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-550" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-850 dark:text-slate-200">No announcements posted yet.</p>
                <p class="text-2xs text-slate-500 mt-0.5">Keep an eye out for updates from the school admin.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4 flex-1">
                <?php foreach ($announcements as $ann): ?>
                <div class="p-4 rounded-xl border border-slate-150 dark:border-slate-800/40 bg-slate-50/50 dark:bg-slate-900/20 hover:border-amber-500/20 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-white"><?= e($ann['title']) ?></h4>
                        <span class="text-3xs text-slate-500 dark:text-slate-450 font-medium whitespace-nowrap bg-slate-100 dark:bg-slate-800/60 px-2 py-0.5 rounded-full">
                            <?= date('d M Y', strtotime($ann['published_at'])) ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed"><?= nl2br(e($ann['content'])) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
?>
