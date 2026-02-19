<?php
$pageTitle = 'My Orders';
$tenant = currentTenant();
$metaDescription = 'Your order history';

// $orders should be passed from the controller
$orders = $orders ?? [];

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
                <p class="mt-1 text-sm text-gray-500">Track and manage your orders</p>
            </div>
            <a href="/account" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                &larr; Back to Account
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <p class="text-gray-500 text-lg mb-2">No orders yet</p>
                <p class="text-gray-400 text-sm mb-6">When you place your first order, it will appear here.</p>
                <a href="/products" class="btn-brand inline-flex items-center px-6 py-3 text-white font-semibold rounded-lg transition text-sm">
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <!-- Mobile: Cards layout -->
            <div class="sm:hidden space-y-4">
                <?php foreach ($orders as $order): ?>
                    <a href="/account/orders/<?= h($order['order_number']) ?>" class="block bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold text-gray-900">#<?= h($order['order_number']) ?></span>
                            <?php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-indigo-100 text-indigo-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'refunded' => 'bg-gray-100 text-gray-800',
                            ];
                            $cls = $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full <?= $cls ?>"><?= h(ucfirst($order['status'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><?= formatDate($order['created_at']) ?></span>
                            <span class="font-medium text-gray-900"><?= formatMoney($order['total_dkk'], $order['currency'] ?? ($tenant['currency'] ?? 'DKK')) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Desktop: Table layout -->
            <div class="hidden sm:block bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left py-3.5 px-6 text-gray-500 font-medium">Order Number</th>
                            <th class="text-left py-3.5 px-6 text-gray-500 font-medium">Date</th>
                            <th class="text-left py-3.5 px-6 text-gray-500 font-medium">Status</th>
                            <th class="text-right py-3.5 px-6 text-gray-500 font-medium">Total</th>
                            <th class="text-right py-3.5 px-6"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4 px-6 font-medium text-gray-900">#<?= h($order['order_number']) ?></td>
                                <td class="py-4 px-6 text-gray-500"><?= formatDate($order['created_at'], 'd M Y H:i') ?></td>
                                <td class="py-4 px-6">
                                    <?php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'shipped' => 'bg-indigo-100 text-indigo-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $cls = $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-block px-2.5 py-0.5 text-xs font-medium rounded-full <?= $cls ?>"><?= h(ucfirst($order['status'])) ?></span>
                                </td>
                                <td class="py-4 px-6 text-right font-medium text-gray-900"><?= formatMoney($order['total_dkk'], $order['currency'] ?? ($tenant['currency'] ?? 'DKK')) ?></td>
                                <td class="py-4 px-6 text-right">
                                    <a href="/account/orders/<?= h($order['order_number']) ?>" class="text-brand hover:underline text-sm font-medium">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
