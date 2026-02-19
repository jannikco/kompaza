<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;

$tenantId = currentTenantId();
$lessonId = (int)($_GET['id'] ?? 0);

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    flashMessage('error', 'Lesson not found.');
    redirect('/admin/kurser');
}

$course = Course::find($lesson['course_id'], $tenantId);
$module = CourseModule::find($lesson['module_id'], $tenantId);

if (!$course || !$module) {
    flashMessage('error', 'Course or module not found.');
    redirect('/admin/kurser');
}

view('admin/courses/lesson-edit', [
    'tenant' => currentTenant(),
    'course' => $course,
    'module' => $module,
    'lesson' => $lesson,
]);
