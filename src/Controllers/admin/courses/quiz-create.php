<?php

use App\Models\Course;

$tenantId = currentTenantId();
$courseId = (int)($_GET['course_id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

// Get modules for this course
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT * FROM course_modules WHERE course_id = ? AND tenant_id = ? ORDER BY sort_order");
$stmt->execute([$courseId, $tenantId]);
$modules = $stmt->fetchAll();

view('admin/courses/quiz-form', [
    'tenant' => currentTenant(),
    'course' => $course,
    'modules' => $modules,
    'quiz' => null,
]);
