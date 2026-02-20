<?php

use App\Models\Certificate;

$tenantId = currentTenantId();
$courseId = (int)($_GET['course_id'] ?? 0);

$certificates = [];
if ($courseId) {
    $certificates = Certificate::getByCourse($courseId, $tenantId);
} else {
    $certificates = Certificate::allByTenant($tenantId);
}

view('admin/courses/certificates', [
    'tenant' => currentTenant(),
    'certificates' => $certificates,
    'courseId' => $courseId,
]);
