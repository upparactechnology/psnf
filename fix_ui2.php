<?php
$f = 'resources/views/attendance/index.php';
$c = file_get_contents($f);

// 1. We replace the super_admin attendance radio buttons entirely:
$oldRadiosPattern = '/<\?php if \(has_role\(\'super_admin\'\)\): \?>\s*<div class="inline-flex p-1 bg-slate-950\/40 border border-slate-850 rounded-xl">.*?<\/div>\s*<\?php else: \?>/s';

$newRadios = '<?php if (has_role(\'super_admin\')): ?>
                                        <div class="inline-flex p-1 bg-slate-100 border border-slate-200 rounded-xl gap-1">
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="attendance[<?= $s[\'id\'] ?>]" value="present" <?= $status === \'present\' ? \'checked\' : \'\' ?> class="peer sr-only">
                                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-transparent text-slate-500 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm">
                                                    Present
                                                </span>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="attendance[<?= $s[\'id\'] ?>]" value="absent" <?= $status === \'absent\' ? \'checked\' : \'\' ?> class="peer sr-only">
                                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-transparent text-slate-500 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-sm">
                                                    Absent
                                                </span>
                                            </label>
                                        </div>
                                    <?php else: ?>';

$c = preg_replace($oldRadiosPattern, $newRadios, $c);
file_put_contents($f, $c);
echo "Fixed radio buttons.\n";

// 2. Fix the leaves.php bug
$f2 = 'resources/views/attendance/leaves.php';
$c2 = file_get_contents($f2);
$c2 = str_replace("require base_path('resources/views/layouts/' . \$layout . '.php');", "// view rendering handled by controller", $c2);
file_put_contents($f2, $c2);
echo "Fixed leaves.php.\n";
