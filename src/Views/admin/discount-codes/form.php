<?php
$pageTitle = $isEdit ? 'Edit Discount Code' : 'Create Discount Code';
$currentPage = 'discount-codes';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/discount-codes" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Discount Codes
    </a>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/discount-codes/update' : '/admin/discount-codes/store' ?>" class="space-y-8">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $discountCode['id'] ?>">
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6"><?= $isEdit ? 'Edit Discount Code' : 'New Discount Code' ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Discount Code *</label>
                <input type="text" name="code" id="code" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent uppercase"
                    value="<?= h($discountCode['code'] ?? '') ?>"
                    placeholder="e.g., SUMMER25"
                    style="text-transform: uppercase;">
                <p class="text-xs text-gray-500 mt-1">Code will be automatically converted to uppercase.</p>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="active" <?= ($discountCode['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($discountCode['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                <select name="type" id="type" x-data x-on:change="$dispatch('type-changed', { value: $event.target.value })"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="percentage" <?= ($discountCode['type'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                    <option value="fixed" <?= ($discountCode['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount (DKK)</option>
                </select>
            </div>

            <!-- Value -->
            <div x-data="{ type: '<?= h($discountCode['type'] ?? 'percentage') ?>' }" @type-changed.window="type = $event.detail.value">
                <label for="value" class="block text-sm font-medium text-gray-700 mb-2">
                    Value *
                    <span class="text-gray-500" x-text="type === 'percentage' ? '(%)' : '(DKK)'"></span>
                </label>
                <input type="number" name="value" id="value" required min="0.01" step="0.01"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($discountCode['value'] ?? '') ?>"
                    placeholder="e.g., 20">
            </div>

            <!-- Min Order Amount -->
            <div>
                <label for="min_order_dkk" class="block text-sm font-medium text-gray-700 mb-2">Minimum Order Amount (DKK)</label>
                <input type="number" name="min_order_dkk" id="min_order_dkk" min="0" step="0.01"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($discountCode['min_order_dkk'] ?? '') ?>"
                    placeholder="Optional">
                <p class="text-xs text-gray-500 mt-1">Leave empty for no minimum.</p>
            </div>

            <!-- Max Uses -->
            <div>
                <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-2">Max Uses</label>
                <input type="number" name="max_uses" id="max_uses" min="1" step="1"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($discountCode['max_uses'] ?? '') ?>"
                    placeholder="Unlimited">
                <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited uses.</p>
            </div>

            <!-- Applies To -->
            <div>
                <label for="applies_to" class="block text-sm font-medium text-gray-700 mb-2">Applies To</label>
                <select name="applies_to" id="applies_to"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="all" <?= ($discountCode['applies_to'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Items</option>
                    <option value="courses" <?= ($discountCode['applies_to'] ?? '') === 'courses' ? 'selected' : '' ?>>Courses Only</option>
                    <option value="products" <?= ($discountCode['applies_to'] ?? '') === 'products' ? 'selected' : '' ?>>Products Only</option>
                    <option value="ebooks" <?= ($discountCode['applies_to'] ?? '') === 'ebooks' ? 'selected' : '' ?>>Ebooks Only</option>
                </select>
            </div>

            <!-- Expires At -->
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expires At</label>
                <input type="datetime-local" name="expires_at" id="expires_at"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= $discountCode && $discountCode['expires_at'] ? date('Y-m-d\TH:i', strtotime($discountCode['expires_at'])) : '' ?>">
                <p class="text-xs text-gray-500 mt-1">Leave empty for no expiration.</p>
            </div>
        </div>

        <?php if ($isEdit && isset($discountCode['used_count'])): ?>
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center space-x-6 text-sm">
                <div>
                    <span class="text-gray-500">Times used:</span>
                    <span class="text-white font-medium ml-1"><?= (int)$discountCode['used_count'] ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Created:</span>
                    <span class="text-white font-medium ml-1"><?= formatDate($discountCode['created_at'], 'd M Y H:i') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/discount-codes" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            <?= $isEdit ? 'Update Discount Code' : 'Create Discount Code' ?>
        </button>
    </div>
</form>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
