<?php
$pageTitle = 'Member: ' . h($member['name']);
$currentPage = 'memberships';
$tenant = currentTenant();
ob_start();
?>

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="/admin/memberships/members" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Members</a>
        <div class="flex items-center gap-3 mt-1">
            <h2 class="text-2xl font-bold text-gray-900"><?= h($member['name']) ?></h2>
            <?php if (($member['status'] ?? '') === 'active'): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">Active</span>
            <?php elseif (($member['status'] ?? '') === 'canceled'): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">Canceled</span>
            <?php elseif (($member['status'] ?? '') === 'past_due'): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700">Past Due</span>
            <?php elseif (($member['status'] ?? '') === 'trialing'): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">Trialing</span>
            <?php else: ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700"><?= h(ucfirst($member['status'] ?? 'Unknown')) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Left Column: Member Info -->
    <div class="space-y-6">
        <!-- Member Info Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Member Information</h3>
            <div class="space-y-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-white"><?= h(mb_strtoupper(mb_substr($member['name'] ?? '?', 0, 1))) ?></span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900"><?= h($member['name']) ?></p>
                        <a href="mailto:<?= h($member['email']) ?>" class="text-xs text-indigo-600 hover:text-indigo-500"><?= h($member['email']) ?></a>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= h($member['plan_name'] ?? 'Unknown') ?></p>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tier Level</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= (int)($member['tier_level'] ?? 0) ?></p>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Interval</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= h(ucfirst($member['billing_interval'] ?? 'monthly')) ?></p>
                </div>

                <?php if (!empty($member['user_id'])): ?>
                <div class="pt-2">
                    <a href="/admin/kunder/<?= $member['user_id'] ?>" class="text-sm text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                        View Customer Profile
                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subscription Details -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Details</h3>
            <div class="space-y-3">
                <?php if (!empty($member['stripe_subscription_id'])): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Stripe Subscription ID</label>
                    <p class="text-sm text-gray-700 mt-0.5 font-mono break-all"><?= h($member['stripe_subscription_id']) ?></p>
                </div>
                <?php endif; ?>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Current Period Start</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= !empty($member['current_period_start']) ? formatDate($member['current_period_start'], 'd M Y, H:i') : '-' ?></p>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Current Period End</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= !empty($member['current_period_end']) ? formatDate($member['current_period_end'], 'd M Y, H:i') : '-' ?></p>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Member Since</label>
                    <p class="text-sm text-gray-700 mt-0.5"><?= !empty($member['created_at']) ? formatDate($member['created_at'], 'd M Y') : '-' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Content & Actions -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Content Selections -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Content Selections</h3>
            </div>
            <?php if (!empty($selections)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selected</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($selections as $selection): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($selection['content_type'] ?? '') === 'course' ? 'bg-indigo-100 text-indigo-700' : 'bg-purple-100 text-purple-700' ?>">
                                        <?= h(ucfirst($selection['content_type'] ?? 'Unknown')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= h($selection['content_title'] ?? 'Unknown') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= !empty($selection['created_at']) ? formatDate($selection['created_at']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-8 text-center">
                    <p class="text-sm text-gray-500">No content selections yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Change Tier -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Membership Tier</h3>
            <form method="POST" action="/admin/memberships/member-change-tier">
                <?= csrfField() ?>
                <input type="hidden" name="membership_id" value="<?= $member['id'] ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="new_plan_id" class="block text-sm font-medium text-gray-700 mb-1.5">New Plan</label>
                        <select id="new_plan_id" name="new_plan_id"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <?php if (!empty($plans)): ?>
                                <?php foreach ($plans as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($member['membership_plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= h($p['name']) ?> (Tier <?= (int)$p['tier_level'] ?> - <?= formatMoney($p['price_monthly']) ?>/mo)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Change Tier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
