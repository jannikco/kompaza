<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<style>
    /* Angled clip-path dividers */
    .angle-down { clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
    .angle-up { clip-path: polygon(0 0, 100% 15%, 100% 100%, 0 100%); }
    .angle-both { clip-path: polygon(0 8%, 100% 0, 100% 92%, 0 100%); }

    /* Geometric accent shapes */
    .geo-accent {
        position: absolute;
        border: 3px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.12);
        pointer-events: none;
    }
    .geo-square {
        width: 80px; height: 80px;
        transform: rotate(15deg);
    }
    .geo-circle {
        width: 120px; height: 120px;
        border-radius: 50%;
    }
    .geo-diamond {
        width: 60px; height: 60px;
        transform: rotate(45deg);
    }

    /* Split panel accent background */
    .split-accent {
        background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.05);
        position: relative;
    }

    /* Section heading underline */
    .split-heading {
        position: relative;
        display: inline-block;
        padding-bottom: 16px;
    }
    .split-heading::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 48px;
        height: 4px;
        border-radius: 2px;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
    }
    .split-heading-center::after {
        left: 50%;
        transform: translateX(-50%);
    }

    /* Timeline for chapters */
    .timeline-line {
        position: absolute;
        left: 19px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2));
    }
    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        border: 3px solid white;
        box-shadow: 0 0 0 2px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    /* Feature horizontal card */
    .feature-h-card {
        background: #fff;
        border-left: 4px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .feature-h-card:hover {
        transform: translateX(4px);
        box-shadow: 0 10px 30px -8px rgba(0,0,0,0.12);
    }

    /* Testimonial alternate alignment */
    .testimonial-left { border-left: 4px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>); }
    .testimonial-right { border-right: 4px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>); }

    /* Hero form styling */
    .split-form-input {
        background: rgba(255,255,255,0.9) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: border-color 0.3s, box-shadow 0.3s !important;
    }
    .split-form-input:focus {
        border-color: <?= h($heroBgColor) ?> !important;
        box-shadow: 0 0 0 3px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15) !important;
        outline: none !important;
    }

    /* Stats banner */
    .stats-banner {
        background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);
    }

    /* Sticky mobile CTA bar */
    .sticky-cta-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 40;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        transform: translateY(100%);
        transition: transform 0.35s ease;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    }
    .sticky-cta-bar.visible {
        transform: translateY(0);
    }
    @media (min-width: 1024px) {
        .sticky-cta-bar { display: none !important; }
    }
</style>

<!-- 1. Hero Section -->
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);" id="hero">
    <div class="absolute inset-0 bg-gradient-to-br from-black/20 to-transparent"></div>

    <!-- Geometric accents -->
    <div class="geo-accent geo-square hidden lg:block" style="top: 10%; right: 8%; opacity: 0.5;"></div>
    <div class="geo-accent geo-circle hidden lg:block" style="bottom: 15%; left: 5%; opacity: 0.3;"></div>
    <div class="geo-accent geo-diamond hidden lg:block" style="top: 60%; right: 25%; opacity: 0.4;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <?php if ($coverImage || $heroImage): ?>
            <!-- Split layout: image left, form right -->
            <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
                <!-- Image side (60%) -->
                <div class="w-full lg:w-[60%]">
                    <?php if ($heroBadge): ?>
                        <div class="inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-semibold mb-6 backdrop-blur-sm">
                            <?= h($heroBadge) ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-white leading-tight mb-6">
                        <?= h($heroHeadline) ?>
                    </h1>

                    <?php if (!empty($heroAccent)): ?>
                        <p class="text-lg lg:text-xl text-white/70 font-medium mb-6"><?= h($heroAccent) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                        <p class="text-lg lg:text-xl text-white/80 leading-relaxed mb-8"><?= h($leadMagnet['hero_subheadline']) ?></p>
                    <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                        <p class="text-lg lg:text-xl text-white/80 leading-relaxed mb-8"><?= h($leadMagnet['subtitle']) ?></p>
                    <?php endif; ?>

                    <div class="rounded-2xl overflow-hidden shadow-2xl">
                        <img src="<?= h($coverImage ?: $heroImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-full h-auto">
                    </div>
                </div>

                <!-- Form side (40%) -->
                <div class="w-full lg:w-[40%]">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 ring-1 ring-white/20" id="signup-form" x-data="{ loading: false, error: '' }">
                        <h2 class="text-xl font-bold text-gray-900 mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
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
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                                    <input type="text" id="name" name="name" required class="split-form-input w-full px-4 py-3 text-sm" placeholder="John Smith">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                    <input type="email" id="email" name="email" required class="split-form-input w-full px-4 py-3 text-sm" placeholder="john@company.com">
                                </div>
                            </div>

                            <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                            <button type="submit" :disabled="loading"
                                    class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg shadow-lg transition hover:shadow-xl text-base disabled:opacity-50">
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
        <?php else: ?>
            <!-- Centered fallback: no hero image -->
            <div class="max-w-3xl mx-auto text-center">
                <?php if ($heroBadge): ?>
                    <div class="inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-semibold mb-6 backdrop-blur-sm">
                        <?= h($heroBadge) ?>
                    </div>
                <?php endif; ?>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-white leading-tight mb-6">
                    <?= h($heroHeadline) ?>
                </h1>

                <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                    <p class="text-lg lg:text-xl text-white/80 leading-relaxed mb-10"><?= h($leadMagnet['hero_subheadline']) ?></p>
                <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                    <p class="text-lg lg:text-xl text-white/80 leading-relaxed mb-10"><?= h($leadMagnet['subtitle']) ?></p>
                <?php endif; ?>

                <div class="max-w-lg mx-auto">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 ring-1 ring-white/20" id="signup-form" x-data="{ loading: false, error: '' }">
                        <h2 class="text-xl font-bold text-gray-900 mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
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
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                                    <input type="text" id="name" name="name" required class="split-form-input w-full px-4 py-3 text-sm" placeholder="John Smith">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                    <input type="email" id="email" name="email" required class="split-form-input w-full px-4 py-3 text-sm" placeholder="john@company.com">
                                </div>
                            </div>

                            <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                            <button type="submit" :disabled="loading"
                                    class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg shadow-lg transition hover:shadow-xl text-base disabled:opacity-50">
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
        <?php endif; ?>
    </div>
</section>

<!-- Angled divider: Hero -> Social Proof -->
<div class="relative h-16 lg:h-24" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="absolute inset-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.06); clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>

<!-- 2. Social Proof Bar -->
<section style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.06);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
        <div class="grid grid-cols-3 gap-4 text-center">
            <?php if (!empty($socialProof)): ?>
                <?php foreach ($socialProof as $proof): ?>
                    <div class="flex flex-col items-center space-y-2">
                        <?php if (!empty($proof['icon'])): ?>
                            <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                <span class="text-2xl"><?= h($proof['icon']) ?></span>
                            </div>
                        <?php endif; ?>
                        <span class="text-2xl sm:text-3xl font-extrabold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($proof['value'] ?? '') ?></span>
                        <span class="text-sm font-medium text-gray-600"><?= h($proof['label'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($sh('default_proof_1', 'PDF Guide')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($sh('default_proof_2', '100% Free')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($sh('default_proof_3', 'Instant Access')) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Features (Split: content left, accent right) -->
<?php if (!empty($features)): ?>
<div class="relative h-12 lg:h-16" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.06);">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-start gap-12 lg:gap-16">
            <!-- Content side (55%) -->
            <div class="w-full lg:w-[55%]">
                <h2 class="split-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-8"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php foreach ($features as $i => $feature): ?>
                        <?php
                            $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                            $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                            $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                        ?>
                        <div class="feature-h-card rounded-xl p-5 shadow-sm">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                    <?php if ($featureIcon): ?>
                                        <span class="text-brand text-lg"><?= h($featureIcon) ?></span>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1"><?= h($featureTitle) ?></h3>
                                    <?php if ($featureDesc): ?>
                                        <p class="text-gray-500 text-sm"><?= h($featureDesc) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Accent side (45%) -->
            <div class="hidden lg:block w-full lg:w-[45%]">
                <div class="split-accent rounded-2xl p-12 min-h-[400px] flex items-center justify-center relative overflow-hidden">
                    <div class="geo-accent geo-square" style="top: 10%; left: 10%;"></div>
                    <div class="geo-accent geo-circle" style="bottom: 10%; right: 10%;"></div>
                    <div class="geo-accent geo-diamond" style="top: 50%; left: 60%;"></div>
                    <div class="text-center">
                        <div class="text-6xl font-extrabold text-brand opacity-20"><?= count($features) ?>+</div>
                        <p class="text-sm font-medium text-gray-500 mt-2"><?= h($sh('features_count_label', 'Key Insights')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters as Timeline (Split: accent left, content right) -->
<?php if (!empty($chapters)): ?>
<div class="relative h-12 lg:h-16 bg-white">
    <div class="absolute inset-0" style="<?= $bgMedium ?> clip-path: polygon(0 0, 100% 40%, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28" style="<?= $bgMedium ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col-reverse lg:flex-row-reverse items-start gap-12 lg:gap-16">
            <!-- Content side (55%) - Timeline -->
            <div class="w-full lg:w-[55%]">
                <h2 class="split-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
                <p class="text-gray-500 mb-10"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>

                <div class="relative pl-10">
                    <div class="timeline-line"></div>
                    <div class="space-y-6">
                        <?php foreach ($chapters as $chapter): ?>
                            <div class="flex items-start space-x-4 relative">
                                <div class="timeline-dot mt-1.5 absolute left-[-27px]"></div>
                                <div class="bg-white rounded-xl p-5 border border-gray-200 w-full transition hover:shadow-md">
                                    <div class="flex items-center space-x-3 mb-1">
                                        <span class="text-xs font-bold text-brand uppercase tracking-wider"><?= h($chapter['number'] ?? '') ?></span>
                                    </div>
                                    <h3 class="font-semibold text-gray-900"><?= h($chapter['title'] ?? '') ?></h3>
                                    <?php if (!empty($chapter['description'])): ?>
                                        <p class="text-gray-500 text-sm mt-1"><?= h($chapter['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Accent side (45%) -->
            <div class="hidden lg:block w-full lg:w-[45%]">
                <div class="split-accent rounded-2xl p-12 min-h-[400px] flex items-center justify-center relative overflow-hidden">
                    <div class="geo-accent geo-circle" style="top: 8%; right: 15%;"></div>
                    <div class="geo-accent geo-square" style="bottom: 15%; left: 12%;"></div>
                    <div class="text-center">
                        <div class="text-6xl font-extrabold text-brand opacity-20"><?= count($chapters) ?></div>
                        <p class="text-sm font-medium text-gray-500 mt-2"><?= h($sh('chapters_count_label', 'Chapters')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics - Full-width brand banner -->
<?php if (!empty($keyStatistics)): ?>
<div class="relative h-12 lg:h-16" style="<?= !empty($chapters) ? $bgMedium : 'background: white;' ?>">
    <div class="absolute inset-0 stats-banner" style="clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>
<section class="stats-banner py-20 lg:py-28 relative overflow-hidden">
    <!-- Geometric accents on banner -->
    <div class="geo-accent geo-square hidden lg:block" style="top: 15%; left: 5%; border-color: rgba(255,255,255,0.15);"></div>
    <div class="geo-accent geo-circle hidden lg:block" style="bottom: 10%; right: 8%; border-color: rgba(255,255,255,0.1);"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="split-heading split-heading-center text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white text-center mb-14" style="padding-bottom: 16px;">
            <?= h($sh('stats_title', 'By the Numbers')) ?>
            <span class="block" style="margin-top: 16px; width: 48px; height: 4px; border-radius: 2px; background: rgba(255,255,255,0.5); margin-left: auto; margin-right: auto;"></span>
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-<?= min(count($keyStatistics), 4) ?> gap-6 max-w-4xl mx-auto">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center p-6 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 transition hover:bg-white/15">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="text-3xl mb-2"><?= h($stat['icon']) ?></div>
                    <?php endif; ?>
                    <div class="text-3xl sm:text-4xl font-extrabold text-white mb-1"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-white/70"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<div class="relative h-12 lg:h-16 stats-banner">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<?php endif; ?>

<!-- 6. Before/After (Split: content left, accent right) -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-start gap-12 lg:gap-16">
            <!-- Content side (60%) -->
            <div class="w-full lg:w-[60%]">
                <h2 class="split-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-10"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <?php if (!empty($beforeAfter['before'])): ?>
                        <div class="bg-white rounded-xl p-7 border border-red-100 shadow-sm">
                            <div class="flex items-center space-x-2 mb-6">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <h3 class="font-semibold text-red-700 text-lg"><?= h($sh('before_label', 'Before')) ?></h3>
                            </div>
                            <ul class="space-y-3">
                                <?php foreach ($beforeAfter['before'] as $item): ?>
                                    <li class="flex items-start space-x-3">
                                        <span class="text-red-400 mt-0.5 flex-shrink-0">&#x2717;</span>
                                        <span class="text-gray-600 text-sm"><?= h($item) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($beforeAfter['after'])): ?>
                        <div class="bg-white rounded-xl p-7 border border-green-100 shadow-sm">
                            <div class="flex items-center space-x-2 mb-6">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <h3 class="font-semibold text-green-700 text-lg"><?= h($sh('after_label', 'After')) ?></h3>
                            </div>
                            <ul class="space-y-3">
                                <?php foreach ($beforeAfter['after'] as $item): ?>
                                    <li class="flex items-start space-x-3">
                                        <span class="text-green-400 mt-0.5 flex-shrink-0">&#x2713;</span>
                                        <span class="text-gray-600 text-sm"><?= h($item) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Accent side (40%) -->
            <div class="hidden lg:block w-full lg:w-[40%]">
                <div class="split-accent rounded-2xl p-12 min-h-[350px] flex items-center justify-center relative overflow-hidden">
                    <div class="geo-accent geo-diamond" style="top: 15%; right: 20%;"></div>
                    <div class="geo-accent geo-square" style="bottom: 20%; left: 15%;"></div>
                    <div class="text-center">
                        <svg class="w-20 h-20 mx-auto text-brand opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA Banner -->
<?php
$showMidCta = !empty($chapters) || !empty($beforeAfter);
if ($showMidCta): ?>
<div class="relative h-12 lg:h-16 bg-white">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>); clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<section class="py-16 lg:py-20 relative overflow-hidden" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="geo-accent geo-circle hidden lg:block" style="top: 10%; right: 10%; border-color: rgba(255,255,255,0.1);"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
        <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-900 font-semibold rounded-lg shadow-lg transition hover:shadow-xl hover:bg-gray-50 text-base">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>
<div class="relative h-12 lg:h-16" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>
<?php endif; ?>

<!-- 7. Target Audience (Split: accent left, content right) -->
<?php if (!empty($targetAudience)): ?>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col-reverse lg:flex-row-reverse items-start gap-12 lg:gap-16">
            <!-- Content side (55%) -->
            <div class="w-full lg:w-[55%]">
                <h2 class="split-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-10"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
                <div class="space-y-5">
                    <?php foreach ($targetAudience as $persona): ?>
                        <div class="flex items-start space-x-4 bg-white rounded-xl p-5 border border-gray-200 shadow-sm transition hover:shadow-md">
                            <?php if (!empty($persona['icon'])): ?>
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                    <span class="text-2xl"><?= h($persona['icon']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1"><?= h($persona['title'] ?? '') ?></h3>
                                <p class="text-gray-500 text-sm"><?= h($persona['description'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Accent side (45%) -->
            <div class="hidden lg:block w-full lg:w-[45%]">
                <div class="split-accent rounded-2xl p-12 min-h-[350px] flex items-center justify-center relative overflow-hidden">
                    <div class="geo-accent geo-square" style="top: 12%; right: 12%;"></div>
                    <div class="geo-accent geo-circle" style="bottom: 8%; left: 8%;"></div>
                    <div class="text-center">
                        <svg class="w-20 h-20 mx-auto text-brand opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio (Split: content left, accent right) -->
<?php if (!empty($authorBio)): ?>
<div class="relative h-12 lg:h-16 bg-white">
    <div class="absolute inset-0" style="<?= $bgLight ?> clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28" style="<?= $bgLight ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
            <!-- Content side (60%) -->
            <div class="w-full lg:w-[60%]">
                <div class="bg-white rounded-xl p-8 border border-gray-200 shadow-sm">
                    <div class="flex items-start space-x-4">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                            <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($sh('author_title', 'About the Author')) ?></h3>
                            <p class="text-gray-600 leading-relaxed"><?= h($authorBio) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Accent side (40%) -->
            <div class="hidden lg:block w-full lg:w-[40%]">
                <div class="split-accent rounded-2xl p-12 min-h-[200px] flex items-center justify-center relative overflow-hidden">
                    <div class="geo-accent geo-diamond" style="top: 20%; left: 25%;"></div>
                    <div class="geo-accent geo-square" style="bottom: 20%; right: 15%;"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials (Stacked, alternating alignment) -->
<?php if (!empty($testimonials)): ?>
<div class="relative h-12 lg:h-16" style="<?= !empty($authorBio) ? $bgLight : 'background: white;' ?>">
    <div class="absolute inset-0" style="<?= $bgMedium ?> clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28" style="<?= $bgMedium ?>">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="split-heading split-heading-center text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
        </div>
        <div class="space-y-8">
            <?php foreach ($testimonials as $tIndex => $testimonial): ?>
                <?php $isEven = $tIndex % 2 === 0; ?>
                <div class="flex <?= $isEven ? 'justify-start' : 'justify-end' ?>">
                    <div class="w-full lg:w-4/5 bg-white rounded-xl p-6 shadow-sm <?= $isEven ? 'testimonial-left' : 'testimonial-right' ?> transition hover:shadow-md">
                        <div class="flex space-x-0.5 mb-3">
                            <?php for ($s = 0; $s < 5; $s++): ?>
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-700 mb-4 italic">"<?= h($testimonial['quote'] ?? '') ?>"</p>
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
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ (Split: content right, accent left) -->
<?php if (!empty($faqItems)): ?>
<div class="relative h-12 lg:h-16" style="<?= !empty($testimonials) ? $bgMedium : 'background: white;' ?>">
    <div class="absolute inset-0 bg-white" style="clip-path: polygon(0 0, 100% 40%, 100% 100%, 0 100%);"></div>
</div>
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col-reverse lg:flex-row-reverse items-start gap-12 lg:gap-16">
            <!-- Content side (55%) -->
            <div class="w-full lg:w-[55%]">
                <h2 class="split-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-10"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
                <div class="space-y-4" x-data="{ openFaq: null }">
                    <?php foreach ($faqItems as $index => $faq): ?>
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                            <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
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
            <!-- Accent side (45%) -->
            <div class="hidden lg:block w-full lg:w-[45%]">
                <div class="split-accent rounded-2xl p-12 min-h-[350px] flex items-center justify-center relative overflow-hidden">
                    <div class="geo-accent geo-circle" style="top: 15%; left: 10%;"></div>
                    <div class="geo-accent geo-diamond" style="bottom: 15%; right: 15%;"></div>
                    <div class="text-center">
                        <svg class="w-20 h-20 mx-auto text-brand opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 11. Bottom CTA -->
<div class="relative h-12 lg:h-16 bg-white">
    <div class="absolute inset-0" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>); clip-path: polygon(0 0, 100% 60%, 100% 100%, 0 100%);"></div>
</div>
<section class="relative overflow-hidden py-20 lg:py-28" style="background: linear-gradient(135deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>);">
    <div class="geo-accent geo-square hidden lg:block" style="top: 15%; right: 8%; border-color: rgba(255,255,255,0.12);"></div>
    <div class="geo-accent geo-circle hidden lg:block" style="bottom: 10%; left: 5%; border-color: rgba(255,255,255,0.08);"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
            <?php if ($coverImage): ?>
                <div class="hidden lg:block flex-shrink-0">
                    <div class="rounded-2xl overflow-hidden shadow-2xl">
                        <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-48 h-auto">
                    </div>
                </div>
            <?php endif; ?>
            <div class="text-center lg:text-left">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
                <p class="text-white/70 mb-8 max-w-lg"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
                <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
                   class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg shadow-lg transition hover:shadow-xl text-base">
                    <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
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