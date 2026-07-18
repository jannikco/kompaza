<?php
/**
 * Setup checklist card.
 *
 * @var array $steps  [ ['key'=>, 'label'=>, 'done'=>bool, 'href'=>, 'description'=> ], ... ]
 * @var bool  $dismissed
 * @var bool  $showWelcome
 */
$steps = $steps ?? [];
$dismissed = !empty($dismissed);
$showWelcome = !empty($showWelcome);
$doneCount = 0;
foreach ($steps as $s) {
    if (!empty($s['done'])) $doneCount++;
}
$total = max(count($steps), 1);
$pct = (int)round(($doneCount / $total) * 100);
$complete = $doneCount >= count($steps) && count($steps) > 0;
?>
<?php if (!$dismissed && !empty($steps)): ?>
<div class="mb-8" x-data="{ welcome: <?= $showWelcome ? 'true' : 'false' ?>, open: true }">
    <?php if ($showWelcome): ?>
    <div
        x-show="welcome"
        x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="welcome = false"
    >
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden" @click.outside="welcome = false">
            <div class="bg-gradient-to-br from-indigo-600 via-indigo-500 to-blue-500 px-6 py-10 text-white text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/15 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <h2 class="text-2xl font-bold">Welcome to Kompaza</h2>
                <p class="mt-2 text-indigo-100 text-sm">Your site is live. Complete these steps to get your first lead or sale.</p>
            </div>
            <div class="p-6">
                <ul class="space-y-2 mb-6">
                    <?php foreach ($steps as $s): ?>
                    <li class="flex items-center gap-2 text-sm text-gray-700">
                        <span class="w-5 h-5 rounded-full border-2 border-indigo-200 flex-shrink-0"></span>
                        <?= h($s['label']) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" @click="welcome = false" class="w-full px-5 py-3 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                    Start setup
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    <?= $complete ? 'Setup complete' : 'Getting started' ?>
                </h3>
                <p class="text-sm text-gray-500 mt-0.5"><?= $doneCount ?> of <?= count($steps) ?> steps done</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:block w-32 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                </div>
                <form method="POST" action="/admin/onboarding/dismiss" class="inline">
                    <?= csrfField() ?>
                    <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">Dismiss</button>
                </form>
            </div>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($steps as $s): ?>
            <a href="<?= h($s['href'] ?? '#') ?>" class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition">
                <span class="mt-0.5 flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center <?= !empty($s['done']) ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' ?>">
                    <?php if (!empty($s['done'])): ?>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <?php else: ?>
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                    <?php endif; ?>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-medium <?= !empty($s['done']) ? 'text-gray-500 line-through' : 'text-gray-900' ?>"><?= h($s['label']) ?></span>
                    <?php if (!empty($s['description'])): ?>
                        <span class="block text-xs text-gray-500 mt-0.5"><?= h($s['description']) ?></span>
                    <?php endif; ?>
                </span>
                <?php if (empty($s['done'])): ?>
                    <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
