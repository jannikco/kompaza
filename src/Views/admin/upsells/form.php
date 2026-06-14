<?php
$isEdit = !empty($offer);
$pageTitle = $isEdit ? 'Edit Offer' : 'Create Upsell/Downsell';
$currentPage = 'upsells';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/upsells" class="text-sm text-gray-500 hover:text-gray-700 transition">&larr; Back to Upsells</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-2"><?= $isEdit ? 'Edit Offer' : 'Create Upsell/Downsell Offer' ?></h2>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/upsells/update' : '/admin/upsells/store' ?>" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $offer['id'] ?>">
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Offer Details</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Offer Type <span class="text-red-500">*</span></label>
                <select name="offer_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="upsell" <?= ($offer['offer_type'] ?? 'upsell') === 'upsell' ? 'selected' : '' ?>>Upsell (premium offer)</option>
                    <option value="downsell" <?= ($offer['offer_type'] ?? '') === 'downsell' ? 'selected' : '' ?>>Downsell (cheaper alternative)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Internal Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= h($offer['name'] ?? '') ?>" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label>
            <input type="text" name="headline" value="<?= h($offer['headline'] ?? '') ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Wait! Don't miss this exclusive offer...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description / Sales Copy</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="Explain why they should add this to their order..."><?= h($offer['description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product to Offer <span class="text-red-500">*</span></label>
            <select name="product_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select a product...</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($offer['product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= h($p['name']) ?> (<?= formatMoney($p['price_dkk']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Offer Price (DKK) <span class="text-red-500">*</span></label>
                <input type="number" name="offer_price_dkk" step="0.01" min="0" value="<?= h($offer['offer_price_dkk'] ?? '') ?>" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Original Price (DKK, strikethrough)</label>
                <input type="number" name="original_price_dkk" step="0.01" min="0" value="<?= h($offer['original_price_dkk'] ?? '') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
            <?php if ($isEdit && $offer['image_path']): ?>
                <div class="mb-2"><img src="<?= h(imageUrl($offer['image_path'])) ?>" class="h-24 rounded-lg" alt=""></div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*"
                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Buttons & Copy</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Accept Button Text</label>
                <input type="text" name="button_text" value="<?= h($offer['button_text'] ?? 'Yes, Add This To My Order!') ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Decline Text</label>
                <input type="text" name="decline_text" value="<?= h($offer['decline_text'] ?? "No thanks, I'll pass") ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Targeting & Settings</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Product IDs (comma-separated, leave empty for all)</label>
            <input type="text" name="trigger_product_ids"
                   value="<?= h(implode(',', json_decode($offer['trigger_product_ids'] ?? 'null', true) ?: [])) ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Leave empty to show after any purchase">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent Upsell (for downsells only)</label>
            <select name="parent_upsell_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">None (standalone)</option>
                <?php foreach ($upsells as $u): ?>
                    <?php if ($isEdit && $u['id'] == $offer['id']) continue; ?>
                    <option value="<?= $u['id'] ?>" <?= ($offer['parent_upsell_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                        <?= h($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-gray-500 mt-1">If this is a downsell, select which upsell's decline triggers it.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="<?= (int)($offer['sort_order'] ?? 0) ?>"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="active" <?= ($offer['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($offer['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <?= $isEdit ? 'Update Offer' : 'Create Offer' ?>
        </button>
        <a href="/admin/upsells" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Cancel</a>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
