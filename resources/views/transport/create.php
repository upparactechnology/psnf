<?php
$layout    = 'app';
$pageTitle = 'Add Transport Route';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Transport', 'url' => '/transport'],
    ['label' => 'New Route']
];
ob_start();
?>

<div class="max-w-2xl mx-auto">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 backdrop-blur p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-6">Create New Bus Route</h3>

        <form action="<?= url('transport') ?>" method="POST" class="space-y-5">
            <?= \Core\View::csrf() ?>

            <!-- Route Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Route Name / Destination</label>
                <input type="text" name="route_name" required placeholder="e.g. Route A — Tambaram to Main Campus" value="<?= e(old('route_name')) ?>"
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <?php if (isset($errors['route_name'])): ?>
                <p class="text-2xs text-red-500 mt-1"><?= $errors['route_name'][0] ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Bus Number -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Bus License / Number</label>
                    <input type="text" name="bus_number" required placeholder="e.g. TN-07-BY-1234" value="<?= e(old('bus_number')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all font-mono">
                    <?php if (isset($errors['bus_number'])): ?>
                    <p class="text-2xs text-red-500 mt-1"><?= $errors['bus_number'][0] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Status Select -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive / Rest</option>
                        <option value="en_route" <?= old('status') === 'en_route' ? 'selected' : '' ?>>En Route (Active)</option>
                        <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Driver Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Driver Name</label>
                    <input type="text" name="driver_name" required placeholder="e.g. Ramesh Kumar" value="<?= e(old('driver_name')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    <?php if (isset($errors['driver_name'])): ?>
                    <p class="text-2xs text-red-500 mt-1"><?= $errors['driver_name'][0] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Driver Phone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Driver Phone</label>
                    <input type="text" name="driver_phone" required placeholder="e.g. +91 98765 43210" value="<?= e(old('driver_phone')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    <?php if (isset($errors['driver_phone'])): ?>
                    <p class="text-2xs text-red-500 mt-1"><?= $errors['driver_phone'][0] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Optional GPS Coordinates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Latitude (Optional)</label>
                    <input type="number" step="0.000001" name="current_latitude" placeholder="e.g. 13.0827" value="<?= e(old('current_latitude')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Longitude (Optional)</label>
                    <input type="number" step="0.000001" name="current_longitude" placeholder="e.g. 80.2707" value="<?= e(old('current_longitude')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all font-mono">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/20 -mx-6 -mb-6 p-6 rounded-b-2xl">
                <a href="<?= url('transport') ?>"
                   class="flex-1 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-xl text-sm transition-all text-center">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 py-2.5 text-white font-semibold rounded-xl text-sm transition-all text-center shadow-md hover:opacity-95"
                        style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    Create Route
                </button>
            </div>

        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
?>
