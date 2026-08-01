<?php
// Let's modify index and show views to remove disability type
$filesToFix = [
    'resources/views/students/index.php' => [
        '/<td class="px-6 py-4 text-sm text-slate-350 max-w-\[150px\] truncate" title=".*?">.*?<?= e\(\$student\[\'disability_type\'\] \?\? \'ADHD\'\) \?>.*?<\/td>/s' => ''
    ],
    'resources/views/parent/dashboard.php' => [
        '/<p class="text-\[10px\] text-slate-450 dark:text-slate-500 font-bold uppercase tracking-wider">Disability Classification<\/p>\s*<p class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">\s*<\?= e\(\$active_student\[\'disability_type\'\]\)\s*\?>\s*<\/p>/s' => ''
    ],
    'resources/views/admissions/index.php' => [
        '/<div class="flex items-center justify-between">\s*<span class="text-slate-500">Disability Type:<\/span>\s*<span class="text-slate-350 font-medium truncate max-w-\[150px\]"><\?= e\(\$s\[\'disability_type\'\]\) \?><\/span>\s*<\/div>/s' => ''
    ],
    'resources/views/classes/show.php' => [
        '/<th class="px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Disability Classification<\/th>/s' => '',
        '/<!-- Disability -->.*?<td class="px-6 py-4 text-sm text-slate-350 max-w-xs truncate".*?<\/td>/s' => ''
    ],
    'resources/views/parent/add_student.php' => [
        '/\$steps = \[\'Personal Details\', \'School & Address\', \'Care & Disability\'\];/s' => "\$steps = ['Personal Details', 'School & Address'];",
        '/<!-- Step 2: Care & Disability -->.*?<div class="flex justify-between items-center pt-2">/s' => '<div class="flex justify-between items-center pt-2">',
        '/<button type="button" @click="validateStep\(2\)".*?<\/button>/s' => ''
    ]
];

foreach ($filesToFix as $file => $replacements) {
    if (file_exists($file)) {
        $c = file_get_contents($file);
        foreach ($replacements as $pattern => $replacement) {
            $c = preg_replace($pattern, $replacement, $c);
        }
        file_put_contents($file, $c);
        echo "Fixed $file\n";
    }
}
