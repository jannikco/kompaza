<?php
$pageTitle = $leadMagnet['title'] ?? 'Free Download';
$tenant = currentTenant();
$metaDescription = $leadMagnet['meta_description'] ?? $leadMagnet['subtitle'] ?? '';
$heroBgColor = $leadMagnet['hero_bg_color'] ?? ($tenant['primary_color'] ?? '#1e40af');

// Compute gradient shades from hero bg color (+-20 per RGB channel)
$hexClean = ltrim($heroBgColor, '#');
if (strlen($hexClean) === 3) {
    $hexClean = $hexClean[0].$hexClean[0].$hexClean[1].$hexClean[1].$hexClean[2].$hexClean[2];
}
$r = hexdec(substr($hexClean, 0, 2));
$g = hexdec(substr($hexClean, 2, 2));
$b = hexdec(substr($hexClean, 4, 2));
$heroBgDarker  = sprintf('#%02x%02x%02x', max(0, $r - 25), max(0, $g - 25), max(0, $b - 25));
$heroBgLighter = sprintf('#%02x%02x%02x', min(255, $r + 25), min(255, $g + 25), min(255, $b + 25));

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

// New hero fields (graceful fallback for existing lead magnets)
$heroBadge = $leadMagnet['hero_badge'] ?? '';
$heroAccent = $leadMagnet['hero_headline_accent'] ?? '';
$heroHeadline = $leadMagnet['hero_headline'] ?? $leadMagnet['title'];

// Helper: wraps accent words in <span class="text-brand">
function heroHeadlineWithAccent($headline, $accent) {
    if (empty($accent)) return h($headline);
    return str_replace(h($accent), '<span class="text-brand">' . h($accent) . '</span>', h($headline));
}

// Section headings helper — falls back to English defaults for existing lead magnets
$sectionHeadings = json_decode($leadMagnet['section_headings'] ?? '', true) ?: [];
$sh = fn($key, $default) => $sectionHeadings[$key] ?? $default;

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

    /* CTA button shimmer */
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    .btn-shimmer { position: relative; overflow: hidden; }
    .btn-shimmer::after {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 3s ease-in-out infinite;
    }

    /* Form card colored glow */
    .form-glow {
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.25), 0 0 40px -15px <?= h($heroBgColor) ?>40;
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
</style>

<!-- 1. Hero Section (Premium) -->
<section class="relative overflow-hidden" style="background: linear-gradient(-45deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>, <?= h($heroBgColor) ?>, <?= h($heroBgLighter) ?>); background-size: 400% 400%; animation: gradientShift 15s ease infinite;" id="hero">
    <!-- Gradient overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/30 to-transparent"></div>
    <!-- Cross-hatch SVG pattern -->
    <div class="absolute inset-0 opacity-[0.04]">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E'); background-size: 30px 30px;"></div>
    </div>
    <!-- Floating decorative orbs -->
    <div class="absolute top-10 right-20 w-64 h-64 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="absolute bottom-10 left-10 w-48 h-48 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb2 25s ease-in-out infinite;"></div>
    <div class="absolute top-1/2 left-1/3 w-40 h-40 rounded-full bg-white/[0.03] blur-3xl pointer-events-none" style="animation: floatOrb3 22s ease-in-out infinite;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- Left: Text content with staggered entrance -->
            <div>
                <!-- Badge -->
                <?php if ($heroBadge): ?>
                    <div class="animate-stagger-1 inline-block bg-white/15 text-white/90 px-4 py-1.5 rounded-full text-sm font-semibold mb-5 backdrop-blur-sm">
                        <?= h($heroBadge) ?>
                    </div>
                <?php endif; ?>

                <!-- Desktop: book + text side-by-side -->
                <?php if ($coverImage): ?>
                    <div class="hidden lg:flex items-start gap-8 animate-stagger-2">
                        <div class="book-mockup flex-shrink-0 relative">
                            <div class="book-glow"></div>
                            <div class="book-mockup-inner book-float book-3d rounded-lg overflow-hidden">
                                <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>"
                                     class="w-44 h-auto">
                            </div>
                        </div>
                        <div>
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                                <?= heroHeadlineWithAccent($heroHeadline, $heroAccent) ?>
                            </h1>
                            <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                                <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed">
                                    <?= h($leadMagnet['hero_subheadline']) ?>
                                </p>
                            <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                                <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed">
                                    <?= h($leadMagnet['subtitle']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden lg:block animate-stagger-2">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                            <?= heroHeadlineWithAccent($heroHeadline, $heroAccent) ?>
                        </h1>
                        <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                            <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed">
                                <?= h($leadMagnet['hero_subheadline']) ?>
                            </p>
                        <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                            <p class="animate-stagger-3 mt-5 text-lg md:text-xl text-white/80 leading-relaxed">
                                <?= h($leadMagnet['subtitle']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Mobile: book centered above text -->
                <div class="lg:hidden">
                    <?php if ($coverImage): ?>
                        <div class="flex justify-center mb-6 animate-stagger-1">
                            <div class="book-mockup relative">
                                <div class="book-glow"></div>
                                <div class="book-mockup-inner book-float book-3d rounded-lg overflow-hidden">
                                    <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>"
                                         class="w-48 h-auto">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <h1 class="animate-stagger-2 text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                        <?= heroHeadlineWithAccent($heroHeadline, $heroAccent) ?>
                    </h1>
                    <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                        <p class="animate-stagger-3 mt-4 text-lg text-white/80 leading-relaxed">
                            <?= h($leadMagnet['hero_subheadline']) ?>
                        </p>
                    <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                        <p class="animate-stagger-3 mt-4 text-lg text-white/80 leading-relaxed">
                            <?= h($leadMagnet['subtitle']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Signup Form -->
            <div class="flex justify-center lg:justify-end animate-stagger-4">
                <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8 w-full max-w-lg ring-1 ring-white/20 form-glow" id="signup-form" x-data="{ loading: false, error: '' }">
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
                                <input type="text" id="name" name="name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                       placeholder="John Smith">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                       placeholder="john@company.com">
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                        <button type="submit" :disabled="loading"
                                class="btn-shimmer mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transform hover:scale-[1.02] shadow-lg transition text-base disabled:opacity-50">
                            <span class="relative z-10" x-show="!loading"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                            <span class="relative z-10" x-show="loading" x-cloak><?= h($sh('form_sending', 'Sending...')) ?></span>
                        </button>

                        <p class="mt-4 text-xs text-gray-400 text-center"><?= h($sh('form_privacy', 'We respect your privacy. Unsubscribe at any time.')) ?></p>
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
                    <span class="text-sm font-semibold text-gray-900"><?= h($sh('default_proof_1', 'PDF Guide')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold text-gray-900"><?= h($sh('default_proof_2', '100% Free')) ?></span>
                </div>
                <div class="flex flex-col items-center space-y-1">
                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="text-sm font-semibold text-gray-900"><?= h($sh('default_proof_3', 'Instant Access')) ?></span>
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
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
            <p class="mt-3 text-gray-500"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
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
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
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
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
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

<!-- 7. Target Audience — "Who Is This For?" -->
<?php if (!empty($targetAudience)): ?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
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
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
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
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
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
<section class="relative overflow-hidden py-16 lg:py-20" style="background: linear-gradient(-45deg, <?= h($heroBgColor) ?>, <?= h($heroBgDarker) ?>, <?= h($heroBgColor) ?>, <?= h($heroBgLighter) ?>); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
    <div class="absolute inset-0 bg-gradient-to-br from-black/30 to-transparent"></div>
    <div class="absolute inset-0 opacity-[0.04]">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200L30%2030M30%200L0%2030%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E'); background-size: 30px 30px;"></div>
    </div>
    <!-- Floating orbs -->
    <div class="absolute top-5 right-16 w-48 h-48 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="absolute bottom-5 left-8 w-36 h-36 rounded-full bg-white/5 blur-3xl pointer-events-none" style="animation: floatOrb2 25s ease-in-out infinite;"></div>
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-center lg:space-x-12 text-center lg:text-left">
            <?php if ($coverImage): ?>
                <div class="hidden lg:block flex-shrink-0 mb-8 lg:mb-0">
                    <div class="book-mockup relative">
                        <div class="book-glow"></div>
                        <div class="book-mockup-inner book-float book-3d rounded-lg overflow-hidden">
                            <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>"
                                 class="w-36 h-auto">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
                <p class="text-white/70 mb-8 max-w-lg"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
                <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
                   class="btn-shimmer btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transform hover:scale-[1.02] shadow-lg transition text-base">
                    <span class="relative z-10"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                </a>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
