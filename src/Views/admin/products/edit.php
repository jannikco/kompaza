<?php
$pageTitle = 'Edit Product: ' . h($product['name']);
$currentPage = 'products';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/produkter" class="text-sm text-gray-400 hover:text-white transition">&larr; Back to Products</a>
    <h2 class="text-2xl font-bold text-white mt-1">Edit Product</h2>
    <p class="text-sm text-gray-400 mt-1">Update product details for <?= h($product['name']) ?>.</p>
</div>

<form method="POST" action="/admin/produkter/opdater" enctype="multipart/form-data" class="max-w-4xl" x-data="{ trackStock: <?= $product['track_stock'] ? 'true' : 'false' ?>, isDigital: <?= $product['is_digital'] ? 'true' : 'false' ?> }">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $product['id'] ?>">

    <!-- Basic Information -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">Name <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" required value="<?= h($product['name']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Product name">
            </div>

            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-1.5">Slug <span class="text-red-400">*</span></label>
                <input type="text" id="slug" name="slug" required value="<?= h($product['slug']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="product-url-slug">
                <p class="text-xs text-gray-500 mt-1">URL-friendly identifier. Use lowercase letters, numbers, and hyphens.</p>
            </div>

            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-300 mb-1.5">Short Description</label>
                <textarea id="short_description" name="short_description" rows="2"
                          class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="A brief summary of the product..."><?= h($product['short_description'] ?? '') ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label for="editor" class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                <textarea id="editor" name="description" rows="10"
                          class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm"><?= h($product['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Media -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Media</h3>

        <?php if (!empty($product['image_path'])): ?>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Current Image</label>
            <div class="flex items-start space-x-4">
                <div class="w-32 h-32 rounded-lg overflow-hidden bg-gray-700 flex-shrink-0">
                    <img src="<?= h(imageUrl($product['image_path'])) ?>" alt="<?= h($product['name']) ?>" class="w-full h-full object-cover">
                </div>
                <div class="flex items-center pt-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 text-red-600 bg-gray-700 border-gray-600 rounded focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-400">Remove current image</span>
                    </label>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-300 mb-1.5"><?= !empty($product['image_path']) ? 'Replace Image' : 'Product Image' ?></label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-lg hover:border-gray-500 transition">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="text-sm text-gray-400">
                        <label for="image" class="relative cursor-pointer text-indigo-400 hover:text-indigo-300 font-medium">
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
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Pricing</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="price_dkk" class="block text-sm font-medium text-gray-300 mb-1.5">Price (DKK) <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="number" id="price_dkk" name="price_dkk" required step="0.01" min="0" value="<?= h($product['price_dkk']) ?>"
                           class="w-full pl-4 pr-16 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-sm">DKK</span>
                    </div>
                </div>
            </div>

            <div>
                <label for="compare_price_dkk" class="block text-sm font-medium text-gray-300 mb-1.5">Compare Price (DKK)</label>
                <div class="relative">
                    <input type="number" id="compare_price_dkk" name="compare_price_dkk" step="0.01" min="0" value="<?= h($product['compare_price_dkk'] ?? '') ?>"
                           class="w-full pl-4 pr-16 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-sm">DKK</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Original price shown as strikethrough. Leave empty if not on sale.</p>
            </div>
        </div>
    </div>

    <!-- Inventory -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Inventory</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="sku" class="block text-sm font-medium text-gray-300 mb-1.5">SKU</label>
                <input type="text" id="sku" name="sku" value="<?= h($product['sku'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Stock keeping unit">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
                <input type="text" id="category" name="category" value="<?= h($product['category'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Electronics, Clothing">
            </div>

            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" id="track_stock" name="track_stock" value="1" x-model="trackStock"
                           class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500"
                           <?= $product['track_stock'] ? 'checked' : '' ?>>
                    <label for="track_stock" class="ml-2 text-sm font-medium text-gray-300">Track stock quantity</label>
                </div>
            </div>

            <div x-show="trackStock" x-cloak>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-300 mb-1.5">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?= h($product['stock_quantity'] ?? 0) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="0">
            </div>
        </div>
    </div>

    <!-- Digital Product -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Digital Product</h3>
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" id="is_digital" name="is_digital" value="1" x-model="isDigital"
                       class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500"
                       <?= $product['is_digital'] ? 'checked' : '' ?>>
                <label for="is_digital" class="ml-2 text-sm font-medium text-gray-300">This is a digital product</label>
            </div>

            <div x-show="isDigital" x-cloak>
                <?php if (!empty($product['digital_file_path'])): ?>
                <div class="mb-3 p-3 bg-gray-700 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="text-sm text-gray-300">Current file: <?= h(basename($product['digital_file_path'])) ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <label for="digital_file" class="block text-sm font-medium text-gray-300 mb-1.5"><?= !empty($product['digital_file_path']) ? 'Replace Digital File' : 'Digital File' ?></label>
                <input type="file" id="digital_file" name="digital_file"
                       class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 file:cursor-pointer">
                <p class="text-xs text-gray-500 mt-1">Upload the file customers will receive after purchase (PDF, ZIP, etc.).</p>
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Publishing</h3>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
            <select id="status" name="status" class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $product['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div x-data="{ confirmDelete: false }">
            <template x-if="!confirmDelete">
                <button type="button" @click="confirmDelete = true" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Product
                </button>
            </template>
            <template x-if="confirmDelete">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-red-400">Are you sure?</span>
                    <button type="button" onclick="document.getElementById('delete-form').submit();" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        Yes, Delete
                    </button>
                    <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </template>
        </div>

        <div class="flex items-center space-x-3">
            <a href="/admin/produkter" class="px-4 py-2 text-sm text-gray-300 hover:text-white transition">Cancel</a>
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Product
            </button>
        </div>
    </div>
</form>

<!-- Hidden delete form -->
<form id="delete-form" method="POST" action="/admin/produkter/slet/<?= $product['id'] ?>" class="hidden">
    <?= csrfField() ?>
</form>

<!-- TinyMCE Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#editor',
            height: 400,
            skin: 'oxide-dark',
            content_css: 'dark',
            menubar: false,
            plugins: 'lists link image code table hr wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link image | table hr | code',
            branding: false,
            promotion: false,
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; color: #e5e7eb; background: #374151; }',
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
