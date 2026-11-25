<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.08); }
    </style>
</head>
<body class="min-h-screen py-16 px-4 sm:px-6">
    <div class="max-w-xl mx-auto glass-card rounded-3xl p-8 text-center space-y-5">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600/20 text-emerald-400 border border-emerald-500/30">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-extrabold text-white">Application Submitted</h1>
        <p class="text-slate-400 text-sm">Thank you. The school office will review the application and contact you.</p>
        <div class="rounded-2xl bg-slate-900/80 border border-slate-700 p-4">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Your application code</p>
            <p class="text-xl font-mono font-bold text-indigo-300 mt-1"><?= e($appCode ?? '') ?></p>
        </div>
        <p class="text-xs text-slate-500">Please keep this code for any follow-up with the school.</p>
    </div>
</body>
</html>
