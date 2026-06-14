<?php

use App\Models\LiveSession;
use App\Models\LiveSessionRegistration;
use App\Services\MembershipGuard;

$tenant = currentTenant();
$tenantId = currentTenantId();

$upcomingSessions = LiveSession::upcoming($tenantId);
$pastSessions = LiveSession::past($tenantId);

$userTierLevel = 0;
$registeredSessionIds = [];

if (isAuthenticated()) {
    $userId = currentUserId();
    $userTierLevel = MembershipGuard::getTierLevel($userId, $tenantId);

    // Get registration status for upcoming sessions
    foreach ($upcomingSessions as $session) {
        $reg = LiveSessionRegistration::find($session['id'], $userId);
        if ($reg) {
            $registeredSessionIds[] = (int)$session['id'];
        }
    }
}

// Filter sessions by tier level visibility
$visibleUpcoming = [];
foreach ($upcomingSessions as $session) {
    if ((int)$session['min_tier_level'] <= $userTierLevel) {
        $visibleUpcoming[] = $session;
    }
}

$visiblePast = [];
foreach ($pastSessions as $session) {
    if ((int)$session['min_tier_level'] <= $userTierLevel) {
        $visiblePast[] = $session;
    }
}

view('shop/live-sessions', [
    'tenant' => $tenant,
    'upcomingSessions' => $visibleUpcoming,
    'pastSessions' => $visiblePast,
    'registeredSessionIds' => $registeredSessionIds,
    'userTierLevel' => $userTierLevel,
]);
