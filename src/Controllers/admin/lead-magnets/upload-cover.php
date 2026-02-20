<?php

header('Content-Type: application/json');

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if (empty($_FILES['cover_image']['name']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please select an image file']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Only images (jpg, png, webp, gif) are allowed']);
    exit;
}

$coverPath = uploadPublicFile($_FILES['cover_image']['tmp_name'], 'lead-magnets', 'lm_cover', $ext);

if (!$coverPath) {
    echo json_encode(['success' => false, 'error' => 'Failed to save cover image.']);
    exit;
}

echo json_encode([
    'success' => true,
    'cover_image_path' => $coverPath,
    'cover_image_url' => imageUrl($coverPath),
]);
