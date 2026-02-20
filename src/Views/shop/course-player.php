<?php
$tenant = $tenant ?? currentTenant();
$primaryColor = $tenant['primary_color'] ?? '#3b82f6';
$companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($currentLesson['title']) ?> — <?= h($course['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: '<?= h($primaryColor) ?>' } } }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .btn-brand { background-color: <?= h($primaryColor) ?>; }
        .btn-brand:hover { filter: brightness(0.9); }
        .text-brand { color: <?= h($primaryColor) ?>; }
        .bg-brand { background-color: <?= h($primaryColor) ?>; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen font-sans antialiased" x-data="{ sidebarOpen: true }">

    <!-- Top Bar -->
    <header class="fixed top-0 left-0 right-0 z-50 h-14 bg-gray-800 border-b border-gray-700 flex items-center px-4">
        <a href="/course/<?= h($course['slug']) ?>" class="text-gray-400 hover:text-white text-sm mr-4 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <h1 class="text-sm font-semibold text-white truncate flex-1"><?= h($course['title']) ?></h1>
        <div class="flex items-center space-x-3">
            <div class="hidden sm:flex items-center space-x-2">
                <div class="w-24 bg-gray-700 rounded-full h-1.5">
                    <div class="bg-brand h-1.5 rounded-full" style="width: <?= (float)$enrollment['progress_percent'] ?>%"></div>
                </div>
                <span class="text-xs text-gray-400"><?= (int)$enrollment['progress_percent'] ?>%</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </header>

    <div class="flex pt-14 min-h-screen">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0 w-80' : '-translate-x-full w-0'" class="fixed top-14 bottom-0 left-0 z-40 bg-gray-800 border-r border-gray-700 overflow-y-auto transition-all duration-300 lg:relative lg:translate-x-0" :style="sidebarOpen ? 'min-width: 20rem' : 'min-width: 0'">
            <nav class="py-4">
                <?php foreach ($modules as $module): ?>
                <div class="mb-2" x-data="{ moduleOpen: true }">
                    <button @click="moduleOpen = !moduleOpen" class="w-full flex items-center justify-between px-4 py-2.5 text-left hover:bg-gray-750 transition">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= h($module['title']) ?></span>
                        <svg class="w-3.5 h-3.5 text-gray-500 transition-transform" :class="moduleOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="moduleOpen" x-cloak>
                        <?php foreach ($module['lessons'] as $lesson): ?>
                        <?php
                        $isActive = $lesson['id'] == $currentLesson['id'];
                        $isCompleted = in_array($lesson['id'], $completedLessonIds);
                        ?>
                        <a href="/course/<?= h($course['slug']) ?>/learn/<?= $lesson['id'] ?>"
                           class="flex items-center px-4 py-2.5 text-sm transition <?= $isActive ? 'bg-brand/20 text-white border-l-2 border-brand' : 'text-gray-300 hover:bg-gray-750 hover:text-white' ?>">
                            <span class="w-5 h-5 mr-3 flex items-center justify-center flex-shrink-0">
                                <?php if ($isCompleted): ?>
                                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <?php elseif ($isActive): ?>
                                    <svg class="w-4 h-4 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                <?php else: ?>
                                    <span class="w-2 h-2 rounded-full bg-gray-600"></span>
                                <?php endif; ?>
                            </span>
                            <span class="truncate"><?= h($lesson['title']) ?></span>
                            <?php if ($lesson['video_duration_seconds']): ?>
                                <span class="ml-auto text-xs text-gray-500 flex-shrink-0"><?= gmdate('i:s', $lesson['video_duration_seconds']) ?></span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php
                $courseQuizzes = \App\Models\Quiz::getPublishedByCourse($course['id'], currentTenantId());
                if (!empty($courseQuizzes)):
                ?>
                <div class="px-4 py-3 border-t border-gray-700">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Quizzes</p>
                    <?php foreach ($courseQuizzes as $cq): ?>
                    <a href="/course/quiz?quiz_id=<?= $cq['id'] ?>" class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-750 hover:text-white rounded-lg transition">
                        <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <?= h($cq['title']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <?php if (($currentLesson['lesson_type'] === 'video' || $currentLesson['lesson_type'] === 'video_text') && $currentLesson['video_status'] === 'ready'): ?>
            <!-- Video Player -->
            <div class="bg-black" x-data="videoPlayer()" x-init="init()">
                <div class="max-w-5xl mx-auto">
                    <video x-ref="video" class="w-full aspect-video" controls
                           @timeupdate="onTimeUpdate()" @ended="onEnded()"
                           @error="onError()">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
            <?php endif; ?>

            <div class="max-w-4xl mx-auto px-6 py-8">
                <h2 class="text-2xl font-bold text-white mb-2"><?= h($currentLesson['title']) ?></h2>

                <?php if (!empty($currentLesson['text_content'])): ?>
                <div class="prose prose-invert max-w-none mt-6">
                    <?= $currentLesson['text_content'] ?>
                </div>
                <?php endif; ?>

                <!-- Attachments -->
                <?php
                $lessonAttachments = \App\Models\LessonAttachment::getByLessonId($currentLesson['id']);
                if (!empty($lessonAttachments)):
                ?>
                <div class="mt-6 bg-gray-800 rounded-lg p-4 border border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-300 mb-3">Downloads</h3>
                    <div class="space-y-2">
                        <?php foreach ($lessonAttachments as $att): ?>
                        <a href="/lesson/attachment/download?id=<?= $att['id'] ?>" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition group">
                            <svg class="w-4 h-4 text-gray-400 mr-2 group-hover:text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                            <span class="text-sm text-gray-300 group-hover:text-white"><?= h($att['title']) ?></span>
                            <span class="text-xs text-gray-500 ml-2"><?= strtoupper($att['file_type']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action buttons -->
                <div class="mt-8 flex items-center justify-between border-t border-gray-700 pt-6" x-data="{ completed: <?= in_array($currentLesson['id'], $completedLessonIds) ? 'true' : 'false' ?> }">
                    <button @click="markComplete()" :class="completed ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-700 hover:bg-gray-600'"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="completed ? 'Completed' : 'Mark Complete'"></span>
                    </button>
                    <?php if ($nextLesson): ?>
                    <a href="/course/<?= h($course['slug']) ?>/learn/<?= $nextLesson['id'] ?>"
                       class="inline-flex items-center px-4 py-2 btn-brand text-white text-sm font-medium rounded-lg transition">
                        Next Lesson
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <?php else: ?>
                    <span class="text-sm text-gray-500">Last lesson in the course</span>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

<script>
function markComplete() {
    fetch('/api/courses/mark-complete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ lesson_id: <?= (int)$currentLesson['id'] ?> })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            this.completed = true;
        }
    });
}

function videoPlayer() {
    return {
        videoUrl: '',
        saveInterval: null,
        lastSavedPosition: 0,

        async init() {
            try {
                const res = await fetch('/api/courses/video-url?lesson_id=<?= (int)$currentLesson['id'] ?>');
                const data = await res.json();
                if (data.url) {
                    this.$refs.video.src = data.url;
                    <?php if ($currentProgress && $currentProgress['video_position_seconds'] > 0): ?>
                    this.$refs.video.addEventListener('loadedmetadata', () => {
                        this.$refs.video.currentTime = <?= (int)$currentProgress['video_position_seconds'] ?>;
                    }, { once: true });
                    <?php endif; ?>
                }
            } catch (e) {
                console.error('Failed to load video:', e);
            }

            // Save position every 15 seconds
            this.saveInterval = setInterval(() => this.savePosition(), 15000);
        },

        onTimeUpdate() {
            // Auto-complete at 90%
            const video = this.$refs.video;
            if (video.duration && (video.currentTime / video.duration) > 0.9) {
                this.markComplete();
            }
        },

        onEnded() {
            this.savePosition();
            this.markComplete();
        },

        onError() {
            // Try to refresh the presigned URL
            fetch('/api/courses/video-url?lesson_id=<?= (int)$currentLesson['id'] ?>')
                .then(r => r.json())
                .then(data => {
                    if (data.url) {
                        const currentTime = this.$refs.video.currentTime;
                        this.$refs.video.src = data.url;
                        this.$refs.video.addEventListener('loadedmetadata', () => {
                            this.$refs.video.currentTime = currentTime;
                        }, { once: true });
                    }
                });
        },

        savePosition() {
            const video = this.$refs.video;
            if (!video || !video.duration) return;
            const position = Math.floor(video.currentTime);
            if (position === this.lastSavedPosition) return;
            this.lastSavedPosition = position;
            const watchedPercent = Math.round((video.currentTime / video.duration) * 100);
            fetch('/api/courses/save-position', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    lesson_id: <?= (int)$currentLesson['id'] ?>,
                    position: position,
                    watched_percent: watchedPercent
                })
            });
        },

        markComplete() {
            fetch('/api/courses/mark-complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lesson_id: <?= (int)$currentLesson['id'] ?> })
            });
        }
    };
}
</script>

</body>
</html>
