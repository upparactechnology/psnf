<?php
$layout    = 'app';
$pageTitle = 'Late Fee Policies';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Late Fee Policies']];
ob_start();
?>

<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    policyId: null,
    formData: {
        name: '',
        type: 'fixed',
        amount: 0,
        grace_days: 0,
        max_amount: 0,
        is_active: true
    },
    openCreate() {
        this.editMode = false;
        this.policyId = null;
        this.formData = { name: '', type: 'fixed', amount: 0, grace_days: 0, max_amount: 0, is_active: true };
        this.modalOpen = true;
    },
    openEdit(pol) {
        this.editMode = true;
        this.policyId = pol.id;
        this.formData = {
            name: pol.name,
            type: pol.rule_type === 'one_time' ? 'fixed' : (pol.rule_type === 'fixed_day' ? 'daily' : pol.rule_type),
            amount: pol.value,
            grace_days: pol.grace_days,
            max_amount: pol.max_cap,
            is_active: true
        };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Late Fee Policies</h1>
            <p class="text-sm text-slate-500">Configure automated penalties for overdue invoices.</p>
        </div>
        <button @click="openCreate()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium">
            Add Policy
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-medium">
                <tr>
                    <th class="px-6 py-4">Policy Name</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Amount</th>
                    <th class="px-6 py-4">Grace Period</th>
                    <th class="px-6 py-4">Max Cap</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($policies as $pol): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-6 py-4 font-medium"><?= e($pol['name']) ?></td>
                    <td class="px-6 py-4 capitalize"><?= e($pol['rule_type']) ?></td>
                    <td class="px-6 py-4"><?= $pol['rule_type'] === 'percentage' ? e($pol['value']) . '%' : '₹' . e($pol['value']) ?></td>
                    <td class="px-6 py-4"><?= e($pol['grace_days']) ?> Days</td>
                    <td class="px-6 py-4"><?= (float)$pol['max_cap'] > 0 ? '₹' . e($pol['max_cap']) : 'No Limit' ?></td>
                    <td class="px-6 py-4">
                        <span class="text-emerald-600">Active</span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <button @click='openEdit(<?= json_encode($pol) ?>)' class="text-indigo-600">Edit</button>
                        <form action="<?= url("fees/late-fee-policies/{$pol['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Delete policy?');">
                            <?= \Core\View::csrf() ?>
                            <button type="submit" class="text-red-600">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md" @click.away="modalOpen = false">
            <form :action="editMode ? '<?= url('fees/late-fee-policies/') ?>' + policyId : '<?= url('fees/late-fee-policies') ?>'" method="POST">
                <?= \Core\View::csrf() ?>
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-semibold" x-text="editMode ? 'Edit Policy' : 'New Policy'"></h3>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <label class="block mb-1.5 font-medium">Policy Name</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1.5 font-medium">Type</label>
                            <select name="type" x-model="formData.type" class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="daily">Daily Flat Fee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 font-medium">Amount/Value</label>
                            <input type="number" step="0.01" name="amount" x-model="formData.amount" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1.5 font-medium">Grace Period (Days)</label>
                            <input type="number" name="grace_days" x-model="formData.grace_days" class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block mb-1.5 font-medium">Max Cap Limit (0 for none)</label>
                            <input type="number" step="0.01" name="max_amount" x-model="formData.max_amount" class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="rounded text-indigo-600">
                            <span>Policy is Active</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 text-right space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 font-medium text-slate-600">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-medium">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

