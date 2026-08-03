<?php
$layout    = 'app';
$pageTitle = 'Manage Fee Structure: ' . e($structure['name']);
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Structures', 'url' => '/fees/structures'], ['label' => e($structure['name'])]];
ob_start();
?>

<div class="space-y-6" x-data="{ modalOpen: false }">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?= e($structure['name']) ?></h1>
            <p class="text-sm text-slate-500 mt-1">
                <?= e($structure['year_name']) ?> &bull; <?= e($structure['main_group_name']) ?> &bull; <span class="capitalize"><?= str_replace('_', ' ', $structure['installment_type']) ?></span>
            </p>
        </div>
        <button @click="modalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium">
            Add Fee Item
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-medium">
                <tr>
                    <th class="px-6 py-4">Fee Category</th>
                    <th class="px-6 py-4">Amount</th>
                    <th class="px-6 py-4">Due Date</th>
                    <th class="px-6 py-4">Late Fee Policy</th>
                    <th class="px-6 py-4">Mandatory</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php if(empty($items)): ?>
                <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No items added yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-white"><?= e($item['category_name']) ?></td>
                    <td class="px-6 py-4 text-emerald-600 font-medium">₹<?= number_format($item['amount'], 2) ?></td>
                    <td class="px-6 py-4"><?= $item['due_date'] ? date('M d, Y', strtotime($item['due_date'])) : 'No Due Date' ?></td>
                    <td class="px-6 py-4 text-slate-500">
                        <?php 
                        if ($item['late_fee_policy_id']) {
                            $p = array_filter($policies, fn($x) => $x['id'] == $item['late_fee_policy_id']);
                            echo $p ? e(array_values($p)[0]['name']) : 'None';
                        } else {
                            echo 'None';
                        }
                        ?>
                    </td>
                    <td class="px-6 py-4">
                        <?= $item['is_mandatory'] ? '<span class="text-indigo-600">Yes</span>' : '<span class="text-slate-400">Optional</span>' ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form action="<?= url("fees/structures/{$structure['id']}/items/{$item['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Remove item?');">
                            <?= \Core\View::csrf() ?>
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Remove</button>
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
            <form action="<?= url("fees/structures/{$structure['id']}/items") ?>" method="POST">
                <?= \Core\View::csrf() ?>
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-semibold">Add Fee Item</h3>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <label class="block mb-1.5 font-medium">Fee Category</label>
                        <select name="fee_category_id" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?> (<?= e($cat['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium">Amount</label>
                        <input type="number" step="0.01" name="amount" required class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium">Due Date (Optional)</label>
                        <input type="date" name="due_date" class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium">Late Fee Policy</label>
                        <select name="late_fee_policy_id" class="w-full border rounded-xl px-3 py-2 dark:bg-slate-800 dark:border-slate-700">
                            <option value="">No Late Fee</option>
                            <?php foreach ($policies as $pol): ?>
                                <option value="<?= $pol['id'] ?>"><?= e($pol['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_mandatory" value="1" checked class="rounded text-indigo-600">
                            <span>Mandatory Fee</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 text-right space-x-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 font-medium text-slate-600">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-medium">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

