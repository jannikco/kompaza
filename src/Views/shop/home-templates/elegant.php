<?php
/**
 * Homepage Template: Elegant
 * Split-layout hero (text left, image right), refined typography,
 * card-based content with soft shadows, premium editorial feel.
 */

$primaryColor = $tenant['primary_color'] ?? '#4f46e5';
$secondaryColor = $tenant['secondary_color'] ?? '#0ea5e9';
$heroImage = !empty($tenant['hero_image_path']) ? imageUrl($tenant['hero_image_path']) : '';

// Hero CTA config with fallbacks
$ctaPrimaryText = $heroConfig['cta_primary_text'] ?? (tenantFeature('orders') ? 'Browse Products' : '');
$ctaPrimaryUrl = $heroConfig['cta_primary_url'] ?? (tenantFeature('orders') ? '/produkter' : '');
$ctaSecondaryText = $heroConfig['cta_secondary_text'] ?? '';
$ctaSecondaryUrl = $heroConfig['cta_secondary_url'] ?? '';
if (empty($ctaSecondaryText)) {
    if (tenantFeature('blog')) {
        $ctaSecondaryText = 'Read Our Blog';
        $ctaSecondaryUrl = '/blog';
    } elseif (tenantFeature('ebooks')) {
        $ctaSecondaryText = 'Browse Ebooks';
        $ctaSecondaryUrl = '/eboger';
    }
}
?>

<style>
    .elegant-shape {
        background: linear-gradient(135deg, <?= h($primaryColor) ?>20 0%, <?= h($secondaryColor) ?>20 100%);
    }
    .elegant-accent {
        background-color: <?= h($primaryColor) ?>12;
    }
    .elegant-border-accent {
        border-color: <?= h($primaryColor) ?>30;
    }
    .elegant-tag {
        color: <?= h($primaryColor) ?>;
        background-color: <?= h($primaryColor) ?>10;
    }
</style>

<!-- Hero Section -->
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
            <!-- Text Side -->
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-block mb-6">
                    <span class="elegant-tag text-xs font-semibold uppercase tracking-widest px-4 py-1.5 rounded-full"><?= h($companyName) ?></span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-5xl xl:text-6xl font-extrabold text-gray-900 leading-[1.1] tracking-tight">
                    <?= h($tenant['tagline'] ?? "Welcome to {$companyName}") ?>
                </h1>
                <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-xl">
                    <?= h($tenant['hero_subtitle'] ?? 'Explore our content, resources, and products.') ?>
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <?php if (!empty($ctaPrimaryText)): ?>
                        <a href="<?= h($ctaPrimaryUrl) ?>" class="btn-brand inline-flex items-center justify-center px-7 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base">
                            <?= h($ctaPrimaryText) ?>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($ctaSecondaryText)): ?>
                        <a href="<?= h($ctaSecondaryUrl) ?>" class="inline-flex items-center justify-center px-7 py-3.5 bg-white text-gray-700 font-semibold rounded-lg transition text-base border border-gray-200 hover:border-gray-300 hover:bg-gray-50">
                            <?= h($ctaSecondaryText) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Image / Shape Side -->
            <div class="flex-shrink-0 w-full lg:w-[45%]">
                <?php if ($heroImage): ?>
                    <div class="relative">
                        <div class="elegant-shape absolute -inset-4 rounded-3xl"></div>
                        <img src="<?= h($heroImage) ?>" alt="<?= h($companyName) ?>"
                             class="relative w-full rounded-2xl shadow-lg">
                    </div>
                <?php else: ?>
                    <div class="elegant-shape rounded-3xl aspect-[4/3] flex items-center justify-center">
                        <?php if (!empty($tenant['logo_path'])): ?>
                            <img src="<?= h(imageUrl($tenant['logo_path'])) ?>" alt="<?= h($companyName) ?>" class="max-w-[200px] max-h-[120px] opacity-60">
                        <?php else: ?>
                            <div class="text-center">
                                <div class="text-5xl font-extrabold text-gray-300"><?= h(mb_substr($companyName, 0, 1)) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
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
