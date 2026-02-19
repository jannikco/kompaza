<?php

use App\Models\CourseModule;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$courseId = (int)($_POST['course_id'] ?? 0);
$orderedIds = $_POST['module_ids'] ?? [];

if (!empty($orderedIds) && is_array($orderedIds)) {
    CourseModule::reorder($courseId, $orderedIds);
}

flashMessage('success', 'Module order updated.');
redirect('/admin/kurser/rediger?id=' . $courseId);
