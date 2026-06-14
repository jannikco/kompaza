<?php

use App\Models\CommunityPost;
use App\Models\CommunityChannel;

$tenantId = currentTenantId();
$hiddenPosts = CommunityPost::hiddenByTenant($tenantId);
$recentPosts = CommunityPost::recentByTenant($tenantId, 20);
$channels = CommunityChannel::allByTenant($tenantId);

view('admin/community/index', [
    'tenant' => currentTenant(),
    'hiddenPosts' => $hiddenPosts,
    'recentPosts' => $recentPosts,
    'channels' => $channels,
]);
