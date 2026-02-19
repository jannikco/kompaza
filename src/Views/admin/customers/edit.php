<?php
$pageTitle = 'Edit Customer: ' . h($customer['name']);
$currentPage = 'customers';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kunder/<?= $customer['id'] ?>" class="text-sm text-gray-400 hover:text-white transition">&larr; Back to Customer</a>
    <h2 class="text-2xl font-bold text-white mt-1">Edit Customer</h2>
    <p class="text-sm text-gray-400 mt-1">Update customer information for <?= h($customer['name']) ?>.</p>
</div>

<form method="POST" action="/admin/kunder/opdater" class="max-w-4xl">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $customer['id'] ?>">

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Account Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">Name <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" required value="<?= h($customer['name']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Full name">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email <span class="text-red-400">*</span></label>
                <input type="email" id="email" name="email" required value="<?= h($customer['email']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="email@example.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                <input type="password" id="password" name="password" minlength="8"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Leave blank to keep current password">
                <p class="text-xs text-gray-500 mt-1">Only fill in if you want to change the password.</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-300 mb-1.5">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= h($customer['phone'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="+45 12 34 56 78">
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-gray-300 mb-1.5">Company</label>
                <input type="text" id="company" name="company" value="<?= h($customer['company'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Company name">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="active" <?= ($customer['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($customer['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($customer['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Address</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="address_line1" class="block text-sm font-medium text-gray-300 mb-1.5">Address Line 1</label>
                <input type="text" id="address_line1" name="address_line1" value="<?= h($customer['address_line1'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Street address">
            </div>

            <div class="md:col-span-2">
                <label for="address_line2" class="block text-sm font-medium text-gray-300 mb-1.5">Address Line 2</label>
                <input type="text" id="address_line2" name="address_line2" value="<?= h($customer['address_line2'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Apartment, suite, unit, etc.">
            </div>

            <div>
                <label for="postal_code" class="block text-sm font-medium text-gray-300 mb-1.5">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?= h($customer['postal_code'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="1234">
            </div>

            <div>
                <label for="city" class="block text-sm font-medium text-gray-300 mb-1.5">City</label>
                <input type="text" id="city" name="city" value="<?= h($customer['city'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="City">
            </div>

            <div>
                <label for="country" class="block text-sm font-medium text-gray-300 mb-1.5">Country</label>
                <input type="text" id="country" name="country" value="<?= h($customer['country'] ?? 'DK') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="DK">
            </div>

            <div>
                <label for="cvr_number" class="block text-sm font-medium text-gray-300 mb-1.5">CVR Number</label>
                <input type="text" id="cvr_number" name="cvr_number" value="<?= h($customer['cvr_number'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="12345678">
            </div>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Additional</h3>
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-300 mb-1.5">Notes</label>
            <textarea id="notes" name="notes" rows="4"
                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                      placeholder="Internal notes about this customer..."><?= h($customer['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div x-data="{ confirmDelete: false }">
            <template x-if="!confirmDelete">
                <button type="button" @click="confirmDelete = true" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Customer
                </button>
            </template>
            <template x-if="confirmDelete">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-red-400">Are you sure?</span>
                    <a href="/admin/kunder/slet/<?= $customer['id'] ?>" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition" onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
                        Yes, Delete
                    </a>
                    <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </template>
        </div>

        <div class="flex items-center space-x-3">
            <a href="/admin/kunder/<?= $customer['id'] ?>" class="px-4 py-2 text-sm text-gray-300 hover:text-white transition">Cancel</a>
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Customer
            </button>
        </div>
    </div>
</form>

<!-- Hidden delete form -->
<form id="delete-form" method="POST" action="/admin/kunder/slet/<?= $customer['id'] ?>" class="hidden">
    <?= csrfField() ?>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
