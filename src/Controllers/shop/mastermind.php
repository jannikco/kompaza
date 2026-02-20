<?php

use App\Models\MastermindProgram;

$tenant = currentTenant();
$tenantId = currentTenantId();

$program = MastermindProgram::findBySlug($slug, $tenantId);
if (!$program) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$tiers = MastermindProgram::getTiers($program['id']);

view('shop/mastermind', [
    'tenant' => $tenant,
    'program' => $program,
    'tiers' => $tiers,
]);
