<?php
$isEdit = !empty($page);
$pageTitle = $isEdit ? 'Edit Custom Page' : 'Create Custom Page';
$currentPage = 'custom-pages';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/custom-pages" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Custom Pages
    </a>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/custom-pages/update' : '/admin/custom-pages/store' ?>" class="space-y-8">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $page['id'] ?>">
    <?php endif; ?>

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Page Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" name="title" id="title" required
                    value="<?= h($page['title'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Page title">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                <input type="text" name="slug" id="slug"
                    value="<?= h($page['slug'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="page-url-slug">
            </div>
            <div class="md:col-span-2">
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" maxlength="500"
                    value="<?= h($page['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="SEO description">
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Page Content (HTML)</h3>
        <p class="text-sm text-gray-500 mb-4">Raw HTML content. For "Full Page" layout, include complete HTML with styles and scripts. For "Shop Layout", content is wrapped in the site header/footer.</p>
        <textarea name="content" id="content" rows="40"
            class="w-full px-4 py-3 bg-gray-900 border border-gray-300 text-green-600 placeholder-gray-500 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
            placeholder="<!DOCTYPE html>..."><?= h($page['content'] ?? '') ?></textarea>
    </div>

    <!-- Settings -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="layout" class="block text-sm font-medium text-gray-700 mb-2">Layout</label>
                <select name="layout" id="layout"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="shop" <?= ($page['layout'] ?? 'shop') === 'shop' ? 'selected' : '' ?>>Shop Layout (header + footer)</option>
                    <option value="full" <?= ($page['layout'] ?? '') === 'full' ? 'selected' : '' ?>>Full Page (standalone HTML)</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="draft" <?= ($page['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= ($page['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" min="0"
                    value="<?= h($page['sort_order'] ?? '0') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="md:col-span-3">
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="is_homepage" value="1"
                        <?= !empty($page['is_homepage']) ? 'checked' : '' ?>
                        class="w-4 h-4 bg-white border-gray-300 rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">Set as Homepage</span>
                    <span class="text-xs text-gray-500">(replaces default shop homepage for this tenant)</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/custom-pages" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            <?= $isEdit ? 'Update Page' : 'Create Page' ?>
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
