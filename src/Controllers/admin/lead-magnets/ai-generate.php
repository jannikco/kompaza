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

$systemPrompt = <<<'PROMPT'
You are a marketing copywriter assistant. Analyze the PDF content provided and generate compelling marketing copy for a lead magnet landing page.

CRITICAL: Detect the language of the PDF content and write ALL output in that SAME language. If the PDF is written in Danish, ALL fields MUST be in Danish. If the PDF is in English, write in English. Never translate to English — always match the source language exactly.

Return a JSON object with exactly these fields:
- "title": A clear, descriptive title for the lead magnet (max 60 chars)
- "slug": URL-friendly slug derived from the title (lowercase, hyphens, no special chars)
- "subtitle": A supporting subtitle (1 sentence, max 120 chars)
- "meta_description": SEO meta description (max 155 chars)
- "hero_headline": An attention-grabbing headline for the landing page hero section (max 10 words, punchy and benefit-driven)
- "hero_subheadline": Supporting text below the headline (1-2 sentences, explains the value)
- "hero_cta_text": Call-to-action button text (2-4 words)
- "hero_bg_color": A professional hex color for the hero background (dark/rich tone, e.g. "#1e3a5f")
- "features_headline": A headline for the features/benefits section
- "features": An array of 3-6 objects, each with "title" (short, 3-6 words) and "description" (1 sentence). These highlight key takeaways from the PDF content.
- "target_audience": An array of exactly 3 objects, each with "icon" (a single relevant emoji), "title" (short persona name, 3-5 words), and "description" (1 sentence explaining why this persona benefits from the PDF). These represent who the lead magnet is for.
- "faq": An array of exactly 3 objects, each with "question" and "answer". These are common questions a prospect might have before downloading. Keep answers concise (1-2 sentences).
- "cover_prompt": A DALL-E image generation prompt describing an ideal abstract book cover for this PDF. Focus on visual elements, colors, mood, and abstract shapes only. Do NOT include any text or typography in the description. Example: "Abstract geometric shapes in deep blue and gold gradients, flowing lines suggesting growth and progress, minimalist professional design"
- "email_subject": Email subject line for delivering the PDF (friendly, enticing)
- "email_body_html": A short, friendly HTML email body that delivers the download link. Use {{name}} for the recipient's name and {{download_link}} for the PDF download URL. Keep it concise (3-5 short paragraphs). Use simple HTML (p tags, a tag for the link). Make it warm and professional.

Make the copy compelling and benefit-focused. The hero headline should grab attention instantly. Remember: output language MUST match the PDF language (except cover_prompt which should be in English for DALL-E).
PROMPT;

$userMessage = "Here is the content of the PDF lead magnet:\n\n" . $pdfText;

// Append optional context from user
$context = trim($_POST['context'] ?? '');
if (!empty($context)) {
    $userMessage .= "\n\nAdditional context from the author about the target audience and goals:\n" . $context;
}

$openai = new OpenAIService();
$result = $openai->chatCompletion($systemPrompt, $userMessage);

if (!$result) {
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

// Ensure features is a JSON string for the form
if (isset($result['features']) && is_array($result['features'])) {
    $result['features_json'] = json_encode($result['features']);
}

// Ensure target_audience and faq are arrays
if (isset($result['target_audience']) && is_array($result['target_audience'])) {
    $result['target_audience_json'] = json_encode($result['target_audience']);
}
if (isset($result['faq']) && is_array($result['faq'])) {
    $result['faq_json'] = json_encode($result['faq']);
}

echo json_encode([
    'success' => true,
    'ai_generated' => true,
    'pdf_filename' => $pdfFilename,
    'pdf_original_name' => $pdfOriginalName,
    'data' => $result,
]);
