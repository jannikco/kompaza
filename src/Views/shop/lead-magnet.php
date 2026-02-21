<?php
$pageTitle = $leadMagnet['title'] ?? 'Free Download';
$tenant = currentTenant();
$metaDescription = $leadMagnet['meta_description'] ?? $leadMagnet['subtitle'] ?? '';
$heroBgColor = $leadMagnet['hero_bg_color'] ?? ($tenant['primary_color'] ?? '#1e40af');

$features = [];
if (!empty($leadMagnet['features'])) {
    $features = json_decode($leadMagnet['features'], true) ?: [];
}

$chapters = [];
if (!empty($leadMagnet['chapters'])) {
    $chapters = json_decode($leadMagnet['chapters'], true) ?: [];
}

$keyStatistics = [];
if (!empty($leadMagnet['key_statistics'])) {
    $keyStatistics = json_decode($leadMagnet['key_statistics'], true) ?: [];
}

$targetAudience = [];
if (!empty($leadMagnet['target_audience'])) {
    $targetAudience = json_decode($leadMagnet['target_audience'], true) ?: [];
}

$faqItems = [];
if (!empty($leadMagnet['faq'])) {
    $faqItems = json_decode($leadMagnet['faq'], true) ?: [];
}

$beforeAfter = null;
if (!empty($leadMagnet['before_after'])) {
    $beforeAfter = json_decode($leadMagnet['before_after'], true);
    if ($beforeAfter && empty($beforeAfter['before']) && empty($beforeAfter['after'])) {
        $beforeAfter = null;
    }
}

$testimonials = [];
if (!empty($leadMagnet['testimonial_templates'])) {
    $testimonials = json_decode($leadMagnet['testimonial_templates'], true) ?: [];
}

$socialProof = [];
if (!empty($leadMagnet['social_proof'])) {
    $socialProof = json_decode($leadMagnet['social_proof'], true) ?: [];
}

$authorBio = $leadMagnet['author_bio'] ?? '';

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

<!-- 1. Hero Section -->
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

<!-- 2. Social Proof Bar -->
<section class="bg-gray-50 border-y border-gray-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-3 gap-4 text-center">
            <?php if (!empty($socialProof)): ?>
                <?php foreach ($socialProof as $proof): ?>
                    <div class="flex flex-col items-center space-y-1">
                        <?php if (!empty($proof['icon'])): ?>
                            <span class="text-2xl"><?= h($proof['icon']) ?></span>
                        <?php endif; ?>
                        <span class="text-sm font-bold text-gray-900"><?= h($proof['value'] ?? '') ?></span>
                        <span class="text-xs text-gray-500"><?= h($proof['label'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Features Section -->
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

<!-- 4. Chapters / Table of Contents -->
<?php if (!empty($chapters)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Table of Contents</h2>
            <p class="mt-3 text-gray-500">A preview of what you'll find inside</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
            <?php foreach ($chapters as $chapter): ?>
                <div class="flex items-start space-x-4 bg-white rounded-xl p-5 border border-gray-200 hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-lg bg-brand/10 flex items-center justify-center flex-shrink-0">
                        <span class="text-brand font-bold text-sm"><?= h($chapter['number'] ?? '') ?></span>
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

<!-- 5. Key Statistics -->
<?php if (!empty($keyStatistics)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">By the Numbers</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-<?= min(count($keyStatistics), 4) ?> gap-6 max-w-3xl mx-auto">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center p-6 bg-gray-50 rounded-xl border border-gray-100">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="text-3xl mb-2"><?= h($stat['icon']) ?></div>
                    <?php endif; ?>
                    <div class="text-3xl sm:text-4xl font-extrabold text-brand mb-1"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After Transformation -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">The Transformation</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <?php if (!empty($beforeAfter['before'])): ?>
                <div class="bg-white rounded-xl p-8 border border-red-100">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="font-semibold text-red-700 text-lg">Before</h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($beforeAfter['before'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-red-400 mt-0.5 flex-shrink-0">&#x2717;</span>
                                <span class="text-gray-600"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($beforeAfter['after'])): ?>
                <div class="bg-white rounded-xl p-8 border border-green-100">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="font-semibold text-green-700 text-lg">After</h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($beforeAfter['after'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-green-400 mt-0.5 flex-shrink-0">&#x2713;</span>
                                <span class="text-gray-600"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 7. Target Audience — "Who Is This For?" -->
<?php if (!empty($targetAudience)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Who Is This For?</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php foreach ($targetAudience as $persona): ?>
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 text-center hover:shadow-md transition">
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

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl p-8 border border-gray-200">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 rounded-full bg-brand/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">About the Author</h3>
                    <p class="text-gray-600 leading-relaxed"><?= h($authorBio) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">What Readers Say</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-<?= min(count($testimonials), 3) ?> gap-8 max-w-4xl mx-auto">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                    <svg class="w-8 h-8 text-brand/20 mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151C7.563 6.068 6 8.789 6 11h4v10H0z"/>
                    </svg>
                    <p class="text-gray-700 mb-4 italic">"<?= h($testimonial['quote'] ?? '') ?>"</p>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-brand/10 flex items-center justify-center">
                            <span class="text-brand text-sm font-bold"><?= h(mb_substr($testimonial['name'] ?? '?', 0, 1)) ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?= h($testimonial['name'] ?? '') ?></p>
                            <?php if (!empty($testimonial['title'])): ?>
                                <p class="text-xs text-gray-500"><?= h($testimonial['title']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ Accordion -->
<?php if (!empty($faqItems)): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Frequently Asked Questions</h2>
        </div>
        <div class="space-y-4" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
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

<!-- 11. Bottom CTA -->
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
