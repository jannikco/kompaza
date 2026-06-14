<?php

use App\Auth\Auth;
use App\Models\CommunityChannel;
use App\Models\CommunityPost;
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

if (!checkRateLimit(getClientIp(), 'community_post', 10, 60)) {
    flashMessage('error', 'You are posting too fast. Please wait a moment.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/community');
}

$tenantId = currentTenantId();
$userId = currentUserId();

$channelId = (int)($_POST['channel_id'] ?? 0);
$channel = CommunityChannel::find($channelId, $tenantId);

if (!$channel) {
    flashMessage('error', 'Channel not found.');
    redirect('/community');
}

// Check channel is not locked
if ($channel['is_locked']) {
    flashMessage('error', 'This channel is locked. New posts are not allowed.');
    redirect('/community/' . $channel['slug']);
}

// Check post tier access
$userTierLevel = MembershipGuard::getTierLevel($userId, $tenantId);
if ($userTierLevel < (int)$channel['post_tier_level']) {
    flashMessage('error', 'You need to upgrade your membership to post in this channel.');
    redirect('/community/' . $channel['slug']);
}

$title = sanitize($_POST['title'] ?? '');
$body = sanitize($_POST['body'] ?? '');

if (empty($body)) {
    flashMessage('error', 'Post content is required.');
    redirect('/community/' . $channel['slug']);
}

$postId = CommunityPost::create([
    'tenant_id' => $tenantId,
    'channel_id' => $channelId,
    'user_id' => $userId,
    'title' => $title ?: null,
    'body' => $body,
]);

logAudit('community_post_created', 'community_post', $postId);
flashMessage('success', 'Your post has been published!');
redirect('/community/' . $channel['slug']);
