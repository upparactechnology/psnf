<?php
$file = 'resources/views/attendance/index.php';
$content = file_get_contents($file);

$remarksHtml = <<<HTML
                                <!-- Remarks -->
                                <td class="px-6 py-4">
                                    <?php if (has_role('super_admin')): ?>
                                        <input type="text" name="remarks[<?= \$s['id'] ?>]" value="<?= e(\$attRecord['remarks']) ?>"
                                               class="w-full bg-slate-900 border border-slate-800 text-slate-300 placeholder-slate-600 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:border-brand-500 transition-all"
                                               placeholder="e.g. sick leave, late bus">
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400"><?= e(\$attRecord['remarks'] ?: '-') ?></span>
                                    <?php endif; ?>
                                </td>
HTML;

$pattern = '/<!-- Remarks -->\s*<td class="px-6 py-4">\s*<input type="text" name="remarks.*?<\/td>/is';
$content = preg_replace($pattern, $remarksHtml, $content);

file_put_contents($file, $content);
echo "Remarks updated.\n";
