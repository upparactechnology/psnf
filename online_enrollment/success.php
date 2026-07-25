<?php
$appCode = htmlspecialchars($_GET['code'] ?? 'APP-SUBMITTED');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted Successfully</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .white-card { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="white-card rounded-3xl p-8 sm:p-10 max-w-lg w-full text-center space-y-6 shadow-xl">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl font-extrabold text-slate-900">Application Submitted!</h1>
            <p class="text-slate-500 text-sm">Thank you! Your student enrollment form and documents have been received successfully.</p>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-1">
            <div class="text-[11px] uppercase text-slate-400 font-bold tracking-wider">Application Reference Number</div>
            <div class="text-2xl font-mono font-bold text-indigo-600"><?= $appCode ?></div>
        </div>

        <p class="text-xs text-slate-400">School administration will review your submitted application and notify you once approved.</p>

        <div>
            <a href="index.php" class="inline-block px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-all shadow-md">Submit Another Response</a>
        </div>
    </div>

</body>
</html>
