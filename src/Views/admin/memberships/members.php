<?php
$pageTitle = 'Members';
$currentPage = 'memberships';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="/admin/memberships" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Membership Plans</a>
        <h2 class="text-2xl font-bold text-gray-900 mt-1">Members</h2>
        <p class="text-sm text-gray-500 mt-1">View and manage your membership subscribers.</p>
    </div>
</div>

<!-- Filter -->
<div class="mb-6">
    <form method="GET" action="/admin/memberships/members" class="flex items-center gap-3">
        <div>
            <select name="plan" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="">All Plans</option>
                <?php if (!empty($plans)): ?>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($filterPlan ?? '') == $p['id'] ? 'selected' : '' ?>><?= h($p['name']) ?> (Tier <?= (int)$p['tier_level'] ?>)</option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
            Filter
        </button>
        <?php if (!empty($filterPlan)): ?>
            <a href="/admin/memberships/members" class="inline-flex items-center px-4 py-2.5 text-sm text-gray-500 hover:text-gray-900 transition">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($members)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-gray-500">No members found. Members will appear here when customers subscribe to a plan.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period End</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($members as $member): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-white"><?= h(mb_strtoupper(mb_substr($member['name'] ?? '?', 0, 1))) ?></span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900"><?= h($member['name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($member['email']) ?></td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($member['plan_name'] ?? 'Unknown') ?></div>
                            <div class="text-xs text-gray-500 mt-0.5">Tier <?= (int)($member['tier_level'] ?? 0) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (($member['status'] ?? '') === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            <?php elseif (($member['status'] ?? '') === 'canceled'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Canceled</span>
                            <?php elseif (($member['status'] ?? '') === 'past_due'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Past Due</span>
                            <?php elseif (($member['status'] ?? '') === 'trialing'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Trialing</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700"><?= h(ucfirst($member['status'] ?? 'Unknown')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= formatDate($member['created_at'] ?? '') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= !empty($member['current_period_end']) ? formatDate($member['current_period_end']) : '-' ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/memberships/member?id=<?= $member['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
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

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
