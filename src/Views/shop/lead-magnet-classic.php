<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap');

    .serif { font-family: 'Playfair Display', Georgia, serif; }

    /* Ornamental centered HR */
    .orn-hr {
        border: none;
        width: 80px;
        height: 2px;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        margin: 0 auto;
    }

    /* Drop cap for newspaper columns */
    .drop-cap::first-letter {
        font-family: 'Playfair Display', Georgia, serif;
        float: left;
        font-size: 3.2em;
        line-height: 0.8;
        padding-right: 8px;
        padding-top: 4px;
        font-weight: 700;
        color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
    }

    /* Giant decorative quote mark */
    .editorial-quote::before {
        content: '\201C';
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 5rem;
        line-height: 1;
        color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        opacity: 0.25;
        display: block;
        margin-bottom: -1.5rem;
    }

    /* Double-line border strip */
    .double-rule {
        border-top: 2px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        border-bottom: 2px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        padding: 0.75rem 0;
    }

    /* Vertical divider between columns */
    .col-divider {
        border-left: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);
    }
</style>

<!-- 1. Hero Section — Full-width dark gradient with serif headline and form -->
<section class="relative overflow-hidden" id="hero"
    <?php if (!empty($leadMagnet['hero_image_path'])): ?>
        style="background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.75)), url('<?= h(imageUrl($leadMagnet['hero_image_path'])) ?>') center/cover no-repeat; min-height: 70vh;"
    <?php else: ?>
        style="background: linear-gradient(180deg, <?= h($midCtaBgDarker) ?>, <?= h($midCtaBg) ?>); min-height: 60vh;"
    <?php endif; ?>
>
    <div class="relative max-w-3xl mx-auto px-6 py-20 lg:py-28 flex flex-col items-center justify-center text-center" style="min-height: inherit;">
        <?php if ($heroBadge): ?>
            <p class="uppercase tracking-[0.2em] text-xs font-medium text-white/70 mb-6"><?= h($heroBadge) ?></p>
        <?php endif; ?>

        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-8">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-44 md:w-52 h-auto rounded-lg shadow-2xl">
            </div>
        <?php endif; ?>

        <h1 class="serif text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight">
            <?php if (!empty($heroAccent)): ?>
                <?= str_replace(h($heroAccent), '<em class="italic">' . h($heroAccent) . '</em>', h($heroHeadline)) ?>
            <?php else: ?>
                <?= h($heroHeadline) ?>
            <?php endif; ?>
        </h1>

        <hr class="orn-hr mt-8 mb-6" style="background: rgba(255,255,255,0.4);">

        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
            <p class="text-lg md:text-xl text-white/80 leading-relaxed max-w-xl"><?= h($leadMagnet['hero_subheadline']) ?></p>
        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
            <p class="text-lg md:text-xl text-white/80 leading-relaxed max-w-xl"><?= h($leadMagnet['subtitle']) ?></p>
        <?php endif; ?>

        <!-- Signup Form -->
        <div class="mt-10 bg-white rounded-xl shadow-xl p-8 max-w-md w-full" id="signup-form" x-data="{ loading: false, error: '' }">
            <h2 class="serif text-xl font-bold text-gray-900 mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
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
                        <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-400" placeholder="John Smith">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1 text-left"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-400" placeholder="john@company.com">
                    </div>
                </div>

                <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                <button type="submit" :disabled="loading"
                        class="mt-6 w-full btn-brand px-10 py-4 text-white font-bold rounded-full shadow-md transition text-lg uppercase tracking-wide disabled:opacity-50">
                    <span x-show="!loading" class="inline-flex items-center justify-center space-x-2">
                        <span><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
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

<!-- 2. Social Proof — Double-rule strip with pipe separators -->
<section class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-6">
        <div class="double-rule text-center">
            <p class="text-sm tracking-wide text-gray-600 uppercase">
                <?php if (!empty($socialProof)): ?>
                    <?php $parts = []; foreach ($socialProof as $proof): ?>
                        <?php $parts[] = h($proof['value'] ?? '') . ' ' . h($proof['label'] ?? ''); ?>
                    <?php endforeach; ?>
                    <?= implode(' <span class="text-gray-300 mx-2">|</span> ', $parts) ?>
                <?php else: ?>
                    <?= h($sh('default_proof_1', 'PDF Guide')) ?>
                    <span class="text-gray-300 mx-2">|</span>
                    <?= h($sh('default_proof_2', '100% Free')) ?>
                    <span class="text-gray-300 mx-2">|</span>
                    <?= h($sh('default_proof_3', 'Instant Access')) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>

<!-- 3. Features — CSS newspaper columns with drop-cap first letter -->
<?php if (!empty($features)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <div class="columns-1 md:columns-2 gap-10 space-y-6" style="column-rule: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
            <?php foreach ($features as $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                ?>
                <div class="break-inside-avoid mb-6">
                    <h3 class="serif font-bold text-gray-900 text-lg mb-1"><?= h($featureTitle) ?></h3>
                    <?php if ($featureDesc): ?>
                        <p class="drop-cap text-gray-600 leading-relaxed"><?= h($featureDesc) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters — 2-col table of contents: narrow left (numbers), wide right (content) -->
<?php if (!empty($chapters)): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
            <p class="mt-4 text-gray-500"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
            <hr class="orn-hr mt-6">
        </div>
        <div class="space-y-0">
            <?php foreach ($chapters as $chapter): ?>
                <div class="flex items-start border-b border-gray-200 py-5 last:border-b-0">
                    <div class="w-16 flex-shrink-0 col-divider-none">
                        <span class="serif text-3xl font-bold" style="color: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);"><?= h($chapter['number'] ?? '') ?></span>
                    </div>
                    <div class="pl-5 col-divider flex-1" style="padding-left: 1.25rem;">
                        <h3 class="serif font-bold text-gray-900"><?= h($chapter['title'] ?? '') ?></h3>
                        <?php if (!empty($chapter['description'])): ?>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed"><?= h($chapter['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA -->
<?php if (!empty($chapters) || !empty($features)): ?>
<section class="py-16 lg:py-20" style="background: linear-gradient(180deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="serif text-2xl md:text-3xl font-bold text-white mb-4"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full transition hover:bg-gray-100 shadow-lg text-lg uppercase tracking-wide">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics — Horizontal flex row with serif numbers and thin vertical dividers -->
<?php if (!empty($keyStatistics)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <div class="flex flex-wrap justify-center items-start">
            <?php foreach ($keyStatistics as $sIndex => $stat): ?>
                <?php if ($sIndex > 0): ?>
                    <div class="hidden sm:block w-px h-16 bg-gray-200 mx-8 self-center"></div>
                <?php endif; ?>
                <div class="text-center px-4 py-3">
                    <div class="serif text-4xl md:text-5xl font-bold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500 mt-1"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After — Two text columns with decorative vertical line separator -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <?php if (!empty($beforeAfter['before'])): ?>
                <div class="md:pr-8 md:border-r" style="border-color: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);">
                    <h3 class="serif text-lg font-bold text-red-700 mb-4"><?= h($sh('before_label', 'Before')) ?></h3>
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
            <?php if (!empty($beforeAfter['after'])): ?>
                <div class="md:pl-8 mt-8 md:mt-0">
                    <h3 class="serif text-lg font-bold text-green-700 mb-4"><?= h($sh('after_label', 'After')) ?></h3>
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
    </div>
</section>
<?php endif; ?>

<!-- 7. Target Audience -->
<?php if (!empty($targetAudience)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <ul class="space-y-5">
            <?php foreach ($targetAudience as $persona): ?>
                <li class="flex items-start space-x-4">
                    <?php if (!empty($persona['icon'])): ?>
                        <span class="text-2xl flex-shrink-0"><?= h($persona['icon']) ?></span>
                    <?php else: ?>
                        <span class="flex-shrink-0 mt-1" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    <?php endif; ?>
                    <div>
                        <h3 class="serif font-bold text-gray-900"><?= h($persona['title'] ?? '') ?></h3>
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
<section class="py-16 lg:py-20" style="background: linear-gradient(180deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="serif text-2xl md:text-3xl font-bold text-white mb-4"><?= h($sh('mid_cta_2', 'Ready to Take the Next Step?')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_2_sub', 'Join thousands of others who have already downloaded this guide.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full transition hover:bg-gray-100 shadow-lg text-lg uppercase tracking-wide">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-8">
            <h2 class="serif text-2xl md:text-3xl font-bold text-gray-900"><?= h($sh('author_title', 'About the Author')) ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <div class="text-center">
            <p class="serif text-gray-600 leading-relaxed italic text-lg"><?= h($authorBio) ?></p>
            <?php if (!empty($tenant['company_name'])): ?>
                <p class="mt-6 text-gray-500 font-medium">&mdash; <?= h($tenant['company_name']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials — Giant serif pull-quotes with decorative opening quote mark -->
<?php if (!empty($testimonials)): ?>
<section class="bg-white py-20 lg:py-24">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <div class="space-y-12">
            <?php foreach ($testimonials as $tIndex => $testimonial): ?>
                <?php if ($tIndex > 0): ?><hr class="orn-hr my-12"><?php endif; ?>
                <blockquote class="editorial-quote text-center">
                    <p class="serif text-xl sm:text-2xl lg:text-3xl text-gray-800 italic leading-relaxed"><?= h($testimonial['quote'] ?? '') ?></p>
                    <footer class="mt-6">
                        <cite class="not-italic text-sm font-medium text-gray-600 tracking-wide uppercase"><?= h($testimonial['name'] ?? '') ?></cite>
                        <?php if (!empty($testimonial['title'])): ?>
                            <p class="text-xs text-gray-400 mt-1"><?= h($testimonial['title']) ?></p>
                        <?php endif; ?>
                    </footer>
                </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ — All visible, Q: prefix, ornamental HR between -->
<?php if (!empty($faqItems)): ?>
<section class="bg-stone-50 py-20 lg:py-24">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
            <hr class="orn-hr mt-6">
        </div>
        <div>
            <?php foreach ($faqItems as $fIndex => $faq): ?>
                <?php if ($fIndex > 0): ?><hr class="orn-hr my-8"><?php endif; ?>
                <div>
                    <h3 class="serif font-bold text-gray-900 text-lg">Q: <?= h($faq['question'] ?? '') ?></h3>
                    <p class="text-gray-500 mt-2 leading-relaxed"><?= h($faq['answer'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Guarantee -->
<section class="bg-white py-14 lg:py-18">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <hr class="orn-hr mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-5" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
            <svg class="w-7 h-7" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="serif text-xl font-bold text-gray-900 mb-2"><?= h($sh('guarantee_title', '100% Free, No Strings Attached')) ?></h3>
        <p class="text-gray-500 text-sm max-w-md mx-auto"><?= h($sh('guarantee_desc', 'This guide is completely free. No credit card required, no hidden fees. Just actionable insights delivered to your inbox.')) ?></p>
        <hr class="orn-hr mt-8">
    </div>
</section>

<!-- 11. Bottom CTA — White text on dark brand bg, outlined button -->
<section class="py-20 lg:py-24" style="background: linear-gradient(180deg, <?= h($midCtaBgDarker) ?>, <?= h($midCtaBg) ?>);">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-8">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-36 h-auto rounded-lg shadow-xl">
            </div>
        <?php endif; ?>
        <h2 class="serif text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full transition hover:bg-gray-100 shadow-lg text-lg uppercase tracking-wide">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
