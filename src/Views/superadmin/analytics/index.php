<?php
$pageTitle = 'Analytics';
$currentPage = 'analytics';
ob_start();
?>

<!-- KPI cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Monthly Recurring Revenue</p>
        <p class="text-3xl font-bold text-green-400">$<?= number_format($mrrUsd, 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Active &amp; trialing subscriptions</p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Annual Recurring Revenue</p>
        <p class="text-3xl font-bold text-purple-400">$<?= number_format($arrUsd, 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">MRR &times; 12</p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Active Tenants</p>
        <p class="text-3xl font-bold text-blue-400"><?= number_format($activeTenants) ?></p>
        <p class="text-xs text-gray-500 mt-1"><?= number_format($totalTenants) ?> total &middot; <?= number_format($trialTenants) ?> in trial</p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Trial &rarr; Active Conversion</p>
        <p class="text-3xl font-bold text-indigo-400"><?= number_format($conversionRate, 1) ?>%</p>
        <p class="text-xs text-gray-500 mt-1"><?= number_format($activeTenants) ?> active of <?= number_format($activeTenants + $trialTenants) ?></p>
    </div>
</div>

<!-- Secondary KPI row -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Total Subscriptions</p>
        <p class="text-2xl font-bold text-white"><?= number_format($totalSubs) ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Active Paying Tenants (with revenue)</p>
        <p class="text-2xl font-bold text-white"><?= number_format(count($topTenants)) ?>+</p>
    </div>
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <p class="text-sm text-gray-400 mb-1">Total Page Views</p>
        <?php if ($totalPageViews === null): ?>
        <p class="text-2xl font-bold text-gray-500">N/A</p>
        <p class="text-xs text-gray-500 mt-1">Tracking not yet enabled</p>
        <?php else: ?>
        <p class="text-2xl font-bold text-white"><?= number_format($totalPageViews) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- New tenants by month -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-lg font-semibold text-white mb-1">New Tenants by Month</h2>
    <p class="text-sm text-gray-400 mb-6">Sign-ups over the last 12 months</p>
    <div class="flex items-end justify-between gap-2 h-48">
        <?php foreach ($newTenantsByMonth as $m): ?>
        <?php $heightPct = $maxMonthlyTenants > 0 ? round(($m['count'] / $maxMonthlyTenants) * 100) : 0; ?>
        <div class="flex-1 flex flex-col items-center justify-end h-full">
            <span class="text-xs text-gray-300 mb-1"><?= $m['count'] ?></span>
            <div class="w-full bg-gray-700 rounded-t-md flex items-end" style="height: 100%;">
                <div class="w-full bg-indigo-500 rounded-t-md transition-all" style="height: <?= $heightPct ?>%; min-height: <?= $m['count'] > 0 ? '4px' : '0' ?>;"></div>
            </div>
            <span class="text-[10px] text-gray-500 mt-2 text-center leading-tight"><?= h($m['label']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Subscriptions by plan -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-white mb-1">Subscriptions by Plan</h2>
        <p class="text-sm text-gray-400 mb-6">Active &amp; trialing subscribers per plan</p>
        <?php if (empty($subsByPlan)): ?>
        <p class="text-sm text-gray-500">No subscription plans configured yet.</p>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($subsByPlan as $p): ?>
            <?php
                $cnt = (int)$p['subscriber_count'];
                $pct = $maxPlanSubs > 0 ? round(($cnt / $maxPlanSubs) * 100) : 0;
            ?>
            <div>
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-300"><?= h($p['name']) ?>
                        <span class="text-gray-500 text-xs">$<?= number_format((float)$p['price_monthly_usd'] / 100, 0) ?>/mo</span>
                    </span>
                    <span class="text-white font-semibold"><?= $cnt ?></span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div class="bg-indigo-500 h-2.5 rounded-full" style="width: <?= $pct ?>%; min-width: <?= $cnt > 0 ? '6px' : '0' ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Subscriptions by status -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-white mb-1">Subscriptions by Status</h2>
        <p class="text-sm text-gray-400 mb-6">Distribution across all subscriptions</p>
        <?php
        $subStatusMeta = [
            'active'   => ['label' => 'Active',    'color' => 'bg-green-500'],
            'trialing' => ['label' => 'Trialing',  'color' => 'bg-blue-500'],
            'past_due' => ['label' => 'Past Due',  'color' => 'bg-yellow-500'],
            'canceled' => ['label' => 'Canceled',  'color' => 'bg-red-500'],
            'unpaid'   => ['label' => 'Unpaid',    'color' => 'bg-red-400'],
        ];
        $subStatusBase = $totalSubs > 0 ? $totalSubs : 1;
        // Include any unexpected statuses present in the data.
        foreach ($subStatusCounts as $st => $c) {
            if (!isset($subStatusMeta[$st])) {
                $subStatusMeta[$st] = ['label' => ucfirst(str_replace('_', ' ', $st)), 'color' => 'bg-gray-500'];
            }
        }
        ?>
        <?php if ($totalSubs === 0): ?>
        <p class="text-sm text-gray-500">No subscriptions yet.</p>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($subStatusMeta as $st => $meta): ?>
            <?php
                $cnt = $subStatusCounts[$st] ?? 0;
                if ($cnt === 0) continue;
                $pct = round(($cnt / $subStatusBase) * 100);
            ?>
            <div>
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-300"><?= h($meta['label']) ?></span>
                    <span class="text-white font-semibold"><?= $cnt ?> <span class="text-gray-500 text-xs">(<?= $pct ?>%)</span></span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div class="<?= $meta['color'] ?> h-2.5 rounded-full" style="width: <?= $pct ?>%; min-width: 6px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Feature adoption -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <div class="flex items-baseline justify-between mb-1">
        <h2 class="text-lg font-semibold text-white">Feature Adoption</h2>
        <span class="text-xs text-gray-500">Base: <?= number_format($activeTenantCount) ?> active tenant<?= $activeTenantCount === 1 ? '' : 's' ?></span>
    </div>
    <p class="text-sm text-gray-400 mb-6">Share of active tenants with each feature enabled</p>
    <?php if ($activeTenantCount === 0): ?>
    <p class="text-sm text-gray-500">No active tenants to measure feature adoption.</p>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
        <?php foreach ($featureAdoption as $f): ?>
        <div>
            <div class="flex justify-between items-center text-sm mb-1">
                <span class="text-gray-300"><?= h($f['label']) ?></span>
                <span class="text-white font-semibold"><?= number_format($f['pct'], 1) ?>%
                    <span class="text-gray-500 text-xs">(<?= $f['enabled'] ?>)</span>
                </span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2.5">
                <div class="bg-indigo-500 h-2.5 rounded-full" style="width: <?= $f['pct'] ?>%; min-width: <?= $f['enabled'] > 0 ? '6px' : '0' ?>;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Top tenants by revenue -->
<div class="bg-gray-800 rounded-xl border border-gray-700 mb-8">
    <div class="px-6 py-4 border-b border-gray-700">
        <h2 class="text-lg font-semibold text-white">Top Tenants by Revenue</h2>
        <p class="text-sm text-gray-400 mt-1">Gross order revenue (paid &amp; fulfilled orders)</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Revenue</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider w-1/3">Share</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Orders</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($topTenants)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No revenue-bearing orders yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($topTenants as $i => $t): ?>
                <?php
                    $rev = (float)$t['revenue'];
                    $pct = $maxTenantRevenue > 0 ? round(($rev / $maxTenantRevenue) * 100) : 0;
                ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-4 text-sm text-gray-400"><?= $i + 1 ?></td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white"><?= h($t['name']) ?></div>
                        <div class="text-xs text-gray-400"><?= h($t['slug']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-green-400 whitespace-nowrap">
                        <?= h(formatMoney($rev, $t['currency'] ?? 'DKK')) ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: <?= $pct ?>%; min-width: <?= $rev > 0 ? '6px' : '0' ?>;"></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300 text-right"><?= number_format((int)$t['order_count']) ?></td>
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
