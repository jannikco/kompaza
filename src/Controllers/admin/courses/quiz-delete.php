<?php

use App\Models\Quiz;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/kurser');
}

$quizId = (int)($_POST['id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);

$quiz = Quiz::find($quizId, $tenantId);
if (!$quiz) {
    flashMessage('error', 'Quiz not found.');
    redirect('/admin/kurser');
}

Quiz::delete($quizId, $tenantId);

flashMessage('success', 'Quiz deleted.');
redirect('/admin/kurser/rediger?id=' . $courseId);
