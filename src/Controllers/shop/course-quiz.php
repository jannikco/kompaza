<?php

use App\Auth\Auth;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Course;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();
$quizId = (int)($_GET['quiz_id'] ?? 0);

$quiz = Quiz::getWithQuestions($quizId, $tenantId);
if (!$quiz || $quiz['status'] !== 'published') {
    flashMessage('error', 'Quiz not found.');
    redirect('/courses');
}

$course = Course::find($quiz['course_id'], $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/courses');
}

// Check enrollment
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT * FROM course_enrollments WHERE course_id = ? AND user_id = ? AND status = 'active'");
$stmt->execute([$course['id'], $userId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    flashMessage('error', 'You must be enrolled in this course to take the quiz.');
    redirect('/course/' . $course['slug']);
}

// Shuffle questions if enabled
if ($quiz['shuffle_questions']) {
    shuffle($quiz['questions']);
}

// Get previous attempts
$attempts = QuizAttempt::getByUserAndQuiz($userId, $quizId);
$bestScore = QuizAttempt::getBestScore($userId, $quizId);
$hasPassed = QuizAttempt::hasPassed($userId, $quizId);

view('shop/course-quiz', [
    'tenant' => $tenant,
    'course' => $course,
    'quiz' => $quiz,
    'attempts' => $attempts,
    'bestScore' => $bestScore,
    'hasPassed' => $hasPassed,
]);
