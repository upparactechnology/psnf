<?php
$layout    = 'app';
$pageTitle = 'Live GPS Fleet Tracking';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Live Tracking']];
ob_start();
?>

<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Live Fleet GPS Radar</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time GPS vehicle movement, speed radar & ETA calculations</p>
        </div>
        <span class="px-3 py-1 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500 font-mono flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            LIVE RADAR ACTIVE
        </span>
    </div>

    <!-- Live Map Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 flex flex-col items-center justify-center min-h-[350px] space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-3xl font-bold">📍</div>
            <div class="text-center space-y-1">
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Interactive GPS Map Active</h3>
                <p class="text-xs text-slate-500 max-w-sm">Bus MH-12-AB-5678 is currently moving at 35 km/h near Pearl Heights Stop.</p>
            </div>
        </div>

        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
            <h3 class="font-bold text-slate-900 dark:text-white text-xs uppercase tracking-wider">Live ETA Radar</h3>
            <div class="space-y-3 font-mono text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1">
                    <span class="text-slate-400 block text-2xs font-sans">Current Stop</span>
                    <span class="font-bold text-slate-900 dark:text-white">Society Main Gate</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1">
                    <span class="text-slate-400 block text-2xs font-sans">Next Stop</span>
                    <span class="font-bold text-indigo-500">Pearl Heights (ETA: 4 mins)</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1">
                    <span class="text-slate-400 block text-2xs font-sans">School Arrival ETA</span>
                    <span class="font-bold text-emerald-500">08:45 AM</span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
