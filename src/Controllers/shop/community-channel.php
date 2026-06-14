<?php

use App\Models\CommunityChannel;
use App\Models\CommunityPost;
use App\Services\MembershipGuard;

if (!tenantFeature('community')) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$tenant = currentTenant();
$tenantId = currentTenantId();

$channel = CommunityChannel::findBySlug($slug, $tenantId);
if (!$channel) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Check read tier access
$userTierLevel = 0;
if (isAuthenticated()) {
    $userTierLevel = MembershipGuard::getTierLevel(currentUserId(), $tenantId);
}

if ((int)$channel['read_tier_level'] > $userTierLevel) {
    flashMessage('error', 'You need to upgrade your membership to access this channel.');
    redirect('/community');
}

// Paginate posts
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$posts = CommunityPost::allByChannel($channel['id'], $tenantId, $page, $perPage);
$totalPosts = CommunityPost::countByChannel($channel['id'], $tenantId);
$totalPages = ceil($totalPosts / $perPage);

// Check if user can post in this channel
$canPost = false;
if (isAuthenticated()) {
    $canPost = $userTierLevel >= (int)$channel['post_tier_level'] && !$channel['is_locked'];
}

view('shop/community/channel', [
    'tenant' => $tenant,
    'channel' => $channel,
    'posts' => $posts,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalPosts' => $totalPosts,
    'canPost' => $canPost,
    'userTierLevel' => $userTierLevel,
]);
