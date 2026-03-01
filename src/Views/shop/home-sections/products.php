<?php
/**
 * Homepage Section: Products
 * Receives: $section, $tenant, $template, $products
 */
if (empty($products)) return;
$heading = $section['heading'] ?? 'Our Products';
$subtitle = $section['subtitle'] ?? '';
$count = (int)($section['count'] ?? 3);
$products = array_slice($products, 0, $count);
?>
<section class="py-16 lg:py-20 <?= $template === 'elegant' ? 'bg-white' : 'bg-gray-50' ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($template === 'starter'): ?>
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                <a href="/produkter" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
            </div>
        <?php elseif ($template === 'bold'): ?>
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900"><?= h($heading) ?></h2>
                <?php if ($subtitle): ?>
                    <p class="mt-3 text-gray-500 text-lg"><?= h($subtitle) ?></p>
                <?php else: ?>
                    <p class="mt-3 text-gray-500 text-lg">Premium tools and resources for professionals</p>
                <?php endif; ?>
            </div>
        <?php else: /* elegant */ ?>
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="mt-2 text-gray-500"><?= h($subtitle) ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-gray-500">Quality tools built for professionals</p>
                    <?php endif; ?>
                </div>
                <a href="/produkter" class="hidden sm:inline-flex items-center text-sm font-semibold text-brand hover:underline">
                    View all
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
                <a href="/produkt/<?= h($product['slug']) ?>" class="group <?= $template === 'bold' ? 'bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 bold-card border border-gray-100' : ($template === 'elegant' ? 'bg-gray-50 rounded-xl border border-gray-100 hover:shadow-md transition-all duration-300' : 'bg-white rounded-xl border border-gray-200 hover:shadow-lg transition-shadow duration-300') ?> overflow-hidden">
                    <?php if (!empty($product['image_path'])): ?>
                        <div class="aspect-video overflow-hidden <?= $template === 'elegant' ? 'bg-gray-100' : '' ?>">
                            <img src="<?= h(imageUrl($product['image_path'])) ?>" alt="<?= h($product['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-<?= $template === 'starter' ? '300' : '500' ?>">
                        </div>
                    <?php else: ?>
                        <div class="aspect-video <?= $template === 'bold' ? 'bold-hero-gradient-light' : ($template === 'elegant' ? 'elegant-accent' : 'bg-gray-100') ?> flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="<?= $template === 'elegant' ? 'text-base font-semibold leading-snug' : 'text-lg font-' . ($template === 'bold' ? 'bold' : 'semibold') ?> text-gray-900 group-hover:text-brand transition mb-2"><?= h($product['title']) ?></h3>
                        <?php if (!empty($product['short_description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3 <?= $template === 'elegant' ? 'leading-relaxed' : '' ?>"><?= h($product['short_description']) ?></p>
                        <?php endif; ?>
                        <span class="<?= $template === 'bold' ? 'text-lg font-extrabold' : ($template === 'elegant' ? 'text-base font-bold' : 'text-lg font-bold') ?> text-gray-900"><?= formatMoney($product['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if ($template === 'bold'): ?>
            <div class="text-center mt-10">
                <a href="/produkter" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-xl transition shadow-sm text-base">
                    View All Products
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
