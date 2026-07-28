<?php
$layout    = 'app';
$pageTitle = 'Document Settings';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Documents Workspace', 'url' => '/documents'], ['label' => 'Settings']];
ob_start();
?>

<div class="space-y-6 max-w-4xl mx-auto py-2">

    <!-- Title Header -->
    <div class="border-b border-slate-200 dark:border-slate-800 pb-2">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Document Settings</h2>
        <p class="text-xs text-slate-500 mt-0.5">Configure storage paths, verification rules, watermarks, metadata headers, and PDF engines.</p>
    </div>

    <!-- Settings Card -->
    <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 space-y-6">
        
        <form class="space-y-6" onsubmit="alert('Settings saved successfully.'); return false;">
            
            <!-- Storage Settings -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs">Storage &amp; Backup</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">Secure Storage Directory</label>
                        <input type="text" value="C:/xampp/htdocs/psnf/storage/uploads/documents" disabled
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-400 rounded-xl py-2 px-3 text-xs focus:outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">Encryption Standard</label>
                        <select class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2 px-3 text-xs focus:outline-none">
                            <option>AES-256-GCM (Bank Grade)</option>
                            <option>AES-192-CBC</option>
                            <option>No Encryption (Not Recommended)</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-850">

            <!-- PDF Template Global Config -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs">PDF &amp; Print Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">School Primary Logo</label>
                        <input type="file" 
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-500">School Watermark Image</label>
                        <input type="file" 
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-850">

            <!-- Verification Workflows -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs">Verification Workflows</h3>
                <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white">Require Admin Sign-off</h4>
                        <p class="text-[10px] text-slate-500">All uploaded student/staff documents remain in pending state until verified by a manager.</p>
                    </div>
                    <input type="checkbox" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
                <div class="flex items-center justify-between">
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white">Enable Expiry Notifications</h4>
                        <p class="text-[10px] text-slate-500">Send dashboard alerts 15 days before any license, insurance, or health record expires.</p>
                    </div>
                    <input type="checkbox" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-150 dark:border-slate-850">
                <button type="submit" 
                        class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-650 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all">
                    Save Changes
                </button>
            </div>

        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
