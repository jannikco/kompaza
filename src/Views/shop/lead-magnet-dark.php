<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<style>
    /* Dark theme base */
    .dark-base { background: #0f172a; }
    .dark-alt { background: #1e293b; }

    /* Glowing section divider */
    .glow-divider {
        height: 1px;
        background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);
        box-shadow: 0 0 10px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3), 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);
    }

    /* Frosted glass card */
    .glass-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.4), 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);
    }

    /* Feature card with brand left border */
    .dark-feature-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-left: 4px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .dark-feature-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.4), 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);
    }

    /* Testimonial card with brand top border */
    .dark-testimonial-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-top: 3px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .dark-testimonial-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.4);
    }

    /* Glow accent text */
    .glow-accent {
        color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        text-shadow: 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.5);
    }

    /* Stats number glow */
    .stat-glow {
        color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        text-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4), 0 0 10px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);
    }

    /* Form card glass on dark */
    .dark-form-glass {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.15);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.5), 0 0 40px -15px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);
        transition: box-shadow 0.35s ease;
    }
    .dark-form-glass:focus-within {
        box-shadow: 0 30px 80px -16px rgba(0,0,0,0.6), 0 0 60px -10px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4);
    }

    /* Dark input styling */
    .dark-input {
        background: rgba(255,255,255,0.1) !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        color: #fff !important;
        transition: border-color 0.3s, background 0.3s, box-shadow 0.3s !important;
    }
    .dark-input::placeholder { color: #6b7280 !important; }
    .dark-input:focus {
        background: rgba(255,255,255,0.15) !important;
        border-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>) !important;
        box-shadow: 0 0 0 3px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2) !important;
        outline: none !important;
    }

    /* CTA button with luminous glow */
    .btn-glow {
        box-shadow: 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.5), 0 4px 15px rgba(0,0,0,0.3);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-glow:hover {
        transform: scale(1.03);
        box-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.6), 0 8px 25px rgba(0,0,0,0.4);
    }
    .btn-glow:active {
        transform: scale(0.98);
        box-shadow: 0 0 15px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4), 0 2px 8px rgba(0,0,0,0.3);
    }

    /* Floating orbs for hero */
    @keyframes floatOrb1 {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(30px, -20px); }
    }
    @keyframes floatOrb2 {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(-20px, 30px); }
    }

    /* Section heading decorative underline */
    .dark-section-heading {
        position: relative;
        display: inline-block;
        padding-bottom: 16px;
    }
    .dark-section-heading::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 48px;
        height: 4px;
        border-radius: 2px;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        box-shadow: 0 0 10px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.5);
    }

    /* Scroll-triggered reveal animation */
    .reveal {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.7s ease, transform 0.7s ease;
    }
    .reveal.revealed {
        opacity: 1;
        transform: translateY(0);
    }

    /* Staggered entrance animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-stagger-1 { animation: fadeInUp 0.6s ease-out 0.1s both; }
    .animate-stagger-2 { animation: fadeInUp 0.6s ease-out 0.3s both; }
    .animate-stagger-3 { animation: fadeInUp 0.6s ease-out 0.5s both; }
    .animate-stagger-4 { animation: fadeInUp 0.8s ease-out 0.7s both; }

    /* Sticky mobile CTA bar */
    .sticky-cta-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 40;
        background: #0f172a;
        border-top: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);
        box-shadow: 0 -4px 20px rgba(0,0,0,0.5), 0 -2px 10px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);
        transform: translateY(100%);
        transition: transform 0.35s ease;
    }
    .sticky-cta-bar.visible { transform: translateY(0); }
    @media (min-width: 1024px) {
        .sticky-cta-bar { display: none !important; }
    }

    /* Book mockup */
    .book-mockup { perspective: 1200px; }
    .book-mockup-inner {
        transform-style: preserve-3d;
        filter: drop-shadow(0 25px 50px rgba(0,0,0,0.6));
    }
    @keyframes bookFloat {
        0%, 100% { transform: translateY(0px) rotateY(-15deg); }
        50% { transform: translateY(-12px) rotateY(-15deg); }
    }
    .book-float {
        animation: bookFloat 6s ease-in-out infinite;
        transform-style: preserve-3d;
    }
    .book-glow-ring {
        position: absolute;
        width: 150%; height: 150%;
        top: -25%; left: -25%;
        background: radial-gradient(circle, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15) 0%, transparent 70%);
        pointer-events: none;
    }
</style>

<!-- 1. Hero Section -->
<section class="relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08) 100%);" id="hero">
    <!-- Decorative orbs -->
    <div class="absolute top-10 right-20 w-72 h-72 rounded-full pointer-events-none opacity-30 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2); animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="absolute bottom-10 left-10 w-56 h-56 rounded-full pointer-events-none opacity-20 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15); animation: floatOrb2 25s ease-in-out infinite;"></div>
    <!-- Subtle grid pattern -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%2040%2040%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200h40v40H0z%22%20fill%3D%22none%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%2F%3E%3C%2Fsvg%3E'); background-size: 40px 40px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <!-- Left: Text content -->
            <div>
                <?php if ($heroBadge): ?>
                    <div class="animate-stagger-1 inline-block px-4 py-1.5 rounded-full text-sm font-semibold mb-6" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15); color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>); border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);">
                        <?= h($heroBadge) ?>
                    </div>
                <?php endif; ?>

                <?php if ($coverImage): ?>
                    <!-- Desktop: book + headline side by side -->
                    <div class="hidden lg:flex items-start gap-8 animate-stagger-2">
                        <div class="book-mockup flex-shrink-0 relative">
                            <div class="book-glow-ring"></div>
                            <div class="book-mockup-inner book-float rounded-lg overflow-hidden">
                                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-40 h-auto rounded-lg" style="box-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);">
                            </div>
                        </div>
                        <div>
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                                <?php if ($heroAccent): ?>
                                    <?= str_replace(h($heroAccent), '<span class="glow-accent">' . h($heroAccent) . '</span>', h($heroHeadline)) ?>
                                <?php else: ?>
                                    <?= h($heroHeadline) ?>
                                <?php endif; ?>
                            </h1>
                            <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                                <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-gray-300 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                            <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                                <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-gray-300 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden lg:block animate-stagger-2">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                            <?php if ($heroAccent): ?>
                                <?= str_replace(h($heroAccent), '<span class="glow-accent">' . h($heroAccent) . '</span>', h($heroHeadline)) ?>
                            <?php else: ?>
                                <?= h($heroHeadline) ?>
                            <?php endif; ?>
                        </h1>
                        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                            <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-gray-300 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                            <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-gray-300 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Mobile layout -->
                <div class="lg:hidden">
                    <?php if ($coverImage): ?>
                        <div class="flex justify-center mb-6 animate-stagger-1">
                            <div class="book-mockup relative">
                                <div class="book-glow-ring"></div>
                                <div class="book-mockup-inner book-float rounded-lg overflow-hidden">
                                    <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-44 h-auto rounded-lg" style="box-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <h1 class="animate-stagger-2 text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                        <?php if ($heroAccent): ?>
                            <?= str_replace(h($heroAccent), '<span class="glow-accent">' . h($heroAccent) . '</span>', h($heroHeadline)) ?>
                        <?php else: ?>
                            <?= h($heroHeadline) ?>
                        <?php endif; ?>
                    </h1>
                    <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                        <p class="animate-stagger-3 mt-4 text-lg text-gray-300 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                    <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                        <p class="animate-stagger-3 mt-4 text-lg text-gray-300 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Signup form -->
            <div class="flex justify-center lg:justify-end animate-stagger-4">
                <div class="dark-form-glass rounded-2xl p-8 w-full max-w-lg" id="signup-form" x-data="{ loading: false, error: '' }">
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.5);">J</div>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.7);">M</div>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">S</div>
                        </div>
                        <span class="text-sm text-gray-400"><?= h($sh('form_social_proof', 'Join 2,500+ readers')) ?></span>
                    </div>

                    <h2 class="text-xl font-bold text-white mb-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></h2>
                    <p class="text-gray-400 text-sm mb-6"><?= h($sh('form_subtitle', 'Enter your details below and we\'ll send it straight to your inbox.')) ?></p>

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
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-1"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                                <input type="text" id="name" name="name" required class="dark-input w-full px-4 py-3 rounded-lg text-sm" placeholder="John Smith">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                <input type="email" id="email" name="email" required class="dark-input w-full px-4 py-3 rounded-lg text-sm" placeholder="john@company.com">
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-900/30 border border-red-500/30 rounded-lg text-red-300 text-sm" x-text="error"></div>

                        <button type="submit" :disabled="loading"
                                class="btn-glow mt-6 w-full px-6 py-3.5 text-white font-semibold rounded-lg text-base disabled:opacity-50"
                                style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
                            <span x-show="!loading"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                            <span x-show="loading" x-cloak><?= h($sh('form_sending', 'Sending...')) ?></span>
                        </button>

                        <div class="mt-4 flex items-center justify-center space-x-4 text-xs text-gray-500">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Secure</span>
                            </span>
                            <span class="text-gray-600">|</span>
                            <span>No spam, ever</span>
                            <span class="text-gray-600">|</span>
                            <span>Unsubscribe anytime</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Glow Divider: Hero -> Social Proof -->
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>

<!-- 2. Social Proof Bar -->
<section class="dark-base">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
        <div class="reveal grid grid-cols-3 gap-4 text-center">
            <?php if (!empty($socialProof)): ?>
                <?php foreach ($socialProof as $proof): ?>
                    <div class="flex flex-col items-center space-y-2">
                        <?php if (!empty($proof['icon'])): ?>
                            <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15); box-shadow: 0 0 15px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                <span class="text-2xl"><?= h($proof['icon']) ?></span>
                            </div>
                        <?php endif; ?>
                        <span class="text-2xl sm:text-3xl font-extrabold stat-glow"><?= h($proof['value'] ?? '') ?></span>
                        <span class="text-sm font-medium text-gray-400"><?= h($proof['label'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                        <svg class="w-6 h-6" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold stat-glow"><?= h($sh('default_proof_1', 'PDF Guide')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                        <svg class="w-6 h-6" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold stat-glow"><?= h($sh('default_proof_2', '100% Free')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                        <svg class="w-6 h-6" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold stat-glow"><?= h($sh('default_proof_3', 'Instant Access')) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Features -->
<?php if (!empty($features)): ?>
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-alt">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php foreach ($features as $i => $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                ?>
                <div class="dark-feature-card rounded-xl p-6">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                        <?php if ($featureIcon): ?>
                            <span class="text-lg" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($featureIcon) ?></span>
                        <?php else: ?>
                            <svg class="w-5 h-5" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-semibold text-white mb-2"><?= h($featureTitle) ?></h3>
                    <?php if ($featureDesc): ?>
                        <p class="text-gray-400 text-sm"><?= h($featureDesc) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters -->
<?php if (!empty($chapters)): ?>
<div class="<?= !empty($features) ? 'dark-alt' : 'dark-base' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-base">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
            <p class="mt-6 text-gray-400"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
            <?php foreach ($chapters as $chapter): ?>
                <div class="glass-card flex items-start space-x-4 rounded-xl p-5">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                        <span class="font-bold text-sm" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($chapter['number'] ?? '') ?></span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white"><?= h($chapter['title'] ?? '') ?></h3>
                        <?php if (!empty($chapter['description'])): ?>
                            <p class="text-gray-400 text-sm mt-1"><?= h($chapter['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA #1 -->
<?php if (!empty($chapters)): ?>
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-16 lg:py-20 relative overflow-hidden" style="background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.05)), #0f172a;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E'); background-size: 30px 30px;"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="reveal">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
            <p class="text-gray-400 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
            <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
               class="btn-glow inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg text-base"
               style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
                <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics -->
<?php if (!empty($keyStatistics)): ?>
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-alt">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-2 md:grid-cols-<?= min(count($keyStatistics), 4) ?> gap-6 max-w-3xl mx-auto">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="glass-card text-center p-6 rounded-xl">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="text-3xl mb-2"><?= h($stat['icon']) ?></div>
                    <?php endif; ?>
                    <div class="text-3xl sm:text-4xl font-extrabold stat-glow mb-1"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-400"><?= h($stat['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<div class="<?= !empty($keyStatistics) ? 'dark-alt' : 'dark-base' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-base">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <?php if (!empty($beforeAfter['before'])): ?>
                <div class="rounded-xl p-8" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.2);">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-red-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="font-semibold text-red-400 text-lg"><?= h($sh('before_label', 'Before')) ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($beforeAfter['before'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-red-400 mt-0.5 flex-shrink-0">&#x2717;</span>
                                <span class="text-gray-300"><?= h($item) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($beforeAfter['after'])): ?>
                <div class="rounded-xl p-8" style="background: rgba(34,197,94,0.05); border: 1px solid rgba(34,197,94,0.2);">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="font-semibold text-green-400 text-lg"><?= h($sh('after_label', 'After')) ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <?php foreach ($beforeAfter['after'] as $item): ?>
                            <li class="flex items-start space-x-3">
                                <span class="text-green-400 mt-0.5 flex-shrink-0">&#x2713;</span>
                                <span class="text-gray-300"><?= h($item) ?></span>
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
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-alt">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php foreach ($targetAudience as $persona): ?>
                <div class="glass-card rounded-xl p-6 text-center">
                    <?php if (!empty($persona['icon'])): ?>
                        <div class="text-4xl mb-4"><?= h($persona['icon']) ?></div>
                    <?php endif; ?>
                    <h3 class="font-semibold text-white mb-2"><?= h($persona['title'] ?? '') ?></h3>
                    <p class="text-gray-400 text-sm"><?= h($persona['description'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA #2 -->
<?php if (!empty($targetAudience)): ?>
<div class="dark-alt"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-16 lg:py-20 relative overflow-hidden" style="background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.05)), #0f172a;">
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E'); background-size: 30px 30px;"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="reveal">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_2', 'Ready to Take the Next Step?')) ?></h2>
            <p class="text-gray-400 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_2_sub', 'Join thousands of others who have already downloaded this guide.')) ?></p>
            <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
               class="btn-glow inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg text-base"
               style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
                <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-alt">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal glass-card rounded-xl p-8">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                    <svg class="w-7 h-7" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2"><?= h($sh('author_title', 'About the Author')) ?></h3>
                    <p class="text-gray-300 leading-relaxed"><?= h($authorBio) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials -->
<?php if (!empty($testimonials)): ?>
<div class="<?= !empty($authorBio) ? 'dark-alt' : 'dark-base' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-base">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-<?= min(count($testimonials), 3) ?> gap-8 max-w-4xl mx-auto">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="dark-testimonial-card rounded-xl p-6">
                    <div class="flex space-x-0.5 mb-4">
                        <?php for ($s = 0; $s < 5; $s++): ?>
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-300 mb-4 italic">"<?= h($testimonial['quote'] ?? '') ?>"</p>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);">
                            <span class="text-sm font-bold" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h(mb_substr($testimonial['name'] ?? '?', 0, 1)) ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white"><?= h($testimonial['name'] ?? '') ?></p>
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

<!-- 10. FAQ -->
<?php if (!empty($faqItems)): ?>
<div class="dark-base"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-20 lg:py-28 dark-alt">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="dark-section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
        </div>
        <div class="reveal space-y-4" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="rounded-xl overflow-hidden" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-6 py-4 text-left transition" style="color: #fff;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                        <span class="font-medium text-white"><?= h($faq['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-4" :class="openFaq === <?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === <?= $index ?>" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" x-cloak>
                        <div class="px-6 pb-4 text-gray-400 text-sm leading-relaxed"><?= h($faq['answer'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 11. Bottom CTA -->
<div class="<?= !empty($faqItems) ? 'dark-alt' : 'dark-base' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="relative overflow-hidden py-20 lg:py-28" style="background: linear-gradient(135deg, #0f172a 0%, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1) 50%, #0f172a 100%);">
    <div class="absolute top-5 right-16 w-48 h-48 rounded-full pointer-events-none opacity-20 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3); animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="absolute bottom-5 left-8 w-36 h-36 rounded-full pointer-events-none opacity-15 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3); animation: floatOrb2 25s ease-in-out infinite;"></div>
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col lg:flex-row items-center justify-center lg:space-x-12 text-center lg:text-left">
            <?php if ($coverImage): ?>
                <div class="hidden lg:block flex-shrink-0 mb-8 lg:mb-0">
                    <div class="book-mockup relative">
                        <div class="book-glow-ring"></div>
                        <div class="book-mockup-inner book-float rounded-lg overflow-hidden">
                            <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-36 h-auto rounded-lg" style="box-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
                <p class="text-gray-400 mb-8 max-w-lg"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
                <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
                   class="btn-glow inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg text-base"
                   style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
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
           class="btn-glow flex-shrink-0 text-white font-semibold text-sm px-5 py-2.5 rounded-lg"
           style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
            Get It Now
        </a>
    </div>
</div>

<script>
(function() {
    // Scroll-triggered reveal animations
    var reveals = document.querySelectorAll('.reveal');
    if (!reveals.length || !('IntersectionObserver' in window)) {
        reveals.forEach(function(el) { el.classList.add('revealed'); });
        return;
    }
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '-60px 0px' });
    reveals.forEach(function(el) { observer.observe(el); });
})();

(function() {
    // Sticky mobile CTA bar
    var signupForm = document.getElementById('signup-form');
    var stickyCta = document.getElementById('sticky-cta');
    if (!signupForm || !stickyCta || !('IntersectionObserver' in window)) return;
    if (window.innerWidth >= 1024) return;
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            entry.isIntersecting ? stickyCta.classList.remove('visible') : stickyCta.classList.add('visible');
        });
    }, { threshold: 0 });
    observer.observe(signupForm);
})();
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>