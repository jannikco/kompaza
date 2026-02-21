<?php
$pageTitle = 'Create Course';
$currentPage = 'courses';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kurser" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Courses</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Create Course</h2>
    <p class="text-sm text-gray-500 mt-1">Set up your new course. You can add modules and lessons after creating.</p>
</div>

<form method="POST" action="/admin/kurser/gem" enctype="multipart/form-data" class="max-w-4xl" x-data="{ pricingType: '<?= h($_POST['pricing_type'] ?? 'free') ?>' }">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($_POST['title'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Complete Digital Marketing Course">
            </div>
            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= h($_POST['slug'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="auto-generated-from-title">
                <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate from title.</p>
            </div>
            <div class="md:col-span-2">
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1.5">Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" value="<?= h($_POST['subtitle'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="A brief tagline for the course">
            </div>
            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1.5">Short Description</label>
                <textarea id="short_description" name="short_description" rows="2"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="A brief summary shown on catalog pages..."><?= h($_POST['short_description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label for="editor" class="block text-sm font-medium text-gray-700 mb-1.5">Full Description</label>
                <input type="hidden" name="description" id="editor-hidden" value="<?= h($_POST['description'] ?? '') ?>">
                <div id="editor-quill" class="bg-white"><?= $_POST['description'] ?? '' ?></div>
            </div>
        </div>
    </div>

    <!-- Cover Image -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cover Image</h3>
        <div>
            <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1.5">Course Thumbnail</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-500 transition">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="text-sm text-gray-500">
                        <label for="cover_image" class="relative cursor-pointer text-indigo-600 hover:text-indigo-500 font-medium">
                            <span>Upload a file</span>
                            <input id="cover_image" name="cover_image" type="file" class="sr-only" accept="image/*">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, WEBP. Recommended 16:9 ratio (1280x720)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pricing Type</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="pricingType === 'free' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="pricing_type" value="free" x-model="pricingType" class="sr-only">
                        <span class="text-sm font-medium text-gray-900">Free</span>
                    </label>
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="pricingType === 'one_time' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="pricing_type" value="one_time" x-model="pricingType" class="sr-only">
                        <span class="text-sm font-medium text-gray-900">One-Time Purchase</span>
                    </label>
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="pricingType === 'subscription' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="pricing_type" value="subscription" x-model="pricingType" class="sr-only">
                        <span class="text-sm font-medium text-gray-900">Subscription</span>
                    </label>
                </div>
            </div>

            <div x-show="pricingType === 'one_time'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="price_dkk" class="block text-sm font-medium text-gray-700 mb-1.5">Price (DKK)</label>
                    <div class="relative">
                        <input type="number" id="price_dkk" name="price_dkk" step="0.01" min="0" value="<?= h($_POST['price_dkk'] ?? '') ?>"
                               class="w-full pl-4 pr-16 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"><span class="text-gray-500 text-sm">DKK</span></div>
                    </div>
                </div>
                <div>
                    <label for="compare_price_dkk" class="block text-sm font-medium text-gray-700 mb-1.5">Compare Price (DKK)</label>
                    <div class="relative">
                        <input type="number" id="compare_price_dkk" name="compare_price_dkk" step="0.01" min="0" value="<?= h($_POST['compare_price_dkk'] ?? '') ?>"
                               class="w-full pl-4 pr-16 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"><span class="text-gray-500 text-sm">DKK</span></div>
                    </div>
                </div>
            </div>

            <div x-show="pricingType === 'subscription'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Monthly Price (DKK)</label>
                    <input type="number" name="subscription_price_monthly_dkk" step="0.01" min="0" value="<?= h($_POST['subscription_price_monthly_dkk'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Yearly Price (DKK)</label>
                    <input type="number" name="subscription_price_yearly_dkk" step="0.01" min="0" value="<?= h($_POST['subscription_price_yearly_dkk'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Stripe Monthly Price ID</label>
                    <input type="text" name="stripe_monthly_price_id" value="<?= h($_POST['stripe_monthly_price_id'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="price_xxx">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Stripe Yearly Price ID</label>
                    <input type="text" name="stripe_yearly_price_id" value="<?= h($_POST['stripe_yearly_price_id'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="price_xxx">
                </div>
            </div>
        </div>
    </div>

    <!-- Instructor -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Instructor</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="instructor_name" class="block text-sm font-medium text-gray-700 mb-1.5">Instructor Name</label>
                <input type="text" id="instructor_name" name="instructor_name" value="<?= h($_POST['instructor_name'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="John Doe">
            </div>
            <div>
                <label for="instructor_image" class="block text-sm font-medium text-gray-700 mb-1.5">Instructor Photo</label>
                <input type="file" id="instructor_image" name="instructor_image" accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
            </div>
            <div class="md:col-span-2">
                <label for="instructor_bio" class="block text-sm font-medium text-gray-700 mb-1.5">Instructor Bio</label>
                <textarea id="instructor_bio" name="instructor_bio" rows="3"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Brief bio..."><?= h($_POST['instructor_bio'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="flex items-end space-x-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600">Featured</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="drip_enabled" value="1" class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600">Enable drip content</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/kurser" class="px-4 py-2 text-sm text-gray-600 hover:text-white transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Course
        </button>
    </div>
</form>

<script>initRichEditor('editor-quill', 'editor-hidden', { height: 400 });</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
