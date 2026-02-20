<?php

use App\Services\LinkedInService;

header('Content-Type: application/json');

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$liAtCookie = trim($input['li_at_cookie'] ?? '');
$csrfToken = trim($input['csrf_token'] ?? '');

if (empty($liAtCookie) || empty($csrfToken)) {
    echo json_encode(['success' => false, 'error' => 'Both li_at cookie and CSRF token are required.']);
    exit;
}

$linkedin = new LinkedInService($liAtCookie, $csrfToken);
$profile = $linkedin->validateCookie();

if ($profile !== false) {
    $name = $profile['firstName'] ?? '';
    if (!empty($profile['lastName'])) {
        $name .= ' ' . $profile['lastName'];
    }
    $profileUrl = '';
    if (!empty($profile['miniProfile']['publicIdentifier'])) {
        $profileUrl = 'https://www.linkedin.com/in/' . $profile['miniProfile']['publicIdentifier'];
    } elseif (!empty($profile['publicIdentifier'])) {
        $profileUrl = 'https://www.linkedin.com/in/' . $profile['publicIdentifier'];
    }

    echo json_encode([
        'success' => true,
        'profile' => [
            'name' => $name,
            'email' => $profile['emailAddress'] ?? '',
            'profile_url' => $profileUrl,
        ],
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Cookie validation failed. The session may be expired or invalid.',
    ]);
}
