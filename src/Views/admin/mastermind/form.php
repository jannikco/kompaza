<?php
$isEdit = !empty($program);
$pageTitle = $isEdit ? 'Edit Program: ' . h($program['title']) : 'Create Mastermind Program';
$currentPage = 'mastermind';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/mastermind" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Mastermind Programs
    </a>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/mastermind/update' : '/admin/mastermind/store' ?>" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= $program['id'] ?>">
    <?php endif; ?>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Program Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <input type="text" name="title" id="title" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($program['title'] ?? '') ?>"
                    placeholder="e.g., Executive Mastermind Q1 2026">
            </div>
            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">URL Slug</label>
                <input type="text" name="slug" id="slug"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($program['slug'] ?? '') ?>"
                    placeholder="auto-generated-from-title">
                <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate from title.</p>
            </div>
            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-300 mb-2">Short Description</label>
                <textarea name="short_description" id="short_description" rows="2"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="A brief one-liner for listings..."><?= h($program['short_description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Full Description</label>
                <textarea name="description" id="description" rows="10"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= $program['description'] ?? '' ?></textarea>
            </div>
            <div>
                <label for="cover_image" class="block text-sm font-medium text-gray-300 mb-2">Cover Image</label>
                <?php if ($isEdit && !empty($program['cover_image_path'])): ?>
                <div class="mb-3">
                    <img src="<?= h(imageUrl($program['cover_image_path'])) ?>" alt="Cover" class="h-32 w-auto rounded-lg object-cover">
                </div>
                <?php endif; ?>
                <input type="file" name="cover_image" id="cover_image" accept="image/*"
                    class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 file:cursor-pointer">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="draft" <?= ($program['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($program['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= ($program['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/mastermind" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            <?= $isEdit ? 'Update Program' : 'Create Program' ?>
        </button>
    </div>
</form>

<script>
tinymce.init({
    selector: '#description',
    height: 400,
    menubar: false,
    plugins: 'lists link',
    toolbar: 'undo redo | bold italic | bullist numlist | link',
    skin: 'oxide-dark',
    content_css: 'dark',
    body_class: 'bg-gray-700 text-white',
});
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
