<?php

use App\Models\MembershipPlan;
use App\Models\CustomerMembership;

$tenant = currentTenant();
$tenantId = currentTenantId();
$plans = MembershipPlan::allByTenant($tenantId, 'active');
$currentMembership = null;

if (isAuthenticated() && isCustomer()) {
    $currentMembership = CustomerMembership::findActiveByUser(currentUserId(), $tenantId);
}

view('shop/membership-plans', [
    'tenant' => $tenant,
    'plans' => $plans,
    'currentMembership' => $currentMembership,
]);
