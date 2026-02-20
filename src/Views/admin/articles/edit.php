<?php
$pageTitle = 'Edit Article';
$currentPage = 'articles';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/artikler" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Articles
    </a>
</div>

<form method="POST" action="/admin/artikler/opdater" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $article['id'] ?>">

    <!-- Basic Information -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Article Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title</label>
                <input type="text" name="title" id="title" required
                    value="<?= h($article['title']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Article title">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" required
                    value="<?= h($article['slug']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="article-url-slug">
            </div>
            <div class="md:col-span-2">
                <label for="excerpt" class="block text-sm font-medium text-gray-300 mb-2">Excerpt</label>
                <textarea name="excerpt" id="excerpt" rows="3"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="A brief summary of the article..."><?= h($article['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label for="editor" class="block text-sm font-medium text-gray-300 mb-2">Content</label>
                <textarea name="content" id="editor" rows="20"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Write your article content here..."><?= h($article['content'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Media & SEO -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Media & SEO</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="featured_image" class="block text-sm font-medium text-gray-300 mb-2">Featured Image</label>
                <?php if (!empty($article['featured_image'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <img src="<?= h(imageUrl($article['featured_image'])) ?>" alt="Current featured image" class="h-16 w-auto rounded-lg border border-gray-600">
                        <span class="text-sm text-gray-400">Current image</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="featured_image" id="featured_image" accept="image/*"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current image.</p>
            </div>
            <div class="md:col-span-2">
                <label for="meta_description" class="block text-sm font-medium text-gray-300 mb-2">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" maxlength="160"
                    value="<?= h($article['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="SEO description (max 160 characters)">
            </div>
        </div>
    </div>

    <!-- Categorization & Publishing -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Categorization & Publishing</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                <input type="text" name="category" id="category"
                    value="<?= h($article['category'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Marketing, Business">
            </div>
            <div>
                <label for="author_name" class="block text-sm font-medium text-gray-300 mb-2">Author Name</label>
                <input type="text" name="author_name" id="author_name"
                    value="<?= h($article['author_name'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Author's display name">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div>
                <label for="published_at" class="block text-sm font-medium text-gray-300 mb-2">Published At</label>
                <input type="datetime-local" name="published_at" id="published_at"
                    value="<?= !empty($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : '' ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/artikler" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Update Article
        </button>
    </div>
</form>

<script>
    tinymce.init({
        selector: '#editor',
        skin: 'oxide-dark',
        content_css: 'dark',
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | code | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 16px; color: #e5e7eb; background: #374151; }',
        branding: false,
        promotion: false
    });
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
