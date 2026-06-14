<?php
$pageTitle = 'Special Offer';
$tenant = currentTenant();
$metaDescription = 'Exclusive offer just for you';
$currency = $tenant['currency'] ?? 'DKK';

ob_start();
?>

<section class="py-12 lg:py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Headline -->
        <div class="mb-8">
            <p class="text-sm font-semibold text-brand uppercase tracking-wider mb-2">Wait! One-Time Offer</p>
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
                <?= h($offer['headline'] ?: 'Upgrade Your Order!') ?>
            </h1>
        </div>

        <!-- Offer Card -->
        <div class="bg-white rounded-2xl border-2 border-brand shadow-lg overflow-hidden mb-8">
            <?php if ($offer['image_path']): ?>
                <div class="h-48 bg-gray-100">
                    <img src="<?= h(imageUrl($offer['image_path'])) ?>" class="w-full h-full object-cover" alt="<?= h($offer['product_name']) ?>">
                </div>
            <?php endif; ?>

            <div class="p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-3"><?= h($offer['product_name']) ?></h2>

                <?php if ($offer['description']): ?>
                    <div class="text-gray-600 text-sm leading-relaxed mb-6"><?= nl2br(h($offer['description'])) ?></div>
                <?php endif; ?>

                <!-- Price -->
                <div class="flex items-center justify-center gap-3 mb-6">
                    <?php if ($offer['original_price_dkk']): ?>
                        <span class="text-2xl text-gray-400 line-through"><?= formatMoney($offer['original_price_dkk']) ?></span>
                    <?php endif; ?>
                    <span class="text-3xl font-bold text-brand"><?= formatMoney($offer['offer_price_dkk']) ?></span>
                    <?php if ($offer['original_price_dkk'] && (float)$offer['original_price_dkk'] > (float)$offer['offer_price_dkk']): ?>
                        <?php $savings = round((1 - $offer['offer_price_dkk'] / $offer['original_price_dkk']) * 100); ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Save <?= $savings ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Accept Button -->
                <form method="POST" action="/checkout/upsell/accept">
                    <?= csrfField() ?>
                    <input type="hidden" name="offer_id" value="<?= (int)$offer['id'] ?>">
                    <button type="submit"
                            class="w-full btn-brand px-8 py-4 text-white font-bold rounded-xl text-lg transition transform hover:scale-[1.02] shadow-lg">
                        <?= h($offer['button_text'] ?: 'Yes, Add This To My Order!') ?>
                    </button>
                </form>

                <p class="text-xs text-gray-400 mt-3">This will be added to your order #<?= h($order['order_number']) ?></p>
            </div>
        </div>

        <!-- Decline -->
        <form method="POST" action="/checkout/upsell/decline">
            <?= csrfField() ?>
            <input type="hidden" name="offer_id" value="<?= (int)$offer['id'] ?>">
            <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition underline">
                <?= h($offer['decline_text'] ?: "No thanks, I'll pass") ?>
            </button>
        </form>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
