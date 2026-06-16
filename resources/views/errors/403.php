<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Forbidden | PSNF ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body style="background: radial-gradient(ellipse at center, #0f172a 0%, #080d1a 100%); min-height:100vh; display:flex; align-items:center; justify-content:center;">
    <div class="text-center px-6">
        <div class="text-8xl font-black mb-4" style="background: linear-gradient(135deg, #f59e0b, #ef4444); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">403</div>
        <h1 class="text-2xl font-bold text-white mb-2">Access Forbidden</h1>
        <p class="text-slate-500 text-sm mb-8 max-w-sm mx-auto">You don't have permission to access this resource. Contact your administrator.</p>
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white mr-3" style="background: linear-gradient(135deg, #6366f1, #a855f7);">← Go Back</a>
        <a href="<?= url('dashboard') ?>" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-medium text-slate-400 border border-slate-700/50 hover:text-white hover:border-slate-600 transition-all">Dashboard</a>
    </div>
</body>
</html>
