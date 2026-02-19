<?php
$pageTitle = 'Edit Tenant: ' . ($tenant['name'] ?? '');
$currentPage = 'tenants';
ob_start();
?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/tenants" class="text-sm text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Tenants
        </a>
    </div>

    <!-- Tenant Info Card -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Tenant Info</h3>
            <a href="https://<?= h($tenant['slug']) ?>.<?= PLATFORM_DOMAIN ?>" target="_blank"
                class="inline-flex items-center text-sm text-indigo-400 hover:text-indigo-300">
                Visit Site
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400">Users</p>
                <p class="text-lg font-semibold text-white"><?= $userCount ?? 0 ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                <?php
                $statusColors = [
                    'active' => 'bg-green-900 text-green-300',
                    'trial' => 'bg-yellow-900 text-yellow-300',
                    'suspended' => 'bg-red-900 text-red-300',
                    'cancelled' => 'bg-gray-700 text-gray-400',
                ];
                $statusClass = $statusColors[$tenant['status']] ?? 'bg-gray-700 text-gray-400';
                ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1 <?= $statusClass ?>">
                    <?= ucfirst(h($tenant['status'])) ?>
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400">Created</p>
                <p class="text-sm text-white mt-1"><?= formatDate($tenant['created_at']) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Subdomain</p>
                <p class="text-sm text-indigo-400 mt-1"><?= h($tenant['slug']) ?>.<?= PLATFORM_DOMAIN ?></p>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <form method="POST" action="/tenants/update" class="space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $tenant['id'] ?>">

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Tenant Name</label>
                <input type="text" id="name" name="name" required value="<?= h($tenant['name']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                <div class="flex items-center">
                    <input type="text" id="slug" name="slug" required value="<?= h($tenant['slug']) ?>"
                        class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-l-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <span class="px-4 py-2.5 bg-gray-600 border border-gray-600 rounded-r-lg text-gray-400 text-sm">.<?= PLATFORM_DOMAIN ?></span>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Contact Email</label>
                <input type="email" id="email" name="email" value="<?= h($tenant['email'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                    <select id="status" name="status"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="trial" <?= $tenant['status'] === 'trial' ? 'selected' : '' ?>>Trial</option>
                        <option value="active" <?= $tenant['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $tenant['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="cancelled" <?= $tenant['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label for="plan_id" class="block text-sm font-medium text-gray-300 mb-1">Plan</label>
                    <select id="plan_id" name="plan_id"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">No plan</option>
                        <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>" <?= ($tenant['plan_id'] ?? '') == $plan['id'] ? 'selected' : '' ?>><?= h($plan['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="trial_ends_at" class="block text-sm font-medium text-gray-300 mb-1">Trial Ends</label>
                <input type="date" id="trial_ends_at" name="trial_ends_at"
                    value="<?= $tenant['trial_ends_at'] ? date('Y-m-d', strtotime($tenant['trial_ends_at'])) : '' ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                <a href="/tenants" class="px-4 py-2.5 text-sm font-medium text-gray-300 hover:text-white transition">Cancel</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                    Update Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
