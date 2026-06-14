<?php

use App\Auth\Auth;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();

$sessionId = sanitize($_GET['session_id'] ?? '');

view('shop/membership-success', [
    'tenant' => $tenant,
    'sessionId' => $sessionId,
]);
