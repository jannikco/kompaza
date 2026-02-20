<?php
$pageTitle = $ebook['title'] ?? 'Ebook';
$tenant = currentTenant();
$metaDescription = $ebook['meta_description'] ?? $ebook['subtitle'] ?? '';

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center text-sm text-gray-400 space-x-2">
                <li><a href="/" class="hover:text-gray-600 transition">Home</a></li>
                <li><span>/</span></li>
                <li><a href="/ebooks" class="hover:text-gray-600 transition">Ebooks</a></li>
                <li><span>/</span></li>
                <li class="text-gray-600 truncate max-w-xs"><?= h($ebook['title']) ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
            <!-- Cover Image -->
            <div>
                <?php if (!empty($ebook['cover_image_path'])): ?>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                             class="w-full h-auto object-cover">
                    </div>
                <?php else: ?>
                    <div class="bg-gray-100 rounded-xl border border-gray-200 aspect-[3/4] flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ebook Details -->
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight"><?= h($ebook['title']) ?></h1>
                <?php if (!empty($ebook['subtitle'])): ?>
                    <p class="mt-3 text-lg text-gray-500"><?= h($ebook['subtitle']) ?></p>
                <?php endif; ?>

                <!-- Meta Info -->
                <div class="flex items-center gap-4 mt-6 text-sm text-gray-500">
                    <?php if (!empty($ebook['page_count'])): ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <?= (int)$ebook['page_count'] ?> pages
                        </span>
                    <?php endif; ?>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </span>
                </div>

                <!-- Price -->
                <div class="mt-8">
                    <?php if ($ebook['price_dkk'] > 0): ?>
                        <span class="text-3xl font-extrabold text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    <?php else: ?>
                        <span class="text-3xl font-extrabold text-green-600">Free</span>
                    <?php endif; ?>
                </div>

                <!-- CTA Button -->
                <div class="mt-8">
                    <?php if ($ebook['price_dkk'] > 0): ?>
                        <a href="/ebook/<?= h($ebook['slug']) ?>/buy"
                           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            Buy Now
                        </a>
                    <?php else: ?>
                        <a href="/ebook/<?= h($ebook['slug']) ?>/download"
                           class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Free
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($ebook['description'])): ?>
                    <div class="mt-10 pt-8 border-t border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">About This Ebook</h2>
                        <div class="prose prose-gray max-w-none text-gray-600">
                            <?= $ebook['description'] ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Features -->
                <?php
                $features = [];
                if (!empty($ebook['features'])) {
                    $features = json_decode($ebook['features'], true) ?: [];
                }
                ?>
                <?php if (!empty($features)): ?>
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">What You'll Learn</h2>
                        <ul class="space-y-3">
                            <?php foreach ($features as $feature): ?>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-gray-600"><?= h(is_array($feature) ? ($feature['text'] ?? $feature['title'] ?? '') : $feature) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
