<?php
$pageTitle = $ebook['title'] ?? 'Ebook';
$tenant = currentTenant();
$metaDescription = $ebook['meta_description'] ?? $ebook['subtitle'] ?? '';
$hasHero = !empty($ebook['hero_headline']);

// Decode JSON fields
$features = !empty($ebook['features']) ? (json_decode($ebook['features'], true) ?: []) : [];
$keyMetrics = !empty($ebook['key_metrics']) ? (json_decode($ebook['key_metrics'], true) ?: []) : [];
$chapters = !empty($ebook['chapters']) ? (json_decode($ebook['chapters'], true) ?: []) : [];
$targetAudience = !empty($ebook['target_audience']) ? (json_decode($ebook['target_audience'], true) ?: []) : [];
$testimonials = !empty($ebook['testimonials']) ? (json_decode($ebook['testimonials'], true) ?: []) : [];
$faq = !empty($ebook['faq']) ? (json_decode($ebook['faq'], true) ?: []) : [];

$heroBgColor = $ebook['hero_bg_color'] ?? ($tenant['primary_color'] ?? '#1e40af');
$heroCtaText = $ebook['hero_cta_text'] ?? ($ebook['price_dkk'] > 0 ? 'Buy Now' : 'Download Free');
$featuresHeadline = $ebook['features_headline'] ?? "What You'll Learn";

// Auto-generate key metrics from page_count if none set
if (empty($keyMetrics) && !empty($ebook['page_count'])) {
    $keyMetrics = [
        ['value' => $ebook['page_count'] . '+', 'label' => 'Pages'],
        ['value' => 'PDF', 'label' => 'Format'],
        ['value' => 'Instant', 'label' => 'Access'],
    ];
}

ob_start();

if ($hasHero):
?>

<!-- Section 1: Hero -->
<section class="relative overflow-hidden" style="background-color: <?= h($heroBgColor) ?>;">
    <div class="absolute inset-0 bg-gradient-to-br from-black/20 to-transparent"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left: Content -->
            <div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                    <?= h($ebook['hero_headline']) ?>
                </h1>
                <?php if (!empty($ebook['hero_subheadline'])): ?>
                    <p class="mt-6 text-lg text-white/80 leading-relaxed">
                        <?= h($ebook['hero_subheadline']) ?>
                    </p>
                <?php elseif (!empty($ebook['subtitle'])): ?>
                    <p class="mt-6 text-lg text-white/80 leading-relaxed">
                        <?= h($ebook['subtitle']) ?>
                    </p>
                <?php endif; ?>

                <div class="mt-8 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <?php if ($ebook['price_dkk'] > 0): ?>
                        <a href="/ebog/<?= h($ebook['slug']) ?>/buy"
                           class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg transition shadow-lg hover:bg-gray-100 text-base">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            <?= h($heroCtaText) ?>
                        </a>
                        <span class="text-2xl font-extrabold text-white"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    <?php else: ?>
                        <a href="/ebog/<?= h($ebook['slug']) ?>/download"
                           class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg transition shadow-lg hover:bg-gray-100 text-base">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <?= h($heroCtaText) ?>
                        </a>
                        <span class="text-2xl font-extrabold text-green-300">Free</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Cover Image -->
            <div class="flex justify-center">
                <?php if (!empty($ebook['cover_image_path'])): ?>
                    <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                         class="rounded-xl shadow-2xl max-w-xs sm:max-w-sm">
                <?php else: ?>
                    <div class="bg-white/10 rounded-xl aspect-[3/4] w-64 flex items-center justify-center">
                        <svg class="w-24 h-24 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Key Metrics Bar -->
<?php if (!empty($keyMetrics)): ?>
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap justify-center gap-8 sm:gap-16">
            <?php foreach ($keyMetrics as $metric): ?>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-extrabold text-gray-900"><?= h($metric['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500 mt-1"><?= h($metric['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 3: Features -->
<?php if (!empty($features)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($featuresHeadline) ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php foreach ($features as $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                ?>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <div class="w-10 h-10 rounded-lg bg-brand/10 flex items-center justify-center mb-4">
                        <?php if ($featureIcon): ?>
                            <span class="text-lg"><?= $featureIcon ?></span>
                        <?php else: ?>
                            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2"><?= h($featureTitle) ?></h3>
                    <?php if ($featureDesc): ?>
                        <p class="text-gray-500 text-sm"><?= h($featureDesc) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 4: Chapters -->
<?php if (!empty($chapters)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">What's Inside</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <?php foreach ($chapters as $i => $chapter): ?>
                <div class="flex gap-4 bg-gray-50 rounded-xl p-6 border border-gray-100">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center font-bold text-sm">
                        <?= $i + 1 ?>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= h($chapter['title'] ?? '') ?></h3>
                        <?php if (!empty($chapter['description'])): ?>
                            <p class="text-gray-500 text-sm mt-1"><?= h($chapter['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 5: Target Audience -->
<?php if (!empty($targetAudience)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Who Is This For?</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php foreach ($targetAudience as $persona): ?>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm text-center">
                    <?php if (!empty($persona['icon'])): ?>
                        <span class="text-3xl mb-4 block"><?= $persona['icon'] ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-gray-900 mb-2"><?= h($persona['title'] ?? '') ?></h3>
                    <?php if (!empty($persona['description'])): ?>
                        <p class="text-gray-500 text-sm"><?= h($persona['description']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 6: Description -->
<?php if (!empty($ebook['description'])): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-8">About This Ebook</h2>
        <div class="prose prose-gray max-w-none text-gray-600">
            <?= $ebook['description'] ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 7: Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">What Readers Say</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= min(count($testimonials), 3) ?> gap-8 max-w-5xl mx-auto">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                    <svg class="w-8 h-8 text-gray-200 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151C7.546 6.068 5.983 8.789 5.983 11H10v10H0z"/></svg>
                    <p class="text-gray-600 mb-4"><?= h($testimonial['quote'] ?? '') ?></p>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm"><?= h($testimonial['name'] ?? '') ?></p>
                        <?php if (!empty($testimonial['title'])): ?>
                            <p class="text-gray-400 text-xs"><?= h($testimonial['title']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 8: FAQ Accordion -->
<?php if (!empty($faq)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Frequently Asked Questions</h2>
        </div>
        <div class="space-y-4" x-data="{ open: null }">
            <?php foreach ($faq as $i => $item): ?>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <button @click="open = open === <?= $i ?> ? null : <?= $i ?>"
                            class="w-full flex items-center justify-between px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 transition">
                        <span class="font-semibold text-gray-900 pr-4"><?= h($item['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                             :class="{ 'rotate-180': open === <?= $i ?> }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === <?= $i ?>" x-collapse x-cloak
                         class="px-6 py-4 text-gray-600 text-sm leading-relaxed">
                        <?= h($item['answer'] ?? '') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Section 9: Final CTA -->
<section class="relative overflow-hidden" style="background-color: <?= h($heroBgColor) ?>;">
    <div class="absolute inset-0 bg-gradient-to-br from-black/20 to-transparent"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Ready to Get Started?</h2>
        <p class="text-white/70 mb-8">Get your copy today and start learning immediately.</p>
        <?php if ($ebook['price_dkk'] > 0): ?>
            <div class="mb-6">
                <span class="text-3xl font-extrabold text-white"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
            </div>
            <a href="/ebog/<?= h($ebook['slug']) ?>/buy"
               class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg transition shadow-lg hover:bg-gray-100 text-base">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <?= h($heroCtaText) ?>
            </a>
        <?php else: ?>
            <a href="/ebog/<?= h($ebook['slug']) ?>/download"
               class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg transition shadow-lg hover:bg-gray-100 text-base">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <?= h($heroCtaText) ?>
            </a>
        <?php endif; ?>
    </div>
</section>

<?php else: ?>
<!-- Simple Mode: Original 2-column layout -->

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center text-sm text-gray-400 space-x-2">
                <li><a href="/" class="hover:text-gray-600 transition">Home</a></li>
                <li><span>/</span></li>
                <li><a href="/eboger" class="hover:text-gray-600 transition">Ebooks</a></li>
                <li><span>/</span></li>
                <li class="text-gray-600 truncate max-w-xs"><?= h($ebook['title']) ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
            <!-- Cover Image -->
            <div>
                <?php if (!empty($ebook['cover_image_path'])): ?>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                             class="w-full h-auto object-cover">
                    </div>
                <?php else: ?>
                    <div class="bg-gray-100 rounded-xl border border-gray-200 aspect-[3/4] flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ebook Details -->
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight"><?= h($ebook['title']) ?></h1>
                <?php if (!empty($ebook['subtitle'])): ?>
                    <p class="mt-3 text-lg text-gray-500"><?= h($ebook['subtitle']) ?></p>
                <?php endif; ?>

                <!-- Meta Info -->
                <div class="flex items-center gap-4 mt-6 text-sm text-gray-500">
                    <?php if (!empty($ebook['page_count'])): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <?= (int)$ebook['page_count'] ?> pages
                        </span>
                    <?php endif; ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </span>
                </div>

                <!-- Price -->
                <div class="mt-8">
                    <?php if ($ebook['price_dkk'] > 0): ?>
                        <span class="text-3xl font-extrabold text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    <?php else: ?>
                        <span class="text-3xl font-extrabold text-green-600">Free</span>
                    <?php endif; ?>
                </div>

                <!-- CTA Button -->
                <div class="mt-8">
                    <?php if ($ebook['price_dkk'] > 0): ?>
                        <a href="/ebog/<?= h($ebook['slug']) ?>/buy"
                           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            Buy Now
                        </a>
                    <?php else: ?>
                        <a href="/ebog/<?= h($ebook['slug']) ?>/download"
                           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Free
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($ebook['description'])): ?>
                    <div class="mt-10 pt-8 border-t border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">About This Ebook</h2>
                        <div class="prose prose-gray max-w-none text-gray-600">
                            <?= $ebook['description'] ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Features -->
                <?php if (!empty($features)): ?>
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">What You'll Learn</h2>
                        <ul class="space-y-3">
                            <?php foreach ($features as $feature): ?>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-gray-600"><?= h(is_array($feature) ? ($feature['text'] ?? $feature['title'] ?? '') : $feature) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
