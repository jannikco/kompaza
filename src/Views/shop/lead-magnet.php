<?php
$pageTitle = $leadMagnet['title'] ?? 'Free Download';
$tenant = currentTenant();
$metaDescription = $leadMagnet['meta_description'] ?? $leadMagnet['subtitle'] ?? '';
$heroBgColor = $leadMagnet['hero_bg_color'] ?? ($tenant['primary_color'] ?? '#1e40af');

$features = [];
if (!empty($leadMagnet['features'])) {
    $features = json_decode($leadMagnet['features'], true) ?: [];
}

ob_start();
?>

<!-- Hero Section -->
<section class="relative overflow-hidden" style="background-color: <?= h($heroBgColor) ?>;">
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

                <?php if (!empty($leadMagnet['hero_image_path'])): ?>
                    <div class="mt-8 lg:hidden">
                        <img src="<?= h(imageUrl($leadMagnet['hero_image_path'])) ?>" alt="<?= h($leadMagnet['title']) ?>"
                             class="rounded-xl shadow-2xl max-w-sm mx-auto">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Image (desktop) or Form -->
            <div class="flex flex-col items-center">
                <?php if (!empty($leadMagnet['hero_image_path'])): ?>
                    <div class="hidden lg:block mb-8">
                        <img src="<?= h(imageUrl($leadMagnet['hero_image_path'])) ?>" alt="<?= h($leadMagnet['title']) ?>"
                             class="rounded-xl shadow-2xl max-w-md">
                    </div>
                <?php endif; ?>

                <!-- Signup Form -->
                <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md" x-data="{ loading: false, error: '' }">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Get Your Free Copy</h2>
                    <p class="text-gray-500 text-sm mb-6">Enter your details below and we'll send it straight to your inbox.</p>

                    <form action="/lp/<?= h($leadMagnet['slug']) ?>/signup" method="POST"
                          @submit.prevent="
                              loading = true; error = '';
                              const fd = new FormData($el);
                              fetch($el.action, { method: 'POST', body: fd })
                                  .then(r => r.json())
                                  .then(d => {
                                      loading = false;
                                      if (d.success) { window.location.href = '/lp/<?= h($leadMagnet['slug']) ?>/success'; }
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

<!-- Features Section -->
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
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
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

<!-- Bottom CTA -->
<section class="py-16 lg:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Ready to Get Started?</h2>
        <p class="text-gray-500 mb-8">Download your free copy now and start implementing today.</p>
        <a href="#" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;"
           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
