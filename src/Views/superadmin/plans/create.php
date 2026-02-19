<?php
$pageTitle = 'Create Plan';
$currentPage = 'plans';
ob_start();
?>

<div class="mb-6">
    <a href="/plans" class="text-indigo-400 hover:text-indigo-300 text-sm">&larr; Back to Plans</a>
</div>

<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 max-w-2xl">
    <h2 class="text-lg font-semibold text-white mb-6">New Plan</h2>
    <form method="POST" action="/plans/store" class="space-y-4">
        <?= csrfField() ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                <input type="text" name="name" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                <input type="text" name="slug" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="auto-generated">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Monthly Price (DKK)</label>
                <input type="number" name="price_monthly_dkk" step="0.01" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Yearly Price (DKK)</label>
                <input type="number" name="price_yearly_dkk" step="0.01" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
        </div>
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider pt-4">Limits (leave empty for unlimited)</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Max Customers</label>
                <input type="number" name="max_customers" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Max Leads</label>
                <input type="number" name="max_leads" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Max Campaigns</label>
                <input type="number" name="max_campaigns" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Max Products</label>
                <input type="number" name="max_products" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Max Lead Magnets</label>
                <input type="number" name="max_lead_magnets" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="is_active" checked class="rounded bg-gray-700 border-gray-600 text-indigo-600">
            <label for="is_active" class="text-sm text-gray-300">Active</label>
        </div>
        <div class="pt-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">Create Plan</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
