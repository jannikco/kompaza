<?php

use App\Models\MembershipPlan;
use App\Models\CustomerMembership;

$tenantId = currentTenantId();
$plans = MembershipPlan::allByTenant($tenantId, null);
$totalMembers = CustomerMembership::countByTenant($tenantId);
$mrr = MembershipPlan::getMRR($tenantId);

foreach ($plans as &$plan) {
    $plan['member_count'] = MembershipPlan::countMembers($plan['id']);
}

view('admin/memberships/index', [
    'tenant' => currentTenant(),
    'plans' => $plans,
    'totalMembers' => $totalMembers,
    'mrr' => $mrr,
]);
