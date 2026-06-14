<?php
$pageTitle = 'Edit User: ' . ($user['name'] ?? '');
$currentPage = 'users';
ob_start();
$isSelf = (int)$user['id'] === (int)(currentUser()['id'] ?? 0);
?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/users" class="text-sm text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users
        </a>
    </div>

    <!-- Edit user -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-6">Edit User</h2>

        <form method="POST" action="/users/update" class="space-y-4" x-data="{ role: '<?= h($user['role']) ?>' }">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                    <input type="text" name="name" value="<?= h($user['name']) ?>" required
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="<?= h($user['email']) ?>" required
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Role</label>
                    <select name="role" x-model="role"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
                        <option value="tenant_admin" <?= $user['role'] === 'tenant_admin' ? 'selected' : '' ?>>Tenant Admin</option>
                        <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                    <select name="status"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="banned" <?= $user['status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
                    </select>
                </div>
            </div>

            <div x-show="role !== 'superadmin'" x-cloak>
                <label class="block text-sm font-medium text-gray-300 mb-1">Tenant</label>
                <select name="tenant_id"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">&mdash; Select tenant &mdash;</option>
                    <?php foreach ($tenants as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= (int)($user['tenant_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                        <?= h($t['name']) ?> (<?= h($t['slug']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Required for tenant admins and customers. Ignored for superadmins.</p>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Save Changes</button>
                <a href="/users" class="text-sm text-gray-400 hover:text-white">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Reset password -->
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
        <h3 class="text-base font-semibold text-white mb-1">Reset Password</h3>
        <p class="text-sm text-gray-400 mb-4">Set a new password for this user. They will need to use it on their next login.</p>
        <form method="POST" action="/users/reset-password" class="flex flex-col sm:flex-row gap-3">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
            <input type="password" name="new_password" required minlength="8" placeholder="New password (min 8 chars)"
                class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium whitespace-nowrap">Reset Password</button>
        </form>
    </div>

    <!-- Status quick actions + delete -->
    <div class="bg-gray-800 rounded-xl border border-red-900/50 p-6">
        <h3 class="text-base font-semibold text-white mb-1">Account Actions</h3>
        <p class="text-sm text-gray-400 mb-4">Change account status or permanently delete this user.</p>
        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="/users/set-status" class="inline">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                <input type="hidden" name="status" value="banned">
                <button type="submit" <?= $isSelf ? 'disabled' : '' ?>
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-red-300 rounded-lg font-medium text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    Ban User
                </button>
            </form>

            <form method="POST" action="/users/delete" class="inline" onsubmit="return confirm('Permanently delete this user? This cannot be undone.');">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                <button type="submit" <?= $isSelf ? 'disabled' : '' ?>
                    class="px-4 py-2 bg-red-900/60 hover:bg-red-800 text-red-200 rounded-lg font-medium text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    Delete User
                </button>
            </form>

            <?php if ($isSelf): ?>
            <span class="text-xs text-gray-500">You cannot ban or delete your own account.</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
