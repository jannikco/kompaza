<?php
$pageTitle = 'Billing — Subscriptions';
$currentPage = 'billing';
ob_start();

// Status badge color map for subscription lifecycle states.
$statusColors = [
    'active'              => 'bg-green-900 text-green-300',
    'trialing'           => 'bg-yellow-900 text-yellow-300',
    'past_due'           => 'bg-orange-900 text-orange-300',
    'unpaid'             => 'bg-red-900 text-red-300',
    'canceled'           => 'bg-gray-700 text-gray-400',
    'incomplete'         => 'bg-purple-900 text-purple-300',
    'incomplete_expired' => 'bg-gray-700 text-gray-400',
    'paused'             => 'bg-blue-900 text-blue-300',
];

$statusOptions = ['trialing', 'active', 'past_due', 'unpaid', 'canceled', 'incomplete', 'paused'];

// price_*_usd columns are stored in cents.
$mrr = number_format(($mrrCents ?? 0) / 100, 2);
$activeCount   = $counts['active'] ?? 0;
$trialingCount = $counts['trialing'] ?? 0;
$canceledCount = $counts['canceled'] ?? 0;
?>

<!-- Sub-navigation -->
<div class="flex items-center gap-2 mb-6">
    <a href="/billing/subscriptions" class="px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white">Subscriptions</a>
    <a href="/billing/invoices" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white">Invoices</a>
</div>

<!-- Summary cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Monthly Recurring Revenue</p>
        <p class="mt-2 text-2xl font-semibold text-white">$<?= $mrr ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active</p>
        <p class="mt-2 text-2xl font-semibold text-green-400"><?= (int) $activeCount ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Trialing</p>
        <p class="mt-2 text-2xl font-semibold text-yellow-400"><?= (int) $trialingCount ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Canceled</p>
        <p class="mt-2 text-2xl font-semibold text-gray-400"><?= (int) $canceledCount ?></p>
    </div>
</div>

<!-- Subscriptions table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold text-white">All Subscriptions</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Interval</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Trial Ends</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Current Period End</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($subscriptions)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No subscriptions yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($subscriptions as $sub): ?>
                <?php $statusClass = $statusColors[$sub['status']] ?? 'bg-gray-700 text-gray-400'; ?>
                <tr class="hover:bg-gray-700/50" x-data="{ trialOpen: false, statusOpen: false }">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white"><?= h($sub['tenant_name'] ?? 'Unknown') ?></div>
                        <div class="text-xs text-gray-400"><?= h($sub['tenant_slug'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h($sub['plan_name'] ?? '—') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h(ucfirst($sub['billing_interval'] ?? '')) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= h(ucfirst(str_replace('_', ' ', $sub['status']))) ?>
                        </span>
                        <?php if (!empty($sub['cancel_at_period_end'])): ?>
                        <div class="mt-1 text-xs text-orange-300">Cancels at period end</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400">
                        <?= $sub['trial_ends_at'] ? formatDate($sub['trial_ends_at']) : '—' ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400">
                        <?= $sub['current_period_end'] ? formatDate($sub['current_period_end']) : '—' ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3 relative">
                            <!-- Extend trial -->
                            <button type="button" @click="trialOpen = !trialOpen; statusOpen = false" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Extend Trial</button>
                            <div x-show="trialOpen" x-cloak @click.outside="trialOpen = false"
                                 class="absolute right-0 top-8 z-20 w-56 bg-gray-800 border border-gray-700 rounded-lg shadow-xl p-4 text-left">
                                <form method="POST" action="/billing/extend-trial">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int) $sub['id'] ?>">
                                    <label class="block text-xs font-medium text-gray-400 mb-1">Extend by (days)</label>
                                    <input type="number" name="days" min="1" max="365" value="14"
                                           class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <button type="submit" class="mt-3 w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Extend</button>
                                </form>
                            </div>

                            <!-- Set status -->
                            <button type="button" @click="statusOpen = !statusOpen; trialOpen = false" class="text-blue-400 hover:text-blue-300 text-sm font-medium">Status</button>
                            <div x-show="statusOpen" x-cloak @click.outside="statusOpen = false"
                                 class="absolute right-0 top-8 z-20 w-56 bg-gray-800 border border-gray-700 rounded-lg shadow-xl p-4 text-left">
                                <form method="POST" action="/billing/set-status">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int) $sub['id'] ?>">
                                    <label class="block text-xs font-medium text-gray-400 mb-1">Set status</label>
                                    <select name="status" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <?php foreach ($statusOptions as $opt): ?>
                                        <option value="<?= h($opt) ?>" <?= $sub['status'] === $opt ? 'selected' : '' ?>><?= h(ucfirst(str_replace('_', ' ', $opt))) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="mt-3 w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Update</button>
                                </form>
                            </div>

                            <!-- Cancel -->
                            <?php if ($sub['status'] !== 'canceled'): ?>
                            <form method="POST" action="/billing/cancel-subscription" class="inline"
                                  onsubmit="return confirm('Cancel this subscription immediately? This sets the status to canceled.');">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $sub['id'] ?>">
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-medium">Cancel</button>
                            </form>
                            <?php else: ?>
                            <span class="text-gray-600 text-sm">Canceled</span>
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
