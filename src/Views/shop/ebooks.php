<?php
$pageTitle = 'Ebooks';
$tenant = currentTenant();
$metaDescription = "Browse ebooks from " . h($tenant['company_name'] ?? $tenant['name'] ?? 'our store');

// $ebooks should be passed from the controller
$ebooks = $ebooks ?? [];

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Ebooks</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">In-depth guides and resources to help you grow.</p>
        </div>

        <?php if (empty($ebooks)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-gray-500 text-lg">No ebooks available yet.</p>
                <p class="text-gray-400 text-sm mt-1">Check back soon for new releases.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($ebooks as $ebook): ?>
                    <a href="/ebook/<?= h($ebook['slug']) ?>" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <?php if (!empty($ebook['cover_image_path'])): ?>
                            <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                                <img src="<?= h($ebook['cover_image_path']) ?>" alt="<?= h($ebook['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        <?php else: ?>
                            <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-brand transition mb-1 line-clamp-2"><?= h($ebook['title']) ?></h2>
                            <?php if (!empty($ebook['subtitle'])): ?>
                                <p class="text-gray-500 text-sm mb-3 line-clamp-2"><?= h($ebook['subtitle']) ?></p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between mt-3">
                                <?php if ($ebook['price_dkk'] > 0): ?>
                                    <span class="text-lg font-bold text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-green-600">Free</span>
                                <?php endif; ?>
                                <?php if (!empty($ebook['page_count'])): ?>
                                    <span class="text-xs text-gray-400"><?= (int)$ebook['page_count'] ?> pages</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
