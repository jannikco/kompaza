<?php
$pageTitle = $product['name'] ?? 'Product';
$tenant = currentTenant();
$metaDescription = $product['short_description'] ?? $product['name'] ?? '';

$gallery = [];
if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?: [];
}

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center text-sm text-gray-400 space-x-2">
                <li><a href="/" class="hover:text-gray-600 transition">Home</a></li>
                <li><span>/</span></li>
                <li><a href="/products" class="hover:text-gray-600 transition">Products</a></li>
                <li><span>/</span></li>
                <li class="text-gray-600 truncate max-w-xs"><?= h($product['name']) ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16" x-data="{
            qty: 1,
            adding: false,
            added: false,
            activeImage: '<?= h($product['image_path'] ?? '') ?>',
            addToCart() {
                this.adding = true;
                let cart = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenant['id'] ?>') || '[]');
                let idx = cart.findIndex(i => i.id === <?= (int)$product['id'] ?>);
                if (idx >= 0) { cart[idx].qty += this.qty; } else { cart.push({ id: <?= (int)$product['id'] ?>, name: <?= json_encode($product['name']) ?>, price: <?= (float)$product['price_dkk'] ?>, image: <?= json_encode($product['image_path'] ?? '') ?>, qty: this.qty }); }
                localStorage.setItem('kz_cart_<?= (int)$tenant['id'] ?>', JSON.stringify(cart));
                window.dispatchEvent(new Event('cart-updated'));
                this.adding = false;
                this.added = true;
                setTimeout(() => this.added = false, 3000);
            }
        }">
            <!-- Product Image -->
            <div>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm mb-4">
                    <template x-if="activeImage">
                        <img :src="activeImage" alt="<?= h($product['name']) ?>"
                             class="w-full h-auto object-cover aspect-square">
                    </template>
                    <template x-if="!activeImage">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </template>
                </div>
                <?php if (!empty($gallery)): ?>
                    <div class="grid grid-cols-4 gap-3">
                        <?php if (!empty($product['image_path'])): ?>
                            <button @click="activeImage = '<?= h($product['image_path']) ?>'"
                                    class="rounded-lg border-2 overflow-hidden aspect-square"
                                    :class="activeImage === '<?= h($product['image_path']) ?>' ? 'border-brand' : 'border-gray-200 hover:border-gray-300'">
                                <img src="<?= h($product['image_path']) ?>" class="w-full h-full object-cover" alt="">
                            </button>
                        <?php endif; ?>
                        <?php foreach ($gallery as $img): ?>
                            <button @click="activeImage = '<?= h($img) ?>'"
                                    class="rounded-lg border-2 overflow-hidden aspect-square"
                                    :class="activeImage === '<?= h($img) ?>' ? 'border-brand' : 'border-gray-200 hover:border-gray-300'">
                                <img src="<?= h($img) ?>" class="w-full h-full object-cover" alt="">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div>
                <?php if (!empty($product['category'])): ?>
                    <span class="inline-block text-xs font-semibold text-brand uppercase tracking-wider mb-2"><?= h($product['category']) ?></span>
                <?php endif; ?>

                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight"><?= h($product['name']) ?></h1>

                <?php if (!empty($product['short_description'])): ?>
                    <p class="mt-3 text-lg text-gray-500"><?= h($product['short_description']) ?></p>
                <?php endif; ?>

                <!-- Price -->
                <div class="mt-6 flex items-center gap-3">
                    <span class="text-3xl font-extrabold text-gray-900"><?= formatMoney($product['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    <?php if (!empty($product['compare_price_dkk']) && $product['compare_price_dkk'] > $product['price_dkk']): ?>
                        <span class="text-lg text-gray-400 line-through"><?= formatMoney($product['compare_price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php
                            $discount = round((1 - $product['price_dkk'] / $product['compare_price_dkk']) * 100);
                        ?>
                        <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded">-<?= $discount ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- SKU & Stock -->
                <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                    <?php if (!empty($product['sku'])): ?>
                        <span>SKU: <?= h($product['sku']) ?></span>
                    <?php endif; ?>
                    <?php if ($product['track_stock']): ?>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="text-green-600 font-medium">In Stock (<?= (int)$product['stock_quantity'] ?> left)</span>
                        <?php else: ?>
                            <span class="text-red-600 font-medium">Out of Stock</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Quantity + Add to Cart -->
                <div class="mt-8 flex items-center gap-4">
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button @click="if (qty > 1) qty--" class="px-3 py-2.5 text-gray-600 hover:text-gray-900 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="number" x-model.number="qty" min="1" max="99"
                               class="w-14 text-center border-x border-gray-300 py-2.5 text-sm font-medium focus:outline-none">
                        <button @click="if (qty < 99) qty++" class="px-3 py-2.5 text-gray-600 hover:text-gray-900 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    <button @click="addToCart()" :disabled="adding || <?= ($product['track_stock'] && $product['stock_quantity'] <= 0) ? 'true' : 'false' ?>"
                            class="flex-1 btn-brand px-8 py-3 text-white font-semibold rounded-lg transition text-base disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!added">
                            <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            Add to Cart
                        </span>
                        <span x-show="added" x-cloak class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Added to Cart
                        </span>
                    </button>
                </div>

                <!-- Description -->
                <?php if (!empty($product['description'])): ?>
                    <div class="mt-10 pt-8 border-t border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Description</h2>
                        <div class="prose prose-gray max-w-none text-gray-600">
                            <?= $product['description'] ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tags -->
                <?php if (!empty($product['tags'])): ?>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (explode(',', $product['tags']) as $tag): ?>
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                    <?= h(trim($tag)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
