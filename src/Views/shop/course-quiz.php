<?php
$pageTitle = $quiz['title'] . ' — ' . $course['title'];
$tenant = currentTenant();
$metaDescription = 'Take the quiz for ' . $course['title'];
$primaryColor = $tenant['primary_color'] ?? '#3b82f6';

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <a href="/course/<?= h($course['slug']) ?>/learn" class="text-sm text-brand hover:underline">&larr; Back to Course</a>

        <div class="mt-4 mb-8">
            <h1 class="text-2xl font-bold text-gray-900"><?= h($quiz['title']) ?></h1>
            <?php if ($quiz['description']): ?>
                <p class="mt-2 text-gray-600"><?= h($quiz['description']) ?></p>
            <?php endif; ?>
            <p class="mt-2 text-sm text-gray-500">
                <?= count($quiz['questions']) ?> questions &middot; Pass threshold: <?= (int)$quiz['pass_threshold'] ?>%
            </p>
        </div>

        <?php if ($hasPassed): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800 font-medium">You have already passed this quiz with a best score of <?= number_format($bestScore, 1) ?>%.</p>
            <p class="text-green-700 text-sm mt-1">You can retake it to improve your score, or <a href="/course/<?= h($course['slug']) ?>/learn" class="underline">continue the course</a>.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($quiz['questions'])): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-gray-500">This quiz has no questions yet.</p>
        </div>
        <?php else: ?>
        <form method="POST" action="/course/quiz/submit" class="space-y-6">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">

            <?php foreach ($quiz['questions'] as $qIndex => $question): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="font-semibold text-gray-900 mb-4">
                    <span class="text-brand"><?= $qIndex + 1 ?>.</span>
                    <?= h($question['text']) ?>
                </p>
                <div class="space-y-3">
                    <?php foreach ($question['choices'] as $choice): ?>
                    <label class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-brand/50 hover:bg-gray-50 cursor-pointer transition">
                        <input type="radio" name="question_<?= $question['id'] ?>" value="<?= $choice['id'] ?>" required
                               class="w-4 h-4 text-brand border-gray-300 focus:ring-brand">
                        <span class="ml-3 text-sm text-gray-700"><?= h($choice['text']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="flex items-center justify-between">
                <a href="/course/<?= h($course['slug']) ?>/learn" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="btn-brand px-8 py-3 text-white font-semibold rounded-lg transition text-base">
                    Submit Answers
                </button>
            </div>
        </form>
        <?php endif; ?>

        <?php if (!empty($attempts)): ?>
        <div class="mt-10">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Previous Attempts</h3>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Date</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Score</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Result</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($attempts as $attempt): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-600"><?= formatDate($attempt['created_at'], 'd M Y H:i') ?></td>
                            <td class="px-4 py-3 text-gray-900 font-medium"><?= number_format($attempt['score_percentage'], 1) ?>%</td>
                            <td class="px-4 py-3">
                                <?php if ($attempt['passed']): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Passed</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
