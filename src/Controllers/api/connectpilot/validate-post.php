<?php

use App\Services\LinkedInService;

header('Content-Type: application/json');

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$postUrl = trim($input['post_url'] ?? '');

if (empty($postUrl)) {
    echo json_encode(['success' => false, 'error' => 'Post URL is required.']);
    exit;
}

// Validate URL format
if (!str_contains($postUrl, 'linkedin.com')) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid LinkedIn post URL.']);
    exit;
}

$postUrn = LinkedInService::extractPostUrn($postUrl);

if (!$postUrn) {
    echo json_encode(['success' => false, 'error' => 'Could not extract post ID from URL. Please check the URL format.']);
    exit;
}

echo json_encode([
    'success' => true,
    'post_urn' => $postUrn,
]);
