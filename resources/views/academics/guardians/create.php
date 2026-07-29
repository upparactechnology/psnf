<?php
$layout = 'app';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= e($title ?? 'Add Parent') ?></h1>
        <a href="<?= url('academics/parents') ?>" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
            &larr; Back to Directory
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <form action="<?= url('academics/parents') ?>" method="POST" class="space-y-5">
            <?= \Core\View::csrf() ?>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="e.g. John Doe" value="<?= e(old('name')) ?>">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
                    <input type="text" name="phone" required class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="10-digit mobile" value="<?= e(old('phone')) ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="parent@example.com" value="<?= e(old('email')) ?>">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Link Students</label>
                <select name="students[]" multiple class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors h-32">
                    <?php if(!empty($students)): foreach($students as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name'] . ' (' . $s['admission_number'] . ')') ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <p class="text-xs text-slate-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple students.</p>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium shadow-sm">
                    Save Parent
                </button>
            </div>
        </form>
    </div>
</div>
