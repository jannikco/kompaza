<?php

use App\Models\Course;

$tenant = currentTenant();
$tenantId = currentTenantId();

$courses = Course::publishedByTenant($tenantId);
$comingSoon = Course::allByTenant($tenantId, 'draft');

view('shop/courses', [
    'tenant' => $tenant,
    'courses' => $courses,
    'comingSoon' => $comingSoon,
]);
