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

// Accept pre-analyzed data from the analyze step
$pdfText = trim($_POST['pdf_text'] ?? '');
$orchestratorJson = $_POST['orchestrator'] ?? '';
$language = trim($_POST['language'] ?? 'en');
$context = trim($_POST['context'] ?? '');
$pdfFilename = $_POST['pdf_filename'] ?? '';
$pdfOriginalName = $_POST['pdf_original_name'] ?? '';

if (empty($pdfText) || empty($orchestratorJson)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing pre-analyzed data. Please start over.']);
    exit;
}

$orchestratorResult = json_decode($orchestratorJson, true);
if (!$orchestratorResult) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid orchestrator data.']);
    exit;
}

$openai = new OpenAIService();
$result = $openai->generateLeadMagnetSections($pdfText, $orchestratorResult, $language, $context);

if (!$result['success']) {
    echo json_encode([
        'success' => true,
        'ai_generated' => false,
        'message' => 'AI generation failed. Please fill in the fields manually.',
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
if (isset($data['section_headings']) && is_array($data['section_headings'])) {
    $data['section_headings_json'] = json_encode($data['section_headings']);
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
