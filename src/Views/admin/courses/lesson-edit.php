<?php
$pageTitle = 'Edit Lesson: ' . ($lesson['title'] ?? '');
$currentPage = 'courses';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kurser/rediger?id=<?= $course['id'] ?>" class="text-sm text-gray-400 hover:text-white transition">&larr; Back to <?= h($course['title']) ?></a>
    <h2 class="text-2xl font-bold text-white mt-1"><?= h($lesson['title']) ?></h2>
    <p class="text-sm text-gray-400 mt-1">Module: <?= h($module['title']) ?></p>
</div>

<form method="POST" action="/admin/kurser/lektion/opdater" class="max-w-4xl" x-data="{ lessonType: '<?= h($lesson['lesson_type']) ?>' }">
    <?= csrfField() ?>
    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Lesson Details</h3>
        <div class="space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($lesson['title']) ?>"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Lesson Type</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'video' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="video" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span class="text-sm text-white">Video</span>
                    </label>
                    <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'text' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="text" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="text-sm text-white">Text</span>
                    </label>
                    <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'video_text' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="video_text" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4"/></svg>
                        <span class="text-sm text-white">Video + Text</span>
                    </label>
                    <label class="flex items-center p-3 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer hover:border-indigo-500 transition" :class="lessonType === 'download' ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                        <input type="radio" name="lesson_type" value="download" x-model="lessonType" class="sr-only">
                        <svg class="w-4 h-4 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                        <span class="text-sm text-white">Download</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Upload -->
    <div x-show="lessonType === 'video' || lessonType === 'video_text'" x-cloak class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Video</h3>
        <?php if ($lesson['video_status'] === 'ready'): ?>
            <div class="mb-4 p-3 bg-green-900/30 border border-green-700 rounded-lg">
                <p class="text-sm text-green-300">Video ready: <?= h($lesson['video_original_filename']) ?>
                    <?php if ($lesson['video_duration_seconds']): ?>(<?= gmdate('H:i:s', $lesson['video_duration_seconds']) ?>)<?php endif; ?>
                </p>
            </div>
        <?php elseif ($lesson['video_status'] === 'transcoding' || $lesson['video_status'] === 'pending'): ?>
            <div class="mb-4 p-3 bg-yellow-900/30 border border-yellow-700 rounded-lg" x-data="{ status: '<?= h($lesson['video_status']) ?>' }" x-init="
                let poll = setInterval(async () => {
                    let res = await fetch('/api/courses/video-status?lesson_id=<?= $lesson['id'] ?>');
                    let data = await res.json();
                    status = data.status;
                    if (data.status === 'ready' || data.status === 'failed') { clearInterval(poll); location.reload(); }
                }, 5000);
            ">
                <p class="text-sm text-yellow-300">Video is <span x-text="status">processing</span>... This page will refresh automatically.</p>
            </div>
        <?php elseif ($lesson['video_status'] === 'failed'): ?>
            <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-lg">
                <p class="text-sm text-red-300">Transcoding failed: <?= h($lesson['video_error_message'] ?? 'Unknown error') ?></p>
            </div>
        <?php endif; ?>

        <!-- Chunked upload widget -->
        <div x-data="videoUploader()" class="space-y-3">
            <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-indigo-500 transition"
                 @dragover.prevent="dragover = true" @dragleave="dragover = false"
                 @drop.prevent="dragover = false; handleDrop($event)"
                 :class="dragover ? 'border-indigo-500 bg-indigo-900/10' : ''"
                 @click="$refs.videoInput.click()">
                <input type="file" x-ref="videoInput" class="hidden" accept="video/*" @change="handleFile($event)">
                <svg class="mx-auto h-10 w-10 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-gray-400" x-show="!uploading">Click or drag video file here</p>
                <p class="text-sm text-gray-400" x-show="!uploading">MP4, MOV, AVI, MKV up to 5GB</p>
            </div>
            <div x-show="uploading" x-cloak>
                <div class="flex items-center justify-between text-sm text-gray-300 mb-1">
                    <span x-text="fileName"></span>
                    <span x-text="Math.round(progress) + '%'"></span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full transition-all" :style="'width: ' + progress + '%'"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="statusMessage"></p>
            </div>
            <div x-show="error" x-cloak class="p-3 bg-red-900/30 border border-red-700 rounded-lg">
                <p class="text-sm text-red-300" x-text="error"></p>
            </div>
        </div>
    </div>

    <!-- Text Content -->
    <div x-show="lessonType !== 'video'" x-cloak class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Lesson Content</h3>
        <textarea id="lesson_editor" name="text_content" rows="15"
                  class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg text-sm"><?= h($lesson['text_content'] ?? '') ?></textarea>
    </div>

    <!-- Settings -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Settings</h3>
        <label class="flex items-center">
            <input type="checkbox" name="is_preview" value="1" <?= $lesson['is_preview'] ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
            <span class="ml-2 text-sm text-gray-300">Free preview (accessible without enrollment)</span>
        </label>
    </div>

    <div class="flex items-center justify-between">
        <form method="POST" action="/admin/kurser/lektion/slet" onsubmit="return confirm('Delete this lesson?')">
            <?= csrfField() ?>
            <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition">Delete Lesson</button>
        </form>
        <div class="flex items-center space-x-3">
            <a href="/admin/kurser/rediger?id=<?= $course['id'] ?>" class="px-4 py-2 text-sm text-gray-300 hover:text-white transition">Cancel</a>
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Save Lesson
            </button>
        </div>
    </div>
</form>

<script>
function videoUploader() {
    return {
        uploading: false,
        progress: 0,
        fileName: '',
        statusMessage: '',
        error: '',
        dragover: false,
        chunkSize: 10 * 1024 * 1024, // 10MB

        handleFile(event) {
            const file = event.target.files[0];
            if (file) this.startUpload(file);
        },
        handleDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file) this.startUpload(file);
        },
        async startUpload(file) {
            this.uploading = true;
            this.progress = 0;
            this.error = '';
            this.fileName = file.name;
            this.statusMessage = 'Preparing upload...';

            const uploadId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const totalChunks = Math.ceil(file.size / this.chunkSize);
            const csrfToken = document.querySelector('input[name="<?= CSRF_TOKEN_NAME ?>"]')?.value || '';

            for (let i = 0; i < totalChunks; i++) {
                const start = i * this.chunkSize;
                const end = Math.min(start + this.chunkSize, file.size);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('chunk', chunk);
                formData.append('chunk_index', i);
                formData.append('total_chunks', totalChunks);
                formData.append('upload_id', uploadId);
                formData.append('lesson_id', '<?= $lesson['id'] ?>');
                formData.append('<?= CSRF_TOKEN_NAME ?>', csrfToken);

                try {
                    const res = await fetch('/api/courses/upload-chunk', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (!data.success) { this.error = data.error || 'Upload failed'; this.uploading = false; return; }
                } catch (e) {
                    this.error = 'Network error: ' + e.message;
                    this.uploading = false;
                    return;
                }
                this.progress = ((i + 1) / totalChunks) * 100;
                this.statusMessage = `Uploading chunk ${i + 1} of ${totalChunks}...`;
            }

            // Finalize
            this.statusMessage = 'Finalizing upload...';
            const finalData = new FormData();
            finalData.append('upload_id', uploadId);
            finalData.append('lesson_id', '<?= $lesson['id'] ?>');
            finalData.append('original_filename', file.name);
            finalData.append('file_size', file.size);
            finalData.append('total_chunks', totalChunks);
            finalData.append('<?= CSRF_TOKEN_NAME ?>', csrfToken);

            try {
                const res = await fetch('/api/courses/finalize-upload', { method: 'POST', body: finalData });
                const data = await res.json();
                if (data.success) {
                    this.statusMessage = 'Upload complete! Transcoding will begin shortly...';
                    setTimeout(() => location.reload(), 2000);
                } else {
                    this.error = data.error || 'Finalization failed';
                }
            } catch (e) {
                this.error = 'Network error: ' + e.message;
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#lesson_editor',
            height: 400,
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
