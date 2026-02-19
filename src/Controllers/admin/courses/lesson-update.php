<?php

use App\Models\Course;
use App\Models\CourseLesson;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$lessonId = (int)($_POST['lesson_id'] ?? 0);

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    flashMessage('error', 'Lesson not found.');
    redirect('/admin/kurser');
}

$title = sanitize($_POST['title'] ?? '');
if (!$title) {
    flashMessage('error', 'Lesson title is required.');
    redirect('/admin/kurser/lektion?id=' . $lessonId);
}

CourseLesson::update($lessonId, [
    'title' => $title,
    'slug' => slugify($title),
    'lesson_type' => sanitize($_POST['lesson_type'] ?? 'video'),
    'text_content' => $_POST['text_content'] ?? null,
    'is_preview' => isset($_POST['is_preview']) ? 1 : 0,
    'drip_days_after_enrollment' => !empty($_POST['drip_days']) ? (int)$_POST['drip_days'] : null,
]);

Course::recalculateStats($lesson['course_id']);
logAudit('course_lesson_updated', 'course_lesson', $lessonId);
flashMessage('success', 'Lesson updated.');
redirect('/admin/kurser/lektion?id=' . $lessonId);
