<?php

use App\Models\MembershipPlan;
use App\Models\CustomerMembership;

if (!tenantFeature('memberships')) {
    http_response_code(404);
    view('errors/404');
    exit;
}

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
