<?php

use App\Models\CommunityChannel;

if (!isPost()) redirect('/admin/community/channels');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/community/channels');
}

$id = (int)($_POST['id'] ?? 0);
$tenantId = currentTenantId();

if (!$id) redirect('/admin/community/channels');

$channel = CommunityChannel::find($id, $tenantId);
if (!$channel) {
    flashMessage('error', 'Channel not found.');
    redirect('/admin/community/channels');
}

$name = sanitize($_POST['name'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($name);
$description = sanitize($_POST['description'] ?? '');
$icon = sanitize($_POST['icon'] ?? '');
$readTierLevel = (int)($_POST['read_tier_level'] ?? 0);
$postTierLevel = (int)($_POST['post_tier_level'] ?? 0);
$sortOrder = (int)($_POST['sort_order'] ?? 0);
$isLocked = isset($_POST['is_locked']) ? 1 : 0;

if (!$name) {
    flashMessage('error', 'Channel name is required.');
    redirect('/admin/community/channels');
}

CommunityChannel::update($id, [
    'name' => $name,
    'slug' => $slug,
    'description' => $description ?: null,
    'icon' => $icon ?: null,
    'read_tier_level' => $readTierLevel,
    'post_tier_level' => $postTierLevel,
    'sort_order' => $sortOrder,
    'is_locked' => $isLocked,
]);

logAudit('community_channel_updated', 'community_channel', $id);
flashMessage('success', 'Channel updated successfully.');
redirect('/admin/community/channels');
