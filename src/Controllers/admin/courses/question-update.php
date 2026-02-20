<?php

use App\Models\Quiz;
use App\Models\QuizQuestion;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/kurser');
}

$questionId = (int)($_POST['question_id'] ?? 0);
$quizId = (int)($_POST['quiz_id'] ?? 0);

$quiz = Quiz::find($quizId, $tenantId);
if (!$quiz) {
    flashMessage('error', 'Quiz not found.');
    redirect('/admin/kurser');
}

$question = QuizQuestion::find($questionId);
if (!$question || $question['quiz_id'] != $quizId) {
    flashMessage('error', 'Question not found.');
    redirect('/admin/kurser/quiz/rediger?id=' . $quizId);
}

// Update question text
$questionText = trim($_POST['question_text'] ?? '');
if (!empty($questionText)) {
    QuizQuestion::update($questionId, ['text' => $questionText]);
}

// Replace all choices
QuizQuestion::deleteChoicesByQuestion($questionId);

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

flashMessage('success', 'Question updated.');
redirect('/admin/kurser/quiz/rediger?id=' . $quizId);
