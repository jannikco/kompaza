<?php

use App\Models\MembershipPlan;

$tenantId = currentTenantId();
$plans = MembershipPlan::allByTenant($tenantId, 'active');

view('admin/live-sessions/create', [
    'tenant' => currentTenant(),
    'plans' => $plans,
]);
