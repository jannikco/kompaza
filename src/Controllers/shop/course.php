<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseEnrollment;

$tenant = currentTenant();
$tenantId = currentTenantId();

$course = Course::findBySlug($slug, $tenantId);
if (!$course) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Course::incrementViews($course['id']);

$modules = CourseModule::allByCourseWithLessons($course['id']);

// Check if current user is enrolled
$enrollment = null;
if (currentUserId()) {
    $enrollment = CourseEnrollment::findByUserAndCourse(currentUserId(), $course['id']);
}

view('shop/course', [
    'tenant' => $tenant,
    'course' => $course,
    'modules' => $modules,
    'enrollment' => $enrollment,
]);
