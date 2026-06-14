<?php
$pageTitle = 'Tenant: ' . ($tenant['name'] ?? '');
$currentPage = 'tenants';
ob_start();

$statusColors = [
    'active'    => 'bg-green-900 text-green-300',
    'trial'     => 'bg-yellow-900 text-yellow-300',
    'suspended' => 'bg-red-900 text-red-300',
    'cancelled' => 'bg-gray-700 text-gray-400',
];
$statusClass = $statusColors[$tenant['status']] ?? 'bg-gray-700 text-gray-400';
$adminUrl = 'https://' . h($tenant['slug']) . '.' . PLATFORM_DOMAIN . '/admin';
$currency = $tenant['currency'] ?? 'DKK';
?>

<div class="max-w-6xl">
    <!-- Back -->
    <div class="mb-6">
        <a href="/tenants" class="text-sm text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Tenants
        </a>
    </div>

    <!-- Identity header -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <?php if (!empty($tenant['logo_url'])): ?>
                <img src="<?= h($tenant['logo_url']) ?>" alt="" class="w-14 h-14 rounded-lg object-contain bg-white p-1">
                <?php else: ?>
                <div class="w-14 h-14 rounded-lg bg-indigo-600/20 border border-indigo-600/40 flex items-center justify-center text-indigo-300 text-xl font-semibold">
                    <?= h(strtoupper(substr($tenant['name'] ?? '?', 0, 1))) ?>
                </div>
                <?php endif; ?>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-white"><?= h($tenant['name']) ?></h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>"><?= ucfirst(h($tenant['status'])) ?></span>
                    </div>
                    <div class="mt-1 text-sm text-gray-400 space-x-3">
                        <a href="https://<?= h($tenant['slug']) ?>.<?= PLATFORM_DOMAIN ?>" target="_blank" rel="noopener" class="text-indigo-400 hover:text-indigo-300"><?= h($tenant['slug']) ?>.<?= PLATFORM_DOMAIN ?></a>
                        <?php if (!empty($tenant['custom_domain'])): ?>
                        <span class="text-gray-500">&middot;</span>
                        <a href="https://<?= h($tenant['custom_domain']) ?>" target="_blank" rel="noopener" class="text-indigo-400 hover:text-indigo-300"><?= h($tenant['custom_domain']) ?></a>
                        <?php endif; ?>
                        <?php if (!empty($tenant['email'])): ?>
                        <span class="text-gray-500">&middot;</span><span><?= h($tenant['email']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= $adminUrl ?>" target="_blank" rel="noopener" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium inline-flex items-center">
                    Manage
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <form method="POST" action="/tenants/impersonate" class="inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="tenant_id" value="<?= (int)$tenant['id'] ?>">
                    <button type="submit" class="px-4 py-2 bg-yellow-600/80 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium">Login as Admin</button>
                </form>
                <a href="/tenants/edit?id=<?= (int)$tenant['id'] ?>" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Edit</a>
                <form method="POST" action="/tenants/extend-trial" class="inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$tenant['id'] ?>">
                    <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium">Extend Trial +14d</button>
                </form>
                <form method="POST" action="/tenants/set-status" class="inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$tenant['id'] ?>">
                    <?php if ($tenant['status'] === 'suspended'): ?>
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="px-4 py-2 bg-green-700 hover:bg-green-600 text-white rounded-lg text-sm font-medium">Activate</button>
                    <?php else: ?>
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" onclick="return confirm('Suspend this tenant? Their site will be blocked.');" class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white rounded-lg text-sm font-medium">Suspend</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Lifetime Revenue</p>
            <p class="text-2xl font-semibold text-white mt-1"><?= formatMoney($revenue, $currency) ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= (int)$paidOrderCount ?> orders</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-semibold text-white mt-1"><?= (int)$totalUsers ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= (int)$customerCount ?> customers</p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Plan</p>
            <p class="text-2xl font-semibold text-white mt-1"><?= h($planName ?: 'None') ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= h(ucfirst($tenant['subscription_status'] ?? '')) ?: '&mdash;' ?></p>
        </div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Trial Ends</p>
            <p class="text-2xl font-semibold text-white mt-1"><?= !empty($tenant['trial_ends_at']) ? formatDate($tenant['trial_ends_at']) : '&mdash;' ?></p>
            <p class="text-xs text-gray-500 mt-1">Created <?= formatDate($tenant['created_at']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: subscription + users + content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Subscription -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Platform Subscription</h3>
                <?php if ($subscription): ?>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400">Plan</dt>
                        <dd class="text-white mt-0.5"><?= h($subscription['plan_name'] ?? 'Unknown') ?></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Status</dt>
                        <dd class="text-white mt-0.5"><?= h(ucfirst($subscription['status'] ?? '')) ?></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Billing</dt>
                        <dd class="text-white mt-0.5"><?= h(ucfirst($subscription['billing_interval'] ?? '')) ?></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Period Start</dt>
                        <dd class="text-white mt-0.5"><?= !empty($subscription['current_period_start']) ? formatDate($subscription['current_period_start']) : '&mdash;' ?></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Period End</dt>
                        <dd class="text-white mt-0.5"><?= !empty($subscription['current_period_end']) ? formatDate($subscription['current_period_end']) : '&mdash;' ?></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Trial Ends</dt>
                        <dd class="text-white mt-0.5"><?= !empty($subscription['trial_ends_at']) ? formatDate($subscription['trial_ends_at']) : '&mdash;' ?></dd>
                    </div>
                </dl>
                <?php else: ?>
                <p class="text-sm text-gray-500">No platform subscription record. Tenant status is <span class="text-gray-300"><?= ucfirst(h($tenant['status'])) ?></span>.</p>
                <?php endif; ?>
            </div>

            <!-- Users by role -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Users by Role</h3>
                <?php $roleLabels = ['tenant_admin' => 'Tenant Admins', 'customer' => 'Customers', 'superadmin' => 'Superadmins']; ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <?php foreach ($roleLabels as $roleKey => $roleLabel): ?>
                    <div class="bg-gray-900/40 rounded-lg p-4 border border-gray-700">
                        <p class="text-xs text-gray-400"><?= $roleLabel ?></p>
                        <p class="text-xl font-semibold text-white mt-1"><?= (int)($usersByRole[$roleKey] ?? 0) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Content counts -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Content</h3>
                <?php if (empty($contentCounts)): ?>
                <p class="text-sm text-gray-500">No content tables available.</p>
                <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <?php foreach ($contentCounts as $label => $count): ?>
                    <div class="bg-gray-900/40 rounded-lg p-4 border border-gray-700">
                        <p class="text-xs text-gray-400"><?= h($label) ?></p>
                        <p class="text-xl font-semibold text-white mt-1"><?= (int)$count ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent activity -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Recent Activity</h3>
                <?php if (empty($recentAudit)): ?>
                <p class="text-sm text-gray-500">No activity logged for this tenant yet.</p>
                <?php else: ?>
                <ul class="divide-y divide-gray-700">
                    <?php foreach ($recentAudit as $log): ?>
                    <li class="py-2.5 flex items-center justify-between text-sm">
                        <div>
                            <span class="text-gray-200 font-medium"><?= h($log['action']) ?></span>
                            <?php if (!empty($log['entity_type'])): ?>
                            <span class="text-gray-500"><?= h($log['entity_type']) ?><?= !empty($log['entity_id']) ? ' #' . (int)$log['entity_id'] : '' ?></span>
                            <?php endif; ?>
                            <?php if (!empty($log['user_name'])): ?>
                            <span class="text-gray-500">by <?= h($log['user_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-gray-500 whitespace-nowrap ml-4"><?= formatDate($log['created_at'], 'd M Y H:i') ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: features -->
        <div class="space-y-6">
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Features</h3>
                <ul class="space-y-2">
                    <?php foreach ($featureLabels as $col => $label): ?>
                    <?php $on = !empty($tenant[$col]); ?>
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-gray-300"><?= h($label) ?></span>
                        <?php if ($on): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Enabled</span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-500">Off</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Integrations</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center justify-between">
                        <span class="text-gray-300">Email Service</span>
                        <span class="text-white"><?= h(ucfirst($tenant['email_service'] ?? 'kompaza')) ?></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-300">Stripe</span>
                        <span class="<?= !empty($tenant['stripe_secret_key']) ? 'text-green-300' : 'text-gray-500' ?>"><?= !empty($tenant['stripe_secret_key']) ? 'Connected' : 'Not set' ?></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-300">Google Analytics</span>
                        <span class="<?= !empty($tenant['google_analytics_id']) ? 'text-green-300' : 'text-gray-500' ?>"><?= !empty($tenant['google_analytics_id']) ? h($tenant['google_analytics_id']) : 'Not set' ?></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-300">Currency</span>
                        <span class="text-white"><?= h($currency) ?></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-300">Tax Rate</span>
                        <span class="text-white"><?= h(number_format((float)($tenant['tax_rate'] ?? 0), 2)) ?>%</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
