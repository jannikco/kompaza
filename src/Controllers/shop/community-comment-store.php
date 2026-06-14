<?php

use App\Auth\Auth;
use App\Models\CommunityPost;
use App\Models\CommunityComment;
use App\Models\CommunityChannel;
use App\Services\MembershipGuard;

Auth::requireCustomer();

if (!tenantFeature('community')) {
    http_response_code(404);
    view('errors/404');
    exit;
}

if (!isPost()) redirect('/community');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/community');
}

if (!checkRateLimit(getClientIp(), 'community_comment', 20, 60)) {
    flashMessage('error', 'You are commenting too fast. Please wait a moment.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/community');
}

$tenantId = currentTenantId();
$userId = currentUserId();

$postId = (int)($_POST['post_id'] ?? 0);
$post = CommunityPost::find($postId, $tenantId);

if (!$post) {
    flashMessage('error', 'Post not found.');
    redirect('/community');
}

$postUrl = '/community/' . $post['channel_slug'] . '/' . $postId;

// Check post is not locked
if ($post['is_locked']) {
    flashMessage('error', 'This post is locked. New comments are not allowed.');
    redirect($postUrl);
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
    redirect($postUrl);
}

$body = sanitize($_POST['body'] ?? '');
$parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

// Validate parent comment belongs to this post (no orphan replies)
if ($parentId) {
    $parentComment = CommunityComment::find($parentId, $tenantId);
    if (!$parentComment || (int)$parentComment['post_id'] !== $postId) {
        flashMessage('error', 'The comment you are replying to could not be found.');
        redirect($postUrl);
    }
}

if (empty($body)) {
    flashMessage('error', 'Comment content is required.');
    redirect($postUrl);
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
redirect($postUrl);
