<?php

use App\Models\CommunityChannel;
use App\Services\MembershipGuard;

$tenant = currentTenant();
$tenantId = currentTenantId();

$allChannels = CommunityChannel::allByTenant($tenantId);

$userTierLevel = 0;
if (isAuthenticated()) {
    $userTierLevel = MembershipGuard::getTierLevel(currentUserId(), $tenantId);
}

// Filter channels by read_tier_level
$channels = [];
foreach ($allChannels as $channel) {
    if ((int)$channel['read_tier_level'] <= $userTierLevel) {
        $channels[] = $channel;
    }
}

view('shop/community', [
    'tenant' => $tenant,
    'channels' => $channels,
    'userTierLevel' => $userTierLevel,
]);
