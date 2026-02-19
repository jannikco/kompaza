<?php

use App\Models\CourseEnrollment;
use App\Auth\Auth;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$enrollments = CourseEnrollment::allByUser($userId, $tenantId);

view('shop/account/courses', [
    'tenant' => $tenant,
    'enrollments' => $enrollments,
]);
