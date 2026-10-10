<?php

use App\Models\LinkedInAccount;
use App\Services\LinkedInService;

header('Content-Type: application/json');

$tenantId = currentTenantId();
$account = LinkedInAccount::forTenant($tenantId);
if (!$account || ($account['status'] ?? '') !== 'active') {
    echo json_encode(['success' => false, 'error' => 'Connect a LinkedIn account first.']);
    exit;
}

try {
    $linkedin = LinkedInService::forAccount((int) $account['id'])->withoutDelay();
    $posts = $linkedin->listRecentPosts(10, 0);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

if ($posts === false) {
    echo json_encode(['success' => false, 'error' => $linkedin->lastError() ?: 'Could not load recent posts.']);
    exit;
}

echo json_encode(['success' => true, 'posts' => $posts]);
