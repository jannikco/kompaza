<?php

use App\Models\Quiz;
use App\Models\QuizQuestion;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/kurser');
}

$quizId = (int)($_POST['quiz_id'] ?? 0);

$quiz = Quiz::find($quizId, $tenantId);
if (!$quiz) {
    flashMessage('error', 'Quiz not found.');
    redirect('/admin/kurser');
}

$questionText = trim($_POST['question_text'] ?? '');
if (empty($questionText)) {
    flashMessage('error', 'Question text is required.');
    redirect('/admin/kurser/quiz/rediger?id=' . $quizId);
}

$questionId = QuizQuestion::create([
    'quiz_id' => $quizId,
    'tenant_id' => $tenantId,
    'text' => $questionText,
]);

// Add choices
$choices = $_POST['choices'] ?? [];
$correctChoice = (int)($_POST['correct_choice'] ?? 0);

foreach ($choices as $index => $choiceText) {
    $choiceText = trim($choiceText);
    if (empty($choiceText)) continue;

    QuizQuestion::addChoice([
        'question_id' => $questionId,
        'text' => $choiceText,
        'is_correct' => ($index == $correctChoice) ? 1 : 0,
    ]);
}

flashMessage('success', 'Question added.');
redirect('/admin/kurser/quiz/rediger?id=' . $quizId);
