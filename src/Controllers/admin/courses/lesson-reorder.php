<?php

use App\Models\CourseLesson;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$moduleId = (int)($_POST['module_id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);
$orderedIds = $_POST['lesson_ids'] ?? [];

if (!empty($orderedIds) && is_array($orderedIds)) {
    CourseLesson::reorder($moduleId, $orderedIds);
}

flashMessage('success', 'Lesson order updated.');
redirect('/admin/kurser/rediger?id=' . $courseId);
