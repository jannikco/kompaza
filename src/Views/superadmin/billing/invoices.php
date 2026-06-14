<?php
$pageTitle = 'Billing — Invoices';
$currentPage = 'billing';
ob_start();

// Invoice status badge colors.
$statusColors = [
    'paid'          => 'bg-green-900 text-green-300',
    'open'          => 'bg-yellow-900 text-yellow-300',
    'draft'         => 'bg-gray-700 text-gray-400',
    'uncollectible' => 'bg-red-900 text-red-300',
    'void'          => 'bg-gray-700 text-gray-500',
];

// Format cents to a currency amount string (e.g. 7900 -> 79.00).
$money = static function ($cents, $currency) {
    return number_format(((int) $cents) / 100, 2) . ' ' . strtoupper($currency ?? 'usd');
};

$paid        = number_format((($totals['paid_cents'] ?? 0) / 100), 2);
$outstanding = number_format((($totals['outstanding_cents'] ?? 0) / 100), 2);
$totalCount  = (int) ($totals['total_count'] ?? 0);
?>

<!-- Sub-navigation -->
<div class="flex items-center gap-2 mb-6">
    <a href="/billing/subscriptions" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white">Subscriptions</a>
    <a href="/billing/invoices" class="px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white">Invoices</a>
</div>

<!-- Summary cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Invoices</p>
        <p class="mt-2 text-2xl font-semibold text-white"><?= $totalCount ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Collected</p>
        <p class="mt-2 text-2xl font-semibold text-green-400">$<?= $paid ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Outstanding</p>
        <p class="mt-2 text-2xl font-semibold text-yellow-400">$<?= $outstanding ?></p>
    </div>
</div>

<!-- Invoices table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold text-white">Platform Invoices</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Invoice</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Period</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($invoices)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No invoices yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                <?php $statusClass = $statusColors[$inv['status']] ?? 'bg-gray-700 text-gray-400'; ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white">#<?= (int) $inv['id'] ?></div>
                        <?php if (!empty($inv['stripe_invoice_id'])): ?>
                        <div class="text-xs text-gray-400 font-mono truncate max-w-[180px]" title="<?= h($inv['stripe_invoice_id']) ?>"><?= h($inv['stripe_invoice_id']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-300"><?= h($inv['tenant_name'] ?? 'Unknown') ?></div>
                        <div class="text-xs text-gray-500"><?= h($inv['tenant_slug'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h($money($inv['amount_cents'], $inv['currency'])) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= h(ucfirst($inv['status'])) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-400">
                        <?php if ($inv['period_start'] && $inv['period_end']): ?>
                        <?= formatDate($inv['period_start']) ?> &ndash; <?= formatDate($inv['period_end']) ?>
                        <?php else: ?>
                        &mdash;
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($inv['created_at']) ?></td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <?php if (!empty($inv['invoice_url'])): ?>
                            <a href="<?= h($inv['invoice_url']) ?>" target="_blank" rel="noopener" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">View</a>
                            <?php endif; ?>
                            <?php if ($inv['status'] !== 'paid'): ?>
                            <form method="POST" action="/billing/mark-invoice-paid" class="inline"
                                  onsubmit="return confirm('Mark this invoice as paid?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $inv['id'] ?>">
                                <button type="submit" class="text-green-400 hover:text-green-300 text-sm font-medium">Mark Paid</button>
                            </form>
                            <?php else: ?>
                            <span class="text-gray-600 text-sm">Paid<?= $inv['paid_at'] ? ' · ' . formatDate($inv['paid_at']) : '' ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
