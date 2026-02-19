<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;
use App\Models\CourseEnrollment;
use App\Models\CourseProgress;
use App\Auth\Auth;

$tenant = currentTenant();
$tenantId = currentTenantId();

$course = Course::findBySlug($slug, $tenantId);
if (!$course) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Must be authenticated to access player (unless viewing preview lesson)
Auth::requireCustomer();

$userId = currentUserId();
$enrollment = CourseEnrollment::findByUserAndCourse($userId, $course['id']);

if (!$enrollment) {
    flashMessage('error', 'You need to enroll in this course first.');
    redirect('/course/' . $course['slug']);
}

CourseEnrollment::updateLastAccessed($enrollment['id']);

$modules = CourseModule::allByCourseWithLessons($course['id']);
$completedLessonIds = CourseProgress::completedLessonIds($enrollment['id']);

// Determine current lesson
$currentLesson = null;
if ($lesson_id) {
    $currentLesson = CourseLesson::find((int)$lesson_id, $tenantId);
}
if (!$currentLesson) {
    $currentLesson = CourseLesson::firstByCourse($course['id']);
}

if (!$currentLesson) {
    flashMessage('error', 'This course has no lessons yet.');
    redirect('/course/' . $course['slug']);
}

// Get progress for current lesson
$currentProgress = CourseProgress::find($enrollment['id'], $currentLesson['id']);

// Next lesson
$nextLesson = CourseLesson::nextLesson($course['id'], $currentLesson['id']);

view('shop/course-player', [
    'tenant' => $tenant,
    'course' => $course,
    'modules' => $modules,
    'enrollment' => $enrollment,
    'currentLesson' => $currentLesson,
    'currentProgress' => $currentProgress,
    'completedLessonIds' => $completedLessonIds,
    'nextLesson' => $nextLesson,
]);
