<?php
$layout    = 'app';
$pageTitle = 'Fee Structures';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Fee Structures']];
ob_start();
?>

<div class="space-y-6" x-data="{
    modalOpen: false,
    formData: {
        name: '',
        academic_year_id: '',
        main_group_id: '',
        installment_type: 'one_time',
        is_active: true
    },
    openCreate() {
        this.formData = { name: '', academic_year_id: '', main_group_id: '', installment_type: 'one_time', is_active: true };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Fee Structures</h1>
            <p class="text-sm text-slate-500">Define fee templates for classes and academic years.</p>
        </div>
        <?php if (has_permission('create_fee_structures')): ?>
        <button @click="openCreate()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium">
            Create Structure
        </button>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-medium">
                <tr>
                    <th class="px-6 py-4">Structure Name</th>
                    <th class="px-6 py-4">Academic Year</th>
                    <th class="px-6 py-4">Main Group</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($structures as $st): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-6 py-4 font-medium text-indigo-600 dark:text-indigo-400">
                        <a href="<?= url("fees/structures/{$st['id']}") ?>"><?= e($st['name']) ?></a>
                    </td>
                    <td class="px-6 py-4"><?= e($st['year_name']) ?></td>
                    <td class="px-6 py-4"><?= e($st['main_group_name']) ?></td>
                    <td class="px-6 py-4 capitalize"><?= str_replace('_', ' ', $st['installment_type']) ?></td>
                    <td class="px-6 py-4">
                        <?= $st['is_active'] ? '<span class="text-emerald-600">Active</span>' : '<span class="text-slate-400">Inactive</span>' ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="<?= url("fees/structures/{$st['id']}") ?>" class="text-slate-500 hover:text-slate-700">Manage Items &rarr;</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md" @click.away="modalOpen = false">
            <form action="<?= url('fees/structures') ?>" method="POST">
                <?= \Core\View::csrf() ?>
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-semibold">New Fee Structure</h3>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <label class="block mb-1.5 font-medium">Structure Name</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1.5 font-medium">Academic Year</label>
                            <select name="academic_year_id" x-model="formData.academic_year_id" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                                <option value="">Select...</option>
                                <?php foreach ($academicYears as $y): ?>
                                    <option value="<?= $y['id'] ?>"><?= e($y['year_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 font-medium">Main Group</label>
                            <select name="main_group_id" x-model="formData.main_group_id" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                                <option value="">Select...</option>
                                <?php foreach ($mainGroups as $mg): ?>
                                    <option value="<?= $mg['id'] ?>"><?= e($mg['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium">Installment Type</label>
                        <select name="installment_type" x-model="formData.installment_type" class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                            <option value="one_time">One Time</option>
                            <option value="monthly">Monthly</option>
                            <option value="term_wise">Term Wise (Quarterly/Half-yearly)</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="rounded text-indigo-600">
                            <span>Structure is Active</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 text-right space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 font-medium text-slate-600">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-medium">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

