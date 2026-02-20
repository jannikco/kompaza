<?php

use App\Models\EmailSequence;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$sequence = EmailSequence::find($id, $tenantId);

if (!$sequence) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$steps = EmailSequence::getSteps($id);
$enrollments = EmailSequence::getEnrollments($id);

view('admin/email-sequences/form', [
    'tenant' => currentTenant(),
    'sequence' => $sequence,
    'steps' => $steps,
    'enrollments' => $enrollments,
]);
