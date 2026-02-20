<?php
$pageTitle = 'Users';
$currentPage = 'users';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Users</h2>
        <p class="text-sm text-gray-400 mt-1">Manage admin users who have access to this dashboard.</p>
    </div>
    <a href="/admin/brugere/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create New
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($users)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-gray-400 mb-4">No users found.</p>
            <a href="/admin/brugere/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create User
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-750" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-medium text-white mr-3">
                                    <?= strtoupper(mb_substr($user['name'] ?? '?', 0, 1)) ?>
                                </div>
                                <span class="text-sm font-medium text-white"><?= h($user['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($user['email']) ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $roleClass = match($user['role'] ?? '') {
                                'superadmin' => 'bg-red-900 text-red-300',
                                'tenant_admin' => 'bg-indigo-900 text-indigo-300',
                                'customer' => 'bg-blue-900 text-blue-300',
                                default => 'bg-gray-700 text-gray-300',
                            };
                            $roleLabel = match($user['role'] ?? '') {
                                'superadmin' => 'Super Admin',
                                'tenant_admin' => 'Admin',
                                'customer' => 'Customer',
                                default => ucfirst($user['role'] ?? 'Unknown'),
                            };
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $roleClass ?>">
                                <?= $roleLabel ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">
                            <?= !empty($user['last_login_at']) ? formatDate($user['last_login_at'], 'd M Y H:i') : 'Never' ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (($user['status'] ?? 'active') === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/brugere/rediger?id=<?= $user['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Edit
                                </a>
                                <?php if (($user['id'] ?? 0) !== currentUserId()): ?>
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/brugere/slet" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                            Confirm
                                        </button>
                                        <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                            Cancel
                                        </button>
                                    </form>
                                </template>
                                <?php endif; ?>
                            </div>
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
