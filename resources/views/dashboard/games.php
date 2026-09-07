<?php
$layout    = 'app';
$pageTitle = 'Learning Games & Activities';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Learning Games']];
ob_start();

$games = [
    [
        'title'       => 'Money Counting',
        'description' => 'Practice counting coins and bills in real-world shopping scenarios',
        'emoji'       => '🪙',
        'color'       => 'amber',
        'url'         => url('game/money-counting.html'),
        'tag'         => 'Math / Life Skills',
        'permission'  => 'view_game_money_counting',
    ],
    [
        'title'       => 'Safe vs Unsafe',
        'description' => 'Identify safe and unsafe situations to build safety awareness',
        'emoji'       => '🛡️',
        'color'       => 'red',
        'url'         => url('game/safe-vs-unsafe.html'),
        'tag'         => 'Safety Skills',
        'permission'  => 'view_game_safe_vs_unsafe',
    ],
    [
        'title'       => 'Safety Signs',
        'description' => 'Learn to recognize and understand important safety signs and symbols',
        'emoji'       => '⚠️',
        'color'       => 'orange',
        'url'         => url('game/safety-signs.html'),
        'tag'         => 'Safety Awareness',
        'permission'  => 'view_game_safety_signs',
    ],
    [
        'title'       => 'Sentence Builder',
        'description' => 'Drag and drop words to build grammatically correct sentences',
        'emoji'       => '📝',
        'color'       => 'blue',
        'url'         => url('game/sentence-builder.html'),
        'tag'         => 'Language Arts',
        'permission'  => 'view_game_sentence_builder',
    ],
    [
        'title'       => 'Shopping Store',
        'description' => 'Interactive grocery shopping game to practice math and life skills',
        'emoji'       => '🛒',
        'color'       => 'emerald',
        'url'         => url('game/shopping-store.html'),
        'tag'         => 'Life Skills / Math',
        'permission'  => 'view_game_shopping_store',
    ],
];

$colorMap = [
    'amber'   => 'bg-amber-500/10 text-amber-500 dark:bg-amber-500/20 hover:border-amber-400/60',
    'red'     => 'bg-red-500/10 text-red-500 dark:bg-red-500/20 hover:border-red-400/60',
    'orange'  => 'bg-orange-500/10 text-orange-500 dark:bg-orange-500/20 hover:border-orange-400/60',
    'blue'    => 'bg-blue-500/10 text-blue-500 dark:bg-blue-500/20 hover:border-blue-400/60',
    'emerald' => 'bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20 hover:border-emerald-400/60',
    'pink'    => 'bg-pink-500/10 text-pink-500 dark:bg-pink-500/20 hover:border-pink-400/60',
];
?>

<div class="space-y-8 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Learning Games & Activities</h1>
            <p class="text-xs text-slate-500 mt-0.5">Gamified educational modules and interactive games for students with special education needs</p>
        </div>
        <a href="<?= url('game/index.html') ?>" target="_blank" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-pink-600 hover:bg-pink-500 transition-all shadow-sm">🎮 Open Full Game Hub →</a>
    </div>

    <!-- Games Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($games as $game): 
            if (!has_permission($game['permission'])) continue;
            $colors = $colorMap[$game['color']] ?? $colorMap['pink'];
            $parts  = explode(' ', $colors);
            $iconBg = $parts[0] ?? 'bg-pink-500/10';
            $iconText = $parts[1] ?? 'text-pink-500';
            $hoverBorder = $parts[3] ?? 'hover:border-pink-400/60';
        ?>
        <a href="<?= $game['url'] ?>" target="_blank"
           class="group relative p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 <?= $hoverBorder ?> hover:shadow-xl transition-all space-y-4 block">
            <div class="w-14 h-14 rounded-2xl <?= $iconBg ?> <?= $iconText ?> flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                <?= $game['emoji'] ?>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base"><?= e($game['title']) ?></h3>
                <p class="text-xs text-slate-500 mt-1"><?= e($game['description']) ?></p>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-2xs font-bold <?= $iconText ?> <?= $iconBg ?> px-2 py-0.5 rounded-full"><?= e($game['tag']) ?></span>
                <span class="text-xs text-slate-400 font-bold group-hover:text-slate-700 dark:group-hover:text-white transition-colors">Play →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
?>
