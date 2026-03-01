<?php
/**
 * Homepage Template: Starter (default)
 * Clean, centered design with white hero, content grids, newsletter signup.
 */

// Hero CTA config with fallbacks
$ctaPrimaryText = $heroConfig['cta_primary_text'] ?? (tenantFeature('orders') ? 'Browse Products' : '');
$ctaPrimaryUrl = $heroConfig['cta_primary_url'] ?? (tenantFeature('orders') ? '/produkter' : '');
$ctaSecondaryText = $heroConfig['cta_secondary_text'] ?? (tenantFeature('blog') ? 'Read Our Blog' : '');
$ctaSecondaryUrl = $heroConfig['cta_secondary_url'] ?? (tenantFeature('blog') ? '/blog' : '');
?>

<!-- Hero Section -->
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight tracking-tight">
                <?= h($tenant['tagline'] ?? "Welcome to {$companyName}") ?>
            </h1>
            <?php if (!empty($tenant['hero_subtitle'])): ?>
                <p class="mt-6 text-lg sm:text-xl text-gray-500 leading-relaxed">
                    <?= h($tenant['hero_subtitle']) ?>
                </p>
            <?php else: ?>
                <p class="mt-6 text-lg sm:text-xl text-gray-500 leading-relaxed">
                    Explore our content, resources, and products.
                </p>
            <?php endif; ?>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <?php if (!empty($ctaPrimaryText)): ?>
                    <a href="<?= h($ctaPrimaryUrl) ?>" class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base">
                        <?= h($ctaPrimaryText) ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($ctaSecondaryText)): ?>
                    <a href="<?= h($ctaSecondaryUrl) ?>" class="inline-flex items-center justify-center px-8 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition text-base">
                        <?= h($ctaSecondaryText) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Render sections loop
foreach ($sections as $section) {
    if (empty($section['enabled'])) continue;
    $sectionType = $section['type'] ?? '';
    $partialFile = VIEWS_PATH . "/shop/home-sections/{$sectionType}.php";
    if (file_exists($partialFile)) {
        include $partialFile;
    }
}
?>
