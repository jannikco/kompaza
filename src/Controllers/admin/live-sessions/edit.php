<?php

use App\Models\LiveSession;
use App\Models\MembershipPlan;

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$session = LiveSession::find($id, $tenantId);
if (!$session) {
    flashMessage('error', 'Live session not found.');
    redirect('/admin/live-sessions');
}

$plans = MembershipPlan::allByTenant($tenantId, 'active');

view('admin/live-sessions/edit', [
    'tenant' => currentTenant(),
    'session' => $session,
    'plans' => $plans,
]);
