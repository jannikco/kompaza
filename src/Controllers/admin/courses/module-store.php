<?php

use App\Models\Course;
use App\Models\CourseModule;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$courseId = (int)($_POST['course_id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

$title = sanitize($_POST['title'] ?? '');
if (!$title) {
    flashMessage('error', 'Module title is required.');
    redirect('/admin/kurser/rediger?id=' . $courseId);
}

$sortOrder = CourseModule::getNextSortOrder($courseId);

$moduleId = CourseModule::create([
    'course_id' => $courseId,
    'tenant_id' => $tenantId,
    'title' => $title,
    'description' => sanitize($_POST['description'] ?? ''),
    'sort_order' => $sortOrder,
    'drip_days_after_enrollment' => !empty($_POST['drip_days']) ? (int)$_POST['drip_days'] : null,
]);

logAudit('course_module_created', 'course_module', $moduleId);
flashMessage('success', 'Module added.');
redirect('/admin/kurser/rediger?id=' . $courseId);
