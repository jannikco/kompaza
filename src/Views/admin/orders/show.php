<?php
$pageTitle = 'Order #' . h($order['order_number']);
$currentPage = 'orders';
$tenant = currentTenant();

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-700',
    'paid' => 'bg-green-100 text-green-700',
    'processing' => 'bg-blue-100 text-blue-700',
    'shipped' => 'bg-indigo-100 text-indigo-700',
    'delivered' => 'bg-green-100 text-green-700',
    'cancelled' => 'bg-red-100 text-red-700',
    'refunded' => 'bg-gray-100 text-gray-700',
];

$allStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

ob_start();
?>

<!-- Breadcrumb & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="/admin/ordrer" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Orders</a>
        <div class="flex items-center gap-3 mt-1">
            <h2 class="text-2xl font-bold text-gray-900">Order #<?= h($order['order_number']) ?></h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                <?= ucfirst($order['status']) ?>
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-1">Placed on <?= formatDate($order['created_at'], 'd M Y \a\t H:i') ?></p>
    </div>
    <div class="flex items-center space-x-3">
        <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'refunded'): ?>
        <button onclick="document.getElementById('status-section').scrollIntoView({ behavior: 'smooth' })" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Update Status
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Order Header Summary -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Order Total</p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= formatMoney($order['total_dkk']) ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= h(ucfirst($order['payment_method'] ?? 'N/A')) ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Items</p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= count($items ?? []) ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= h($order['currency'] ?? 'DKK') ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Left Column: Customer & Address Info -->
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer</h3>
            <div class="space-y-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-gray-900"><?= h(mb_strtoupper(mb_substr($order['customer_name'] ?? '?', 0, 1))) ?></span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900"><?= h($order['customer_name'] ?? 'Guest') ?></p>
                        <?php if (!empty($order['customer_email'])): ?>
                            <a href="mailto:<?= h($order['customer_email']) ?>" class="text-xs text-indigo-600 hover:text-indigo-500"><?= h($order['customer_email']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($order['customer_phone'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= h($order['customer_phone']) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($order['customer_company'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Company</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= h($order['customer_company']) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($order['customer_id'])): ?>
                <div class="pt-2">
                    <a href="/admin/kunder/<?= $order['customer_id'] ?>" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                        View Customer Profile
                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Billing Address -->
        <?php if (!empty($order['billing_address'])): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Billing Address</h3>
            <p class="text-sm text-gray-600 whitespace-pre-line"><?= h($order['billing_address']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Shipping Address -->
        <?php if (!empty($order['shipping_address'])): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Shipping Address</h3>
            <p class="text-sm text-gray-600 whitespace-pre-line"><?= h($order['shipping_address']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Payment Reference -->
        <?php if (!empty($order['payment_reference'])): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Payment Reference</h3>
            <p class="text-sm text-gray-600 font-mono break-all"><?= h($order['payment_reference']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Invoice Info -->
        <?php if ($order['payment_method'] === 'invoice'): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Invoice</h3>
            <?php if (!empty($order['invoice_number'])): ?>
            <div class="space-y-2">
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Number</label>
                    <p class="text-sm text-gray-700 mt-0.5 font-mono"><?= h($order['invoice_number']) ?></p>
                </div>
                <?php if (!empty($order['invoice_due_date'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= formatDate($order['invoice_due_date'], 'd M Y') ?></p>
                </div>
                <?php endif; ?>
                <div class="pt-2">
                    <a href="/admin/ordrer/faktura?id=<?= $order['id'] ?>" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                        View Invoice
                    </a>
                </div>
            </div>
            <?php else: ?>
            <form method="POST" action="/admin/ordrer/generer-faktura">
                <?= csrfField() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <p class="text-sm text-gray-500 mb-3">No invoice generated yet.</p>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Generate Invoice
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Order Items & Totals -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?= h($item['product_name']) ?></div>
                                    <?php if (!empty($item['product_sku'])): ?>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">SKU: <?= h($item['product_sku']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['is_digital'])): ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 mt-1">Digital</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 text-center"><?= (int)$item['quantity'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600 text-right"><?= formatMoney($item['unit_price_dkk']) ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right"><?= formatMoney($item['total_price_dkk']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">No items in this order.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="max-w-xs ml-auto space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="text-gray-700"><?= formatMoney($order['subtotal_dkk'] ?? $order['total_dkk']) ?></span>
                    </div>
                    <?php if (!empty($order['tax_dkk']) && $order['tax_dkk'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tax (VAT)</span>
                        <span class="text-gray-700"><?= formatMoney($order['tax_dkk']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['shipping_dkk']) && $order['shipping_dkk'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Shipping</span>
                        <span class="text-gray-700"><?= formatMoney($order['shipping_dkk']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['discount_dkk']) && $order['discount_dkk'] > 0): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Discount</span>
                        <span class="text-green-600">-<?= formatMoney($order['discount_dkk']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="text-base font-semibold text-gray-900">Total</span>
                        <span class="text-base font-bold text-gray-900"><?= formatMoney($order['total_dkk']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Notes -->
        <?php if (!empty($order['notes'])): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Order Notes</h3>
            <p class="text-sm text-gray-600 whitespace-pre-line"><?= h($order['notes']) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Status Update Section -->
<div id="status-section" class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Order Status</h3>
    <form method="POST" action="/admin/ordrer/status">
        <?= csrfField() ?>
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="new_status" class="block text-sm font-medium text-gray-700 mb-1.5">New Status</label>
                <select id="new_status" name="status" x-data="{ status: '<?= h($order['status']) ?>' }" x-model="status"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <?php foreach ($allStatuses as $status): ?>
                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-gray-700 mb-1.5">Note (optional)</label>
                <input type="text" id="note" name="note"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Add a note about this status change...">
            </div>
        </div>

        <!-- Shipping fields (shown for shipped/delivered status) -->
        <div x-data="{ showShipping: <?= in_array($order['status'], ['shipped', 'delivered']) ? 'true' : 'false' ?> }" class="mt-4">
            <div x-show="showShipping || document.getElementById('new_status')?.value === 'shipped'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200">
                    <div>
                        <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-1.5">Tracking Number</label>
                        <input type="text" id="tracking_number" name="tracking_number" value="<?= h($order['tracking_number'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                               placeholder="Enter tracking number">
                    </div>
                    <div>
                        <label for="tracking_url" class="block text-sm font-medium text-gray-700 mb-1.5">Tracking URL</label>
                        <input type="url" id="tracking_url" name="tracking_url" value="<?= h($order['tracking_url'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                               placeholder="https://tracking.example.com/...">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Update Status
            </button>
        </div>
    </form>
</div>

<!-- Order Status History Timeline -->
<?php if (!empty($statusHistory)): ?>
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Status History</h3>

    <div class="relative">
        <!-- Timeline line -->
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-300"></div>

        <div class="space-y-6">
            <?php foreach ($statusHistory as $index => $history): ?>
            <div class="relative flex items-start pl-10">
                <!-- Timeline dot -->
                <div class="absolute left-2.5 w-3 h-3 rounded-full mt-1.5 <?= $index === 0 ? 'bg-indigo-500 ring-4 ring-indigo-900/50' : 'bg-gray-600' ?>"></div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$history['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                            <?= ucfirst($history['status']) ?>
                        </span>
                        <span class="text-xs text-gray-500"><?= formatDate($history['created_at'], 'd M Y, H:i') ?></span>
                    </div>
                    <?php if (!empty($history['note'])): ?>
                    <p class="text-sm text-gray-500 mt-1"><?= h($history['note']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Alpine.js: Show shipping fields when status changes to shipped -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('new_status');
    const shippingSection = document.querySelector('[x-data*="showShipping"]');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const showShipping = this.value === 'shipped' || this.value === 'delivered';
            if (shippingSection && shippingSection.__x) {
                shippingSection.__x.$data.showShipping = showShipping;
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
