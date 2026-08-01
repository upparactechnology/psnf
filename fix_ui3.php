<?php
$f = 'resources/views/attendance/index.php';
$c = file_get_contents($f);

// 1. We replace the alpine radio buttons with guaranteed tailwind classes / styles.
$oldRadiosPattern = '/<div class="inline-flex p-1 bg-slate-100 border border-slate-200 rounded-xl gap-1" x-data="{ status: \'<\?= \$status \?>\' }">.*?<\/div>/s';

$newRadios = '<div class="inline-flex p-1 bg-slate-100 border border-slate-200 rounded-xl gap-1" x-data="{ status: \'<?= $status ?>\' }">
                                            <label class="relative cursor-pointer" @click="status = \'present\'">
                                                <input type="radio" name="attendance[<?= $s[\'id\'] ?>]" value="present" x-model="status" class="sr-only">
                                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
                                                      :style="status === \'present\' ? \'background-color: #10b981; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.05);\' : \'background-color: transparent; color: #64748b;\'">
                                                    Present
                                                </span>
                                            </label>
                                            <label class="relative cursor-pointer" @click="status = \'absent\'">
                                                <input type="radio" name="attendance[<?= $s[\'id\'] ?>]" value="absent" x-model="status" class="sr-only">
                                                <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
                                                      :style="status === \'absent\' ? \'background-color: #f43f5e; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.05);\' : \'background-color: transparent; color: #64748b;\'">
                                                    Absent
                                                </span>
                                            </label>
                                        </div>';

$c = preg_replace($oldRadiosPattern, $newRadios, $c);
file_put_contents($f, $c);
echo "Fixed radio buttons using style.\n";
