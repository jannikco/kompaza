<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<!-- 1. Hero Section (Minimal — solid brand background, centered layout) -->
<section class="relative" style="background: <?= h($heroBgColor) ?>;" id="hero">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 text-center">
        <?php if ($heroBadge): ?>
            <div class="inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-medium mb-6">
                <?= h($heroBadge) ?>
            </div>
        <?php endif; ?>

        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-8">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-40 h-auto rounded-lg shadow-md">
            </div>
        <?php endif; ?>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-white leading-tight">
            <?php if (!empty($heroAccent)): ?>
                <?= str_replace(h($heroAccent), '<span class="font-bold">' . h($heroAccent) . '</span>', h($heroHeadline)) ?>
            <?php else: ?>
                <?= h($heroHeadline) ?>
            <?php endif; ?>
        </h1>

        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
            <p class="mt-5 text-lg text-white/80 leading-relaxed max-w-2xl mx-auto"><?= h($leadMagnet['hero_subheadline']) ?></p>
        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
            <p class="mt-5 text-lg text-white/80 leading-relaxed max-w-2xl mx-auto"><?= h($leadMagnet['subtitle']) ?></p>
        <?php endif; ?>

        <!-- Signup Form -->
        <div class="mt-10 max-w-md mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100" id="signup-form" x-data="{ loading: false, error: '' }">
                <h2 class="text-xl font-semibold text-gray-900 mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
                <p class="text-gray-500 text-sm mb-6"><?= h($sh('form_subtitle', 'Enter your details below and we\'ll send it straight to your inbox.')) ?></p>

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
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1 text-left"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 focus:border-gray-300" style="--tw-ring-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" placeholder="John Smith">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1 text-left"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-opacity-50 focus:border-gray-300" style="--tw-ring-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" placeholder="john@company.com">
                        </div>
                    </div>

                    <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                    <button type="submit" :disabled="loading"
                            class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transition text-base disabled:opacity-50">
                        <span x-show="!loading"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                        <span x-show="loading" x-cloak><?= h($sh('form_sending', 'Sending...')) ?></span>
                    </button>

                    <div class="mt-4 flex items-center justify-center space-x-4 text-xs text-gray-400">
                        <span class="flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Secure</span>
                        </span>
                        <span class="text-gray-300">|</span>
                        <span>No spam, ever</span>
                        <span class="text-gray-300">|</span>
                        <span>Unsubscribe anytime</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- 2. Social Proof Bar -->
<section class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-3 gap-4 text-center">
            <?php if (!empty($socialProof)): ?>
                <?php foreach ($socialProof as $proof): ?>
                    <div class="flex flex-col items-center space-y-2">
                        <?php if (!empty($proof['icon'])): ?>
                            <span class="text-2xl"><?= h($proof['icon']) ?></span>
                        <?php endif; ?>
                        <span class="text-xl sm:text-2xl font-semibold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($proof['value'] ?? '') ?></span>
                        <span class="text-sm text-gray-500"><?= h($proof['label'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-xl sm:text-2xl font-semibold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($sh('default_proof_1', 'PDF Guide')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xl sm:text-2xl font-semibold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($sh('default_proof_2', '100% Free')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="text-xl sm:text-2xl font-semibold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($sh('default_proof_3', 'Instant Access')) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Features -->
<?php if (!empty($features)): ?>
<section class="py-24 lg:py-32">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($features as $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                ?>
                <div class="bg-white rounded-lg p-6 border border-gray-100">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
                            <?php if ($featureIcon): ?>
                                <span class="text-brand text-lg"><?= h($featureIcon) ?></span>
                            <?php else: ?>
                                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1"><?= h($featureTitle) ?></h3>
                            <?php if ($featureDesc): ?>
                                <p class="text-gray-500 text-sm leading-relaxed"><?= h($featureDesc) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters -->
<?php if (!empty($chapters)): ?>
<section class="py-24 lg:py-32 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
            <p class="mt-4 text-gray-500"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
        </div>
        <div class="space-y-3">
            <?php foreach ($chapters as $chapter): ?>
                <div class="flex items-start space-x-4 bg-white rounded-lg p-5 border border-gray-100">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
                        <span class="text-brand font-semibold text-sm"><?= h($chapter['number'] ?? '') ?></span>
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
<section class="py-24 lg:py-32">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-<?= min(count($keyStatistics), 4) ?> gap-6">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center p-6 bg-white rounded-lg border border-gray-100">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="text-2xl mb-2"><?= h($stat['icon']) ?></div>
                    <?php endif; ?>
                    <div class="text-2xl sm:text-3xl font-semibold mb-1" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="py-24 lg:py-32 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php if (!empty($beforeAfter['before'])): ?>
                <div class="bg-white rounded-lg p-8 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="font-semibold text-red-600"><?= h($sh('before_label', 'Before')) ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($beforeAfter['before'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-red-300 mt-0.5 flex-shrink-0">&#x2717;</span>
                                <span class="text-gray-600 text-sm"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($beforeAfter['after'])): ?>
                <div class="bg-white rounded-lg p-8 border border-gray-100">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="font-semibold text-green-600"><?= h($sh('after_label', 'After')) ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($beforeAfter['after'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-green-300 mt-0.5 flex-shrink-0">&#x2713;</span>
                                <span class="text-gray-600 text-sm"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 7. Target Audience -->
<?php if (!empty($targetAudience)): ?>
<section class="py-24 lg:py-32">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($targetAudience as $persona): ?>
                <div class="bg-white rounded-lg p-6 border border-gray-100 text-center">
                    <?php if (!empty($persona['icon'])): ?>
                        <div class="text-3xl mb-4"><?= h($persona['icon']) ?></div>
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
<section class="py-24 lg:py-32 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg p-8 border border-gray-100">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($sh('author_title', 'About the Author')) ?></h3>
                    <p class="text-gray-600 text-sm leading-relaxed"><?= h($authorBio) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="py-24 lg:py-32">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-<?= min(count($testimonials), 3) ?> gap-6">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-white rounded-lg p-6 border border-gray-100">
                    <div class="flex space-x-0.5 mb-4">
                        <?php for ($s = 0; $s < 5; $s++): ?>
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-600 mb-4 text-sm italic">"<?= h($testimonial['quote'] ?? '') ?>"</p>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
                            <span class="text-brand text-sm font-semibold"><?= h(mb_substr($testimonial['name'] ?? '?', 0, 1)) ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?= h($testimonial['name'] ?? '') ?></p>
                            <?php if (!empty($testimonial['title'])): ?>
                                <p class="text-xs text-gray-400"><?= h($testimonial['title']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ -->
<?php if (!empty($faqItems)): ?>
<section class="py-24 lg:py-32 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-semibold text-gray-900"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
        </div>
        <div class="space-y-3" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
                    <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-6 py-4 text-left transition">
                        <span class="font-medium text-gray-900 text-sm"><?= h($faq['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-300 transition-transform duration-200 flex-shrink-0 ml-4" :class="openFaq === <?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === <?= $index ?>" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" x-cloak>
                        <div class="px-6 pb-4 text-gray-500 text-sm leading-relaxed"><?= h($faq['answer'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 11. Bottom CTA -->
<section class="py-24 lg:py-32" style="background: <?= h($heroBgColor) ?>;">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-8">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-32 h-auto rounded-lg shadow-md">
            </div>
        <?php endif; ?>
        <h2 class="text-2xl sm:text-3xl font-semibold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition text-base">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
