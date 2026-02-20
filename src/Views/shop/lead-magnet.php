<?php
$pageTitle = $leadMagnet['title'] ?? 'Free Download';
$tenant = currentTenant();
$metaDescription = $leadMagnet['meta_description'] ?? $leadMagnet['subtitle'] ?? '';
$heroBgColor = $leadMagnet['hero_bg_color'] ?? ($tenant['primary_color'] ?? '#1e40af');

$features = [];
if (!empty($leadMagnet['features'])) {
    $features = json_decode($leadMagnet['features'], true) ?: [];
}

$targetAudience = [];
if (!empty($leadMagnet['target_audience'])) {
    $targetAudience = json_decode($leadMagnet['target_audience'], true) ?: [];
}

$faqItems = [];
if (!empty($leadMagnet['faq'])) {
    $faqItems = json_decode($leadMagnet['faq'], true) ?: [];
}

// Determine cover image: cover_image_path -> hero_image_path -> null
$coverImage = null;
if (!empty($leadMagnet['cover_image_path'])) {
    $coverImage = imageUrl($leadMagnet['cover_image_path']);
} elseif (!empty($leadMagnet['hero_image_path'])) {
    $coverImage = imageUrl($leadMagnet['hero_image_path']);
}

ob_start();
?>

<style>
    .book-mockup {
        perspective: 1200px;
    }
    .book-mockup-inner {
        transform: rotateY(-15deg);
        transform-style: preserve-3d;
        transition: transform 0.4s ease;
        box-shadow: 10px 10px 30px rgba(0,0,0,0.4), 1px 1px 5px rgba(0,0,0,0.2);
    }
    .book-mockup:hover .book-mockup-inner {
        transform: rotateY(-5deg);
    }
    .book-spine {
        position: absolute;
        top: 0;
        left: 0;
        width: 20px;
        height: 100%;
        transform: rotateY(90deg) translateZ(10px);
        transform-origin: left;
    }
</style>

<!-- Hero Section -->
<section class="relative overflow-hidden" style="background-color: <?= h($heroBgColor) ?>;" id="hero">
    <div class="absolute inset-0 bg-gradient-to-br from-black/20 to-transparent"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left: Content -->
            <div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                    <?= h($leadMagnet['hero_headline'] ?? $leadMagnet['title']) ?>
                </h1>
                <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                    <p class="mt-6 text-lg text-white/80 leading-relaxed">
                        <?= h($leadMagnet['hero_subheadline']) ?>
                    </p>
                <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                    <p class="mt-6 text-lg text-white/80 leading-relaxed">
                        <?= h($leadMagnet['subtitle']) ?>
                    </p>
                <?php endif; ?>

                <!-- Mobile book mockup -->
                <div class="mt-8 lg:hidden flex justify-center">
                    <?php if ($coverImage): ?>
                        <div class="book-mockup">
                            <div class="book-mockup-inner rounded-lg overflow-hidden">
                                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>"
                                     class="w-48 h-auto">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="book-mockup">
                            <div class="book-mockup-inner rounded-lg w-48 h-64 bg-white/10 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Book mockup (desktop) + Form -->
            <div class="flex flex-col items-center">
                <!-- Desktop book mockup -->
                <div class="hidden lg:flex justify-center mb-8">
                    <?php if ($coverImage): ?>
                        <div class="book-mockup">
                            <div class="book-mockup-inner rounded-lg overflow-hidden">
                                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>"
                                     class="w-56 h-auto">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="book-mockup">
                            <div class="book-mockup-inner rounded-lg w-56 h-72 bg-white/10 flex items-center justify-center">
                                <svg class="w-20 h-20 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Signup Form -->
                <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md" id="signup-form" x-data="{ loading: false, error: '' }">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Get Your Free Copy</h2>
                    <p class="text-gray-500 text-sm mb-6">Enter your details below and we'll send it straight to your inbox.</p>

                    <form action="/lp/tilmeld" method="POST"
                          @submit.prevent="
                              loading = true; error = '';
                              const fd = new FormData($el);
                              fetch($el.action, { method: 'POST', body: fd })
                                  .then(r => r.json())
                                  .then(d => {
                                      loading = false;
                                      if (d.success) { window.location.href = '/lp/succes/<?= h($leadMagnet['slug']) ?>'; }
                                      else { error = d.message || 'Something went wrong. Please try again.'; }
                                  })
                                  .catch(() => { loading = false; error = 'Network error. Please try again.'; });
                          ">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="lead_magnet_id" value="<?= (int)$leadMagnet['id'] ?>">

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                       placeholder="John Smith">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                       placeholder="john@company.com">
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                        <button type="submit" :disabled="loading"
                                class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transition text-base disabled:opacity-50">
                            <span x-show="!loading"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                            <span x-show="loading" x-cloak>Sending...</span>
                        </button>

                        <p class="mt-4 text-xs text-gray-400 text-center">We respect your privacy. Unsubscribe at any time.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Metrics Bar -->
<section class="bg-gray-50 border-y border-gray-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="flex flex-col items-center space-y-1">
                <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-sm font-semibold text-gray-900">PDF Guide</span>
            </div>
            <div class="flex flex-col items-center space-y-1">
                <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-semibold text-gray-900">100% Free</span>
            </div>
            <div class="flex flex-col items-center space-y-1">
                <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="text-sm font-semibold text-gray-900">Instant Access</span>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<?php if (!empty($features)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?>
            </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php foreach ($features as $i => $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                ?>
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-lg bg-brand/10 flex items-center justify-center mb-4">
                        <?php if ($featureIcon): ?>
                            <span class="text-brand text-lg"><?= h($featureIcon) ?></span>
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

<!-- Target Audience — "Who Is This For?" -->
<?php if (!empty($targetAudience)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Who Is This For?</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php foreach ($targetAudience as $persona): ?>
                <div class="bg-white rounded-xl p-6 border border-gray-200 text-center hover:shadow-md transition">
                    <?php if (!empty($persona['icon'])): ?>
                        <div class="text-4xl mb-4"><?= h($persona['icon']) ?></div>
                    <?php endif; ?>
                    <h3 class="font-semibold text-gray-900 mb-2"><?= h($persona['title'] ?? '') ?></h3>
                    <p class="text-gray-500 text-sm"><?= h($persona['description'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQ Accordion -->
<?php if (!empty($faqItems)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Frequently Asked Questions</h2>
        </div>
        <div class="space-y-4" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <button type="button"
                        @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <span class="font-medium text-gray-900"><?= h($faq['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                             :class="openFaq === <?= $index ?> ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === <?= $index ?>"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         x-cloak>
                        <div class="px-6 pb-4 text-gray-500 text-sm leading-relaxed">
                            <?= h($faq['answer'] ?? '') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Bottom CTA -->
<section class="relative overflow-hidden py-16 lg:py-20" style="background-color: <?= h($heroBgColor) ?>;">
    <div class="absolute inset-0 bg-gradient-to-br from-black/20 to-transparent"></div>
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-center lg:space-x-12 text-center lg:text-left">
            <!-- Book mockup (smaller, repeated) -->
            <?php if ($coverImage): ?>
                <div class="hidden lg:block flex-shrink-0 mb-8 lg:mb-0">
                    <div class="book-mockup">
                        <div class="book-mockup-inner rounded-lg overflow-hidden">
                            <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>"
                                 class="w-36 h-auto">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Ready to Get Started?</h2>
                <p class="text-white/70 mb-8 max-w-lg">Download your free copy now and start implementing today.</p>
                <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
                   class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base">
                    <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
