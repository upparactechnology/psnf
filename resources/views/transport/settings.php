<?php
$layout    = 'app';
$pageTitle = 'Transport Settings';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Transport Settings']];
ob_start();
?>

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Transport Workspace Settings</h1>
        <p class="text-xs text-slate-500 mt-0.5">Tab-based administration for Route Buffers, Notifications, GPS Providers & School Transport Calendar</p>
    </div>

    <!-- Active Transport Configuration Card -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">System Transport Defaults</h3>
            <span class="px-2.5 py-0.5 rounded-full text-2xs font-bold bg-emerald-500/10 text-emerald-500">ACTIVE CONFIG</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-mono">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Default Pickup Buffer</span>
                <span class="font-bold text-slate-900 dark:text-white">10 Minutes</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Max Route Capacity</span>
                <span class="font-bold text-indigo-500">50 Students</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">Parent Alerts</span>
                <span class="font-bold text-emerald-500">SMS & WhatsApp</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                <span class="text-2xs text-slate-400 block font-sans">GPS Refresh Interval</span>
                <span class="font-bold text-purple-500">5 Seconds</span>
            </div>
        </div>
    </div>

    <!-- Campus Location Form -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm">Campus Location</h3>
        </div>
        <p class="text-xs text-slate-500">Set the latitude and longitude of the school campus. This is used for live tracking routes once a student is picked up.</p>
        
        <form action="<?= url('transport/settings') ?>" method="POST" class="space-y-4 max-w-md">
            <?= \Core\View::csrf() ?>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Campus Latitude</label>
                <input type="text" name="campus_lat" value="<?= e($campusLat) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="e.g. 23.0225" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Campus Longitude</label>
                <input type="text" name="campus_lng" value="<?= e($campusLng) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="e.g. 72.5714" required>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all">Save Location</button>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
