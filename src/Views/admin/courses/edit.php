<?php
$pageTitle = 'Edit Course: ' . ($course['title'] ?? '');
$currentPage = 'courses';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kurser" class="text-sm text-gray-400 hover:text-white transition">&larr; Back to Courses</a>
    <div class="flex items-center justify-between mt-1">
        <h2 class="text-2xl font-bold text-white"><?= h($course['title']) ?></h2>
        <div class="flex items-center space-x-2">
            <?php if ($course['status'] === 'published'): ?>
                <a href="/course/<?= h($course['slug']) ?>" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                    View Course &rarr;
                </a>
            <?php endif; ?>
            <a href="/admin/kurser/tilmeldinger?course_id=<?= $course['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                Students (<?= (int)$course['enrollment_count'] ?>)
            </a>
        </div>
    </div>
    <p class="text-sm text-gray-400 mt-1"><?= (int)$course['total_lessons'] ?> lessons &middot; <?= $course['total_duration_seconds'] ? gmdate('H:i:s', $course['total_duration_seconds']) : '0:00' ?> total</p>
</div>

<!-- Course Details Form -->
<form method="POST" action="/admin/kurser/opdater" enctype="multipart/form-data" class="max-w-4xl" x-data="{ pricingType: '<?= h($course['pricing_type']) ?>' }">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $course['id'] ?>">

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Course Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-300 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($course['title']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-1.5">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= h($course['slug']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-300 mb-1.5">Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" value="<?= h($course['subtitle'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-300 mb-1.5">Short Description</label>
                <textarea id="short_description" name="short_description" rows="2"
                          class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"><?= h($course['short_description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label for="editor" class="block text-sm font-medium text-gray-300 mb-1.5">Full Description</label>
                <textarea id="editor" name="description" rows="10" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg text-sm"><?= h($course['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Cover Image -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Cover Image</h3>
        <?php if (!empty($course['cover_image_path'])): ?>
            <div class="mb-4">
                <img src="<?= h($course['cover_image_path']) ?>" alt="" class="w-48 h-auto rounded-lg">
            </div>
        <?php endif; ?>
        <input type="file" name="cover_image" accept="image/*"
               class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
    </div>

    <!-- Pricing -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Pricing</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="pricingType === 'free' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                    <input type="radio" name="pricing_type" value="free" x-model="pricingType" class="sr-only">
                    <span class="text-sm font-medium text-white">Free</span>
                </label>
                <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="pricingType === 'one_time' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                    <input type="radio" name="pricing_type" value="one_time" x-model="pricingType" class="sr-only">
                    <span class="text-sm font-medium text-white">One-Time</span>
                </label>
                <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="pricingType === 'subscription' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                    <input type="radio" name="pricing_type" value="subscription" x-model="pricingType" class="sr-only">
                    <span class="text-sm font-medium text-white">Subscription</span>
                </label>
            </div>
            <div x-show="pricingType === 'one_time'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Price (DKK)</label>
                    <input type="number" name="price_dkk" step="0.01" min="0" value="<?= h($course['price_dkk'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Compare Price (DKK)</label>
                    <input type="number" name="compare_price_dkk" step="0.01" min="0" value="<?= h($course['compare_price_dkk'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>
            <div x-show="pricingType === 'subscription'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Monthly (DKK)</label>
                    <input type="number" name="subscription_price_monthly_dkk" step="0.01" min="0" value="<?= h($course['subscription_price_monthly_dkk'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Yearly (DKK)</label>
                    <input type="number" name="subscription_price_yearly_dkk" step="0.01" min="0" value="<?= h($course['subscription_price_yearly_dkk'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Stripe Monthly Price ID</label>
                    <input type="text" name="stripe_monthly_price_id" value="<?= h($course['stripe_monthly_price_id'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="price_xxx">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Stripe Yearly Price ID</label>
                    <input type="text" name="stripe_yearly_price_id" value="<?= h($course['stripe_yearly_price_id'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="price_xxx">
                </div>
            </div>
        </div>
    </div>

    <!-- Instructor -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Instructor</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Name</label>
                <input type="text" name="instructor_name" value="<?= h($course['instructor_name'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Photo</label>
                <?php if (!empty($course['instructor_image_path'])): ?>
                    <img src="<?= h($course['instructor_image_path']) ?>" class="w-10 h-10 rounded-full mb-2">
                <?php endif; ?>
                <input type="file" name="instructor_image" accept="image/*"
                       class="w-full text-sm text-gray-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Bio</label>
                <textarea name="instructor_bio" rows="2" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"><?= h($course['instructor_bio'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $course['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $course['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="flex items-end space-x-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" <?= $course['is_featured'] ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-300">Featured</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="drip_enabled" value="1" <?= $course['drip_enabled'] ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-300">Drip content</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3 mb-10">
        <a href="/admin/kurser" class="px-4 py-2 text-sm text-gray-300 hover:text-white transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            Save Course
        </button>
    </div>
</form>

<!-- Modules & Lessons Section -->
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white">Curriculum</h3>
    </div>

    <!-- Modules list -->
    <div class="space-y-4" id="modules-list">
        <?php foreach ($modules as $module): ?>
        <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden" x-data="{ open: true, editModule: false }">
            <!-- Module header -->
            <div class="flex items-center justify-between px-5 py-3 bg-gray-750 border-b border-gray-700 cursor-pointer" @click="open = !open">
                <div class="flex items-center space-x-3">
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <h4 class="text-sm font-semibold text-white"><?= h($module['title']) ?></h4>
                    <span class="text-xs text-gray-500"><?= count($module['lessons']) ?> lessons</span>
                </div>
                <div class="flex items-center space-x-2" @click.stop>
                    <button @click="editModule = !editModule" class="text-xs text-gray-400 hover:text-white px-2 py-1 rounded hover:bg-gray-700 transition">Edit</button>
                    <form method="POST" action="/admin/kurser/modul/slet" onsubmit="return confirm('Delete this module and all its lessons?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        <button type="submit" class="text-xs text-red-400 hover:text-red-300 px-2 py-1 rounded hover:bg-gray-700 transition">Delete</button>
                    </form>
                </div>
            </div>

            <!-- Edit module inline form -->
            <div x-show="editModule" x-cloak class="px-5 py-3 bg-gray-800/50 border-b border-gray-700">
                <form method="POST" action="/admin/kurser/modul/opdater" class="flex items-end gap-3">
                    <?= csrfField() ?>
                    <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-400 mb-1">Title</label>
                        <input type="text" name="title" value="<?= h($module['title']) ?>" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-400 mb-1">Description</label>
                        <input type="text" name="description" value="<?= h($module['description'] ?? '') ?>"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Save</button>
                </form>
            </div>

            <!-- Lessons list -->
            <div x-show="open" x-cloak class="divide-y divide-gray-700/50">
                <?php if (empty($module['lessons'])): ?>
                    <div class="px-5 py-4 text-sm text-gray-500 text-center">No lessons in this module yet.</div>
                <?php else: ?>
                    <?php foreach ($module['lessons'] as $lesson): ?>
                    <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-750/50 transition group">
                        <div class="flex items-center space-x-3">
                            <?php
                            $typeIcons = [
                                'video' => '<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
                                'text' => '<svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                                'video_text' => '<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>',
                                'download' => '<svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                            ];
                            echo $typeIcons[$lesson['lesson_type']] ?? $typeIcons['video'];
                            ?>
                            <span class="text-sm text-gray-200"><?= h($lesson['title']) ?></span>
                            <?php if ($lesson['is_preview']): ?>
                                <span class="px-1.5 py-0.5 text-xs rounded bg-green-900/50 text-green-300">Preview</span>
                            <?php endif; ?>
                            <?php if ($lesson['video_status']): ?>
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-900/50 text-yellow-300',
                                    'uploading' => 'bg-blue-900/50 text-blue-300',
                                    'transcoding' => 'bg-purple-900/50 text-purple-300',
                                    'ready' => 'bg-green-900/50 text-green-300',
                                    'failed' => 'bg-red-900/50 text-red-300',
                                ];
                                $vsColor = $statusColors[$lesson['video_status']] ?? 'bg-gray-700 text-gray-300';
                                ?>
                                <span class="px-1.5 py-0.5 text-xs rounded <?= $vsColor ?>"><?= h($lesson['video_status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition">
                            <?php if ($lesson['video_duration_seconds']): ?>
                                <span class="text-xs text-gray-500"><?= gmdate('i:s', $lesson['video_duration_seconds']) ?></span>
                            <?php endif; ?>
                            <a href="/admin/kurser/lektion?id=<?= $lesson['id'] ?>" class="text-xs text-gray-400 hover:text-white px-2 py-1 rounded hover:bg-gray-700 transition">Edit</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Add lesson button -->
                <div class="px-5 py-3">
                    <a href="/admin/kurser/lektion/opret?course_id=<?= $course['id'] ?>&module_id=<?= $module['id'] ?>"
                       class="inline-flex items-center text-xs text-indigo-400 hover:text-indigo-300 font-medium transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Lesson
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Module Form -->
    <div class="mt-6 bg-gray-800 border border-gray-700 rounded-xl p-5" x-data="{ showForm: false }">
        <button @click="showForm = !showForm" class="inline-flex items-center text-sm text-indigo-400 hover:text-indigo-300 font-medium transition" x-show="!showForm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Module
        </button>
        <form x-show="showForm" x-cloak method="POST" action="/admin/kurser/modul/gem" class="space-y-3">
            <?= csrfField() ?>
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Module Title <span class="text-red-400">*</span></label>
                <input type="text" name="title" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                       placeholder="e.g. Module 1: Getting Started">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Description</label>
                <input type="text" name="description" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                       placeholder="Brief description (optional)">
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Add Module</button>
                <button type="button" @click="showForm = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#editor',
            height: 300,
            skin: 'oxide-dark',
            content_css: 'dark',
            menubar: false,
            plugins: 'lists link image code table hr wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link image | code',
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
