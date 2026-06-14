<?php
$pageTitle = 'Edit Prompt: ' . h($prompt['title']);
$currentPage = 'prompts';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/prompts" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Prompts</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Edit Prompt</h2>
    <p class="text-sm text-gray-500 mt-1">Update details for <?= h($prompt['title']) ?>.</p>
</div>

<form method="POST" action="/admin/prompts/update" class="max-w-4xl">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $prompt['id'] ?>">

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($prompt['title']) ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Sales Email Generator">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select id="category_id" name="category_id" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($prompt['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="ai_tool" class="block text-sm font-medium text-gray-700 mb-1.5">AI Tool</label>
                <input type="text" id="ai_tool" name="ai_tool" value="<?= h($prompt['ai_tool'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. ChatGPT, Claude, Midjourney">
            </div>

            <div class="md:col-span-2">
                <label for="prompt_text" class="block text-sm font-medium text-gray-700 mb-1.5">Prompt Text <span class="text-red-600">*</span></label>
                <textarea id="prompt_text" name="prompt_text" rows="6" required
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono"
                          placeholder="Enter the prompt text that members will copy and use..."><?= h($prompt['prompt_text']) ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Briefly describe what this prompt does and when to use it..."><?= h($prompt['description'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="use_case" class="block text-sm font-medium text-gray-700 mb-1.5">Use Case</label>
                <textarea id="use_case" name="use_case" rows="2"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Describe the ideal use case for this prompt..."><?= h($prompt['use_case'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1.5">Tags</label>
                <input type="text" id="tags" name="tags" value="<?= h($prompt['tags'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="marketing, email, sales (comma-separated)">
                <p class="text-xs text-gray-500 mt-1">Separate tags with commas.</p>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="membership_tier_level" class="block text-sm font-medium text-gray-700 mb-1.5">Membership Tier</label>
                <select id="membership_tier_level" name="membership_tier_level" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="0" <?= (int)($prompt['membership_tier_level'] ?? 0) === 0 ? 'selected' : '' ?>>Free</option>
                    <option value="1" <?= (int)($prompt['membership_tier_level'] ?? 0) === 1 ? 'selected' : '' ?>>Pro</option>
                    <option value="2" <?= (int)($prompt['membership_tier_level'] ?? 0) === 2 ? 'selected' : '' ?>>Premium</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="draft" <?= ($prompt['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($prompt['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" min="0" value="<?= h($prompt['sort_order'] ?? 0) ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="0">
            </div>

            <div class="md:col-span-3">
                <div class="flex items-center">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                           class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500"
                           <?= !empty($prompt['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured" class="ml-2 text-sm font-medium text-gray-700">Featured prompt</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Featured prompts are highlighted in the library.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div x-data="{ confirmDelete: false }">
            <template x-if="!confirmDelete">
                <button type="button" @click="confirmDelete = true" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Prompt
                </button>
            </template>
            <template x-if="confirmDelete">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-red-600">Are you sure?</span>
                    <button type="button" onclick="document.getElementById('delete-form').submit();" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        Yes, Delete
                    </button>
                    <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </template>
        </div>

        <div class="flex items-center space-x-3">
            <a href="/admin/prompts" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Prompt
            </button>
        </div>
    </div>
</form>

<!-- Hidden delete form -->
<form id="delete-form" method="POST" action="/admin/prompts/delete" class="hidden">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $prompt['id'] ?>">
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
