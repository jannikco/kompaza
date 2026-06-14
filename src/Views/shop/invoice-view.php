<?php
$pageTitle = 'Invoice ' . $invoice['invoice_number'];
$metaDescription = 'View your invoice';
$currency = $invoice['tenant_currency'] ?? 'DKK';
$companyName = $invoice['tenant_company'] ?? 'Company';
$addr = json_decode($invoice['billing_address'] ?? '{}', true);

$statusColors = [
    'sent' => 'bg-blue-100 text-blue-700',
    'viewed' => 'bg-blue-100 text-blue-700',
    'paid' => 'bg-green-100 text-green-700',
    'partially_paid' => 'bg-yellow-100 text-yellow-700',
    'overdue' => 'bg-red-100 text-red-700',
];
$badgeClass = $statusColors[$invoice['status']] ?? 'bg-gray-100 text-gray-700';
$amountDue = (float)$invoice['total_dkk'] - (float)$invoice['amount_paid_dkk'];

ob_start();
?>

<section class="py-12 lg:py-16 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <!-- Print button -->
        <div class="flex justify-end mb-4">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition print:hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="p-8 border-b border-gray-200">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Invoice</h1>
                        <p class="text-gray-500 mt-1"><?= h($companyName) ?></p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $badgeClass ?>">
                        <?= ucfirst(str_replace('_', ' ', $invoice['status'])) ?>
                    </span>
                </div>
            </div>

            <div class="p-8">
                <!-- Meta -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Invoice #</p>
                        <p class="text-sm font-medium text-gray-900 mt-0.5"><?= h($invoice['invoice_number']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Issue Date</p>
                        <p class="text-sm text-gray-900 mt-0.5"><?= date('d/m/Y', strtotime($invoice['issue_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Due Date</p>
                        <p class="text-sm mt-0.5 <?= strtotime($invoice['due_date']) < time() && $amountDue > 0 ? 'text-red-600 font-medium' : 'text-gray-900' ?>">
                            <?= date('d/m/Y', strtotime($invoice['due_date'])) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Amount Due</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5"><?= number_format($amountDue, 2, ',', '.') ?> <?= h($currency) ?></p>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">From</p>
                        <p class="text-sm text-gray-900"><?= h($companyName) ?></p>
                        <?php if ($invoice['tenant_address']): ?><p class="text-sm text-gray-600"><?= h($invoice['tenant_address']) ?></p><?php endif; ?>
                        <?php if ($invoice['tenant_email']): ?><p class="text-sm text-gray-600"><?= h($invoice['tenant_email']) ?></p><?php endif; ?>
                        <?php if ($invoice['tenant_cvr']): ?><p class="text-sm text-gray-600">CVR: <?= h($invoice['tenant_cvr']) ?></p><?php endif; ?>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">To</p>
                        <p class="text-sm text-gray-900"><?= h($invoice['customer_name']) ?></p>
                        <?php if ($invoice['customer_company']): ?><p class="text-sm text-gray-600"><?= h($invoice['customer_company']) ?></p><?php endif; ?>
                        <p class="text-sm text-gray-600"><?= h($invoice['customer_email']) ?></p>
                        <?php if (!empty($addr['street'])): ?><p class="text-sm text-gray-600"><?= h($addr['street']) ?></p><?php endif; ?>
                        <?php if (!empty($addr['postal']) || !empty($addr['city'])): ?>
                            <p class="text-sm text-gray-600"><?= h(($addr['postal'] ?? '') . ' ' . ($addr['city'] ?? '')) ?></p>
                        <?php endif; ?>
                        <?php if ($invoice['customer_cvr']): ?><p class="text-sm text-gray-600">CVR: <?= h($invoice['customer_cvr']) ?></p><?php endif; ?>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="w-full mb-8">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="pb-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="pb-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-3 text-sm text-gray-900"><?= h($item['description']) ?></td>
                            <td class="py-3 text-sm text-gray-600 text-right"><?= (float)$item['quantity'] == (int)$item['quantity'] ? (int)$item['quantity'] : number_format($item['quantity'], 2) ?></td>
                            <td class="py-3 text-sm text-gray-600 text-right"><?= number_format($item['unit_price_dkk'], 2, ',', '.') ?></td>
                            <td class="py-3 text-sm font-medium text-gray-900 text-right"><?= number_format($item['total_dkk'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span><?= number_format($invoice['subtotal_dkk'], 2, ',', '.') ?> <?= h($currency) ?></span></div>
                        <?php if ((float)$invoice['discount_dkk'] > 0): ?>
                        <div class="flex justify-between text-green-600"><span>Discount</span><span>-<?= number_format($invoice['discount_dkk'], 2, ',', '.') ?> <?= h($currency) ?></span></div>
                        <?php endif; ?>
                        <div class="flex justify-between"><span class="text-gray-600">Tax (<?= (float)$invoice['tax_rate'] ?>%)</span><span><?= number_format($invoice['tax_dkk'], 2, ',', '.') ?> <?= h($currency) ?></span></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold"><span>Total</span><span><?= number_format($invoice['total_dkk'], 2, ',', '.') ?> <?= h($currency) ?></span></div>
                        <?php if ((float)$invoice['amount_paid_dkk'] > 0): ?>
                        <div class="flex justify-between text-green-600"><span>Paid</span><span>-<?= number_format($invoice['amount_paid_dkk'], 2, ',', '.') ?> <?= h($currency) ?></span></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold"><span>Amount Due</span><span><?= number_format($amountDue, 2, ',', '.') ?> <?= h($currency) ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notes -->
                <?php if ($invoice['notes']): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Notes</p>
                    <p class="text-sm text-gray-600"><?= nl2br(h($invoice['notes'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Payment Terms -->
                <?php if ($invoice['payment_terms']): ?>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400">Payment terms: <?= h($invoice['payment_terms']) ?>. Please reference invoice number <strong><?= h($invoice['invoice_number']) ?></strong> when making payment.</p>
                </div>
                <?php endif; ?>

                <?php if ($invoice['footer_text']): ?>
                <div class="mt-4">
                    <p class="text-xs text-gray-400"><?= h($invoice['footer_text']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
