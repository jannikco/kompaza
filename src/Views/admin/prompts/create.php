<?php
$pageTitle = 'Create Prompt';
$currentPage = 'prompts';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/prompts" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Prompts</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Create Prompt</h2>
    <p class="text-sm text-gray-500 mt-1">Add a new prompt to your library.</p>
</div>

<form method="POST" action="/admin/prompts/store" class="max-w-4xl">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($_POST['title'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Sales Email Generator">
            </div>

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select id="category_id" name="category_id" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="ai_tool" class="block text-sm font-medium text-gray-700 mb-1.5">AI Tool</label>
                <input type="text" id="ai_tool" name="ai_tool" value="<?= h($_POST['ai_tool'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. ChatGPT, Claude, Midjourney">
            </div>

            <div class="md:col-span-2">
                <label for="prompt_text" class="block text-sm font-medium text-gray-700 mb-1.5">Prompt Text <span class="text-red-600">*</span></label>
                <textarea id="prompt_text" name="prompt_text" rows="6" required
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono"
                          placeholder="Enter the prompt text that members will copy and use..."><?= h($_POST['prompt_text'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Briefly describe what this prompt does and when to use it..."><?= h($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="use_case" class="block text-sm font-medium text-gray-700 mb-1.5">Use Case</label>
                <textarea id="use_case" name="use_case" rows="2"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Describe the ideal use case for this prompt..."><?= h($_POST['use_case'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1.5">Tags</label>
                <input type="text" id="tags" name="tags" value="<?= h($_POST['tags'] ?? '') ?>"
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
                    <option value="0" <?= ($_POST['membership_tier_level'] ?? '0') === '0' ? 'selected' : '' ?>>Free</option>
                    <option value="1" <?= ($_POST['membership_tier_level'] ?? '') === '1' ? 'selected' : '' ?>>Pro</option>
                    <option value="2" <?= ($_POST['membership_tier_level'] ?? '') === '2' ? 'selected' : '' ?>>Premium</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="draft" <?= ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" min="0" value="<?= h($_POST['sort_order'] ?? '0') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="0">
            </div>

            <div class="md:col-span-3">
                <div class="flex items-center">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                           class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500"
                           <?= !empty($_POST['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured" class="ml-2 text-sm font-medium text-gray-700">Featured prompt</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Featured prompts are highlighted in the library.</p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/prompts" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Prompt
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
