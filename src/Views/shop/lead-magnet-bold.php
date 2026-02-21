<?php
require __DIR__ . '/lead-magnet-setup.php';

// Bold template accent helper: wraps accent words in gradient-highlighted span
function heroHeadlineWithAccent($headline, $accent) {
    if (empty($accent)) return h($headline);
    return str_replace(h($accent), '<span class="text-brand-gradient accent-blur-in">' . h($accent) . '</span>', h($headline));
}

ob_start();
?>

<style>
    /* Book mockup base */
    .book-mockup { perspective: 1200px; }
    .book-mockup-inner {
        transform-style: preserve-3d;
        filter: drop-shadow(0 25px 50px rgba(0,0,0,0.4));
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotateY(-15deg); }
        50% { transform: translateY(-15px) rotateY(-15deg); }
    }
    .book-float {
        animation: float 6s ease-in-out infinite;
        transform-style: preserve-3d;
    }
    .book-mockup:hover .book-float {
        animation-play-state: paused;
        transform: translateY(-5px) rotateY(-5deg);
        transition: transform 0.4s ease;
    }

    /* 3D book spine & page edges */
    .book-3d { position: relative; }
    .book-3d::before {
        content: '';
        position: absolute;
        top: 3%; left: 0;
        width: 10px; height: 94%;
        background: linear-gradient(to right, rgba(0,0,0,0.3), rgba(0,0,0,0.1));
        transform: rotateY(-60deg) translateX(-5px);
        transform-origin: left;
    }
    .book-3d::after {
        content: '';
        position: absolute;
        top: 2%; right: -6px;
        width: 6px; height: 96%;
        background: repeating-linear-gradient(to bottom, #f0f0f0 0px, #e8e8e8 1px, #f5f5f5 2px);
        border-radius: 0 1px 1px 0;
    }

    /* Animated gradient background */
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
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

    /* Soft radial glow behind book */
    @keyframes glowPulse {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.1); }
    }
    .book-glow {
        position: absolute;
        width: 200%; height: 200%;
        top: -50%; left: -50%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        animation: glowPulse 4s ease-in-out infinite;
        pointer-events: none;
    }

    /* CTA button shimmer + ripple */
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .btn-shimmer { position: relative; overflow: hidden; will-change: transform; }
    .btn-shimmer::after {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 3s ease-in-out infinite;
    }
    .btn-shimmer:active {
        transform: scale(0.97) translateY(1px) !important;
        box-shadow: 0 2px 8px rgba(99,102,241,0.5), 0 1px 2px rgba(0,0,0,0.2) !important;
    }
    .btn-shimmer .ripple {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        transform: scale(0);
        background: rgba(255,255,255,0.25);
        opacity: 0;
        transition: transform 0.5s, opacity 0.7s;
    }

    /* Enhanced glassmorphic form card */
    .form-glow {
        border: 1.5px solid rgba(255,255,255,0.18);
        box-shadow:
            0 25px 60px -12px rgba(0,0,0,0.25),
            0 0 40px -15px <?= h($heroBgColor) ?>40,
            0 0 0 4px rgba(99,102,241,0.08),
            0 2px 32px 2px rgba(125,211,252,0.12) inset;
        backdrop-filter: blur(12px) saturate(1.15);
        transition: box-shadow 0.35s cubic-bezier(.42,1.4,.42,1.0);
    }
    .form-glow:focus-within {
        box-shadow:
            0 40px 80px -16px rgba(30,64,175,0.23),
            0 0 60px -10px rgba(99,102,241,0.5),
            0 0 0 6px rgba(125,211,252,0.3),
            0 2px 48px 4px rgba(165,180,252,0.2) inset;
    }

    /* Hero form input styling */
    .hero-input {
        background: rgba(255,255,255,0.6) !important;
        border: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-radius: 8px 8px 0 0 !important;
        transition: border-color 0.3s, background 0.3s, box-shadow 0.3s !important;
    }
    .hero-input:focus {
        background: rgba(255,255,255,0.9) !important;
        border-bottom-color: <?= h($heroBgColor) ?> !important;
        box-shadow: 0 2px 0 0 <?= h($heroBgColor) ?> !important;
    }

    /* Gradient accent text for headline highlight */
    .text-brand-gradient {
        background: linear-gradient(90deg, #7dd3fc 20%, #a78bfa 50%, #7dd3fc 80%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: gradientText 4s ease-in-out infinite;
        filter: drop-shadow(0 2px 12px rgba(99,102,241,0.3));
    }
    @keyframes gradientText {
        0%, 100% { background-position: 0% center; }
        50% { background-position: 200% center; }
    }

    /* Headline accent blur-in entrance */
    @keyframes accentBlurIn {
        0% { opacity: 0; filter: blur(8px); }
        100% { opacity: 1; filter: blur(0); }
    }
    .accent-blur-in {
        animation: accentBlurIn 1s cubic-bezier(.6,1.5,.5,1.1) 0.8s both;
    }

    /* Hero headline text shadow for depth */
    .hero-headline {
        letter-spacing: -0.02em;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2), 0 1px 0 rgba(0,0,0,0.15);
    }

    /* Floating decorative orbs */
    @keyframes floatOrb1 {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(30px, -20px); }
    }
    @keyframes floatOrb2 {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(-20px, 30px); }
    }
    @keyframes floatOrb3 {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(-15px, -25px); }
    }

    /* Section heading decorative underline */
    .section-heading {
        position: relative;
        display: inline-block;
        padding-bottom: 16px;
    }
    .section-heading::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 48px;
        height: 4px;
        border-radius: 2px;
        background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
    }

    /* Wave divider */
    .wave-divider {
        display: block;
        width: 100%;
        height: 48px;
        line-height: 0;
    }
    @media (min-width: 1024px) {
        .wave-divider { height: 72px; }
    }
    .wave-divider svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    /* Feature card hover lift */
    .feature-card {
        background: #fff;
        border-left: 4px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        border-top: none;
        border-right: none;
        border-bottom: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15);
    }

    /* Testimonial card */
    .testimonial-card {
        border-top: 3px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .testimonial-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15);
    }

    /* Scroll-triggered reveal animation */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.7s ease, transform 0.7s ease;
    }
    .reveal.revealed {
        opacity: 1;
        transform: translateY(0);
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

    /* Mid-page CTA banner */
    .mid-cta-banner {
        position: relative;
        overflow: hidden;
    }
    .mid-cta-banner .cta-pattern {
        position: absolute;
        inset: 0;
        opacity: 0.04;
        background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E');
        background-size: 30px 30px;
    }
</style>

<!-- 1. Hero Section (Premium) -->
<section class="relative overflow-hidden" style="background: linear-gradient(-45deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>, <?= h($midCtaBg) ?>, <?= h($heroBgLighterHsl) ?>); background-size: 400% 400%; animation: gradientShift 15s ease infinite;" id="hero">
    <div class="absolute inset-0 bg-gradient-to-br from-black/30 to-transparent"></div>
    <div class="absolute inset-0 opacity-[0.04]">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E'); background-size: 30px 30px;"></div>
    </div>
    <div id="parallax-orb1" class="absolute top-10 right-20 w-64 h-64 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb1 20s ease-in-out infinite; transition: transform 0.15s ease-out;"></div>
    <div id="parallax-orb2" class="absolute bottom-10 left-10 w-48 h-48 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb2 25s ease-in-out infinite; transition: transform 0.15s ease-out;"></div>
    <div id="parallax-orb3" class="absolute top-1/2 left-1/3 w-40 h-40 rounded-full bg-white/[0.03] blur-3xl pointer-events-none" style="animation: floatOrb3 22s ease-in-out infinite; transition: transform 0.15s ease-out;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div id="hero-parallax" class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <div>
                <?php if ($heroBadge): ?>
                    <div class="animate-stagger-1 inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-semibold mb-5 backdrop-blur-sm">
                        <?= h($heroBadge) ?>
                    </div>
                <?php endif; ?>

                <?php if ($coverImage): ?>
                    <div class="hidden lg:flex items-start gap-8 animate-stagger-2">
                        <div class="book-mockup flex-shrink-0 relative" id="parallax-book">
                            <div class="book-glow"></div>
                            <div class="book-mockup-inner book-float book-3d rounded-lg overflow-hidden" style="transition: transform 0.15s ease-out;">
                                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-44 h-auto">
                            </div>
                        </div>
                        <div>
                            <h1 class="hero-headline text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                                <?= heroHeadlineWithAccent($heroHeadline, $heroAccent) ?>
                            </h1>
                            <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                                <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                            <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                                <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden lg:block animate-stagger-2">
                        <h1 class="hero-headline text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                            <?= heroHeadlineWithAccent($heroHeadline, $heroAccent) ?>
                        </h1>
                        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                            <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                            <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="lg:hidden">
                    <?php if ($coverImage): ?>
                        <div class="flex justify-center mb-6 animate-stagger-1">
                            <div class="book-mockup relative">
                                <div class="book-glow"></div>
                                <div class="book-mockup-inner book-float book-3d rounded-lg overflow-hidden">
                                    <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-48 h-auto">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <h1 class="hero-headline animate-stagger-2 text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                        <?= heroHeadlineWithAccent($heroHeadline, $heroAccent) ?>
                    </h1>
                    <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                        <p class="animate-stagger-3 mt-4 text-lg text-white/80 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                    <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                        <p class="animate-stagger-3 mt-4 text-lg text-white/80 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-center lg:justify-end animate-stagger-4">
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8 w-full max-w-lg ring-1 ring-white/20 form-glow" id="signup-form" x-data="{ loading: false, error: '' }">
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.7);">J</div>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.85);">M</div>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">S</div>
                        </div>
                        <span class="text-sm text-gray-600"><?= h($sh('form_social_proof', 'Join 2,500+ readers')) ?></span>
                    </div>

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
                                <input type="text" id="name" name="name" required class="hero-input w-full px-4 py-3 rounded-lg text-sm focus:outline-none" placeholder="John Smith">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                <input type="email" id="email" name="email" required class="hero-input w-full px-4 py-3 rounded-lg text-sm focus:outline-none" placeholder="john@company.com">
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                        <button type="submit" :disabled="loading" onclick="heroRipple(event)"
                                class="btn-shimmer mt-6 w-full btn-brand px-10 py-4 text-white font-bold rounded-full transform hover:scale-[1.02] shadow-lg transition text-lg uppercase tracking-wide disabled:opacity-50">
                            <span class="relative z-10 inline-flex items-center justify-center space-x-2" x-show="!loading">
                                <span><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                            <span class="relative z-10" x-show="loading" x-cloak><?= h($sh('form_sending', 'Sending...')) ?></span>
                            <span class="ripple"></span>
                        </button>

                        <!-- Trust badges -->
                        <div class="mt-5 flex items-center justify-center space-x-5 text-xs text-gray-400">
                            <span class="flex items-center space-x-1.5">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                    <svg class="w-3 h-3 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <span>Secure</span>
                            </span>
                            <span class="flex items-center space-x-1.5">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                    <svg class="w-3 h-3 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </span>
                                <span>No spam</span>
                            </span>
                            <span class="flex items-center space-x-1.5">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);">
                                    <svg class="w-3 h-3 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </span>
                                <span>Instant</span>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Wave: Hero -> Social Proof -->
<div class="wave-divider" style="background: linear-gradient(-45deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>, <?= h($midCtaBg) ?>, <?= h($heroBgLighterHsl) ?>); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C300,48 900,0 1200,48 L1200,48 L0,48 Z" fill="rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.06)"/>
    </svg>
</div>

<!-- 2. Social Proof Bar -->
<section style="background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.06), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.12));">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
        <div class="reveal grid grid-cols-3 gap-4 text-center">
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

<?php if (!empty($features)): ?>
<div class="wave-divider" style="background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.06), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.12));">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,48 C200,0 400,48 600,24 C800,0 1000,48 1200,0 L1200,48 L0,48 Z" fill="rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.02)"/>
    </svg>
</div>
<?php endif; ?>

<!-- 3. Features -->
<?php if (!empty($features)): ?>
<section class="py-20 lg:py-28" style="<?= $bgLight ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php foreach ($features as $i => $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                ?>
                <div class="feature-card rounded-xl p-6 shadow-sm">
                    <div class="w-12 h-12 rounded-lg bg-brand/10 flex items-center justify-center mb-4">
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

<?php if (!empty($chapters)): ?>
<div class="wave-divider" style="<?= !empty($features) ? $bgLight : 'background: linear-gradient(135deg, rgba('.$r.','.$g.','.$b.',0.06), rgba('.$r.','.$g.','.$b.',0.12));' ?>">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C150,48 350,0 600,32 C850,64 1050,0 1200,48 L1200,48 L0,48 Z" fill="rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.05)"/>
    </svg>
</div>
<?php endif; ?>

<!-- 4. Chapters -->
<?php if (!empty($chapters)): ?>
<section class="py-20 lg:py-28" style="<?= $bgMedium ?>">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
            <p class="mt-6 text-gray-500"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
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

<!-- Mid-CTA #1 -->
<?php if (!empty($chapters)): ?>
<div class="wave-divider" style="<?= $bgMedium ?>">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,48 C300,0 900,48 1200,0 L1200,48 L0,48 Z" fill="<?= h($midCtaBg) ?>"/>
    </svg>
</div>
<section class="mid-cta-banner py-16 lg:py-20" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="cta-pattern"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="reveal">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
            <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
            <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
               class="inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full transform hover:scale-[1.02] shadow-lg transition text-lg uppercase tracking-wide">
                <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
<div class="wave-divider" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C300,48 900,0 1200,48 L1200,48 L0,48 Z" fill="rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,<?= !empty($keyStatistics) ? '0.02' : '0.05' ?>)"/>
    </svg>
</div>
<?php endif; ?>

<!-- 5. Key Statistics -->
<?php if (!empty($keyStatistics)): ?>
<section class="py-20 lg:py-28" style="<?= $bgLight ?>">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-2 md:grid-cols-<?= min(count($keyStatistics), 4) ?> gap-6 max-w-3xl mx-auto">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center p-6 bg-white rounded-xl shadow-sm" style="border-left: 4px solid rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
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

<!-- 6. Before/After -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<section class="py-20 lg:py-28" style="<?= $bgMedium ?>">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <?php if (!empty($beforeAfter['before'])): ?>
                <div class="bg-white rounded-xl p-8 border border-red-100">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="font-semibold text-red-700 text-lg"><?= h($sh('before_label', 'Before')) ?></h3>
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
                        <h3 class="font-semibold text-green-700 text-lg"><?= h($sh('after_label', 'After')) ?></h3>
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

<!-- 7. Target Audience -->
<?php if (!empty($targetAudience)): ?>
<section class="py-20 lg:py-28" style="<?= $bgLight ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php foreach ($targetAudience as $persona): ?>
                <div class="feature-card rounded-xl p-6 shadow-sm text-center">
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

<!-- Mid-CTA #2 -->
<?php if (!empty($targetAudience)): ?>
<div class="wave-divider" style="<?= $bgLight ?>">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C150,48 350,0 600,32 C850,64 1050,0 1200,48 L1200,48 L0,48 Z" fill="<?= h($midCtaBg) ?>"/>
    </svg>
</div>
<section class="mid-cta-banner py-16 lg:py-20" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <div class="cta-pattern"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="reveal">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_2', 'Ready to Take the Next Step?')) ?></h2>
            <p class="text-white/70 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_2_sub', 'Join thousands of others who have already downloaded this guide.')) ?></p>
            <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
               class="inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full transform hover:scale-[1.02] shadow-lg transition text-lg uppercase tracking-wide">
                <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
<div class="wave-divider" style="background: linear-gradient(135deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>);">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C300,48 900,0 1200,48 L1200,48 L0,48 Z" fill="rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.05)"/>
    </svg>
</div>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<section class="py-20 lg:py-28" style="<?= $bgMedium ?>">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal bg-white rounded-xl p-8 border border-gray-200 shadow-sm">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 rounded-full bg-brand/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($sh('author_title', 'About the Author')) ?></h3>
                    <p class="text-gray-600 leading-relaxed"><?= h($authorBio) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials -->
<?php if (!empty($testimonials)): ?>
<section class="py-20 lg:py-28" style="<?= $bgLight ?>">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
        </div>
        <div class="reveal grid grid-cols-1 md:grid-cols-<?= min(count($testimonials), 3) ?> gap-8 max-w-4xl mx-auto">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card bg-white rounded-xl p-6 shadow-sm">
                    <div class="flex space-x-0.5 mb-4">
                        <?php for ($s = 0; $s < 5; $s++): ?>
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
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

<!-- 10. FAQ -->
<?php if (!empty($faqItems)): ?>
<section class="py-20 lg:py-28" style="<?= $bgMedium ?>">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center mb-12">
            <h2 class="section-heading text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
        </div>
        <div class="reveal space-y-4" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
                    <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <span class="font-medium text-gray-900"><?= h($faq['question'] ?? '') ?></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="openFaq === <?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

<!-- Wave -> Bottom CTA -->
<?php $prevBgBottom = !empty($faqItems) ? $bgMedium : (!empty($testimonials) ? $bgLight : $bgMedium); ?>
<div class="wave-divider" style="<?= $prevBgBottom ?>">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C150,48 350,0 600,32 C850,64 1050,0 1200,48 L1200,48 L0,48 Z" fill="<?= h($midCtaBg) ?>"/>
    </svg>
</div>

<!-- Guarantee Section -->
<section class="py-14 lg:py-18" style="<?= $bgMedium ?>">
    <div class="reveal max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-5" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08);">
            <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= h($sh('guarantee_title', '100% Free, No Strings Attached')) ?></h3>
        <p class="text-gray-500 text-sm max-w-md mx-auto"><?= h($sh('guarantee_desc', 'This guide is completely free. No credit card required, no hidden fees. Just actionable insights delivered to your inbox.')) ?></p>
    </div>
</section>

<!-- Wave -> Bottom CTA -->
<div class="wave-divider" style="<?= $bgMedium ?>">
    <svg viewBox="0 0 1200 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C150,48 350,0 600,32 C850,64 1050,0 1200,48 L1200,48 L0,48 Z" fill="<?= h($midCtaBg) ?>"/>
    </svg>
</div>

<!-- 11. Bottom CTA -->
<section class="relative overflow-hidden py-16 lg:py-20" style="background: linear-gradient(-45deg, <?= h($midCtaBg) ?>, <?= h($midCtaBgDarker) ?>, <?= h($midCtaBg) ?>, <?= h($heroBgLighterHsl) ?>); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
    <div class="absolute inset-0 bg-gradient-to-br from-black/30 to-transparent"></div>
    <div class="absolute inset-0 opacity-[0.04]">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E'); background-size: 30px 30px;"></div>
    </div>
    <div class="absolute top-5 right-16 w-48 h-48 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="absolute bottom-5 left-8 w-36 h-36 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb2 25s ease-in-out infinite;"></div>
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col lg:flex-row items-center justify-center lg:space-x-12 text-center lg:text-left">
            <?php if ($coverImage): ?>
                <div class="hidden lg:block flex-shrink-0 mb-8 lg:mb-0">
                    <div class="book-mockup relative">
                        <div class="book-glow"></div>
                        <div class="book-mockup-inner book-float book-3d rounded-lg overflow-hidden">
                            <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-36 h-auto">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
                <p class="text-white/70 mb-4 max-w-lg"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>

                <!-- Benefit stack -->
                <div class="flex flex-col sm:flex-row items-center lg:items-start gap-3 mb-8 text-sm text-white/70">
                    <?php
                        $benefitItems = array_slice($features, 0, 3);
                        if (empty($benefitItems)) {
                            $benefitItems = [
                                ['title' => $sh('benefit_1', 'Actionable strategies')],
                                ['title' => $sh('benefit_2', 'Expert insights')],
                                ['title' => $sh('benefit_3', 'Instant PDF download')],
                            ];
                        }
                    ?>
                    <?php foreach ($benefitItems as $bi): ?>
                        <span class="flex items-center space-x-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span><?= h(is_array($bi) ? ($bi['title'] ?? '') : $bi) ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>

                <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
                   class="btn-shimmer inline-flex items-center justify-center px-10 py-4 bg-white text-gray-900 font-bold rounded-full transform hover:scale-[1.02] shadow-lg transition text-lg uppercase tracking-wide">
                    <span class="relative z-10 inline-flex items-center space-x-2">
                        <span><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
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
    const hero = document.getElementById('hero-parallax');
    if (!hero || window.innerWidth < 1024) return;
    const book = document.getElementById('parallax-book');
    const orb1 = document.getElementById('parallax-orb1');
    const orb2 = document.getElementById('parallax-orb2');
    const orb3 = document.getElementById('parallax-orb3');
    document.getElementById('hero').addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        if (book) {
            const inner = book.querySelector('.book-mockup-inner');
            if (inner) inner.style.transform = 'translateY(-5px) rotateY(' + (-15 + x * 12) + 'deg) rotateX(' + (y * 8) + 'deg)';
        }
        if (orb1) orb1.style.transform = 'translate(' + (x * 30) + 'px, ' + (y * 15) + 'px)';
        if (orb2) orb2.style.transform = 'translate(' + (x * 20) + 'px, ' + (y * 10) + 'px)';
        if (orb3) orb3.style.transform = 'translate(' + (x * 10) + 'px, ' + (y * 10) + 'px)';
    });
    document.getElementById('hero').addEventListener('mouseleave', function() {
        if (book) { const inner = book.querySelector('.book-mockup-inner'); if (inner) inner.style.transform = ''; }
        if (orb1) orb1.style.transform = '';
        if (orb2) orb2.style.transform = '';
        if (orb3) orb3.style.transform = '';
    });
})();

function heroRipple(e) {
    var btn = e.currentTarget, ripple = btn.querySelector('.ripple');
    if (!ripple) return;
    var rect = btn.getBoundingClientRect(), size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
    ripple.style.opacity = '1'; ripple.style.transform = 'scale(1)';
    setTimeout(function() { ripple.style.opacity = '0'; ripple.style.transform = 'scale(0)'; }, 450);
}

(function() {
    var reveals = document.querySelectorAll('.reveal');
    if (!reveals.length || !('IntersectionObserver' in window)) { reveals.forEach(function(el) { el.classList.add('revealed'); }); return; }
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) { if (entry.isIntersecting) { entry.target.classList.add('revealed'); observer.unobserve(entry.target); } });
    }, { threshold: 0.1, rootMargin: '-60px 0px' });
    reveals.forEach(function(el) { observer.observe(el); });
})();

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
