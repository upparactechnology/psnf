<?php
$layout    = 'app';
$pageTitle = 'Create Exam';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Exams Setup', 'url' => '/academics/exams?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId . '&semester=' . urlencode($selectedSemester)],
    ['label' => 'Create Exam']
];
ob_start();
?>

<div x-data="createExam()" x-init="fetchSubjects()" class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-2xl border border-slate-800/60 bg-slate-900/40 backdrop-blur">
        <div>
            <h2 class="text-xl font-bold text-white">Create Exam</h2>
            <p class="text-sm text-slate-500 mt-0.5">Define a new exam with subject assignments</p>
        </div>
        <a href="<?= url('academics/exams?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId . '&semester=' . urlencode($selectedSemester)) ?>"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-750 border border-slate-700/60 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Exams
        </a>
    </div>

    <!-- Create Form -->
    <div class="rounded-2xl border border-slate-800/60 bg-slate-900/40 p-6 shadow-sm">
        <form method="POST" action="<?= url('academics/exams/store') ?>" class="space-y-5">
            <?= \Core\View::csrf() ?>
            <input type="hidden" name="main_group_id" :value="selectedGroupId">
            <input type="hidden" name="academic_year_id" :value="selectedYearId">

            <div class="space-y-1.5">
                <label class="block text-slate-400 font-medium text-xs">Exam Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Unit Test - 1"
                       class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium text-xs">Academic Year <span class="text-red-400">*</span></label>
                    <select name="academic_year_id" x-model="selectedYearId" required
                            class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y['id'] ?>" <?= $selectedYearId == $y['id'] ? 'selected' : '' ?>><?= e($y['year_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium text-xs">Main Group <span class="text-red-400">*</span></label>
                    <select name="main_group_id" x-model="selectedGroupId" @change="fetchSubjects()" required
                            class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="0">-- No Group --</option>
                        <?php foreach ($mainGroups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-slate-400 font-medium text-xs">Semester <span class="text-red-400">*</span></label>
                    <select name="semester" required
                            class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:border-brand-500 transition-all">
                        <option value="Semester 1" <?= $selectedSemester === 'Semester 1' ? 'selected' : '' ?>>Semester 1</option>
                        <option value="Semester 2" <?= $selectedSemester === 'Semester 2' ? 'selected' : '' ?>>Semester 2</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-slate-400 font-medium text-xs">Max Marks <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="max_marks" value="100" required
                       class="w-full bg-slate-950 border border-slate-800 text-slate-300 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:border-brand-500 transition-all font-mono">
            </div>

            <div class="space-y-1.5">
                <label class="block text-slate-400 font-medium text-xs">Curriculum Subjects <span class="text-slate-600 font-normal">(leave empty for all)</span></label>
                <div class="max-h-64 overflow-y-auto bg-slate-950 border border-slate-800 rounded-xl p-3">
                    <template x-if="loadingSubjects">
                        <div class="flex items-center justify-center py-6">
                            <svg class="animate-spin h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span class="ml-2 text-xs text-slate-500">Loading subjects...</span>
                        </div>
                    </template>
                    <template x-if="!loadingSubjects && curriculumSubjects.length === 0">
                        <p class="text-xs text-slate-600 italic py-6 text-center">No curriculum subjects found for this group. Create a curriculum template first.</p>
                    </template>
                    <template x-if="!loadingSubjects && curriculumSubjects.length > 0">
                        <div class="space-y-1">
                            <template x-for="subj in curriculumSubjects" :key="subj.id">
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-slate-900 rounded px-2 py-1.5">
                                    <input type="checkbox" name="subject_ids[]" :value="subj.id"
                                           class="rounded border-slate-700 text-indigo-500 focus:ring-indigo-500 bg-slate-900">
                                    <span class="text-xs text-slate-300" x-text="subj.name"></span>
                                    <span class="text-[10px] text-slate-600 ml-auto" x-text="subj.category"></span>
                                </label>
                            </template>
                        </div>
                    </template>
                </div>
                <p class="text-[10px] text-slate-600">Select specific subjects from the curriculum or leave empty to include all</p>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-slate-800">
                <a href="<?= url('academics/exams?academic_year_id=' . $selectedYearId . '&main_group_id=' . $selectedGroupId . '&semester=' . urlencode($selectedSemester)) ?>"
                   class="flex-1 py-2.5 border border-slate-800 text-slate-300 hover:bg-slate-800 font-semibold rounded-xl text-sm transition-all text-center">
                    Cancel
                </a>
                <button type="submit"
                        class="flex-1 py-2.5 text-white font-semibold rounded-xl text-sm transition-all text-center shadow-md hover:opacity-95 bg-gradient-to-r from-indigo-500 to-purple-600">
                    Create Exam
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function createExam() {
    return {
        selectedGroupId: <?= $selectedGroupId ?>,
        selectedYearId: <?= $selectedYearId ?>,
        curriculumSubjects: [],
        loadingSubjects: false,

        async fetchSubjects() {
            if (!this.selectedGroupId || this.selectedGroupId == 0) {
                this.curriculumSubjects = [];
                return;
            }
            this.loadingSubjects = true;
            try {
                const resp = await fetch('<?= url("academics/exams/subjects-by-group") ?>?main_group_id=' + this.selectedGroupId);
                this.curriculumSubjects = await resp.json();
            } catch (e) {
                this.curriculumSubjects = [];
            }
            this.loadingSubjects = false;
        }
    }
}
</script>

<?php
$content = ob_get_clean();
?>
