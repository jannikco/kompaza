<?php

use App\Services\Outreach\SuggestAutomationSettings;

header('Content-Type: application/json');

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$postText = trim((string) ($input['post_text'] ?? ''));
$title = trim((string) ($input['lead_magnet_title'] ?? ''));

if ($postText === '') {
    echo json_encode(['success' => false, 'error' => 'Post text is required.']);
    exit;
}

$suggestion = SuggestAutomationSettings::fromPost($postText, $title !== '' ? $title : null);
if ($suggestion === null) {
    echo json_encode(['success' => false, 'error' => 'Could not draft settings.']);
    exit;
}

echo json_encode(['success' => true, 'suggestion' => $suggestion]);
