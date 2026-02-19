<?php

use App\Models\Course;

$tenant = currentTenant();
$tenantId = currentTenantId();

$courses = Course::publishedByTenant($tenantId);

view('shop/courses', [
    'tenant' => $tenant,
    'courses' => $courses,
]);
