<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
            window.isDark = false;
        } else {
            document.documentElement.classList.add('dark');
            window.isDark = true;
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PSNF ERP' ?></title>
    <meta name="description" content="Pearl Special Needs Foundation — ERP System">
    <meta name="csrf-token" content="<?= \Core\View::csrfToken() ?>">

    <!-- Tailwind CSS (Local Fallback) -->
    <script src="<?= url('js/tailwindcss.js') ?>"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0f4ff',
                            100: '#e0e9ff',
                            200: '#c7d7fe',
                            300: '#a5b8fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        surface: {
                            50:  '#f8fafc',
                            100: '#f1f5f9',
                            800: '#1e293b',
                            850: '#172033',
                            900: '#0f172a',
                            950: '#080d1a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>

    <style type="tailwindcss">
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        /* Sidebar transition */
        .sidebar-link {
            @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200;
        }
        .sidebar-link.active {
            @apply bg-brand-600/20 text-brand-400 border border-brand-500/20;
        }

        /* Glass card */
        .glass {
            backdrop-filter: blur(16px);
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #818cf8, #a78bfa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Status badges */
        .badge-applied    { @apply bg-slate-700/50 text-slate-300 border border-slate-600/30; }
        .badge-review     { @apply bg-yellow-900/30 text-yellow-400 border border-yellow-700/30; }
        .badge-assessment { @apply bg-blue-900/30 text-blue-400 border border-blue-700/30; }
        .badge-approved   { @apply bg-emerald-900/30 text-emerald-400 border border-emerald-700/30; }
        .badge-enrolled   { @apply bg-brand-900/30 text-brand-400 border border-brand-700/30; }
        .badge-withdrawn  { @apply bg-red-900/30 text-red-400 border border-red-700/30; }

        /* HTMX loading indicator */
        .htmx-indicator { opacity: 0; transition: opacity 200ms; }
        .htmx-request .htmx-indicator { opacity: 1; }
        .htmx-request.htmx-indicator { opacity: 1; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-surface-950 dark:text-slate-200 font-sans antialiased min-h-screen" x-data="{ sidebarOpen: true, mobileNav: false, isDark: window.isDark, toggleTheme() { this.isDark = !this.isDark; if (this.isDark) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); } } }">

<!-- Flash Messages -->
<?php $success = \Core\Session::getFlash('success'); $error = \Core\Session::getFlash('error'); ?>
<?php if ($success || $error): ?>
<div id="flash-container" class="fixed top-4 right-4 z-[9999] space-y-2" x-data="{ show: true }" x-show="show" x-transition>
    <?php if ($success): ?>
    <div class="flex items-center gap-3 bg-emerald-900/80 backdrop-blur border border-emerald-700/50 text-emerald-300 px-4 py-3 rounded-xl shadow-xl max-w-sm" x-init="setTimeout(() => show = false, 4000)">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="text-sm font-medium"><?= e($success) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="flex items-center gap-3 bg-red-900/80 backdrop-blur border border-red-700/50 text-red-300 px-4 py-3 rounded-xl shadow-xl max-w-sm" x-init="setTimeout(() => show = false, 5000)">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium"><?= e($error) ?></span>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
$currentPath = \Core\Application::$app->request->getPath();
$isDashboard = ($currentPath === '/dashboard' || $currentPath === '/teacher/dashboard' || $currentPath === '/' || $currentPath === '');
$user = auth();
$db = \Core\Application::$app->db;
$assignedApps = [];
if ($user) {
    $hasAppAccessRecords = $db->selectOne("SELECT 1 FROM user_apps WHERE user_id = ?", [$user['id']]);
    if ($hasAppAccessRecords) {
        $userApps = $db->select("SELECT app_name FROM user_apps WHERE user_id = ?", [$user['id']]);
        $rawApps = array_column($userApps, 'app_name');
        foreach ($rawApps as $rawApp) {
            if ($rawApp === 'staff_dashboard') {
                $assignedApps = array_merge($assignedApps, [
                    'academic', 'academic_summary', 'hr', 'access_control', 'finance', 'medical', 
                    'transport', 'file_manager', 'games', 'config'
                ]);
            } elseif ($rawApp === 'driver_app') {
                $assignedApps[] = 'transport';
            } elseif ($rawApp === 'teacher_app') {
                $assignedApps = array_merge($assignedApps, [
                    'academic', 'academic_summary', 'medical', 'games'
                ]);
            } elseif ($rawApp === 'parents_dashboard') {
                // Parents dashboard doesn't need admin launcher items
            } else {
                $assignedApps[] = $rawApp;
            }
        }
        $assignedApps = array_unique($assignedApps);
    } else {
        if (has_role('super_admin') || has_role('school_admin') || has_role('manager')) {
            $assignedApps = [
                'academic', 'academic_summary', 'hr', 'access_control', 'finance', 'medical', 
                'transport', 'file_manager', 'games', 'config'
            ];
        }
    }
}
?>
<div class="flex h-screen overflow-hidden">

    <?php if (!$isDashboard): ?>
    <!-- Sidebar -->
    <aside class="flex-shrink-0 flex flex-col border-r border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900 transition-all duration-300"
           :class="sidebarOpen ? 'w-64' : 'w-16'">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-slate-200 dark:border-slate-800/60">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                <p class="text-sm font-bold text-white leading-tight">PSNF ERP</p>
                <p class="text-xs text-slate-500">v1.0.0</p>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="ml-auto text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <?php 
            $sidebarOpen = true; 
            ?>
            <?php if (!function_exists('navLink')) {
                function navLink(string $href, string $icon, string $label, string $current, bool $open = true, bool $disabled = false): void {
                    $active = str_starts_with($current, $href) && $href !== '/';
                    if ($href === '/dashboard' || $href === '/teacher/dashboard') {
                        $active = ($current === '/dashboard' || $current === '/teacher/dashboard');
                    }
                    
                    if ($disabled) {
                        $classes = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-350 dark:text-slate-650 cursor-not-allowed opacity-50';
                        echo "<div class=\"$classes\" title=\"$label (Coming Soon)\">";
                        echo "<span class=\"flex-shrink-0\">$icon</span>";
                        if ($open) {
                            echo "<span class=\"truncate flex-1\">$label</span>";
                            echo "<span class=\"text-[9px] px-1.5 py-0.5 rounded bg-slate-150 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-normal uppercase tracking-wide\">Soon</span>";
                        }
                        echo "</div>";
                    } else {
                        $classes = $active ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all bg-brand-500/10 text-brand-600 dark:bg-brand-600/20 dark:text-brand-400 border border-brand-500/20 dark:border-brand-500/20' : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/5 transition-all duration-200';
                        echo "<a href=\"" . url(ltrim($href, '/')) . "\" class=\"$classes\" title=\"$label\">";
                        echo "<span class=\"flex-shrink-0\">$icon</span>";
                        if ($open) echo "<span class=\"truncate\" x-show=\"sidebarOpen\">$label</span>";
                        echo "</a>";
                    }
                }
            } 

            $ic = [
                'launcher'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>',
                'dashboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'students'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'users'     => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'roles'     => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                'logs'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
                'fees'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                'transport' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
                'certificates' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'admissions' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
                'classes' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                'attendance' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'timetables' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                'exams' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'receipts' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'scholarships' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222m4 9.722v-7.5l-4-2.222"/></svg>',
                'tracking' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'settings' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            ];

            // Determine active module from path
            $module = '';
            if (str_starts_with($currentPath, '/students') || str_starts_with($currentPath, '/admissions')) {
                $module = 'academic';
            } elseif (str_starts_with($currentPath, '/classes') || str_starts_with($currentPath, '/attendance') || str_starts_with($currentPath, '/certificates') || str_starts_with($currentPath, '/timetables') || str_starts_with($currentPath, '/exams')) {
                $module = 'academic_summary';
            } elseif (str_starts_with($currentPath, '/fees') || str_starts_with($currentPath, '/receipts') || str_starts_with($currentPath, '/scholarships')) {
                $module = 'finance';
            } elseif (str_starts_with($currentPath, '/transport')) {
                $module = 'transport';
            } elseif (str_starts_with($currentPath, '/users') || str_starts_with($currentPath, '/roles') || str_starts_with($currentPath, '/settings')) {
                $module = 'administration';
            } elseif (str_starts_with($currentPath, '/medical')) {
                $module = 'medical';
            }
            ?>

            <!-- Back to Launcher -->
            <div class="mb-4 pb-3 border-b border-slate-200 dark:border-slate-800/60">
                <?php navLink(dashboard_url(), $ic['launcher'], 'Back to Apps', $currentPath, $sidebarOpen); ?>
            </div>

            <?php if ($module === 'academic'): ?>
                <div class="text-2xs font-bold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Academic</div>
                <?php if (in_array('academic', $assignedApps)): ?>
                    <?php navLink('/students', $ic['students'], 'Students', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>

            <?php elseif ($module === 'academic_summary'): ?>
                <div class="text-2xs font-bold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Academic Summary</div>
                <?php if (in_array('academic_summary', $assignedApps) || in_array('academic', $assignedApps)): ?>
                    <?php navLink('/classes', $ic['classes'], 'Classes & Sections', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/attendance', $ic['attendance'], 'Attendance', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/certificates', $ic['certificates'], 'Certificates', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/timetables', $ic['timetables'], 'Timetables', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/exams', $ic['exams'], 'Exams & Grades', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>

            <?php elseif ($module === 'finance'): ?>
                <?php if (in_array('finance', $assignedApps)): ?>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Finance</div>
                    <?php navLink('/fees', $ic['fees'], 'Fees & Invoices', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/receipts', $ic['receipts'], 'Receipts', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/scholarships', $ic['scholarships'], 'Scholarships', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>

            <?php elseif ($module === 'transport'): ?>
                <?php if (in_array('transport', $assignedApps)): ?>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Transport</div>
                    <?php navLink('/transport', $ic['transport'], 'Routes & Vehicles', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/transport/tracking', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'Live Tracking', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>

            <?php elseif ($module === 'administration'): ?>
                <div class="text-2xs font-bold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">H.R Directory</div>
                <?php if (in_array('hr', $assignedApps)): ?>
                    <?php navLink('/users', $ic['users'], 'Users Management', $currentPath, $sidebarOpen); ?>
                    <?php navLink('/users/attendance', $ic['attendance'], 'Teacher Attendance', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>
                <?php if (in_array('access_control', $assignedApps)): ?>
                    <?php navLink('/roles', $ic['roles'], 'Roles & Permissions', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>

                <?php if (in_array('config', $assignedApps)): ?>
                    <?php navLink('/settings', $ic['settings'], 'Settings', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>
            <?php elseif ($module === 'medical'): ?>
                <?php if (in_array('medical', $assignedApps)): ?>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Medical</div>
                    <?php navLink('/medical', '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>', 'Care Profiles', $currentPath, $sidebarOpen); ?>
                <?php endif; ?>
            <?php endif; ?>

        </nav>

        <!-- User Footer -->
        <div class="border-t border-slate-200 dark:border-slate-800/60 p-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 dark:text-white truncate"><?= e($user['name'] ?? '') ?></p>
                    <p class="text-xs text-slate-500 truncate"><?= e(implode(', ', array_slice($user['role_names'] ?? [], 0, 2))) ?></p>
                </div>
                <a x-show="sidebarOpen" href="<?= url('logout') ?>" class="text-slate-500 hover:text-red-400 transition-colors" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top Bar -->
        <?php if ($isDashboard): ?>
        <header class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-white/80 dark:bg-surface-900/50 backdrop-blur">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-bold text-slate-800 dark:text-white tracking-wide">PSNF ERP Portal</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Theme Toggler -->
                <button @click="toggleTheme()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" title="Toggle Theme">
                    <!-- Sun (light mode: click to switch to dark) -->
                    <svg x-show="!isDark" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"/></svg>
                    <!-- Moon (dark mode: click to switch to light) -->
                    <svg x-show="isDark" class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <span class="text-xs text-slate-500"><?= date('D, d M Y') ?></span>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="text-xs text-slate-600 dark:text-slate-300 font-medium"><?= e($user['name'] ?? '') ?></span>
                    <a href="<?= url('logout') ?>" class="text-slate-500 hover:text-red-400 transition-colors ml-1" title="Logout">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>
        </header>
        <?php else: ?>
        <header class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-white/80 dark:bg-surface-900/50 backdrop-blur">
            <div>
                <h1 class="text-lg font-semibold text-slate-800 dark:text-white"><?= $pageTitle ?? 'Dashboard' ?></h1>
                <?php if (!empty($breadcrumbs)): ?>
                <nav class="flex items-center gap-1 mt-0.5">
                    <?php foreach ($breadcrumbs as $i => $bc): ?>
                    <?php if ($i > 0): ?><span class="text-slate-400 dark:text-slate-600 text-xs">›</span><?php endif; ?>
                    <?php if ($i < count($breadcrumbs) - 1): ?>
                    <a href="<?= url(ltrim($bc['url'] ?? '#', '/')) ?>" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300"><?= e($bc['label']) ?></a>
                    <?php else: ?>
                    <span class="text-xs text-slate-400 dark:text-slate-500"><?= e($bc['label']) ?></span>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3">
                <!-- HTMX Loading Spinner -->
                <div class="htmx-indicator">
                    <div class="w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <!-- Theme Toggler -->
                <button @click="toggleTheme()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" title="Toggle Theme">
                    <!-- Sun (light mode: click to switch to dark) -->
                    <svg x-show="!isDark" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"/></svg>
                    <!-- Moon (dark mode: click to switch to light) -->
                    <svg x-show="isDark" class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                <span class="text-xs text-slate-500"><?= date('D, d M Y') ?></span>
            </div>
        </header>
        <?php endif; ?>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6" id="main-content">
            <?= $content ?>
        </main>
    </div>
</div>

<script>
// CSRF for HTMX
document.body.addEventListener('htmx:configRequest', function(e) {
    e.detail.headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf-token"]')?.content;
});

// Flash auto-dismiss
setTimeout(() => {
    const flash = document.getElementById('flash-container');
    if (flash) flash.style.opacity = '0';
}, 5000);
</script>

</body>
</html>
