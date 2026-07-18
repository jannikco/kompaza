<?php
/**
 * First-visit feature intro modal (Alpine + localStorage).
 *
 * @var string $featureKey  unique key e.g. lead-magnets
 * @var string $title
 * @var string $subtitle
 * @var array  $steps       list of strings
 * @var string $ctaHref
 * @var string $ctaLabel
 */
$featureKey = $featureKey ?? 'feature';
$title = $title ?? 'Welcome';
$subtitle = $subtitle ?? '';
$steps = $steps ?? [];
$ctaHref = $ctaHref ?? '#';
$ctaLabel = $ctaLabel ?? 'Get started';
$storageKey = 'kz_intro_' . preg_replace('/[^a-z0-9_-]/i', '_', $featureKey);
?>
<div
    x-data="{
        open: false,
        key: '<?= h($storageKey) ?>',
        init() {
            try {
                if (!localStorage.getItem(this.key)) {
                    this.open = true;
                }
            } catch (e) { this.open = true; }
        },
        dismiss() {
            this.open = false;
            try { localStorage.setItem(this.key, '1'); } catch (e) {}
        }
    }"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="dismiss()"
    >
        <div
            x-show="open"
            x-transition
            @click.outside="dismiss()"
            class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-gray-200"
        >
            <div class="bg-gradient-to-br from-indigo-600 to-blue-500 px-6 py-8 text-white">
                <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wider mb-2">Feature guide</p>
                <h2 class="text-2xl font-bold"><?= h($title) ?></h2>
                <?php if ($subtitle !== ''): ?>
                    <p class="mt-2 text-indigo-100 text-sm leading-relaxed"><?= h($subtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="px-6 py-6">
                <?php if (!empty($steps)): ?>
                    <ol class="space-y-3 mb-6">
                        <?php foreach ($steps as $i => $step): ?>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-indigo-50 text-indigo-700 text-sm font-semibold flex items-center justify-center"><?= $i + 1 ?></span>
                            <span class="text-sm text-gray-700 pt-1"><?= h($step) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="<?= h($ctaHref) ?>" @click="dismiss()" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        <?= h($ctaLabel) ?>
                    </a>
                    <button type="button" @click="dismiss()" class="text-sm text-gray-500 hover:text-gray-800 px-3 py-2">
                        Don&apos;t show again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
