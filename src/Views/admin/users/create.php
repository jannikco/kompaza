<?php
$pageTitle = 'Create User';
$currentPage = 'users';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/brugere" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Users
    </a>
</div>

<form method="POST" action="/admin/brugere/gem" class="max-w-2xl space-y-8">
    <?= csrfField() ?>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">User Details</h3>
        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                <input type="text" name="name" id="name" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Full name">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" name="email" id="email" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="user@example.com">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <input type="password" name="password" id="password" required minlength="8"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Minimum 8 characters">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-300 mb-2">Role</label>
                <select name="role" id="role"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="tenant_admin">Admin</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">Currently only tenant admin role is available.</p>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/brugere" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Create User
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
