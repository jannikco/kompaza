<?php

use App\Models\LiveSession;

$tenantId = currentTenantId();
$sessions = LiveSession::allByTenant($tenantId);

view('admin/live-sessions/index', [
    'tenant' => currentTenant(),
    'sessions' => $sessions,
]);
