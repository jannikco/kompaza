<?php
$isEdit = !empty($link);
$pageTitle = $isEdit ? 'Edit Payment Link' : 'Create Payment Link';
$currentPage = 'payment-links';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/payment-links" class="text-sm text-gray-500 hover:text-gray-700 transition">&larr; Back to Payment Links</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-2"><?= $isEdit ? 'Edit Payment Link' : 'Create Payment Link' ?></h2>
</div>

<?php if ($isEdit): ?>
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6 max-w-3xl">
        <p class="text-sm font-medium text-indigo-800">Payment Link URL:</p>
        <div class="flex items-center gap-2 mt-1" x-data="{ copied: false }">
            <code class="text-sm text-indigo-700 bg-white px-3 py-1.5 rounded-lg border border-indigo-200 flex-1"><?= h(tenantUrl('pay/' . $link['token'])) ?></code>
            <button @click="navigator.clipboard.writeText('<?= h(tenantUrl('pay/' . $link['token'])) ?>'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="px-3 py-1.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
            </button>
        </div>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $isEdit ? '/admin/payment-links/update' : '/admin/payment-links/store' ?>" class="space-y-6 max-w-3xl">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $link['id'] ?>">
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Link Details</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="<?= h($link['name'] ?? '') ?>" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="e.g. Spring Sale - Premium Course">
            <p class="text-xs text-gray-500 mt-1">Internal reference name (not shown to customers).</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
            <select name="product_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select a product...</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($link['product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= h($p['name']) ?> (<?= formatMoney($p['price_dkk']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custom Price (DKK)</label>
                <input type="number" name="custom_price_dkk" step="0.01" min="0" value="<?= h($link['custom_price_dkk'] ?? '') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Leave empty to use product price">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custom Product Name</label>
                <input type="text" name="custom_name" value="<?= h($link['custom_name'] ?? '') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Override product name on checkout">
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Limits & Settings</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Uses</label>
                <input type="number" name="max_uses" min="1" value="<?= h($link['max_uses'] ?? '') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Unlimited">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                <input type="datetime-local" name="expires_at" value="<?= $link['expires_at'] ? date('Y-m-d\TH:i', strtotime($link['expires_at'])) : '' ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="allow_quantity" value="1" <?= !empty($link['allow_quantity']) ? 'checked' : '' ?>
                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Allow customer to change quantity</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Redirect URL After Purchase</label>
            <input type="url" name="redirect_url" value="<?= h($link['redirect_url'] ?? '') ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="https://... (leave empty for default thank-you page)">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="active" <?= ($link['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($link['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <?= $isEdit ? 'Update Payment Link' : 'Create Payment Link' ?>
        </button>
        <a href="/admin/payment-links" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Cancel</a>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
