<?php

use App\Auth\Auth;
use App\Models\Certificate;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\Course;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$courseSlug = $slug ?? '';
$course = Course::findBySlug($courseSlug, $tenantId);
if (!$course) {
    $course = Course::findBySlugAnyStatus($courseSlug, $tenantId);
}
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/courses');
}

// Check if already has certificate
$existing = Certificate::findByUserAndCourse($userId, $course['id']);
if ($existing) {
    flashMessage('success', 'You already have a certificate for this course.');
    redirect('/konto/certificates');
}

// Check if user has passed a quiz for this course
$quizzes = Quiz::getPublishedByCourse($course['id'], $tenantId);
$hasPassed = false;
$bestScore = null;

foreach ($quizzes as $quiz) {
    if (QuizAttempt::hasPassed($userId, $quiz['id'])) {
        $hasPassed = true;
        $score = QuizAttempt::getBestScore($userId, $quiz['id']);
        if ($bestScore === null || $score > $bestScore) {
            $bestScore = $score;
        }
    }
}

if (!$hasPassed) {
    flashMessage('error', 'You must pass a quiz to receive a certificate.');
    redirect('/course/' . $courseSlug . '/learn');
}

// Issue certificate
$certNumber = Certificate::issue([
    'tenant_id' => $tenantId,
    'user_id' => $userId,
    'course_id' => $course['id'],
    'score_percentage' => $bestScore,
]);

flashMessage('success', 'Certificate issued! Certificate #' . $certNumber);
redirect('/konto/certificates');
