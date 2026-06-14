<?php
/**
 * Superadmin platform dashboard.
 *
 * Expects (from src/Controllers/superadmin/dashboard.php):
 *   float $mrr, float $arr
 *   array $tenantStatusCounts (trial|active|suspended|cancelled => int)
 *   int   $totalTenants, $totalUsers, $newTenants7, $newTenants30
 *   array $subStatusCounts (status => int)
 *   array $topFeatures (['label','count'])
 *   array $recentActivity, $recentTenants
 */

$statusBadgeColors = [
    'active'    => 'bg-green-900 text-green-300',
    'trial'     => 'bg-yellow-900 text-yellow-300',
    'trialing'  => 'bg-yellow-900 text-yellow-300',
    'suspended' => 'bg-red-900 text-red-300',
    'cancelled' => 'bg-gray-700 text-gray-400',
    'canceled'  => 'bg-gray-700 text-gray-400',
    'past_due'  => 'bg-orange-900 text-orange-300',
];

// Largest feature count drives the relative bar widths (avoid div-by-zero).
$maxFeatureCount = 0;
foreach ($topFeatures as $f) {
    if ($f['count'] > $maxFeatureCount) {
        $maxFeatureCount = $f['count'];
    }
}

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<!-- Revenue Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- MRR -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-400">Monthly Recurring Revenue</p>
            <div class="p-2 rounded-lg bg-green-900/50 text-green-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="mt-3 text-3xl font-bold text-white">$<?= number_format($mrr, 2) ?></p>
        <p class="mt-1 text-xs text-gray-500">USD per month</p>
    </div>

    <!-- ARR -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-400">Annual Recurring Revenue</p>
            <div class="p-2 rounded-lg bg-emerald-900/50 text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
        </div>
        <p class="mt-3 text-3xl font-bold text-white">$<?= number_format($arr, 2) ?></p>
        <p class="mt-1 text-xs text-gray-500">MRR &times; 12</p>
    </div>

    <!-- Total Tenants -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-400">Total Tenants</p>
            <div class="p-2 rounded-lg bg-blue-900/50 text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <p class="mt-3 text-3xl font-bold text-white"><?= number_format($totalTenants) ?></p>
        <p class="mt-1 text-xs text-gray-500"><?= number_format($tenantStatusCounts['active'] ?? 0) ?> active</p>
    </div>

    <!-- Total Users -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-400">Total Users</p>
            <div class="p-2 rounded-lg bg-purple-900/50 text-purple-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
        <p class="mt-3 text-3xl font-bold text-white"><?= number_format($totalUsers) ?></p>
        <p class="mt-1 text-xs text-gray-500">across all tenants</p>
    </div>
</div>

<!-- Tenants by status + growth -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Tenants by status -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Tenants by Status</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php
            $statusCards = [
                ['key' => 'trial',     'label' => 'Trial',     'dot' => 'bg-yellow-400', 'text' => 'text-yellow-300'],
                ['key' => 'active',    'label' => 'Active',    'dot' => 'bg-green-400',  'text' => 'text-green-300'],
                ['key' => 'suspended', 'label' => 'Suspended', 'dot' => 'bg-red-400',    'text' => 'text-red-300'],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'dot' => 'bg-gray-500',   'text' => 'text-gray-400'],
            ];
            ?>
            <?php foreach ($statusCards as $sc): ?>
            <div class="bg-gray-900/50 rounded-lg border border-gray-700 p-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full <?= $sc['dot'] ?>"></span>
                    <span class="text-xs uppercase tracking-wider <?= $sc['text'] ?>"><?= $sc['label'] ?></span>
                </div>
                <p class="mt-2 text-2xl font-bold text-white"><?= number_format($tenantStatusCounts[$sc['key']] ?? 0) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6">
            <div class="bg-gray-900/50 rounded-lg border border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-400">New (7 days)</p>
                <p class="mt-2 text-2xl font-bold text-indigo-400">+<?= number_format($newTenants7) ?></p>
            </div>
            <div class="bg-gray-900/50 rounded-lg border border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-400">New (30 days)</p>
                <p class="mt-2 text-2xl font-bold text-indigo-400">+<?= number_format($newTenants30) ?></p>
            </div>
        </div>

        <?php if (!empty($subStatusCounts)): ?>
        <div class="mt-6 pt-6 border-t border-gray-700">
            <p class="text-xs uppercase tracking-wider text-gray-400 mb-3">Subscriptions</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($subStatusCounts as $status => $count): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadgeColors[$status] ?? 'bg-gray-700 text-gray-400' ?>">
                    <?= ucfirst(h(str_replace('_', ' ', $status))) ?>: <?= number_format($count) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Feature adoption -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Feature Adoption</h3>
            <span class="text-xs text-gray-500">Top <?= count($topFeatures) ?> &middot; of <?= number_format($totalTenants) ?> tenants</span>
        </div>
        <?php if (empty($topFeatures) || $maxFeatureCount === 0): ?>
        <p class="text-sm text-gray-500 py-8 text-center">No feature usage data yet.</p>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($topFeatures as $feature): ?>
            <?php
                $pct = $totalTenants > 0 ? round(($feature['count'] / $totalTenants) * 100) : 0;
                // Bar width is relative to the most-adopted feature for visual contrast.
                $barWidth = $maxFeatureCount > 0 ? round(($feature['count'] / $maxFeatureCount) * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm text-gray-300"><?= h($feature['label']) ?></span>
                    <span class="text-xs text-gray-400"><?= number_format($feature['count']) ?> <span class="text-gray-600">(<?= $pct ?>%)</span></span>
                </div>
                <div class="w-full bg-gray-900 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: <?= $barWidth ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent activity + Recent tenants -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <!-- Recent activity -->
    <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Recent Activity</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($recentActivity)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No activity recorded yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentActivity as $a): ?>
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?= h($a['action']) ?></div>
                            <?php if (!empty($a['entity_type'])): ?>
                            <div class="text-xs text-gray-500">
                                <?= h($a['entity_type']) ?><?= !empty($a['entity_id']) ? ' #' . h($a['entity_id']) : '' ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-300"><?= h($a['user_name'] ?? 'System') ?></div>
                            <?php if (!empty($a['user_email'])): ?>
                            <div class="text-xs text-gray-500"><?= h($a['user_email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($a['tenant_name'] ?? '—') ?></td>
                        <td class="px-6 py-4 text-right text-sm text-gray-400" title="<?= h($a['created_at']) ?>">
                            <?= formatDate($a['created_at'], 'd M Y H:i') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent tenants -->
    <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">Recent Tenants</h3>
            <a href="/tenants" class="text-sm text-indigo-400 hover:text-indigo-300">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($recentTenants)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No tenants yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recentTenants as $t): ?>
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?= h($t['name']) ?></div>
                            <a href="/tenants/edit?id=<?= (int) $t['id'] ?>" class="text-xs text-indigo-400 hover:text-indigo-300"><?= h($t['slug']) ?></a>
                        </td>
                        <td class="px-6 py-4">
                            <?php $statusClass = $statusBadgeColors[$t['status']] ?? 'bg-gray-700 text-gray-400'; ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= ucfirst(h($t['status'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($t['plan_name'] ?? 'None') ?></td>
                        <td class="px-6 py-4 text-right text-sm text-gray-400"><?= formatDate($t['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
