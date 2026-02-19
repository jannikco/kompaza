<?php
$pageTitle = 'Customer: ' . h($customer['name']);
$currentPage = 'customers';
$tenant = currentTenant();
ob_start();
?>

<!-- Breadcrumb & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="/admin/kunder" class="text-sm text-gray-400 hover:text-white transition">&larr; Back to Customers</a>
        <h2 class="text-2xl font-bold text-white mt-1"><?= h($customer['name']) ?></h2>
    </div>
    <div class="flex items-center space-x-3">
        <a href="/admin/kunder/rediger/<?= $customer['id'] ?>" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Customer
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Customer Info -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Customer Details Card -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center mb-6">
                <div class="flex-shrink-0 w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center">
                    <span class="text-xl font-bold text-white"><?= h(mb_strtoupper(mb_substr($customer['name'] ?? '?', 0, 1))) ?></span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-white"><?= h($customer['name']) ?></h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($customer['status'] ?? 'active') === 'active' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300' ?>">
                        <?= ucfirst($customer['status'] ?? 'active') ?>
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</label>
                    <p class="text-sm text-gray-200 mt-0.5">
                        <a href="mailto:<?= h($customer['email']) ?>" class="text-indigo-400 hover:text-indigo-300"><?= h($customer['email']) ?></a>
                    </p>
                </div>

                <?php if (!empty($customer['phone'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</label>
                    <p class="text-sm text-gray-200 mt-0.5"><?= h($customer['phone']) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer['company'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Company</label>
                    <p class="text-sm text-gray-200 mt-0.5"><?= h($customer['company']) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer['cvr_number'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">CVR Number</label>
                    <p class="text-sm text-gray-200 mt-0.5"><?= h($customer['cvr_number']) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($customer['address_line1'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</label>
                    <p class="text-sm text-gray-200 mt-0.5">
                        <?= h($customer['address_line1']) ?><br>
                        <?php if (!empty($customer['address_line2'])): ?>
                            <?= h($customer['address_line2']) ?><br>
                        <?php endif; ?>
                        <?= h($customer['postal_code'] ?? '') ?> <?= h($customer['city'] ?? '') ?><br>
                        <?= h($customer['country'] ?? 'DK') ?>
                    </p>
                </div>
                <?php endif; ?>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Since</label>
                    <p class="text-sm text-gray-200 mt-0.5"><?= formatDate($customer['created_at'], 'd M Y, H:i') ?></p>
                </div>

                <?php if (!empty($customer['last_login_at'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</label>
                    <p class="text-sm text-gray-200 mt-0.5"><?= formatDate($customer['last_login_at'], 'd M Y, H:i') ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Email Signups -->
        <?php if (!empty($signups)): ?>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Email Signups</h3>
            <div class="space-y-3">
                <?php foreach ($signups as $signup): ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-0">
                    <div>
                        <p class="text-sm text-gray-200"><?= h($signup['source'] ?? 'Newsletter') ?></p>
                        <p class="text-xs text-gray-500"><?= formatDate($signup['created_at']) ?></p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Subscribed</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Orders -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Recent Orders -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Orders</h3>
                <span class="text-sm text-gray-400"><?= count($orders ?? []) ?> total</span>
            </div>

            <?php if (empty($orders)): ?>
                <div class="p-8 text-center">
                    <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-gray-400 text-sm">No orders yet for this customer.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-700 bg-gray-800/50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-900 text-yellow-300',
                                'paid' => 'bg-green-900 text-green-300',
                                'processing' => 'bg-blue-900 text-blue-300',
                                'shipped' => 'bg-indigo-900 text-indigo-300',
                                'delivered' => 'bg-green-900 text-green-300',
                                'cancelled' => 'bg-red-900 text-red-300',
                                'refunded' => 'bg-gray-700 text-gray-300',
                            ];
                            ?>
                            <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-750 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-white">#<?= h($order['order_number']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$order['status']] ?? 'bg-gray-700 text-gray-300' ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300"><?= formatMoney($order['total_dkk']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($order['created_at']) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="/admin/ordrer/<?= $order['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notes Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Notes</h3>
            <form method="POST" action="/admin/kunder/noter/<?= $customer['id'] ?>">
                <?= csrfField() ?>
                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Add internal notes about this customer..."><?= h($customer['notes'] ?? '') ?></textarea>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        Save Notes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
