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
</head>
<body class="min-h-screen font-sans antialiased bg-slate-50 text-slate-800 dark:bg-[#080d1a] dark:text-slate-200" style="background-image: radial-gradient(ellipse at 20% 50%, rgba(99,102,241,0.08) 0%, transparent 50%), radial-gradient(ellipse at 80% 20%, rgba(168,85,247,0.05) 0%, transparent 50%);">

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
