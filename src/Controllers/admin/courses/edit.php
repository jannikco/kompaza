<?php

use App\Models\Course;
use App\Models\CourseModule;

$tenantId = currentTenantId();
$courseId = (int)($_GET['id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

$modules = CourseModule::allByCourseWithLessons($courseId);

view('admin/courses/edit', [
    'tenant' => currentTenant(),
    'course' => $course,
    'modules' => $modules,
]);
