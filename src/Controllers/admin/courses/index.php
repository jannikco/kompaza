<?php

use App\Models\Course;

$tenantId = currentTenantId();
$courses = Course::allByTenant($tenantId);

view('admin/courses/index', [
    'tenant' => currentTenant(),
    'courses' => $courses,
]);
