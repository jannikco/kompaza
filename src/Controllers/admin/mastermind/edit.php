<?php

use App\Models\MastermindProgram;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$program = MastermindProgram::find($id, $tenantId);

if (!$program) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$tiers = MastermindProgram::getTiers($id);
$enrollments = MastermindProgram::getEnrollments($id, $tenantId);

view('admin/mastermind/edit', [
    'tenant' => currentTenant(),
    'program' => $program,
    'tiers' => $tiers,
    'enrollments' => $enrollments,
]);
