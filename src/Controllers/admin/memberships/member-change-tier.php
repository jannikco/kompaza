<?php

use App\Models\CustomerMembership;
use App\Models\MembershipPlan;

if (!isPost()) redirect('/admin/memberships/members');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/memberships/members');
}

$membershipId = $_POST['membership_id'] ?? null;
$newPlanId = $_POST['plan_id'] ?? null;
$tenantId = currentTenantId();

if (!$membershipId || !$newPlanId) {
    flashMessage('error', 'Missing required fields.');
    redirect('/admin/memberships/members');
}

$membership = CustomerMembership::find($membershipId, $tenantId);
if (!$membership) {
    flashMessage('error', 'Membership not found.');
    redirect('/admin/memberships/members');
}

$newPlan = MembershipPlan::find($newPlanId, $tenantId);
if (!$newPlan) {
    flashMessage('error', 'Target plan not found.');
    redirect('/admin/memberships/members');
}

CustomerMembership::update($membershipId, [
    'plan_id' => $newPlanId,
]);

logAudit('membership_tier_changed', 'customer_membership', $membershipId);
flashMessage('success', 'Membership plan changed to ' . htmlspecialchars($newPlan['name']) . '.');
redirect('/admin/memberships/member-show?id=' . $membershipId);
