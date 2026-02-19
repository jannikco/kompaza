<?php

use App\Models\Course;
use App\Models\CourseEnrollment;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$enrollmentId = (int)($_POST['enrollment_id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);

$enrollment = CourseEnrollment::find($enrollmentId, $tenantId);
if (!$enrollment) {
    flashMessage('error', 'Enrollment not found.');
    redirect('/admin/kurser');
}

CourseEnrollment::update($enrollmentId, ['status' => 'cancelled']);
Course::decrementEnrollment($courseId);
logAudit('course_enrollment_cancelled', 'course_enrollment', $enrollmentId);
flashMessage('success', 'Student unenrolled.');
redirect('/admin/kurser/tilmeldinger?course_id=' . $courseId);
