<?php
$pageTitle = 'My Courses';
$metaDescription = 'Your enrolled courses';
$tenant = $tenant ?? currentTenant();
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Courses</h1>
                <p class="mt-2 text-gray-500">Continue where you left off.</p>
            </div>
            <a href="/courses" class="text-sm font-medium text-brand hover:underline">Browse all courses &rarr;</a>
        </div>

        <?php if (empty($enrollments)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <p class="text-gray-500 mb-4">You haven't enrolled in any courses yet.</p>
                <a href="/courses" class="inline-flex items-center px-4 py-2 btn-brand text-white text-sm font-medium rounded-lg transition">
                    Browse Courses
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($enrollments as $enrollment): ?>
                <a href="/course/<?= h($enrollment['course_slug']) ?>/learn" class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300 group">
                    <div class="aspect-video bg-gray-100 overflow-hidden relative">
                        <?php if (!empty($enrollment['cover_image_path'])): ?>
                            <img src="<?= h($enrollment['cover_image_path']) ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100">
                                <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                        <?php endif; ?>
                        <?php if ((float)$enrollment['progress_percent'] >= 100): ?>
                            <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">Complete</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 group-hover:text-brand transition"><?= h($enrollment['course_title']) ?></h3>
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                                <span><?= (int)$enrollment['completed_lessons'] ?>/<?= (int)$enrollment['course_total_lessons'] ?> lessons</span>
                                <span><?= (int)$enrollment['progress_percent'] ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-brand h-1.5 rounded-full transition-all" style="width: <?= (float)$enrollment['progress_percent'] ?>%"></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            <?php if ($enrollment['last_accessed_at']): ?>
                                Last accessed <?= formatDate($enrollment['last_accessed_at'], 'd M Y') ?>
                            <?php else: ?>
                                Enrolled <?= formatDate($enrollment['enrolled_at'], 'd M Y') ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
