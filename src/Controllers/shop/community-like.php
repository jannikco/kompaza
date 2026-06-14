<?php

use App\Models\CommunityLike;
use App\Auth\Auth;

header('Content-Type: application/json');

if (!tenantFeature('community')) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

if (!isPost() || !isAuthenticated()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

if (!verifyCsrfToken($input[CSRF_TOKEN_NAME] ?? '')) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if (!checkRateLimit(getClientIp(), 'community_like', 60, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please slow down.']);
    exit;
}

$tenantId = currentTenantId();
$userId = currentUserId();

if (isset($input['post_id'])) {
    $entityType = 'post';
    $entityId = (int)$input['post_id'];
} elseif (isset($input['comment_id'])) {
    $entityType = 'comment';
    $entityId = (int)$input['comment_id'];
} else {
    $entityType = '';
    $entityId = 0;
}

if (!$entityId) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$liked = CommunityLike::toggle($userId, $tenantId, $entityType, $entityId);
$count = CommunityLike::count($tenantId, $entityType, $entityId);

echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
exit;
