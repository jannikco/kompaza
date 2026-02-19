<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$courseId = (int)($_POST['course_id'] ?? 0);
$moduleId = (int)($_POST['module_id'] ?? 0);

$course = Course::find($courseId, $tenantId);
$module = CourseModule::find($moduleId, $tenantId);

if (!$course || !$module) {
    flashMessage('error', 'Course or module not found.');
    redirect('/admin/kurser');
}

$title = sanitize($_POST['title'] ?? '');
if (!$title) {
    flashMessage('error', 'Lesson title is required.');
    redirect('/admin/kurser/lektion/opret?course_id=' . $courseId . '&module_id=' . $moduleId);
}

$slug = slugify($title);
$sortOrder = CourseLesson::getNextSortOrder($moduleId);

$lessonId = CourseLesson::create([
    'module_id' => $moduleId,
    'course_id' => $courseId,
    'tenant_id' => $tenantId,
    'title' => $title,
    'slug' => $slug,
    'lesson_type' => sanitize($_POST['lesson_type'] ?? 'video'),
    'text_content' => $_POST['text_content'] ?? null,
    'is_preview' => isset($_POST['is_preview']) ? 1 : 0,
    'sort_order' => $sortOrder,
    'drip_days_after_enrollment' => !empty($_POST['drip_days']) ? (int)$_POST['drip_days'] : null,
]);

Course::recalculateStats($courseId);
logAudit('course_lesson_created', 'course_lesson', $lessonId);
flashMessage('success', 'Lesson created.');
redirect('/admin/kurser/lektion?id=' . $lessonId);
