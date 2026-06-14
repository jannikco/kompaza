<?php
$pageTitle = 'Abandoned Carts';
$currentPage = 'abandoned-carts';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Abandoned Carts</h2>
    <p class="text-sm text-gray-500 mt-1">Track and recover abandoned checkout sessions with automated email reminders.</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-sm text-gray-500">Active Abandoned</p>
        <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int)$activeCount ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-sm text-gray-500">Recovered</p>
        <p class="text-2xl font-bold text-green-600 mt-1"><?= (int)$recoveredCount ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-sm text-gray-500">Recovered Revenue</p>
        <p class="text-2xl font-bold text-green-600 mt-1"><?= formatMoney($recoveredRevenue) ?></p>
    </div>
</div>

<!-- Filter tabs -->
<div class="flex gap-2 mb-4">
    <a href="/admin/abandoned-carts" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= empty($currentStatus) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">All</a>
    <a href="/admin/abandoned-carts?status=active" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $currentStatus === 'active' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Active</a>
    <a href="/admin/abandoned-carts?status=recovered" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $currentStatus === 'recovered' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Recovered</a>
    <a href="/admin/abandoned-carts?status=expired" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $currentStatus === 'expired' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Expired</a>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($carts)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            <p class="text-gray-500">No abandoned carts found.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cart Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emails Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Abandoned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($carts as $cart): ?>
                    <?php $items = json_decode($cart['cart_data'] ?? '[]', true); ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($cart['customer_name'] ?: 'Unknown') ?></div>
                            <div class="text-xs text-gray-500"><?= h($cart['email'] ?: 'No email') ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= formatMoney($cart['subtotal_dkk']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= (int)$cart['emails_sent'] ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= $cart['abandoned_at'] ? formatDate($cart['abandoned_at'], 'd M Y H:i') : '-' ?></td>
                        <td class="px-6 py-4">
                            <?php if ($cart['status'] === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Active</span>
                            <?php elseif ($cart['status'] === 'recovered'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Recovered</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
