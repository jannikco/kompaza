<?php

use App\Models\CommunityLike;
use App\Auth\Auth;

header('Content-Type: application/json');

if (!isPost() || !isAuthenticated()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$tenantId = currentTenantId();
$userId = currentUserId();
$entityType = sanitize($_POST['entity_type'] ?? '');
$entityId = (int)($_POST['entity_id'] ?? 0);

if (!in_array($entityType, ['post', 'comment']) || !$entityId) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$liked = CommunityLike::toggle($userId, $tenantId, $entityType, $entityId);
echo json_encode(['success' => true, 'liked' => $liked]);
exit;
