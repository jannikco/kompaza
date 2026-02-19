<?php

use App\Models\Course;
use App\Models\CourseEnrollment;

$tenantId = currentTenantId();
$courseId = (int)($_GET['course_id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

$enrollments = CourseEnrollment::allByCourse($courseId);

view('admin/courses/enrollments', [
    'tenant' => currentTenant(),
    'course' => $course,
    'enrollments' => $enrollments,
]);
