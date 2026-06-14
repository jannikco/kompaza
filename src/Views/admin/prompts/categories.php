<?php
$pageTitle = 'Prompt Categories';
$currentPage = 'prompts';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Prompt Categories</h2>
        <p class="text-sm text-gray-500 mt-1">Organize your prompts into categories.</p>
    </div>
    <a href="/admin/prompts" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Prompts
    </a>
</div>

<!-- Add New Category Form -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Category</h3>
    <form method="POST" action="/admin/prompts/categories">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="e.g. Marketing">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <input type="text" name="description"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="Brief description">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon</label>
                <input type="text" name="icon"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="e.g. sparkles">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" name="sort_order" value="0" min="0"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Category
            </button>
        </div>
    </form>
</div>

<!-- Existing Categories -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Existing Categories</h3>
    </div>
    <?php if (empty($categories)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            <p class="text-gray-500">No categories yet. Create your first category above.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
            <?php foreach ($categories as $cat): ?>
            <div class="border border-gray-200 rounded-xl p-4" x-data="{ editing: false }">
                <!-- View mode -->
                <div x-show="!editing">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h4 class="text-base font-medium text-gray-900"><?= h($cat['name']) ?></h4>
                            <p class="text-xs text-gray-500 mt-0.5"><?= h($cat['slug']) ?></p>
                        </div>
                        <?php if (!empty($cat['icon'])): ?>
                            <span class="text-gray-400 text-sm"><?= h($cat['icon']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($cat['description'])): ?>
                        <p class="text-sm text-gray-500 mb-3"><?= h($cat['description']) ?></p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500"><?= (int)($cat['prompt_count'] ?? 0) ?> prompt<?= (int)($cat['prompt_count'] ?? 0) !== 1 ? 's' : '' ?></span>
                        <div class="flex items-center gap-2">
                            <button @click="editing = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                Edit
                            </button>
                            <form method="POST" action="/admin/prompts/categories" onsubmit="return confirm('Delete this category? Prompts in this category will not be deleted.')" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit mode -->
                <form x-show="editing" x-cloak method="POST" action="/admin/prompts/categories">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-600">*</span></label>
                            <input type="text" name="name" required value="<?= h($cat['name']) ?>"
                                   class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="description" value="<?= h($cat['description'] ?? '') ?>"
                                   class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                                <input type="text" name="icon" value="<?= h($cat['icon'] ?? '') ?>"
                                       class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="<?= (int)($cat['sort_order'] ?? 0) ?>" min="0"
                                       class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                Save Changes
                            </button>
                            <button type="button" @click="editing = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
