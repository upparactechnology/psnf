<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PSNF ERP' ?></title>
    <meta name="description" content="Pearl Special Needs Foundation — ERP System">
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
           :class="sidebarOpen ? 'w-64' : 'w-16'" style="background: linear-gradient(180deg, #0f172a 0%, #080d1a 100%);">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-slate-800/60">
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

            <?php $currentPath = \Core\Application::$app->request->getPath(); ?>

            <?php function navLink(string $href, string $icon, string $label, string $current, bool $open = true): void {
                $active = str_starts_with($current, $href) && $href !== '/';
                if ($href === '/dashboard') $active = $current === '/dashboard';
                $classes = $active ? 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all bg-indigo-600/20 text-indigo-400 border border-indigo-500/20' : 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200';
                echo "<a href=\"" . url(ltrim($href, '/')) . "\" class=\"$classes\" title=\"$label\">";
                echo "<span class=\"flex-shrink-0\">$icon</span>";
                if ($open) echo "<span class=\"truncate\">$label</span>";
                echo "</a>";
            }

            $ic = [
                'dashboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'students'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'users'     => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'roles'     => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                'logs'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
            ];
            ?>

            <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-3 mb-2" x-show="sidebarOpen">Overview</div>
            <?php navLink('/dashboard', $ic['dashboard'], 'Dashboard', $currentPath, $sidebarOpen); ?>

            <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-3 mt-5 mb-2" x-show="sidebarOpen">Students</div>
            <?php navLink('/students', $ic['students'], 'Students', $currentPath, $sidebarOpen); ?>

            <div class="text-xs font-semibold text-slate-600 uppercase tracking-wider px-3 mt-5 mb-2" x-show="sidebarOpen">Administration</div>
            <?php navLink('/users', $ic['users'], 'Users', $currentPath, $sidebarOpen); ?>
            <?php navLink('/roles', $ic['roles'], 'Roles & Permissions', $currentPath, $sidebarOpen); ?>

        </nav>

        <!-- User Footer -->
        <?php $user = auth(); ?>
        <div class="border-t border-slate-800/60 p-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div x-show="sidebarOpen" class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?= e($user['name'] ?? '') ?></p>
                    <p class="text-xs text-slate-500 truncate"><?= e(implode(', ', array_slice($user['role_names'] ?? [], 0, 2))) ?></p>
                </div>
                <a x-show="sidebarOpen" href="<?= url('logout') ?>" class="text-slate-500 hover:text-red-400 transition-colors" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top Bar -->
        <header class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-800/60 bg-surface-900/50 backdrop-blur">
            <div>
                <h1 class="text-lg font-semibold text-white"><?= $pageTitle ?? 'Dashboard' ?></h1>
                <?php if (!empty($breadcrumbs)): ?>
                <nav class="flex items-center gap-1 mt-0.5">
                    <?php foreach ($breadcrumbs as $i => $bc): ?>
                    <?php if ($i > 0): ?><span class="text-slate-600 text-xs">›</span><?php endif; ?>
                    <?php if ($i < count($breadcrumbs) - 1): ?>
                    <a href="<?= url(ltrim($bc['url'] ?? '#', '/')) ?>" class="text-xs text-slate-500 hover:text-slate-300"><?= e($bc['label']) ?></a>
                    <?php else: ?>
                    <span class="text-xs text-slate-400"><?= e($bc['label']) ?></span>
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

                <span class="text-xs text-slate-500"><?= date('D, d M Y') ?></span>
            </div>
        </header>

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
