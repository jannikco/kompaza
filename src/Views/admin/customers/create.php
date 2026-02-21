<?php
$pageTitle = 'Add Customer';
$currentPage = 'customers';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kunder" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Customers</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Add Customer</h2>
    <p class="text-sm text-gray-500 mt-1">Create a new customer account.</p>
</div>

<form method="POST" action="/admin/kunder/gem" class="max-w-4xl">
    <?= csrfField() ?>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-600">*</span></label>
                <input type="text" id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Full name">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-600">*</span></label>
                <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="email@example.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-600">*</span></label>
                <input type="password" id="password" name="password" required minlength="8"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Minimum 8 characters">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= h($_POST['phone'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="+45 12 34 56 78">
            </div>

            <div class="md:col-span-2">
                <label for="company" class="block text-sm font-medium text-gray-700 mb-1.5">Company</label>
                <input type="text" id="company" name="company" value="<?= h($_POST['company'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Company name">
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Address</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 1</label>
                <input type="text" id="address_line1" name="address_line1" value="<?= h($_POST['address_line1'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Street address">
            </div>

            <div class="md:col-span-2">
                <label for="address_line2" class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 2</label>
                <input type="text" id="address_line2" name="address_line2" value="<?= h($_POST['address_line2'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Apartment, suite, unit, etc.">
            </div>

            <div>
                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?= h($_POST['postal_code'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="1234">
            </div>

            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                <input type="text" id="city" name="city" value="<?= h($_POST['city'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="City">
            </div>

            <div>
                <label for="country" class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                <input type="text" id="country" name="country" value="<?= h($_POST['country'] ?? 'DK') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="DK">
            </div>

            <div>
                <label for="cvr_number" class="block text-sm font-medium text-gray-700 mb-1.5">CVR Number</label>
                <input type="text" id="cvr_number" name="cvr_number" value="<?= h($_POST['cvr_number'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="12345678">
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional</h3>
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
            <textarea id="notes" name="notes" rows="4"
                      class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                      placeholder="Internal notes about this customer..."><?= h($_POST['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/kunder" class="px-4 py-2 text-sm text-gray-600 hover:text-white transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Customer
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
