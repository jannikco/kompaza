<?php

use App\Models\Course;
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

CourseModule::delete($moduleId, $tenantId);
Course::recalculateStats($courseId);
logAudit('course_module_deleted', 'course_module', $moduleId);
flashMessage('success', 'Module deleted.');
redirect('/admin/kurser/rediger?id=' . $courseId);
