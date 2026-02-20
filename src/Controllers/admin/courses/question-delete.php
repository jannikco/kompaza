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

QuizQuestion::delete($questionId);

flashMessage('success', 'Question deleted.');
redirect('/admin/kurser/quiz/rediger?id=' . $quizId);
