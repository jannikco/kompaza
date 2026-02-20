<?php

use App\Models\Quiz;
use App\Models\Course;

$tenantId = currentTenantId();
$quizId = (int)($_GET['id'] ?? 0);

$quiz = Quiz::getWithQuestions($quizId, $tenantId);
if (!$quiz) {
    flashMessage('error', 'Quiz not found.');
    redirect('/admin/kurser');
}

$course = Course::find($quiz['course_id'], $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

// Get modules for this course
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT * FROM course_modules WHERE course_id = ? AND tenant_id = ? ORDER BY sort_order");
$stmt->execute([$quiz['course_id'], $tenantId]);
$modules = $stmt->fetchAll();

view('admin/courses/quiz-form', [
    'tenant' => currentTenant(),
    'course' => $course,
    'modules' => $modules,
    'quiz' => $quiz,
]);
