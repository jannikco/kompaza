<?php
$isEdit = !empty($quiz);
$pageTitle = $isEdit ? 'Edit Quiz: ' . ($quiz['title'] ?? '') : 'Create Quiz';
$currentPage = 'courses';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kurser/rediger?id=<?= $course['id'] ?>" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to <?= h($course['title']) ?></a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1"><?= $isEdit ? 'Edit Quiz' : 'Create Quiz' ?></h2>
    <p class="text-sm text-gray-500">Course: <?= h($course['title']) ?></p>
</div>

<!-- Quiz Settings Form -->
<form method="POST" action="<?= $isEdit ? '/admin/kurser/quiz/opdater' : '/admin/kurser/quiz/gem' ?>" class="max-w-3xl">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $quiz['id'] ?>">
    <?php endif; ?>
    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quiz Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($quiz['title'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="e.g. Module 1 Quiz">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="2"
                          class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                          placeholder="Optional quiz description..."><?= h($quiz['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="module_id" class="block text-sm font-medium text-gray-700 mb-1.5">Module (optional)</label>
                <select id="module_id" name="module_id" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">Course-level quiz</option>
                    <?php foreach ($modules as $module): ?>
                        <option value="<?= $module['id'] ?>" <?= ($quiz['module_id'] ?? '') == $module['id'] ? 'selected' : '' ?>><?= h($module['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="pass_threshold" class="block text-sm font-medium text-gray-700 mb-1.5">Pass Threshold (%)</label>
                <input type="number" id="pass_threshold" name="pass_threshold" min="0" max="100" step="1"
                       value="<?= h($quiz['pass_threshold'] ?? '80') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="draft" <?= ($quiz['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($quiz['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div class="flex items-center">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="shuffle_questions" value="1" <?= !empty($quiz['shuffle_questions']) ? 'checked' : '' ?>
                           class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600">Shuffle questions</span>
                </label>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition text-sm">
                <?= $isEdit ? 'Update Quiz' : 'Create Quiz' ?>
            </button>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
<!-- Questions Section -->
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Questions (<?= count($quiz['questions'] ?? []) ?>)</h3>
    </div>

    <?php if (!empty($quiz['questions'])): ?>
        <?php foreach ($quiz['questions'] as $qIndex => $question): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-4" x-data="{ editing: false }">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-500 mb-1">Question <?= $qIndex + 1 ?></p>
                    <p class="text-white font-medium" x-show="!editing"><?= h($question['text']) ?></p>
                </div>
                <div class="flex items-center space-x-2 ml-4">
                    <button @click="editing = !editing" class="text-gray-500 hover:text-gray-900 text-sm">
                        <span x-show="!editing">Edit</span>
                        <span x-show="editing">Cancel</span>
                    </button>
                    <form method="POST" action="/admin/kurser/quiz/spoergsmaal/slet" class="inline" onsubmit="return confirm('Delete this question?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-300 text-sm">Delete</button>
                    </form>
                </div>
            </div>

            <!-- Display choices -->
            <div class="mt-3 space-y-2" x-show="!editing">
                <?php foreach ($question['choices'] as $choice): ?>
                <div class="flex items-center text-sm <?= $choice['is_correct'] ? 'text-green-600' : 'text-gray-500' ?>">
                    <span class="w-5 h-5 mr-2 flex items-center justify-center">
                        <?php if ($choice['is_correct']): ?>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <?php else: ?>
                            <span class="w-2 h-2 rounded-full bg-gray-600"></span>
                        <?php endif; ?>
                    </span>
                    <?= h($choice['text']) ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Edit form -->
            <form method="POST" action="/admin/kurser/quiz/spoergsmaal/opdater" x-show="editing" x-cloak class="mt-4">
                <?= csrfField() ?>
                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text</label>
                    <input type="text" name="question_text" value="<?= h($question['text']) ?>" required
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div class="space-y-2 mb-4">
                    <label class="block text-sm font-medium text-gray-700">Choices (select the correct answer)</label>
                    <?php foreach ($question['choices'] as $cIndex => $choice): ?>
                    <div class="flex items-center space-x-2">
                        <input type="radio" name="correct_choice" value="<?= $cIndex ?>" <?= $choice['is_correct'] ? 'checked' : '' ?>
                               class="text-indigo-600 bg-white border-gray-300 focus:ring-indigo-500">
                        <input type="text" name="choices[]" value="<?= h($choice['text']) ?>"
                               class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                    <?php endforeach; ?>
                    <?php for ($i = count($question['choices']); $i < 4; $i++): ?>
                    <div class="flex items-center space-x-2">
                        <input type="radio" name="correct_choice" value="<?= $i ?>"
                               class="text-indigo-600 bg-white border-gray-300 focus:ring-indigo-500">
                        <input type="text" name="choices[]" placeholder="Option <?= $i + 1 ?>"
                               class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    Update Question
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center mb-4">
            <p class="text-gray-500">No questions yet. Add your first question below.</p>
        </div>
    <?php endif; ?>

    <!-- Add Question Form -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center text-indigo-600 hover:text-indigo-500 text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Question
        </button>
        <form method="POST" action="/admin/kurser/quiz/spoergsmaal/gem" x-show="open" x-cloak class="mt-4">
            <?= csrfField() ?>
            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text <span class="text-red-600">*</span></label>
                <input type="text" name="question_text" required
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="Enter your question...">
            </div>
            <div class="space-y-2 mb-4">
                <label class="block text-sm font-medium text-gray-700">Choices (select the correct answer)</label>
                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="flex items-center space-x-2">
                    <input type="radio" name="correct_choice" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>
                           class="text-indigo-600 bg-white border-gray-300 focus:ring-indigo-500">
                    <input type="text" name="choices[]" <?= $i < 2 ? 'required' : '' ?>
                           class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="Option <?= $i + 1 ?><?= $i < 2 ? ' (required)' : ' (optional)' ?>">
                </div>
                <?php endfor; ?>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Add Question
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
