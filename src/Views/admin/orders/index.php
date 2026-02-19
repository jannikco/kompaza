<?php
$pageTitle = 'Orders';
$currentPage = 'orders';
$tenant = currentTenant();

$statusColors = [
    'pending' => 'bg-yellow-900 text-yellow-300 border-yellow-700',
    'paid' => 'bg-green-900 text-green-300 border-green-700',
    'processing' => 'bg-blue-900 text-blue-300 border-blue-700',
    'shipped' => 'bg-indigo-900 text-indigo-300 border-indigo-700',
    'delivered' => 'bg-green-900 text-green-300 border-green-700',
    'cancelled' => 'bg-red-900 text-red-300 border-red-700',
    'refunded' => 'bg-gray-700 text-gray-300 border-gray-600',
];

$statusBadgeColors = [
    'pending' => 'bg-yellow-900 text-yellow-300',
    'paid' => 'bg-green-900 text-green-300',
    'processing' => 'bg-blue-900 text-blue-300',
    'shipped' => 'bg-indigo-900 text-indigo-300',
    'delivered' => 'bg-green-900 text-green-300',
    'cancelled' => 'bg-red-900 text-red-300',
    'refunded' => 'bg-gray-700 text-gray-300',
];

$allStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Orders</h2>
        <p class="text-sm text-gray-400 mt-1">View and manage customer orders.</p>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="mb-6 flex flex-wrap gap-2">
    <a href="/admin/ordrer"
       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition border <?= empty($statusFilter) ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
        All
        <?php if (empty($statusFilter)): ?>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-indigo-500 text-white"><?= count($orders ?? []) ?></span>
        <?php endif; ?>
    </a>
    <?php foreach ($allStatuses as $status): ?>
    <a href="/admin/ordrer?status=<?= $status ?>"
       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition border <?= ($statusFilter ?? '') === $status ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
        <?= ucfirst($status) ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($orders)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <p class="text-gray-400">
                <?php if (!empty($statusFilter)): ?>
                    No <?= $statusFilter ?> orders found.
                <?php else: ?>
                    No orders yet. Orders will appear here when customers make purchases.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700 bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4">
                            <a href="/admin/ordrer/<?= $order['id'] ?>" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">
                                #<?= h($order['order_number']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?= h($order['customer_name'] ?? 'Guest') ?></div>
                            <div class="text-xs text-gray-400"><?= h($order['customer_email'] ?? '') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadgeColors[$order['status']] ?? 'bg-gray-700 text-gray-300' ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-white"><?= formatMoney($order['total_dkk']) ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($order['payment_method'])): ?>
                                <span class="text-sm text-gray-300"><?= h(ucfirst($order['payment_method'])) ?></span>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($order['created_at'], 'd M Y, H:i') ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/ordrer/<?= $order['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-400">
                Showing <?= (($currentPageNum - 1) * $perPage) + 1 ?> to <?= min($currentPageNum * $perPage, $totalOrders) ?> of <?= $totalOrders ?> orders
            </div>
            <div class="flex items-center space-x-1">
                <?php if ($currentPageNum > 1): ?>
                    <a href="/admin/ordrer?page=<?= $currentPageNum - 1 ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-1.5 text-sm text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $currentPageNum - 2); $i <= min($totalPages, $currentPageNum + 2); $i++): ?>
                    <a href="/admin/ordrer?page=<?= $i ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>"
                       class="px-3 py-1.5 text-sm rounded-lg transition <?= $i === $currentPageNum ? 'bg-indigo-600 text-white' : 'text-gray-300 bg-gray-700 hover:bg-gray-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPageNum < $totalPages): ?>
                    <a href="/admin/ordrer?page=<?= $currentPageNum + 1 ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?>" class="px-3 py-1.5 text-sm text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
