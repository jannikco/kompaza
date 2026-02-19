<?php
$pageTitle = 'Products';
$tenant = currentTenant();
$metaDescription = "Browse products from " . h($tenant['company_name'] ?? $tenant['name'] ?? 'our store');

// $products should be passed from the controller
$products = $products ?? [];

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Products</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Explore our full range of products.</p>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-500 text-lg">No products available yet.</p>
                <p class="text-gray-400 text-sm mt-1">Check back soon for new arrivals.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300"
                         x-data="{ adding: false, added: false }">
                        <a href="/product/<?= h($product['slug']) ?>" class="block">
                            <?php if (!empty($product['image_path'])): ?>
                                <div class="aspect-square overflow-hidden bg-gray-50">
                                    <img src="<?= h($product['image_path']) ?>" alt="<?= h($product['name']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            <?php else: ?>
                                <div class="aspect-square bg-gray-50 flex items-center justify-center">
                                    <svg class="w-14 h-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="p-4">
                            <a href="/product/<?= h($product['slug']) ?>" class="block">
                                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition line-clamp-2 text-sm"><?= h($product['name']) ?></h3>
                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="text-gray-500 text-xs mt-1 line-clamp-2"><?= h($product['short_description']) ?></p>
                                <?php endif; ?>
                            </a>
                            <div class="flex items-center justify-between mt-3">
                                <div>
                                    <span class="text-lg font-bold text-gray-900"><?= formatMoney($product['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                                    <?php if (!empty($product['compare_price_dkk']) && $product['compare_price_dkk'] > $product['price_dkk']): ?>
                                        <span class="text-sm text-gray-400 line-through ml-1"><?= formatMoney($product['compare_price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button
                                @click="
                                    adding = true;
                                    let cart = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenant['id'] ?>') || '[]');
                                    let idx = cart.findIndex(i => i.id === <?= (int)$product['id'] ?>);
                                    if (idx >= 0) { cart[idx].qty++; } else { cart.push({ id: <?= (int)$product['id'] ?>, name: <?= json_encode($product['name']) ?>, price: <?= (float)$product['price_dkk'] ?>, image: <?= json_encode($product['image_path'] ?? '') ?>, qty: 1 }); }
                                    localStorage.setItem('kz_cart_<?= (int)$tenant['id'] ?>', JSON.stringify(cart));
                                    window.dispatchEvent(new Event('cart-updated'));
                                    adding = false; added = true;
                                    setTimeout(() => added = false, 2000);
                                "
                                :disabled="adding"
                                class="mt-3 w-full btn-brand px-4 py-2.5 text-white font-medium rounded-lg transition text-sm disabled:opacity-50">
                                <span x-show="!added">Add to Cart</span>
                                <span x-show="added" x-cloak class="flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Added
                                </span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
