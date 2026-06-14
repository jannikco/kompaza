<?php

use App\Auth\Auth;
use App\Models\CustomerMembership;

if (!tenantFeature('memberships')) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$sessionId = sanitize($_GET['session_id'] ?? '');

// Only reachable after a Stripe checkout (session_id present, webhook may still
// be activating the membership) or once an active membership exists.
$membership = CustomerMembership::findActiveByUser($userId, $tenantId);
if (!$membership && $sessionId === '') {
    redirect('/membership');
}

view('shop/membership-success', [
    'tenant' => $tenant,
    'sessionId' => $sessionId,
]);
