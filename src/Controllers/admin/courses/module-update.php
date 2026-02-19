<?php

use App\Models\CourseModule;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$moduleId = (int)($_POST['module_id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);

$module = CourseModule::find($moduleId, $tenantId);
if (!$module) {
    flashMessage('error', 'Module not found.');
    redirect('/admin/kurser');
}

$title = sanitize($_POST['title'] ?? '');
if (!$title) {
    flashMessage('error', 'Module title is required.');
    redirect('/admin/kurser/rediger?id=' . $courseId);
}

CourseModule::update($moduleId, [
    'title' => $title,
    'description' => sanitize($_POST['description'] ?? ''),
    'drip_days_after_enrollment' => !empty($_POST['drip_days']) ? (int)$_POST['drip_days'] : null,
]);

logAudit('course_module_updated', 'course_module', $moduleId);
flashMessage('success', 'Module updated.');
redirect('/admin/kurser/rediger?id=' . $courseId);
