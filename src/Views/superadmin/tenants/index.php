<?php
$pageTitle = 'Tenants';
$currentPage = 'tenants';
ob_start();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <!-- Search -->
    <form method="GET" action="/tenants" class="flex-1 max-w-md">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="Search tenants..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
    </form>

    <a href="/tenants/create" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Tenant
    </a>
</div>

<!-- Tenants Table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Slug</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Users</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($tenants)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <?= $search ? 'No tenants found matching "' . h($search) . '".' : 'No tenants yet.' ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($tenants as $t): ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white"><?= h($t['name']) ?></div>
                        <div class="text-xs text-gray-400"><?= h($t['email'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <a href="https://<?= h($t['slug']) ?>.<?= PLATFORM_DOMAIN ?>" target="_blank" class="text-sm text-indigo-400 hover:text-indigo-300">
                            <?= h($t['slug']) ?>
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $statusColors = [
                            'active' => 'bg-green-900 text-green-300',
                            'trial' => 'bg-yellow-900 text-yellow-300',
                            'suspended' => 'bg-red-900 text-red-300',
                            'cancelled' => 'bg-gray-700 text-gray-400',
                        ];
                        $statusClass = $statusColors[$t['status']] ?? 'bg-gray-700 text-gray-400';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= ucfirst(h($t['status'])) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h($t['plan_name'] ?? 'None') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= $t['user_count'] ?? 0 ?></td>
                    <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($t['created_at']) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a href="/tenants/edit?id=<?= $t['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Edit</a>
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
