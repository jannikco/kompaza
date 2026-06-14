<?php

use App\Models\CustomerMembership;
use App\Models\MembershipPlan;

$tenantId = currentTenantId();
$planId = $_GET['plan_id'] ?? null;
$status = $_GET['status'] ?? null;

$members = CustomerMembership::allByTenant($tenantId, $status ?: null);

// Filter by plan_id if provided
if ($planId) {
    $members = array_filter($members, fn($m) => $m['plan_id'] == $planId);
    $members = array_values($members);
}

$plans = MembershipPlan::allByTenant($tenantId, null);

view('admin/memberships/members', [
    'tenant' => currentTenant(),
    'members' => $members,
    'plans' => $plans,
    'currentPlanId' => $planId,
    'currentStatus' => $status,
]);
