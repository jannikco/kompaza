<?php

use App\Models\CustomerMembership;
use App\Models\MembershipPlan;
use App\Models\MembershipContentSelection;
use App\Models\User;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    flashMessage('error', 'Membership not found.');
    redirect('/admin/memberships/members');
}

$membership = CustomerMembership::find($id, $tenantId);

if (!$membership) {
    flashMessage('error', 'Membership not found.');
    redirect('/admin/memberships/members');
}

$plan = MembershipPlan::find($membership['plan_id'], $tenantId);
$user = User::find($membership['user_id']);
$contentSelections = MembershipContentSelection::allByUser($membership['user_id'], $tenantId);
$plans = MembershipPlan::allByTenant($tenantId, 'active');

view('admin/memberships/member-show', [
    'tenant' => currentTenant(),
    'membership' => $membership,
    'plan' => $plan,
    'user' => $user,
    'contentSelections' => $contentSelections,
    'plans' => $plans,
]);
