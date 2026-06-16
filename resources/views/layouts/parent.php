<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PSNF Parent Portal' ?></title>
    <meta name="description" content="Pearl Special Needs Foundation — Parent Portal">
    <meta name="csrf-token" content="<?= \Core\View::csrfToken() ?>">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="bg-surface-950 font-sans antialiased text-slate-200 min-h-screen" x-data="{ sidebarOpen: true, mobileNav: false }">

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
    <aside class="flex-shrink-0 flex flex-col border-r border-slate-800/60"
           :class="sidebarOpen ? 'w-66' : 'w-16'" style="background: linear-gradient(180deg, #0f172a 0%, #080d1a 100%);">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-slate-800/60">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                <p class="text-sm font-bold text-white leading-tight">PSNF Portal</p>
                <p class="text-xs text-slate-500">Parent Access</p>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="ml-auto text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Multi-Child Context Selector -->
        <?php if (!empty($all_students) && !empty($active_student)): ?>
        <div class="px-4 py-4 border-b border-slate-800/60" x-show="sidebarOpen" x-data="{ childDropdown: false }">
            <label class="block text-[10px] uppercase tracking-wider font-semibold text-slate-500 mb-1.5">Selected Child</label>
            <div class="relative">
                <button @click="childDropdown = !childDropdown"
                        class="w-full flex items-center justify-between gap-2.5 p-2 rounded-xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition-all text-left">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                            <?= strtoupper(substr($active_student['first_name'], 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-white leading-tight truncate"><?= e($active_student['first_name'] . ' ' . $active_student['last_name']) ?></p>
                            <p class="text-[10px] text-slate-500 truncate"><?= e($active_student['class']) ?></p>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown -->
                <div x-show="childDropdown" @click.outside="childDropdown = false" x-cloak
                     class="absolute top-full left-0 w-full mt-1.5 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl z-50 p-1.5 space-y-1">
                    <?php
                    $currentPath = \Core\Application::$app->request->getPath();
                    foreach ($all_students as $std) {
                        $isActive = (int)$std['id'] === (int)$active_student['id'];
                        // Construct dynamic switch URL to preserve parent's current page tab
                        if (str_contains($currentPath, '/parent/students/')) {
                            $switchUrl = str_replace('/parent/students/' . $active_student['id'], '/parent/students/' . $std['id'], $currentPath);
                        } else {
                            $switchUrl = url("parent/dashboard");
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

            function parentNavLink(string $href, string $icon, string $label, string $current, bool $open = true): void {
                $active = str_starts_with($current, $href) && $href !== '/';
                if ($href === '/parent/dashboard') $active = ($current === '/parent/dashboard' || $current === '/parent');
                $classes = $active
                    ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all bg-indigo-600/20 text-indigo-400 border border-indigo-500/20'
                    : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200';
                echo "<a href=\"" . url(ltrim($href, '/')) . "\" class=\"$classes\" title=\"$label\">";
                echo "<span class=\"flex-shrink-0\">$icon</span>";
                if ($open) echo "<span class=\"truncate\">$label</span>";
                echo "</a>";
            }

            $ic = [
                'dashboard' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'attendance' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                'timetable' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'homework'  => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
                'exams'     => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'medical'   => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
                'transport' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M18 16h3a1 1 0 001-1v-5a1 1 0 00-1-1h-3V6a1 1 0 00-1-1h-4"/></svg>',
                'fees'      => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                'announcements' => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                'messages'  => '<svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
            ];
            ?>

            <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Main Portal</div>
            <?php parentNavLink('/parent/dashboard', $ic['dashboard'], 'Overview Dashboard', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/announcements', $ic['announcements'], 'Announcements', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/communication', $ic['messages'], 'Staff Messages', $currentPath, $sidebarOpen); ?>

            <?php if (!empty($active_student)): ?>
            <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-3 mt-5 mb-2" x-show="sidebarOpen">Child Record</div>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/attendance', $ic['attendance'], 'Attendance Log', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/timetable', $ic['timetable'], 'Weekly Timetable', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/homework', $ic['homework'], 'Active Homework', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/exams', $ic['exams'], 'Exams & Progress', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/medical', $ic['medical'], 'Medical Warnings', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/transport', $ic['transport'], 'Bus Transport', $currentPath, $sidebarOpen); ?>
            <?php parentNavLink('/parent/students/' . $active_student['id'] . '/fees', $ic['fees'], 'Fee Invoices', $currentPath, $sidebarOpen); ?>
            <?php endif; ?>
        </nav>

        <!-- Parent Footer -->
        <?php $pUser = auth(); ?>
        <div class="border-t border-slate-800/60 p-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($pUser['name'] ?? 'P', 0, 1)) ?>
                </div>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= e($pUser['name'] ?? '') ?></p>
                    <p class="text-xs text-slate-500 truncate">Parent Account</p>
                </div>
                <a x-show="sidebarOpen" href="<?= url('logout') ?>" class="text-slate-500 hover:text-red-400 transition-colors" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main View Window -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Header -->
        <header class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-800/60 bg-surface-900/40 backdrop-blur">
            <div>
                <h1 class="text-lg font-bold text-white"><?= $pageTitle ?? 'Parent Portal' ?></h1>
                <?php if (!empty($active_student)): ?>
                <p class="text-xs text-slate-400">Viewing profile context of: <strong class="text-slate-200"><?= e($active_student['first_name'] . ' ' . $active_student['last_name']) ?></strong></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3.5">
                <!-- HTMX loading spinner -->
                <div class="htmx-indicator">
                    <div class="w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <span class="text-xs text-slate-500 font-medium"><?= date('D, d M Y') ?></span>
            </div>
        </header>

        <!-- View Body -->
        <main class="flex-1 overflow-y-auto p-6" id="main-content">
            <?= $content ?>
        </main>
    </div>
</div>

<script>
    // Set CSRF on all HTMX actions
    document.body.addEventListener('htmx:configRequest', function(e) {
        e.detail.headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf-token"]')?.content;
    });

    // Auto fadeout flash alerts
    setTimeout(() => {
        const flash = document.getElementById('flash-container');
        if (flash) flash.style.opacity = '0';
    }, 5000);
</script>
</body>
</html>
