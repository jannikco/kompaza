<?php
$pageTitle = 'Plans';
$currentPage = 'plans';
ob_start();
?>

<!-- Plans Table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-white">All Plans</h3>
        <a href="/plans/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Plan
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Slug</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Monthly Price</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Yearly Price</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Limits</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($plans)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No plans configured yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($plans as $plan): ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white"><?= h($plan['name']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h($plan['slug']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                        <?= $plan['price_monthly_dkk'] ? formatMoney($plan['price_monthly_dkk']) : 'Free' ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                        <?= $plan['price_yearly_dkk'] ? formatMoney($plan['price_yearly_dkk']) : '-' ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($plan['is_active']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Active</span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-400">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-400 space-y-0.5">
                            <?php if ($plan['max_customers']): ?>
                            <div>Customers: <?= $plan['max_customers'] ?></div>
                            <?php endif; ?>
                            <?php if ($plan['max_leads']): ?>
                            <div>Leads: <?= $plan['max_leads'] ?></div>
                            <?php endif; ?>
                            <?php if ($plan['max_products']): ?>
                            <div>Products: <?= $plan['max_products'] ?></div>
                            <?php endif; ?>
                            <?php if ($plan['max_lead_magnets']): ?>
                            <div>Lead Magnets: <?= $plan['max_lead_magnets'] ?></div>
                            <?php endif; ?>
                            <?php if (!$plan['max_customers'] && !$plan['max_leads'] && !$plan['max_products'] && !$plan['max_lead_magnets']): ?>
                            <div class="text-gray-500">Unlimited</div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="/plans/edit?id=<?= $plan['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Edit</a>
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
