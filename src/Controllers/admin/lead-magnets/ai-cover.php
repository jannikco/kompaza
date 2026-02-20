<?php

use App\Services\OpenAIService;

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

if (!OpenAIService::isConfigured()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'OpenAI is not configured']);
    exit;
}

$prompt = trim($_POST['prompt'] ?? '');
if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please provide a cover description']);
    exit;
}

// Wrap with book cover instructions
$fullPrompt = "Professional ebook cover design. Abstract, modern, visually striking. IMPORTANT: Do NOT include any text, letters, words, or typography anywhere in the image. The image should be purely visual with no readable content. " . $prompt;

$openai = new OpenAIService();
$tempUrl = $openai->generateImage($fullPrompt);

if (!$tempUrl) {
    echo json_encode(['success' => false, 'error' => 'Failed to generate cover image. Please try again.']);
    exit;
}

// Download the image from the temporary DALL-E URL
$ch = curl_init($tempUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($imageData)) {
    echo json_encode(['success' => false, 'error' => 'Failed to download generated image.']);
    exit;
}

// Save to a temp file and upload to storage
$tmpFile = tempnam(sys_get_temp_dir(), 'cover_');
file_put_contents($tmpFile, $imageData);

$tenantId = currentTenantId();
$filename = generateUniqueId('lm_cover_') . '.png';

// Try S3 first, then local fallback
if (\App\Services\S3Service::isConfigured()) {
    $key = "tenants/{$tenantId}/lead-magnets/{$filename}";
    $s3 = new \App\Services\S3Service();
    if ($s3->putObject($key, $tmpFile, 'image/png')) {
        $coverPath = $key;
    } else {
        $coverPath = null;
    }
} else {
    // Local storage — use copy() since this isn't an HTTP upload
    $uploadPath = tenantUploadPath('lead-magnets');
    $destPath = $uploadPath . '/' . $filename;
    if (copy($tmpFile, $destPath)) {
        $coverPath = '/uploads/' . $tenantId . '/lead-magnets/' . $filename;
    } else {
        $coverPath = null;
    }
}

@unlink($tmpFile);

if (!$coverPath) {
    echo json_encode(['success' => false, 'error' => 'Failed to save cover image.']);
    exit;
}

echo json_encode([
    'success' => true,
    'cover_image_path' => $coverPath,
    'cover_image_url' => imageUrl($coverPath),
]);
