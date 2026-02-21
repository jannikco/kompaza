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

// Validate PDF upload
if (empty($_FILES['pdf_file']['name']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please upload a PDF file']);
    exit;
}

$pdfOriginalName = $_FILES['pdf_file']['name'];
$ext = strtolower(pathinfo($pdfOriginalName, PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
    exit;
}

$tmpPath = $_FILES['pdf_file']['tmp_name'];

// Upload PDF to S3/storage now so we don't need to re-upload on form submit
$pdfFilename = uploadPrivateFile($tmpPath, 'pdfs', 'lm', 'pdf');

// Extract text from PDF using pdftotext
$pdfText = '';
$extractPath = $_FILES['pdf_file']['tmp_name'];

if (file_exists($extractPath)) {
    $escapedPath = escapeshellarg($extractPath);
    $pdfText = shell_exec("pdftotext {$escapedPath} - 2>/dev/null") ?? '';
} else {
    $tenantId = currentTenantId();
    $localPath = STORAGE_PATH . '/pdfs/' . $tenantId . '/' . $pdfFilename;
    if (file_exists($localPath)) {
        $escapedPath = escapeshellarg($localPath);
        $pdfText = shell_exec("pdftotext {$escapedPath} - 2>/dev/null") ?? '';
    }
}

$pdfText = trim($pdfText);

if (empty($pdfText)) {
    echo json_encode([
        'success' => true,
        'ai_generated' => false,
        'message' => 'PDF uploaded but could not extract text. Please fill in the fields manually.',
        'pdf_filename' => $pdfFilename,
        'pdf_original_name' => $pdfOriginalName,
        'data' => null,
    ]);
    exit;
}

// Truncate to ~12k chars to fit within context
$pdfText = mb_substr($pdfText, 0, 12000);

$context = trim($_POST['context'] ?? '');

$openai = new OpenAIService();
$orchestratorResult = $openai->analyzeLeadMagnet($pdfText, $context);

if (!$orchestratorResult) {
    echo json_encode([
        'success' => true,
        'ai_generated' => false,
        'message' => 'AI analysis failed. PDF uploaded — please fill in the fields manually.',
        'pdf_filename' => $pdfFilename,
        'pdf_original_name' => $pdfOriginalName,
        'data' => null,
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'ai_generated' => true,
    'pdf_filename' => $pdfFilename,
    'pdf_original_name' => $pdfOriginalName,
    'pdf_text' => $pdfText,
    'orchestrator' => $orchestratorResult,
]);
