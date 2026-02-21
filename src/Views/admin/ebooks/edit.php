<?php
$pageTitle = 'Edit Ebook';
$currentPage = 'ebooks';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/eboger" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Ebooks
    </a>
</div>

<form method="POST" action="/admin/eboger/opdater" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $ebook['id'] ?>">

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Ebook Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" name="title" id="title" required
                    value="<?= h($ebook['title']) ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Ebook title">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" required
                    value="<?= h($ebook['slug']) ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="ebook-url-slug">
            </div>
            <div class="md:col-span-2">
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                <input type="text" name="subtitle" id="subtitle"
                    value="<?= h($ebook['subtitle'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="A short subtitle for the ebook">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="hidden" name="description" id="description-hidden" value="<?= h($ebook['description'] ?? '') ?>">
                <div id="description-editor" class="bg-white"><?= $ebook['description'] ?? '' ?></div>
                <p class="text-xs text-gray-500 mt-2">Supports rich text formatting via the editor.</p>
            </div>
        </div>
    </div>

    <!-- Landing Page (Sales Page Mode) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" x-data="{ open: <?= !empty($ebook['hero_headline']) ? 'true' : 'false' ?> }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
            <h3 class="text-lg font-semibold text-gray-900">Landing Page</h3>
            <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <p class="text-xs text-gray-500 mt-1">Set a Hero Headline to enable the full sales page layout.</p>
        <div x-show="open" x-collapse x-cloak class="mt-6 space-y-6">
            <!-- Hero -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="hero_headline" class="block text-sm font-medium text-gray-700 mb-2">Hero Headline</label>
                    <input type="text" name="hero_headline" id="hero_headline"
                        value="<?= h($ebook['hero_headline'] ?? '') ?>"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Main headline for the hero section">
                </div>
                <div>
                    <label for="hero_cta_text" class="block text-sm font-medium text-gray-700 mb-2">CTA Button Text</label>
                    <input type="text" name="hero_cta_text" id="hero_cta_text"
                        value="<?= h($ebook['hero_cta_text'] ?? '') ?>"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g., Buy Now, Get Your Copy">
                </div>
                <div class="md:col-span-2">
                    <label for="hero_subheadline" class="block text-sm font-medium text-gray-700 mb-2">Hero Subheadline</label>
                    <textarea name="hero_subheadline" id="hero_subheadline" rows="2"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Supporting text below the headline"><?= h($ebook['hero_subheadline'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="hero_bg_color" class="block text-sm font-medium text-gray-700 mb-2">Hero Background Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="hero_bg_color" id="hero_bg_color"
                            value="<?= h($ebook['hero_bg_color'] ?? '#1e40af') ?>"
                            class="w-12 h-10 rounded cursor-pointer border border-gray-300">
                        <span class="text-sm text-gray-500"><?= h($ebook['hero_bg_color'] ?? 'Default: brand color') ?></span>
                    </div>
                </div>
                <div>
                    <label for="features_headline" class="block text-sm font-medium text-gray-700 mb-2">Features Section Headline</label>
                    <input type="text" name="features_headline" id="features_headline"
                        value="<?= h($ebook['features_headline'] ?? '') ?>"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Default: What You'll Learn">
                </div>
            </div>

            <!-- Features JSON -->
            <div>
                <label for="features" class="block text-sm font-medium text-gray-700 mb-2">Features (JSON)</label>
                <textarea name="features" id="features" rows="5"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder='[{"icon": "🎯", "title": "Feature Title", "description": "Description"}]'><?= h($ebook['features'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">JSON array. Each item: {"icon": "emoji", "title": "...", "description": "..."}</p>
            </div>

            <!-- Key Metrics JSON -->
            <div>
                <label for="key_metrics" class="block text-sm font-medium text-gray-700 mb-2">Key Metrics (JSON)</label>
                <textarea name="key_metrics" id="key_metrics" rows="3"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder='[{"value": "120+", "label": "Pages"}]'><?= h($ebook['key_metrics'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">JSON array. Each item: {"value": "120+", "label": "Pages"}. Auto-generated from page count if empty.</p>
            </div>

            <!-- Chapters JSON -->
            <div>
                <label for="chapters" class="block text-sm font-medium text-gray-700 mb-2">Chapters (JSON)</label>
                <textarea name="chapters" id="chapters" rows="5"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder='[{"title": "Chapter Title", "description": "What this chapter covers"}]'><?= h($ebook['chapters'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">JSON array. Each item: {"title": "...", "description": "..."}</p>
            </div>

            <!-- Target Audience JSON -->
            <div>
                <label for="target_audience" class="block text-sm font-medium text-gray-700 mb-2">Target Audience (JSON)</label>
                <textarea name="target_audience" id="target_audience" rows="4"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder='[{"icon": "👩‍💼", "title": "Persona", "description": "Why this is for them"}]'><?= h($ebook['target_audience'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">JSON array. Each item: {"icon": "emoji", "title": "...", "description": "..."}</p>
            </div>

            <!-- Testimonials JSON -->
            <div>
                <label for="testimonials" class="block text-sm font-medium text-gray-700 mb-2">Testimonials (JSON)</label>
                <textarea name="testimonials" id="testimonials" rows="4"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder='[{"quote": "Great book!", "name": "John Doe", "title": "CEO"}]'><?= h($ebook['testimonials'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">JSON array. Each item: {"quote": "...", "name": "...", "title": "..."}</p>
            </div>

            <!-- FAQ JSON -->
            <div>
                <label for="faq" class="block text-sm font-medium text-gray-700 mb-2">FAQ (JSON)</label>
                <textarea name="faq" id="faq" rows="4"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder='[{"question": "Is this for beginners?", "answer": "Yes, absolutely!"}]'><?= h($ebook['faq'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">JSON array. Each item: {"question": "...", "answer": "..."}</p>
            </div>
        </div>
    </div>

    <!-- Files & Media -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Files & Media</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                <?php if (!empty($ebook['cover_image'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <img src="<?= h(imageUrl($ebook['cover_image'])) ?>" alt="Current cover" class="h-20 w-auto rounded-lg border border-gray-300">
                        <span class="text-sm text-gray-500">Current cover</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="cover_image" id="cover_image" accept="image/*"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current cover.</p>
            </div>
            <div>
                <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">PDF File</label>
                <?php if (!empty($ebook['pdf_filename'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span class="text-sm text-gray-600"><?= h($ebook['pdf_filename']) ?></span>
                    </div>
                <?php endif; ?>
                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current PDF.</p>
            </div>
        </div>
    </div>

    <!-- Pricing & Details -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Pricing & Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="page_count" class="block text-sm font-medium text-gray-700 mb-2">Page Count</label>
                <input type="number" name="page_count" id="page_count" min="0"
                    value="<?= h($ebook['page_count'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., 45">
            </div>
            <div>
                <label for="price_dkk" class="block text-sm font-medium text-gray-700 mb-2">Price (DKK)</label>
                <div class="relative">
                    <input type="number" name="price_dkk" id="price_dkk" min="0" step="0.01"
                        value="<?= h($ebook['price_dkk'] ?? '0') ?>"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-16"
                        placeholder="0.00">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-500">DKK</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Set to 0 for a free ebook.</p>
            </div>
            <div class="md:col-span-2">
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" maxlength="160"
                    value="<?= h($ebook['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="SEO description (max 160 characters)">
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Publishing</h3>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" id="status"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="draft" <?= ($ebook['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($ebook['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/eboger" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Update Ebook
        </button>
    </div>
</form>

<script>initRichEditor('description-editor', 'description-hidden', { height: 400, simple: true });</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
