<?php
$pageTitle = 'Edit Lead Magnet';
$currentPage = 'lead-magnets';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/lead-magnets" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Lead Magnets
    </a>
</div>

<form method="POST" action="/admin/lead-magnets/opdater" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $leadMagnet['id'] ?>">

    <!-- Basic Information -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title</label>
                <input type="text" name="title" id="title" required
                    value="<?= h($leadMagnet['title']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., 10 Tips for Better Marketing">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" required
                    value="<?= h($leadMagnet['slug']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="10-tips-for-better-marketing">
            </div>
            <div class="md:col-span-2">
                <label for="subtitle" class="block text-sm font-medium text-gray-300 mb-2">Subtitle</label>
                <input type="text" name="subtitle" id="subtitle"
                    value="<?= h($leadMagnet['subtitle'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="A short subtitle for the lead magnet">
            </div>
            <div class="md:col-span-2">
                <label for="meta_description" class="block text-sm font-medium text-gray-300 mb-2">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" maxlength="160"
                    value="<?= h($leadMagnet['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="SEO description (max 160 characters)">
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Hero Section</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="hero_headline" class="block text-sm font-medium text-gray-300 mb-2">Hero Headline</label>
                <input type="text" name="hero_headline" id="hero_headline"
                    value="<?= h($leadMagnet['hero_headline'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Main headline on the landing page">
            </div>
            <div class="md:col-span-2">
                <label for="hero_subheadline" class="block text-sm font-medium text-gray-300 mb-2">Hero Subheadline</label>
                <input type="text" name="hero_subheadline" id="hero_subheadline"
                    value="<?= h($leadMagnet['hero_subheadline'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Supporting text below the headline">
            </div>
            <div>
                <label for="hero_cta_text" class="block text-sm font-medium text-gray-300 mb-2">Hero CTA Text</label>
                <input type="text" name="hero_cta_text" id="hero_cta_text"
                    value="<?= h($leadMagnet['hero_cta_text'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Download Free Guide">
            </div>
            <div>
                <label for="hero_bg_color" class="block text-sm font-medium text-gray-300 mb-2">Hero Background Color</label>
                <div class="flex items-center space-x-3">
                    <input type="color" name="hero_bg_color" id="hero_bg_color" value="<?= h($leadMagnet['hero_bg_color'] ?? '#1e1b4b') ?>"
                        class="w-12 h-10 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                    <input type="text" id="hero_bg_color_text" value="<?= h($leadMagnet['hero_bg_color'] ?? '#1e1b4b') ?>"
                        class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="#1e1b4b"
                        oninput="document.getElementById('hero_bg_color').value = this.value"
                        onchange="document.getElementById('hero_bg_color').value = this.value">
                </div>
            </div>
            <div class="md:col-span-2">
                <label for="hero_image" class="block text-sm font-medium text-gray-300 mb-2">Hero Image</label>
                <?php if (!empty($leadMagnet['hero_image'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <img src="<?= h($leadMagnet['hero_image']) ?>" alt="Current hero image" class="h-16 w-auto rounded-lg border border-gray-600">
                        <span class="text-sm text-gray-400">Current image</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="hero_image" id="hero_image" accept="image/*"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current image.</p>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Features Section</h3>
        <div>
            <label for="features_headline" class="block text-sm font-medium text-gray-300 mb-2">Features Headline</label>
            <input type="text" name="features_headline" id="features_headline"
                value="<?= h($leadMagnet['features_headline'] ?? '') ?>"
                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., What You'll Learn">
        </div>
    </div>

    <!-- PDF File -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Downloadable PDF</h3>
        <div>
            <label for="pdf_file" class="block text-sm font-medium text-gray-300 mb-2">PDF File</label>
            <?php if (!empty($leadMagnet['pdf_filename'])): ?>
                <div class="mb-3 flex items-center space-x-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span class="text-sm text-gray-300"><?= h($leadMagnet['pdf_filename']) ?></span>
                </div>
            <?php endif; ?>
            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf"
                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
            <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current PDF. Upload a new file to replace it.</p>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Email Delivery</h3>
        <div class="space-y-6">
            <div>
                <label for="email_subject" class="block text-sm font-medium text-gray-300 mb-2">Email Subject</label>
                <input type="text" name="email_subject" id="email_subject"
                    value="<?= h($leadMagnet['email_subject'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Here's your free guide!">
            </div>
            <div>
                <label for="email_body" class="block text-sm font-medium text-gray-300 mb-2">Email Body</label>
                <textarea name="email_body" id="email_body" rows="6"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="The email content that will be sent with the download link..."><?= h($leadMagnet['email_body'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-2">Use {{download_link}} to insert the PDF download link. Use {{name}} to insert the recipient's name.</p>
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
                <option value="draft" <?= ($leadMagnet['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($leadMagnet['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/lead-magnets" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Update Lead Magnet
        </button>
    </div>
</form>

<script>
    // Sync color picker with text input
    document.getElementById('hero_bg_color').addEventListener('input', function() {
        document.getElementById('hero_bg_color_text').value = this.value;
    });
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
