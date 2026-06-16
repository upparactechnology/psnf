<?php
$layout    = 'app';
$pageTitle = 'Create Fee Invoice';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Fees', 'url' => '/fees'],
    ['label' => 'New Invoice']
];
ob_start();
?>

<div class="max-w-2xl mx-auto">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800/60 bg-white dark:bg-slate-900/30 backdrop-blur p-6 shadow-sm">
        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-6">Create New Student Invoice</h3>

        <form action="<?= url('fees') ?>" method="POST" class="space-y-5">
            <?= \Core\View::csrf() ?>

            <!-- Student Select -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Recipient Student</label>
                <select name="student_id" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    <option value="">Select a student...</option>
                    <?php foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>" <?= (old('student_id') == $student['id']) ? 'selected' : '' ?>>
                        <?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($student['admission_number']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['student_id'])): ?>
                <p class="text-2xs text-red-500 mt-1"><?= $errors['student_id'][0] ?></p>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Invoice Title / Purpose</label>
                <input type="text" name="title" required placeholder="e.g. Tuition Fee — Q3 2026" value="<?= e(old('title')) ?>"
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
                <?php if (isset($errors['title'])): ?>
                <p class="text-2xs text-red-500 mt-1"><?= $errors['title'][0] ?></p>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Invoice Description (Optional)</label>
                <textarea name="description" rows="3" placeholder="Additional details about the fee..."
                          class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all"><?= e(old('description')) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Amount -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Invoice Amount (INR)</label>
                    <input type="number" step="0.01" name="amount" required placeholder="0.00" value="<?= e(old('amount')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all font-semibold">
                    <?php if (isset($errors['amount'])): ?>
                    <p class="text-2xs text-red-500 mt-1"><?= $errors['amount'][0] ?></p>
                    <?php endif; ?>
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Due Date</label>
                    <input type="date" name="due_date" required value="<?= e(old('due_date')) ?>"
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
                    <?php if (isset($errors['due_date'])): ?>
                    <p class="text-2xs text-red-500 mt-1"><?= $errors['due_date'][0] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/20 -mx-6 -mb-6 p-6 rounded-b-2xl">
                <a href="<?= url('fees') ?>"
                   class="flex-1 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-xl text-sm transition-all text-center">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 py-2.5 text-white font-semibold rounded-xl text-sm transition-all text-center shadow-md hover:opacity-95"
                        style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    Issue Invoice
                </button>
            </div>

        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
?>
