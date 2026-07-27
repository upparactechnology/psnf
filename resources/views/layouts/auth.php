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
    <title><?= $title ?? 'PSNF ERP — Login' ?></title>
    <!-- Tailwind CSS (Local Fallback) -->
    <script src="<?= url('js/tailwindcss.js') ?>"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { brand: { 400:'#818cf8', 500:'#6366f1', 600:'#4f46e5' } } } }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if (!window.tailwind) {
            var style = document.createElement('style');
            style.innerHTML = `
                body { background-color: #080d1a !important; color: #f8fafc !important; font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
                .w-full { width: 100% !important; }
                .max-w-md { max-width: 448px !important; margin: 0 auto !important; box-sizing: border-box !important; }
                .rounded-2xl { border-radius: 1rem !important; }
                .p-8 { padding: 2rem !important; }
                .border { border: 1px solid rgba(255, 255, 255, 0.06) !important; }
                .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important; }
                .text-center { text-align: center !important; }
                .mb-8 { margin-bottom: 2rem !important; }
                .mb-6 { margin-bottom: 1.5rem !important; }
                .mx-auto { margin-left: auto !important; margin-right: auto !important; }
                .mb-4 { margin-bottom: 1rem !important; }
                .flex { display: flex !important; }
                .items-center { align-items: center !important; }
                .justify-center { justify-content: center !important; }
                .space-y-5 > * + * { margin-top: 1.25rem !important; }
                .space-y-1.5 > * + * { margin-top: 0.375rem !important; }
                .block { display: block !important; }
                .text-sm { font-size: 0.875rem !important; }
                .font-medium { font-weight: 500 !important; }
                .text-slate-300 { color: #cbd5e1 !important; }
                .text-slate-400 { color: #94a3b8 !important; }
                .text-slate-500 { color: #64748b !important; }
                .text-slate-600 { color: #475569 !important; }
                .text-white { color: #ffffff !important; }
                .rounded-xl { border-radius: 0.75rem !important; }
                .py-3 { padding-top: 0.75rem !important; padding-bottom: 0.75rem !important; }
                .px-6 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
                .relative { position: relative !important; }
                .absolute { position: absolute !important; }
                .inset-y-0 { top: 0 !important; bottom: 0 !important; }
                .left-0 { left: 0 !important; }
                .right-0 { right: 0 !important; }
                .pointer-events-none { pointer-events: none !important; }
                .justify-between { justify-content: space-between !important; }
                .cursor-pointer { cursor: pointer !important; }
                .gap-2 { gap: 0.5rem !important; }
                .text-brand-400 { color: #818cf8 !important; }
                .w-4 { width: 1rem !important; }
                .h-4 { height: 1rem !important; }
                .text-slate-500 { color: #64748b !important; }
                input { width: 100% !important; box-sizing: border-box !important; background-color: rgba(15, 23, 42, 0.7) !important; border: 1px solid rgba(51, 65, 85, 0.6) !important; color: #fff !important; border-radius: 0.75rem !important; padding: 0.75rem 1rem 0.75rem 2.5rem !important; font-size: 0.875rem !important; }
                input[type="checkbox"] { width: auto !important; margin: 0 !important; padding: 0 !important; }
                input:focus { outline: none !important; border-color: #6366f1 !important; }
                button[type="submit"] { cursor: pointer !important; width: 100% !important; box-sizing: border-box !important; background: linear-gradient(135deg, #6366f1, #a855f7) !important; border: none !important; color: #fff !important; font-weight: 600 !important; border-radius: 0.75rem !important; padding: 0.75rem 1.5rem !important; }
            `;
            document.head.appendChild(style);
        }
    });
    </script>
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
    </style>
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-800 dark:bg-[#080d1a] dark:text-slate-200 transition-colors duration-300" 
      x-data="{ isDark: window.isDark, toggleTheme() { this.isDark = !this.isDark; if (this.isDark) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); } } }"
      style="background-image: radial-gradient(ellipse at 20% 50%, rgba(99,102,241,0.08) 0%, transparent 50%), radial-gradient(ellipse at 80% 20%, rgba(168,85,247,0.05) 0%, transparent 50%);">

<div class="fixed top-4 right-4 z-50">
    <button @click="toggleTheme()" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-white/80 dark:bg-slate-900/80 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-white/5 backdrop-blur shadow-lg transition-all" title="Toggle Theme">
        <!-- Sun (shows in dark mode) -->
        <svg x-show="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z"/></svg>
        <!-- Moon (shows in light mode) -->
        <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
    </button>
</div>

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-2xl" style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1, #a855f7);">
                <svg class="w-8 h-8 text-white" style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">PSNF ERP</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Pearl Special Needs Foundation</p>
        </div>

        <!-- Card -->
        <div class="rounded-2xl p-8 shadow-2xl border border-slate-200 dark:border-white/5 bg-white/95 dark:bg-slate-900/90 backdrop-blur-md">
            <?= $content ?>
        </div>

        <p class="text-center text-xs text-slate-400 dark:text-slate-600 mt-6">© <?= date('Y') ?> Pearl Special Needs Foundation. All rights reserved.</p>
    </div>
</div>

</body>
</html>
