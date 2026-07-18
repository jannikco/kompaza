<?php
/**
 * Integration / setup readiness banner.
 *
 * @var string $type    success|warning|error|info
 * @var string $message
 * @var string|null $ctaHref
 * @var string|null $ctaLabel
 */
$type = $type ?? 'warning';
$message = $message ?? '';
$ctaHref = $ctaHref ?? null;
$ctaLabel = $ctaLabel ?? 'Configure';
$styles = [
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-900',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
];
$cls = $styles[$type] ?? $styles['warning'];
?>
<div class="mb-6 rounded-xl border px-4 py-3 <?= $cls ?> flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm"><?= h($message) ?></p>
    <?php if ($ctaHref): ?>
        <a href="<?= h($ctaHref) ?>" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-white/80 hover:bg-white border border-current/10 transition whitespace-nowrap">
            <?= h($ctaLabel) ?>
        </a>
    <?php endif; ?>
</div>
