<?php

use App\Models\Course;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$courseId = (int)($_POST['id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

Course::delete($courseId, $tenantId);
logAudit('course_deleted', 'course', $courseId);
flashMessage('success', 'Course deleted.');
redirect('/admin/kurser');
