<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            window.isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            window.isDark = false;
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PSNF Parent Portal' ?></title>
    <meta name="description" content="Pearl Special Needs Foundation — Parent Portal">
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

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        /* Glass card */
        .glass {
            backdrop-filter: blur(16px);
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #818cf8, #a78bfa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* HTMX loading indicator */
        .htmx-indicator { opacity: 0; transition: opacity 200ms; }
        .htmx-request .htmx-indicator { opacity: 1; }
        .htmx-request.htmx-indicator { opacity: 1; }
        /* Light Mode CSS Overrides */
        html:not(.dark) body {
            background-color: #f8fafc !important;
            color: #1e293b !important;
        }
        
        /* Background Overrides */
        html:not(.dark) [class*="bg-slate-950"] { background-color: #f8fafc !important; }
        html:not(.dark) [class*="bg-slate-900"] { background-color: #ffffff !important; }
        html:not(.dark) [class*="bg-slate-850"] { background-color: #ffffff !important; }
        html:not(.dark) [class*="bg-slate-800"] { background-color: #f1f5f9 !important; }
        html:not(.dark) [class*="bg-slate-700"] { background-color: #cbd5e1 !important; }
        html:not(.dark) [class*="bg-white/5"] { background-color: rgba(0, 0, 0, 0.03) !important; }
        html:not(.dark) [class*="bg-white/10"] { background-color: rgba(0, 0, 0, 0.06) !important; }
        html:not(.dark) [class*="bg-white/20"] { background-color: rgba(0, 0, 0, 0.1) !important; }

        /* Text Overrides */
        html:not(.dark) [class*="text-slate-100"] { color: #1e293b !important; }
        html:not(.dark) [class*="text-slate-200"] { color: #334155 !important; }
        html:not(.dark) [class*="text-slate-300"] { color: #475569 !important; }
        html:not(.dark) [class*="text-slate-400"] { color: #64748b !important; }
        html:not(.dark) [class*="text-slate-500"] { color: #64748b !important; }
        html:not(.dark) [class*="text-white"]:not(button):not([class*="bg-brand"]):not([class*="bg-indigo"]):not([class*="bg-purple"]):not([class*="bg-emerald"]):not([class*="bg-red"]):not([class*="bg-amber"]):not([class*="badge-"]):not(.rounded-2xl) {
            color: #0f172a !important;
        }

        /* Hover Text Overrides */
        html:not(.dark) [class*="hover:text-white"]:hover { color: #0f172a !important; }
        html:not(.dark) [class*="hover:text-slate-100"]:hover { color: #1e293b !important; }
        html:not(.dark) [class*="hover:text-slate-200"]:hover { color: #334155 !important; }
        html:not(.dark) [class*="hover:text-slate-300"]:hover { color: #475569 !important; }

        /* Brand / Accent colors */
        html:not(.dark) [class*="text-brand-400"] { color: #4f46e5 !important; }
        html:not(.dark) [class*="text-brand-300"] { color: #4338ca !important; }
        html:not(.dark) [class*="hover:text-brand-300"]:hover { color: #4338ca !important; }

        /* Borders Overrides */
        html:not(.dark) [class*="border-slate-"] { border-color: #e2e8f0 !important; }
        html:not(.dark) [class*="border-white/5"] { border-color: rgba(0, 0, 0, 0.06) !important; }
        html:not(.dark) [class*="border-white/10"] { border-color: rgba(0, 0, 0, 0.08) !important; }
        html:not(.dark) [class*="border-white/20"] { border-color: rgba(0, 0, 0, 0.12) !important; }

        /* Form Inputs */
        html:not(.dark) input, 
        html:not(.dark) select, 
        html:not(.dark) textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        html:not(.dark) input::placeholder,
        html:not(.dark) textarea::placeholder {
            color: #94a3b8 !important;
        }
        html:not(.dark) input[type="checkbox"] {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #4f46e5 !important;
        }

        /* Glass Panel */
        html:not(.dark) .glass {
            background: rgba(255, 255, 255, 0.8) !important;
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
        }

        /* Sidebar Navigation */
        html:not(.dark) .sidebar-link {
            color: #475569 !important;
        }
        html:not(.dark) .sidebar-link:hover {
            color: #0f172a !important;
            background-color: rgba(0, 0, 0, 0.04) !important;
        }
        html:not(.dark) .sidebar-link.active {
            background-color: rgba(99, 102, 241, 0.1) !important;
            color: #4f46e5 !important;
            border-color: rgba(99, 102, 241, 0.2) !important;
        }

        /* Badges */
        html:not(.dark) .badge-applied { background-color: #f1f5f9 !important; color: #475569 !important; border-color: #cbd5e1 !important; }
        html:not(.dark) .badge-review { background-color: #fef9c3 !important; color: #854d0e !important; border-color: #fef08a !important; }
        html:not(.dark) .badge-assessment { background-color: #dbeafe !important; color: #1e40af !important; border-color: #bfdbfe !important; }
        html:not(.dark) .badge-approved { background-color: #d1fae5 !important; color: #065f46 !important; border-color: #a7f3d0 !important; }
        html:not(.dark) .badge-enrolled { background-color: #e0e9ff !important; color: #3730a3 !important; border-color: #c7d7fe !important; }
        html:not(.dark) .badge-withdrawn { background-color: #fee2e2 !important; color: #991b1b !important; border-color: #fca5a5 !important; }

        /* Tables */
        html:not(.dark) table {
            border-color: #e2e8f0 !important;
        }
        html:not(.dark) thead tr {
            background-color: #f8fafc !important;
        }
        html:not(.dark) th {
            color: #475569 !important;
        }
        html:not(.dark) td {
            color: #334155 !important;
            border-color: #e2e8f0 !important;
        }
        html:not(.dark) tr:hover {
            background-color: rgba(241, 245, 249, 0.5) !important;
        }

        html:not(.dark) button[type="submit"]:not(.bg-red-600):not(.bg-emerald-600) {
            color: #ffffff !important;
        }

        /* Color panel overrides for light mode */
        html:not(.dark) [class*="bg-blue-"]:not(button) { background-color: #eff6ff !important; }
        html:not(.dark) [class*="border-blue-"] { border-color: #bfdbfe !important; }
        html:not(.dark) [class*="text-blue-"] { color: #1d4ed8 !important; }

        html:not(.dark) [class*="bg-red-"]:not(button) { background-color: #fef2f2 !important; }
        html:not(.dark) [class*="border-red-"] { border-color: #fca5a5 !important; }
        html:not(.dark) [class*="text-red-"] { color: #b91c1c !important; }

        html:not(.dark) [class*="bg-emerald-"]:not(button) { background-color: #ecfdf5 !important; }
        html:not(.dark) [class*="border-emerald-"] { border-color: #a7f3d0 !important; }
        html:not(.dark) [class*="text-emerald-"] { color: #047857 !important; }

        html:not(.dark) [class*="bg-yellow-"]:not(button) { background-color: #fefce8 !important; }
        html:not(.dark) [class*="border-yellow-"] { border-color: #fef08a !important; }
        html:not(.dark) [class*="text-yellow-"] { color: #a16207 !important; }

        html:not(.dark) [class*="bg-amber-"]:not(button) { background-color: #fffbeb !important; }
        html:not(.dark) [class*="border-amber-"] { border-color: #fde68a !important; }
        html:not(.dark) [class*="text-amber-"] { color: #b45309 !important; }

        /* Dropdown/Menu Items inside slate containers */
        html:not(.dark) [class*="bg-slate-"] button,
        html:not(.dark) [class*="bg-slate-"] a {
            color: #475569 !important;
        }
        html:not(.dark) [class*="bg-slate-"] button:hover,
        html:not(.dark) [class*="bg-slate-"] a:hover {
            color: #0f172a !important;
            background-color: #f1f5f9 !important;
        }

        /* Explicit Dropdown Menu button styles inside absolute containers */
        html:not(.dark) [class*="absolute"] button,
        html:not(.dark) [class*="absolute"] a {
            color: #1e293b !important;
        }
        html:not(.dark) [class*="absolute"] button:hover,
        html:not(.dark) [class*="absolute"] a:hover {
            color: #ffffff !important;
            background-color: #4f46e5 !important;
        }
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

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="hidden md:flex flex-shrink-0 flex-col border-r border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900 transition-all duration-300"
           :class="sidebarOpen ? 'w-64' : 'w-16'">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-slate-200 dark:border-slate-800/60">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                <p class="text-sm font-bold text-slate-800 dark:text-white leading-tight">PSNF Portal</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Parent Access</p>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="ml-auto text-slate-500 hover:text-slate-850 dark:hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>



        <!-- Multi-Child Context Selector -->
        <?php if (!empty($all_students) && !empty($active_student)): ?>
        <div class="px-4 py-4 border-b border-slate-200 dark:border-slate-800/60" x-show="sidebarOpen" x-data="{ childDropdown: false }">
            <label class="block text-[10px] uppercase tracking-wider font-semibold text-slate-500 mb-1.5">Selected Child</label>
            <div class="relative">
                <button @click="childDropdown = !childDropdown"
                        class="w-full flex items-center justify-between gap-2.5 p-2 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all text-left">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                            <?= strtoupper(substr($active_student['first_name'], 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-slate-800 dark:text-white leading-tight truncate"><?= e($active_student['first_name'] . ' ' . $active_student['last_name']) ?></p>
                            <p class="text-[10px] text-slate-500 truncate"><?= e($active_student['class']) ?></p>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown -->
                <div x-show="childDropdown" @click.outside="childDropdown = false" x-cloak
                     class="absolute top-full left-0 w-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl z-50 p-1.5 space-y-1">
                    <?php
                    $currentPath = \Core\Application::$app->request->getPath();
                    foreach ($all_students as $std) {
                        $isActive = (int)$std['id'] === (int)$active_student['id'];
                        // Construct dynamic switch URL to preserve parent's current page tab
                        if (str_contains($currentPath, '/parent/students/')) {
                            $switchUrl = str_replace('/parent/students/' . $active_student['id'], '/parent/students/' . $std['id'], $currentPath);
                        } else {
                            $switchUrl = url("parent/dashboard?child_id=" . $std['id']);
                        }

                        $classes = $isActive
                            ? 'w-full flex items-center gap-2 p-2 rounded-lg bg-indigo-600/10 text-indigo-400 text-xs font-semibold'
                            : 'w-full flex items-center gap-2 p-2 rounded-lg hover:bg-slate-800 text-slate-300 text-xs transition-colors';

                        echo "<a href=\"" . url(ltrim($switchUrl, '/')) . "\" class=\"$classes\">";
                        echo "<div class=\"w-6 h-6 rounded-md bg-slate-800 flex items-center justify-center text-[10px] font-bold\">" . strtoupper(substr($std['first_name'], 0, 1)) . "</div>";
                        echo "<span class=\"truncate\">" . e($std['first_name'] . ' ' . $std['last_name']) . "</span>";
                        echo "</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Nav Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <?php
            $currentPath = \Core\Application::$app->request->getPath();
            $sidebarOpen = true;

            if (!function_exists('parentNavLink')) {
                function parentNavLink(string $href, string $icon, string $label, string $current, bool $open = true): void {
                    $active = str_starts_with($current, $href) && $href !== '/';
                    if ($href === '/parent/dashboard') $active = ($current === '/parent/dashboard' || $current === '/parent');
                    $classes = $active
                        ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all bg-brand-500/10 text-brand-600 dark:bg-brand-600/20 dark:text-brand-400 border border-brand-500/20 dark:border-brand-500/20'
                        : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/5 transition-all duration-200';
                    echo "<a href=\"" . url(ltrim($href, '/')) . "\" class=\"$classes\" title=\"$label\">";
                    echo "<span class=\"flex-shrink-0\">$icon</span>";
                    if ($open) echo "<span class=\"truncate\" x-show=\"sidebarOpen\">$label</span>";
                    echo "</a>";
                }
            }

            $ic = [
                'dashboard' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'attendance' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                'timetable' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'homework'  => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                'exams'     => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'certificates' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'report_card'  => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'medical'   => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
                'transport' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg>',
                'fees'      => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                'announcements' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                'messages'  => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
            ];
            ?>

            <?php if (has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('teacher')): ?>
            <div class="mb-4 pb-3 border-b border-slate-200 dark:border-slate-800/60">
                <a href="<?= has_role('teacher') && !has_role('super_admin') && !has_role('school_admin') && !has_role('manager') ? url('teacher/dashboard') : url('dashboard') ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/5 transition-all duration-200" 
                   title="Back to Admin Panel">
                    <span class="flex-shrink-0 text-amber-500 dark:text-amber-400">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7"/></svg>
                    </span>
                    <span class="truncate text-slate-800 dark:text-slate-200 font-semibold" x-show="sidebarOpen">Back to Admin Panel</span>
                </a>
            </div>
            <?php endif; ?>

            <div class="text-xs font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Main Portal</div>
            <?php parentNavLink('/parent/dashboard', $ic['dashboard'], 'Overview Dashboard', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/announcements', $ic['announcements'], 'Announcements', $currentPath, $sidebarOpen); ?>
            <?php if (!empty($active_student)): ?>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-wider px-3 mt-5 mb-2" x-show="sidebarOpen">Child Record</div>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/attendance', $ic['attendance'], 'Attendance Log', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/timetable', $ic['timetable'], 'Weekly Timetable', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/exams', $ic['exams'], 'Exams & Progress', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/certificates', $ic['certificates'], 'Certificates', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/report-card', $ic['report_card'], 'Report Card', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/transport', $ic['transport'], 'Bus Transport', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/fees', $ic['fees'], 'Fee Invoices', $currentPath, $sidebarOpen); ?>
            <a href="<?= url('games') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/5 transition-all duration-200" title="Interactive Games">
                <span class="flex-shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="truncate" x-show="sidebarOpen">Interactive Games</span>
            </a>
            <?php endif; ?>
        </nav>



        <!-- Parent Footer -->
        <?php $pUser = auth(); ?>
        <div class="border-t border-slate-200 dark:border-slate-800/60 p-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($pUser['name'] ?? 'P', 0, 1)) ?>
                </div>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate"><?= e($pUser['name'] ?? '') ?></p>
                    <p class="text-xs text-slate-500 truncate">Parent Account</p>
                </div>
                <a x-show="sidebarOpen" href="<?= url('parent/logout') ?>" class="text-slate-500 hover:text-red-400 transition-colors" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main View Window -->
    <div class="flex-1 flex flex-col overflow-hidden w-full relative">

        <!-- Header -->
        <header class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-white/80 dark:bg-surface-900/40 backdrop-blur">
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white"><?= $pageTitle ?? 'Parent Portal' ?></h1>
                <?php if (!empty($active_student)): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400">Viewing profile context of: <strong class="text-slate-700 dark:text-slate-200 font-semibold"><?= e($active_student['first_name'] . ' ' . $active_student['last_name']) ?></strong></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3.5">
                <!-- HTMX loading spinner -->
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

                <span class="text-xs text-slate-500 flex items-center gap-1.5 font-medium">
                    <span><?= date('D, d M Y') ?></span>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <span class="live-header-clock font-mono">--:--:--</span>
                </span>
            </div>
        </header>

        <!-- View Body -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6" id="main-content">
            <?= $content ?>
        </main>
    </div>
</div>

<script>
    // Set CSRF on all HTMX actions
    document.body.addEventListener('htmx:configRequest', function(e) {
        e.detail.headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf-token"]')?.content;
    });

    // Re-initialize Alpine.js on HTMX swaps
    document.body.addEventListener('htmx:afterSwap', function(e) {
        if (typeof Alpine !== 'undefined') {
            Alpine.process(e.detail.target);
        }
    });

    // Auto fadeout flash alerts
    setTimeout(() => {
        const flash = document.getElementById('flash-container');
        if (flash) flash.style.opacity = '0';
    }, 5000);

    // Live Clock script
    function startHeaderClock() {
        const clockElements = document.querySelectorAll('.live-header-clock');
        if (clockElements.length === 0) return;
        function update() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            clockElements.forEach(el => el.textContent = timeStr);
        }
        update();
        setInterval(update, 1000);
    }
    document.addEventListener('DOMContentLoaded', startHeaderClock);
    document.body.addEventListener('htmx:afterSwap', startHeaderClock);
</script>
</body>
</html>
