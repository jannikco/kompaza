<?php
$pageTitle = 'Quiz Result — ' . $course['title'];
$tenant = currentTenant();
$metaDescription = 'Quiz result';
$primaryColor = $tenant['primary_color'] ?? '#3b82f6';

ob_start();
?>

<section class="py-12 lg:py-20">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <?php if ($passed): ?>
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Congratulations!</h1>
                <p class="mt-2 text-lg text-gray-600">You passed the quiz!</p>
            </div>
        <?php else: ?>
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Not Quite</h1>
                <p class="mt-2 text-lg text-gray-600">You didn't pass this time, but you can try again!</p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl border border-gray-200 p-8 mb-8">
            <div class="text-5xl font-bold <?= $passed ? 'text-green-600' : 'text-red-600' ?> mb-2">
                <?= number_format($scorePercentage, 1) ?>%
            </div>
            <p class="text-gray-600">
                <?= $correctAnswers ?> out of <?= $totalQuestions ?> correct
            </p>
            <p class="text-sm text-gray-500 mt-1">
                Pass threshold: <?= (int)$quiz['pass_threshold'] ?>%
            </p>
        </div>

        <div class="flex items-center justify-center space-x-4">
            <?php if (!$passed): ?>
                <a href="/course/quiz?quiz_id=<?= $quiz['id'] ?>" class="btn-brand px-6 py-3 text-white font-semibold rounded-lg transition">
                    Try Again
                </a>
            <?php endif; ?>
            <?php if ($passed): ?>
                <a href="/course/<?= h($course['slug']) ?>/certificate" class="btn-brand px-6 py-3 text-white font-semibold rounded-lg transition">
                    Get Certificate
                </a>
            <?php endif; ?>
            <a href="/course/<?= h($course['slug']) ?>/learn" class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                Back to Course
            </a>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
