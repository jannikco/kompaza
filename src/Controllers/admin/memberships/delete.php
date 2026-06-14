<?php

use App\Models\MembershipPlan;

if (!isPost()) redirect('/admin/memberships');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/memberships');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/memberships');

$plan = MembershipPlan::find($id, $tenantId);
if (!$plan) {
    flashMessage('error', 'Membership plan not found.');
    redirect('/admin/memberships');
}

MembershipPlan::delete($id, $tenantId);

logAudit('membership_plan_deleted', 'membership_plan', $id);
flashMessage('success', 'Membership plan archived.');
redirect('/admin/memberships');
