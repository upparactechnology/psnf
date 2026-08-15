<?php
$layout = 'app';
$title = "Announcements";
?>
<div x-data="{ showModal: false, showDeleteModal: false, deleteId: null }" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Announcements</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Manage and send announcements to parents and staff</p>
        </div>
        <button @click="showModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Announcement
        </button>
    </div>

    <!-- Announcements List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800/60 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800/60">
                    <tr>
                        <th class="px-6 py-4">Title</th>
                        <th class="px-6 py-4">Target</th>
                        <th class="px-6 py-4">Class</th>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Published At</th>
                        <th class="px-6 py-4">Author</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    <?php if (empty($announcements)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                            No announcements found. Click "New Announcement" to create one.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-white"><?= e($announcement['title']) ?></div>
                                <div class="text-xs text-slate-500 mt-1 line-clamp-1"><?= e($announcement['content']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($announcement['target_audience'] === 'all'): ?>
                                    <span class="px-2.5 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-lg text-xs font-medium border border-indigo-200 dark:border-indigo-800/50">All</span>
                                <?php elseif ($announcement['target_audience'] === 'parents'): ?>
                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-medium border border-emerald-200 dark:border-emerald-800/50">Parents</span>
                                <?php elseif ($announcement['target_audience'] === 'teachers'): ?>
                                    <span class="px-2.5 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-medium border border-amber-200 dark:border-amber-800/50">Teachers</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-400 rounded-lg text-xs font-medium border border-slate-200 dark:border-slate-700">Staff</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($announcement['class_name'])): ?>
                                    <span class="px-2.5 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 rounded-lg text-xs font-medium border border-cyan-200 dark:border-cyan-800/50"><?= e($announcement['class_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">All Classes</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (($announcement['priority'] ?? 'normal') === 'critical'): ?>
                                    <span class="px-2.5 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-xs font-bold border border-red-200 dark:border-red-800/50">Critical</span>
                                <?php elseif (($announcement['priority'] ?? 'normal') === 'urgent'): ?>
                                    <span class="px-2.5 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-semibold border border-amber-200 dark:border-amber-800/50">Urgent</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg text-xs font-medium border border-slate-200 dark:border-slate-700">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= date('d M Y, h:i A', strtotime($announcement['published_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= e($announcement['created_by_name'] ?? 'System') ?>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <button @click="deleteId = <?= $announcement['id'] ?>; showDeleteModal = true;" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Delete">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-lg relative z-[101] overflow-hidden m-4">
            
            <form action="<?= url('academics/announcements') ?>" method="POST">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/20">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">New Announcement</h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors placeholder:text-slate-400" placeholder="e.g. Important Update Regarding School Timings">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Target Audience <span class="text-red-500">*</span></label>
                        <select name="target_audience" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                            <option value="parents">Parents Only</option>
                            <option value="teachers">Teachers Only</option>
                            <option value="staff">Staff Only</option>
                            <option value="all">Everyone</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Class (Optional)</label>
                        <select name="class_name" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                            <option value="">All Classes</option>
                            <?php foreach ($classes ?? [] as $cls): ?>
                            <option value="<?= e($cls) ?>"><?= e($cls) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Leave blank to send to all classes</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Priority</label>
                        <select name="priority" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Content <span class="text-red-500">*</span></label>
                        <textarea name="content" required rows="5" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors placeholder:text-slate-400" placeholder="Type your announcement here..."></textarea>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">Send Announcement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
        <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div x-show="showDeleteModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-sm relative z-[101] overflow-hidden m-4 p-6 text-center">
            
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Delete Announcement</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Are you sure you want to delete this announcement? This action cannot be undone.</p>
            
            <div class="flex justify-center gap-3">
                <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                <form :action="'<?= url('academics/announcements/') ?>' + deleteId + '/delete'" method="POST" class="inline">
                    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>

</div>
