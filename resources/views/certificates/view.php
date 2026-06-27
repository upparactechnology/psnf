<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Certificate — <?= e($design['title']) ?></title>
    <!-- Tailwind CSS (Local Fallback) -->
    <script src="<?= url('js/tailwindcss.js') ?>"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Inter:wght@300;450;600;800&display=swap" rel="stylesheet">
    <style>
        @media print {
            body { background: white !important; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .cert-container {
                box-shadow: none !important;
                border-width: 8px !important;
                margin: 0 !important;
                width: 100% !important;
                height: 100vh !important;
                page-break-inside: avoid;
            }
        }
        @page {
            size: A4 landscape;
            margin: 0;
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen p-4 sm:p-8">

    <!-- Top floating print header (no-print) -->
    <div class="no-print w-full max-w-3xl flex items-center justify-between mb-5 bg-white shadow-md rounded-2xl p-4 border border-slate-200">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="text-sm font-semibold text-slate-800">Certificate Viewer</span>
        </div>
        <div class="flex gap-2">
            <button onclick="window.close()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold rounded-xl text-xs transition-all">
                Close
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-xl text-xs hover:bg-indigo-700 shadow transition-all">
                Print / Save PDF
            </button>
        </div>
    </div>

    <!-- The Certificate visual sheet -->
    <?php if (isset($design['is_legacy']) && $design['is_legacy'] && str_ends_with($design['file_path'], '.pdf')): ?>
        <div class="w-full max-w-3xl bg-white shadow-2xl rounded-2xl overflow-hidden border border-slate-200 flex flex-col items-center p-8 space-y-4">
            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-650">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Certificate PDF Document</h3>
            <p class="text-xs text-slate-500">Your certificate document is loaded below. You can download it directly to print.</p>
            <iframe src="<?= url(ltrim($design['file_path'], '/')) ?>" class="w-full h-[500px] border rounded-xl" frameborder="0"></iframe>
            <a href="<?= url(ltrim($design['file_path'], '/')) ?>" download class="w-full text-center py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs hover:bg-indigo-700 shadow transition-all">
                Download PDF Certificate
            </a>
        </div>
    <?php else: ?>
        <div class="cert-container w-full max-w-3xl bg-white border-8 p-12 flex flex-col justify-between aspect-[1.414/1] shadow-2xl relative select-none rounded-sm border-double"
             style="
                font-family: '<?= e($design['font_family'] ?? 'Playfair Display') ?>', serif;
                border-color: <?= ($design['border_style'] === 'gold') ? '#d97706' : e($design['primary_color'] ?? '#3b82f6') ?>;
                border-style: <?= ($design['border_style'] === 'none') ? 'none' : 'double' ?>;
             ">

        <!-- Background Overlay Patterns -->
        <?php if (($design['template'] ?? 'academic') === 'academic'): ?>
        <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#d97706_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
        <?php elseif (($design['template'] ?? 'academic') === 'creative'): ?>
        <div class="absolute inset-0 opacity-[0.04] bg-gradient-to-br from-indigo-500 via-transparent to-pink-500 pointer-events-none"></div>
        <?php endif; ?>

        <!-- Badge and Top Title -->
        <div class="flex flex-col items-center text-center mt-4">
            <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-md mb-3"
                 style="background: linear-gradient(135deg, <?= e($design['primary_color'] ?? '#d97706') ?>, #92400e);">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11.57M12 11c0 3.517 1.009 6.799 2.753 9.571m3.44-2.04l-.054-.09a13.916 13.916 0 00-2.5-4.96M12 11a5 5 0 10-10 0 5 5 0 0010 0zm0 0a5 5 0 1010 0 5 5 0 00-10 0z"/></svg>
            </div>
            <h2 class="text-2xl font-extrabold uppercase tracking-widest text-slate-800"
                style="color: <?= e($design['primary_color'] ?? '#d97706') ?>;">
                <?= e($design['title']) ?>
            </h2>
            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest mt-1.5">Award of Recognition</p>
        </div>

        <!-- Body / Content -->
        <div class="text-center my-6 space-y-4">
            <p class="text-xs italic text-slate-500">This certifies that the student</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-wide">
                <?= e($design['recipient']) ?>
            </h3>
            <p class="text-sm text-slate-650 max-w-lg mx-auto leading-relaxed">
                <?= e($design['description']) ?>
            </p>
        </div>

        <!-- Footer / Signature Details -->
        <div class="flex items-end justify-between border-t border-slate-100 pt-6 px-8 mb-4">
            <div class="text-left">
                <span class="block text-[9px] text-slate-400 font-mono">Date Issued</span>
                <span class="text-xs font-semibold text-slate-700"><?= e($certificate['issued_at'] ?? date('Y-m-d')) ?></span>
            </div>
            
            <div class="text-center">
                <span class="font-serif text-sm italic text-indigo-600 block mb-1">Het Shah</span>
                <div class="w-28 border-b border-slate-200 mx-auto"></div>
                <span class="block text-[9px] font-bold text-slate-800 uppercase tracking-wider mt-1.5"><?= e($design['issuer_name']) ?></span>
                <span class="block text-[9px] text-slate-400"><?= e($design['issuer_title']) ?></span>
            </div>
        </div>

    </div>
    <?php endif; ?>

</body>
</html>
