<?php

use App\Models\Quiz;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/kurser');
}

$courseId = (int)($_POST['course_id'] ?? 0);

$quizId = Quiz::create([
    'tenant_id' => $tenantId,
    'course_id' => $courseId,
    'module_id' => !empty($_POST['module_id']) ? (int)$_POST['module_id'] : null,
    'title' => sanitize($_POST['title'] ?? ''),
    'description' => $_POST['description'] ?? null,
    'pass_threshold' => (float)($_POST['pass_threshold'] ?? 80),
    'shuffle_questions' => isset($_POST['shuffle_questions']) ? 1 : 0,
    'status' => $_POST['status'] ?? 'draft',
]);

flashMessage('success', 'Quiz created. Now add questions.');
redirect('/admin/kurser/quiz/rediger?id=' . $quizId);
