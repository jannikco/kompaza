<?php

use App\Models\CommunityPost;

if (!isPost()) redirect('/admin/community');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/community');
}

$tenantId = currentTenantId();
$postId = (int)($_POST['post_id'] ?? 0);
$action = sanitize($_POST['action'] ?? '');

if (!$postId) {
    flashMessage('error', 'Post not found.');
    redirect('/admin/community');
}

$post = CommunityPost::find($postId, $tenantId);
if (!$post) {
    flashMessage('error', 'Post not found.');
    redirect('/admin/community');
}

$validActions = ['hide', 'show', 'pin', 'unpin', 'lock', 'unlock', 'delete'];
if (!in_array($action, $validActions)) {
    flashMessage('error', 'Invalid moderation action.');
    redirect('/admin/community');
}

switch ($action) {
    case 'hide':
        CommunityPost::update($postId, ['is_hidden' => 1]);
        break;
    case 'show':
        CommunityPost::update($postId, ['is_hidden' => 0]);
        break;
    case 'pin':
        CommunityPost::update($postId, ['is_pinned' => 1]);
        break;
    case 'unpin':
        CommunityPost::update($postId, ['is_pinned' => 0]);
        break;
    case 'lock':
        CommunityPost::update($postId, ['is_locked' => 1]);
        break;
    case 'unlock':
        CommunityPost::update($postId, ['is_locked' => 0]);
        break;
    case 'delete':
        CommunityPost::delete($postId, $tenantId);
        break;
}

logAudit('community_post_moderated', 'community_post', $postId, ['action' => $action]);
$actionLabels = [
    'hide' => 'hidden',
    'show' => 'shown',
    'pin' => 'pinned',
    'unpin' => 'unpinned',
    'lock' => 'locked',
    'unlock' => 'unlocked',
    'delete' => 'deleted',
];
flashMessage('success', 'Post ' . $actionLabels[$action] . ' successfully.');
redirect('/admin/community');
