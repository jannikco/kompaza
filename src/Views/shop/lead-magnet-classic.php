<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&display=swap');

    .serif-heading {
        font-family: 'Playfair Display', Georgia, serif;
    }

    /* Decorative horizontal rule */
    .classic-hr {
        border: none;
        width: 80px;
        height: 2px;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        margin: 0 auto;
    }

    /* Blockquote large opening quote */
    .classic-quote::before {
        content: '\201C';
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 4rem;
        line-height: 1;
        color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        opacity: 0.3;
        position: absolute;
        top: -0.25rem;
        left: 0;
    }
</style>

<!-- 1. Hero Section -->
<section class="relative overflow-hidden" style="background: linear-gradient(180deg, <?= h($heroBgDarker) ?>, <?= h($heroBgColor) ?>);" id="hero">
    <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/40"></div>
    <div class="relative max-w-2xl mx-auto px-6 py-20 lg:py-28 text-center">

        <?php if ($heroBadge): ?>
            <div class="inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-medium mb-8 backdrop-blur-sm">
                <?= h($heroBadge) ?>
            </div>
        <?php endif; ?>

        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-10">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-48 md:w-56 h-auto rounded-lg shadow-2xl">
            </div>
        <?php endif; ?>

        <h1 class="serif-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight">
            <?php if (!empty($heroAccent)): ?>
                <?= str_replace(h($heroAccent), '<em class="text-brand not-italic" style="color: rgb(' . $r . ',' . $g . ',' . $b . '); filter: brightness(1.6);">' . h($heroAccent) . '</em>', h($heroHeadline)) ?>
            <?php else: ?>
                <?= h($heroHeadline) ?>
            <?php endif; ?>
        </h1>

        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
            <p class="mt-6 text-lg md:text-xl text-white/80 leading-relaxed max-w-xl mx-auto"><?= h($leadMagnet['hero_subheadline']) ?></p>
        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
            <p class="mt-6 text-lg md:text-xl text-white/80 leading-relaxed max-w-xl mx-auto"><?= h($leadMagnet['subtitle']) ?></p>
        <?php endif; ?>

        <!-- Signup Form -->
        <div class="mt-10 bg-white rounded-xl shadow-xl p-8 max-w-md mx-auto" id="signup-form" x-data="{ loading: false, error: '' }">
            <h2 class="serif-heading text-xl font-bold text-gray-900 mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
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
                        <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand" placeholder="John Smith">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1 text-left"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand" placeholder="john@company.com">
                    </div>
                </div>

                <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                <button type="submit" :disabled="loading"
                        class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg shadow-md transition text-base disabled:opacity-50">
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
</section>

<!-- 2. Social Proof -->
<section class="bg-stone-50">
    <div class="max-w-2xl mx-auto px-6 py-12 lg:py-14">
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 text-center">
            <?php if (!empty($socialProof)): ?>
                <?php foreach ($socialProof as $proof): ?>
                    <div class="flex flex-col items-center">
                        <span class="text-2xl font-bold text-brand"><?= h($proof['value'] ?? '') ?></span>
                        <span class="text-sm text-gray-500 mt-1"><?= h($proof['label'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex flex-col items-center">
                    <svg class="w-5 h-5 text-brand mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-sm font-medium text-gray-700"><?= h($sh('default_proof_1', 'PDF Guide')) ?></span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-5 h-5 text-brand mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium text-gray-700"><?= h($sh('default_proof_2', '100% Free')) ?></span>
                </div>
                <div class="flex flex-col items-center">
                    <svg class="w-5 h-5 text-brand mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="text-sm font-medium text-gray-700"><?= h($sh('default_proof_3', 'Instant Access')) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Features -->
<?php if (!empty($features)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
            <hr class="classic-hr mt-6">
        </div>
        <ul class="space-y-6">
            <?php foreach ($features as $i => $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                ?>
                <li class="flex items-start space-x-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= $i + 1 ?></span>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= h($featureTitle) ?></h3>
                        <?php if ($featureDesc): ?>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed"><?= h($featureDesc) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters -->
<?php if (!empty($chapters)): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
            <p class="mt-4 text-gray-500"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
            <hr class="classic-hr mt-6">
        </div>
        <ol class="space-y-5">
            <?php foreach ($chapters as $chapter): ?>
                <li class="flex items-start space-x-4 pb-5 border-b border-gray-200 last:border-b-0 last:pb-0">
                    <span class="flex-shrink-0 text-2xl font-bold text-brand serif-heading" style="min-width: 2rem;"><?= h($chapter['number'] ?? '') ?></span>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= h($chapter['title'] ?? '') ?></h3>
                        <?php if (!empty($chapter['description'])): ?>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed"><?= h($chapter['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA #1 -->
<?php if (!empty($chapters) || !empty($features)): ?>
<section class="py-16 lg:py-20" style="background: linear-gradient(180deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <h2 class="serif-heading text-2xl md:text-3xl font-bold text-white mb-4"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg shadow-md transition text-base">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics -->
<?php if (!empty($keyStatistics)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
            <hr class="classic-hr mt-6">
        </div>
        <div class="flex flex-wrap justify-center gap-8 md:gap-12">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="text-2xl mb-2"><?= h($stat['icon']) ?></div>
                    <?php endif; ?>
                    <div class="text-3xl md:text-4xl font-bold text-brand serif-heading"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500 mt-1"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
            <hr class="classic-hr mt-6">
        </div>

        <?php if (!empty($beforeAfter['before'])): ?>
            <div class="mb-10">
                <h3 class="serif-heading text-lg font-bold text-red-700 mb-4"><?= h($sh('before_label', 'Before')) ?></h3>
                <ul class="space-y-3">
                    <?php foreach ($beforeAfter['before'] as $item): ?>
                        <li class="flex items-start space-x-3">
                            <span class="text-red-400 mt-0.5 flex-shrink-0">&#x2717;</span>
                            <span class="text-gray-600 leading-relaxed"><?= h($item) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <hr class="classic-hr my-8">

        <?php if (!empty($beforeAfter['after'])): ?>
            <div>
                <h3 class="serif-heading text-lg font-bold text-green-700 mb-4"><?= h($sh('after_label', 'After')) ?></h3>
                <ul class="space-y-3">
                    <?php foreach ($beforeAfter['after'] as $item): ?>
                        <li class="flex items-start space-x-3">
                            <span class="text-green-400 mt-0.5 flex-shrink-0">&#x2713;</span>
                            <span class="text-gray-600 leading-relaxed"><?= h($item) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- 7. Target Audience -->
<?php if (!empty($targetAudience)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
            <hr class="classic-hr mt-6">
        </div>
        <ul class="space-y-6">
            <?php foreach ($targetAudience as $persona): ?>
                <li class="flex items-start space-x-4">
                    <?php if (!empty($persona['icon'])): ?>
                        <span class="text-2xl flex-shrink-0"><?= h($persona['icon']) ?></span>
                    <?php else: ?>
                        <span class="flex-shrink-0 text-brand mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    <?php endif; ?>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= h($persona['title'] ?? '') ?></h3>
                        <?php if (!empty($persona['description'])): ?>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed"><?= h($persona['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA #2 -->
<?php if (!empty($targetAudience) || !empty($beforeAfter)): ?>
<section class="py-16 lg:py-20" style="background: linear-gradient(180deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <h2 class="serif-heading text-2xl md:text-3xl font-bold text-white mb-4"><?= h($sh('mid_cta_2', 'Ready to Take the Next Step?')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_2_sub', 'Join thousands of others who have already downloaded this guide.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg shadow-md transition text-base">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('author_title', 'About the Author')) ?></h2>
            <hr class="classic-hr mt-6">
        </div>
        <div class="text-center">
            <p class="text-gray-600 leading-relaxed italic text-lg"><?= h($authorBio) ?></p>
            <?php if (!empty($tenant['company_name'])): ?>
                <p class="mt-6 text-gray-500 font-medium">&mdash; <?= h($tenant['company_name']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
            <hr class="classic-hr mt-6">
        </div>
        <div class="space-y-10">
            <?php foreach ($testimonials as $testimonial): ?>
                <blockquote class="relative pl-10">
                    <div class="classic-quote relative">
                        <p class="text-gray-700 text-lg leading-relaxed italic"><?= h($testimonial['quote'] ?? '') ?></p>
                    </div>
                    <footer class="mt-4 flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-brand/10 flex items-center justify-center flex-shrink-0">
                            <span class="text-brand text-sm font-bold"><?= h(mb_substr($testimonial['name'] ?? '?', 0, 1)) ?></span>
                        </div>
                        <div>
                            <cite class="not-italic text-sm font-medium text-gray-900"><?= h($testimonial['name'] ?? '') ?></cite>
                            <?php if (!empty($testimonial['title'])): ?>
                                <p class="text-xs text-gray-500"><?= h($testimonial['title']) ?></p>
                            <?php endif; ?>
                        </div>
                    </footer>
                </blockquote>
                <?php if ($testimonial !== end($testimonials)): ?>
                    <hr class="classic-hr">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ -->
<?php if (!empty($faqItems)): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-2xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="serif-heading text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
            <hr class="classic-hr mt-6">
        </div>
        <div class="space-y-3" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-6 py-4 text-left">
                        <span class="font-medium text-gray-900"><?= h($faq['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-4" :class="openFaq === <?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
<section class="py-20 lg:py-24" style="background: linear-gradient(180deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-8">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-36 h-auto rounded-lg shadow-xl">
            </div>
        <?php endif; ?>
        <h2 class="serif-heading text-2xl md:text-3xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg shadow-md transition text-base">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
