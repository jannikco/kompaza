<?php

use App\Auth\Auth;
use App\Models\CommunityPost;
use App\Models\CommunityComment;
use App\Models\CommunityChannel;
use App\Services\MembershipGuard;

Auth::requireCustomer();

if (!isPost()) redirect('/community');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/community');
}

$tenantId = currentTenantId();
$userId = currentUserId();

$postId = (int)($_POST['post_id'] ?? 0);
$post = CommunityPost::find($postId, $tenantId);

if (!$post) {
    flashMessage('error', 'Post not found.');
    redirect('/community');
}

// Check post is not locked
if ($post['is_locked']) {
    flashMessage('error', 'This post is locked. New comments are not allowed.');
    redirect('/community/post/' . $postId);
}

// Check channel post tier access
$channel = CommunityChannel::find($post['channel_id'], $tenantId);
if (!$channel) {
    flashMessage('error', 'Channel not found.');
    redirect('/community');
}

$userTierLevel = MembershipGuard::getTierLevel($userId, $tenantId);
if ($userTierLevel < (int)$channel['post_tier_level']) {
    flashMessage('error', 'You need to upgrade your membership to comment in this channel.');
    redirect('/community/post/' . $postId);
}

$body = sanitize($_POST['body'] ?? '');
$parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

if (empty($body)) {
    flashMessage('error', 'Comment content is required.');
    redirect('/community/post/' . $postId);
}

$commentId = CommunityComment::create([
    'tenant_id' => $tenantId,
    'post_id' => $postId,
    'user_id' => $userId,
    'parent_id' => $parentId,
    'body' => $body,
]);

CommunityPost::incrementCommentCount($postId);

logAudit('community_comment_created', 'community_comment', $commentId);
flashMessage('success', 'Your comment has been added!');
redirect('/community/post/' . $postId);
