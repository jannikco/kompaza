<?php
/**
 * Homepage Template: Bold
 * Full-width gradient hero, modern SaaS aesthetic.
 * Trust strip, featured content cards with hover effects, bold newsletter CTA.
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
    .bold-hero-gradient {
        background: linear-gradient(135deg, <?= h($primaryColor) ?> 0%, <?= h($secondaryColor) ?> 100%);
    }
    .bold-hero-gradient-light {
        background: linear-gradient(135deg, <?= h($primaryColor) ?>15 0%, <?= h($secondaryColor) ?>15 100%);
    }
    .bold-card:hover {
        transform: translateY(-4px);
    }
    .bold-gradient-text {
        background: linear-gradient(135deg, <?= h($primaryColor) ?>, <?= h($secondaryColor) ?>);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<!-- Hero Section -->
<section class="bold-hero-gradient relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight tracking-tight">
                    <?= h($tenant['tagline'] ?? "Welcome to {$companyName}") ?>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-white/80 leading-relaxed max-w-2xl">
                    <?= h($tenant['hero_subtitle'] ?? 'Explore our content, resources, and products.') ?>
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <?php if (!empty($ctaPrimaryText)): ?>
                        <a href="<?= h($ctaPrimaryUrl) ?>" class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition shadow-lg text-base">
                            <?= h($ctaPrimaryText) ?>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($ctaSecondaryText)): ?>
                        <a href="<?= h($ctaSecondaryUrl) ?>" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur text-white font-semibold rounded-xl hover:bg-white/20 transition border border-white/20 text-base">
                            <?= h($ctaSecondaryText) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($heroImage): ?>
                <div class="flex-shrink-0 lg:w-[45%]">
                    <img src="<?= h($heroImage) ?>" alt="<?= h($companyName) ?>"
                         class="w-full rounded-2xl shadow-2xl">
                </div>
            <?php endif; ?>
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
