<?php

use App\Models\MembershipPlan;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    flashMessage('error', 'Membership plan not found.');
    redirect('/admin/memberships');
}

$plan = MembershipPlan::find($id, $tenantId);

if (!$plan) {
    flashMessage('error', 'Membership plan not found.');
    redirect('/admin/memberships');
}

view('admin/memberships/edit', [
    'tenant' => currentTenant(),
    'plan' => $plan,
]);
