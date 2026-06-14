<?php
$pageTitle = 'New Superadmin';
$currentPage = 'users';
ob_start();
?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/users" class="text-sm text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Users
        </a>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-white mb-1">Create Superadmin</h2>
        <p class="text-sm text-gray-400 mb-6">Superadmins have full platform-wide access across all tenants. They are not tied to any tenant.</p>

        <form method="POST" action="/users/store" class="space-y-4">
            <?= csrfField() ?>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                <input type="text" name="name" required autofocus
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <input type="email" name="email" required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters.</p>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Create Superadmin</button>
                <a href="/users" class="text-sm text-gray-400 hover:text-white">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
