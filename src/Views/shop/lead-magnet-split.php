<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<style>
    /* Angled section dividers */
    .angle-divider {
        position: relative;
        height: 80px;
    }
    .angle-divider::after {
        content: '';
        position: absolute;
        inset: 0;
        clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);
    }

    /* Hero diagonal clip */
    .hero-diagonal {
        clip-path: polygon(0 0, 85% 0, 100% 100%, 0 100%);
    }
    @media (max-width: 1023px) {
        .hero-diagonal { clip-path: none; }
    }

    /* Zigzag feature rows */
    .zigzag-row {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        transition: background 0.3s;
    }
    .zigzag-row:nth-child(odd) { border-left-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>); }
    .zigzag-row:nth-child(even) { border-right-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>); }
    .zigzag-row:hover { background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.03); }

    /* Center timeline */
    .timeline-center { position: relative; }
    .timeline-center::before {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1));
    }
    @media (max-width: 767px) {
        .timeline-center::before {
            left: 16px;
            transform: none;
        }
    }
    .timeline-dot {
        width: 14px; height: 14px;
        border-radius: 50%;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        border: 3px solid white;
        box-shadow: 0 0 0 2px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }

    /* Bento grid */
    .bento-grid {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 1rem;
    }
    @media (max-width: 767px) {
        .bento-grid { grid-template-columns: 1fr; }
    }

    /* Featured testimonial */
    .featured-testimonial {
        background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.02));
        border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);
    }

    /* Scroll strip */
    .scroll-strip { -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .scroll-strip::-webkit-scrollbar { display: none; }

    /* Form input */
    .split-input {
        background: rgba(255,255,255,0.9) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: border-color 0.3s, box-shadow 0.3s !important;
    }
    .split-input:focus {
        border-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>) !important;
        box-shadow: 0 0 0 3px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15) !important;
        outline: none !important;
    }

    /* Sticky mobile CTA */
    .sticky-cta-bar {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 40;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        transform: translateY(100%);
        transition: transform 0.35s ease;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    }
    .sticky-cta-bar.visible { transform: translateY(0); }
    @media (min-width: 1024px) { .sticky-cta-bar { display: none !important; } }
</style>

<!-- 1. Hero — True 50/50 split screen -->
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);" id="hero">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row min-h-[600px] lg:min-h-[650px]">
            <!-- Left half: Brand bg with text -->
            <div class="w-full lg:w-1/2 flex items-center relative z-10 px-6 sm:px-10 lg:px-12 py-16 lg:py-20">
                <div class="max-w-xl">
                    <?php if ($heroBadge): ?>
                        <div class="inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-semibold mb-6 backdrop-blur-sm">
                            <?= h($heroBadge) ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-6">
                        <?= h($heroHeadline) ?>
                    </h1>

                    <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                        <p class="text-lg text-white/80 leading-relaxed mb-8"><?= h($leadMagnet['hero_subheadline']) ?></p>
                    <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                        <p class="text-lg text-white/80 leading-relaxed mb-8"><?= h($leadMagnet['subtitle']) ?></p>
                    <?php endif; ?>

                    <?php if ($coverImage): ?>
                        <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-48 h-auto rounded-xl shadow-2xl hidden lg:block">
                    <?php endif; ?>

                    <!-- Inline social proof -->
                    <div class="mt-8 text-white/60 text-sm">
                        <?php if (!empty($socialProof)): ?>
                            <?php $parts = []; foreach ($socialProof as $proof): ?>
                                <?php $parts[] = '<strong class="text-white">' . h($proof['value'] ?? '') . '</strong> ' . h($proof['label'] ?? ''); ?>
                            <?php endforeach; ?>
                            <?= implode(' <span class="text-white/30 mx-1">|</span> ', $parts) ?>
                        <?php else: ?>
                            <strong class="text-white"><?= h($sh('default_proof_1', 'PDF Guide')) ?></strong>
                            <span class="text-white/30 mx-1">|</span>
                            <strong class="text-white"><?= h($sh('default_proof_2', '100% Free')) ?></strong>
                            <span class="text-white/30 mx-1">|</span>
                            <strong class="text-white"><?= h($sh('default_proof_3', 'Instant Access')) ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right half: White bg with form -->
            <div class="w-full lg:w-1/2 bg-white flex items-center px-6 sm:px-10 lg:px-12 py-12 lg:py-20">
                <div class="w-full max-w-md mx-auto" id="signup-form" x-data="{ loading: false, error: '' }">
                    <h2 class="text-xl font-bold text-gray-900 mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
                    <p class="text-gray-500 text-sm mb-6"><?= h($sh('form_subtitle', 'Enter your details below and we\'ll send it straight to your inbox.')) ?></p>

                    <?php if ($coverImage): ?>
                        <div class="flex justify-center mb-6 lg:hidden">
                            <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-36 h-auto rounded-lg shadow-lg">
                        </div>
                    <?php endif; ?>

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
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                                <input type="text" id="name" name="name" required class="split-input w-full px-4 py-3 text-sm" placeholder="John Smith">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                <input type="email" id="email" name="email" required class="split-input w-full px-4 py-3 text-sm" placeholder="john@company.com">
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                        <button type="submit" :disabled="loading"
                                class="mt-6 w-full btn-brand px-10 py-4 text-white font-bold rounded-full shadow-lg transition hover:shadow-xl text-lg uppercase tracking-wide disabled:opacity-50">
                            <span x-show="!loading"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                            <svg x-show="!loading" class="inline-block w-5 h-5 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
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
    </div>
</section>

<!-- Angled divider -->
<div class="relative h-16 lg:h-20" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>

<!-- 3. Features — Full-width zigzag rows -->
<?php if (!empty($features)): ?>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 text-center mb-14"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
        <div class="space-y-4 max-w-5xl mx-auto">
            <?php foreach ($features as $i => $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                    $isOdd = $i % 2 === 0;
                ?>
                <div class="zigzag-row flex items-center gap-6 p-6 rounded-xl <?= $isOdd ? 'flex-row' : 'flex-row-reverse' ?>">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
                        <?php if ($featureIcon): ?>
                            <span class="text-brand text-xl"><?= h($featureIcon) ?></span>
                        <?php else: ?>
                            <span class="text-xl font-extrabold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= $i + 1 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="<?= $isOdd ? 'text-left' : 'text-right' ?> flex-1">
                        <h3 class="font-bold text-gray-900 text-lg"><?= h($featureTitle) ?></h3>
                        <?php if ($featureDesc): ?>
                            <p class="text-gray-500 mt-1"><?= h($featureDesc) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters — Center-line vertical timeline, alternating L/R -->
<?php if (!empty($chapters)): ?>
<div class="relative h-16 lg:h-20 bg-white">
    <div class="absolute inset-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.04); clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.04);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 text-center mb-4"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
        <p class="text-center text-gray-500 mb-14"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>

        <div class="timeline-center">
            <div class="space-y-8">
                <?php foreach ($chapters as $cIndex => $chapter): ?>
                    <?php $isLeft = $cIndex % 2 === 0; ?>
                    <!-- Desktop: alternate L/R -->
                    <div class="hidden md:flex items-start <?= $isLeft ? 'flex-row' : 'flex-row-reverse' ?>">
                        <div class="w-[calc(50%-20px)] <?= $isLeft ? 'text-right pr-6' : 'text-left pl-6' ?>">
                            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm inline-block <?= $isLeft ? 'ml-auto' : 'mr-auto' ?> max-w-md">
                                <span class="text-xs font-bold uppercase tracking-wider" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($chapter['number'] ?? '') ?></span>
                                <h3 class="font-semibold text-gray-900 mt-1"><?= h($chapter['title'] ?? '') ?></h3>
                                <?php if (!empty($chapter['description'])): ?>
                                    <p class="text-gray-500 text-sm mt-1"><?= h($chapter['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center justify-center w-10 flex-shrink-0">
                            <div class="timeline-dot"></div>
                        </div>
                        <div class="w-[calc(50%-20px)]"></div>
                    </div>
                    <!-- Mobile: left-aligned -->
                    <div class="md:hidden flex items-start gap-4">
                        <div class="flex flex-col items-center flex-shrink-0" style="padding-left: 2px;">
                            <div class="timeline-dot"></div>
                        </div>
                        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex-1">
                            <span class="text-xs font-bold uppercase tracking-wider" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($chapter['number'] ?? '') ?></span>
                            <h3 class="font-semibold text-gray-900 mt-1"><?= h($chapter['title'] ?? '') ?></h3>
                            <?php if (!empty($chapter['description'])): ?>
                                <p class="text-gray-500 text-sm mt-1"><?= h($chapter['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics — Oversized stacked numbers, one per row -->
<?php if (!empty($keyStatistics)): ?>
<div class="relative h-16 lg:h-20" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.04);">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>); clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-14"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
        <div class="space-y-10 max-w-3xl mx-auto">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center">
                    <div class="text-6xl sm:text-7xl lg:text-8xl font-black text-white/90 leading-none"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-white/60 mt-2 uppercase tracking-wider"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<div class="relative h-16 lg:h-20" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<?php endif; ?>

<!-- 6. Before/After — Full-bleed 50/50 split with color backgrounds -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 text-center mb-14"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 rounded-2xl overflow-hidden max-w-5xl mx-auto">
            <?php if (!empty($beforeAfter['before'])): ?>
                <div class="p-8 sm:p-10" style="background: rgba(239,68,68,0.05);">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="font-bold text-red-700 text-lg"><?= h($sh('before_label', 'Before')) ?></h3>
                    </div>
                    <ul class="space-y-3">
                        <?php foreach ($beforeAfter['before'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-red-400 mt-0.5 flex-shrink-0">&#x2717;</span>
                                <span class="text-gray-700"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($beforeAfter['after'])): ?>
                <div class="p-8 sm:p-10" style="background: rgba(34,197,94,0.05);">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="font-bold text-green-700 text-lg"><?= h($sh('after_label', 'After')) ?></h3>
                    </div>
                    <ul class="space-y-3">
                        <?php foreach ($beforeAfter['after'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-green-400 mt-0.5 flex-shrink-0">&#x2713;</span>
                                <span class="text-gray-700"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA -->
<?php if (!empty($beforeAfter) || !empty($chapters)): ?>
<div class="relative h-16 lg:h-20 bg-white">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>); clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<section class="py-16 lg:py-20" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full shadow-lg transition hover:shadow-xl hover:bg-gray-50 text-lg uppercase tracking-wide">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            <svg class="w-5 h-5 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>
<div class="relative h-16 lg:h-20" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>
<?php endif; ?>

<!-- 7. Target Audience — Asymmetric bento grid -->
<?php if (!empty($targetAudience)): ?>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 text-center mb-14"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
        <?php if (count($targetAudience) >= 3): ?>
            <div class="bento-grid max-w-5xl mx-auto">
                <!-- Large card -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm flex flex-col justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.03);">
                    <?php if (!empty($targetAudience[0]['icon'])): ?>
                        <div class="text-4xl mb-4"><?= h($targetAudience[0]['icon']) ?></div>
                    <?php endif; ?>
                    <h3 class="font-bold text-gray-900 text-xl mb-2"><?= h($targetAudience[0]['title'] ?? '') ?></h3>
                    <p class="text-gray-500"><?= h($targetAudience[0]['description'] ?? '') ?></p>
                </div>
                <!-- Stacked smaller cards -->
                <div class="space-y-4">
                    <?php foreach (array_slice($targetAudience, 1) as $persona): ?>
                        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                            <div class="flex items-start gap-3">
                                <?php if (!empty($persona['icon'])): ?>
                                    <span class="text-2xl flex-shrink-0"><?= h($persona['icon']) ?></span>
                                <?php endif; ?>
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= h($persona['title'] ?? '') ?></h3>
                                    <p class="text-gray-500 text-sm mt-1"><?= h($persona['description'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-<?= count($targetAudience) ?> gap-6 max-w-4xl mx-auto">
                <?php foreach ($targetAudience as $persona): ?>
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm text-center">
                        <?php if (!empty($persona['icon'])): ?>
                            <div class="text-3xl mb-4"><?= h($persona['icon']) ?></div>
                        <?php endif; ?>
                        <h3 class="font-semibold text-gray-900 mb-2"><?= h($persona['title'] ?? '') ?></h3>
                        <p class="text-gray-500 text-sm"><?= h($persona['description'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<section class="py-20 lg:py-28" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.04);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl p-8 border border-gray-200 shadow-sm flex items-start space-x-4">
            <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($sh('author_title', 'About the Author')) ?></h3>
                <p class="text-gray-600 leading-relaxed"><?= h($authorBio) ?></p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials — First featured large, rest horizontal scroll -->
<?php if (!empty($testimonials)): ?>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 text-center mb-14"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>

        <!-- Featured first testimonial -->
        <div class="featured-testimonial rounded-2xl p-8 sm:p-10 max-w-3xl mx-auto mb-8">
            <p class="text-xl sm:text-2xl text-gray-700 italic leading-relaxed mb-6">"<?= h($testimonials[0]['quote'] ?? '') ?>"</p>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                    <span class="text-brand text-sm font-bold"><?= h(mb_substr($testimonials[0]['name'] ?? '?', 0, 1)) ?></span>
                </div>
                <div>
                    <p class="font-medium text-gray-900"><?= h($testimonials[0]['name'] ?? '') ?></p>
                    <?php if (!empty($testimonials[0]['title'])): ?>
                        <p class="text-sm text-gray-500"><?= h($testimonials[0]['title']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Remaining in horizontal scroll strip -->
        <?php if (count($testimonials) > 1): ?>
            <div class="scroll-strip flex gap-6 overflow-x-auto pb-4 -mx-4 px-4">
                <?php foreach (array_slice($testimonials, 1) as $testimonial): ?>
                    <div class="flex-shrink-0 w-[320px] bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <p class="text-gray-600 italic mb-4">"<?= h($testimonial['quote'] ?? '') ?>"</p>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
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
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ — Two-column on desktop: Q left, A right. Accordion on mobile. -->
<?php if (!empty($faqItems)): ?>
<section class="py-20 lg:py-28" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.04);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 text-center mb-14"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>

        <!-- Desktop: two-column -->
        <div class="hidden lg:block max-w-5xl mx-auto" x-data="{ activeFaq: 0 }">
            <div class="grid grid-cols-2 gap-10">
                <!-- Questions list -->
                <div class="space-y-2">
                    <?php foreach ($faqItems as $index => $faq): ?>
                        <button type="button" @click="activeFaq = <?= $index ?>"
                            class="w-full text-left px-5 py-4 rounded-xl transition font-medium"
                            :class="activeFaq === <?= $index ?> ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'">
                            <?= h($faq['question'] ?? '') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <!-- Answer display -->
                <div class="bg-white rounded-xl p-8 border border-gray-200 shadow-sm">
                    <?php foreach ($faqItems as $index => $faq): ?>
                        <div x-show="activeFaq === <?= $index ?>" x-transition>
                            <h3 class="font-bold text-gray-900 text-lg mb-4"><?= h($faq['question'] ?? '') ?></h3>
                            <p class="text-gray-500 leading-relaxed"><?= h($faq['answer'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Mobile: accordion -->
        <div class="lg:hidden max-w-2xl mx-auto space-y-3" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                    <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-5 py-4 text-left">
                        <span class="font-medium text-gray-900"><?= h($faq['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-3" :class="openFaq === <?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === <?= $index ?>" x-transition x-cloak>
                        <div class="px-5 pb-4 text-gray-500 text-sm leading-relaxed"><?= h($faq['answer'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Guarantee Section -->
<section class="py-14 lg:py-18">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-5" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
            <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= h($sh('guarantee_title', '100% Free, No Strings Attached')) ?></h3>
        <p class="text-gray-500 text-sm max-w-md mx-auto"><?= h($sh('guarantee_desc', 'This guide is completely free. No credit card required, no hidden fees. Just actionable insights delivered to your inbox.')) ?></p>
    </div>
</section>

<!-- 11. Bottom CTA -->
<div class="relative h-16 lg:h-20 bg-white">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>); clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<section class="relative overflow-hidden py-20 lg:py-28" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
            <?php if ($coverImage): ?>
                <div class="hidden lg:block flex-shrink-0">
                    <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-48 h-auto rounded-2xl shadow-2xl">
                </div>
            <?php endif; ?>
            <div class="text-center lg:text-left">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
                <p class="text-white/70 mb-8 max-w-lg"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
                <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
                   class="btn-brand inline-flex items-center justify-center px-10 py-4 text-white font-bold rounded-full shadow-lg transition hover:shadow-xl text-lg uppercase tracking-wide">
                    <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
                    <svg class="w-5 h-5 ml-2 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Sticky Mobile CTA Bar -->
<div id="sticky-cta" class="sticky-cta-bar lg:hidden">
    <div class="px-4 py-3 flex items-center justify-between">
        <span class="text-white font-semibold text-sm truncate mr-3"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="flex-shrink-0 bg-white text-gray-900 font-semibold text-sm px-5 py-2.5 rounded-lg shadow transition hover:bg-gray-100">
            Get It Now
        </a>
    </div>
</div>

<script>
(function() {
    var signupForm = document.getElementById('signup-form'), stickyCta = document.getElementById('sticky-cta');
    if (!signupForm || !stickyCta || !('IntersectionObserver' in window)) return;
    if (window.innerWidth >= 1024) return;
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) { entry.isIntersecting ? stickyCta.classList.remove('visible') : stickyCta.classList.add('visible'); });
    }, { threshold: 0 });
    observer.observe(signupForm);
})();
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
