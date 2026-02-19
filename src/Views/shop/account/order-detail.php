<?php
$pageTitle = 'Order #' . ($order['order_number'] ?? '');
$tenant = currentTenant();
$metaDescription = 'Order details';
$currency = $order['currency'] ?? ($tenant['currency'] ?? 'DKK');

// $order should be passed from the controller
// $orderItems should be passed from the controller
$orderItems = $orderItems ?? [];

$statusClasses = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'shipped' => 'bg-indigo-100 text-indigo-800',
    'delivered' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
    'refunded' => 'bg-gray-100 text-gray-800',
];

$statusSteps = ['pending', 'processing', 'shipped', 'delivered'];
$currentStatusIndex = array_search($order['status'], $statusSteps);

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <a href="/account/orders" class="text-sm text-gray-500 hover:text-gray-700 transition mb-2 inline-block">&larr; Back to Orders</a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Order #<?= h($order['order_number']) ?></h1>
                <p class="mt-1 text-sm text-gray-500">Placed on <?= formatDate($order['created_at'], 'd M Y \a\t H:i') ?></p>
            </div>
            <span class="inline-block px-3 py-1 text-sm font-medium rounded-full <?= $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                <?= h(ucfirst($order['status'])) ?>
            </span>
        </div>

        <!-- Status Tracker -->
        <?php if ($currentStatusIndex !== false && !in_array($order['status'], ['cancelled', 'refunded'])): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between relative">
                    <!-- Progress line -->
                    <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200"></div>
                    <div class="absolute top-4 left-0 h-0.5 bg-brand transition-all" style="width: <?= ($currentStatusIndex / (count($statusSteps) - 1)) * 100 ?>%"></div>

                    <?php foreach ($statusSteps as $i => $step): ?>
                        <div class="relative flex flex-col items-center z-10">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                <?php if ($i <= $currentStatusIndex): ?>
                                    bg-brand text-white
                                <?php else: ?>
                                    bg-gray-200 text-gray-400
                                <?php endif; ?>
                            ">
                                <?php if ($i < $currentStatusIndex): ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php else: ?>
                                    <?= $i + 1 ?>
                                <?php endif; ?>
                            </div>
                            <span class="mt-2 text-xs font-medium <?= $i <= $currentStatusIndex ? 'text-gray-900' : 'text-gray-400' ?> hidden sm:block">
                                <?= h(ucfirst($step)) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="font-bold text-gray-900">Order Items</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php if (!empty($orderItems)): ?>
                            <?php foreach ($orderItems as $item): ?>
                                <div class="flex items-center gap-4 p-4 sm:p-6">
                                    <div class="w-16 h-16 rounded-lg bg-gray-50 border border-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?= h($item['product_image']) ?>" alt="" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 text-sm"><?= h($item['product_name']) ?></p>
                                        <?php if (!empty($item['product_sku'])): ?>
                                            <p class="text-xs text-gray-400">SKU: <?= h($item['product_sku']) ?></p>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-500 mt-0.5">Qty: <?= (int)$item['quantity'] ?> x <?= formatMoney($item['unit_price_dkk'], $currency) ?></p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="font-medium text-gray-900 text-sm"><?= formatMoney($item['total_price_dkk'], $currency) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-6 text-sm text-gray-500">No items found for this order.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Order Total -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Order Total</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span><?= formatMoney($order['subtotal_dkk'], $currency) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax</span>
                            <span><?= formatMoney($order['tax_dkk'], $currency) ?></span>
                        </div>
                        <?php if (($order['shipping_dkk'] ?? 0) > 0): ?>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span><?= formatMoney($order['shipping_dkk'], $currency) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (($order['discount_dkk'] ?? 0) > 0): ?>
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span>-<?= formatMoney($order['discount_dkk'], $currency) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="border-t border-gray-200 pt-2 flex justify-between text-gray-900 font-bold text-base">
                            <span>Total</span>
                            <span><?= formatMoney($order['total_dkk'], $currency) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <?php if (!empty($order['shipping_address'])): ?>
                    <?php $shipping = is_string($order['shipping_address']) ? json_decode($order['shipping_address'], true) : $order['shipping_address']; ?>
                    <?php if ($shipping): ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="font-bold text-gray-900 mb-3">Shipping Address</h2>
                            <div class="text-sm text-gray-600 space-y-1">
                                <?php if (!empty($shipping['street'])): ?><p><?= h($shipping['street']) ?></p><?php endif; ?>
                                <p>
                                    <?= h($shipping['postal'] ?? '') ?>
                                    <?= h($shipping['city'] ?? '') ?>
                                </p>
                                <?php if (!empty($shipping['country'])): ?><p><?= h($shipping['country']) ?></p><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Customer Info -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="font-bold text-gray-900 mb-3">Customer Details</h2>
                    <div class="text-sm text-gray-600 space-y-1">
                        <?php if (!empty($order['customer_name'])): ?><p class="font-medium text-gray-900"><?= h($order['customer_name']) ?></p><?php endif; ?>
                        <?php if (!empty($order['customer_email'])): ?><p><?= h($order['customer_email']) ?></p><?php endif; ?>
                        <?php if (!empty($order['customer_phone'])): ?><p><?= h($order['customer_phone']) ?></p><?php endif; ?>
                        <?php if (!empty($order['customer_company'])): ?><p><?= h($order['customer_company']) ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Notes -->
                <?php if (!empty($order['notes'])): ?>
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-bold text-gray-900 mb-3">Order Notes</h2>
                        <p class="text-sm text-gray-600"><?= h($order['notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
