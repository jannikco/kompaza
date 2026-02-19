<?php

use App\Models\Course;
use App\Models\CourseModule;

$tenantId = currentTenantId();
$courseId = (int)($_GET['course_id'] ?? 0);
$moduleId = (int)($_GET['module_id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

$module = CourseModule::find($moduleId, $tenantId);
if (!$module) {
    flashMessage('error', 'Module not found.');
    redirect('/admin/kurser/rediger?id=' . $courseId);
}

view('admin/courses/lesson-create', [
    'tenant' => currentTenant(),
    'course' => $course,
    'module' => $module,
]);
