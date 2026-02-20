<?php
$pageTitle = 'My Account';
$tenant = currentTenant();
$metaDescription = 'Your account dashboard';
$user = currentUser();

// $recentOrders should be passed from the controller (optional)
$recentOrders = $recentOrders ?? [];

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Account</h1>
            <p class="mt-2 text-gray-500">Welcome back, <?= h($user['name'] ?? 'there') ?>!</p>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <a href="/account/orders" class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition">Order History</h3>
                <p class="text-sm text-gray-500 mt-1">View and track your orders</p>
            </a>

            <?php if (tenantFeature('courses')): ?>
            <a href="/konto/kurser" class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition">My Courses</h3>
                <p class="text-sm text-gray-500 mt-1">Continue learning</p>
            </a>
            <?php endif; ?>

            <?php if (tenantFeature('courses')): ?>
            <a href="/konto/certificates" class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition">Certificates</h3>
                <p class="text-sm text-gray-500 mt-1">View your earned certificates</p>
            </a>
            <?php endif; ?>

            <a href="/products" class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition">Browse Products</h3>
                <p class="text-sm text-gray-500 mt-1">Explore our catalog</p>
            </a>

            <a href="/logout" class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300 group">
                <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition">Log Out</h3>
                <p class="text-sm text-gray-500 mt-1">Sign out of your account</p>
            </a>
        </div>

        <!-- Account Details -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Account Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Name</p>
                    <p class="text-gray-900 font-medium"><?= h($user['name'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Email</p>
                    <p class="text-gray-900 font-medium"><?= h($user['email'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Member Since</p>
                    <p class="text-gray-900 font-medium"><?= formatDate($user['created_at'] ?? '', 'd M Y') ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <?php if (!empty($recentOrders)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                    <a href="/account/orders" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-2 text-gray-500 font-medium">Order</th>
                                <th class="text-left py-3 px-2 text-gray-500 font-medium">Date</th>
                                <th class="text-left py-3 px-2 text-gray-500 font-medium">Status</th>
                                <th class="text-right py-3 px-2 text-gray-500 font-medium">Total</th>
                                <th class="text-right py-3 px-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-2 font-medium text-gray-900">#<?= h($order['order_number']) ?></td>
                                    <td class="py-3 px-2 text-gray-500"><?= formatDate($order['created_at']) ?></td>
                                    <td class="py-3 px-2">
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
                                    </td>
                                    <td class="py-3 px-2 text-right font-medium text-gray-900"><?= formatMoney($order['total_dkk'], $order['currency'] ?? ($tenant['currency'] ?? 'DKK')) ?></td>
                                    <td class="py-3 px-2 text-right">
                                        <a href="/account/orders/<?= h($order['order_number']) ?>" class="text-brand hover:underline text-xs font-medium">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
