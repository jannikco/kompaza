<?php
$isEdit = !empty($bump);
$pageTitle = $isEdit ? 'Edit Order Bump' : 'Create Order Bump';
$currentPage = 'order-bumps';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/order-bumps" class="text-sm text-gray-500 hover:text-gray-700 transition">&larr; Back to Order Bumps</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-2"><?= $isEdit ? 'Edit Order Bump' : 'Create Order Bump' ?></h2>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/order-bumps/update' : '/admin/order-bumps/store' ?>" class="space-y-6 max-w-3xl">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $bump['id'] ?>">
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Bump Details</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="<?= h($bump['name'] ?? '') ?>" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="e.g. Add Premium Workbook">
            <p class="text-xs text-gray-500 mt-1">Internal name for your reference.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product to Offer <span class="text-red-500">*</span></label>
            <select name="product_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select a product...</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($bump['product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= h($p['name']) ?> (<?= formatMoney($p['price_dkk']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bump Price (DKK) <span class="text-red-500">*</span></label>
            <input type="number" name="bump_price_dkk" step="0.01" min="0" value="<?= h($bump['bump_price_dkk'] ?? '') ?>" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="19.00">
            <p class="text-xs text-gray-500 mt-1">Special price for the bump offer (usually discounted from the regular product price).</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Text</label>
            <input type="text" name="display_text" value="<?= h($bump['display_text'] ?? '') ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Yes! Add the Premium Workbook for only 19 DKK">
            <p class="text-xs text-gray-500 mt-1">Text shown next to the checkbox on the checkout page.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="Brief description of what makes this a great add-on..."><?= h($bump['description'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Targeting</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Show For</label>
            <select name="applies_to" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all" <?= ($bump['applies_to'] ?? 'all') === 'all' ? 'selected' : '' ?>>All products in cart</option>
                <option value="specific_products" <?= ($bump['applies_to'] ?? '') === 'specific_products' ? 'selected' : '' ?>>Specific products in cart</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Specific Product IDs (comma-separated)</label>
            <input type="text" name="applies_to_value"
                   value="<?= h(implode(',', json_decode($bump['applies_to_value'] ?? '[]', true) ?: [])) ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="e.g. 1,5,12">
            <p class="text-xs text-gray-500 mt-1">Only used when "Specific products" is selected above.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Settings</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="<?= (int)($bump['sort_order'] ?? 0) ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="active" <?= ($bump['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($bump['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <?= $isEdit ? 'Update Order Bump' : 'Create Order Bump' ?>
        </button>
        <a href="/admin/order-bumps" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Cancel</a>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
