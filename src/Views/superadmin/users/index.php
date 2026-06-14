<?php
$pageTitle = 'Users';
$currentPage = 'users';
ob_start();
?>

<!-- Header: filters + create -->
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <form method="GET" action="/users" class="flex flex-col sm:flex-row gap-3 flex-1">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="Search name or email..."
                class="w-full pl-10 pr-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <select name="role" class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All roles</option>
            <option value="superadmin" <?= ($role ?? '') === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
            <option value="tenant_admin" <?= ($role ?? '') === 'tenant_admin' ? 'selected' : '' ?>>Tenant Admin</option>
            <option value="customer" <?= ($role ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
        </select>
        <select name="status" class="px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="banned" <?= ($status ?? '') === 'banned' ? 'selected' : '' ?>>Banned</option>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-medium text-sm rounded-lg transition">Filter</button>
        <?php if (($search ?? '') !== '' || ($role ?? '') !== '' || ($status ?? '') !== ''): ?>
        <a href="/users" class="px-4 py-2.5 text-gray-400 hover:text-white text-sm rounded-lg transition self-center">Clear</a>
        <?php endif; ?>
    </form>

    <a href="/users/create" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition whitespace-nowrap">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Superadmin
    </a>
</div>

<!-- Users Table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Last Login</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        No users found<?= (($search ?? '') !== '' || ($role ?? '') !== '' || ($status ?? '') !== '') ? ' matching your filters' : '' ?>.
                    </td>
                </tr>
                <?php else: ?>
                <?php
                $roleBadges = [
                    'superadmin' => 'bg-indigo-900 text-indigo-300',
                    'tenant_admin' => 'bg-blue-900 text-blue-300',
                    'customer' => 'bg-gray-700 text-gray-300',
                ];
                $roleLabels = [
                    'superadmin' => 'Superadmin',
                    'tenant_admin' => 'Tenant Admin',
                    'customer' => 'Customer',
                ];
                $statusBadges = [
                    'active' => 'bg-green-900 text-green-300',
                    'inactive' => 'bg-yellow-900 text-yellow-300',
                    'banned' => 'bg-red-900 text-red-300',
                ];
                $currentId = (int)(currentUser()['id'] ?? 0);
                ?>
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white"><?= h($u['name']) ?></div>
                        <?php if ((int)$u['id'] === $currentId): ?>
                            <span class="text-xs text-indigo-400">You</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h($u['email']) ?></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $roleBadges[$u['role']] ?? 'bg-gray-700 text-gray-300' ?>">
                            <?= h($roleLabels[$u['role']] ?? $u['role']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <?php if (!empty($u['tenant_name'])): ?>
                            <span class="text-gray-300"><?= h($u['tenant_name']) ?></span>
                            <?php if (!empty($u['tenant_slug'])): ?>
                                <div class="text-xs text-gray-500"><?= h($u['tenant_slug']) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-500">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadges[$u['status']] ?? 'bg-gray-700 text-gray-300' ?>">
                            <?= ucfirst(h($u['status'])) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400">
                        <?= $u['last_login_at'] ? formatDate($u['last_login_at'], 'd M Y H:i') : '<span class="text-gray-600">Never</span>' ?>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-3">
                            <a href="/users/edit?id=<?= (int)$u['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Edit</a>

                            <?php if ($u['status'] === 'active'): ?>
                            <form method="POST" action="/users/set-status" class="inline" onsubmit="return confirm('Suspend this user?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="text-yellow-400 hover:text-yellow-300 text-sm font-medium">Suspend</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="/users/set-status" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="text-green-400 hover:text-green-300 text-sm font-medium">Activate</button>
                            </form>
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
