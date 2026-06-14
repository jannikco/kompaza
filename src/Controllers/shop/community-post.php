<?php

use App\Models\CommunityPost;
use App\Models\CommunityComment;
use App\Models\CommunityChannel;
use App\Models\CommunityLike;
use App\Services\MembershipGuard;

$tenant = currentTenant();
$tenantId = currentTenantId();

$post = CommunityPost::find($id, $tenantId);
if (!$post) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Check channel read access
$channel = CommunityChannel::find($post['channel_id'], $tenantId);
if (!$channel) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$userTierLevel = 0;
if (isAuthenticated()) {
    $userTierLevel = MembershipGuard::getTierLevel(currentUserId(), $tenantId);
}

if ((int)$channel['read_tier_level'] > $userTierLevel) {
    flashMessage('error', 'You need to upgrade your membership to access this content.');
    redirect('/community');
}

// Get comments
$comments = CommunityComment::allByPost($post['id'], $tenantId);

// Get user like status for post and comments
$likedPostIds = [];
$likedCommentIds = [];
if (isAuthenticated()) {
    $userId = currentUserId();
    $likedPostIds = CommunityLike::getLikedIds($userId, $tenantId, 'post', [$post['id']]);
    $commentIds = array_column($comments, 'id');
    if (!empty($commentIds)) {
        $likedCommentIds = CommunityLike::getLikedIds($userId, $tenantId, 'comment', $commentIds);
    }
}

// Check if user can comment
$canComment = false;
if (isAuthenticated()) {
    $canComment = $userTierLevel >= (int)$channel['post_tier_level'] && !$post['is_locked'];
}

view('shop/community-post', [
    'tenant' => $tenant,
    'post' => $post,
    'channel' => $channel,
    'comments' => $comments,
    'likedPostIds' => $likedPostIds,
    'likedCommentIds' => $likedCommentIds,
    'canComment' => $canComment,
    'userTierLevel' => $userTierLevel,
]);
