<?php
/**
 * Shared empty state card.
 *
 * @var string      $title
 * @var string      $body
 * @var string|null $ctaHref
 * @var string|null $ctaLabel
 * @var string|null $secondaryHref
 * @var string|null $secondaryLabel
 * @var string|null $icon  SVG path d attribute (optional)
 */
$title = $title ?? 'Nothing here yet';
$body = $body ?? '';
$ctaHref = $ctaHref ?? null;
$ctaLabel = $ctaLabel ?? 'Get started';
$secondaryHref = $secondaryHref ?? null;
$secondaryLabel = $secondaryLabel ?? null;
$icon = $icon ?? 'M12 6v6m0 0v6m0-6h6m-6 0H6';
?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
    <div class="mx-auto w-14 h-14 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
        <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= h($icon) ?>"/>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($title) ?></h3>
    <?php if ($body !== ''): ?>
        <p class="text-gray-500 mb-6 max-w-md mx-auto"><?= h($body) ?></p>
    <?php endif; ?>
    <div class="flex flex-wrap items-center justify-center gap-3">
        <?php if ($ctaHref): ?>
            <a href="<?= h($ctaHref) ?>" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                <?= h($ctaLabel) ?>
            </a>
        <?php endif; ?>
        <?php if ($secondaryHref): ?>
            <a href="<?= h($secondaryHref) ?>" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                <?= h($secondaryLabel ?? 'Learn more') ?>
            </a>
        <?php endif; ?>
    </div>
</div>
