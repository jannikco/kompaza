<?php
$pageTitle = 'Add Lesson';
$currentPage = 'courses';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kurser/rediger?id=<?= $course['id'] ?>" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to <?= h($course['title']) ?></a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Add Lesson</h2>
    <p class="text-sm text-gray-500 mt-1">Module: <?= h($module['title']) ?></p>
</div>

<form method="POST" action="/admin/kurser/lektion/gem" class="max-w-4xl" x-data="{ lessonType: '<?= h($_POST['lesson_type'] ?? 'video') ?>' }">
    <?= csrfField() ?>
    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
    <input type="hidden" name="module_id" value="<?= $module['id'] ?>">

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lesson Details</h3>
        <div class="space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($_POST['title'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="Lesson title">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Type</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'video' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="video" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span class="text-sm text-white">Video</span>
                    </label>
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'text' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="text" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="text-sm text-white">Text</span>
                    </label>
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'video_text' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="video_text" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4"/></svg>
                        <span class="text-sm text-white">Video + Text</span>
                    </label>
                    <label class="flex items-center p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'download' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="download" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                        <span class="text-sm text-white">Download</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Text Content (shown for text, video_text, download types) -->
    <div x-show="lessonType !== 'video'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lesson Content</h3>
        <input type="hidden" name="text_content" id="lesson_editor-hidden" value="<?= h($_POST['text_content'] ?? '') ?>">
        <div id="lesson_editor-quill" class="bg-white"><?= $_POST['text_content'] ?? '' ?></div>
    </div>

    <!-- Settings -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
        <div class="space-y-3">
            <label class="flex items-center">
                <input type="checkbox" name="is_preview" value="1" class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-600">Free preview (accessible without enrollment)</span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/kurser/rediger?id=<?= $course['id'] ?>" class="px-4 py-2 text-sm text-gray-600 hover:text-white transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            Create Lesson
        </button>
    </div>
</form>

<script>initRichEditor('lesson_editor-quill', 'lesson_editor-hidden', { height: 400 });</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
