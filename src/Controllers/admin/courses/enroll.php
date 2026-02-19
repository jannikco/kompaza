<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Database\Database;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$courseId = (int)($_POST['course_id'] ?? 0);
$userId = (int)($_POST['user_id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

// Check if already enrolled
$existing = CourseEnrollment::findAnyByUserAndCourse($userId, $courseId);
if ($existing) {
    if ($existing['status'] !== 'active') {
        CourseEnrollment::update($existing['id'], ['status' => 'active']);
        flashMessage('success', 'Enrollment reactivated.');
    } else {
        flashMessage('error', 'User is already enrolled.');
    }
    redirect('/admin/kurser/tilmeldinger?course_id=' . $courseId);
}

$totalLessons = CourseLesson::countByCourse($courseId);

CourseEnrollment::create([
    'tenant_id' => $tenantId,
    'course_id' => $courseId,
    'user_id' => $userId,
    'enrollment_source' => 'manual',
    'total_lessons' => $totalLessons,
]);

Course::incrementEnrollment($courseId);
logAudit('course_enrollment_manual', 'course', $courseId, ['user_id' => $userId]);
flashMessage('success', 'Student enrolled.');
redirect('/admin/kurser/tilmeldinger?course_id=' . $courseId);
