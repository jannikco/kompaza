<?php
$pageTitle = 'Edit Ebook';
$currentPage = 'ebooks';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/eboger" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Ebooks
    </a>
</div>

<form method="POST" action="/admin/eboger/opdater/<?= $ebook['id'] ?>" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Ebook Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title</label>
                <input type="text" name="title" id="title" required
                    value="<?= h($ebook['title']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Ebook title">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" required
                    value="<?= h($ebook['slug']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="ebook-url-slug">
            </div>
            <div class="md:col-span-2">
                <label for="subtitle" class="block text-sm font-medium text-gray-300 mb-2">Subtitle</label>
                <input type="text" name="subtitle" id="subtitle"
                    value="<?= h($ebook['subtitle'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="A short subtitle for the ebook">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="8"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Detailed description of the ebook..."><?= h($ebook['description'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-2">Supports rich text formatting via the editor.</p>
            </div>
        </div>
    </div>

    <!-- Files & Media -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Files & Media</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="cover_image" class="block text-sm font-medium text-gray-300 mb-2">Cover Image</label>
                <?php if (!empty($ebook['cover_image'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <img src="<?= h($ebook['cover_image']) ?>" alt="Current cover" class="h-20 w-auto rounded-lg border border-gray-600">
                        <span class="text-sm text-gray-400">Current cover</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="cover_image" id="cover_image" accept="image/*"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current cover.</p>
            </div>
            <div>
                <label for="pdf_file" class="block text-sm font-medium text-gray-300 mb-2">PDF File</label>
                <?php if (!empty($ebook['pdf_filename'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span class="text-sm text-gray-300"><?= h($ebook['pdf_filename']) ?></span>
                    </div>
                <?php endif; ?>
                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current PDF.</p>
            </div>
        </div>
    </div>

    <!-- Pricing & Details -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Pricing & Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="page_count" class="block text-sm font-medium text-gray-300 mb-2">Page Count</label>
                <input type="number" name="page_count" id="page_count" min="0"
                    value="<?= h($ebook['page_count'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., 45">
            </div>
            <div>
                <label for="price_dkk" class="block text-sm font-medium text-gray-300 mb-2">Price (DKK)</label>
                <div class="relative">
                    <input type="number" name="price_dkk" id="price_dkk" min="0" step="0.01"
                        value="<?= h($ebook['price_dkk'] ?? '0') ?>"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-16"
                        placeholder="0.00">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">DKK</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Set to 0 for a free ebook.</p>
            </div>
            <div class="md:col-span-2">
                <label for="meta_description" class="block text-sm font-medium text-gray-300 mb-2">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" maxlength="160"
                    value="<?= h($ebook['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="SEO description (max 160 characters)">
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Publishing</h3>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
            <select name="status" id="status"
                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="draft" <?= ($ebook['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($ebook['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/eboger" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Update Ebook
        </button>
    </div>
</form>

<script>
    tinymce.init({
        selector: '#description',
        skin: 'oxide-dark',
        content_css: 'dark',
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'charmap',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic | bullist numlist | link | removeformat | code',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 16px; color: #e5e7eb; background: #374151; }',
        branding: false,
        promotion: false
    });
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
