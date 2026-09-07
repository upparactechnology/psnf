<?php
$layout    = 'app';
$pageTitle = 'Fee Categories';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Fees', 'url' => '/fees'], ['label' => 'Fee Categories']];
ob_start();
?>

<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    categoryId: null,
    formData: {
        name: '',
        code: '',
        description: '',
        tax: 0,
        is_refundable: false,
        is_active: true,
        display_order: 0
    },
    openCreate() {
        this.editMode = false;
        this.categoryId = null;
        this.formData = { name: '', code: '', description: '', tax: 0, is_refundable: false, is_active: true, display_order: 0 };
        this.modalOpen = true;
    },
    openEdit(cat) {
        this.editMode = true;
        this.categoryId = cat.id;
        this.formData = {
            name: cat.name,
            code: cat.code,
            description: cat.description,
            tax: cat.tax,
            is_refundable: cat.is_refundable == 1,
            is_active: cat.is_active == 1,
            display_order: cat.display_order
        };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Fee Categories</h1>
            <p class="text-sm text-slate-500 mt-1">Manage global fee types like Tuition, Admission, Transport, etc.</p>
        </div>
        <?php if (has_permission('create_fee_categories')): ?>
        <button @click="openCreate()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/30 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Name & Code</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4">Tax %</th>
                        <th class="px-6 py-4">Refundable</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No categories found.</td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900 dark:text-white"><?= e($cat['name']) ?></div>
                            <div class="text-xs text-slate-500 mt-0.5"><?= e($cat['code']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 max-w-xs truncate" title="<?= e($cat['description']) ?>">
                            <?= e($cat['description']) ?: '-' ?>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            <?= (float)$cat['tax'] > 0 ? e($cat['tax']) . '%' : '-' ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($cat['is_refundable']): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Yes</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($cat['is_active']): ?>
                                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400"></span> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <?php if (has_permission('edit_fee_categories')): ?>
                            <button @click='openEdit(<?= json_encode($cat) ?>)' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium text-xs transition-colors">Edit</button>
                            <?php endif; ?>
                            <?php if (has_permission('delete_fee_categories')): ?>
                            <form action="<?= url("fees/categories/{$cat['id']}/delete") ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium text-xs transition-colors">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div x-show="modalOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div x-show="modalOpen" x-transition.scale.origin.bottom class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md overflow-hidden ring-1 ring-slate-200 dark:ring-slate-800">
            
            <form :action="editMode ? '<?= url('fees/categories/') ?>' + categoryId : '<?= url('fees/categories') ?>'" method="POST" class="flex flex-col max-h-[90vh]">
                <?= \Core\View::csrf() ?>
                
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white" x-text="editMode ? 'Edit Category' : 'New Category'"></h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 overflow-y-auto space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Category Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="formData.name" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Short Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" x-model="formData.code" required class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                        <textarea name="description" x-model="formData.description" rows="2" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tax (%)</label>
                            <input type="number" step="0.01" min="0" name="tax" x-model="formData.tax" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Display Order</label>
                            <input type="number" name="display_order" x-model="formData.display_order" class="w-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-2 border-t border-slate-100 dark:border-slate-800/50 mt-2">
                        <label class="text-sm text-slate-700 dark:text-slate-300 font-medium">Refundable</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_refundable" value="1" x-model="formData.is_refundable" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between pb-2">
                        <label class="text-sm text-slate-700 dark:text-slate-300 font-medium">Active Status</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-3">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-sm" x-text="editMode ? 'Save Changes' : 'Create Category'"></button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();

