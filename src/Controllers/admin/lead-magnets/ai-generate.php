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

// Upload PDF to S3 now so we don't need to re-upload on form submit
$pdfFilename = uploadPrivateFile($tmpPath, 'pdfs', 'lm', 'pdf');

// Extract text from PDF using pdftotext
$pdfText = '';
$extractPath = $_FILES['pdf_file']['tmp_name'];

// pdftotext might work on the original tmp file, but it may have been moved by uploadPrivateFile
// If it was moved to local storage, use that path; otherwise try the tmp path
if (file_exists($extractPath)) {
    $escapedPath = escapeshellarg($extractPath);
    $pdfText = shell_exec("pdftotext {$escapedPath} - 2>/dev/null") ?? '';
} else {
    // File was moved to local storage, try that path
    $tenantId = currentTenantId();
    $localPath = STORAGE_PATH . '/pdfs/' . $tenantId . '/' . $pdfFilename;
    if (file_exists($localPath)) {
        $escapedPath = escapeshellarg($localPath);
        $pdfText = shell_exec("pdftotext {$escapedPath} - 2>/dev/null") ?? '';
    }
}

$pdfText = trim($pdfText);

if (empty($pdfText)) {
    // Still return success with PDF uploaded, but no AI content (scanned PDF or pdftotext not available)
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
$result = $openai->generateLeadMagnetContent($pdfText, $context);

if (!$result['success']) {
    echo json_encode([
        'success' => true,
        'ai_generated' => false,
        'message' => 'AI generation failed. PDF uploaded — please fill in the fields manually.',
        'pdf_filename' => $pdfFilename,
        'pdf_original_name' => $pdfOriginalName,
        'data' => null,
    ]);
    exit;
}

$data = $result['data'];

// Ensure array fields have _json variants for backward compat
if (isset($data['features']) && is_array($data['features'])) {
    $data['features_json'] = json_encode($data['features']);
}
if (isset($data['target_audience']) && is_array($data['target_audience'])) {
    $data['target_audience_json'] = json_encode($data['target_audience']);
}
if (isset($data['faq']) && is_array($data['faq'])) {
    $data['faq_json'] = json_encode($data['faq']);
}
if (isset($data['chapters']) && is_array($data['chapters'])) {
    $data['chapters_json'] = json_encode($data['chapters']);
}
if (isset($data['key_statistics']) && is_array($data['key_statistics'])) {
    $data['key_statistics_json'] = json_encode($data['key_statistics']);
}
if (isset($data['before_after']) && is_array($data['before_after'])) {
    $data['before_after_json'] = json_encode($data['before_after']);
}
if (isset($data['testimonial_templates']) && is_array($data['testimonial_templates'])) {
    $data['testimonial_templates_json'] = json_encode($data['testimonial_templates']);
}
if (isset($data['social_proof']) && is_array($data['social_proof'])) {
    $data['social_proof_json'] = json_encode($data['social_proof']);
}

echo json_encode([
    'success' => true,
    'ai_generated' => true,
    'pdf_filename' => $pdfFilename,
    'pdf_original_name' => $pdfOriginalName,
    'data' => $data,
    'partial' => $result['partial'] ?? false,
    'errors' => $result['errors'] ?? [],
]);
