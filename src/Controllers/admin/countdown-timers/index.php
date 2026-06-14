<?php

use App\Auth\Auth;
use App\Models\CountdownTimer;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();
$timers = CountdownTimer::allByTenant($tenantId);

view('admin/countdown-timers/index', [
    'timers' => $timers,
]);
