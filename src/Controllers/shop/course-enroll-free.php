<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Auth\Auth;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$course = Course::findBySlug($slug, $tenantId);
if (!$course) {
    http_response_code(404);
    view('errors/404');
    exit;
}

if ($course['pricing_type'] !== 'free') {
    flashMessage('error', 'This course is not free.');
    redirect('/course/' . $course['slug']);
}

// Check if already enrolled
$existing = CourseEnrollment::findAnyByUserAndCourse($userId, $course['id']);
if ($existing) {
    if ($existing['status'] !== 'active') {
        CourseEnrollment::update($existing['id'], ['status' => 'active']);
    }
    redirect('/course/' . $course['slug'] . '/learn');
}

$totalLessons = CourseLesson::countByCourse($course['id']);

CourseEnrollment::create([
    'tenant_id' => $tenantId,
    'course_id' => $course['id'],
    'user_id' => $userId,
    'enrollment_source' => 'free',
    'total_lessons' => $totalLessons,
]);

Course::incrementEnrollment($course['id']);
logAudit('course_enrollment_free', 'course', $course['id']);
flashMessage('success', 'You are now enrolled! Start learning.');
redirect('/course/' . $course['slug'] . '/learn');
