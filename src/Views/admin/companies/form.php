<?php $pageTitle = 'Create Company Account'; $currentPage = 'companies'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/companies" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Company Accounts
    </a>
</div>

<form method="POST" action="/admin/companies/store" class="space-y-8">
    <?= csrfField() ?>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Company Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="company_name" class="block text-sm font-medium text-gray-300 mb-2">Company Name *</label>
                <input type="text" name="company_name" id="company_name" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Acme Corp">
            </div>
            <div>
                <label for="admin_user_id" class="block text-sm font-medium text-gray-300 mb-2">Account Admin</label>
                <select name="admin_user_id" id="admin_user_id"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">-- Select a customer --</option>
                    <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id'] ?>">
                        <?= h($customer['name']) ?> (<?= h($customer['email']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">The customer who will manage this company account.</p>
            </div>
            <div>
                <label for="total_licenses" class="block text-sm font-medium text-gray-300 mb-2">Total Licenses</label>
                <input type="number" name="total_licenses" id="total_licenses" min="0" value="0"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Maximum number of team members allowed.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/companies" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Create Company Account
        </button>
    </div>
</form>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
