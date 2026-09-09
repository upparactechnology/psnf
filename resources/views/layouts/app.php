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
    <script>
        window.APP_BASE_URL = '<?= rtrim(get_dynamic_base_url(), '/') ?>';
        window.appUrl = function(path) {
            return window.APP_BASE_URL + '/' + path.replace(/^\/+/, '');
        };
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PSNF Management System' ?></title>
    <meta name="description" content="Pearl Special Needs Foundation — Management System">
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
    <style>
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
        html:not(.dark) [class*="text-white"]:not(button):not([class*="bg-brand"]):not([class*="bg-indigo"]):not([class*="bg-purple"]):not([class*="bg-emerald"]):not([class*="bg-red"]):not([class*="bg-amber"]):not([class*="badge-"]):not(.rounded-2xl):not([class*="inline-flex"]) {
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

        /* Keep button text white on colored backgrounds */
        html:not(.dark) [class*="bg-indigo-600"][class*="text-white"],
        html:not(.dark) [class*="bg-indigo-500"][class*="text-white"],
        html:not(.dark) [class*="bg-brand-600"][class*="text-white"],
        html:not(.dark) [class*="bg-brand-500"][class*="text-white"] {
            color: #ffffff !important;
        }

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

        /* Sidebar Navigation & High Specificity Active Item Styling */
        aside nav {
            scrollbar-width: none !important; /* Firefox */
            -ms-overflow-style: none !important; /* IE 10+ */
        }
        aside nav::-webkit-scrollbar {
            display: none !important; /* Chrome, Safari, Opera */
            width: 0 !important;
            height: 0 !important;
        }

        .sidebar-active-item {
            background-color: rgba(79, 70, 229, 0.15) !important;
            color: #4f46e5 !important;
            border: 1px solid rgba(79, 70, 229, 0.3) !important;
            font-weight: 700 !important;
        }
        .dark .sidebar-active-item {
            background-color: rgba(99, 102, 241, 0.25) !important;
            color: #818cf8 !important;
            border: 1px solid rgba(99, 102, 241, 0.4) !important;
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

        html:not(.dark) button[type="submit"]:not(.bg-red-600):not(.bg-emerald-600):not(.hover\:underline):not([class*="text-"]) {
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
<body class="bg-slate-50 text-slate-800 dark:bg-surface-950 dark:text-slate-200 font-sans antialiased min-h-screen" 
      x-data="{ 
          sidebarOpen: true, 
          mobileNav: false, 
          commandPalette: false,
          searchQuery: '',
          isDark: window.isDark, 
          toggleTheme() { 
              this.isDark = !this.isDark; 
              if (this.isDark) { 
                  document.documentElement.classList.add('dark'); 
                  localStorage.setItem('theme', 'dark'); 
              } else { 
                  document.documentElement.classList.remove('dark'); 
                  localStorage.setItem('theme', 'light'); 
              } 
          } 
      }"
      @keydown.window.cmd.k.prevent="commandPalette = true"
      @keydown.window.ctrl.k.prevent="commandPalette = true">

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
$isDashboard = ($currentPath === '/dashboard' || $currentPath === '/' || $currentPath === '' || $currentPath === '/games');
$isKiosk = ($currentPath === '/attendance/face-kiosk' && \Core\Session::get('kiosk_guest'));
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
                    'transport', 'file_manager', 'games', 'config', 'report_cards'
                ]);
            } elseif ($rawApp === 'driver_app') {
                $assignedApps[] = 'transport';
            } elseif ($rawApp === 'teacher_app') {
                $assignedApps = array_merge($assignedApps, [
                    'academic', 'academic_summary', 'medical', 'games', 'report_cards'
                ]);
            } elseif ($rawApp === 'parents_dashboard') {
                // Parents dashboard doesn't need admin launcher items
            } else {
                $assignedApps[] = $rawApp;
            }
        }
        $assignedApps = array_unique($assignedApps);
    } else {
        if (has_role('super_admin') || has_role('school_admin') || has_role('manager') || has_role('teacher')) {
            $assignedApps = [
                'academic', 'academic_summary', 'hr', 'access_control', 'finance', 'medical', 
                'transport', 'file_manager', 'games', 'config', 'report_cards'
            ];
        }
    }
}
?>
<div class="flex h-screen overflow-hidden">

    <!-- Mobile Backdrop -->
    <div x-show="mobileNav" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[90] md:hidden" @click="mobileNav = false" x-transition.opacity x-cloak></div>

    <?php if (!$isDashboard && !$isKiosk): ?>
    <!-- Sidebar -->
    <aside class="flex-shrink-0 flex flex-col border-r border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900 transition-all duration-300 fixed md:relative z-[100] h-full"
           :class="[
               sidebarOpen ? 'w-64' : 'w-16',
               mobileNav ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
           ]">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-slate-200 dark:border-slate-800/60">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden bg-white">
                <img src="<?= url('game/images/logo.png') ?>" class="w-8 h-8 object-contain" alt="PSNF Logo">
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                <p class="text-xs font-bold text-slate-800 dark:text-white leading-tight">PSNF Management System</p>
                <p class="text-[10px] text-slate-500">v1.0.0</p>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="hidden md:block ml-auto text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button @click="mobileNav = false" class="md:hidden ml-auto text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <?php 
            $sidebarOpen = true; 

            if (!function_exists('can')) {
                function can(string $slug): bool {
                    $user = \Core\Session::get('user');
                    if (!$user) return false;
                    if (in_array('super_admin', $user['roles'] ?? [])) return true;
                    $perms = $user['permissions'] ?? [];
                    return in_array($slug, $perms);
                }
            }
            ?>
            <?php if (!function_exists('navLink')) {
                function navLink(string $href, string $icon, string $label, string $current, bool $open = true, bool $disabled = false): void {
                    $cleanHref = strtok($href, '?');
                    $cleanCurrent = strtok($current, '?');
                    
                    if ($cleanHref === '/academics' || $cleanHref === '/academics/') {
                        $active = ($cleanCurrent === '/academics' || $cleanCurrent === '/academics/' || $cleanCurrent === '/academics/overview');
                    } elseif ($cleanHref === '/dashboard') {
                        $active = ($cleanCurrent === '/dashboard');
                    } else {
                        $active = str_starts_with($cleanCurrent, $cleanHref) && $cleanHref !== '/';
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
                        $classes = $active ? 'sidebar-active-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-bold transition-all bg-indigo-600/15 text-indigo-600 dark:bg-indigo-600/30 dark:text-indigo-400 border border-indigo-500/30 dark:border-indigo-500/40 shadow-sm' : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/5 transition-all duration-200';
                        $targetUrl = url(ltrim($href, '/'));
                        echo "<a href=\"$targetUrl\" class=\"$classes\" title=\"$label\">";
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

            // Determine active workspace module from path
            $module = '';
            if ($currentPath === '/dashboard' || $currentPath === '/games') {
                $module = 'launcher';
            } elseif (str_starts_with($currentPath, '/reports')) {
                $module = 'reports';
            } elseif (str_starts_with($currentPath, '/settings')) {
                $module = 'settings';
            } elseif (str_starts_with($currentPath, '/academic') || str_starts_with($currentPath, '/students') || str_starts_with($currentPath, '/admissions') || str_starts_with($currentPath, '/classes') || str_starts_with($currentPath, '/timetables') || str_starts_with($currentPath, '/exams') || str_starts_with($currentPath, '/report-cards') || str_contains($currentPath, '/report-card')) {
                $module = 'academic';
            } elseif (str_starts_with($currentPath, '/fees') || str_starts_with($currentPath, '/receipts') || str_starts_with($currentPath, '/scholarships') || str_starts_with($currentPath, '/certificates')) {
                $module = 'finance';
            } elseif (str_starts_with($currentPath, '/transport')) {
                $module = 'transport';
            } elseif (str_starts_with($currentPath, '/payroll')) {
                $module = 'payroll';
            } elseif (str_starts_with($currentPath, '/users') || str_starts_with($currentPath, '/staff') || str_starts_with($currentPath, '/roles') || str_starts_with($currentPath, '/attendance')) {
                $module = 'staff';
            } elseif (str_starts_with($currentPath, '/documents')) {
                $module = 'documents';
            } elseif (str_starts_with($currentPath, '/medical')) {
                $module = 'medical';
            }
            ?>

            <!-- Back to Launcher -->
            <div class="mb-4 pb-3 border-b border-slate-200 dark:border-slate-800/60">
                <?php navLink(dashboard_url(), $ic['launcher'], 'Back to Apps', $currentPath, $sidebarOpen); ?>
            </div>

            <!-- DYNAMIC CLIENT-SIDE SIDEBAR MODULE SECTIONS (ZERO PAGE RELOAD) -->
            <div x-data="{
                currentModule() {
                    const basePath = '<?= rtrim(get_dynamic_base_url(), '/') ?>'.replace(/^https?:\/\/[^\/]+/, '') || '';
                    const path = (window.location.pathname.replace(basePath, '') || '/').replace(/\/$/, '') || '/';
                    if (path === '/dashboard' || path === '/games' || path === '/') return 'launcher';
                    if (path.startsWith('/settings')) return 'settings';
                    if (path.startsWith('/reports')) return 'reports';
                    if (path.startsWith('/academic') || path.startsWith('/students') || path.startsWith('/admissions') || path.startsWith('/classes') || path.startsWith('/timetables') || path.startsWith('/exams') || path.includes('/report-card')) return 'academic';
                    if (path.startsWith('/transport')) return 'transport';
                    if (path.startsWith('/fees') || path.startsWith('/receipts') || path.startsWith('/scholarships')) return 'finance';
                    if (path.startsWith('/certificates') || path.startsWith('/certificate_generator')) return 'documents';
                    if (path.startsWith('/payroll')) return 'payroll';
                    if (path.startsWith('/medical')) return 'medical';
                    if (path.startsWith('/documents') || path.startsWith('/file_manager') || path.startsWith('/file-manager')) return 'documents';
                    if (path.startsWith('/users') || path.startsWith('/staff') || path.startsWith('/roles') || path.startsWith('/attendance')) return 'staff';
                    return 'launcher';
                }
            }" @htmx:after-swap.window="$nextTick(() => {})" class="space-y-1">

                <!-- SETTINGS WORKSPACE -->
                <?php if (can('view_general_settings') || can('edit_general_settings') || can('view_integrations') || can('edit_integrations') || can('view_system_config') || can('edit_system_config')): ?>
                <div x-show="currentModule() === 'settings'" class="space-y-1">
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">System Settings</div>
                    <?php if (can('view_general_settings')): ?><?php navLink('/settings/general', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', 'General Settings', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_integrations')): ?><?php navLink('/settings/integrations', '<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'Integrations & APIs', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_system_config')): ?><?php navLink('/settings/system', '<svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'System Configurations', $currentPath, $sidebarOpen); ?><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- REPORTS WORKSPACE -->
                <?php if (can('view_reports_overview') || can('view_financial_reports') || can('view_student_reports') || can('view_staff_reports') || can('view_whatsapp_logs')): ?>
                <div x-show="currentModule() === 'reports'" class="space-y-1">
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Reports & Analytics</div>
                    <?php if (can('view_reports_overview')): ?><?php navLink('/reports', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'Overview', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_financial_reports')): ?><?php navLink('/reports/finance', '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'Financial Reports', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_student_reports')): ?><?php navLink('/reports/students', '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'Student Reports', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_staff_reports')): ?><?php navLink('/reports/staff', '<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'Staff & HR Reports', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_whatsapp_logs')): ?><?php navLink('/reports/communication', '<svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>', 'WhatsApp Logs', $currentPath, $sidebarOpen); ?><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- ACADEMICS WORKSPACE -->
                <?php if (can('view_academic_years') || can('view_main_groups') || can('view_curriculum') || can('view_subjects') || can('view_classes') || can('view_students_list') || can('view_teachers') || can('view_acad_attendance') || can('view_timetable') || can('view_assessments') || can('view_exams') || can('view_report_cards') || can('view_promotion') || can('view_announcements') || can('view_academic_settings')): ?>
                <div x-show="currentModule() === 'academic'" class="space-y-1">
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Academic</div>
                    <?php if (can('view_academic_years')): ?><?php navLink('/academics/settings?tab=years', $ic['timetables'], 'Academic Years', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_main_groups')): ?><?php navLink('/academics/main-groups', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/></svg>', 'Main Groups', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_curriculum')): ?><?php navLink('/academics/curriculum', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'Curriculum', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_subjects')): ?><?php navLink('/academics/subjects', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>', 'Subjects', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_classes')): ?><?php navLink('/academics/classes', $ic['classes'], 'Classes', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_students_list')): ?><?php navLink('/academics/students', $ic['students'], 'Students', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_teachers')): ?><?php navLink('/academics/teachers', $ic['users'], 'Teachers', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_acad_attendance')): ?><?php navLink('/academics/attendance', $ic['attendance'], 'Attendance', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_timetable')): ?><?php navLink('/academics/timetable', $ic['timetables'], 'Timetable', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_assessments')): ?><?php navLink('/academics/assessments', $ic['exams'], 'Assessments', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_exams')): ?><?php navLink('/academics/exams', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'Exams Setup', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_report_cards')): ?><?php navLink('/academics/report-cards', $ic['certificates'], 'Report Cards', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_promotion')): ?><?php navLink('/academics/promotion', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>', 'Promotion', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_announcements')): ?><?php navLink('/academics/announcements', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>', 'Announcements', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_academic_settings')): ?><?php navLink('/academics/settings', $ic['settings'], 'Settings', $currentPath, $sidebarOpen); ?><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- FINANCE WORKSPACE -->
                <?php if (can('view_fees_dashboard') || can('view_all_invoices') || can('view_fee_structures') || can('view_batch_generator') || can('view_receipts_log') || can('view_fee_categories') || can('view_late_fee_policies')): ?>
                <div x-show="currentModule() === 'finance'" class="space-y-1" x-cloak>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Finance & Billing</div>
                    <?php if (can('view_fees_dashboard')): ?><?php navLink('/fees', $ic['fees'], 'Fees Dashboard', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_all_invoices')): ?><?php navLink('/fees/invoices', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'All Invoices', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_fee_structures')): ?><?php navLink('/fees/structures', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', 'Fee Structures', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_batch_generator')): ?><?php navLink('/fees/batch-generator', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', 'Batch Generator', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_receipts_log')): ?><?php navLink('/receipts', $ic['receipts'], 'Receipts Log', $currentPath, $sidebarOpen); ?><?php endif; ?>

                    <?php if (can('view_fee_categories') || can('view_late_fee_policies')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Settings</div>
                    <?php if (can('view_fee_categories')): ?><?php navLink('/fees/categories', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>', 'Fee Categories', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_late_fee_policies')): ?><?php navLink('/fees/late-fee-policies', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'Late Fee Policies', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- TRANSPORT MANAGEMENT WORKSPACE (PERMANENT SIDEBAR) -->
                <?php if (can('view_transport_overview') || can('view_transport_drivers') || can('view_student_transport') || can('view_live_tracking') || can('view_transport_settings') || can('view_transport_logs')): ?>
                <div x-show="currentModule() === 'transport'" class="space-y-1">
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Transport</div>
                    <?php if (can('view_transport_overview')): ?><?php navLink('/transport/overview', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'Overview', $currentPath, $sidebarOpen); ?><?php endif; ?>

                    <?php if (can('view_transport_drivers') || can('view_student_transport') || can('view_live_tracking')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Transport</div>
                    <?php if (can('view_transport_drivers')): ?><?php navLink('/transport/drivers', $ic['users'], 'Drivers', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_student_transport')): ?><?php navLink('/transport/student-assignments', $ic['students'], 'Student Assignments', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_live_tracking')): ?><?php navLink('/transport/live-tracking', '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'Live Tracking', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>

                    <?php if (can('view_transport_settings') || can('view_transport_logs')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Administration</div>
                    <?php if (can('view_transport_settings')): ?><?php navLink('/transport/settings', $ic['settings'], 'Transport Settings', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_transport_logs')): ?><?php navLink('/transport/logs', '<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'Transport Logs', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- STAFF MANAGEMENT WORKSPACE (ENTERPRISE HRMS PERMANENT SIDEBAR) -->
                <?php if (can('view_staff_overview') || can('view_staff_directory') || can('view_departments') || can('view_designations') || can('view_staff_attendance') || can('view_face_kiosk') || can('view_face_register') || can('view_leave_management') || can('view_staff_roles') || can('view_staff_user_accounts') || can('view_staff_settings')): ?>
                <div x-show="currentModule() === 'staff'" class="space-y-1">
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Staff Management</div>
                    <?php if (can('view_staff_overview')): ?><?php navLink('/staff/overview', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'Overview', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    
                    <?php if (can('view_staff_directory') || can('view_departments') || can('view_designations')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Staff</div>
                    <?php if (can('view_staff_directory')): ?><?php navLink('/staff/employees', $ic['users'], 'Staff Directory', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_departments')): ?><?php navLink('/staff/departments', $ic['classes'], 'Departments', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_designations')): ?><?php navLink('/staff/designations', $ic['roles'], 'Designations', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>

                    <?php if (can('view_staff_attendance') || can('view_face_kiosk') || can('view_face_register') || can('view_leave_management')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Operations</div>
                    <?php if (can('view_staff_attendance')): ?><?php navLink('/staff/attendance', $ic['attendance'], 'Attendance', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_staff_attendance')): ?><?php navLink('/staff/attendance/lectures', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'Lecture Check-ins', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_face_kiosk')): ?><?php navLink('/attendance/face-kiosk', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>', 'Face Kiosk', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_face_register')): ?><?php navLink('/attendance/face-register', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>', 'Face Register', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_leave_management')): ?><?php navLink('/staff/leaves', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', 'Leave Management', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>

                    <?php if (can('view_staff_roles') || can('view_staff_user_accounts')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Security</div>
                    <?php if (can('view_staff_roles')): ?><?php navLink('/roles', $ic['roles'], 'Roles & Permissions', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_staff_user_accounts')): ?><?php navLink('/staff/users', $ic['users'], 'User Accounts', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>

                    <?php if (can('view_staff_settings')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase px-3" x-show="sidebarOpen">Administration</div>
                    <?php navLink('/staff/settings', $ic['settings'], 'Staff Settings', $currentPath, $sidebarOpen); ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- PAYROLL MANAGEMENT WORKSPACE -->
                <?php if (can('view_payroll_runs') || can('view_payroll_attendance') || can('view_holidays_calendar') || can('view_payroll_audit')): ?>
                <div x-show="currentModule() === 'payroll'" class="space-y-1" x-cloak>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Payroll Management</div>
                    <?php if (can('view_payroll_runs')): ?><?php navLink('/payroll/runs', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>', 'Payroll Runs', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_payroll_attendance')): ?><?php navLink('/payroll/attendance', $ic['attendance'], 'Detailed Attendance', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_holidays_calendar')): ?><?php navLink('/payroll/holidays', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', 'Holidays Calendar', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_payroll_audit')): ?><?php navLink('/payroll/audit', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'Audit History Logs', $currentPath, $sidebarOpen); ?><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- DOCUMENTS WORKSPACE -->
                <?php if (can('view_documents_overview') || can('view_student_documents') || can('view_parent_documents') || can('view_driver_documents') || can('view_certificates')): ?>
                <div x-show="currentModule() === 'documents'" class="space-y-1" x-cloak>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Document Management</div>
                    <?php if (can('view_documents_overview')): ?><?php navLink('/documents/dashboard', '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'Overview', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    
                    <?php if (can('view_student_documents') || can('view_parent_documents') || can('view_driver_documents')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-650 uppercase px-3" x-show="sidebarOpen">Documents</div>
                    <?php if (can('view_student_documents')): ?><?php navLink('/documents/student-documents', '<svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'Student Documents', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_parent_documents')): ?><?php navLink('/documents/parent-documents', '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>', 'Parent Documents', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_driver_documents')): ?><?php navLink('/documents/driver-documents', '<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>', 'Driver Documents', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php endif; ?>

                    <?php if (can('view_certificates')): ?>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-650 uppercase px-3" x-show="sidebarOpen">Generated</div>
                    <?php navLink('/documents/generated?category=certificates', '<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'Certificates', $currentPath, $sidebarOpen); ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- FILE MANAGER WORKSPACE -->
                <?php if (can('view_file_manager')): ?>
                <div x-show="currentModule() === 'documents'" class="space-y-1" x-cloak>
                    <div class="pt-2 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-650 uppercase px-3" x-show="sidebarOpen">File Manager</div>
                    <?php navLink('/file-manager/', '<svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>', 'Browse Files', $currentPath, $sidebarOpen); ?>
                </div>
                <?php endif; ?>

                <!-- LAUNCHER / QUICK NAVIGATION WORKSPACE -->
                <div x-show="currentModule() === 'launcher'" class="space-y-1" x-cloak>
                    <div class="text-2xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Quick Navigation</div>
                    <?php if (can('view_students_list')): ?><?php navLink('/students', $ic['students'], 'Students Registry', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_classes')): ?><?php navLink('/classes', $ic['classes'], 'Classes', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_exams')): ?><?php navLink('/exams', $ic['exams'], 'Exams & Grades', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_transport_overview')): ?><?php navLink('/transport', $ic['transport'], 'Transport Routes', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_documents_overview') || can('view_student_documents')): ?><?php navLink('/documents', '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>', 'Documents Workspace', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_staff_roles')): ?><?php navLink('/roles', $ic['roles'], 'Roles & Permissions', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_reports_overview')): ?><?php navLink('/reports', '<svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>', 'Global Reports', $currentPath, $sidebarOpen); ?><?php endif; ?>
                    <?php if (can('view_general_settings')): ?><?php navLink('/settings', '<svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'System Settings', $currentPath, $sidebarOpen); ?><?php endif; ?>
                </div>

            </div>

        </nav>

        <!-- User Footer -->
        <div class="border-t border-slate-200 dark:border-slate-800/60 p-3">
            <div class="flex items-center gap-3">
                <a href="<?= url('profile') ?>" class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold overflow-hidden">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= url('uploads/avatars/' . $user['avatar']) ?>" class="w-full h-full object-cover" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </a>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <a href="<?= url('profile') ?>" class="text-sm font-medium text-slate-800 dark:text-white truncate hover:text-brand-400 dark:hover:text-brand-400 transition-colors"><?= e($user['name'] ?? '') ?></a>
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
        <?php if (!$isKiosk): ?>
        <?php if ($isDashboard): ?>
        <header class="flex-shrink-0 flex items-center justify-between px-4 md:px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-white/80 dark:bg-surface-900/50 backdrop-blur">
            <div class="flex items-center gap-3">
                <button @click="mobileNav = true" class="md:hidden p-2 -ml-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden bg-white">
                    <img src="<?= url('game/images/logo.png') ?>" class="w-7 h-7 object-contain" alt="PSNF Logo">
                </div>
                <span class="text-sm font-bold text-slate-800 dark:text-white tracking-wide hidden sm:block">PSNF Management System</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Theme Toggler -->
                <button @click="toggleTheme()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all" title="Toggle Theme">
                    <!-- Sun (light mode: click to switch to dark) -->
                    <svg x-show="!isDark" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"/></svg>
                    <!-- Moon (dark mode: click to switch to light) -->
                    <svg x-show="isDark" class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <span class="hidden sm:flex text-xs text-slate-500 items-center gap-1.5 font-medium">
                    <span><?= date('D, d M Y') ?></span>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <span class="live-header-clock font-mono">--:--:--</span>
                </span>
                <div class="flex items-center gap-2">
                    <a href="<?= url('profile') ?>" class="flex items-center gap-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl px-2 py-1.5 transition-all" title="My Profile">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="hidden sm:inline text-xs text-slate-600 dark:text-slate-300 font-medium"><?= e($user['name'] ?? '') ?></span>
                    </a>
                    <a href="<?= url('logout') ?>" class="text-slate-500 hover:text-red-400 transition-colors ml-1" title="Logout">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>        
        </header>
        <?php else: ?>
        <header class="flex-shrink-0 flex items-center justify-between px-4 md:px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-white/80 dark:bg-surface-900/50 backdrop-blur">
            <div class="flex items-center gap-3">
                <button @click="mobileNav = true" class="md:hidden p-2 -ml-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold text-slate-800 dark:text-white"><?= $pageTitle ?? 'Dashboard' ?></h1>
                    <?php if (!empty($breadcrumbs)): ?>
                    <nav class="flex items-center gap-1 mt-0.5">
                        <?php foreach ($breadcrumbs as $i => $bc): ?>
                        <?php if ($i > 0): ?><span class="text-slate-400 dark:text-slate-600 text-xs">·</span><?php endif; ?>
                        <?php if ($i < count($breadcrumbs) - 1): ?>
                        <a href="<?= url(ltrim($bc['url'] ?? '#', '/')) ?>" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-300"><?= e($bc['label']) ?></a>
                        <?php else: ?>
                        <span class="text-xs text-slate-400 dark:text-slate-500"><?= e($bc['label']) ?></span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                </div>
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

                <span class="text-xs text-slate-500 flex items-center gap-1.5 font-medium">
                    <span><?= date('D, d M Y') ?></span>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <span class="live-header-clock font-mono">--:--:--</span>
                </span>
            </div>
        </header>
        <?php endif; ?>
        <?php endif; // !isKiosk ?>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6" id="main-content">
            <?= $content ?>
        </main>
    </div>
</div>

<!-- Global Command Palette Modal (CTRL + K) -->
<div x-show="commandPalette" class="fixed inset-0 overflow-y-auto z-[99999]" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
        <div x-show="commandPalette" x-transition.opacity class="fixed inset-0 bg-slate-950/70 backdrop-blur-md" @click="commandPalette = false"></div>

        <div x-show="commandPalette" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
            <div class="p-4 border-b border-slate-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="searchQuery" placeholder="Type a module, student name, or route... (ESC to close)" class="w-full bg-transparent border-none text-white text-sm focus:outline-none placeholder-slate-500">
                <kbd class="px-2 py-0.5 text-2xs font-mono bg-slate-800 text-slate-400 rounded-md border border-slate-700">ESC</kbd>
            </div>
            <div class="p-3 max-h-96 overflow-y-auto space-y-1">
                <div class="text-2xs font-bold text-slate-500 uppercase px-3 py-1.5">Quick Links & Workspaces</div>
                <a href="<?= url('students') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white transition-all text-xs">
                    <span class="w-6 h-6 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold">1</span>
                    <span>Students Registry Workspace</span>
                </a>
                <a href="<?= url('exams/bulk-entry') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white transition-all text-xs">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">2</span>
                    <span>Bulk Excel Mark Entry Kiosk</span>
                </a>
                <a href="<?= url('transport') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white transition-all text-xs">
                    <span class="w-6 h-6 rounded-lg bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-xs font-bold">3</span>
                    <span>Live GPS Bus Tracking & Routes</span>
                </a>
                <a href="<?= url('roles') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 text-slate-300 hover:text-white transition-all text-xs">
                    <span class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Roles & Excel Permissions Matrix</span>
                </a>
            </div>
        </div>
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
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startHeaderClock);
} else {
    startHeaderClock();
}
document.body.addEventListener('htmx:afterSwap', startHeaderClock);

// Scroll Active Sidebar Link Into Viewport Container
function scrollActiveSidebarIntoView() {
    setTimeout(() => {
        const activeLink = document.querySelector('.sidebar-active-item');
        const navContainer = document.querySelector('aside nav');
        if (activeLink && navContainer) {
            const linkTop = activeLink.offsetTop;
            const linkHeight = activeLink.offsetHeight;
            const containerHeight = navContainer.clientHeight;
            navContainer.scrollTo({
                top: linkTop - (containerHeight / 2) + (linkHeight / 2),
                behavior: 'smooth'
            });
        }
    }, 150);
}
document.addEventListener('DOMContentLoaded', scrollActiveSidebarIntoView);
document.body.addEventListener('htmx:afterSwap', scrollActiveSidebarIntoView);

// Dynamically recalculate sidebar active highlight on HTMX page swaps
function updateSidebarActiveState() {
    const rawPath = window.location.pathname;
    // Normalize path by stripping base directory prefix if present (e.g. /psnf/public)
    const basePrefix = '<?= rtrim(parse_url(url('/'), PHP_URL_PATH), '/') ?>';
    let currentPath = rawPath.startsWith(basePrefix) ? rawPath.substring(basePrefix.length) : rawPath;
    currentPath = currentPath.replace(/\/$/, '') || '/';
    
    document.querySelectorAll('aside nav a').forEach(link => {
        let linkHref = link.getAttribute('href') || '';
        let linkPath = new URL(link.href, window.location.origin).pathname;
        if (linkPath.startsWith(basePrefix)) {
            linkPath = linkPath.substring(basePrefix.length);
        }
        linkPath = linkPath.replace(/\/$/, '') || '/';

        let isActive = false;

        if (linkPath === '/academics' || linkPath === '/academics/') {
            isActive = (currentPath === '/academics' || currentPath === '/academics/' || currentPath === '/academics/overview');
        } else if (linkPath === '/dashboard') {
            isActive = (currentPath === '/dashboard');
        } else if (linkPath !== '/') {
            isActive = (currentPath === linkPath || currentPath.startsWith(linkPath + '/'));
        }

        if (isActive) {
            link.classList.add('sidebar-active-item', 'bg-indigo-600/15', 'text-indigo-600', 'dark:bg-indigo-600/30', 'dark:text-indigo-400', 'border-indigo-500/30', 'dark:border-indigo-500/40', 'shadow-sm', 'font-bold');
            link.classList.remove('text-slate-500', 'hover:bg-slate-100', 'dark:text-slate-400');
        } else {
            link.classList.remove('sidebar-active-item', 'bg-indigo-600/15', 'text-indigo-600', 'dark:bg-indigo-600/30', 'dark:text-indigo-400', 'border-indigo-500/30', 'dark:border-indigo-500/40', 'shadow-sm', 'font-bold');
            link.classList.add('text-slate-500', 'dark:text-slate-400');
        }
    });

    scrollActiveSidebarIntoView();
}

document.addEventListener('DOMContentLoaded', updateSidebarActiveState);
document.body.addEventListener('htmx:afterSwap', updateSidebarActiveState);
window.addEventListener('popstate', updateSidebarActiveState);

// Global Seamless AJAX Interceptor for Forms & Navigation
document.addEventListener('DOMContentLoaded', function() {
    // Automatically convert forms into AJAX submissions using HTMX dynamic binding
    document.querySelectorAll('form:not([hx-post]):not([hx-get]):not([target="_blank"])').forEach(form => {
        const method = (form.method || 'POST').toUpperCase();
        if (method === 'POST') {
            form.setAttribute('hx-post', form.action);
        } else {
            form.setAttribute('hx-get', form.action);
        }
        form.setAttribute('hx-target', '#main-content');
        form.setAttribute('hx-swap', 'innerHTML');
        htmx.process(form);
    });
});
</script>

</body>
</html>
