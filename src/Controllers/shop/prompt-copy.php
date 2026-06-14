<?php

use App\Models\Prompt;
use App\Services\MembershipGuard;

header('Content-Type: application/json');

if (!isPost()) {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$tenantId = currentTenantId();
$promptId = (int)($_POST['id'] ?? 0);
$prompt = Prompt::find($promptId, $tenantId);

if (!$prompt) {
    echo json_encode(['error' => 'Prompt not found']);
    exit;
}

// Check tier access
if ($prompt['membership_tier_level'] > 0) {
    if (!isAuthenticated()) {
        echo json_encode(['error' => 'Please log in to access this prompt']);
        exit;
    }
    if (!MembershipGuard::canAccess(currentUserId(), $tenantId, $prompt['membership_tier_level'])) {
        echo json_encode(['error' => 'Upgrade your membership to access this prompt']);
        exit;
    }
}

Prompt::incrementCopyCount($promptId);
echo json_encode(['success' => true, 'prompt_text' => $prompt['prompt_text']]);
exit;
