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

$basePrompt = "Professional ebook cover design. Abstract, modern, visually striking. IMPORTANT: Do NOT include any text, letters, words, or typography anywhere in the image. The image should be purely visual with no readable content. " . $prompt;

$prompts = [
    $basePrompt,
    $basePrompt . ", alternative color palette",
    $basePrompt . ", minimalist style",
];

$openai = new OpenAIService();
$tempUrls = $openai->generateImages($basePrompt, 3);

$tenantId = currentTenantId();
$covers = [];

foreach ($tempUrls as $i => $tempUrl) {
    if (!$tempUrl) {
        $covers[] = null;
        continue;
    }

    // Download the image
    $ch = curl_init($tempUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || empty($imageData)) {
        $covers[] = null;
        continue;
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'cover_');
    file_put_contents($tmpFile, $imageData);

    $filename = generateUniqueId('lm_cover_') . '.png';
    $coverPath = null;

    if (\App\Services\S3Service::isConfigured()) {
        $key = "tenants/{$tenantId}/lead-magnets/{$filename}";
        $s3 = new \App\Services\S3Service();
        if ($s3->putObject($key, $tmpFile, 'image/png')) {
            $coverPath = $key;
        }
    } else {
        $uploadPath = tenantUploadPath('lead-magnets');
        $destPath = $uploadPath . '/' . $filename;
        if (copy($tmpFile, $destPath)) {
            $coverPath = '/uploads/' . $tenantId . '/lead-magnets/' . $filename;
        }
    }

    @unlink($tmpFile);

    if ($coverPath) {
        $covers[] = [
            'cover_image_path' => $coverPath,
            'cover_image_url' => imageUrl($coverPath),
        ];
    } else {
        $covers[] = null;
    }
}

// Filter out nulls but keep at least the successful ones
$validCovers = array_values(array_filter($covers));

if (empty($validCovers)) {
    echo json_encode(['success' => false, 'error' => 'Failed to generate cover images. Please try again.']);
    exit;
}

echo json_encode([
    'success' => true,
    'covers' => $validCovers,
]);
