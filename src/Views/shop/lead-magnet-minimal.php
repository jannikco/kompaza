<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<!-- MINIMAL: "The Substack Newsletter" — narrow single-column, text-only, no cards, no grids -->

<!-- 1. Hero Section — White background, centered, bare underline inputs -->
<section class="bg-white" id="hero">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-20 lg:py-28">
        <?php if ($heroBadge): ?>
            <p class="text-center text-sm font-medium tracking-wide uppercase mb-6" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
                <?= h($heroBadge) ?>
            </p>
        <?php endif; ?>

        <?php if ($coverImage): ?>
            <div class="flex justify-center mb-8">
                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-32 h-auto rounded shadow-sm">
            </div>
        <?php endif; ?>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight text-center">
            <?php if (!empty($heroAccent)): ?>
                <?= str_replace(h($heroAccent), '<span style="color: rgb(' . $r . ',' . $g . ',' . $b . ');">' . h($heroAccent) . '</span>', h($heroHeadline)) ?>
            <?php else: ?>
                <?= h($heroHeadline) ?>
            <?php endif; ?>
        </h1>

        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
            <p class="mt-5 text-lg text-gray-500 leading-relaxed text-center max-w-xl mx-auto"><?= h($leadMagnet['hero_subheadline']) ?></p>
        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
            <p class="mt-5 text-lg text-gray-500 leading-relaxed text-center max-w-xl mx-auto"><?= h($leadMagnet['subtitle']) ?></p>
        <?php endif; ?>

        <!-- Signup Form — bare underline inputs, no card wrapper -->
        <div class="mt-12 max-w-sm mx-auto" id="signup-form" x-data="{ loading: false, error: '' }">
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

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm text-gray-500 mb-1"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                        <input type="text" id="name" name="name" required
                               class="w-full bg-transparent border-0 border-b-2 border-gray-200 px-0 py-2 text-gray-900 placeholder-gray-300 focus:outline-none focus:border-gray-900 transition-colors"
                               placeholder="John Smith">
                    </div>
                    <div>
                        <label for="email" class="block text-sm text-gray-500 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                        <input type="email" id="email" name="email" required
                               class="w-full bg-transparent border-0 border-b-2 border-gray-200 px-0 py-2 text-gray-900 placeholder-gray-300 focus:outline-none focus:border-gray-900 transition-colors"
                               placeholder="john@company.com">
                    </div>
                </div>

                <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 text-red-700 text-sm rounded" x-text="error"></div>

                <button type="submit" :disabled="loading"
                        class="mt-8 w-full btn-brand px-10 py-4 text-white font-bold rounded-full transition text-lg uppercase tracking-wide disabled:opacity-50">
                    <span x-show="!loading" class="inline-flex items-center justify-center space-x-2">
                        <span><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                    <span x-show="loading" x-cloak><?= h($sh('form_sending', 'Sending...')) ?></span>
                </button>

                <p class="mt-4 text-center text-xs text-gray-400">No spam, ever. Unsubscribe anytime.</p>
            </form>
        </div>
    </div>
</section>

<!-- 2. Social Proof — Single centered line with dot separators -->
<section class="bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
        <p class="text-center text-sm text-gray-500">
            <?php if (!empty($socialProof)): ?>
                <?php $proofParts = []; foreach ($socialProof as $proof): ?>
                    <?php $proofParts[] = '<strong class="text-gray-900">' . h($proof['value'] ?? '') . '</strong> ' . h($proof['label'] ?? ''); ?>
                <?php endforeach; ?>
                <?= implode(' &middot; ', $proofParts) ?>
            <?php else: ?>
                <strong class="text-gray-900"><?= h($sh('default_proof_1', 'PDF Guide')) ?></strong> &middot;
                <strong class="text-gray-900"><?= h($sh('default_proof_2', '100% Free')) ?></strong> &middot;
                <strong class="text-gray-900"><?= h($sh('default_proof_3', 'Instant Access')) ?></strong>
            <?php endif; ?>
        </p>
    </div>
</section>

<!-- 3. Features — Numbered ordered list, no cards, no grid -->
<?php if (!empty($features)): ?>
<section class="bg-white py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-12"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
        <ol class="space-y-8 list-none counter-reset-custom">
            <?php foreach ($features as $i => $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                ?>
                <li class="flex items-start space-x-4">
                    <span class="flex-shrink-0 text-2xl font-bold text-gray-200 w-8"><?= $i + 1 ?>.</span>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= h($featureTitle) ?></h3>
                        <?php if ($featureDesc): ?>
                            <p class="text-gray-500 mt-1 leading-relaxed"><?= h($featureDesc) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters — Simple numbered list with <hr> between -->
<?php if (!empty($chapters)): ?>
<section class="bg-gray-50 py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-4"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
        <p class="text-center text-gray-500 mb-12"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
        <div>
            <?php foreach ($chapters as $cIndex => $chapter): ?>
                <?php if ($cIndex > 0): ?><hr class="border-gray-200 my-6"><?php endif; ?>
                <div class="flex items-start space-x-4">
                    <span class="text-3xl font-light text-gray-200 flex-shrink-0 w-10 text-right"><?= h($chapter['number'] ?? '') ?></span>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?= h($chapter['title'] ?? '') ?></h3>
                        <?php if (!empty($chapter['description'])): ?>
                            <p class="text-gray-500 mt-1 leading-relaxed"><?= h($chapter['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics — Vertical stack, one per row, giant centered number -->
<?php if (!empty($keyStatistics)): ?>
<section class="bg-white py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-14"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
        <div class="space-y-12">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center">
                    <div class="text-5xl sm:text-6xl font-bold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500 mt-2"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After — Two stacked lists under headings, not side-by-side -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="bg-gray-50 py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-12"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>

        <?php if (!empty($beforeAfter['before'])): ?>
            <h3 class="text-lg font-semibold text-red-600 mb-4"><?= h($sh('before_label', 'Before')) ?></h3>
            <ul class="space-y-3 mb-8">
                <?php foreach ($beforeAfter['before'] as $item): ?>
                    <li class="flex items-start space-x-3">
                        <span class="text-red-300 mt-0.5 flex-shrink-0">&times;</span>
                        <span class="text-gray-600 leading-relaxed"><?= h($item) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($beforeAfter['before']) && !empty($beforeAfter['after'])): ?>
            <hr class="border-gray-200 my-8">
        <?php endif; ?>

        <?php if (!empty($beforeAfter['after'])): ?>
            <h3 class="text-lg font-semibold text-green-600 mb-4"><?= h($sh('after_label', 'After')) ?></h3>
            <ul class="space-y-3">
                <?php foreach ($beforeAfter['after'] as $item): ?>
                    <li class="flex items-start space-x-3">
                        <span class="text-green-400 mt-0.5 flex-shrink-0">&#x2713;</span>
                        <span class="text-gray-600 leading-relaxed"><?= h($item) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Mid CTA — Subtle centered text with small button -->
<?php if (!empty($features) || !empty($chapters)): ?>
<section class="bg-white py-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <p class="text-gray-500 mb-4"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-brand inline-flex items-center justify-center px-10 py-4 text-white font-bold rounded-full transition text-lg uppercase tracking-wide">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 7. Target Audience — Checkmark + text list, no cards -->
<?php if (!empty($targetAudience)): ?>
<section class="bg-gray-50 py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-12"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
        <ul class="space-y-5">
            <?php foreach ($targetAudience as $persona): ?>
                <li class="flex items-start space-x-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div>
                        <span class="font-semibold text-gray-900"><?= h($persona['title'] ?? '') ?></span>
                        <?php if (!empty($persona['description'])): ?>
                            <span class="text-gray-500"> &mdash; <?= h($persona['description']) ?></span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<section class="bg-white py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center"><?= h($sh('author_title', 'About the Author')) ?></h3>
        <p class="text-gray-600 leading-relaxed text-center"><?= h($authorBio) ?></p>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials — Full-width blockquotes, no stars, no avatars -->
<?php if (!empty($testimonials)): ?>
<section class="bg-gray-50 py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-12"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
        <div>
            <?php foreach ($testimonials as $tIndex => $testimonial): ?>
                <?php if ($tIndex > 0): ?><hr class="border-gray-200 my-10"><?php endif; ?>
                <blockquote class="text-center">
                    <p class="text-xl sm:text-2xl text-gray-700 italic leading-relaxed">"<?= h($testimonial['quote'] ?? '') ?>"</p>
                    <footer class="mt-4 text-sm text-gray-500">
                        &mdash; <?= h($testimonial['name'] ?? '') ?><?php if (!empty($testimonial['title'])): ?>, <?= h($testimonial['title']) ?><?php endif; ?>
                    </footer>
                </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ — All visible, no accordion -->
<?php if (!empty($faqItems)): ?>
<section class="bg-white py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-12"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
        <div>
            <?php foreach ($faqItems as $fIndex => $faq): ?>
                <?php if ($fIndex > 0): ?><hr class="border-gray-200 my-8"><?php endif; ?>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2"><?= h($faq['question'] ?? '') ?></h3>
                    <p class="text-gray-500 leading-relaxed"><?= h($faq['answer'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Guarantee -->
<section class="bg-white py-14 lg:py-18">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($sh('guarantee_title', '100% Free, No Strings Attached')) ?></h3>
        <p class="text-gray-500 text-sm max-w-md mx-auto"><?= h($sh('guarantee_desc', 'This guide is completely free. No credit card required, no hidden fees. Just actionable insights delivered to your inbox.')) ?></p>
    </div>
</section>

<!-- 11. Bottom CTA — Subtle, not full-width brand banner -->
<section class="bg-gray-50 py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
        <p class="text-gray-500 mb-8 max-w-lg mx-auto"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-brand inline-flex items-center justify-center px-10 py-4 text-white font-bold rounded-full transition text-lg uppercase tracking-wide">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
