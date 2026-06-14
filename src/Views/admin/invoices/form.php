<?php
$isEdit = !empty($invoice);
$pageTitle = $isEdit ? 'Edit Invoice' : 'New Invoice';
$currentPage = 'invoices';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="/admin/invoices" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-900"><?= h($pageTitle) ?></h2>
        <?php if ($isEdit): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                <?php
                $statusColors = [
                    'draft' => 'bg-gray-100 text-gray-700',
                    'sent' => 'bg-blue-100 text-blue-700',
                    'viewed' => 'bg-purple-100 text-purple-700',
                    'paid' => 'bg-green-100 text-green-700',
                    'partially_paid' => 'bg-yellow-100 text-yellow-700',
                    'overdue' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-500',
                ];
                echo $statusColors[$invoice['status']] ?? 'bg-gray-100 text-gray-700';
                ?>">
                <?= ucfirst(str_replace('_', ' ', $invoice['status'])) ?>
            </span>
        <?php endif; ?>
    </div>
    <?php if ($isEdit): ?>
    <div class="flex gap-2">
        <?php if (in_array($invoice['status'], ['draft', 'sent', 'viewed'])): ?>
        <form method="POST" action="/admin/invoices/send" class="inline">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <?= $invoice['status'] === 'draft' ? 'Send Invoice' : 'Resend' ?>
            </button>
        </form>
        <?php endif; ?>
        <?php if (!in_array($invoice['status'], ['draft', 'paid', 'cancelled'])): ?>
        <form method="POST" action="/admin/invoices/send-reminder" class="inline">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition">
                Send Reminder
            </button>
        </form>
        <?php endif; ?>
        <?php if ($invoice['status'] === 'draft'): ?>
        <form method="POST" action="/admin/invoices/delete" onsubmit="return confirm('Delete this draft invoice?')">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">Delete</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/invoices/update' : '/admin/invoices/store' ?>" x-data="invoiceForm()">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$invoice['id'] ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Invoice Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Customer</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" value="<?= h($invoice['customer_name'] ?? $customer['name'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="customer_email" value="<?= h($invoice['customer_email'] ?? $customer['email'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="customer_phone" value="<?= h($invoice['customer_phone'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                        <input type="text" name="customer_company" value="<?= h($invoice['customer_company'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVR Number</label>
                        <input type="text" name="customer_cvr" value="<?= h($invoice['customer_cvr'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <?php $addr = $isEdit ? json_decode($invoice['billing_address'] ?? '{}', true) : []; ?>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                        <input type="text" name="billing_street" value="<?= h($addr['street'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="billing_city" value="<?= h($addr['city'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                        <input type="text" name="billing_postal" value="<?= h($addr['postal'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <input type="text" name="billing_country" value="<?= h($addr['country'] ?? 'DK') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Line Items</h3>
                <div class="space-y-3">
                    <template x-for="(item, index) in lineItems" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-start">
                            <div class="col-span-6">
                                <input type="text" :name="'item_description['+index+']'" x-model="item.description" placeholder="Description" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <input type="number" :name="'item_quantity['+index+']'" x-model.number="item.quantity" step="0.01" min="0.01" placeholder="Qty"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <input type="number" :name="'item_unit_price['+index+']'" x-model.number="item.unit_price" step="0.01" min="0" placeholder="Price"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                            </div>
                            <div class="col-span-1 flex items-center justify-end">
                                <span class="text-sm font-medium text-gray-700" x-text="(item.quantity * item.unit_price).toFixed(2)"></span>
                            </div>
                            <div class="col-span-1 flex items-center justify-center">
                                <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 transition" x-show="lineItems.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addItem()" class="mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                    + Add Line Item
                </button>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (visible to customer)</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500"><?= h($invoice['notes'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes (private)</label>
                        <textarea name="internal_notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500"><?= h($invoice['internal_notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Summary & Settings -->
        <div class="space-y-6">
            <!-- Invoice Details -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Invoice Details</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                        <input type="text" name="invoice_number" value="<?= h($invoiceNumber) ?>" readonly
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
                        <input type="date" name="issue_date" value="<?= h($invoice['issue_date'] ?? date('Y-m-d')) ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="<?= h($invoice['due_date'] ?? date('Y-m-d', strtotime('+14 days'))) ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms</label>
                        <input type="text" name="payment_terms" value="<?= h($invoice['payment_terms'] ?? 'Net 14') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" x-model.number="taxRate" step="0.01" min="0" max="100"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                        <input type="number" name="discount_dkk" x-model.number="discount" step="0.01" min="0"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
                        <input type="text" name="footer_text" value="<?= h($invoice['footer_text'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Totals</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span x-text="subtotal.toFixed(2) + ' DKK'" class="font-medium"></span></div>
                    <div class="flex justify-between" x-show="discount > 0"><span class="text-gray-600">Discount</span><span x-text="'-' + discount.toFixed(2) + ' DKK'" class="font-medium text-green-600"></span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Tax (<span x-text="taxRate"></span>%)</span><span x-text="tax.toFixed(2) + ' DKK'" class="font-medium"></span></div>
                    <div class="flex justify-between border-t border-gray-200 pt-2"><span class="font-bold text-gray-900">Total</span><span x-text="total.toFixed(2) + ' DKK'" class="font-bold text-gray-900"></span></div>
                </div>
            </div>

            <button type="submit" class="w-full px-4 py-3 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <?= $isEdit ? 'Update Invoice' : 'Create Invoice' ?>
            </button>
        </div>
    </div>
</form>

<?php if ($isEdit && !in_array($invoice['status'], ['draft', 'cancelled'])): ?>
<!-- Record Payment Section -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <!-- Payment History -->
        <?php $payments = \App\Models\Invoice::getPayments($invoice['id']); ?>
        <?php if (!empty($payments)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Payment History</h3>
            <div class="space-y-3">
                <?php foreach ($payments as $p): ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?= formatMoney($p['amount_dkk']) ?></p>
                        <p class="text-xs text-gray-500"><?= h($p['payment_method'] ?? 'N/A') ?> <?= $p['payment_reference'] ? '- ' . h($p['payment_reference']) : '' ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($p['paid_at'])) ?></p>
                        <?php if ($p['recorded_by_name']): ?>
                        <p class="text-xs text-gray-400">by <?= h($p['recorded_by_name']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div>
        <?php if ((float)$invoice['amount_paid_dkk'] < (float)$invoice['total_dkk']): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Record Payment</h3>
            <form method="POST" action="/admin/invoices/record-payment">
                <?= csrfField() ?>
                <input type="hidden" name="invoice_id" value="<?= (int)$invoice['id'] ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (DKK)</label>
                        <input type="number" name="amount_dkk" step="0.01" min="0.01" max="<?= (float)$invoice['total_dkk'] - (float)$invoice['amount_paid_dkk'] ?>"
                               value="<?= (float)$invoice['total_dkk'] - (float)$invoice['amount_paid_dkk'] ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="cash">Cash</option>
                            <option value="mobilepay">MobilePay</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                        <input type="text" name="payment_reference" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500" placeholder="Transaction ID, check #, etc.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" name="notes" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-green-50 rounded-xl border border-green-200 p-6 text-center">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-green-700 font-medium">Fully Paid</p>
            <p class="text-green-600 text-sm mt-1"><?= formatMoney($invoice['amount_paid_dkk']) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function invoiceForm() {
    return {
        lineItems: <?= json_encode(!empty($items) ? array_map(fn($it) => [
            'description' => $it['description'],
            'quantity' => (float)$it['quantity'],
            'unit_price' => (float)$it['unit_price_dkk'],
        ], $items) : [['description' => '', 'quantity' => 1, 'unit_price' => 0]]) ?>,
        taxRate: <?= (float)($invoice['tax_rate'] ?? 25) ?>,
        discount: <?= (float)($invoice['discount_dkk'] ?? 0) ?>,

        get subtotal() {
            return this.lineItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        },
        get discountedSubtotal() {
            return Math.max(0, this.subtotal - this.discount);
        },
        get tax() {
            return this.discountedSubtotal * (this.taxRate / 100);
        },
        get total() {
            return this.discountedSubtotal + this.tax;
        },

        addItem() {
            this.lineItems.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            this.lineItems.splice(index, 1);
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
