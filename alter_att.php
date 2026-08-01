<?php
$file = 'resources/views/attendance/index.php';
$content = file_get_contents($file);

$statusHtml = <<<HTML
                                <!-- Status Toggles -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if (has_role('super_admin')): ?>
                                        <div class="inline-flex p-1 bg-slate-950/40 border border-slate-850 rounded-xl">
                                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-emerald-600 has-[:checked]:text-white text-slate-450 hover:text-white">
                                                <input type="radio" name="attendance[<?= \$s['id'] ?>]" value="present" <?= \$status === 'present' ? 'checked' : '' ?> class="sr-only">
                                                Present
                                            </label>
                                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-amber-600 has-[:checked]:text-white text-slate-450 hover:text-white">
                                                <input type="radio" name="attendance[<?= \$s['id'] ?>]" value="late" <?= \$status === 'late' ? 'checked' : '' ?> class="sr-only">
                                                Late
                                            </label>
                                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-red-600 has-[:checked]:text-white text-slate-450 hover:text-white">
                                                <input type="radio" name="attendance[<?= \$s['id'] ?>]" value="absent" <?= \$status === 'absent' ? 'checked' : '' ?> class="sr-only">
                                                Absent
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <?php if (\$status === 'present'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Present</span>
                                        <?php elseif (\$status === 'late'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">Late</span>
                                        <?php elseif (\$status === 'absent'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Absent</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">null</span>
                                        <?php endif; ?>
                                        <input type="hidden" name="attendance[<?= \$s['id'] ?>]" value="<?= e(\$status ?? '') ?>">
                                    <?php endif; ?>
                                </td>
HTML;

// Regex to replace the entire <td> block for status
$pattern = '/<!-- Status Toggles -->\s*<td class="px-6 py-4 whitespace-nowrap text-center">.*?<\/td>/s';

$content = preg_replace($pattern, $statusHtml, $content);

// Also we should hide the "Save Daily Attendance" button for non super admins, unless they can still save remarks?
// The user said: "can edit that attendnace only super admin". Let's hide the submit button for non super admins as well, or keep it if they can save remarks.
// It's safer to just wrap the submit button in if(has_role('super_admin'))
$submitBtnPattern = '/(<div class="flex justify-end pt-3">\s*<button type="submit"[^>]+>\s*Save Daily Attendance\s*<\/button>\s*<\/div>)/is';
$content = preg_replace($submitBtnPattern, "<?php if (has_role('super_admin')): ?>\n        $1\n        <?php endif; ?>", $content);

file_put_contents($file, $content);
echo "Admin attendance toggles updated.\n";
