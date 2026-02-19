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
$courseId = (int)($_POST['course_id'] ?? 0);

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    flashMessage('error', 'Lesson not found.');
    redirect('/admin/kurser');
}

CourseLesson::delete($lessonId, $tenantId);
Course::recalculateStats($courseId);
logAudit('course_lesson_deleted', 'course_lesson', $lessonId);
flashMessage('success', 'Lesson deleted.');
redirect('/admin/kurser/rediger?id=' . $courseId);
