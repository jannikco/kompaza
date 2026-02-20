<?php

use App\Models\MastermindProgram;

$tenantId = currentTenantId();
$programs = MastermindProgram::allByTenant($tenantId);

// Get enrollment counts for each program
$enrollmentCounts = [];
foreach ($programs as $program) {
    $enrollmentCounts[$program['id']] = MastermindProgram::countEnrollments($program['id']);
}

view('admin/mastermind/index', [
    'tenant' => currentTenant(),
    'programs' => $programs,
    'enrollmentCounts' => $enrollmentCounts,
]);
