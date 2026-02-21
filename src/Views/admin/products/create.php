<?php
$pageTitle = 'Add Product';
$currentPage = 'products';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/produkter" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Products</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Add Product</h2>
    <p class="text-sm text-gray-500 mt-1">Create a new product in your catalog.</p>
</div>

<form method="POST" action="/admin/produkter/gem" enctype="multipart/form-data" class="max-w-4xl" x-data="{ trackStock: false, isDigital: false }">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-600">*</span></label>
                <input type="text" id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Product name">
            </div>

            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">Slug <span class="text-red-600">*</span></label>
                <input type="text" id="slug" name="slug" required value="<?= h($_POST['slug'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="product-url-slug">
                <p class="text-xs text-gray-500 mt-1">URL-friendly identifier. Use lowercase letters, numbers, and hyphens.</p>
            </div>

            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1.5">Short Description</label>
                <textarea id="short_description" name="short_description" rows="2"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="A brief summary of the product..."><?= h($_POST['short_description'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="editor" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <input type="hidden" name="description" id="editor-hidden" value="<?= h($_POST['description'] ?? '') ?>">
                <div id="editor-quill" class="bg-white"><?= $_POST['description'] ?? '' ?></div>
            </div>
        </div>
    </div>

    <!-- Media -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Media</h3>
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1.5">Product Image</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-500 transition">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="text-sm text-gray-500">
                        <label for="image" class="relative cursor-pointer text-indigo-600 hover:text-indigo-500 font-medium">
                            <span>Upload a file</span>
                            <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                        </label>
                        <span class="pl-1">or drag and drop</span>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, WEBP up to 5MB</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="price_dkk" class="block text-sm font-medium text-gray-700 mb-1.5">Price (DKK) <span class="text-red-600">*</span></label>
                <div class="relative">
                    <input type="number" id="price_dkk" name="price_dkk" required step="0.01" min="0" value="<?= h($_POST['price_dkk'] ?? '') ?>"
                           class="w-full pl-4 pr-16 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">DKK</span>
                    </div>
                </div>
            </div>

            <div>
                <label for="compare_price_dkk" class="block text-sm font-medium text-gray-700 mb-1.5">Compare Price (DKK)</label>
                <div class="relative">
                    <input type="number" id="compare_price_dkk" name="compare_price_dkk" step="0.01" min="0" value="<?= h($_POST['compare_price_dkk'] ?? '') ?>"
                           class="w-full pl-4 pr-16 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">DKK</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Original price shown as strikethrough. Leave empty if not on sale.</p>
            </div>
        </div>
    </div>

    <!-- Inventory -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700 mb-1.5">SKU</label>
                <input type="text" id="sku" name="sku" value="<?= h($_POST['sku'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Stock keeping unit">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <input type="text" id="category" name="category" value="<?= h($_POST['category'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Electronics, Clothing">
            </div>

            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" id="track_stock" name="track_stock" value="1" x-model="trackStock"
                           class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                    <label for="track_stock" class="ml-2 text-sm font-medium text-gray-700">Track stock quantity</label>
                </div>
            </div>

            <div x-show="trackStock" x-cloak>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1.5">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?= h($_POST['stock_quantity'] ?? '0') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="0">
            </div>
        </div>
    </div>

    <!-- Digital Product -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Digital Product</h3>
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" id="is_digital" name="is_digital" value="1" x-model="isDigital"
                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <label for="is_digital" class="ml-2 text-sm font-medium text-gray-700">This is a digital product</label>
            </div>

            <div x-show="isDigital" x-cloak>
                <label for="digital_file" class="block text-sm font-medium text-gray-700 mb-1.5">Digital File</label>
                <input type="file" id="digital_file" name="digital_file"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 file:cursor-pointer">
                <p class="text-xs text-gray-500 mt-1">Upload the file customers will receive after purchase (PDF, ZIP, etc.).</p>
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Publishing</h3>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
            <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="draft" <?= ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="archived" <?= ($_POST['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/produkter" class="px-4 py-2 text-sm text-gray-600 hover:text-white transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Product
        </button>
    </div>
</form>

<script>initRichEditor('editor-quill', 'editor-hidden', { height: 400 });</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
